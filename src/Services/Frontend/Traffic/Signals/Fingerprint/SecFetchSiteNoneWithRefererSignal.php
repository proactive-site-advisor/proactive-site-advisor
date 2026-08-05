<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects contradictory requests that send a Referer with Sec-Fetch-Site: none.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class SecFetchSiteNoneWithRefererSignal implements ScoreSignalInterface
{
    /** Score for an impossible browser fetch metadata combination. */
    private const SCORE_CONTRADICTION = 3;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        if (
            HeaderReader::getSecFetchMode() === 'navigate' &&
            HeaderReader::getSecFetchDest() === 'document' &&
            stripos(HeaderReader::getSecPurpose(), 'prefetch') !== false
        ) {
            return 0;
        }
        
        $site = HeaderReader::getSecFetchSite();

        if ($site !== 'none') {
            return 0;
        }

        return HeaderReader::getReferer() === ''
            ? 0
            : self::SCORE_CONTRADICTION;
    }
}