<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\BrowserHelper;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\ClientHintsHelper;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes Sec-Fetch-* headers for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class FetchHeadersSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Score values for individual signals. */
    private const SCORE_NAVIGATION_MISMATCH = 3;
    private const SCORE_EXPLICIT_NON_USER   = 3;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if ($this->hasMissingAllFetchHeaders()) {
            return true;
        }

        if ($this->isScannerClientHints($ua)) {
            return true;
        }

        if ($this->hasFetchUserForNonModern($ua)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getScore(): int
    {
        $score = 0;

        if ($this->hasNavigationMismatch()) {
            $score += self::SCORE_NAVIGATION_MISMATCH;
        }

        if ($this->hasExplicitNonUserNavigation()) {
            $score += self::SCORE_EXPLICIT_NON_USER;
        }

        return $score;
    }

    /** Checks if all Sec-Fetch-* headers are missing. */
    private function hasMissingAllFetchHeaders(): bool
    {
        return HeaderReader::getSecFetchSite() === ''
            && HeaderReader::getSecFetchMode() === ''
            && HeaderReader::getSecFetchDest() === '';
    }

    /** Checks if the request is a scanner based on client hints + referrer. */
    private function isScannerClientHints(string $ua): bool
    {
        if (!ClientHintsHelper::hasInvalidClientHints($ua)) {
            return false;
        }

        if (HeaderReader::getReferer() !== '') {
            return false;
        }

        $site = HeaderReader::getSecFetchSite();

        return !($site !== '' && $site !== 'none');
    }

    /** Checks if non-modern browsers are sending Sec-Fetch-User header. */
    private function hasFetchUserForNonModern(string $ua): bool
    {
        if (BrowserHelper::isModernChromeFamily($ua) || BrowserHelper::isModernFirefox($ua)) {
            return false;
        }

        $user = HeaderReader::getSecFetchUser();

        return $user === '?1';
    }

    /** Checks navigation mismatch. */
    private function hasNavigationMismatch(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();

        if ($mode === '' && $dest === '') {
            return false;
        }

        return !($mode === 'navigate' && $dest === 'document');
    }

    /** Only flag if Sec-Fetch-User is explicitly ?0 (non-user-initiated). */
    private function hasExplicitNonUserNavigation(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if (!BrowserHelper::isModernChromeFamily($ua) && !BrowserHelper::isModernFirefox($ua)) {
            return false;
        }

        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();
        $user = HeaderReader::getSecFetchUser();

        return ($mode === 'navigate' && $dest === 'document' && $user === '?0');
    }
}