<?php

namespace ProactiveSiteAdvisor\Services\Insights\Analyzers;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects abnormal traffic changes.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @since   1.0.0
 */
class TrafficAnalyzer
{
    /** Analyzes today's traffic against the baseline average. */
    public function analyze(int $todayPv, float $avgPv): array
    {
        $spikePercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::TRAFFIC_SPIKE_PERCENT),
            50
        );
        $dropPercent  = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::TRAFFIC_DROP_PERCENT),
            30
        );

        $spikeRatio = 1 + ($spikePercent / 100);
        $dropRatio  = 1 - ($dropPercent / 100);

        if ($avgPv <= 0) {
            return ['type' => null, 'severity' => null, 'change_pct' => 0];
        }

        $ratio  = $todayPv / $avgPv;
        $change = ($ratio - 1) * 100;

        if ($ratio < $dropRatio) {
            return [
                'type'       => 'traffic_drop',
                'severity'   => abs($change) >= 40 ? 'critical' : 'warning',
                'change_pct' => round($change, 2)
            ];
        }

        if ($ratio > $spikeRatio) {
            return [
                'type'       => 'traffic_spike',
                'severity'   => 'info',
                'change_pct' => round($change, 2)
            ];
        }

        return ['type' => null, 'severity' => null, 'change_pct' => round($change, 2)];
    }
}