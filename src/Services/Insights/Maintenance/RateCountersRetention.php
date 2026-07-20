<?php

namespace ProactiveSiteAdvisor\Services\Insights\Maintenance;

use ProactiveSiteAdvisor\Models\RateCounter;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles retention policy for rate counters.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Maintenance
 * @since   1.1.0
 */
class RateCountersRetention
{
    /** Purge all expired rate counter rows. */
    public function purgeExpired(): void
    {
        RateCounter::purgeExpired();
    }
}