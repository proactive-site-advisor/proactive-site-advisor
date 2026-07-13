<?php

namespace ProactiveSiteAdvisor\Services\Insights;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects abnormal increases in 404 errors.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @since   1.0.0
 */
class Error404Analyzer
{
    /** Analyzes whether today's 404 errors exceed normal baseline levels. */
    public function analyze(int $today404, float $avg404): array
    {
        if ($avg404 <= 0) {
            return [
                'type'       => null,
                'severity'   => null,
                'change_pct' => 0
            ];
        }

        $ratio  = $today404 / $avg404;
        $change = ($ratio - 1) * 100;

        if ($ratio > 2) {
            $severity = $ratio >= 3 ? 'critical' : 'warning';

            return [
                'type'       => '404_spike',
                'severity'   => $severity,
                'change_pct' => round($change, 2)
            ];
        }

        return [
            'type'       => null,
            'severity'   => null,
            'change_pct' => round($change, 2)
        ];
    }
}