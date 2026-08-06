<?php

namespace ProactiveSiteAdvisor\Lifecycle;

use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\PluginOptions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin activation logic.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Multisite network query requires direct access.
 *
 * @package ProactiveSiteAdvisor\Lifecycle
 * @since   1.0.0
 */
class ActivationHandler
{
    /** Registered table schema classes. */
    private static array $tableSchemas = [
        CoreTables::class,
    ];

    /** Register the activation hook. */
    public static function register(): void
    {
        register_activation_hook(PROACTIVE_SITE_ADVISOR_PLUGIN_FILE, [self::class, 'activate']);
    }

    /** Run activation logic. */
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

        /**
         * Fires after the plugin has been activated.
         *
         * @param bool $networkWide
         * @since  1.0.0
         */
        do_action('proactive_site_advisor_activated', $networkWide);
    }

    /** Run activation for a single site. */
    private static function singleActivate(): void
    {
        self::setDefaultOptions();
        self::createTables();
        self::flushRewriteRules();
        self::setVersion();
    }

    /** Run activation for all sites in a network. */
    private static function networkActivate(): void
    {
        global $wpdb;

        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            self::singleActivate();
            restore_current_blog();
        }
    }

    /** Set the plugin version in the database. */
    private static function setVersion(): void
    {
        OptionUtils::setMeta(PluginMeta::VERSION, PROACTIVE_SITE_ADVISOR_VERSION);
    }

    /** Create database tables. */
    private static function createTables(): void
    {
        foreach (self::$tableSchemas as $schemaClass) {
            if (class_exists($schemaClass) && method_exists($schemaClass, 'createTables')) {
                $schemaClass::createTables();
            }
        }
    }

    /** Set default plugin options. */
    private static function setDefaultOptions(): void
    {
        $optionName = PluginOptions::OPTION_NAME;

        if (get_option($optionName) === false) {
            add_option($optionName, OptionUtils::getDefaults());
        }
    }

    /** Flush rewrite rules. */
    private static function flushRewriteRules(): void
    {
        add_action('shutdown', static function () {
            flush_rewrite_rules();
        });
    }
}