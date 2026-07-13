<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
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
class BrowserHeadersSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** {@inheritDoc} */
    public function isBot(): bool
    {
        return !$this->isBrowser();
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score = 0;

        if (!$this->hasHtmlAcceptHeader()) {
            $score += 4;
        }

        if (!$this->hasBrowserUserAgent()) {
            $score += 3;
        }

        $headerScore = $this->getHeaderScore();
        if ($headerScore < 2) {
            $score += 3;
        } elseif ($headerScore < 4) {
            $score += 2;
        }

        if ($this->isPrefetchOrPreview()) {
            $score += 2;
        }

        return $score;
    }

    /** Determines if the request resembles a real browser navigation. */
    private function isBrowser(): bool
    {
        if ($this->isPrefetchOrPreview()) {
            return false;
        }

        if (!$this->hasHtmlAcceptHeader()) {
            return false;
        }

        if (!$this->hasBrowserUserAgent()) {
            return false;
        }

        if ($this->getHeaderScore() < 4) {
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

        return in_array(
            $site,
            ['none', 'same-origin', 'same-site', 'cross-site'],
            true
        );
    }

    /** Checks for prefetch or preview headers. */
    private function isPrefetchOrPreview(): bool
    {
        $checks = [
            HeaderReader::getPurpose()    => ['prefetch', 'preview'],
            HeaderReader::getSecPurpose() => ['prefetch'],
        ];

        foreach ($checks as $header => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($header, $keyword) !== false) {
                    if ($this->isNavigationRequest()) {
                        return false;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /** Helper to determine if the request is a navigation. */
    private function isNavigationRequest(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();

        return ($mode === 'navigate' && $dest === 'document');
    }

    /** Calculates browser request score. */
    private function getHeaderScore(): int
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

        if ($this->hasValidFetchSite()) {
            $score++;
        }

        return $score;
    }
}