<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard\Data;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;
use ProactiveSiteAdvisor\Services\Admin\Dashboard\DashboardProcessor;
use ProactiveSiteAdvisor\Utils\DisplayUtils;
use ProactiveSiteAdvisor\Utils\PluginStatus;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prepares dashboard history data for display.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard\Data
 * @since   1.0.0
 */
class DashboardHistory
{
    /** Daily stats data provider instance. */
    private DailyStatsDataProvider $dailyStatsDataProvider;

    /** Dashboard processor instance. */
    private DashboardProcessor $dashboardProcessor;

    /** Constructor. */
    public function __construct()
    {
        $this->dailyStatsDataProvider = new DailyStatsDataProvider();
        $this->dashboardProcessor     = new DashboardProcessor();
    }

    /** Get 7-day history formatted for dashboard. */
    public function getHistory(int $daysWithData): array
    {
        $status = PluginStatus::getStatus($daysWithData);
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
                $average['errors_404'],
                $average['bot_pageviews'],
            ),
            'columns' => [
                ['key' => 'date', 'label' => __('Date', 'proactive-site-advisor')],
                ['key' => 'pageviews', 'label' => __('Pageviews', 'proactive-site-advisor')],
                ['key' => 'bot_pageviews', 'label' => __('Bot Pageviews', 'proactive-site-advisor')],
                ['key' => 'errors_404', 'label' => __('404 Errors', 'proactive-site-advisor')],
            ],
            'rows'    => $rows,
            'class'   => PrefixConfig::css('table--striped'),
        ];
    }
}