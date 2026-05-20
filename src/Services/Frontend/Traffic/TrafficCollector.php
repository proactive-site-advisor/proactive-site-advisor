<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficCollector
 *
 * Collects frontend pageview counts using WordPress transients.
 * Runs only on legitimate frontend requests, skipping admin, REST, AJAX,
 * cron, feed, and preview requests.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class TrafficCollector
{
    /**
     * Transient TTL in seconds (10 days).
     * Long TTL ensures transient survives until daily cron processes it.
     */
    private const TRANSIENT_TTL = DAY_IN_SECONDS * 10;

    /**
     * Increment pageview count if this is a valid frontend request.
     *
     * @return void
     */
    public function maybeCountPageview(): void
    {
        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        $key = CacheKeys::pageviewsToday();
        CacheManager::instance()->increment($key, 1, self::TRANSIENT_TTL);
    }
}
