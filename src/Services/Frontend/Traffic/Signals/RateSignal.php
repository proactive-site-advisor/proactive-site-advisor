<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects bots based on request rate analysis.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class RateSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Rate window in seconds. */
    private const WINDOW = 10;

    /** Maximum allowed burst requests. */
    private const BURST_LIMIT = 3;

    /** Cached request count. */
    private static ?int $count = null;

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        if ($this->hasHighBurstRate()) {
            return true;
        }

        if ($this->isBlatantBot()) {
            return true;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $count = $this->count();

        if ($count <= 5) {
            return 0;
        }

        return 1;
    }

    /** Checks if request rate is blatantly a bot. */
    private function isBlatantBot(): bool
    {
        return $this->count() > 10;
    }

    /** Checks if request burst rate exceeds limit. */
    private function hasHighBurstRate(): bool
    {
        static $burstCount = null;

        if ($burstCount !== null) {
            return $burstCount > self::BURST_LIMIT;
        }

        $cache = CacheManager::instance();

        $secondKey = DateTimeUtils::timestamp();
        $key       = CacheKeys::burstRate($this->requestHash(), $secondKey);

        $count = (int)$cache->get($key);
        $count++;
        $cache->set($key, $count, 2);

        $burstCount = $count;

        return $burstCount > self::BURST_LIMIT;
    }

    /** Returns request count within the window. */
    private function count(): int
    {
        if (self::$count !== null) {
            return self::$count;
        }

        $cache = CacheManager::instance();

        $key = CacheKeys::requestRate($this->requestHash());

        $count = (int)$cache->get($key);

        $count++;

        $cache->set($key, $count, self::WINDOW);

        self::$count = $count;

        return $count;
    }

    /** Builds anonymous requester fingerprint. */
    private function requestHash(): string
    {
        $ip = HeaderReader::getIp();

        $baseFingerprint = HeaderReader::getUserAgent()
            . '|'
            . HeaderReader::getAcceptLanguage()
            . '|'
            . HeaderReader::getSecChUa();

        if ($ip === '' || $ip === 'unknown') {
            return 'noip_' . md5($baseFingerprint);
        }

        return md5($ip . '|' . $baseFingerprint);
    }
}