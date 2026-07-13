<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Executes raw and prepared SQL queries, and provides error/insert ID info.
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class QueryRunner
{
    /** Execute a raw SQL query. */
    public static function query(string $sql)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Raw query method, caller responsible for escaping
        return $wpdb->query($sql);
    }

    /** Execute a prepared SQL query. */
    public static function preparedQuery(string $sql, ...$args)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is a trusted internal query with placeholders, prepared below
        $prepared = $wpdb->prepare($sql, ...$args);

        // self::query expects a raw query; $prepared is already safe
        return self::query($prepared);
    }

    /** Get the last database error. */
    public static function getLastError(): string
    {
        global $wpdb;

        return $wpdb->last_error;
    }

    /** Get the last inserted ID. */
    public static function getLastInsertId(): int
    {
        global $wpdb;

        return $wpdb->insert_id;
    }
}