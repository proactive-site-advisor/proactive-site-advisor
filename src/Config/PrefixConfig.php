<?php

namespace ProactiveSiteAdvisor\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides helper methods to generate prefixed identifiers.
 *
 * @since      1.0.0
 * @package    ProactiveSiteAdvisor
 * @subpackage Config
 */
class PrefixConfig
{
    /** Used for unique identifiers. */
    public const SLUG = 'proactive-site-advisor';

    /** Used for CSS classes (kebab-case). */
    public const BASE = 'psa';

    /** PHP-safe prefix (snake_case). */
    public const PREFIX = 'proactive_site_advisor';

    /** Global JS config object name. */
    public const CONFIG_OBJECT = 'psaConfig';

    /** Prevent instantiation. */
    private function __construct(){}

    /** Build a BASE-prefixed string (kebab-case). */
    public static function base(string $name): string
    {
        return self::BASE . '-' . $name;
    }

    /** Build a PREFIX-prefixed string (snake_case). */
    public static function prefix(string $name): string
    {
        return self::PREFIX . '_' . $name;
    }

    /** Generate a prefixed CSS class. */
    public static function css(string $name): string
    {
        return self::base($name);
    }

    /** Generate a prefixed data attribute name. */
    public static function dataAttr(string $name): string
    {
        return 'data-' . self::base($name);
    }

    /** Generate a script/style handle. */
    public static function handle(string $name): string
    {
        return self::SLUG . '-' . $name;
    }

    /** Generate an Ajax action name. */
    public static function ajaxAction(string $name): string
    {
        return self::prefix($name);
    }

    /** Generate a nonce action name. */
    public static function nonce(string $name = 'nonce'): string
    {
        return self::prefix($name);
    }

    /** Generate a database table name (without $wpdb prefix). */
    public static function table(string $name): string
    {
        return self::prefix($name);
    }
}
