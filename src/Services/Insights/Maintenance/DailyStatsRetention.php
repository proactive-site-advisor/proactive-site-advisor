<?php

namespace ProactiveSiteAdvisor\Services\Insights\Maintenance;

use ProactiveSiteAdvisor\Models\DailyStats;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles retention policy for stored daily statistics.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Maintenance
 * @since   1.0.0
 */
class DailyStatsRetention
{
    /** Deletes daily statistics older than the specified date. */
    public function purgeOlderThan(string $date): void
    {
        DailyStats::purgeOlderThan($date);
    }
}