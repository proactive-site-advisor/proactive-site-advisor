<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CacheKeys
 *
 * Centralized cache key definitions for the plugin.
 * Keys returned here do NOT include cache group prefixes.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since 1.0.0
 */
final class CacheKeys
{
    private const PREFIX_PAGEVIEWS  = 'pv_';
    private const PREFIX_404_TOTAL  = '404_total_';
    private const PREFIX_404_MAP    = '404_map_';
    private const KEY_DAILY_LOCK    = 'daily_lock';
    private const KEY_ADMIN_NOTICES = 'admin_notices';
    private const KEY_FLUSH_REWRITE = 'flush_rewrite_rules';

    /**
     * Pageviews for today.
     */
    public static function pageviewsToday(): string
    {
        return self::PREFIX_PAGEVIEWS . DateTimeUtils::todayKey();
    }

    /**
     * 404 total count for today.
     */
    public static function notFoundTotalToday(): string
    {
        return self::PREFIX_404_TOTAL . DateTimeUtils::todayKey();
    }

    /**
     * 404 path map for today.
     */
    public static function notFoundMapToday(): string
    {
        return self::PREFIX_404_MAP . DateTimeUtils::todayKey();
    }

    /**
     * Pageviews for specific date (Ymd).
     */
    public static function pageviewsForDate(string $dateKey): string
    {
        return self::PREFIX_PAGEVIEWS . self::normalizeDateKey($dateKey);
    }

    /**
     * 404 total for specific date (Ymd).
     */
    public static function notFoundTotalForDate(string $dateKey): string
    {
        return self::PREFIX_404_TOTAL . self::normalizeDateKey($dateKey);
    }

    /**
     * 404 path map for specific date (Ymd).
     */
    public static function notFoundMapForDate(string $dateKey): string
    {
        return self::PREFIX_404_MAP . self::normalizeDateKey($dateKey);
    }

    /**
     * Daily cron lock key.
     */
    public static function dailyLock(): string
    {
        return self::KEY_DAILY_LOCK;
    }

    /**
     * Admin notices key.
     */
    public static function adminNotices(): string
    {
        return self::KEY_ADMIN_NOTICES;
    }

    /**
     * Rewrite rules flush flag.
     */
    public static function flushRewriteRules(): string
    {
        return self::KEY_FLUSH_REWRITE;
    }

    /**
     * Normalize date key to Ymd format.
     */
    private static function normalizeDateKey(string $dateKey): string
    {
        $dateKey = preg_replace('/[^0-9]/', '', $dateKey);

        if (strlen($dateKey) !== 8) {
            return DateTimeUtils::todayKey();
        }

        return $dateKey;
    }
}
