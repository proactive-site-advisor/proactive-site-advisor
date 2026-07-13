<?php

namespace ProactiveSiteAdvisor\Database;

use ProactiveSiteAdvisor\Utils\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Creates or updates database tables using WordPress dbDelta() function.
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class SchemaBuilder
{
    /** Create or update all registered tables. */
    public static function createTables(): array
    {
        $tables = SchemaRegistry::getTables();

        if (empty($tables)) {
            return [];
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $results = [];

        foreach ($tables as $schema) {
            $results[$schema->getName()] = self::createTable($schema);
        }

        VersionManager::saveVersion();

        return $results;
    }

    /** Create or update a single table. */
    public static function createTable(TableSchema $schema): array
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = $schema->toSql();

        Logger::debug('Creating database table', [
            'table' => $schema->getFullName(),
            'sql'   => $sql,
        ]);

        $result = dbDelta($sql);

        if (!empty($result)) {
            Logger::info('Database table created/updated', [
                'table'  => $schema->getFullName(),
                'result' => $result,
            ]);
        }

        return $result;
    }
}