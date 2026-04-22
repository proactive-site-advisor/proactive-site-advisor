<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache key definitions used by the plugin.
 *
 * Returned keys do not include cache group prefixes.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @version 1.0.0
 */
final class CacheKeys
{
    /**
     * Prevent instantiation
     */
    private function __construct()
    {
    }

    /**
     * Cache key for persistent admin notices.
     *
     * Stores notices that should remain until manually dismissed.
     */
    private const KEY_ADMIN_NOTICES = 'admin_notices';

    /**
     * Cache key for flash admin notices.
     *
     * Stores one-time notices that display on the next request and auto-expire.
     */
    private const KEY_ADMIN_FLASH_NOTICES = 'admin_flash_notices';

    /**
     * Cache key used to flag when rewrite rules should be flushed.
     */
    private const KEY_FLUSH_REWRITE = 'flush_rewrite_rules';

    /**
     * Cache key used for daily cron lock prevention.
     */
    private const KEY_DAILY_LOCK = 'daily_lock';

    /**
     * Cache key prefix for pageview counters.
     *
     * Appended with a normalized date key (Ymd), e.g. "pv_20260416".
     */
    private const PREFIX_PAGEVIEWS = 'pv_';

    /**
     * Cache key prefix for daily 404 total counters.
     *
     * Appended with a normalized date key (Ymd), e.g. "404_total_20260416".
     */
    private const PREFIX_404_TOTAL = '404_total_';

    /**
     * Cache key prefix for daily 404 path maps.
     *
     * Used to store structured lists of URLs causing 404s for the day.
     * Appended with a normalized date key (Ymd), e.g. "404_map_20260416".
     */
    private const PREFIX_404_MAP = '404_map_';

    /**
     * Normalize date key to Ymd format.
     *
     * @param string $dateKey
     * @return string
     */
    private static function normalizeDateKey(string $dateKey): string
    {
        // Replace all non-digits using \D instead of [^0-9]
        $dateKey = preg_replace('/\D/', '', $dateKey);

        if (strlen($dateKey) !== 8) {
            return DateTimeUtils::todayKey();
        }

        return $dateKey;
    }

    /**
     * Get admin notices cache key.
     *
     * @return string
     */
    public static function adminNotices(): string
    {
        return self::KEY_ADMIN_NOTICES;
    }

    /**
     * Get admin flash notices cache key.
     *
     * @return string
     */
    public static function adminFlashNotices(): string
    {
        return self::KEY_ADMIN_FLASH_NOTICES;
    }

    /**
     * Get rewrite rules flush flag key.
     *
     * @return string
     */
    public static function flushRewriteRules(): string
    {
        return self::KEY_FLUSH_REWRITE;
    }

    /**
     * Daily cron lock key.
     *
     * @return string
     */
    public static function dailyLock(): string
    {
        return self::KEY_DAILY_LOCK;
    }

    /**
     * Pageviews for today.
     *
     * @return string
     */
    public static function pageviewsToday(): string
    {
        return self::PREFIX_PAGEVIEWS . DateTimeUtils::todayKey();
    }

    /**
     * 404 total count for today.
     *
     * @return string
     */
    public static function notFoundTotalToday(): string
    {
        return self::PREFIX_404_TOTAL . DateTimeUtils::todayKey();
    }

    /**
     * 404 total count for today.
     *
     * @return string
     */
    public static function notFoundMapToday(): string
    {
        return self::PREFIX_404_MAP . DateTimeUtils::todayKey();
    }

    /**
     * Pageviews for specific date (Ymd).
     *
     * @param string $dateKey
     *
     * @return string
     */
    public static function pageviewsForDate(string $dateKey): string
    {
        return self::PREFIX_PAGEVIEWS . self::normalizeDateKey($dateKey);
    }

    /**
     * 404 total for specific date (Ymd).
     *
     * @param string $dateKey
     *
     * @return string
     */
    public static function notFoundTotalForDate(string $dateKey): string
    {
        return self::PREFIX_404_TOTAL . self::normalizeDateKey($dateKey);
    }

    /**
     * 404 path map for specific date (Ymd).
     *
     * @param string $dateKey
     *
     * @return string
     */
    public static function notFoundMapForDate(string $dateKey): string
    {
        return self::PREFIX_404_MAP . self::normalizeDateKey($dateKey);
    }
}
