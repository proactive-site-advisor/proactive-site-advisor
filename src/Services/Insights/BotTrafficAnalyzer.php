<?php

namespace ProactiveSiteAdvisor\Services\Insights;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BotTrafficAnalyzer
 *
 * Detects abnormal bot traffic changes by comparing today's bot
 * pageviews against the historical baseline average.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @version 1.0.0
 */
class BotTrafficAnalyzer
{
    /**
     * Analyze today's bot traffic against the baseline average.
     *
     * @param int $todayBotPv Bot pageviews today.
     * @param float $avgBotPv Average daily bot pageviews (baseline).
     *
     * @return array
     */
    public function analyze(int $todayBotPv, float $avgBotPv): array
    {
        if ($avgBotPv <= 0) {
            return [
                'type'       => null,
                'severity'   => null,
                'change_pct' => 0,
            ];
        }

        $ratio  = $todayBotPv / $avgBotPv;
        $change = ($ratio - 1) * 100;

        if ($ratio > 2.0) {
            return [
                'type'       => 'bot_spike',
                'severity'   => $ratio >= 3.5 ? 'critical' : 'warning',
                'change_pct' => round($change, 2),
            ];
        }

        if ($ratio < 0.5) {
            return [
                'type'       => 'bot_drop',
                'severity'   => $ratio <= 0.3 ? 'critical' : 'warning',
                'change_pct' => round($change, 2),
            ];
        }

        return [
            'type'       => null,
            'severity'   => null,
            'change_pct' => round($change, 2),
        ];
    }
}