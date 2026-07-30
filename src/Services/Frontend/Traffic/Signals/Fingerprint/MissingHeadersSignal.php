<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks for missing common browser headers.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class MissingHeadersSignal implements ScoreSignalInterface
{
    /**
     * {@inheritDoc}
     */
    public function getScore(): int
    {
        $score = 0;

        if (HeaderReader::getAcceptLanguage() === '') {
            ++$score;
        }

        if (HeaderReader::getAcceptEncoding() === '') {
            ++$score;
        }

        if (HeaderReader::getUpgradeInsecureRequests() === '') {
            ++$score;
        }

        if (HeaderReader::getAccept() === '') {
            ++$score;
        }

        return $score;
    }
}