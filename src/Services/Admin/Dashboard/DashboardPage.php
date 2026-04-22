<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Abstracts\AbstractAdminPage;
use ProactiveSiteAdvisor\Admin\Notices\PromoNotice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DashboardPage
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @version 1.0.0
 */
class DashboardPage extends AbstractAdminPage
{
    /**
     * Holds DashboardData instance for data access.
     *
     * @var DashboardData
     */
    protected DashboardData $dashboardData;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->dashboardData = new DashboardData();
    }

    /**
     * Returns the path of the dashboard page header template.
     *
     * @return string Template path
     */
    protected function getPageHeaderTemplate(): string
    {
        return 'admin/pages/dashboard/header';
    }

    /**
     * Returns the path of the main dashboard template.
     *
     * @return string Template path
     */
    protected function getTemplate(): string
    {
        return 'admin/pages/dashboard/content';
    }

    /**
     * Context data passed to the header template.
     *
     * @return array<string,mixed> Header context data
     */
    protected function getPageHeaderContext(): array
    {
        return [
            'statusLine' => $this->dashboardData->getStatusLine(),
        ];
    }

    /**
     * Context data passed to the main dashboard template.
     *
     * @return array<string,mixed> Template context data
     */
    protected function getBodyContext(): array
    {
        // Determine whether promo notice should be shown (disabled for initial release)
        $showPromoNotice = PromoNotice::shouldShowPromoNotice();

        return [
            'status'          => $this->dashboardData->getStatus(),
            'stats'           => $this->dashboardData->getStatsCards(),
            'latestAlerts'    => $this->dashboardData->getLatestAlerts(),
            'history'         => $this->dashboardData->getHistory(),
            // Promo notice intentionally disabled for the initial public release
            'showPromoNotice' => false,
        ];
    }
}