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

    /** Thresholds settings section. */
    public const SECTION_THRESHOLDS = 'thresholds';

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

    /** Enable alert for bot traffic spike. */
    public const ALERT_BOT_SPIKE = 'bot_spike';

    /** Enable alert for bot traffic drop. */
    public const ALERT_BOT_DROP = 'bot_drop';

    /*
    |--------------------------------------------------------------------------
    | Thresholds Settings
    |--------------------------------------------------------------------------
    */

    /** Minimum weekly average pageviews to enable alerts. */
    public const MIN_WEEKLY_AVG = 'min_weekly_avg';

    /** Minimum pageviews required today to trigger an alert. */
    public const MIN_PAGEVIEWS_FOR_ALERT = 'min_pageviews_for_alert';

    /** Percentage increase in human pageviews that triggers a spike alert. */
    public const TRAFFIC_SPIKE_PERCENT = 'traffic_spike_percent';

    /** Percentage decrease in human pageviews that triggers a drop alert. */
    public const TRAFFIC_DROP_PERCENT = 'traffic_drop_percent';

    /** Percentage increase in 404 errors that triggers an alert. */
    public const ERROR_404_SPIKE_PERCENT = '404_spike_percent';

    /** Percentage increase in bot pageviews that triggers a spike alert. */
    public const BOT_SPIKE_PERCENT = 'bot_spike_percent';

    /** Percentage decrease in bot pageviews that triggers a drop alert. */
    public const BOT_DROP_PERCENT = 'bot_drop_percent';

    /*
    |--------------------------------------------------------------------------
    | Notifications Settings
    |--------------------------------------------------------------------------
    */

    /** Enable or disable the daily digest email. */
    public const ENABLE_DAILY_DIGEST = 'enable_daily_digest';

    /** Email address to receive daily digest notifications. */
    public const DIGEST_RECIPIENT_EMAIL = 'digest_recipient_email';

    /** Include traffic alerts (spike/drop) in the digest. */
    public const DIGEST_INCLUDE_TRAFFIC = 'digest_include_traffic';

    /** Include 404 spike alerts in the digest. */
    public const DIGEST_INCLUDE_404 = 'digest_include_404';

    /** Include bot alerts (spike/drop) in the digest. */
    public const DIGEST_INCLUDE_BOT = 'digest_include_bot';
}