<?php

namespace ProactiveSiteAdvisor\DataProviders;

use ProactiveSiteAdvisor\Abstracts\AbstractDataProvider;
use ProactiveSiteAdvisor\Models\Alert;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides query helpers for retrieving alert data from the database.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct database queries are required for custom data retrieval.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Query results require fresh database state.
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are trusted internal identifiers.
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- SQL statements are prepared before execution.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Database identifiers are generated from trusted internal methods.
 *
 * @package ProactiveSiteAdvisor\DataProviders
 * @since   1.0.0
 */
class AlertsDataProvider extends AbstractDataProvider
{
    /** Retrieve the latest alert rows from the database. */
    public function getLatestAlerts(int $limit = 7, int $days = 7): array
    {
        global $wpdb;

        $limit = max(1, min(20, $limit));

        $table = Alert::getTableName();
        $start = DateTimeUtils::current()->modify("-$days days")->format(DateTimeUtils::FORMAT_DATE);

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, alert_date, type, severity, meta_json, created_at
                 FROM {$table}
                 WHERE alert_date >= %s
                 ORDER BY alert_date DESC, id DESC
                 LIMIT %d",
                $start,
                $limit
            ),
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        return $rows;
    }

    /** Get the count of alerts grouped by severity for the last N days. */
    public function getSeverityCounts(int $days = 7, int $lastSeenId = 0): array
    {
        global $wpdb;

        $table = Alert::getTableName();
        $start = DateTimeUtils::current()->modify("-$days days")->format(DateTimeUtils::FORMAT_DATE);

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

        $row = $wpdb->get_row($sql, ARRAY_A);

        if (!is_array($row)) {
            $row = [];
        }

        return [
            'critical' => (int)($row['critical'] ?? 0),
            'warning'  => (int)($row['warning'] ?? 0),
            'info'     => (int)($row['info'] ?? 0),
        ];
    }

    /** Retrieve digest source rows for the last N days. */
    public function getDigestRows(int $days = 7): array
    {
        global $wpdb;

        $table = Alert::getTableName();
        $start = DateTimeUtils::current()->modify("-$days days")->format(DateTimeUtils::FORMAT_DATE);

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT type, severity
                 FROM {$table}
                 WHERE alert_date >= %s",
                $start
            ),
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        return $rows;
    }

    /** Get the ID of the most recently created alert. */
    public function getLatestAlertId(): int
    {
        global $wpdb;

        $table = Alert::getTableName();

        return (int)$wpdb->get_var("SELECT MAX(id) FROM $table");
    }

    /** Get repetition count of a specific alert type in previous days (excluding the given date). */
    public function getRepetitionCount(string $type, string $date, int $days = 3): int
    {
        global $wpdb;

        $table = Alert::getTableName();
        $start = DateTimeUtils::format(strtotime("-$days days", strtotime($date)), DateTimeUtils::FORMAT_DATE);

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table}
                WHERE type = %s
                AND alert_date < %s
                AND alert_date >= %s",
                $type,
                $date,
                $start
            )
        );

        return (int)$count;
    }

    /** Get distinct alert types other than the current type that occurred on the same date. */
    public function getConcurrentTypes(string $date, string $currentType): array
    {
        global $wpdb;

        $table = Alert::getTableName();

        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT type FROM {$table}
                WHERE alert_date = %s
                AND type != %s",
                $date,
                $currentType
            )
        );

        return array_values(array_filter($results));
    }
}