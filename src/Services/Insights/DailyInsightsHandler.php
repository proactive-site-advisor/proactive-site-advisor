<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\Services\Insights\Maintenance\AlertRetention;
use ProactiveSiteAdvisor\Services\Insights\Maintenance\DailyFingerprintRetention;
use ProactiveSiteAdvisor\Services\Insights\Maintenance\DailyStatsRetention;
use ProactiveSiteAdvisor\Services\Insights\Maintenance\RateCountersRetention;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Coordinates post-processing tasks after daily stats are flushed.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @since   1.0.0
 */
class DailyInsightsHandler
{
    /** Executes daily insight tasks for a specific date. */
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

        $rateCountersRetention = new RateCountersRetention();
        $rateCountersRetention->purgeExpired();

        $dailyFingerprintRetention = new DailyFingerprintRetention();
        $dailyFingerprintRetention->purgeOlderThan($sevenDaysAgo);
    }
}