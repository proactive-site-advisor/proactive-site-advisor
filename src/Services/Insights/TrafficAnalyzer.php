<?php

namespace ProactiveSiteAdvisor\Services\Insights;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficAnalyzer
 *
 * Detects abnormal traffic changes by comparing today's pageviews with the historical baseline average.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class TrafficAnalyzer
{
    /**
     * Analyzes today's traffic against the baseline average.
     *
     * @param int $todayPv
     * @param float $avgPv
     *
     * @return array
     */
    public function analyze(int $todayPv, float $avgPv): array
    {
        if ($avgPv <= 0) {
            return ['type' => null, 'severity' => null, 'change_pct' => 0];
        }

        $ratio  = $todayPv / $avgPv;
        $change = ($ratio - 1) * 100;

        if ($ratio < 0.7) {
            return [
                'type'       => 'traffic_drop',
                'severity'   => abs($change) >= 40 ? 'critical' : 'warning',
                'change_pct' => round($change, 2)
            ];
        }

        if ($ratio > 1.5) {
            return [
                'type'       => 'traffic_spike',
                'severity'   => 'info',
                'change_pct' => round($change, 2)
            ];
        }

        return ['type' => null, 'severity' => null, 'change_pct' => round($change, 2)];
    }
}