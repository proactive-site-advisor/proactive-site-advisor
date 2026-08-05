<?php

namespace ProactiveSiteAdvisor\Lifecycle;

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Config\PluginOptions;

/**
 * Handles plugin uninstallation logic for complete cleanup.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Uninstall cleanup requires direct database operations.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup does not require database caching.
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall cleanup requires dropping plugin tables.
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table identifiers cannot be prepared with placeholders.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names are validated and escaped before use.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @since   1.0.0
 */
class UninstallHandler
{
    /** Table schema classes used by the plugin. */
    private static array $tableSchemas = [
        CoreTables::class,
    ];

    /** Run uninstallation logic. */
    public static function uninstall(): void
    {
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }

        if (is_multisite()) {
            self::networkUninstall();
        } else {
            self::singleUninstall();
        }
    }

    /** Run uninstall for a single site. */
    private static function singleUninstall(): void
    {
        self::deleteOptions();
        self::deleteUserMeta();
        self::deleteTables();
        self::deleteTransients();
        self::clearCronHooks();
        self::deleteUploads();
        self::flushRewriteRules();

        /**
         * Fires after the plugin has been completely uninstalled.
         *
         * @since 1.0.0
         */
        do_action('proactive_site_advisor_uninstalled');
    }

    /** Run uninstall for all sites in a network. */
    private static function networkUninstall(): void
    {
        global $wpdb;

        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleUninstall();
            restore_current_blog();
        }
    }

    /** Delete all plugin options. */
    private static function deleteOptions(): void
    {
        global $wpdb;

        delete_option(PluginOptions::OPTION_NAME);

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                PluginOptions::META_PREFIX . '%'
            )
        );
    }

    /** Delete all user meta created by the plugin. */
    private static function deleteUserMeta(): void
    {
        delete_metadata('user', 0, PluginOptions::OPTION_NAME, '', true);
    }

    /** Delete custom database tables. */
    private static function deleteTables(): void
    {
        global $wpdb;

        $schemas        = self::$tableSchemas;
        $tablesToDelete = [];

        foreach ($schemas as $schemaClass) {
            if (class_exists($schemaClass) && method_exists($schemaClass, 'getSchemas')) {
                $tables = $schemaClass::getSchemas();

                foreach ($tables as $table) {
                    $tablesToDelete[] = $table->getFullName();
                }
            }
        }

        foreach ($tablesToDelete as $tableName) {
            $tableName = esc_sql($tableName);

            $wpdb->query("DROP TABLE IF EXISTS $tableName");
        }
    }

    /** Delete all transients. */
    private static function deleteTransients(): void
    {
        CacheManager::instance()->flush();
    }

    /** Delete uploaded files. */
    private static function deleteUploads(): void
    {
        $uploadDir        = wp_upload_dir();
        $pluginUploadsDir = $uploadDir['basedir'] . '/' . PrefixConfig::handle('logs');

        if (is_dir($pluginUploadsDir)) {
            self::deleteDirectory($pluginUploadsDir);
        }
    }

    /** Clear all scheduled cron hooks. */
    private static function clearCronHooks(): void
    {
        /**
         * Filters the list of cron hooks to clear on uninstall.
         *
         * @param array $hooks Array of hook names to clear.
         * @since  1.0.0
         */
        $hooks = apply_filters('proactive_site_advisor_cron_hooks_to_clear', [
            'proactive_site_advisor_daily_cron',
        ]);

        foreach ($hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /** Recursively delete a directory. */
    private static function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                self::deleteDirectory($path);
            } else {
                wp_delete_file($path);
            }
        }

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $wp_filesystem->rmdir($dir);
    }

    /** Flush rewrite rules. */
    private static function flushRewriteRules(): void
    {
        flush_rewrite_rules();
    }
}