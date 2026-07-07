<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BrowserSignal
 *
 * Validates whether the current request resembles a real browser navigation.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class BrowserSignal
{
    /**
     * Determine whether the current request looks like a browser-generated page navigation.
     *
     * @return bool
     */
    public static function isBrowser(): bool
    {
        if (self::isPrefetchOrPreview()) {
            return false;
        }

        if (!self::hasHtmlAcceptHeader()) {
            return false;
        }

        if (!self::hasBrowserUserAgent()) {
            return false;
        }

        if (self::getHeaderScore() < 4) {
            return false;
        }

        return true;
    }

    /**
     * Verify HTML document negotiation.
     *
     * @return bool
     */
    private static function hasHtmlAcceptHeader(): bool
    {
        $accept = HeaderReader::getAccept();

        if ($accept === '') {
            return false;
        }

        return stripos($accept, 'text/html') !== false;
    }

    /**
     * Verify browser User-Agent.
     *
     * @return bool
     */
    private static function hasBrowserUserAgent(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if ($ua === '') {
            return false;
        }

        static $pattern = null;

        if ($pattern === null) {
            $pattern = DataLoader::loadBrowserPatterns();
        }

        if (!is_string($pattern) || $pattern === '') {
            return false;
        }

        return preg_match($pattern, $ua) === 1;
    }

    /**
     * Verify Sec-Fetch-Site header.
     *
     * @return bool
     */
    private static function hasValidFetchSite(): bool
    {
        $site = HeaderReader::getSecFetchSite();

        if ($site === '') {
            return false;
        }

        return in_array(
            $site,
            ['none', 'same-origin', 'same-site', 'cross-site'],
            true
        );
    }

    /**
     * Check for Purpose or Sec-Purpose headers.
     *
     * @return bool
     */
    private static function isPrefetchOrPreview(): bool
    {
        $purpose = HeaderReader::getPurpose();
        if ($purpose !== '' && (stripos($purpose, 'prefetch') !== false || stripos($purpose, 'preview') !== false)) {
            return true;
        }

        $secPurpose = HeaderReader::getSecPurpose();
        return $secPurpose !== '' && stripos($secPurpose, 'prefetch') !== false;
    }

    /**
     * Calculate browser request score.
     *
     * @return int
     */
    private static function getHeaderScore(): int
    {
        $score = 0;

        $acceptLanguage = HeaderReader::getAcceptLanguage();
        if ($acceptLanguage !== '' && strlen($acceptLanguage) > 2) {
            $score++;
        }

        $acceptEncoding = HeaderReader::getAcceptEncoding();
        if ($acceptEncoding !== '') {
            if (
                stripos($acceptEncoding, 'gzip') !== false ||
                stripos($acceptEncoding, 'br') !== false ||
                stripos($acceptEncoding, 'zstd') !== false
            ) {
                $score++;
            }
        }

        $upgrade = HeaderReader::getUpgradeInsecureRequests();
        if ($upgrade === '1') {
            $score += 2;
        }

        $accept = HeaderReader::getAccept();
        if ($accept !== '') {
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
}