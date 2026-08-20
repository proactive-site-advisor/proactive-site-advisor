<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Processes dashboard-specific data for display.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @since   1.0.0
 */
class DashboardProcessor
{
    /** Build average pageviews / errors_404 from history rows. */
    public function calculateHistoryAverage(array $rows): ?array
    {
        if (empty($rows)) {
            return null;
        }

        $totalPageviews    = 0;
        $totalBotPageviews = 0;
        $total404          = 0;
        $count             = count($rows);

        foreach ($rows as $row) {
            $totalPageviews    += (int)$row['pageviews'];
            $totalBotPageviews += (int)$row['bot_pageviews'];
            $total404          += (int)$row['errors_404'];
        }

        return [
            'pageviews'     => (int)round($totalPageviews / $count),
            'bot_pageviews' => (int)round($totalBotPageviews / $count),
            'errors_404'    => (int)round($total404 / $count),
        ];
    }

    /** Convert raw DB rows into table rows for template. */
    public function formatHistoryRows(array $rows): array
    {
        $formatted = [];

        foreach ($rows as $row) {
            $formatted[] = [
                'date'          => DateTimeUtils::format($row['stats_date'], 'F j, Y'),
                'pageviews'     => $row['pageviews'],
                'bot_pageviews' => $row['bot_pageviews'],
                'errors_404'    => $row['errors_404'],
            ];
        }

        return $formatted;
    }
}