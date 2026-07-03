<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficCollector
 *
 * Collects frontend pageview counts using database storage.
 * Runs only on legitimate frontend requests, skipping admin, REST, AJAX,
 * cron, feed, and preview requests.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class TrafficCollector
{
    /**
     * Maximum number of bot names to keep in daily stats.
     */
    private const MAX_BOT_NAMES = 30;

    /**
     * Increment pageview count if this is a valid frontend request.
     *
     * @return void
     */
    public function maybeCountPageview(): void
    {
        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        $today = DateTimeUtils::todayKey();

        if ($this->isVisitor() && !$this->isAdvancedBot()) {
            DailyStats::incrementAtomic($today, 'pageviews', 1);
        } else {
            DailyStats::incrementAtomic($today, 'bot_pageviews', 1);
            $this->trackBotName($today);
        }
    }

    /**
     * Track the bot name for today's statistics.
     *
     * @param string $today Today's date in Ymd format.
     * @return void
     */
    private function trackBotName(string $today): void
    {
        $botName = BotDetector::getBotName() ?: 'unknown';
        $botName = strtolower($botName);

        DailyStats::updateJsonMap($today, 'top_bots_json', [$botName => 1], self::MAX_BOT_NAMES);
    }

    /**
     * Determine if the current request is from a real human user.
     * A request is considered real ONLY if all signals pass.
     *
     * @return bool
     */
    private function isVisitor(): bool
    {
        if (BotDetector::isBot()) {
            return false;
        }

        if (RequestRateSignal::isBotLike()) {
            return false;
        }

        if (!BrowserSignal::isBrowser()) {
            return false;
        }

        return true;
    }

    /**
     * Additional bot detection using new methods from other classes.
     * This does not replace isVisitor(), but complements it.
     *
     * @return bool
     */
    private function isAdvancedBot(): bool
    {
        if (BotDetector::isHeadless()) {
            return true;
        }

        if (BrowserSignal::isSuspicious()) {
            return true;
        }

        return false;
    }
}