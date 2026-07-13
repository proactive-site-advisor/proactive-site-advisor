<?php

namespace ProactiveSiteAdvisor\Services\Insights\Maintenance;

use ProactiveSiteAdvisor\Models\Alert;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles retention policy for stored alerts.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Maintenance
 * @since   1.0.0
 */
class AlertRetention
{
    /** Deletes alerts older than the specified date. */
    public function purgeOlderThan(string $date): void
    {
        Alert::purgeOlderThan($date);
    }
}