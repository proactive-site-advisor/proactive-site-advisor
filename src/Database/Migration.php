<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Migration
 *
 * Handles database schema migrations.
 *
 * @package ProactiveSiteAdvisor\Database
 * @version 1.0.0
 */
class Migration
{
    /**
     * Run all pending migrations.
     *
     * @return void
     */
    public static function up(): void
    {
        $migrations = [
            '1.0.1' => function () {
                self::migrateTo101();
            },
        ];

        foreach ($migrations as $version => $callback) {
            $result = DatabaseManager::migrate($version, $callback);

            if (!$result) {
                return;
            }
        }
    }

    /**
     * Migration to version 1.0.3
     *
     * - Drop title column from alerts table.
     *
     * @return void
     */
    private static function migrateTo101(): void
    {
        if (DatabaseManager::columnExists('alerts', 'title')) {
            DatabaseManager::dropColumn('alerts', 'title');
        }
    }
}