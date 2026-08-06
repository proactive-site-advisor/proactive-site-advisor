<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Cache\CacheGroups;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Database\VersionManager;
use ProactiveSiteAdvisor\Database\Migration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin update logic including database migrations.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Multisite network query requires direct access.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @since   1.0.0
 */
class UpdateHandler
{
    /** Register the update handler. */
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'checkForUpdates']);
    }

    /** Check for and run updates on admin init. */
    public static function checkForUpdates(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (is_multisite() && is_network_admin()) {
            self::networkUpdate();
        } else {
            self::singleUpdate();
        }
    }

    /** Run update for a single site. */
    private static function singleUpdate(): void
    {
        if (VersionManager::needsUpdate()) {
            self::runDatabaseMigrations();

            self::flushRewriteRules();
            self::clearTransients();
        }

        if (PluginMigration::needsUpdate()) {
            self::runPluginMigrations();

            self::flushRewriteRules();
            self::clearTransients();
        }
    }

    /** Run update for all sites in a network. */
    private static function networkUpdate(): void
    {
        global $wpdb;

        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleUpdate();
            restore_current_blog();
        }
    }

    /** Run database migrations. */
    private static function runDatabaseMigrations(): void
    {
        Migration::up();

        VersionManager::saveVersion();

        /**
         * Fires after database migrations are run.
         *
         * @since 1.0.0
         */
        do_action('proactive_site_advisor_database_migrations_complete');
    }

    /** Run plugin-level migrations (settings, etc.). */
    private static function runPluginMigrations(): void
    {
        PluginMigration::up();

        PluginMigration::saveVersion();

        /**
         * Fires after plugin-level migrations have been applied.
         *
         * @since 1.0.0
         */
        do_action('proactive_site_advisor_plugin_updated');
    }

    /** Flush rewrite rules. */
    private static function flushRewriteRules(): void
    {
        flush_rewrite_rules();
    }

    /** Clear plugin transients. */
    private static function clearTransients(): void
    {
        $cache = CacheManager::instance();

        $cache->flushGroup(CacheGroups::QUERY);
        $cache->flushGroup(CacheGroups::FRAGMENT);
    }
}