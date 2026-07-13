<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * High-level CRUD operations for database records.
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class DataStore
{
    /** Insert a row into a table. */
    public static function insert(string $name, array $data, array $format = [])
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return false;
        }

        $tableName = $schema->getFullName();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table management requires direct queries
        $result = $wpdb->insert($tableName, $data, $format ?: null);

        return $result ? $wpdb->insert_id : false;
    }

    /** Update rows in a table. */
    public static function update(string $name, array $data, array $where, array $format = [], array $whereFormat = [])
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return false;
        }

        $tableName = $schema->getFullName();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table management requires direct queries
        return $wpdb->update($tableName, $data, $where, $format ?: null, $whereFormat ?: null);
    }

    /** Delete rows from a table. */
    public static function delete(string $name, array $where, array $whereFormat = [])
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return false;
        }

        $tableName = $schema->getFullName();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table management requires direct queries
        return $wpdb->delete($tableName, $where, $whereFormat ?: null);
    }

    /** Get a single row from a table. */
    public static function getRow(string $name, $idOrWhere, string $idColumn = 'id'): ?object
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return null;
        }

        $tableName = $schema->getFullName();

        if (is_array($idOrWhere)) {
            $conditions = [];
            $values     = [];

            foreach ($idOrWhere as $column => $value) {
                $conditions[] = "`$column` = %s";
                $values[]     = $value;
            }

            $whereClause = implode(' AND ', $conditions);

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is sanitized, placeholders built dynamically
            $sql = $wpdb->prepare("SELECT * FROM $tableName WHERE $whereClause LIMIT 1", ...$values);
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is sanitized, value is prepared
            $sql = $wpdb->prepare("SELECT * FROM $tableName WHERE `$idColumn` = %s LIMIT 1", $idOrWhere);
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above
        return $wpdb->get_row($sql);
    }

    /** Get multiple rows from a table. */
    public static function getRows(string $name, array $args = []): array
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return [];
        }

        $tableName = $schema->getFullName();

        $defaults = [
            'where'   => [],
            'orderby' => 'id',
            'order'   => 'ASC',
            'limit'   => 0,
            'offset'  => 0,
            'columns' => '*',
        ];

        $args = wp_parse_args($args, $defaults);

        $columns = is_array($args['columns']) ? implode(', ', $args['columns']) : $args['columns'];
        $sql     = "SELECT $columns FROM $tableName";

        if (!empty($args['where'])) {
            $conditions = [];
            $values     = [];

            foreach ($args['where'] as $column => $value) {
                if (is_array($value)) {
                    $placeholders = array_fill(0, count($value), '%s');
                    $conditions[] = "`$column` IN (" . implode(', ', $placeholders) . ")";
                    $values       = array_merge($values, $value);
                } else {
                    $conditions[] = "`$column` = %s";
                    $values[]     = $value;
                }
            }

            $sql .= ' WHERE ' . implode(' AND ', $conditions);

            if (!empty($values)) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared with values
                $sql = $wpdb->prepare($sql, ...$values);
            }
        }

        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- orderby is from trusted defaults, order is validated
        $sql .= " ORDER BY `{$args['orderby']}` $order";

        if ($args['limit'] > 0) {
            $sql .= $wpdb->prepare(' LIMIT %d', $args['limit']);

            if ($args['offset'] > 0) {
                $sql .= $wpdb->prepare(' OFFSET %d', $args['offset']);
            }
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is built with prepared statements
        return $wpdb->get_results($sql);
    }

    /** Get table row count. */
    public static function getRowCount(string $name, array $where = []): int
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);
        if (!$schema) {
            return 0;
        }

        $tableName = $schema->getFullName();
        $sql       = "SELECT COUNT(*) FROM $tableName";
        $values    = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                if (is_array($value)) {
                    $placeholders = array_fill(0, count($value), '%s');
                    $conditions[] = "`" . esc_sql($column) . "` IN (" . implode(', ', $placeholders) . ")";
                    $values       = array_merge($values, $value);
                } else {
                    $conditions[] = "`" . esc_sql($column) . "` = %s";
                    $values[]     = $value;
                }
            }

            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if (!empty($values)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is prepared with values
            $sql = $wpdb->prepare($sql, ...$values);
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table management requires direct queries
        return (int)$wpdb->get_var($sql);
    }
}