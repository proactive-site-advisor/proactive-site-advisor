<?php

namespace ProactiveSiteAdvisor\Database;

use ProactiveSiteAdvisor\Utils\Logger;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\PluginMeta;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages database schema versioning and migration execution.
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class VersionManager
{
    /** Current database schema version. */
    private static string $version = '1.0.2';

    /** Set the database schema version. */
    public static function setVersion(string $version): void
    {
        self::$version = $version;
    }

    /** Save the current database version to the database. */
    public static function saveVersion(): void
    {
        OptionUtils::setMeta(PluginMeta::DB_VERSION, self::$version);
    }

    /** Get the current database schema version. */
    public static function getVersion(): string
    {
        return self::$version;
    }

    /** Get the installed database version. */
    public static function getInstalledVersion(): string
    {
        return OptionUtils::getMeta(PluginMeta::DB_VERSION, '0.0.0');
    }

    /** Check if database needs update. */
    public static function needsUpdate(): bool
    {
        return version_compare(self::getInstalledVersion(), self::$version, '<');
    }

    /** Run a database migration. */
    public static function migrate(string $fromVersion, callable $callback): bool
    {
        $installedVersion = self::getInstalledVersion();

        if (version_compare($installedVersion, $fromVersion, '>=')) {
            return true;
        }

        Logger::info('Running database migration', [
            'from_version'      => $fromVersion,
            'installed_version' => $installedVersion,
        ]);

        try {
            $callback();
            return true;
        } catch (Exception $e) {
            Logger::error('Database migration failed', [
                'from_version' => $fromVersion,
                'error'        => $e->getMessage(),
            ]);
            return false;
        }
    }
}