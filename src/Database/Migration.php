<?php

namespace ProactiveSiteAdvisor\Database;

use ProactiveSiteAdvisor\Database\Schemas\CoreTables;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles database schema migrations.
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class Migration
{
    /** Run all pending migrations. */
    public static function up(): void
    {
        $migrations = [
            '1.0.1' => function () {
                self::migrateTo101();
            },
            '1.0.2' => function () {
                self::migrateTo102();
            },
            '1.0.3' => function () {
                self::migrateTo103();
            },
        ];

        foreach ($migrations as $version => $callback) {
            $result = VersionManager::migrate($version, $callback);

            if (!$result) {
                return;
            }
        }
    }

    /** Migration to version 1.0.1 - Drop title column from alerts table. */
    private static function migrateTo101(): void
    {
        TableMaintenance::dropColumn('alerts', 'title');
    }

    /** Migration to version 1.0.2 - Add bot_pageviews and top_bots_json columns to daily_stats table. */
    private static function migrateTo102(): void
    {
        TableMaintenance::addColumn(
            'daily_stats',
            'bot_pageviews',
            "INT UNSIGNED NOT NULL DEFAULT 0",
            "AFTER top_404_json"
        );

        TableMaintenance::addColumn(
            'daily_stats',
            'top_bots_json',
            "longtext NULL DEFAULT NULL",
            "AFTER bot_pageviews"
        );
    }

    /** Migration to version 1.0.3 - Create rate_counters table for atomic burst detection. */
    private static function migrateTo103(): void
    {
        SchemaRegistry::registerTable(CoreTables::getRateCountersSchema());
        SchemaRegistry::registerTable(CoreTables::getDailyFingerprintSchema());
        SchemaBuilder::createTables();
    }
}