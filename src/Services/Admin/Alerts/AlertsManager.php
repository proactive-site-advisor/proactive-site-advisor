<?php

namespace ProactiveSiteAdvisor\Services\Admin\Alerts;

use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\Utils\MenuUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AlertsManager
 *
 * Manages the main alerts admin page and menu registration.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Alerts
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
        add_filter('proactive_site_advisor_menu_items', [$this, 'addMenuItem']);
        add_action('admin_init', [$this, 'handleLastSeenUpdate']);
    }

    /**
     * Update the last seen alert ID when visiting the alerts page.
     *
     * @return void
     */
    public function handleLastSeenUpdate(): void
    {
        // Safe: Only updates current user's data; nonce is verified and user capability is checked in AjaxComponent::register().
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['page']) && $_GET['page'] === MenuUtils::getSlug('proactive-site-advisor')) {
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
        $position = apply_filters('proactive_site_advisor_plugins_menu_item_position', 66);

        $priority   = AlertsDataProvider::getInstance()->getPriorityCount();
        $count      = $priority['count'];
        $severity   = $priority['severity'];
        $badgeTitle = esc_html__('Site Advisor', 'proactive-site-advisor');

        if ($count > 0) {
            $colors = [
                'critical' => '#ff4c51',
                'warning'  => '#ff9f43',
                'info'     => '#00bad1',
            ];

            $color = $colors[$severity] ?? '#3b82f6';

            $badgeTitle .= sprintf(
                ' <span class="update-plugins count-%1$d" style="background-color: %2$s;"><span class="plugin-count">%1$d</span></span>',
                $count,
                esc_attr($color)
            );
        }

        $items[] = [
            'id'       => 'proactive-site-advisor',
            'title'    => $badgeTitle,
            'icon'     => 'dashicons-warning',
            'position' => $position,
            'callback' => AlertsPage::class,
        ];

        $items[] = [
            'id'       => 'proactive-site-advisor',
            'title'    => esc_html__('Alerts', 'proactive-site-advisor'),
            'parentId' => 'proactive-site-advisor',
            'callback' => AlertsPage::class,
        ];

        return $items;
    }
}
