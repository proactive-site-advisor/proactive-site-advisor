<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;

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
class TrafficManager extends AbstractSingleton
{
    /**
     * Register all traffic tracking services.
     *
     * @return void
     */
    public function register(): void
    {
        TrafficCollector::getInstance()->register();
        NotFoundTracker::getInstance()->register();
    }
}
