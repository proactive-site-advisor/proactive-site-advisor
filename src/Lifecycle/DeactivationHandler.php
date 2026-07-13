<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Cache\CacheManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin deactivation logic.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @since   1.0.0
 */
class DeactivationHandler
{
    /** Register the deactivation hook. */
    public static function register(): void
    {
        register_deactivation_hook(PROACTIVE_SITE_ADVISOR_PLUGIN_FILE, [self::class, 'deactivate']);
    }

    /** Run deactivation logic. */
    public static function deactivate(bool $networkWide = false): void
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        if ($networkWide && is_multisite()) {
            self::networkDeactivate();
        } else {
            self::singleDeactivate();
        }

        /**
         * Fires after the plugin has been deactivated.
         *
         * @param bool $networkWide Whether this was a network-wide deactivation.
         * @since  1.0.0
         */
        do_action('proactive_site_advisor_deactivated', $networkWide);
    }

    /** Run deactivation for a single site. */
    private static function singleDeactivate(): void
    {
        self::clearScheduledEvents();
        self::clearTransients();
        self::flushRewriteRules();
    }

    /** Run deactivation for all sites in a network. */
    private static function networkDeactivate(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Multisite network query requires direct access
        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleDeactivate();
            restore_current_blog();
        }
    }

    /** Clear all scheduled cron events. */
    private static function clearScheduledEvents(): void
    {
        $defaultHooks = [
            'proactive_site_advisor_daily_cron',
        ];

        /**
         * Filters the list of cron hooks to clear on deactivation.
         *
         * @param array $hooks Array of hook names to clear.
         * @since  1.0.0
         */
        $hooks = apply_filters('proactive_site_advisor_cron_hooks_to_clear', $defaultHooks);

        foreach ($hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /** Clear plugin transients. */
    private static function clearTransients(): void
    {
        CacheManager::instance()->flush();
    }

    /** Flush rewrite rules. */
    private static function flushRewriteRules(): void
    {
        flush_rewrite_rules();
    }
}