<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Computes the statistical baseline used by alert analyzers.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @since   1.0.0
 */
class BaselineCalculator
{
    /** Fetches daily stats from DB. */
    private DailyStatsDataProvider $dailyStatsDataProvider;

    /** Constructor. */
    public function __construct()
    {
        $this->dailyStatsDataProvider = new DailyStatsDataProvider();
    }

    /** Calculates average pageviews and 404 errors for the specified window before the given date. */
    public function calculate(string $today, int $days = 7): array
    {
        $rows = $this->dailyStatsDataProvider->getDailyStatsBeforeDate($today, $days);

        if (!$rows) {
            return [
                'count'             => 0,
                'avg_pageviews'     => 0.0,
                'avg_404'           => 0.0,
                'avg_bot_pageviews' => 0.0,
            ];
        }

        $count = count($rows);

        $sumPv    = 0;
        $sum404   = 0;
        $sumBotPv = 0;

        foreach ($rows as $row) {
            $sumPv    += $row['pageviews'];
            $sum404   += $row['errors_404'];
            $sumBotPv += $row['bot_pageviews'];
        }

        return [
            'count'             => $count,
            'avg_pageviews'     => $sumPv / $count,
            'avg_404'           => $sum404 / $count,
            'avg_bot_pageviews' => $sumBotPv / $count,
        ];
    }
}