<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Builders\AlertDigestBuilder;
use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;
use ProactiveSiteAdvisor\Services\Admin\Dashboard\Data\DashboardAlerts;
use ProactiveSiteAdvisor\Services\Admin\Dashboard\Data\DashboardHistory;
use ProactiveSiteAdvisor\Services\Admin\Dashboard\Data\DashboardStatsCards;
use ProactiveSiteAdvisor\Services\Admin\Dashboard\Data\DashboardStatus;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides dashboard data and status information.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @since   1.0.0
 */
class DashboardData
{
    /** Alerts data provider instance. */
    private AlertsDataProvider $alertsDataProvider;

    /** Alert digest builder instance. */
    private AlertDigestBuilder $alertDigestBuilder;

    /** Dashboard status data preparer. */
    private DashboardStatus $dashboardStatus;

    /** Dashboard stats cards data preparer. */
    private DashboardStatsCards $dashboardStatsCards;

    /** Dashboard alerts data preparer. */
    private DashboardAlerts $dashboardAlerts;

    /** Dashboard history data preparer. */
    private DashboardHistory $dashboardHistory;

    /** Number of days with data. */
    private int $daysWithData;

    /** Constructor. */
    public function __construct()
    {
        $this->alertsDataProvider = new AlertsDataProvider();
        $this->alertDigestBuilder = new AlertDigestBuilder();

        $this->dashboardStatus     = new DashboardStatus();
        $this->dashboardStatsCards = new DashboardStatsCards();
        $this->dashboardAlerts     = new DashboardAlerts();
        $this->dashboardHistory    = new DashboardHistory();

        $this->daysWithData = (new DailyStatsDataProvider())->getDaysWithData();
    }

    /** Returns the dashboard top status line text. */
    public function getStatusLine(): string
    {
        return $this->dashboardStatus->getStatusLine($this->daysWithData);
    }

    /** Returns the colored dashboard status block (severity or plugin-status). */
    public function getStatus(): array
    {
        $stats = $this->alertDigestBuilder->build(
            $this->alertsDataProvider->getDigestRows()
        );

        return $this->dashboardStatus->getStatus($this->daysWithData, $stats);
    }

    /** Returns full card data for the dashboard. */
    public function getStatsCards(): array
    {
        $stats = $this->alertDigestBuilder->build(
            $this->alertsDataProvider->getDigestRows()
        );

        return $this->dashboardStatsCards->getCards($stats, $this->daysWithData);
    }

    /** Get latest alerts for dashboard. */
    public function getLatestAlerts(): array
    {
        return $this->dashboardAlerts->getAlerts($this->daysWithData);
    }

    /** Get 7-day history formatted for dashboard. */
    public function getHistory(): array
    {
        return $this->dashboardHistory->getHistory($this->daysWithData);
    }

    /** Returns the top severity summary (critical → warning → info). */
    public function getTopSeveritySummary(int $days = 7, int $lastSeenId = 0): array
    {
        $counts = $this->alertsDataProvider->getSeverityCounts($days, $lastSeenId);

        foreach (['critical', 'warning', 'info'] as $sev) {
            if (!empty($counts[$sev])) {
                return [
                    'severity' => $sev,
                    'count'    => $counts[$sev],
                ];
            }
        }

        return ['severity' => 'info', 'count' => 0];
    }
}