<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Abstracts\AbstractAdminPage;
use ProactiveSiteAdvisor\Admin\Notices\PromoNotice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard page for the plugin.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @since   1.0.0
 */
class DashboardPage extends AbstractAdminPage
{
    /** Holds DashboardData instance for data access. */
    protected DashboardData $dashboardData;

    /** Constructor. */
    protected function __construct()
    {
        parent::__construct();

        $this->dashboardData = new DashboardData();
    }

    /** Returns the path of the dashboard page header template. */
    protected function getPageHeaderTemplate(): string
    {
        return 'admin/pages/dashboard/header';
    }

    /** Returns the path of the main dashboard template. */
    protected function getTemplate(): string
    {
        return 'admin/pages/dashboard/content';
    }

    /** Context data passed to the header template. */
    protected function getPageHeaderContext(): array
    {
        return [
            'statusLine' => $this->dashboardData->getStatusLine(),
        ];
    }

    /** Context data passed to the main dashboard template. */
    protected function getBodyContext(): array
    {
        $showPromoNotice = PromoNotice::shouldShowPromoNotice();

        return [
            'status'          => $this->dashboardData->getStatus(),
            'stats'           => $this->dashboardData->getStatsCards(),
            'latestAlerts'    => $this->dashboardData->getLatestAlerts(),
            'history'         => $this->dashboardData->getHistory(),
            'showPromoNotice' => false,
        ];
    }
}