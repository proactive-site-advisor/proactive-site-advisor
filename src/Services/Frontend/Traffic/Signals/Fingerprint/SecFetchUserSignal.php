<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\BrowserHelper;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects a missing Sec-Fetch-User header on top-level navigation requests.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class SecFetchUserSignal implements ScoreSignalInterface
{
    /** Score assigned when Sec-Fetch-User is unexpectedly missing. */
    private const SCORE = 1;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        if (BrowserHelper::isNavigationWithPrefetch()) {
            return 0;
        }

        if (
            HeaderReader::getSecFetchMode() !== 'navigate' ||
            HeaderReader::getSecFetchDest() !== 'document'
        ) {
            return 0;
        }

        if (HeaderReader::getSecFetchUser() !== '') {
            return 0;
        }

        $userAgent = HeaderReader::getUserAgent();

        $isSupportedBrowser =
            BrowserHelper::isModernChromeFamily($userAgent) ||
            BrowserHelper::isModernFirefox($userAgent) ||
            BrowserHelper::isSafariLike($userAgent);

        if (!$isSupportedBrowser) {
            return 0;
        }

        return self::SCORE;
    }
}