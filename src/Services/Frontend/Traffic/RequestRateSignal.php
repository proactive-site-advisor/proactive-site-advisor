<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RequestRateSignal
 *
 * Detects bot-like request velocity.
 *
 * This class does not block traffic.
 * It protects traffic statistics from polluted data
 * by classifying abnormal request bursts as bot-like.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
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
     * Prevents multiple increments during one request lifecycle.
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
     * No raw requester data is stored.
     *
     * @return string
     */
    private static function requestHash(): string
    {
        return md5(
            self::getIp()
            . '|'
            . self::getUserAgent()
            . '|'
            . sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''))
            . '|'
            . sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_CH_UA'] ?? ''))
        );
    }

    /**
     * Get current user agent.
     *
     * @return string
     */
    private static function getUserAgent(): string
    {
        return sanitize_text_field(
            wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')
        );
    }


    /**
     * Get requester IP.
     *
     * REMOTE_ADDR is preferred because forwarded headers
     * can be spoofed.
     *
     * @return string
     */
    private static function getIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && is_string($_SERVER[$header]) && $_SERVER[$header] !== '') {
                $ip = sanitize_text_field(wp_unslash($_SERVER[$header]));

                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $ip);
                    $ip  = trim($ips[0]);
                }

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }
}