<?php

namespace ProactiveSiteAdvisor\DataProviders;

use ProactiveSiteAdvisor\Abstracts\AbstractDataProvider;
use ProactiveSiteAdvisor\Models\Alert;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AlertsDataProvider
 *
 * Provides query helpers for retrieving alert data from the database.
 *
 * @package ProactiveSiteAdvisor\DataProviders
 * @version 1.0.0
 */
class AlertsDataProvider extends AbstractDataProvider
{
    /**
     * Retrieve the latest alert rows from the database.
     *
     * @param int $limit Maximum number of rows to return.
     * @param int $days Number of days to look back.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestAlerts(int $limit = 7, int $days = 7): array
    {
        global $wpdb;

        $limit = max(1, min(20, $limit));

        $table = Alert::getTableName();
        $start = wp_date('Y-m-d', strtotime(sprintf('-%d days', $days)));

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from trusted internal method
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, alert_date, type, severity, title, meta_json, created_at
                 FROM {$table}
                 WHERE alert_date >= %s
                 ORDER BY alert_date DESC, id DESC
                 LIMIT %d",
                $start,
                $limit
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
     * Get the count of alerts grouped by severity for the last N days.
     *
     * @param int $days Number of days to look back.
     * @param int $lastSeenId Only count alerts with ID greater than this value.
     *
     * @return array{critical: int, warning: int, info: int}
     */
    public function getSeverityCounts(int $days = 7, int $lastSeenId = 0): array
    {
        global $wpdb;

        $table = Alert::getTableName();
        $start = wp_date('Y-m-d', strtotime(sprintf('-%d days', $days)));

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare(
            "
            SELECT
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) AS critical,
                SUM(CASE WHEN severity = 'warning' THEN 1 ELSE 0 END) AS warning,
                SUM(CASE WHEN severity = 'info' THEN 1 ELSE 0 END) AS info
            FROM {$table}
            WHERE alert_date >= %s
            AND id > %d
            ",
            $start,
            $lastSeenId
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $row = $wpdb->get_row($sql, ARRAY_A) ?? [];

        return [
            'critical' => (int)($row['critical'] ?? 0),
            'warning'  => (int)($row['warning'] ?? 0),
            'info'     => (int)($row['info'] ?? 0),
        ];
    }

    /**
     * Retrieve digest source rows for the last N days.
     *
     * @param int $days Number of days to look back.
     *
     * @return array<int, array{type: string, severity: string}>
     */
    public function getDigestRows(int $days = 7): array
    {
        global $wpdb;

        $table = Alert::getTableName();
        $start = wp_date('Y-m-d', strtotime(sprintf('-%d days', $days)));

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from trusted internal method
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT type, severity
                 FROM {$table}
                 WHERE alert_date >= %s",
                $start
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
     * Retrieve meta_json rows for 404 spike alerts in the last N days.
     *
     * @param int $days Number of days to look back.
     *
     * @return array<int, array{meta_json: string}>
     */
    public function get404SpikeRows(int $days = 7): array
    {
        global $wpdb;

        $table = Alert::getTableName();
        $start = wp_date('Y-m-d', strtotime(sprintf('-%d days', $days)));

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from trusted internal method
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_json
                 FROM {$table}
                 WHERE type = 'error_404_spike'
                 AND alert_date >= %s
                 AND meta_json IS NOT NULL",
                $start
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
     * Get the ID of the most recently created alert.
     *
     * @return int
     */
    public function getLatestAlertId(): int
    {
        global $wpdb;

        $table = Alert::getTableName();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return (int)$wpdb->get_var("SELECT MAX(id) FROM {$table}");
    }
}