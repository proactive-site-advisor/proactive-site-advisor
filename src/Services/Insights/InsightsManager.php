<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\Services\Insights\Crons\DailyStatsFlusher;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class InsightsManager
 *
 * Entry point for registering Insights-related services within the plugin.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class InsightsManager
{
    /**
     * Registers hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_filter('proactive_site_advisor_daily_cron_services', [$this, 'registerDailyCronServices']);
    }

    /**
     * Registers daily cron services by merging them with the existing service list.
     *
     * @param array $services
     *
     * @return array
     */
    public function registerDailyCronServices(array $services): array
    {
        $dailyCronServices = [
            DailyStatsFlusher::class
        ];

        return array_merge($services, $dailyCronServices);
    }
}