<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects bot-like navigation patterns across consecutive page views.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class NavigationBehaviorSignal implements ScoreSignalInterface
{
    /** Maximum score this signal can contribute. */
    private const MAX_SCORE = 3;

    /** Maximum time span (seconds) for "many pages" to be considered suspicious. */
    private const RAPID_PAGES_TIME_WINDOW = 30;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $fingerprint = HeaderReader::getFingerprint();

        if ($fingerprint === '') {
            return 0;
        }

        $cache = CacheManager::instance();
        $state = $cache->get(CacheKeys::navigationBehavior($fingerprint));

        if (!is_array($state)) {
            return 0;
        }

        if (!isset($state['pages']) || $state['pages'] < 3) {
            return 0;
        }

        $score = 0;

        if ($this->isCrawlerSpeed($state)) {
            $score += 2;
        } elseif ($this->isFastUser($state)) {
            ++$score;
        }

        if (
            isset($state['unique_pages'], $state['pages'])
            && $state['pages'] >= 8
            && $state['unique_pages'] >= 6
            && !empty($state['timestamps'])
            && (max($state['timestamps']) - min($state['timestamps'])) < self::RAPID_PAGES_TIME_WINDOW
        ) {
            ++$score;
        }

        if (
            isset($state['repeat_pages'])
            && $state['repeat_pages'] >= 5
        ) {
            ++$score;
        }

        return min($score, self::MAX_SCORE);
    }

    /** Checks for crawler-speed navigation (median interval <= 1 second). */
    private function isCrawlerSpeed(array $state): bool
    {
        $intervals = $state['intervals'] ?? [];

        if (count($intervals) < 3) {
            return false;
        }

        sort($intervals);

        $median = $intervals[(int)floor(count($intervals) / 2)];

        return $median <= 1;
    }

    /** Checks for fast human navigation (median interval <= 3 seconds). */
    private function isFastUser(array $state): bool
    {
        $intervals = $state['intervals'] ?? [];

        if (empty($intervals)) {
            return false;
        }

        sort($intervals);

        $median = $intervals[(int)floor(count($intervals) / 2)];

        return $median <= 3;
    }
}