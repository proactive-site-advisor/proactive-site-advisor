<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Abstracts\AbstractAdminPage;
use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Displays the plugin settings page.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsPage extends AbstractAdminPage
{
    /** Gets the page header template. */
    protected function getPageHeaderTemplate(): string
    {
        return 'admin/pages/settings/header';
    }

    /** Gets the page template. */
    protected function getTemplate(): string
    {
        return 'admin/pages/settings/content';
    }

    /** Gets the page body context. */
    protected function getBodyContext(): array
    {
        $settingsData = new SettingsData();

        return [
            'settings'    => $settingsData->getSettings(),
            'sections'    => $settingsData->getSections(),
            'nonceAction' => PrefixConfig::nonce('settings'),
            'nonceName'   => PrefixConfig::nonce('settings_nonce'),
            'formAction'  => admin_url('admin-post.php'),
            'formName'    => PrefixConfig::BASE . '_save_settings',
        ];
    }
}