<?php

namespace ProactiveSiteAdvisor\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines plugin configuration keys and settings constants.
 *
 * @package ProactiveSiteAdvisor\Config
 * @since 1.0.0
 */
class PluginSettings
{
    /** Alerts settings section. */
    public const SECTION_ALERTS = 'alerts';

    /** Sensitivity settings section. */
    public const SECTION_SENSITIVITY = 'sensitivity';

    /** Notifications settings section. */
    public const SECTION_NOTIFICATIONS = 'notifications';

    /*
    |--------------------------------------------------------------------------
    | Alerts Settings
    |--------------------------------------------------------------------------
    */

    /** Enable alert for human traffic drop. */
    public const ALERT_TRAFFIC_DROP = 'traffic_drop';

    /** Enable alert for human traffic spike. */
    public const ALERT_TRAFFIC_SPIKE = 'traffic_spike';

    /** Enable alert for 404 error surge. */
    public const ALERT_404_SPIKE = '404_spike';

    /** Enable alert for bot traffic changes (spike or drop). */
    public const ALERT_BOT_CHANGE = 'bot_change';

    /*
    |--------------------------------------------------------------------------
    | Sensitivity Settings
    |--------------------------------------------------------------------------
    */

    /** Sensitivity level (low / normal / high). */
    public const SENSITIVITY_LEVEL = 'level';

    /** Auto-tune minimum change thresholds based on site scale. */
    public const SENSITIVITY_AUTO_TUNE = 'auto_tune';

    /** Minimum meaningful traffic change (used when auto-tune is off). */
    public const TRAFFIC_MIN_ABS = 'traffic_min_abs';

    /** Minimum meaningful 404 change (used when auto-tune is off). */
    public const ERROR_404_MIN_ABS = 'error_404_min_abs';

    /** Minimum meaningful bot change (used when auto-tune is off). */
    public const BOT_MIN_ABS = 'bot_min_abs';

    /*
    |--------------------------------------------------------------------------
    | Notifications Settings
    |--------------------------------------------------------------------------
    */

    /** Enable or disable the daily digest email. */
    public const ENABLE_DAILY_DIGEST = 'enable_daily_digest';

    /** Email address to receive daily digest notifications. */
    public const DIGEST_RECIPIENT_EMAIL = 'digest_recipient_email';
}