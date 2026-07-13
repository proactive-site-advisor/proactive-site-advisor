<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders;

use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotAgentSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\PageviewSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\TrafficEngine;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Records pageviews for valid requests.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders
 * @since   1.0.0
 */
class PageviewRecorder
{
    /** Maximum number of bot names to track. */
    private const MAX_BOT_NAMES = 30;

    /** Whether the recorder has already run. */
    private static bool $hasRun = false;

    /** Records pageview if request is valid. */
    public function maybeRecord(): void
    {
        if (self::$hasRun) {
            return;
        }

        self::$hasRun = true;

        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        $today = DateTimeUtils::todayKey();

        if (TrafficEngine::isHuman()) {
            DailyStats::incrementAtomic($today, 'pageviews', 1);
        } else {
            DailyStats::incrementAtomic($today, 'bot_pageviews', 1);
            $this->trackBotName($today);
        }
    }

    /** Tracks bot name for today's statistics. */
    private function trackBotName(string $today): void
    {
        $botName = BotAgentSignal::getBotName() ?: 'unknown';
        $botName = strtolower($botName);

        DailyStats::updateJsonMap($today, 'top_bots_json', [$botName => 1], self::MAX_BOT_NAMES);
    }
}