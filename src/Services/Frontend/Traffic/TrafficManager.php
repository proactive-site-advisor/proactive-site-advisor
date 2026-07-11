<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders\NotFoundRecorder;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders\PageviewRecorder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficManager
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class TrafficManager
{
    /**
     * Registers all traffic-related hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp', [$this, 'recordPageview'], 20);
        add_action('template_redirect', [$this, 'recordNotFound'], 1);
    }

    /**
     * Records pageview if applicable.
     *
     * @return void
     */
    public function recordPageview(): void
    {
        (new PageviewRecorder())->maybeRecord();
    }

    /**
     * Records 404 if applicable.
     *
     * @return void
     */
    public function recordNotFound(): void
    {
        (new NotFoundRecorder())->maybeRecord();
    }
}