<?php

namespace ProactiveSiteAdvisor\Services\Insights\Maintenance;

use ProactiveSiteAdvisor\Models\DailyFingerprint;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles retention policy for daily fingerprint records.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Maintenance
 * @since   1.0.0
 */
class DailyFingerprintRetention
{
    /** Purge fingerprint records older than the given date. */
    public function purgeOlderThan(string $date): void
    {
        DailyFingerprint::purgeOlderThan($date);
    }
}