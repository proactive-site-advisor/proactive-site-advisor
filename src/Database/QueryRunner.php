<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Executes raw and prepared SQL queries, and provides error/insert ID info.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Database query helper requires direct query execution.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Query execution does not use database caching.
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- Raw SQL support requires trusted prepared queries from callers.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Raw query method is restricted to trusted internal SQL.
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

        return $wpdb->query($sql);
    }

    /** Execute a prepared SQL query. */
    public static function preparedQuery(string $sql, ...$args)
    {
        global $wpdb;

        $prepared = $wpdb->prepare($sql, ...$args);

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

        return (int)$wpdb->insert_id;
    }
}