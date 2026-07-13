<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Config;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotAgentSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserHeadersSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserNameSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\FingerprintSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\IpSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\RateSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\ReferrerSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\ScannerPatternSignal;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuration for traffic detection signals.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Config
 * @since   1.0.0
 */
class SignalConfig
{
    /** Returns list of bot signal classes. */
    public static function getBotSignals(): array
    {
        $signals = [
            BotAgentSignal::class,
            BrowserHeadersSignal::class,
            BrowserNameSignal::class,
            IpSignal::class,
            ReferrerSignal::class,
            ScannerPatternSignal::class,
            FingerprintSignal::class,
            RateSignal::class,
        ];

        /**
         * Filters the list of bot signal classes.
         *
         * @param string[] $signals Array of fully qualified class names.
         * @since  1.0.0
         */
        return apply_filters('proactive_site_advisor_bot_signals', $signals);
    }

    /** Returns list of score signal classes. */
    public static function getScoreSignals(): array
    {
        $signals = [
            BrowserHeadersSignal::class,
            BrowserNameSignal::class,
            FingerprintSignal::class,
            RateSignal::class,
        ];

        /**
         * Filters the list of score signal classes.
         *
         * @param string[] $signals Array of fully qualified class names.
         * @since  1.0.0
         */
        return apply_filters('proactive_site_advisor_score_signals', $signals);
    }
}