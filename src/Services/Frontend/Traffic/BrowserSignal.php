<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BrowserSignal
 *
 * Validates whether the current request resembles a real browser navigation.
 *
 * This class intentionally does NOT detect bots.
 * Its responsibility is limited to validating browser-like request signals.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @since 1.0.0
 */
class BrowserSignal
{
    /**
     * Determine whether the current request looks like
     * a browser-generated page navigation.
     *
     * @return bool
     */
    public static function isBrowser(): bool
    {
        if (self::hasPurposeHeader()) {
            return false;
        }

        if (!self::hasHtmlAcceptHeader()) {
            return false;
        }

        if (!self::hasBrowserUserAgent()) {
            return false;
        }

        if (!self::hasNavigationFetchMode()) {
            return false;
        }

        if (!self::hasDocumentDestination()) {
            return false;
        }

        if (self::getHeaderScore() < 3) {
            return false;
        }

        return true;
    }

    /**
     * Verify HTML document negotiation.
     *
     * Browsers navigating to pages almost always request HTML.
     *
     * @return bool
     */
    private static function hasHtmlAcceptHeader(): bool
    {
        $accept = sanitize_text_field(
            wp_unslash($_SERVER['HTTP_ACCEPT'] ?? '')
        );

        if ($accept === '') {
            return false;
        }

        return stripos($accept, 'text/html') !== false;
    }

    /**
     * Verify browser navigation mode.
     *
     * @return bool
     */
    private static function hasNavigationFetchMode(): bool
    {
        if (!isset($_SERVER['HTTP_SEC_FETCH_MODE'])) {
            return false;
        }

        $mode = sanitize_text_field(
            wp_unslash($_SERVER['HTTP_SEC_FETCH_MODE'])
        );

        return in_array(
            $mode,
            ['navigate', 'nested-navigate'],
            true
        );
    }

    /**
     * Verify browser User-Agent.
     *
     * This does not detect bots.
     * It simply verifies whether the UA resembles
     * a modern web browser.
     *
     * @return bool
     */
    private static function hasBrowserUserAgent(): bool
    {
        $ua = sanitize_text_field(
            wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

        if ($ua === '') {
            return false;
        }

        static $pattern = null;

        if ($pattern === null) {
            $pattern = require PROACTIVE_SITE_ADVISOR_PATH . 'data/browser-patterns.php';
        }

        if (!is_string($pattern) || $pattern === '') {
            return false;
        }

        return preg_match($pattern, $ua) === 1;
    }

    /**
     * Verify browser navigation destination.
     *
     * @return bool
     */
    private static function hasDocumentDestination(): bool
    {
        if (!isset($_SERVER['HTTP_SEC_FETCH_DEST'])) {
            return false;
        }

        $dest = sanitize_text_field(
            wp_unslash($_SERVER['HTTP_SEC_FETCH_DEST'])
        );

        return $dest === 'document';
    }

    /**
     * Verify Sec-Fetch-Site header.
     *
     * Browser navigations typically send one of these values.
     *
     * @return bool
     */
    private static function hasValidFetchSite(): bool
    {
        if (empty($_SERVER['HTTP_SEC_FETCH_SITE'])) {
            return false;
        }

        $site = sanitize_text_field(
            wp_unslash($_SERVER['HTTP_SEC_FETCH_SITE'])
        );

        return in_array(
            $site,
            ['none', 'same-origin', 'same-site', 'cross-site'],
            true
        );
    }

    /**
     * Check for Purpose or Sec-Purpose headers.
     *
     * These headers indicate prefetch or preview requests,
     * which are not real browser navigations.
     *
     * @return bool
     */
    private static function hasPurposeHeader(): bool
    {
        if (isset($_SERVER['HTTP_PURPOSE'])) {
            $purpose = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_PURPOSE'])
            );

            if (
                stripos($purpose, 'prefetch') !== false ||
                stripos($purpose, 'preview') !== false
            ) {
                return true;
            }
        }

        if (isset($_SERVER['HTTP_SEC_PURPOSE'])) {
            $secPurpose = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_SEC_PURPOSE'])
            );

            if (stripos($secPurpose, 'prefetch') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate browser request score.
     *
     * @return int
     */
    private static function getHeaderScore(): int
    {
        $score = 0;

        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            );

            if (strlen($lang) > 2) {
                $score++;
            }
        }

        if (!empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $encoding = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])
            );

            if (
                stripos($encoding, 'gzip') !== false ||
                stripos($encoding, 'br') !== false ||
                stripos($encoding, 'zstd') !== false
            ) {
                $score++;
            }
        }

        if (
            isset($_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS']) &&
            $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] === '1'
        ) {
            $score++;
        }

        if (!empty($_SERVER['HTTP_ACCEPT'])) {
            $accept = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_ACCEPT'])
            );

            $types = array_filter(explode(',', $accept));

            if (count($types) >= 2) {
                $score++;
            }
        }

        if (self::hasValidFetchSite()) {
            $score++;
        }

        return $score;
    }

    /**
     * Detect suspicious header patterns commonly used by headless browsers.
     * This complements the main isBrowser() check.
     *
     * @return bool
     */
    public static function isSuspicious(): bool
    {
        $ua             = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $acceptEncoding = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''));
        $referer        = sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'] ?? ''));
        $acceptLanguage = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
        $secFetchSite   = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_FETCH_SITE'] ?? ''));
        $accept         = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT'] ?? ''));

        $isChrome = stripos($ua, 'Chrome') !== false
            && stripos($ua, 'Edg') === false
            && stripos($ua, 'OPR') === false;

        $isFirefox = stripos($ua, 'Firefox') !== false;

        $isSafari = stripos($ua, 'Safari') !== false
            && stripos($ua, 'Chrome') === false;

        $isKnownBrowser = $isChrome || $isFirefox || $isSafari;

        if (
            $isKnownBrowser &&
            stripos($acceptEncoding, 'br') === false &&
            stripos($acceptEncoding, 'zstd') === false
        ) {
            return true;
        }

        $isLinux = stripos($ua, 'Linux') !== false;

        $missingContext =
            empty($referer) &&
            $secFetchSite === 'none';

        $invalidAcceptLanguage =
            empty($acceptLanguage) ||
            !preg_match('/;q=[0-9.]+/', $acceptLanguage);

        if (($isChrome || $isFirefox) &&
            $isLinux &&
            $missingContext &&
            $invalidAcceptLanguage
        ) {
            return true;
        }

        if ($isKnownBrowser && stripos($accept, 'image/webp') === false && stripos($accept, 'image/avif') === false) {
            return true;
        }

        return false;
    }
}