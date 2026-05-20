<?php

namespace ProactiveSiteAdvisor\Services\Insights\Maintenance;

use ProactiveSiteAdvisor\Models\DailyStats;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyStatsRetention
 *
 * Handles retention policy for stored daily statistics.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class DailyStatsRetention
{
    /**
     * Deletes daily statistics older than the specified date.
     *
     * @param string $date
     *
     * @return void
     */
    public function purgeOlderThan(string $date): void
    {
        DailyStats::purgeOlderThan($date);
    }
}