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
        $icon = 'data:image/svg+xml;base64, PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIj4KICA8ZyBmaWxsPSJub25lIiBzdHJva2U9IiNmM2YxZjEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj4KICAgIDxwYXRoIGQ9Ik0zIDMgTDEwIDEgTDE3IDMgTDE3IDEwIEMxNyAxNCAxMCAxOSAxMCAxOSBDMTAgMTkgMyAxNCAzIDEwIEwzIDMgWiIgc3Ryb2tlLXdpZHRoPSIyIi8+CiAgICA8Y2lyY2xlIGN4PSIxMC41IiBjeT0iMTAuNSIgcj0iMyIgc3Ryb2tlLXdpZHRoPSIyIi8+CiAgICA8cGF0aCBkPSJNMTMgMTMgTDE3LjggMTcuOCIgc3Ryb2tlLXdpZHRoPSIyIi8+CiAgPC9nPgo8L3N2Zz4=';

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
            'icon'     => $icon,
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
