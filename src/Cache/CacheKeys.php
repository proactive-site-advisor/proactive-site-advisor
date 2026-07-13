<?php

namespace ProactiveSiteAdvisor\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache key definitions used by the plugin.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since   1.0.0
 */
class CacheKeys
{
    /** Prevent instantiation. */
    private function __construct()
    {
    }

    /** Cache key for persistent admin notices. */
    private const KEY_ADMIN_NOTICES = 'admin_notices';

    /** Cache key for flash admin notices. */
    private const KEY_ADMIN_FLASH_NOTICES = 'admin_flash_notices';

    /** Cache key used to flag when rewrite rules should be flushed. */
    private const KEY_FLUSH_REWRITE = 'flush_rewrite_rules';

    /** Cache key used for daily cron lock prevention. */
    private const KEY_DAILY_LOCK = 'daily_lock';

    /** Prefix for request rate tracking. */
    private const PREFIX_REQUEST_RATE = 'rate_';

    /** Prefix for burst rate tracking (per second). */
    private const PREFIX_BURST_RATE = 'burst_';

    /** Prefix for table columns cache key. */
    private const PREFIX_TABLE_COLUMNS = 'table_columns';

    /** Get admin notices cache key. */
    public static function adminNotices(): string
    {
        return self::KEY_ADMIN_NOTICES;
    }

    /** Get admin flash notices cache key. */
    public static function adminFlashNotices(): string
    {
        return self::KEY_ADMIN_FLASH_NOTICES;
    }

    /** Get rewrite rules flush flag key. */
    public static function flushRewriteRules(): string
    {
        return self::KEY_FLUSH_REWRITE;
    }

    /** Daily cron lock key. */
    public static function dailyLock(): string
    {
        return self::KEY_DAILY_LOCK;
    }

    /** Request rate key for anonymous requester. */
    public static function requestRate(string $hash): string
    {
        return self::PREFIX_REQUEST_RATE . $hash;
    }

    /** Get burst rate cache key for a specific second. */
    public static function burstRate(string $hash, int $timestamp): string
    {
        return self::PREFIX_BURST_RATE . $hash . '_' . $timestamp;
    }

    /** Get table columns cache key. */
    public static function tableColumns(string $tableName): string
    {
        return self::PREFIX_TABLE_COLUMNS . '_' . md5($tableName);
    }
}