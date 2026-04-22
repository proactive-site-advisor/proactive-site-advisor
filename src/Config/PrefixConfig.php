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
     * Used for unique identifiers.
     */
    public const SLUG = 'proactive-site-advisor';
    
    /**
     * Used for CSS classes (kebab-case).
     */
    public const BASE = 'psa';

    /**
     * PHP-safe prefix (snake_case).
     */
    public const PREFIX = 'proactive_site_advisor';

    /**
     * Global JS config object name.
     */
    public const CONFIG_OBJECT = 'psaConfig';

    /**
     * Prevent instantiation.
     */
    private function __construct(){}
    
    /**
     * Build a BASE-prefixed string (kebab-case).
     *
     * @param string $name Suffix to append.
     * @return string
     */
    public static function base(string $name): string
    {
        return self::BASE . '-' . $name;
    }
    
    /**
     * Build a PREFIX-prefixed string (snake_case).
     *
     * @param string $name Suffix to append.
     * @return string
     */
    public static function prefix(string $name): string
    {
        return self::PREFIX . '_' . $name;
    }

    /**
     * Generate a prefixed CSS class.
     *
     * @param string $name Class suffix.
     * @return string
     */
    public static function css(string $name): string
    {
        return self::base($name);
    }

    /**
     * Generate a prefixed data attribute name.
     *
     * @param string $name Attribute suffix.
     * @return string
     */
    public static function dataAttr(string $name): string
    {
        return 'data-' . self::base($name);
    }

    /**
     * Generate a script/style handle.
     *
     * @param string $name Handle suffix.
     * @return string
     */
    public static function handle(string $name): string
    {
        return self::SLUG . '-' . $name;
    }

    /**
     * Generate an Ajax action name.
     *
     * @param string $name Action suffix.
     * @return string
     */
    public static function ajaxAction(string $name): string
    {
        return self::prefix($name);
    }

    /**
     * Generate a nonce action name.
     *
     * @param string $name Nonce suffix.
     * @return string
     */
    public static function nonce(string $name = 'nonce'): string
    {
        return self::prefix($name);
    }

    /**
     * Generate a database table name (without $wpdb prefix).
     *
     * @param string $name Table suffix.
     * @return string
     */
    public static function table(string $name): string
    {
        return self::prefix($name);
    }
}
