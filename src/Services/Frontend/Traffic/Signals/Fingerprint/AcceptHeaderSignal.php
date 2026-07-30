<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes Accept header patterns for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class AcceptHeaderSignal implements ScoreSignalInterface
{
    /** Score values. */
    private const SCORE_WILDCARD_ONLY = 1;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        if ($this->hasWildcardOnlyAccept()) {
            return self::SCORE_WILDCARD_ONLY;
        }

        return 0;
    }

    /** Checks if Accept contains only wildcard. */
    private function hasWildcardOnlyAccept(): bool
    {
        return trim(HeaderReader::getAccept()) === '*/*';
    }
}