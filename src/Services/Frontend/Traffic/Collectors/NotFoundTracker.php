<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Collectors;

use ProactiveSiteAdvisor\Utils\Request;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\PageviewSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Decision\TrafficClassifier;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NotFoundTracker
 *
 * Tracks 404 errors on frontend requests using database storage.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Collectors
 * @version 1.0.0
 */
class NotFoundTracker
{
    /**
     * Maximum number of paths to keep in the map.
     */
    private const MAX_PATHS = 30;

    /**
     * Prevents duplicate tracking per request.
     *
     * @var bool
     */
    private static bool $hasRun = false;

    /**
     * Track 404 if this is a valid 404 request.
     *
     * @return void
     */
    public function maybeTrack404(): void
    {
        if (self::$hasRun) {
            return;
        }

        self::$hasRun = true;

        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        if (!is_404()) {
            return;
        }

        if (!TrafficClassifier::shouldTrack404()) {
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
}