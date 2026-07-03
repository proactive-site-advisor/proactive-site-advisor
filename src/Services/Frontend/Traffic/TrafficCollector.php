<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficCollector
 *
 * Collects frontend pageview counts using WordPress transients.
 * Runs only on legitimate frontend requests, skipping admin, REST, AJAX,
 * cron, feed, and preview requests.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class TrafficCollector
{
    /**
     * Transient TTL in seconds (10 days).
     * Long TTL ensures transient survives until daily cron processes it.
     */
    private const TRANSIENT_TTL = DAY_IN_SECONDS * 10;

    /**
     * Maximum number of bot names to keep in daily cache.
     */
    private const MAX_BOT_NAMES = 30;

    /**
     * Increment pageview count if this is a valid frontend request.
     *
     * @return void
     */
    public function maybeCountPageview(): void
    {
        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        $cache = CacheManager::instance();

        if ($this->isVisitor() && !$this->isAdvancedBot()) {
            $key = CacheKeys::pageviewsToday();
            $cache->increment($key, 1, self::TRANSIENT_TTL);
        } else {
            $key = CacheKeys::pageviewsBotToday();
            $cache->increment($key, 1, self::TRANSIENT_TTL);

            $this->incrementBotNameCount();
        }
    }

    /**
     * Increase the daily counter for a specific bot name.
     *
     * @return void
     */
    private function incrementBotNameCount(): void
    {
        $botName = BotDetector::getBotName() ?: 'unknown';
        $key     = CacheKeys::botNameCountsToday();

        $cache  = CacheManager::instance();
        $counts = $this->getBotNameMap($key);

        $botName          = strtolower($botName);
        $counts[$botName] = ($counts[$botName] ?? 0) + 1;

        $counts = $this->pruneBotNames($counts);

        $cache->set($key, wp_json_encode($counts), self::TRANSIENT_TTL);
    }

    /**
     * Prune bot name counts to keep only the top names.
     *
     * @param array $counts
     *
     * @return array
     */
    private function pruneBotNames(array $counts): array
    {
        if (count($counts) <= self::MAX_BOT_NAMES) {
            return $counts;
        }

        arsort($counts);
        return array_slice($counts, 0, self::MAX_BOT_NAMES, true);
    }

    /**
     * Get bot name counts map from cache.
     *
     * @param string $mapKey
     *
     * @return array
     */
    private function getBotNameMap(string $mapKey): array
    {
        $raw = CacheManager::instance()->get($mapKey);
        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Determine if the current request is from a real human user.
     * A request is considered real ONLY if all signals pass.
     *
     * @return bool
     */
    private function isVisitor(): bool
    {
        if (BotDetector::isBot()) {
            return false;
        }

        if (RequestRateSignal::isBotLike()) {
            return false;
        }

        if (!BrowserSignal::isBrowser()) {
            return false;
        }

        return true;
    }

    /**
     * Additional bot detection using new methods from other classes.
     * This does not replace isVisitor(), but complements it.
     *
     * @return bool
     */
    private function isAdvancedBot(): bool
    {
        if (BotDetector::isHeadless()) {
            return true;
        }

        if (BrowserSignal::isSuspicious()) {
            return true;
        }

        return false;
    }
}
