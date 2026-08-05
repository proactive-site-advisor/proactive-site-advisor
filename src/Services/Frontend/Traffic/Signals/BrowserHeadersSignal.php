<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes browser headers for bot detection and suspicion scoring.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class BrowserHeadersSignal implements BotSignalInterface
{
    /** Score value for Upgrade-Insecure-Requests header. */
    private const UPGRADE_HEADER_SCORE = 2;

    /** Minimum header score to be considered a real browser. */
    private const MIN_BROWSER_HEADER_SCORE = 4;

    /** Valid Sec-Fetch-Site values for a browser request. */
    private const VALID_FETCH_SITES = [
        'none',
        'same-origin',
        'same-site',
        'cross-site',
    ];

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        return !$this->isBrowser();
    }

    /** Determines if the request resembles a real browser navigation. */
    private function isBrowser(): bool
    {
        if (!$this->hasHtmlAcceptHeader()) {
            return false;
        }

        if (!$this->hasBrowserUserAgent()) {
            return false;
        }

        if ($this->hasJsonAcceptInNavigation()) {
            return false;
        }

        if ($this->getHeaderScore() < self::MIN_BROWSER_HEADER_SCORE) {
            return false;
        }

        return true;
    }

    /** Verifies HTML document negotiation. */
    private function hasHtmlAcceptHeader(): bool
    {
        $accept = HeaderReader::getAccept();

        if ($accept === '') {
            return false;
        }

        return stripos($accept, 'text/html') !== false;
    }

    /** Verifies browser User-Agent. */
    private function hasBrowserUserAgent(): bool
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

    /** Verifies Sec-Fetch-Site header. */
    private function hasValidFetchSite(): bool
    {
        $site = HeaderReader::getSecFetchSite();

        if ($site === '') {
            return false;
        }

        /**
         * Filters the list of valid Sec-Fetch-Site header values.
         *
         * @param string[] $sites Default ['none', 'same-origin', 'same-site', 'cross-site'].
         * @since  1.0.0
         */
        $validSites = apply_filters('proactive_site_advisor_valid_fetch_sites', self::VALID_FETCH_SITES);

        if (!is_array($validSites)) {
            $validSites = self::VALID_FETCH_SITES;
        }

        return in_array($site, $validSites, true);
    }

    /** Calculates browser request score. */
    private function getHeaderScore(): int
    {
        $score = 0;

        $acceptLanguage = HeaderReader::getAcceptLanguage();
        if ($acceptLanguage !== '') {
            $score++;
        }

        $acceptEncoding = HeaderReader::getAcceptEncoding();
        if ($acceptEncoding !== '') {
            $score++;
        }

        $upgrade = HeaderReader::getUpgradeInsecureRequests();
        if ($upgrade === '1') {
            $score += self::UPGRADE_HEADER_SCORE;
        }

        $accept = HeaderReader::getAccept();
        if ($accept !== '') {
            $score++;
        }

        if ($this->hasValidFetchSite()) {
            $score++;
        }

        return $score;
    }

    /** Checks if navigation request contains JSON in Accept header. */
    private function hasJsonAcceptInNavigation(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        if ($mode !== 'navigate') {
            return false;
        }

        $accept = HeaderReader::getAccept();
        return stripos($accept, 'application/json') !== false;
    }
}