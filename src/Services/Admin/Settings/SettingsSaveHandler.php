<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Admin\Notices\AdminNotices;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\MenuUtils;
use ProactiveSiteAdvisor\Utils\Request;
use ProactiveSiteAdvisor\Utils\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles settings save requests.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsSaveHandler
{
    /** Processes settings save requests. */
    public function handle(): void
    {
        if (Request::str('action', '', 'post') !== PrefixConfig::BASE . '_save_settings') {
            wp_safe_redirect(MenuUtils::getUrl('settings'));
            exit;
        }

        if (!Security::hasCapability()) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'proactive-site-advisor'));
        }

        if (Request::method() !== 'post') {
            wp_safe_redirect(MenuUtils::getUrl('settings'));
            exit;
        }

        $settings = Request::arr('settings', [], 'post');

        if (!$settings) {
            wp_safe_redirect(MenuUtils::getUrl('settings'));
            exit;
        }

        $saver = new SettingsSaver();
        $saver->save($settings);

        AdminNotices::success(
            __('Your settings have been saved and are now active.', 'proactive-site-advisor')
        )
            ->flash()
            ->setTitle(
                __('Settings Updated', 'proactive-site-advisor')
            );

        wp_safe_redirect(MenuUtils::getUrl('settings'));
        exit;
    }
}