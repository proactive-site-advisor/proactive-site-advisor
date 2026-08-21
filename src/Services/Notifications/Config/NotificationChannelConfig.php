<?php

namespace ProactiveSiteAdvisor\Services\Notifications\Config;

use ProactiveSiteAdvisor\Services\Notifications\Channels\EmailChannel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configures available notification channels.
 *
 * @package ProactiveSiteAdvisor\Services\Notifications\Config
 * @since   1.0.0
 */
class NotificationChannelConfig
{
    /** Returns the list of available notification channels. */
    public static function getChannels(): array
    {
        $channels = [
            EmailChannel::class,
        ];

        /**
         * Filters the available notification channels.
         *
         * @param string[] $channels List of channel class names.
         * @since 1.0.0
         */
        return apply_filters('proactive_site_advisor_notification_channels', $channels);
    }
}