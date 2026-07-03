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
        if (isset($_SERVER['HTTP_SEC_FETCH_MODE']) &&
            sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_FETCH_MODE'])) === 'navigate') {
            return false;
        }

        if (isset($_SERVER['HTTP_PURPOSE'])) {
            $purpose = sanitize_text_field(wp_unslash($_SERVER['HTTP_PURPOSE']));
            if (stripos($purpose, 'prefetch') !== false || stripos($purpose, 'preview') !== false) {
                return true;
            }
        }

        if (isset($_SERVER['HTTP_SEC_PURPOSE'])) {
            $secPurpose = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_PURPOSE']));
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
     * Detect suspicious requests using a weighted scoring system.
     * Returns true only if the total score exceeds a dynamic threshold.
     *
     * @return bool
     */
    public static function isSuspicious(): bool
    {
        $ua             = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $accept         = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT'] ?? ''));
        $acceptLanguage = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
        $acceptEncoding = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''));
        $secChUa        = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_CH_UA'] ?? ''));
        $secFetchSite   = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_FETCH_SITE'] ?? ''));

        $score = 0;

        $isChrome = stripos($ua, 'Chrome') !== false
            && stripos($ua, 'Edg') === false
            && stripos($ua, 'OPR') === false;

        $isFirefox = stripos($ua, 'Firefox') !== false;
        $isSafari  = stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false;
        $isEdge    = stripos($ua, 'Edg') !== false;
        $isOpera   = stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false;

        $isKnownBrowser = $isChrome || $isFirefox || $isSafari || $isEdge || $isOpera;

        if (stripos($accept, 'text/html') === false) {
            $score += 3;
        }

        if ($acceptLanguage === '') {
            ++$score;
        }

        if ($acceptEncoding === '' || trim($acceptEncoding) === 'identity') {
            ++$score;
        }

        $acceptTypes = array_filter(explode(',', $accept));
        if (count($acceptTypes) < 2) {
            ++$score;
        }

        if ($isChrome || $isEdge || $isOpera) {
            if ($secChUa === '') {
                ++$score;
            }
            if ($secFetchSite === '') {
                ++$score;
            }
        }

        if ($isFirefox && $secChUa !== '') {
            $score += 2;
        }

        if ($isKnownBrowser) {
            --$score;
        }

        if (isset($_SERVER['HTTP_SEC_FETCH_MODE'], $_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_MODE'] === 'navigate' && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'document') {
            $score -= 2;
        }

        return $score >= 5;
    }
}