<?php

namespace ProactiveSiteAdvisor\Models;

use ProactiveSiteAdvisor\Abstracts\AbstractModel;
use ProactiveSiteAdvisor\Database\DatabaseManager;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyStats
 *
 * Model for daily statistics including pageviews and 404 errors.
 * Stores one record per day with aggregated traffic data.
 *
 * @package ProactiveSiteAdvisor\Models
 * @version 1.0.0
 */
class DailyStats extends AbstractModel
{
    /**
     * Table name (without prefix)
     *
     * @var string
     */
    protected static string $table = 'daily_stats';

    /**
     * Fillable fields (allowed for mass assignment)
     *
     * @var array
     */
    protected static array $fillable = [
        'stats_date',
        'pageviews',
        'errors_404',
        'top_404_json',
        'bot_pageviews',
        'top_bots_json',
    ];

    /**
     * Attribute type casts
     *
     * @var array
     */
    protected static array $casts = [
        'pageviews'     => 'integer',
        'errors_404'    => 'integer',
        'top_404_json'  => 'json',
        'bot_pageviews' => 'integer',
        'top_bots_json' => 'json',
    ];


    /**
     * Ensure a row exists for the given date.
     *
     * @param string $dateYmd
     *
     * @return void
     */
    public static function ensureDayExists(string $dateYmd): void
    {
        $table  = static::getTableName();
        $nowRaw = DateTimeUtils::now();

        DatabaseManager::preparedQuery(
            "INSERT IGNORE INTO $table (stats_date, pageviews, errors_404, top_404_json, bot_pageviews, top_bots_json, created_at, updated_at)
             VALUES (%s, 0, 0, NULL, 0, NULL, %s, %s)",
            $dateYmd,
            $nowRaw,
            $nowRaw
        );
    }

    /**
     * Increment statistics and update top 404 paths for a specific date.
     *
     * @param string $dateYmd
     * @param int $pageviews
     * @param int $errors404
     * @param string|null $top404Json
     * @param int $botPageviews
     * @param string|null $topBotsJson
     *
     * @return void
     */
    public static function updateDay(string $dateYmd, int $pageviews, int $errors404, ?string $top404Json, int $botPageviews, ?string $topBotsJson): void
    {
        $table  = static::getTableName();
        $nowRaw = DateTimeUtils::now();

        DatabaseManager::preparedQuery(
            "UPDATE $table SET
                pageviews = pageviews + %d,
                errors_404 = errors_404 + %d,
                top_404_json = %s,
                bot_pageviews = bot_pageviews + %d,
                top_bots_json = %s,
                updated_at = %s
            WHERE stats_date = %s",
            max(0, $pageviews),
            max(0, $errors404),
            $top404Json,
            max(0, $botPageviews),
            $topBotsJson,
            $nowRaw,
            $dateYmd
        );
    }

    /**
     * Delete records older than the given date.
     *
     * @param string $dateYmd
     *
     * @return void
     */
    public static function purgeOlderThan(string $dateYmd): void
    {
        $table = static::getTableName();

        DatabaseManager::preparedQuery(
            "DELETE FROM $table WHERE stats_date < %s",
            $dateYmd
        );
    }

    /**
     * Delete the record for a specific date.
     *
     * @param string $dateYmd
     *
     * @return void
     */
    public static function deleteByDate(string $dateYmd): void
    {
        $table = static::getTableName();

        DatabaseManager::preparedQuery(
            "DELETE FROM $table WHERE stats_date = %s",
            $dateYmd
        );
    }
}