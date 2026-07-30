<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper for browser detection based on User-Agent string.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers
 * @since   1.0.0
 */
class BrowserHelper
{
    /** Version threshold for modern Chrome-family browsers. */
    private const MODERN_CHROME_VERSION_THRESHOLD = 90;

    /** Version threshold for modern Firefox browsers. */
    private const MODERN_FIREFOX_VERSION_THRESHOLD = 90;

    /** Checks for modern Chromium browsers (Chrome, Edge, Opera). */
    public static function isModernChromeFamily(string $ua): bool
    {
        if (!preg_match('/(Chrome|Edg|OPR)\/(\d+)/i', $ua, $match)) {
            return false;
        }

        return (int)$match[2] >= self::MODERN_CHROME_VERSION_THRESHOLD;
    }

    /** Determines if User-Agent is Firefox 90 or higher. */
    public static function isModernFirefox(string $ua): bool
    {
        if (!preg_match('/Firefox\/(\d+)/i', $ua, $match)) {
            return false;
        }

        return (int)$match[1] >= self::MODERN_FIREFOX_VERSION_THRESHOLD;
    }

    /** Detects Safari-like browsers (including Firefox on iOS). */
    public static function isSafariLike(string $ua): bool
    {
        if (stripos($ua, 'FxiOS') !== false) {
            return true;
        }

        return (
            stripos($ua, 'Safari') !== false &&
            stripos($ua, 'Chrome') === false &&
            stripos($ua, 'CriOS') === false &&
            stripos($ua, 'Edg') === false
        );
    }
}