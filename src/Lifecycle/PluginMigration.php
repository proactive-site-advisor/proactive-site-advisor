<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Config\PluginOptions;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin data migrations for new versions.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @since   1.0.0
 */
class PluginMigration
{
    /** Save the current plugin version to the database. */
    public static function saveVersion(): void
    {
        OptionUtils::setMeta(PluginMeta::VERSION, self::getVersion());
    }

    /** Get the current plugin version. */
    public static function getVersion(): string
    {
        return PROACTIVE_SITE_ADVISOR_VERSION;
    }

    /** Get the installed plugin version. */
    public static function getInstalledVersion(): string
    {
        return OptionUtils::getMeta(PluginMeta::VERSION, '1.0.0');
    }

    /** Check if plugin migrations are pending. */
    public static function needsUpdate(): bool
    {
        $storedVersion = OptionUtils::getMeta(PluginMeta::VERSION, '1.0.0');
        return version_compare($storedVersion, PROACTIVE_SITE_ADVISOR_VERSION, '<');
    }

    /** Run all pending plugin migrations. */
    public static function up(): void
    {
        $installedVersion = self::getInstalledVersion();
        $migrations       = [
            '1.1.0' => function () {
                self::migrateTo110();
            },
        ];

        foreach ($migrations as $version => $callback) {
            if (version_compare($installedVersion, $version, '>=')) {
                continue;
            }

            $callback();
        }
    }

    /** Merge default settings for thresholds with existing options. */
    private static function migrateTo110(): void
    {
        $optionName = PluginOptions::OPTION_NAME;
        $existing   = get_option($optionName, []);
        $defaults   = OptionUtils::getDefaults();

        if (!is_array($existing)) {
            $existing = [];
        }

        $merged = array_replace_recursive($defaults, $existing);

        OptionUtils::updateAll($merged);
    }
}