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
            PluginSettings::SECTION_ALERTS           => [
                PluginSettings::ALERT_TRAFFIC_DROP  => 'bool',
                PluginSettings::ALERT_TRAFFIC_SPIKE => 'bool',
                PluginSettings::ALERT_404_SPIKE     => 'bool',
                PluginSettings::ALERT_BOT_SPIKE     => 'bool',
                PluginSettings::ALERT_BOT_DROP      => 'bool',
            ],
            PluginSettings::SECTION_THRESHOLDS => [
                PluginSettings::MIN_WEEKLY_AVG          => 'int',
                PluginSettings::MIN_PAGEVIEWS_FOR_ALERT => 'int',
                PluginSettings::TRAFFIC_SPIKE_PERCENT   => 'int',
                PluginSettings::TRAFFIC_DROP_PERCENT    => 'int',
                PluginSettings::ERROR_404_SPIKE_PERCENT => 'int',
                PluginSettings::BOT_SPIKE_PERCENT       => 'int',
                PluginSettings::BOT_DROP_PERCENT        => 'int',
            ],
        ];
    }
}