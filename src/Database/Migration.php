<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Migration
 *
 * Handles database schema migrations.
 *
 * @package ProactiveSiteAdvisor\Database
 * @version 1.0.0
 */
class Migration
{
    /**
     * Run all pending migrations.
     *
     * @return void
     */
    public static function up(): void
    {
        $migrations = [
            '1.0.1' => function () {
                self::migrateTo101();
            },
            '1.0.2' => function () {
                self::migrateTo102();
            }
        ];

        foreach ($migrations as $version => $callback) {
            $result = DatabaseManager::migrate($version, $callback);

            if (!$result) {
                return;
            }
        }
    }

    /**
     * Migration to version 1.0.1
     *
     * - Drop title column from alerts table.
     *
     * @return void
     */
    private static function migrateTo101(): void
    {
        DatabaseManager::dropColumn('alerts', 'title');
    }

    /**
     * Migration to version 1.0.2
     *
     * - Add bot_pageviews column to daily_stats table.
     * - Add top_bots_json column to daily_stats table.
     *
     * @return void
     */
    private static function migrateTo102(): void
    {
        DatabaseManager::addColumn(
            'daily_stats',
            'bot_pageviews',
            "INT UNSIGNED NOT NULL DEFAULT 0",
            "AFTER top_404_json"
        );

        DatabaseManager::addColumn(
            'daily_stats',
            'top_bots_json',
            "longtext NULL DEFAULT NULL",
            "AFTER bot_pageviews"
        );
    }
}