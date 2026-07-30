<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages the plugin settings page.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsManager
{
    /** Registers the settings hooks. */
    public function register(): void
    {
        add_filter('proactive_site_advisor_menu_items', [$this, 'addMenuItem']);
        add_action('admin_post_psa_save_settings', [$this, 'handle']);
    }

    /** Adds the settings menu item. */
    public function addMenuItem(array $items): array
    {
        $items[] = [
            'id'       => PrefixConfig::handle('settings'),
            'title'    => esc_html__('Settings', 'proactive-site-advisor'),
            'parentId' => PrefixConfig::SLUG,
            'callback' => SettingsPage::class,
        ];

        return $items;
    }

    /** Handles settings save requests. */
    public function handle(): void
    {
        $settingsSaveHandler = new SettingsSaveHandler();
        $settingsSaveHandler->handle();
    }
}