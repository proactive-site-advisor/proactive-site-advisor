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
 * Detects suspicious request velocity.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.1.0
 */
class RequestRateSignal
{
    /**
     * Sliding window duration.
     */
    private const WINDOW = 10;

    /**
     * Runtime cached counter.
     *
     * @var int|null
     */
    private static ?int $count = null;


    /**
     * Get request rate suspicion score.
     *
     * @return int
     */
    public static function getScore(): int
    {
        $count = self::count();

        if ($count <= 5) {
            return 0;
        }

        if ($count <= 15) {
            return 1;
        }

        if ($count <= 30) {
            return 2;
        }

        if ($count <= 50) {
            return 3;
        }

        return 5;
    }


    /**
     * Determine whether current request looks bot-like.
     *
     * Backward compatibility method.
     *
     * @return bool
     */
    public static function isBotLike(): bool
    {
        return self::getScore() >= 4;
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
        $ip = HeaderReader::getIp();

        if ($ip === '' || $ip === 'unknown') {
            return 'rate_skip_' . uniqid('', true);
        }

        return md5(
            $ip
            . '|'
            . HeaderReader::getUserAgent()
            . '|'
            . HeaderReader::getAcceptLanguage()
            . '|'
            . HeaderReader::getSecChUa()
        );
    }
}