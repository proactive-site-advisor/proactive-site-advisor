<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficManager
 *
 * Manages frontend traffic tracking services.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class TrafficManager
{
    /**
     * Register all traffic-related hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp', [$this, 'maybeCountPageview'], 20);
        add_action('template_redirect', [$this, 'maybeTrack404'], 1);
    }

    /**
     * Proxy pageview counting.
     *
     * @return void
     */
    public function maybeCountPageview(): void
    {
        (new TrafficCollector())->maybeCountPageview();
    }

    /**
     * Proxy 404 tracking.
     *
     * @return void
     */
    public function maybeTrack404(): void
    {
        (new NotFoundTracker())->maybeTrack404();
    }
}
