<?php

namespace ProactiveSiteAdvisor\Services\Insights;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Error404Analyzer
 *
 * Detects abnormal increases in 404 errors by comparing today's 404 count with the historical baseline.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class Error404Analyzer
{
    /**
     * Analyzes whether today’s 404 errors exceed normal baseline levels.
     *
     * @param int $today404
     * @param float $avg404
     *
     * @return array
     */
    public function analyze(int $today404, float $avg404): array
    {
        if ($today404 <= 0) {
            return ['trigger' => false, 'change_pct' => null];
        }

        $trigger = false;

        if ($avg404 < 3 && $today404 >= 10) {
            $trigger = true;
        }

        if ($avg404 > 0 && $today404 > $avg404 * 2) {
            $trigger = true;
        }

        $pct = $avg404 > 0 ? (($today404 / $avg404) - 1) * 100 : null;

        return [
            'trigger'    => $trigger,
            'change_pct' => $pct ? round($pct, 2) : null
        ];
    }
}