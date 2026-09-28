<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Config\PluginSettings;

/**
 * Defines sanitization rules for all settings sections.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsSanitizationSchema
{
    /** Returns sanitization rules for all settings sections. */
    public static function getRules(): array
    {
        return [
            PluginSettings::SECTION_ALERTS        => [
                PluginSettings::ALERT_TRAFFIC_DROP  => 'bool',
                PluginSettings::ALERT_TRAFFIC_SPIKE => 'bool',
                PluginSettings::ALERT_404_SPIKE     => 'bool',
                PluginSettings::ALERT_BOT_CHANGE    => 'bool',
            ],
            PluginSettings::SECTION_SENSITIVITY   => [
                PluginSettings::SENSITIVITY_LEVEL     => 'string',
                PluginSettings::SENSITIVITY_AUTO_TUNE => 'bool',
                PluginSettings::TRAFFIC_MIN_ABS       => 'int',
                PluginSettings::ERROR_404_MIN_ABS     => 'int',
                PluginSettings::BOT_MIN_ABS           => 'int',
            ],
            PluginSettings::SECTION_NOTIFICATIONS => [
                PluginSettings::ENABLE_DAILY_DIGEST    => 'bool',
                PluginSettings::DIGEST_RECIPIENT_EMAIL => 'email',
            ],
        ];
    }
}