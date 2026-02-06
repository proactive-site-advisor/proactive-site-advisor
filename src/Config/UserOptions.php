<?php

namespace SiteAlerts\Config;

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Class UserOptions
 *
 * Centralizes all user-specific option keys (user_meta keys).
 */
final class UserOptions
{
    /**
     * Stores the ID of the most recent alert the user has seen.
     * Used to determine which alerts are "new" for the menu badge count.
     *
     * @var string
     */
    public const LAST_SEEN_ALERT_ID = 'last_seen_alert_id';
}