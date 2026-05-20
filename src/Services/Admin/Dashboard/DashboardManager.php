<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Config\UserOptions;
use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\Utils\DisplayUtils;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Utils\PluginUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DashboardManager
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @version 1.0.0
 */
class DashboardManager
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
     * Update the last seen alert ID when visiting the dashboard page.
     *
     * @return void
     */
    public function handleLastSeenUpdate(): void
    {
        if (!PluginUtils::isPluginAdminRequest()) {
            return;
        }

        $latestId = (new AlertsDataProvider())->getLatestAlertId();

        if ($latestId <= 0) {
            return;
        }

        OptionUtils::setUserOption(UserOptions::LAST_SEEN_ALERT_ID, $latestId);
    }

    /**
     * Add dashboard menu items.
     *
     * @param array $items Existing menu items.
     *
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

        $dashboardData = new DashboardData();

        $lastSeenId = OptionUtils::getUserOption(UserOptions::LAST_SEEN_ALERT_ID, 0);

        $summary = $dashboardData->getTopSeveritySummary(7, $lastSeenId);

        $count    = $summary['count'];
        $severity = $summary['severity'];

        $badgeTitle = esc_html__('Site Advisor', 'proactive-site-advisor');

        $badge = DisplayUtils::renderSeverityBadge($count, $severity);

        if (!empty($badge)) {
            $badgeTitle .= ' ' . $badge;
        }

        $items[] = [
            'id'       => 'proactive-site-advisor',
            'title'    => $badgeTitle,
            'icon'     => 'dashicons-warning',
            'position' => $position,
            'callback' => DashboardPage::class,
        ];

        $items[] = [
            'id'       => 'proactive-site-advisor',
            'title'    => esc_html__('Dashboard', 'proactive-site-advisor'),
            'parentId' => 'proactive-site-advisor',
            'callback' => DashboardPage::class,
        ];

        return $items;
    }
}
