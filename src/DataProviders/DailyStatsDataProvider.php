<?php

namespace ProactiveSiteAdvisor\DataProviders;

use ProactiveSiteAdvisor\Abstracts\AbstractDataProvider;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides query helpers for retrieving daily statistics data from the database.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct database queries are required for custom statistics retrieval.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Statistics require fresh database state.
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are trusted internal identifiers.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Database identifiers are generated from trusted internal methods.
 *
 * @package ProactiveSiteAdvisor\DataProviders
 * @since   1.0.0
 */
class DailyStatsDataProvider extends AbstractDataProvider
{
    /** Get stats for the last N days. */
    public function getLastDays(int $days = 7): array
    {
        global $wpdb;

        $days  = max(1, min(90, $days));
        $table = DailyStats::getTableName();
        $today = DateTimeUtils::current()->format(DateTimeUtils::FORMAT_DATE);

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT stats_date, pageviews, errors_404, bot_pageviews, top_404_json, top_bots_json
                 FROM {$table}
                 WHERE stats_date < %s
                 ORDER BY stats_date DESC
                 LIMIT %d",
                $today,
                $days
            ),
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        return $rows;
    }

    /** Retrieve daily statistics for a given number of days prior to a specific date. */
    public function getDailyStatsBeforeDate(string $today, int $days = 7): array
    {
        global $wpdb;

        $table = DailyStats::getTableName();

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pageviews, errors_404, bot_pageviews
             FROM {$table}
             WHERE stats_date <= %s
             ORDER BY stats_date DESC
             LIMIT %d",
                $today,
                $days
            ),
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        return $rows;
    }

    /** Retrieve daily statistics for a specific date. */
    public function getDailyStatsByDate(string $date): array
    {
        global $wpdb;

        $table = DailyStats::getTableName();

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT pageviews, errors_404, bot_pageviews, top_404_json, top_bots_json
             FROM {$table}
             WHERE stats_date = %s
             LIMIT 1",
                $date
            ),
            ARRAY_A
        );

        if (!is_array($row)) {
            return [];
        }

        return $row;
    }

    /** Get number of days with collected data. */
    public function getDaysWithData(): int
    {
        global $wpdb;

        $table = DailyStats::getTableName();
        $today = DateTimeUtils::current()->format(DateTimeUtils::FORMAT_DATE);

        return (int)$wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE stats_date < %s",
                $today
            )
        );
    }
}