<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Config\SignalConfig;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Utils\Environment;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects and classifies traffic as human, bot, or suspicious.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @since   1.0.0
 */
class TrafficEngine
{
    /** Runtime cache for signal results. */
    private static array $cache = [];

    /** Cache key for human detection. */
    private const HUMAN = 'is_human';

    /** Cache key for bot detection. */
    private const BOT = 'is_bot';

    /** Cache key for suspicious score. */
    private const SUSPICIOUS = 'is_suspicious';

    /** Minimum threshold for suspicion score. */
    private const MINIMUM_SUSPICION_THRESHOLD = 4;

    /** Default threshold for suspicion score. */
    private const DEFAULT_SUSPICION_THRESHOLD = 4;

    /** Determines if the current request is from a human. */
    public static function isHuman(): bool
    {
        if (array_key_exists(self::HUMAN, self::$cache)) {
            return self::$cache[self::HUMAN];
        }

        if (Environment::isLocal()) {
            return self::$cache[self::HUMAN] = true;
        }

        if (self::detectBot()) {
            return self::$cache[self::HUMAN] = false;
        }

        if (self::hasSuspicionScore()) {
            return self::$cache[self::HUMAN] = false;
        }

        return self::$cache[self::HUMAN] = true;
    }

    /** Checks all bot signals. */
    private static function detectBot(): bool
    {
        if (array_key_exists(self::BOT, self::$cache)) {
            return self::$cache[self::BOT];
        }

        $signals = SignalConfig::getBotSignals();

        foreach ($signals as $signalClass) {
            /** @var BotSignalInterface $signal */
            $signal = new $signalClass();
            if ($signal->isBot()) {
                return self::$cache[self::BOT] = true;
            }
        }

        return self::$cache[self::BOT] = false;
    }

    /** Calculates total suspicion score. */
    private static function hasSuspicionScore(): bool
    {
        if (array_key_exists(self::SUSPICIOUS, self::$cache)) {
            return self::$cache[self::SUSPICIOUS];
        }

        $score   = 0;
        $signals = SignalConfig::getScoreSignals();

        foreach ($signals as $signalClass) {
            /** @var ScoreSignalInterface $signal */
            $signal = new $signalClass();
            $score  += $signal->getScore();
        }

        /**
         * Filters the suspicion score threshold.
         *
         * @param int $threshold The minimum score to mark a request as suspicious.
         * @return int
         * @since  1.0.0
         */
        $threshold = (int)apply_filters(
            'proactive_site_advisor_suspicion_threshold',
            self::DEFAULT_SUSPICION_THRESHOLD
        );
        $threshold = max(self::MINIMUM_SUSPICION_THRESHOLD, $threshold);

        return self::$cache[self::SUSPICIOUS] = $score >= $threshold;
    }
}