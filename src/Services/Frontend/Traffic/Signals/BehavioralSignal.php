<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects bots by analyzing request timing consistency.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class BehavioralSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Maximum timestamps kept per fingerprint. */
    private const HISTORY_LIMIT = 10;

    /** Cache lifetime in seconds. */
    private const CACHE_TTL = 60;

    /** Returned when there is not enough data. */
    private const NO_DATA_DEVIATION = 999.0;

    /** Default threshold for hard bot detection. */
    private const DEFAULT_BOT_THRESHOLD = 0.5;

    /** Default threshold for perfect interval (cron-like) detection. */
    private const DEFAULT_PERFECT_INTERVAL_THRESHOLD = 0.05;

    /** Score thresholds. */
    private const SCORE_MEDIUM_THRESHOLD = 1.0;

    /** Minimum number of data points required to compute deviation. */
    private const MIN_HISTORY_COUNT = 3;

    /** Score values. */
    private const SCORE_MEDIUM_VALUE = 2;

    /** Cached deviation for the current request. */
    private static ?float $intervalDeviation = null;

    /** Prevents duplicate timestamp recording per request. */
    private static bool $recorded = false;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        $deviation = $this->getIntervalDeviation();

        if ($deviation < $this->getPerfectIntervalThreshold()) {
            return true;
        }

        return $deviation < $this->getBotThreshold();
    }

    /**
     * {@inheritDoc}
     */
    public function getScore(): int
    {
        if ($this->getIntervalDeviation() < self::SCORE_MEDIUM_THRESHOLD) {
            return self::SCORE_MEDIUM_VALUE;
        }

        return 0;
    }

    /** Returns the configured hard bot threshold. */
    private function getBotThreshold(): float
    {
        /**
         * Filters the standard deviation threshold used for hard bot detection.
         *
         * @param float $threshold Default 0.5 seconds.
         * @since 1.0.0
         */
        return (float)apply_filters(
            'proactive_site_advisor_behavioral_bot_threshold',
            self::DEFAULT_BOT_THRESHOLD
        );
    }

    /** Returns the configured perfect interval threshold. */
    private function getPerfectIntervalThreshold(): float
    {
        /**
         * Filters the standard deviation threshold used for detecting
         * perfectly regular (cron-like) request intervals.
         *
         * @param float $threshold Default 0.05 seconds.
         * @since 1.0.0
         */
        return (float)apply_filters(
            'proactive_site_advisor_behavioral_perfect_interval_threshold',
            self::DEFAULT_PERFECT_INTERVAL_THRESHOLD
        );
    }

    /** Returns the request interval deviation for the current fingerprint. */
    private function getIntervalDeviation(): float
    {
        if (self::$intervalDeviation !== null) {
            return self::$intervalDeviation;
        }

        $fingerprint = HeaderReader::getFingerprint();
        $cacheKey    = CacheKeys::behavioralHistory($fingerprint);

        $cache   = CacheManager::instance();
        $history = $cache->get($cacheKey);

        if (!is_array($history)) {
            $history = [];
        }

        if (!self::$recorded) {
            $history[] = microtime(true);

            if (count($history) > self::HISTORY_LIMIT) {
                array_shift($history);
            }

            $cache->set($cacheKey, $history, self::CACHE_TTL);
            self::$recorded = true;
        }

        self::$intervalDeviation = $this->calculateDeviation($history);

        return self::$intervalDeviation;
    }

    /** Calculates the standard deviation of request intervals. */
    private function calculateDeviation(array $history): float
    {
        if (count($history) < self::MIN_HISTORY_COUNT) {
            return self::NO_DATA_DEVIATION;
        }

        $intervals = [];

        for ($i = 1, $max = count($history); $i < $max; $i++) {
            $intervals[] = $history[$i] - $history[$i - 1];
        }

        $count = count($intervals);

        $mean = array_sum($intervals) / $count;

        $variance = 0.0;

        foreach ($intervals as $interval) {
            $variance += ($interval - $mean) ** 2;
        }

        return sqrt($variance / $count);
    }
}