<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\ClientHintsHelper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects the HeadlessChrome brand in the Sec-CH-UA header.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.2.5
 */
class HeadlessChromeSignal implements BotSignalInterface
{
    /** {@inheritDoc} */
    public function isBot(): bool
    {
        return ClientHintsHelper::containsBrand(['HeadlessChrome']);
    }
}