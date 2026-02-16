<?php

namespace ProactiveSiteAdvisor\Lifecycle;

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Config\PluginOptions;

/**
 * Class UninstallHandler
 *
 * Handles plugin uninstallation logic for complete cleanup.
 * This class provides methods that can be called from uninstall.php.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @version 1.0.0
 */
class UninstallHandler
{
    /**
     * Table schema classes used by the plugin.
     *
     * @var array
     */
    private static array $tableSchemas = [
        CoreTables::class,
    ];

    /**
     * Run uninstallation logic.
     *
     * @return void
     */
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

    /**
     * Run uninstall for a single site.
     *
     * @return void
     */
    private static function singleUninstall(): void
    {
        self::deleteOptions();
        self::deleteUserMeta();
        self::deleteTables();
        self::deleteTransients();
        self::clearCronHooks();
        self::deleteUploads();
    }

    /**
     * Run uninstall for all sites in a network.
     *
     * @return void
     */
    private static function networkUninstall(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Multisite network query requires direct access
        $blogIds = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleUninstall();
            restore_current_blog();
        }
    }

    /**
     * Delete all plugin options.
     *
     * @return void
     */
    public static function deleteOptions(): void
    {
        global $wpdb;

        // Delete main plugin option
        delete_option(PluginOptions::OPTION_NAME);

        // Delete any options with our prefix
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk cleanup on uninstall requires direct query
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                PluginOptions::META_PREFIX . '%'
            )
        );
    }

    /**
     * Delete all user meta created by the plugin.
     *
     * @return void
     */
    public static function deleteUserMeta(): void
    {
        delete_metadata('user', 0, PluginOptions::OPTION_NAME, '', true);
    }

    /**
     * Delete custom database tables.
     *
     * @return void
     */
    public static function deleteTables(): void
    {
        global $wpdb;

        $schemas        = self::$tableSchemas;
        $fullTableNames = [];

        foreach ($schemas as $schemaClass) {
            if (class_exists($schemaClass) && method_exists($schemaClass, 'getSchemas')) {
                $tables = $schemaClass::getSchemas();

                foreach ($tables as $table) {
                    $fullTableNames[] = $table->getFullName();
                }
            }
        }

        /**
         * Filter the list of tables to delete on uninstall.
         *
         * @param array $tables Array of full table names (with prefix).
         */
        $tablesToDelete = apply_filters('proactive_site_advisor_tables_to_delete', $fullTableNames);

        foreach ($tablesToDelete as $tableName) {
            $tableName = esc_sql($tableName);

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is sanitized above
            $wpdb->query("DROP TABLE IF EXISTS {$tableName}");
        }
    }

    /**
     * Delete all transients.
     *
     * @return void
     */
    public static function deleteTransients(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk cleanup on uninstall requires direct query
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_' . PluginOptions::META_PREFIX . '%',
                '_transient_timeout_' . PluginOptions::META_PREFIX . '%'
            )
        );
    }

    /**
     * Delete uploaded files.
     *
     * @return void
     */
    public static function deleteUploads(): void
    {
        $uploadDir        = wp_upload_dir();
        $pluginUploadsDir = $uploadDir['basedir'] . '/' . PrefixConfig::handle('logs');

        if (is_dir($pluginUploadsDir)) {
            self::deleteDirectory($pluginUploadsDir);
        }
    }

    /**
     * Clear all scheduled cron hooks.
     *
     * @return void
     */
    public static function clearCronHooks(): void
    {
        /**
         * Filter the list of cron hooks to clear on uninstall.
         *
         * @param array $hooks Array of hook names to clear.
         */
        $hooks = apply_filters('proactive_site_advisor_cron_hooks_to_clear', [
            'proactive_site_advisor_daily_cron',
        ]);

        foreach ($hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir Directory path.
     * @return bool
     */
    private static function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
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

        return $wp_filesystem->rmdir($dir);
    }
}
