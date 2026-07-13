<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders;

use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\PageviewSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\TrafficEngine;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\Request;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Records 404 errors for valid requests.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders
 * @since   1.0.0
 */
class NotFoundRecorder
{
    /** Maximum number of paths to track. */
    private const MAX_PATHS = 30;

    /** Whether the recorder has already run. */
    private static bool $hasRun = false;

    /** Records 404 if request is valid. */
    public function maybeRecord(): void
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

        if (!TrafficEngine::shouldLog404()) {
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