<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BrowserValidator
 *
 * Validates browser names against an allowlist.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class BrowserValidator
{
    /**
     * Cached allowlist
     *
     * @var array|null
     */
    private static ?array $allowlist = null;

    /**
     * Check if browser name is valid (in allowlist)
     *
     * @param string $browser
     * @return bool
     */
    public static function isValidBrowserName(string $browser): bool
    {
        if ($browser === '') {
            return false;
        }

        $allowlist = self::getAllowlist();

        return in_array($browser, $allowlist, true);
    }

    /**
     * Parse user agent and extract browser name
     *
     * @param string $ua
     * @return array{browser: string, version: string, platform: string}
     */
    public static function parseUserAgent(string $ua): array
    {
        $defaults = [
            'browser'  => '',
            'version'  => '',
            'platform' => '',
        ];

        if ($ua === '') {
            return $defaults;
        }

        $browser  = '';
        $version  = '';
        $platform = '';

        if (preg_match('/(Windows|Macintosh|Android|iPhone|iPad|Linux|Chrome OS|CrOS)/i', $ua, $match)) {
            $platform = $match[1];
        }

        $patterns = [
            '/Edg\/([\d.]+)/i'            => 'Edge',
            '/OPR\/([\d.]+)/i'            => 'Opera',
            '/SamsungBrowser\/([\d.]+)/i' => 'SamsungBrowser',
            '/HeadlessChrome\/([\d.]+)/i' => 'HeadlessChrome',
            '/CriOS\/([\d.]+)/i'          => 'Chrome iOS',
            '/FxiOS\/([\d.]+)/i'          => 'Firefox iOS',
            '/Firefox\/([\d.]+)/i'        => 'Firefox',
            '/Chrome\/([\d.]+)/i'         => 'Chrome',
            '/Safari\/([\d.]+)/i'         => 'Safari',
        ];

        foreach ($patterns as $pattern => $name) {
            if (preg_match($pattern, $ua, $match)) {
                $browser = $name;
                $version = $match[1];
                break;
            }
        }

        return [
            'browser'  => $browser,
            'version'  => $version,
            'platform' => $platform,
        ];
    }

    /**
     * Get browser allowlist from data file
     *
     * @return array
     */
    private static function getAllowlist(): array
    {
        if (self::$allowlist !== null) {
            return self::$allowlist;
        }

        self::$allowlist = DataLoader::loadBrowserAllowlist();

        return self::$allowlist;
    }

    /**
     * Check if user agent has invalid browser name
     *
     * @param string $ua
     * @return bool
     */
    public static function hasInvalidBrowserName(string $ua): bool
    {
        if ($ua === '') {
            return true;
        }

        $parsed = self::parseUserAgent($ua);

        return !self::isValidBrowserName($parsed['browser']);
    }
}