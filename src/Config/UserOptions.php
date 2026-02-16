<?php

namespace ProactiveSiteAdvisor\Config;

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Class UserOptions
 *
 * Centralizes all user-specific option keys (user_meta keys).
 *
 * @package ProactiveSiteAdvisor\Config
 * @version 1.0.0
 */
final class UserOptions
{
    /**
     * ID of the last alert viewed by the user.
     * Used to calculate unread alert counts.
     */
    public const LAST_SEEN_ALERT_ID = 'last_seen_alert_id';

    /**
     * Selected admin theme for the user.
     */
    public const ADMIN_THEME = 'admin_theme';

    /**
     * Timestamp until the promo banner remains dismissed.
     */
    public const PROMO_BANNER_DISMISSED_UNTIL = 'promo_banner_dismissed_until';

    /**
     * List of dismissed notice IDs.
     */
    public const DISMISSED_NOTICES = 'dismissed_notices';
}