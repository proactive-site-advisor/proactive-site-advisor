<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Computes the statistical baseline used by alert analyzers.
 *
 * Uses a 7-day window with robust statistics (median, MAD)
 * and count-aware noise floors (Poisson, relative, absolute).
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

    /** Calculates baseline statistics for the specified window before the given date. */
    public function calculate(string $today, int $days = 7): array
    {
        $rows = $this->dailyStatsDataProvider->getDailyStatsBeforeDate($today, $days);

        if (!$rows) {
            return [
                'count'         => 0,
                'pageviews'     => $this->emptyStats(),
                'errors_404'    => $this->emptyStats(),
                'bot_pageviews' => $this->emptyStats(),
            ];
        }

        return [
            'count'         => count($rows),
            'pageviews'     => $this->computeStats($rows, 'pageviews'),
            'errors_404'    => $this->computeStats($rows, 'errors_404'),
            'bot_pageviews' => $this->computeStats($rows, 'bot_pageviews'),
        ];
    }

    /** Compute statistics for a single metric column. */
    private function computeStats(array $rows, string $column): array
    {
        $values = [];
        $sum    = 0;

        foreach ($rows as $row) {
            $value    = (float)($row[$column] ?? 0);
            $values[] = $value;
            $sum      += $value;
        }

        $count  = count($values);
        $avg    = $count > 0 ? $sum / $count : 0.0;
        $median = $this->median($values);

        $deviations = [];
        foreach ($values as $value) {
            $deviations[] = abs($value - $median);
        }
        $mad = $this->median($deviations);

        $smad  = 1.4826 * $mad;
        $spois = ($median < 20) ? sqrt(max($median, 0.5)) : 0.0;
        $srel  = max(0.01 * $median, 1.0);
        $s     = max($smad, $spois, $srel, 1.0);

        return [
            'avg'    => $avg,
            'median' => $median,
            'mad'    => $mad,
            'smad'   => $smad,
            'spois'  => $spois,
            'srel'   => $srel,
            's'      => $s,
        ];
    }

    /** Compute median of an array of floats. */
    private function median(array $values): float
    {
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        sort($values);

        $mid = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float)$values[$mid];
        }

        return ((float)$values[$mid - 1] + (float)$values[$mid]) / 2;
    }

    /** Empty stats structure for the no-data case. */
    private function emptyStats(): array
    {
        return [
            'avg'    => 0.0,
            'median' => 0.0,
            'mad'    => 0.0,
            'smad'   => 0.0,
            'spois'  => 0.0,
            'srel'   => 1.0,
            's'      => 1.0,
        ];
    }
}