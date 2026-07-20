<?php

namespace ProactiveSiteAdvisor\Models;

use ProactiveSiteAdvisor\Abstracts\AbstractModel;
use ProactiveSiteAdvisor\Database\QueryRunner;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tracks daily fingerprint pageviews for bot correction.
 *
 * @package ProactiveSiteAdvisor\Models
 * @since   1.1.0
 */
class DailyFingerprint extends AbstractModel
{
    /** {@inheritDoc} */
    protected static string $table = 'daily_fingerprint';

    /** {@inheritDoc} */
    protected static array $fillable = [
        'fingerprint',
        'record_date',
        'pageview_count',
        'is_bot',
    ];

    /** Increment human pageview count if fingerprint is not yet marked as bot. */
    public static function incrementHumanCount(string $fingerprint, string $dateYmd): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "INSERT INTO $table (fingerprint, record_date, pageview_count, is_bot)
             VALUES (%s, %s, 1, 0)
             ON DUPLICATE KEY UPDATE
                 pageview_count = IF(is_bot = 0, pageview_count + 1, pageview_count)",
            $fingerprint,
            $dateYmd
        );
    }

    /** Check if a fingerprint has been marked as bot for the given date. */
    public static function isBot(string $fingerprint, string $dateYmd): bool
    {
        global $wpdb;
        $table = static::getTableName();

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT is_bot FROM $table WHERE fingerprint = %s AND record_date = %s",
            $fingerprint,
            $dateYmd
        ), ARRAY_A);

        return !empty($row) && (int)$row['is_bot'] === 1;
    }

    /**
     * Mark fingerprint as bot for today and return the number of human pageviews
     * that had been recorded before this mark.
     */
    public static function markAsBot(string $fingerprint, string $dateYmd): int
    {
        global $wpdb;
        $table = static::getTableName();

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT pageview_count, is_bot FROM $table WHERE fingerprint = %s AND record_date = %s FOR UPDATE",
            $fingerprint,
            $dateYmd
        ), ARRAY_A);

        $currentPageviewCount = $row ? (int)$row['pageview_count'] : 0;
        $alreadyBot           = $row ? (int)$row['is_bot'] : 0;

        if ($alreadyBot) {
            return 0;
        }

        QueryRunner::preparedQuery(
            "INSERT INTO $table (fingerprint, record_date, pageview_count, is_bot)
             VALUES (%s, %s, %d, 1)
             ON DUPLICATE KEY UPDATE is_bot = 1",
            $fingerprint,
            $dateYmd,
            $currentPageviewCount
        );

        return $currentPageviewCount;
    }

    /** Delete records older than the given date. */
    public static function purgeOlderThan(string $dateYmd): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE record_date < %s",
            $dateYmd
        );
    }
}