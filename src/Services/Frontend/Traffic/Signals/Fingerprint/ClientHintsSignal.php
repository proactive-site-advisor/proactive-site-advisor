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
 * Analyzes Client Hints (Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform) for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class ClientHintsSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Score values for individual signals. */
    private const SCORE_VERSION_MISMATCH         = 3;
    private const SCORE_CLIENT_MOBILE_MISMATCH   = 2;
    private const SCORE_CLIENT_PLATFORM_MISMATCH = 2;
    private const SCORE_INVALID_CLIENT_HINTS     = 3;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if (ClientHintsHelper::hasMalformedClientHints()) {
            return true;
        }

        if (BrowserHelper::isModernChromeFamily($ua) && ClientHintsHelper::hasMissingClientHints()) {
            return true;
        }

        if (ClientHintsHelper::hasIncompleteClientHints()) {
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
        $ua    = HeaderReader::getUserAgent();

        if (!BrowserHelper::isSafariLike($ua)) {
            if (BrowserHelper::isModernChromeFamily($ua) && ClientHintsHelper::hasVersionMismatch($ua)) {
                $score += self::SCORE_VERSION_MISMATCH;
            }

            if (ClientHintsHelper::hasClientMobileMismatch($ua)) {
                $score += self::SCORE_CLIENT_MOBILE_MISMATCH;
            }

            if (ClientHintsHelper::hasClientPlatformMismatch($ua)) {
                $score += self::SCORE_CLIENT_PLATFORM_MISMATCH;
            }

            if (BrowserHelper::isModernChromeFamily($ua) && ClientHintsHelper::hasInvalidClientHints($ua)) {
                $score += self::SCORE_INVALID_CLIENT_HINTS;
            }
        }

        return $score;
    }
}