<?php

namespace ProactiveSiteAdvisor\Services\Insights\Maintenance;

use ProactiveSiteAdvisor\Models\Alert;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AlertRetention
 *
 * Handles retention policy for stored alerts.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class AlertRetention
{
    /**
     * Deletes alerts older than the specified date.
     *
     * @param string $date
     *
     * @return void
     */
    public function purgeOlderThan(string $date): void
    {
        Alert::purgeOlderThan($date);
    }
}