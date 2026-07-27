<?php

namespace ProactiveSiteAdvisor\DataProviders;

use ProactiveSiteAdvisor\Abstracts\AbstractDataProvider;
use ProactiveSiteAdvisor\Models\DailyStats;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides query helpers for retrieving daily statistics data from the database.
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

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from trusted internal method
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT stats_date, pageviews, errors_404, bot_pageviews, top_404_json, top_bots_json
                 FROM {$table}
                 ORDER BY stats_date DESC
                 LIMIT %d",
                $days
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

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

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from trusted internal method
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pageviews, errors_404, bot_pageviews
             FROM {$table}
             WHERE stats_date < %s
             ORDER BY stats_date DESC
             LIMIT %d",
                $today,
                $days
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

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

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from trusted internal method
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
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
}