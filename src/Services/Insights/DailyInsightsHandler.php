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
    private const RETENTION_DAYS = 7;

    /** Executes daily insight tasks for a specific date. */
    public function handle(string $date): void
    {
        $this->generateAlerts($date);
        $this->fireDailyInsightsAction($date);
        $this->purgeExpiredData();
    }

    /** Generates alerts for the given date. */
    private function generateAlerts(string $date): void
    {
        $alertEngine = new AlertEngine();
        $alertEngine->generateForDay($date);
    }

    /** Fires the daily insights action after alerts have been generated. */
    private function fireDailyInsightsAction(string $date): void
    {
        /**
         * Fires after daily alerts have been generated.
         *
         * @param string $date The date for which alerts were generated (Y-m-d).
         * @since 1.0.0
         */
        do_action('proactive_site_advisor_after_daily_insights', $date);
    }

    /** Removes data that is outside the retention period. */
    private function purgeExpiredData(): void
    {
        $retentionDate = DateTimeUtils::current()
            ->modify('-' . self::RETENTION_DAYS . ' days')
            ->format('Y-m-d');

        (new AlertRetention())->purgeOlderThan($retentionDate);
        (new DailyStatsRetention())->purgeOlderThan($retentionDate);
        (new RateCountersRetention())->purgeExpired();
        (new DailyFingerprintRetention())->purgeOlderThan($retentionDate);
    }
}
