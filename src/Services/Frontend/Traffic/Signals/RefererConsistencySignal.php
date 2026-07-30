<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects inconsistent referrer behavior.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class RefererConsistencySignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Score values. */
    private const SCORE_MISSING_REFERER_NAVIGATION = 2;

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        return $this->hasInvalidReferer();
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score = 0;

        if ($this->hasMissingRefererOnNavigation()) {
            $score += self::SCORE_MISSING_REFERER_NAVIGATION;
        }

        return $score;
    }

    /** Detects malformed referrer header. */
    private function hasInvalidReferer(): bool
    {
        $referer = HeaderReader::getReferer();

        if ($referer === '') {
            return false;
        }

        return wp_parse_url($referer) === false;
    }

    /** Checks missing referrer on browser navigation. */
    private function hasMissingRefererOnNavigation(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();
        $site = HeaderReader::getSecFetchSite();

        if (HeaderReader::getSecFetchSite() === 'cross-site') {
            return false;
        }

        if ($mode !== 'navigate' || $dest !== 'document') {
            return false;
        }

        if ($site === 'none') {
            return false;
        }

        return HeaderReader::getReferer() === '';
    }
}