<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Utils\Request;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NotFoundTracker
 *
 * Tracks 404 errors on frontend requests using database storage.
 * Stores total count and a pruned map of paths that triggered 404s.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class NotFoundTracker
{
    /**
     * Maximum number of paths to keep in the map.
     */
    private const MAX_PATHS = 30;

    /**
     * Track 404 if this is a valid 404 request.
     *
     * @return void
     */
    public function maybeTrack404(): void
    {
        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        if (!is_404()) {
            return;
        }

        if (BotDetector::isBot()) {
            return;
        }

        if (RequestRateSignal::isBotLike()) {
            return;
        }

        if (!BrowserSignal::isBrowser()) {
            return;
        }

        if ($this->isAdvancedBot()) {
            return;
        }

        $today = DateTimeUtils::todayKey();

        DailyStats::incrementAtomic($today, 'errors_404', 1);

        $path = Request::getRequestPath();
        if ($path === '') {
            return;
        }

        DailyStats::updateJsonMap($today, 'top_404_json', [$path => 1], self::MAX_PATHS);
    }

    /**
     * Additional bot detection using new methods from other classes.
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