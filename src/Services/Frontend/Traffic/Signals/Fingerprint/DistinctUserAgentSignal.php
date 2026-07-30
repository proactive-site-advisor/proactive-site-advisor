<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes distinct User-Agents from the same IP for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class DistinctUserAgentSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Cache lifetime in seconds for IP‑based User‑Agent tracking. */
    private const CACHE_TTL = 15;

    /** Default minimum number of distinct User‑Agents to flag as a definite bot. */
    private const MIN_DISTINCT_UAS_FOR_BOT = 4;

    /** Threshold for the number of distinct User‑Agents to add a suspicion score. */
    private const DISTINCT_UA_THRESHOLD = 3;

    /** Score value for distinct UA count. */
    private const SCORE_DISTINCT_UA = 2;

    /** Cached request count. */
    private static ?int $count = null;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        /**
         * Filters the minimum number of distinct user-agents required to flag as a definite bot.
         *
         * @param int $minCount
         * @since 1.0.0
         */
        $minCount = apply_filters('proactive_site_advisor_min_distinct_uas_for_bot', self::MIN_DISTINCT_UAS_FOR_BOT);

        return $this->getDistinctUserAgentCount() >= (int)$minCount;
    }

    /**
     * {@inheritDoc}
     */
    public function getScore(): int
    {
        if ($this->getDistinctUserAgentCount() >= self::DISTINCT_UA_THRESHOLD) {
            return self::SCORE_DISTINCT_UA;
        }

        return 0;
    }

    /**
     * Returns the number of distinct normalized User-Agents for this IP within the last 15 seconds.
     */
    private function getDistinctUserAgentCount(): int
    {
        if (self::$count !== null) {
            return self::$count;
        }

        $ip = HeaderReader::getIp();
        if ($ip === '' || $ip === 'unknown') {
            return 0;
        }

        $currentUa = HeaderReader::getUserAgent();
        if ($currentUa === '') {
            return 0;
        }

        $cache    = CacheManager::instance();
        $cacheKey = CacheKeys::ipUserAgents(md5($ip));

        $recentUas = $cache->get($cacheKey);
        if (!is_array($recentUas)) {
            $recentUas = [];
        }

        $normalized = preg_replace('/\s+/', ' ', trim($currentUa));
        $normalized = preg_replace('/AppleWebKit\/[\d.]+/', '', $normalized);
        $normalized = preg_replace('/Safari\/[\d.]+/', '', $normalized);
        $normalized = preg_replace('/Chrome\/[\d.]+/', 'Chrome', $normalized);
        $normalized = preg_replace('/Edg\/[\d.]+/', 'Edg', $normalized);
        $normalized = preg_replace('/OPR\/[\d.]+/', 'OPR', $normalized);
        $normalized = preg_replace('/Firefox\/[\d.]+/', 'Firefox', $normalized);
        $normalized = trim($normalized);

        if (!in_array($normalized, $recentUas, true)) {
            $recentUas[] = $normalized;
        }

        $cache->set($cacheKey, $recentUas, self::CACHE_TTL);

        self::$count = count($recentUas);
        return self::$count;
    }
}