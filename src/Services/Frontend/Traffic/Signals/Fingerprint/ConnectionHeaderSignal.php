<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes connection related headers for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class ConnectionHeaderSignal implements ScoreSignalInterface
{
    /** Score value for proxy connection header. */
    private const SCORE_PROXY_CONNECTION = 2;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        if ($this->hasProxyConnectionHeader()) {
            return self::SCORE_PROXY_CONNECTION;
        }

        return 0;
    }

    /** Detects non-standard proxy connection header. */
    private function hasProxyConnectionHeader(): bool
    {
        return HeaderReader::hasHeader('HTTP_PROXY_CONNECTION');
    }
}