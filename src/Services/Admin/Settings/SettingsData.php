<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\PluginSettings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides admin settings data.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsData
{
    /** Gets all plugin settings. */
    public function getSettings(): array
    {
        return OptionUtils::getAllOptions();
    }

    /** Gets settings sections. */
    public function getSections(): array
    {
        $sections = [
            PluginSettings::SECTION_ALERTS           => [
                'template' => 'admin/pages/settings/sections/alerts',
                'title'    => __('Alerts', 'proactive-site-advisor'),
                'icon'     => PrefixConfig::css('icon--bell'),
            ],
            PluginSettings::SECTION_THRESHOLDS => [
                'template' => 'admin/pages/settings/sections/thresholds',
                'title'    => __('Thresholds', 'proactive-site-advisor'),
                'icon'     => PrefixConfig::css('icon--slider'),
            ],
        ];

        /**
         * Filters plugin settings sections.
         *
         * @param array $sections Settings sections.
         * @since 1.0.0
         */
        return apply_filters(
            'proactive_site_advisor_settings_sections',
            $sections
        );
    }
}