<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Config\PluginOptions;
use ProactiveSiteAdvisor\Config\PluginSettings;
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
            '1.2.0' => function () {
                self::migrateTo120();
            },
            '1.2.6' => function () {
                self::migrateTo126();
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
            $existing = $defaults;
        }

        $merged = array_replace_recursive($defaults, $existing);

        OptionUtils::updateAll($merged);
    }

    /** Add notification settings to existing options. */
    private static function migrateTo120(): void
    {
        $optionName = PluginOptions::OPTION_NAME;
        $existing   = get_option($optionName, []);

        if (!is_array($existing)) {
            $existing = OptionUtils::getDefaults();
        }

        if (!isset($existing[PluginSettings::SECTION_NOTIFICATIONS])) {
            $existing[PluginSettings::SECTION_NOTIFICATIONS] = [
                PluginSettings::ENABLE_DAILY_DIGEST    => 1,
                PluginSettings::DIGEST_RECIPIENT_EMAIL => get_option('admin_email'),
                'digest_include_traffic'               => 1,
                'digest_include_404'                   => 1,
                'digest_include_bot'                   => 1,
            ];

            OptionUtils::updateAll($existing);
        }
    }

    /** Migrate settings structure to 1.2.6: replace thresholds with sensitivity, merge bot alerts. */
    private static function migrateTo126(): void
    {
        $optionName = PluginOptions::OPTION_NAME;
        $existing   = get_option($optionName, []);

        if (!is_array($existing)) {
            $existing = OptionUtils::getDefaults();
        }

        $alerts = $existing[PluginSettings::SECTION_ALERTS] ?? [];

        $botSpike = !empty($alerts['bot_spike']);
        $botDrop  = !empty($alerts['bot_drop']);

        $alerts[PluginSettings::ALERT_BOT_CHANGE] = ($botSpike || $botDrop) ? 1 : 0;

        unset($alerts['bot_spike'], $alerts['bot_drop']);

        $existing[PluginSettings::SECTION_ALERTS] = $alerts;

        unset($existing['thresholds']);

        if (!isset($existing[PluginSettings::SECTION_SENSITIVITY])) {
            $existing[PluginSettings::SECTION_SENSITIVITY] = [
                PluginSettings::SENSITIVITY_LEVEL     => 'normal',
                PluginSettings::SENSITIVITY_AUTO_TUNE => 1,
                PluginSettings::TRAFFIC_MIN_ABS       => 10,
                PluginSettings::ERROR_404_MIN_ABS     => 3,
                PluginSettings::BOT_MIN_ABS           => 10,
            ];
        }
        
        $notifications = $existing[PluginSettings::SECTION_NOTIFICATIONS] ?? [];

        unset(
            $notifications['digest_include_traffic'],
            $notifications['digest_include_404'],
            $notifications['digest_include_bot']
        );

        $existing[PluginSettings::SECTION_NOTIFICATIONS] = $notifications;

        OptionUtils::updateAll($existing);
    }
}