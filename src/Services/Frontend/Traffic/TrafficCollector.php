<?php

namespace SiteAlerts\Services\Frontend\Traffic;

use SiteAlerts\Abstracts\AbstractSingleton;
use SiteAlerts\Cache\CacheManager;
use SiteAlerts\Utils\CacheKeys;

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
 * @package SiteAlerts\Services\Frontend\Traffic
 * @version 1.0.0
 */
class TrafficCollector extends AbstractSingleton
{
    /**
     * Transient TTL in seconds (10 days).
     * Long TTL ensures transient survives until daily cron processes it.
     */
    private const TRANSIENT_TTL = DAY_IN_SECONDS * 10;

    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Run late enough so WP query is ready, but before output
        add_action('wp', [$this, 'maybeCountPageview'], 20);
    }

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
        CacheManager::getInstance()->increment($key, 1, self::TRANSIENT_TTL);
    }
}
