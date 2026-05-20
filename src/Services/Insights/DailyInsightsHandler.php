<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\Services\Insights\Maintenance\AlertRetention;
use ProactiveSiteAdvisor\Services\Insights\Maintenance\DailyStatsRetention;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyInsightsHandler
 *
 * Coordinates post‑processing tasks after daily stats are flushed.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class DailyInsightsHandler
{
    /**
     * Executes daily insight tasks for a specific date.
     *
     * @param string $date
     *
     * @return void
     */
    public function handle(string $date): void
    {
        $alertEngine = new AlertEngine();
        $alertEngine->generateForDay($date);

        $now          = DateTimeUtils::current();
        $sevenDaysAgo = $now->modify('-7 days')->format('Y-m-d');

        $alertRetention = new AlertRetention();
        $alertRetention->purgeOlderThan($sevenDaysAgo);

        $dailyStatsRetention = new DailyStatsRetention();
        $dailyStatsRetention->purgeOlderThan($sevenDaysAgo);
    }
}