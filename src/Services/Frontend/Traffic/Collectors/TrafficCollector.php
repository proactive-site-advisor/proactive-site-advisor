<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Collectors;

use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotDetector;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\PageviewSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Decision\TrafficClassifier;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficCollector
 *
 * Collects frontend pageview counts using database storage.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Collectors
 * @version 1.0.0
 */
class TrafficCollector
{
    /**
     * Maximum number of bot names to keep in daily stats.
     */
    private const MAX_BOT_NAMES = 30;

    /**
     * Prevents duplicate tracking per request.
     *
     * @var bool
     */
    private static bool $hasRun = false;

    /**
     * Increment pageview count if this is a valid frontend request.
     *
     * @return void
     */
    public function maybeCountPageview(): void
    {
        if (self::$hasRun) {
            return;
        }

        self::$hasRun = true;

        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        $today = DateTimeUtils::todayKey();

        if (TrafficClassifier::isRealHuman()) {
            DailyStats::incrementAtomic($today, 'pageviews', 1);
        } else {
            DailyStats::incrementAtomic($today, 'bot_pageviews', 1);
            $this->trackBotName($today);
        }
    }

    /**
     * Track the bot name for today's statistics.
     *
     * @param string $today
     * @return void
     */
    private function trackBotName(string $today): void
    {
        $botName = BotDetector::getBotName() ?: 'unknown';
        $botName = strtolower($botName);

        DailyStats::updateJsonMap($today, 'top_bots_json', [$botName => 1], self::MAX_BOT_NAMES);
    }
}