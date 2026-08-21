<?php

namespace ProactiveSiteAdvisor\Services\Notifications\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines the contract for notification channels.
 *
 * @package ProactiveSiteAdvisor\Services\Notifications\Contracts
 * @since   1.0.0
 */
interface NotificationChannelInterface
{
    /** Checks if the channel is enabled based on settings. */
    public function isEnabled(array $settings): bool;

    /** Sends the notification. */
    public function send(array $alerts, string $date, array $settings): void;
}