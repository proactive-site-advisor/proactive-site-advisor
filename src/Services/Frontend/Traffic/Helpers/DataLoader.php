<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DataLoader
 *
 * Centralized data file loader with runtime caching.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers
 * @version 1.0.0
 */
class DataLoader
{
    /**
     * Runtime cache for loaded data.
     *
     * @var array
     */
    private static array $cache = [];

    /**
     * Load bot patterns from data file.
     *
     * @return string|null
     */
    public static function loadBotPatterns(): ?string
    {
        return self::loadFile('bot-patterns.php');
    }

    /**
     * Load browser patterns from data file.
     *
     * @return string|null
     */
    public static function loadBrowserPatterns(): ?string
    {
        return self::loadFile('browser-patterns.php');
    }

    /**
     * Load browser allowlist from data file.
     *
     * @return array
     */
    public static function loadBrowserAllowlist(): array
    {
        $data = self::loadFile('browser-allowlist.php');
        return is_array($data) ? $data : [];
    }

    /**
     * Load referrer spam list from data file.
     *
     * @return array
     */
    public static function loadReferrerSpamList(): array
    {
        $data = self::loadFile('referrer-spam.php');
        return is_array($data) ? $data : [];
    }

    /**
     * Generic file loader with caching.
     *
     * @param string $filename
     * @return mixed
     */
    private static function loadFile(string $filename)
    {
        if (isset(self::$cache[$filename])) {
            return self::$cache[$filename];
        }

        $filePath = PROACTIVE_SITE_ADVISOR_PATH . 'data/' . $filename;

        if (!file_exists($filePath)) {
            self::$cache[$filename] = null;
            return null;
        }

        self::$cache[$filename] = require $filePath;

        return self::$cache[$filename];
    }
}