<?php

namespace ProactiveSiteAdvisor\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class PrefixConfig
 *
 * Provides helper methods to generate prefixed identifiers
 * used across the plugin.
 *
 * @package ProactiveSiteAdvisor\Config
 * @version 1.0.0
 */
final class PrefixConfig
{
    /**
     * Base slug used for CSS classes and handles (kebab-case).
     */
    public const BASE = 'proactive-site-advisor';

    /**
     * PHP-safe prefix (snake_case).
     */
    public const PREFIX = 'proactive_site_advisor';

    /**
     * Uppercase constant prefix.
     */
    public const CONSTANT_PREFIX = 'PROACTIVE_SITE_ADVISOR';

    /**
     * Global JS config object name.
     */
    public const CONFIG_OBJECT = 'proactiveSiteAdvisorConfig';

    /**
     * Prevent instantiation.
     */
    private function __construct(){}

    /**
     * Generate a prefixed CSS class.
     *
     * @param string $name Class suffix.
     * @return string
     */
    public static function css(string $name): string
    {
        return self::BASE . '-' . $name;
    }

    /**
     * Generate a prefixed data attribute name.
     *
     * @param string $name Attribute suffix.
     * @return string
     */
    public static function dataAttr(string $name): string
    {
        return 'data-' . self::BASE . '-' . $name;
    }

    /**
     * Generate a script/style handle.
     *
     * @param string $name Handle suffix.
     * @return string
     */
    public static function handle(string $name): string
    {
        return self::BASE . '-' . $name;
    }

    /**
     * Generate an Ajax action name.
     *
     * @param string $name Action suffix.
     * @return string
     */
    public static function ajaxAction(string $name): string
    {
        return self::PREFIX . '_' . $name;
    }

    /**
     * Generate a nonce action name.
     *
     * @param string $name Nonce suffix.
     * @return string
     */
    public static function nonce(string $name = 'nonce'): string
    {
        return self::PREFIX . '_' . $name;
    }

    /**
     * Generate a database table name (without $wpdb prefix).
     *
     * @param string $name Table suffix.
     * @return string
     */
    public static function table(string $name): string
    {
        return self::PREFIX . '_' . $name;
    }
}
