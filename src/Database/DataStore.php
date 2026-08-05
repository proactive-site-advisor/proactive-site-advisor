<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * High-level CRUD operations for database records.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table management requires direct database queries.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Database operations require fresh state and are not cacheable.
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names and SQL identifiers are generated internally.
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- SQL statements are prepared before execution.
 * phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Dynamic placeholders are generated safely.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Database identifiers are generated from trusted internal schema definitions.
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

            $sql = $wpdb->prepare("SELECT * FROM $tableName WHERE $whereClause LIMIT 1", ...$values);
        } else {
            $sql = $wpdb->prepare("SELECT * FROM $tableName WHERE `$idColumn` = %s LIMIT 1", $idOrWhere);
        }

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
                $sql = $wpdb->prepare($sql, ...$values);
            }
        }

        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
        $sql   .= " ORDER BY `{$args['orderby']}` $order";

        if ($args['limit'] > 0) {
            $sql .= $wpdb->prepare(' LIMIT %d', $args['limit']);

            if ($args['offset'] > 0) {
                $sql .= $wpdb->prepare(' OFFSET %d', $args['offset']);
            }
        }

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
            $sql = $wpdb->prepare($sql, ...$values);
        }

        return (int)$wpdb->get_var($sql);
    }
}