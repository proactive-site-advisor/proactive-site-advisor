<?php

namespace ProactiveSiteAdvisor\Models;

use ProactiveSiteAdvisor\Abstracts\AbstractModel;
use ProactiveSiteAdvisor\Database\QueryRunner;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Model for daily statistics including pageviews and 404 errors.
 *
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- SQL is prepared after trusted internal identifiers are validated.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct database operations are required for reading dynamic JSON data.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Statistics require fresh database state.
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table and column names are trusted internal identifiers.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Database identifiers are generated from internal allowlists.
 *
 * @package ProactiveSiteAdvisor\Models
 * @since   1.0.0
 */
class DailyStats extends AbstractModel
{
    /** {@inheritDoc} */
    protected static string $table = 'daily_stats';

    /** {@inheritDoc} */
    protected static array $fillable = [
        'stats_date',
        'pageviews',
        'errors_404',
        'top_404_json',
        'bot_pageviews',
        'top_bots_json',
    ];

    /** {@inheritDoc} */
    protected static array $casts = [
        'pageviews'     => 'integer',
        'errors_404'    => 'integer',
        'top_404_json'  => 'json',
        'bot_pageviews' => 'integer',
        'top_bots_json' => 'json',
    ];

    /** Ensure a row exists for the given date. */
    public static function ensureDayExists(string $dateYmd): void
    {
        $table  = static::getTableName();
        $nowRaw = DateTimeUtils::now();

        QueryRunner::preparedQuery(
            "INSERT IGNORE INTO $table (stats_date, pageviews, errors_404, top_404_json, bot_pageviews, top_bots_json, created_at, updated_at)
             VALUES (%s, 0, 0, NULL, 0, NULL, %s, %s)",
            $dateYmd,
            $nowRaw,
            $nowRaw
        );
    }

    /** Delete records older than the given date. */
    public static function purgeOlderThan(string $dateYmd): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE stats_date < %s",
            $dateYmd
        );
    }

    /** Delete the record for a specific date. */
    public static function deleteByDate(string $dateYmd): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE stats_date = %s",
            $dateYmd
        );
    }

    /** Atomically increment a numeric column for a specific date. */
    public static function incrementAtomic(string $dateYmd, string $column, int $amount): void
    {
        $table  = static::getTableName();
        $nowRaw = DateTimeUtils::now();

        $allowedColumns = ['pageviews', 'errors_404', 'bot_pageviews'];
        if (!in_array($column, $allowedColumns, true)) {
            return;
        }

        QueryRunner::preparedQuery(
            "INSERT INTO $table (stats_date, $column, created_at, updated_at) 
             VALUES (%s, %d, %s, %s) 
             ON DUPLICATE KEY UPDATE 
                $column = $column + %d,
                updated_at = %s",
            $dateYmd,
            max(0, $amount),
            $nowRaw,
            $nowRaw,
            max(0, $amount),
            $nowRaw
        );
    }

    /** Update a JSON column by merging new data and keeping only top N entries. */
    public static function updateJsonMap(string $dateYmd, string $jsonColumn, array $newData, int $maxEntries = 30): void
    {
        $table  = static::getTableName();
        $nowRaw = DateTimeUtils::now();

        $allowedColumns = ['top_404_json', 'top_bots_json'];
        if (!in_array($jsonColumn, $allowedColumns, true)) {
            return;
        }

        $currentData = static::getJsonMap($dateYmd, $jsonColumn);

        foreach ($newData as $key => $count) {
            if (!is_numeric($count)) {
                $count = 1;
            }

            $currentData[$key] = isset($currentData[$key])
                ? ((int)$currentData[$key] + (int)$count)
                : (int)$count;
        }

        if (count($currentData) > $maxEntries) {
            arsort($currentData);
            $currentData = array_slice($currentData, 0, $maxEntries, true);
        }

        $jsonValue = !empty($currentData) ? wp_json_encode($currentData) : null;

        QueryRunner::preparedQuery(
            "INSERT INTO $table (stats_date, $jsonColumn, created_at, updated_at) 
             VALUES (%s, %s, %s, %s) 
             ON DUPLICATE KEY UPDATE 
                $jsonColumn = %s,
                updated_at = %s",
            $dateYmd,
            $jsonValue,
            $nowRaw,
            $nowRaw,
            $jsonValue,
            $nowRaw
        );
    }

    /** Get JSON map data for a specific date and column. */
    private static function getJsonMap(string $dateYmd, string $jsonColumn): array
    {
        global $wpdb;

        $columns = [
            'top_404_json'  => '`top_404_json`',
            'top_bots_json' => '`top_bots_json`',
        ];

        if (!isset($columns[$jsonColumn])) {
            return [];
        }

        $columnSql = $columns[$jsonColumn];
        $table     = static::getTableName();

        $sql = "SELECT $columnSql FROM `$table` WHERE stats_date = %s LIMIT 1";

        $row = (array)$wpdb->get_row($wpdb->prepare($sql, $dateYmd), ARRAY_A);

        if (empty($row) || empty($row[$jsonColumn])) {
            return [];
        }

        $decoded = json_decode($row[$jsonColumn], true);

        return is_array($decoded) ? $decoded : [];
    }

    /** Transfer a count of pageviews from human to bot for correction. */
    public static function transferPageviewsToBot(string $dateYmd, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $table  = static::getTableName();
        $nowRaw = DateTimeUtils::now();

        static::ensureDayExists($dateYmd);

        QueryRunner::preparedQuery(
            "UPDATE $table SET
            pageviews = IF(pageviews >= %d, pageviews - %d, 0),
            bot_pageviews = bot_pageviews + %d,
            updated_at = %s
        WHERE stats_date = %s",
            $count,
            $count,
            $count,
            $nowRaw,
            $dateYmd
        );
    }
}