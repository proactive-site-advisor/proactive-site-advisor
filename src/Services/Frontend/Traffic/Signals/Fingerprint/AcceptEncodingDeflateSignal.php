<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\BrowserHelper;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects browser-like requests that omit the 'deflate' Accept-Encoding token.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class AcceptEncodingDeflateSignal implements ScoreSignalInterface
{
    /** Score for missing deflate in a browser request. */
    private const SCORE_MISSING_DEFLATE = 2;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $ua = HeaderReader::getUserAgent();

        if (!$this->isBrowserUA($ua)) {
            return 0;
        }

        $acceptEncoding = HeaderReader::getAcceptEncoding();

        if ($acceptEncoding === '') {
            return 0;
        }

        foreach (explode(',', strtolower($acceptEncoding)) as $encoding) {
            if (trim(strtok($encoding, ';')) === 'deflate') {
                return 0;
            }
        }

        return self::SCORE_MISSING_DEFLATE;
    }

    /** Returns whether the User-Agent belongs to a supported modern browser. */
    private function isBrowserUA(string $ua): bool
    {
        return BrowserHelper::isModernChromeFamily($ua)
            || BrowserHelper::isModernFirefox($ua)
            || BrowserHelper::isSafariLike($ua);
    }
}