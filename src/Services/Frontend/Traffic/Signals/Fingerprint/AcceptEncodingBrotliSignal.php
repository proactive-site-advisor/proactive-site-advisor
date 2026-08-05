<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\BrowserHelper;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects modern Chromium-based browsers that omit Brotli ("br")
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class AcceptEncodingBrotliSignal implements ScoreSignalInterface
{
    /** Score assigned when a modern Chromium browser omits Brotli. */
    private const SCORE_MISSING_BROTLI = 2;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $ua = HeaderReader::getUserAgent();
        if (!BrowserHelper::isModernChromeFamily($ua) && !BrowserHelper::isModernFirefox($ua)) {
            return 0;
        }

        $acceptEncoding = HeaderReader::getAcceptEncoding();

        if ($acceptEncoding === '') {
            return 0;
        }

        $tokens = array_map(
            static function (string $encoding): string {
                return trim(strtok(strtolower($encoding), ';'));
            },
            explode(',', $acceptEncoding)
        );

        return in_array('br', $tokens, true)
            ? 0
            : self::SCORE_MISSING_BROTLI;
    }
}