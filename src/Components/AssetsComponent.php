<?php

namespace ProactiveSiteAdvisor\Components;

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Config\PrefixConfig;

/**
 * Handles registration, enqueueing, and localization of scripts and styles.
 *
 * @package ProactiveSiteAdvisor\Components
 * @since   1.0.0
 */
class AssetsComponent
{
    /** Assets URL */
    public static string $assetsUrl = PROACTIVE_SITE_ADVISOR_ASSETS;

    /** Enqueue a JavaScript file. */
    public static function enqueueScript(string $handle, ?string $src = null, array $deps = [], ?string $version = null, bool $inFooter = false): void
    {
        $handle  = self::getHandle($handle);
        $version = $version ?? PROACTIVE_SITE_ADVISOR_VERSION;

        wp_enqueue_script($handle, self::getSrc($src), $deps, $version, $inFooter);
    }

    /** Register a JavaScript file. */
    public static function registerScript(string $handle, string $src, array $deps = [], ?string $version = null, bool $inFooter = false): void
    {
        $handle  = self::getHandle($handle);
        $version = $version ?? PROACTIVE_SITE_ADVISOR_VERSION;

        wp_register_script($handle, self::getSrc($src), $deps, $version, $inFooter);
    }

    /** Enqueue a CSS file. */
    public static function enqueueStyle(string $handle, ?string $src = null, array $deps = [], string $media = 'all', ?string $version = null): void
    {
        $handle  = self::getHandle($handle);
        $version = $version ?? PROACTIVE_SITE_ADVISOR_VERSION;

        wp_enqueue_style($handle, self::getSrc($src), $deps, $version, $media);
    }

    /** Register a CSS file. */
    public static function registerStyle(string $handle, string $src, array $deps = [], string $media = 'all', ?string $version = null): void
    {
        $handle  = self::getHandle($handle);
        $version = $version ?? PROACTIVE_SITE_ADVISOR_VERSION;

        wp_register_style($handle, self::getSrc($src), $deps, $version, $media);
    }

    /** Localize a script with data. */
    public static function localizeScript(string $handle, string $objectName, array $data): void
    {
        $handle = self::getHandle($handle);
        wp_localize_script($handle, $objectName, $data);
    }

    /** Get the full URL for an asset. */
    public static function getSrc(?string $src): string
    {
        return $src ? rtrim(self::$assetsUrl, '/') . '/' . ltrim($src, '/') : '';
    }

    /** Generate a standardized handle name. */
    public static function getHandle(string $handle): string
    {
        return PrefixConfig::handle(sanitize_key($handle));
    }
}