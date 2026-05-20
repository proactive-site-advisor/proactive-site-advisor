<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\DisplayUtils;
use ProactiveSiteAdvisor\Utils\PluginStatus;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DashboardData
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @version 1.0.0
 */
class DashboardData
{
    /**
     * @var AlertsDataProvider
     */
    private AlertsDataProvider $alertsDataProvider;

    /**
     * @var DailyStatsDataProvider
     */
    private DailyStatsDataProvider $dailyStatsDataProvider;

    /**
     * @var DashboardProcessor
     */
    private DashboardProcessor $dashboardProcessor;

    private int $daysWithData;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->alertsDataProvider     = new AlertsDataProvider();
        $this->dailyStatsDataProvider = new DailyStatsDataProvider();
        $this->dashboardProcessor     = new DashboardProcessor();
        $this->daysWithData           = $this->dailyStatsDataProvider->getDaysWithData();
    }

    /**
     * Returns the meta subtitles for each card (i18n-safe)
     *
     * @return array
     */
    private function getCardMeta(): array
    {
        return [
            'critical_alerts' => [
                'zero'   => __('No critical issues detected', 'proactive-site-advisor'),
                'active' => __('Issues needing attention', 'proactive-site-advisor'),
            ],
            'traffic_alerts'  => [
                'zero'   => __('No unusual traffic detected', 'proactive-site-advisor'),
                'active' => __('Unusual traffic changes detected', 'proactive-site-advisor'),
            ],
            'error_alerts'    => [
                'zero'   => __('No 404 issues detected', 'proactive-site-advisor'),
                'active' => __('Pages returning 404 errors', 'proactive-site-advisor'),
            ],
            'total_alerts'    => [
                'zero'   => __('Last 7 days', 'proactive-site-advisor'),
                'active' => __('Total in last 7 days', 'proactive-site-advisor'),
            ],
        ];
    }

    /**
     * Returns the dashboard top status line text.
     *
     * @return string
     */
    public function getStatusLine(): string
    {
        $status = PluginStatus::getStatus($this->daysWithData);

        if ($status === PluginStatus::STATUS_FRESH) {
            return __('Not checked yet · Monitoring starting', 'proactive-site-advisor');
        }

        if ($status === PluginStatus::STATUS_LIMITED) {
            return __('Collecting data · Insights will appear soon', 'proactive-site-advisor');
        }

        if ($status === PluginStatus::STATUS_ISSUE) {
            return __('Last checked: over 24 hours ago · Check monitoring status', 'proactive-site-advisor');
        }

        return $this->getNormalStatusLine();
    }

    /**
     * Returns the status line for normal operating mode.
     *
     * @return string
     */
    private function getNormalStatusLine(): string
    {
        $lastRun = PluginStatus::getLastRunTimestamp();
        $now     = DateTimeUtils::timestamp();
        $timeAgo = human_time_diff($lastRun, $now);

        /* translators: %s: Time ago string */
        return sprintf(__('Last checked: %s ago · Range: last 7 days', 'proactive-site-advisor'), $timeAgo);
    }

    /**
     * Returns the colored dashboard status block (severity or plugin-status).
     *
     * @return array
     */
    public function getStatus(): array
    {
        $status = PluginStatus::getStatus($this->daysWithData);

        if ($status !== PluginStatus::STATUS_NORMAL) {
            return $this->getPluginStatus($status);
        }

        return $this->getSeverityStatus();
    }

    /**
     * Returns the block for plugin-level status (fresh, limited, issue).
     *
     * @param string $status
     *
     * @return array
     */
    private function getPluginStatus(string $status): array
    {
        switch ($status) {

            case PluginStatus::STATUS_FRESH:
                return [
                    'color' => 'info',
                    'title' => __('Getting started', 'proactive-site-advisor'),
                    'text'  => __("We're collecting baseline data. Your first insights will appear shortly.", 'proactive-site-advisor'),
                ];

            case PluginStatus::STATUS_LIMITED:
                $days       = $this->daysWithData;
                $percentage = min(100, round(($days / PluginStatus::BASELINE_DAYS) * 100));


                return [
                    'color'    => 'info',
                    'title'    => __('Building history', 'proactive-site-advisor'),
                    'text'     => sprintf(
                    /* translators: 1: Current day number, 2: Total number of baseline days required */
                        __('Collecting baseline data (Day %1$d of %2$d). Your insights will become more accurate as history grows.', 'proactive-site-advisor'),
                        $days,
                        PluginStatus::BASELINE_DAYS
                    ),
                    'progress' => $percentage,
                ];

            case PluginStatus::STATUS_ISSUE:
                return [
                    'color' => 'warning',
                    'title' => __('Check monitoring', 'proactive-site-advisor'),
                    'text'  => __('Last check was over 24 hours ago. Verify your site cron is running.', 'proactive-site-advisor'),
                ];
        }

        return [];
    }

    /**
     * Returns status block based on alert severity levels.
     *
     * @return array
     */
    private function getSeverityStatus(): array
    {
        $stats = $this->dashboardProcessor->buildDigest(
            $this->alertsDataProvider->getDigestRows()
        );

        $critical = $stats['critical_alerts'];
        $warning  = $stats['warning_alerts'];
        $info     = $stats['info_alerts'];

        if ($critical > 0) {
            return [
                'color' => 'error',
                'title' => __('Critical issues detected', 'proactive-site-advisor'),
                'text'  => sprintf(
                /* translators: %d: Number of critical issues detected in the last 7 days */
                    _n('%d critical issue detected in the last 7 days.',
                        '%d critical issues detected in the last 7 days.',
                        $critical,
                        'proactive-site-advisor'),
                    $critical
                ),
            ];
        }

        if ($warning > 0) {
            return [
                'color' => 'warning',
                'title' => __('Warnings detected', 'proactive-site-advisor'),
                'text'  => sprintf(
                /* translators: %d: Number of warnings detected in the last 7 days */
                    _n('%d warning detected in the last 7 days.',
                        '%d warnings detected in the last 7 days.',
                        $warning,
                        'proactive-site-advisor'),
                    $warning
                ),
            ];
        }

        if ($info > 0) {
            return [
                'color' => 'info',
                'title' => __('Notices detected', 'proactive-site-advisor'),
                'text'  => sprintf(
                /* translators: %d: Number of notices recorded in the last 7 days */
                    _n('%d notice recorded in the last 7 days.',
                        '%d notices recorded in the last 7 days.',
                        $info,
                        'proactive-site-advisor'),
                    $info
                ),
            ];
        }

        return [
            'color' => 'success',
            'title' => __('All clear', 'proactive-site-advisor'),
            'text'  => __("No unusual activity detected in the last 7 days. We'll keep monitoring and surface issues with recommended actions.", 'proactive-site-advisor'),
        ];
    }

    /**
     * Returns the top severity summary (critical → warning → info)
     *
     * @param int $days
     * @param int $lastSeenId
     *
     * @return array
     */
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

    /**
     * Returns full card data for the dashboard.
     *
     * @return array
     */
    public function getStatsCards(): array
    {
        $stats = $this->dashboardProcessor->buildDigest(
            $this->alertsDataProvider->getDigestRows()
        );

        $baseCards = $this->getCardDefinitions();
        $status    = PluginStatus::getStatus($this->daysWithData);
        $meta      = $this->getCardMeta();

        $finalCards = [];

        foreach ($baseCards as $key => $card) {

            $value = $stats[$key];

            $metaForKey = $meta[$key] ?? [
                'zero'   => __('Last 7 days', 'proactive-site-advisor'),
                'active' => __('Last 7 days', 'proactive-site-advisor'),
            ];

            $state = $this->getCardState($value, $metaForKey, $status);

            // Final assembled card (no array_merge, no references)
            $finalCards[$key] = [
                'icon'     => $card['icon'],
                'label'    => $card['label'],
                'color'    => $card['color'],
                'value'    => $state['value'],
                'subtitle' => $state['subtitle'],
            ];
        }

        return $finalCards;
    }

    /**
     * Builds final visual state for a single card.
     *
     * @param int $value
     * @param array $meta
     * @param string $status
     *
     * @return array
     */
    private function getCardState(int $value, array $meta, string $status): array
    {
        if ($status === PluginStatus::STATUS_FRESH) {
            return [
                'value'    => '—',
                'subtitle' => __('Collecting data', 'proactive-site-advisor'),
            ];
        }

        if ($status === PluginStatus::STATUS_LIMITED) {
            return [
                'value'    => '—',
                'subtitle' => __('Limited data available', 'proactive-site-advisor'),
            ];
        }

        if ($value === 0) {
            return [
                'value'    => '0',
                'subtitle' => $meta['zero'],
            ];
        }

        return [
            'value'    => (string)$value,
            'subtitle' => $meta['active'],
        ];
    }

    /**
     * Returns static card definitions (icons, colors, labels).
     *
     * @return array
     */
    private function getCardDefinitions(): array
    {
        return [
            'critical_alerts' => [
                'icon'  => PrefixConfig::css('icon--critical'),
                'label' => __('Critical Alerts', 'proactive-site-advisor'),
                'color' => 'error',
            ],
            'traffic_alerts'  => [
                'icon'  => PrefixConfig::css('icon--traffic'),
                'label' => __('Traffic Alerts', 'proactive-site-advisor'),
                'color' => 'primary',
            ],
            'error_alerts'    => [
                'icon'  => PrefixConfig::css('icon--error-404'),
                'label' => __('404 Alerts', 'proactive-site-advisor'),
                'color' => 'warning',
            ],
            'total_alerts'    => [
                'icon'  => PrefixConfig::css('icon--alert'),
                'label' => __('Total Alerts', 'proactive-site-advisor'),
                'color' => 'info',
            ],
        ];
    }

    /**
     * Get latest alerts for dashboard.
     *
     * @return array
     */
    public function getLatestAlerts(): array
    {
        $status = PluginStatus::getStatus($this->daysWithData);

        if ($status === PluginStatus::STATUS_FRESH) {

            return array(
                'hasData' => false,
                'title'   => __('Getting started', 'proactive-site-advisor'),
                'text'    => __("We're collecting baseline data for your site.", 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--info'),
                'color'   => 'info',
            );
        }

        if ($status === PluginStatus::STATUS_LIMITED) {

            return array(
                'hasData' => false,
                'title'   => __('Limited history', 'proactive-site-advisor'),
                'text'    => __('Not enough data yet to detect unusual activity.', 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--clock'),
                'color'   => 'warning',
            );
        }

        $rawAlerts = $this->alertsDataProvider->getLatestAlerts(20);

        if (empty($rawAlerts)) {

            return array(
                'hasData' => false,
                'title'   => __('All clear', 'proactive-site-advisor'),
                'text'    => __('No unusual activity detected in the last 7 days.', 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--check-circle'),
                'color'   => 'success',
            );
        }

        return array(
            'hasData' => true,
            'data'    => $this->dashboardProcessor->buildAlerts($rawAlerts),
        );
    }

    /**
     * Get 7‑day history formatted for dashboard.
     *
     * @return array
     */
    public function getHistory(): array
    {
        $status = PluginStatus::getStatus($this->daysWithData);
        $raw    = $this->dailyStatsDataProvider->getLastDays();

        $daysWithData = count($raw);

        if ($status === PluginStatus::STATUS_FRESH || $daysWithData === 0) {
            return [
                'hasData' => false,
                'title'   => __('Getting started', 'proactive-site-advisor'),
                'text'    => __("We're collecting baseline data. Your first history will appear shortly.", 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--info'),
                'color'   => 'info',
            ];
        }

        if ($daysWithData < 3) {
            return [
                'hasData' => false,
                'title'   => __('Building history', 'proactive-site-advisor'),
                'text'    => __('Building history — check back in a couple days for meaningful trends.', 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--traffic'),
                'color'   => 'warning',
            ];
        }

        $average = $this->dashboardProcessor->calculateHistoryAverage($raw);
        $rows    = $this->dashboardProcessor->formatHistoryRows($raw);

        return [
            'hasData' => true,
            'average' => DisplayUtils::renderHistoryAverage(
                $average['pageviews'],
                $average['errors_404']
            ),
            'columns' => [
                ['key' => 'date', 'label' => __('Date', 'proactive-site-advisor')],
                ['key' => 'pageviews', 'label' => __('Pageviews', 'proactive-site-advisor')],
                ['key' => 'errors_404', 'label' => __('404 Errors', 'proactive-site-advisor')],
            ],
            'rows'    => $rows,
            'class'   => PrefixConfig::css('table--striped'),
        ];
    }
}