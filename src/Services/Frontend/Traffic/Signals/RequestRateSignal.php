<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RequestRateSignal
 *
 * Detects bot-like request velocity.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class RequestRateSignal
{
    /**
     * Sliding window duration.
     */
    private const WINDOW = 10;

    /**
     * Maximum requests allowed inside window.
     */
    private const LIMIT = 20;

    /**
     * Runtime cached counter.
     *
     * @var int|null
     */
    private static ?int $count = null;

    /**
     * Determine whether current request looks bot-like.
     *
     * @return bool
     */
    public static function isBotLike(): bool
    {
        return self::count() > self::LIMIT;
    }

    /**
     * Get current requester request count.
     *
     * @return int
     */
    public static function getCount(): int
    {
        return self::count();
    }

    /**
     * Increment and return request counter.
     *
     * @return int
     */
    private static function count(): int
    {
        if (self::$count !== null) {
            return self::$count;
        }

        $cache = CacheManager::instance();

        $key = CacheKeys::requestRate(
            self::requestHash()
        );

        $count = (int)$cache->get($key);

        $count++;

        $cache->set(
            $key,
            $count,
            self::WINDOW
        );

        self::$count = $count;

        return $count;
    }

    /**
     * Build anonymous requester fingerprint.
     *
     * @return string
     */
    private static function requestHash(): string
    {
        return md5(
            HeaderReader::getIp()
            . '|'
            . HeaderReader::getUserAgent()
            . '|'
            . HeaderReader::getAcceptLanguage()
            . '|'
            . HeaderReader::getSecChUa()
        );
    }
}