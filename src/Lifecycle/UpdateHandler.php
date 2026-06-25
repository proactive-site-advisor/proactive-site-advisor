<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Database\DatabaseManager;
use ProactiveSiteAdvisor\Database\Migration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class UpdateHandler
 *
 * Handles plugin update logic including database migrations
 * and version-specific upgrades.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @version 1.0.0
 */
class UpdateHandler
{
    /**
     * Register the update handler.
     *
     * @return void
     */
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'checkForUpdates']);
    }

    /**
     * Check for and run updates on admin init.
     *
     * @return void
     */
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

    /**
     * Run update for a single site.
     *
     * @return void
     */
    private static function singleUpdate(): void
    {
        if (DatabaseManager::needsUpdate()) {
            self::runDatabaseMigrations();
        }
    }

    /**
     * Run update for all sites in a network.
     *
     * @return void
     */
    private static function networkUpdate(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Multisite network query requires direct access
        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleUpdate();
            restore_current_blog();
        }
    }

    /**
     * Run database migrations.
     *
     * @return void
     */
    private static function runDatabaseMigrations(): void
    {
        Migration::up();

        DatabaseManager::saveVersion();

        /**
         * Fires after database migrations are run.
         */
        do_action('proactive_site_advisor_database_migrations_complete');
    }
}