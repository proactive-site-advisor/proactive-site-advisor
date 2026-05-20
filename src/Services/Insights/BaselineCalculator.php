<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BaselineCalculator
 *
 * Computes the statistical baseline used by alert analyzers.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class BaselineCalculator
{
    /**
     * Fetches daily stats from DB.
     *
     * @var DailyStatsDataProvider
     */
    private DailyStatsDataProvider $dailyStatsDataProvider;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dailyStatsDataProvider = new DailyStatsDataProvider();
    }

    /**
     * Calculates average pageviews and 404 errors for the specified window before the given date.
     *
     * @param string $today
     * @param int $days
     *
     * @return array
     */
    public function calculate(string $today, int $days = 7): array
    {
        $rows = $this->dailyStatsDataProvider->getDailyStatsBeforeDate($today, $days);

        if (!$rows) {
            return [
                'count'         => 0,
                'avg_pageviews' => 0.0,
                'avg_404'       => 0.0
            ];
        }

        $count = count($rows);

        $sumPv  = 0;
        $sum404 = 0;

        foreach ($rows as $row) {
            $sumPv  += (int)$row['pageviews'];
            $sum404 += (int)$row['errors_404'];
        }

        return [
            'count'         => $count,
            'avg_pageviews' => $sumPv / $count,
            'avg_404'       => $sum404 / $count
        ];
    }
}
