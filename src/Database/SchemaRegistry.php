<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers and stores table schema metadata (TableSchema objects).
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class SchemaRegistry
{
    /** Registered table schemas. */
    private static array $tables = [];

    /** Register a table schema. */
    public static function registerTable(TableSchema $schema): void
    {
        self::$tables[$schema->getName()] = $schema;
    }

    /** Register multiple table schemas. */
    public static function registerTables(array $schemas): void
    {
        foreach ($schemas as $schema) {
            self::registerTable($schema);
        }
    }

    /** Get a registered table schema. */
    public static function getTable(string $name): ?TableSchema
    {
        return self::$tables[$name] ?? null;
    }

    /** Get all registered table schemas. */
    public static function getTables(): array
    {
        return self::$tables;
    }
}