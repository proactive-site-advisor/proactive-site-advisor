<?php

namespace ProactiveSiteAdvisor\DataProviders;

use ProactiveSiteAdvisor\Abstracts\AbstractDataProvider;
use ProactiveSiteAdvisor\Models\DailyStats;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyStatsDataProvider
 *
 * @package ProactiveSiteAdvisor\DataProviders
 * @version 1.0.0
 */
class DailyStatsDataProvider extends AbstractDataProvider
{
    /**
     * Get stats for the last N days.
     *
     * @param int $days Number of days to retrieve (1-90).
     *
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * Retrieve daily statistics for a given number of days prior to a specific date.
     *
     * @param string $today
     * @param int $days
     *
     * @return array
     */
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

    /**
     * Retrieve daily statistics for a specific date.
     *
     * @param string $date
     *
     * @return array
     */
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

    /**
     * Get number of days with collected data.
     *
     * @return int
     */
    public function getDaysWithData(): int
    {
        global $wpdb;

        $table = DailyStats::getTableName();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        return (int)$count;
    }
}
