<?php

namespace SiteAlerts\Services\Admin\Alerts;

use SiteAlerts\DataProviders\AlertsDataProvider;
use SiteAlerts\Utils\MenuUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AlertsManager
 *
 * Manages the main alerts admin page and menu registration.
 *
 * @package SiteAlerts\Services\Admin\Alerts
 * @version 1.0.0
 */
class AlertsManager
{
    /**
     * Register hooks and filters.
     *
     * @return void
     */
    public function register(): void
    {
        add_filter('site_alerts_menu_items', [$this, 'addMenuItem']);
        add_action('admin_init', [$this, 'handleLastSeenUpdate']);
    }

    /**
     * Update the last seen alert ID when visiting the alerts page.
     *
     * @return void
     */
    public function handleLastSeenUpdate(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['page']) && $_GET['page'] === MenuUtils::getSlug('site-alerts')) {
            AlertsDataProvider::getInstance()->updateLastSeenAlertId();
        }
    }

    /**
     * Add alerts menu items.
     *
     * @param array $items Existing menu items.
     * @return array Modified menu items.
     */
    public function addMenuItem(array $items): array
    {
        /**
         * Apply a filter to determine the position of the plugin menu item.
         *
         * @param float $position The default position of the menu item in the admin menu.
         *
         * @return float The possibly modified position after applying filters.
         */
        $position = apply_filters('site_alerts_plugins_menu_item_position', 65.0);

        $priority   = AlertsDataProvider::getInstance()->getPriorityCount();
        $count      = $priority['count'];
        $severity   = $priority['severity'];
        $badgeTitle = esc_html__('Site Alerts', 'site-alerts');

        if ($count > 0) {
            $colors = [
                'critical' => '#ff4c51', // $sa-danger
                'warning'  => '#ff9f43', // $sa-warning
                'info'     => '#00bad1', // $sa-info
            ];

            $color = $colors[$severity] ?? '#3b82f6';

            $badgeTitle .= sprintf(
                ' <span class="update-plugins count-%1$d" style="background-color: %2$s;"><span class="plugin-count">%1$d</span></span>',
                $count,
                esc_attr($color)
            );
        }

        $items[] = [
            'id'       => 'site-alerts',
            'title'    => $badgeTitle,
            'icon'     => 'dashicons-warning',
            'position' => $position,
            'callback' => AlertsPage::class,
        ];

        $items[] = [
            'id'       => 'site-alerts',
            'title'    => esc_html__('Alerts', 'site-alerts'),
            'parentId' => 'site-alerts',
            'callback' => AlertsPage::class,
        ];

        return $items;
    }
}