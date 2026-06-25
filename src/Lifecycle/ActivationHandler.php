<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\PluginOptions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ActivationHandler
 *
 * Handles plugin activation logic including version tracking,
 * database table creation, and initial setup.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @version 1.0.3
 */
class ActivationHandler
{
    /**
     * Registered table schema classes
     *
     * @var array
     */
    private static array $tableSchemas = [
        CoreTables::class,
    ];

    /**
     * Register the activation hook.
     *
     * @return void
     */
    public static function register(): void
    {
        register_activation_hook(PROACTIVE_SITE_ADVISOR_PLUGIN_FILE, [self::class, 'activate']);
    }

    /**
     * Run activation logic.
     *
     * @param bool $networkWide Whether this is a network-wide activation.
     * @return void
     */
    public static function activate(bool $networkWide = false): void
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        if ($networkWide && is_multisite()) {
            self::networkActivate();
        } else {
            self::singleActivate();
        }
    }

    /**
     * Run activation for a single site.
     *
     * @return void
     */
    private static function singleActivate(): void
    {
        self::setDefaultOptions();
        self::createTables();
        self::flushRewriteRules();
        self::setVersion();
    }

    /**
     * Run activation for all sites in a network.
     *
     * @return void
     */
    private static function networkActivate(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Multisite network query requires direct access
        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleActivate();
            restore_current_blog();
        }
    }

    /**
     * Set the plugin version in the database.
     *
     * @return void
     */
    public static function setVersion(): void
    {
        OptionUtils::setMeta(PluginMeta::VERSION, PROACTIVE_SITE_ADVISOR_VERSION);
    }

    /**
     * Create database tables.
     *
     * @return void
     */
    public static function createTables(): void
    {
        foreach (self::$tableSchemas as $schemaClass) {
            if (class_exists($schemaClass) && method_exists($schemaClass, 'register')) {
                $schemaClass::register();
            }
        }
    }

    /**
     * Set default plugin options.
     *
     * @return void
     */
    public static function setDefaultOptions(): void
    {
        $optionName = PluginOptions::OPTION_NAME;

        if (get_option($optionName) === false) {
            add_option($optionName, OptionUtils::getDefaults());
        }
    }

    /**
     * Flush rewrite rules.
     *
     * @return void
     */
    public static function flushRewriteRules(): void
    {
        CacheManager::instance()->set(CacheKeys::flushRewriteRules(), true, 60);
    }
}
