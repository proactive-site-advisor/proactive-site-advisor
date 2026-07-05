<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Utils\Environment;

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
        if (Environment::isLocal()) {
            return self::hasHtmlAcceptHeader() && self::hasBrowserUserAgent();
        }

        if (self::isPrefetchOrPreview()) {
            return false;
        }

        if (!self::hasHtmlAcceptHeader()) {
            return false;
        }

        if (!self::hasBrowserUserAgent()) {
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
            $filePath = PROACTIVE_SITE_ADVISOR_PATH . 'data/browser-patterns.php';
            if (file_exists($filePath)) {
                $pattern = require $filePath;
            }
        }

        if (!is_string($pattern) || $pattern === '') {
            return false;
        }

        return preg_match($pattern, $ua) === 1;
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
    private static function isPrefetchOrPreview(): bool
    {
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
            sanitize_text_field(wp_unslash($_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'])) === '1'
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
     * Check if the User-Agent version is implausibly old for the current date.
     * This detects bots using outdated UA strings that real users rarely use.
     *
     * @return bool
     */
    private static function isUserAgentImplausible(): bool
    {
        $ua              = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $secChUa         = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_CH_UA'] ?? ''));
        $secChUaMobile   = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? ''));
        $secChUaPlatform = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? ''));
        $acceptLanguage  = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));

        if ($ua === '') {
            return false;
        }

        $score          = 0;
        $hasModernHints = $secChUa !== '' || $secChUaMobile !== '' || $secChUaPlatform !== '';

        $isChrome  = preg_match('/Chrome\/(\d+)/i', $ua, $chromeMatch) && stripos($ua, 'Edg') === false && stripos($ua, 'OPR') === false;
        $isEdge    = preg_match('/Edg\/(\d+)/i', $ua, $edgeMatch);
        $isOpera   = preg_match('/OPR\/(\d+)/i', $ua, $operaMatch);
        $isFirefox = preg_match('/Firefox\/(\d+)/i', $ua, $firefoxMatch);
        $isSafari  = preg_match('/Version\/(\d+)/i', $ua, $safariMatch) || preg_match('/OS (\d+)_/i', $ua, $osMatch);

        if ($isChrome) {
            $version = (int)$chromeMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 90) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else {
                if (!$hasModernHints) {
                    $score += 3;
                }
                if ($hasModernHints && $secChUa !== '' && stripos($secChUa, 'Chrome') === false && stripos($secChUa, 'Chromium') === false) {
                    $score += 2;
                }
            }
        }

        if ($isEdge) {
            $version = (int)$edgeMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 90) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else {
                if (!$hasModernHints) {
                    $score += 3;
                }
                if ($hasModernHints && $secChUa !== '' && stripos($secChUa, 'Edg') === false && stripos($secChUa, 'Edge') === false) {
                    $score += 2;
                }
            }
        }

        if ($isOpera) {
            $version = (int)$operaMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 90) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else {
                if (!$hasModernHints) {
                    $score += 3;
                }
                if ($hasModernHints && $secChUa !== '' && stripos($secChUa, 'Opera') === false && stripos($secChUa, 'OPR') === false) {
                    $score += 2;
                }
            }
        }

        if ($isFirefox) {
            $version = (int)$firefoxMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 100) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else if (!$hasModernHints) {
                $score += 3;
            }
        }

        if ($isSafari) {
            $version = (int)($safariMatch[1] ?? $osMatch[1] ?? 0);
            if ($version < 15) {
                $score += 3;
            } elseif ($version < 17) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else if (!$hasModernHints) {
                $score += 3;
            }
        }

        if ($acceptLanguage === '') {
            ++$score;
        }

        return $score >= 4;
    }

    /**
     * @param string $ua
     * @param string $acceptLanguage
     * @param string $secChUa
     *
     * @return int
     */
    private static function getHeaderInconsistencyScore(
        string $ua,
        string $acceptLanguage,
        string $secChUa
    ): int
    {
        $score = 0;

        if ($secChUa !== '') {
            $isChromeFamily = (
                stripos($ua, 'Chrome') !== false ||
                stripos($ua, 'Edg') !== false ||
                stripos($ua, 'OPR') !== false
            );

            if ($isChromeFamily) {
                $hasChromeHint = (
                    stripos($secChUa, 'Chrome') !== false ||
                    stripos($secChUa, 'Google Chrome') !== false ||
                    stripos($secChUa, 'Chromium') !== false
                );

                if (!$hasChromeHint) {
                    $score += 2;
                }
            }
        }

        if ($acceptLanguage === '') {
            ++$score;
        } else {
            $firstLang = strtok($acceptLanguage, ',;');
            $firstLang = trim($firstLang);
            if (!preg_match('/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]{1,8})*$/', $firstLang)) {
                ++$score;
            }
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $encoding = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])
            );

            $isModernUA = preg_match(
                '/(Chrome|Firefox|Edg|Safari)\/\d{2,}/',
                $ua
            );

            if ($isModernUA && stripos($encoding, 'br') === false) {
                ++$score;
            }
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
        $secFetchMode   = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_FETCH_MODE'] ?? ''));
        $secFetchDest   = sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_FETCH_DEST'] ?? ''));

        $score = 0;

        $isChrome = stripos($ua, 'Chrome') !== false
            && stripos($ua, 'Edg') === false
            && stripos($ua, 'OPR') === false;

        $isSafari = stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false;
        $isEdge   = stripos($ua, 'Edg') !== false;
        $isOpera  = stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false;

        if (self::isUserAgentImplausible()) {
            $score += 4;
        }

        if (stripos($accept, 'text/html') === false) {
            $score += 3;
        }

        $acceptTypes = $accept !== ''
            ? array_filter(array_map('trim', explode(',', $accept)))
            : [];
        if (count($acceptTypes) < 2) {
            $score += 2;
        }

        if ($acceptLanguage === '') {
            ++$score;
        }

        if ($acceptEncoding === '' || trim($acceptEncoding) === 'identity') {
            ++$score;
        }

        if (($isChrome || $isEdge || $isOpera) && $secChUa === '') {
            ++$score;
        }

        if ($isSafari && $acceptLanguage === '') {
            $score += 2;
        }

        if (!in_array($secFetchSite, ['none', 'same-origin', 'same-site', 'cross-site'], true)) {
            ++$score;
        }

        if ($secFetchMode === 'navigate' && $secFetchDest === 'document') {
            $score -= 2;
        }

        $inconsistencyScore = self::getHeaderInconsistencyScore(
            $ua,
            $acceptLanguage,
            $secChUa
        );
        $score              += $inconsistencyScore;

        return $score >= 3;
    }
}