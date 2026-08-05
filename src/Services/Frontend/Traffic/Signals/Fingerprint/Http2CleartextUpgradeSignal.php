<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects HTTP/2 cleartext (h2c) upgrade attempts.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class Http2CleartextUpgradeSignal implements ScoreSignalInterface
{
    /** Score assigned for an h2c upgrade attempt. */
    private const SCORE_H2C_UPGRADE = 3;

    /**
     * {@inheritDoc}
     */
    public function getScore(): int
    {
        $upgrade = HeaderReader::getHeader('HTTP_UPGRADE');

        if (stripos($upgrade, 'h2c') !== false) {
            return self::SCORE_H2C_UPGRADE;
        }

        if (
            HeaderReader::hasHeader('HTTP_HTTP2_SETTINGS') &&
            stripos(HeaderReader::getHeader('HTTP_CONNECTION'), 'upgrade') !== false
        ) {
            return self::SCORE_H2C_UPGRADE;
        }

        return 0;
    }
}