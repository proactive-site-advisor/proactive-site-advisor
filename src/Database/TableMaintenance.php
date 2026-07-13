<?php

namespace ProactiveSiteAdvisor\Database;

use ProactiveSiteAdvisor\Utils\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles DDL operations (table structure management) including:
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class TableMaintenance
{
    /** Drop a table. */
    public static function dropTable(string $name): bool
    {
        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return false;
        }

        $tableName = $schema->getFullName();

        Logger::warning('Dropping database table', ['table' => $tableName]);

        $sql = "DROP TABLE IF EXISTS `$tableName`";

        $result = QueryRunner::query($sql);

        if ($result === false) {
            Logger::error('Failed to drop table', [
                'table' => $tableName,
                'error' => QueryRunner::getLastError(),
            ]);
            return false;
        }

        return true;
    }

    /** Drop all registered tables. */
    public static function dropAllTables(): void
    {
        foreach (SchemaRegistry::getTables() as $schema) {
            self::dropTable($schema->getName());
        }
    }

    /** Truncate a table. */
    public static function truncateTable(string $name): bool
    {
        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return false;
        }

        $tableName = $schema->getFullName();

        Logger::warning('Truncating database table', ['table' => $tableName]);

        $sql = "TRUNCATE TABLE `$tableName`";

        $result = QueryRunner::query($sql);

        if ($result === false) {
            Logger::error('Failed to truncate table', [
                'table' => $tableName,
                'error' => QueryRunner::getLastError(),
            ]);
            return false;
        }

        return true;
    }

    /** Check if a table exists. */
    public static function tableExists(string $name): bool
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return false;
        }

        $tableName = $schema->getFullName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table management requires direct queries
        return $wpdb->get_var("SHOW TABLES LIKE '$tableName'") === $tableName;
    }

    /** Get table columns. */
    public static function getTableColumns(string $name): array
    {
        global $wpdb;

        $schema = SchemaRegistry::getTable($name);

        if (!$schema) {
            return [];
        }

        $tableName = $schema->getFullName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table management requires direct queries
        $columns = $wpdb->get_results("DESCRIBE $tableName", ARRAY_A);

        return $columns ?: [];
    }

    /** Check if a column exists in a table. */
    public static function columnExists(string $tableName, string $columnName): bool
    {
        $columns = self::getTableColumns($tableName);

        foreach ($columns as $column) {
            if ($column['Field'] === $columnName) {
                return true;
            }
        }

        return false;
    }

    /** Drop a column from a table. */
    public static function dropColumn(string $tableName, string $columnName): bool
    {
        $schema = SchemaRegistry::getTable($tableName);

        if (!$schema) {
            Logger::error('Table not found for dropping column', [
                'table'  => $tableName,
                'column' => $columnName,
            ]);
            return false;
        }

        $fullTableName = $schema->getFullName();

        if (!self::columnExists($tableName, $columnName)) {
            Logger::warning('Column does not exist, skipping drop', [
                'table'  => $fullTableName,
                'column' => $columnName,
            ]);
            return true;
        }

        Logger::info('Dropping column from table', [
            'table'  => $fullTableName,
            'column' => $columnName,
        ]);

        $sql = "ALTER TABLE `$fullTableName` DROP COLUMN `$columnName`";

        $result = QueryRunner::query($sql);

        if ($result === false) {
            Logger::error('Failed to drop column', [
                'table'  => $fullTableName,
                'column' => $columnName,
                'error'  => QueryRunner::getLastError(),
            ]);
            return false;
        }

        Logger::info('Column dropped successfully', [
            'table'  => $fullTableName,
            'column' => $columnName,
        ]);

        return true;
    }

    /** Add a column to a table. */
    public static function addColumn(string $tableName, string $columnName, string $columnDefinition, string $position = ''): bool
    {
        $schema = SchemaRegistry::getTable($tableName);

        if (!$schema) {
            Logger::error('Table not found for adding column', [
                'table'  => $tableName,
                'column' => $columnName,
            ]);
            return false;
        }

        $fullTableName = $schema->getFullName();

        if (self::columnExists($tableName, $columnName)) {
            Logger::warning('Column already exists, skipping add', [
                'table'  => $fullTableName,
                'column' => $columnName,
            ]);
            return true;
        }

        $sql = "ALTER TABLE `$fullTableName` ADD COLUMN `$columnName` $columnDefinition";
        if ($position !== '') {
            $sql .= ' ' . $position;
        }

        Logger::info('Adding column to table', [
            'table'  => $fullTableName,
            'column' => $columnName,
        ]);

        $result = QueryRunner::query($sql);

        if ($result === false) {
            Logger::error('Failed to add column', [
                'table'  => $fullTableName,
                'column' => $columnName,
                'error'  => QueryRunner::getLastError(),
            ]);
            return false;
        }

        Logger::info('Column added successfully', [
            'table'  => $fullTableName,
            'column' => $columnName,
        ]);

        return true;
    }
}