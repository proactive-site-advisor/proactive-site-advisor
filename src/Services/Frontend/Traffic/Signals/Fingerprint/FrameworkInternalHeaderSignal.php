<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects internal framework middleware headers that no browser sends.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.2.5
 */
class FrameworkInternalHeaderSignal implements BotSignalInterface
{
    /** Headers emitted only by internal framework middleware. */
    private const INTERNAL_HEADERS = [
        'HTTP_X_MIDDLEWARE_SUBREQUEST',
        'HTTP_X_NEXTJS_DATA',
    ];

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        foreach (self::INTERNAL_HEADERS as $header) {
            if (HeaderReader::hasHeader($header)) {
                return true;
            }
        }

        return false;
    }
}