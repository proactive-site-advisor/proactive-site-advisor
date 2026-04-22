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
     * Selected admin theme for the user.
     */
    public const ADMIN_THEME = 'admin_theme';

    /**
     * List of dismissed notice IDs.
     */
    public const DISMISSED_NOTICES = 'dismissed_notices';

    /**
     * ID of the last alert viewed by the user.
     * Used to calculate unread alert counts.
     */
    public const LAST_SEEN_ALERT_ID = 'last_seen_alert_id';

    /**
     * Timestamp until the promo notice remains dismissed.
     */
    public const PROMO_NOTICE_DISMISSED_UNTIL = 'promo_notice_dismissed_until';
}