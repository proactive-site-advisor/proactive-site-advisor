<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Config;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\NavigationBehaviorSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\AcceptEncodingBrotliSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\SecFetchUserSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\Http2CleartextUpgradeSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\SecFetchSiteNoneWithRefererSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\AcceptEncodingDeflateSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\AcceptEncodingSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\AcceptHeaderSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\AcceptLanguageSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\ClientHintsSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\ConnectionHeaderSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\DistinctUserAgentSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\FetchHeadersSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\MissingHeadersSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint\CookieBehaviorSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BehavioralSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotAgentSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserHeadersSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserNameSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\IpSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\RateSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\RefererConsistencySignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\ReferrerSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\RequestMethodSignal;
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
            RateSignal::class,
            BehavioralSignal::class,
            ClientHintsSignal::class,
            FetchHeadersSignal::class,
            DistinctUserAgentSignal::class,
            RefererConsistencySignal::class,
            RequestMethodSignal::class,
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
            BrowserNameSignal::class,
            RateSignal::class,
            BehavioralSignal::class,
            ClientHintsSignal::class,
            AcceptLanguageSignal::class,
            FetchHeadersSignal::class,
            DistinctUserAgentSignal::class,
            MissingHeadersSignal::class,
            AcceptEncodingSignal::class,
            ConnectionHeaderSignal::class,
            AcceptHeaderSignal::class,
            RefererConsistencySignal::class,
            RequestMethodSignal::class,
            CookieBehaviorSignal::class,
            SecFetchSiteNoneWithRefererSignal::class,
            AcceptEncodingDeflateSignal::class,
            Http2CleartextUpgradeSignal::class,
            SecFetchUserSignal::class,
            AcceptEncodingBrotliSignal::class,
            NavigationBehaviorSignal::class,
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