<?php

namespace ProactiveSiteAdvisor\Database\Schemas;

use ProactiveSiteAdvisor\Database\SchemaBuilder;
use ProactiveSiteAdvisor\Database\SchemaRegistry;
use ProactiveSiteAdvisor\Database\TableSchema;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines core database tables for the Proactive Site Advisor plugin.
 *
 * @package ProactiveSiteAdvisor\Database\Schemas
 * @since   1.0.0
 */
class CoreTables
{
    /** Get all table schemas for this provider. */
    public static function getSchemas(): array
    {
        return [
            self::getDailyStatsSchema(),
            self::getAlertsSchema(),
        ];
    }

    /** Register and create all core tables. */
    public static function createTables(): void
    {
        SchemaRegistry::registerTable(self::getDailyStatsSchema());
        SchemaRegistry::registerTable(self::getAlertsSchema());

        SchemaBuilder::createTables();
    }

    /** Daily statistics table schema. */
    public static function getDailyStatsSchema(): TableSchema
    {
        $schema = new TableSchema('daily_stats');
        $schema
            ->id()
            ->date('stats_date')
            ->int('pageviews')->default(0)
            ->int('errors_404')->default(0)
            ->json('top_404_json')->nullable()
            ->int('bot_pageviews')->default(0)
            ->json('top_bots_json')->nullable()
            ->timestamps()
            ->unique('daily_stats_unique', ['stats_date']);

        return $schema;
    }

    /** Alerts table schema. */
    public static function getAlertsSchema(): TableSchema
    {
        $schema = new TableSchema('alerts');
        $schema
            ->id()
            ->date('alert_date')
            ->varchar('type', 40)
            ->varchar('severity', 12)
            ->json('meta_json')
            ->timestamps()
            ->unique('alerts_unique', ['alert_date', 'type']);

        return $schema;
    }
}