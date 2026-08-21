<?php

namespace ProactiveSiteAdvisor\Services\Notifications;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages notification hooks and filters.
 *
 * @package ProactiveSiteAdvisor\Services\Notifications
 * @since   1.0.0
 */
class NotificationManager
{
    /** Registers hooks and filters. */
    public function register(): void
    {
        // Hook into daily insights after alerts are generated
        add_action('proactive_site_advisor_after_daily_insights', [$this, 'sendDigest']);
    }

    /** Sends the daily digest for the given date. */
    public function sendDigest(string $date): void
    {
        (new NotificationEngine())->send($date);
    }
}