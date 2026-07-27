<?php

namespace ProactiveSiteAdvisor\Services\Insights\Analyzers;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

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
        $spikePercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::ERROR_404_SPIKE_PERCENT),
            100
        );

        $spikeRatio = 1 + ($spikePercent / 100);

        if ($avg404 <= 0) {
            return [
                'type'       => null,
                'severity'   => null,
                'change_pct' => 0
            ];
        }

        $ratio  = $today404 / $avg404;
        $change = ($ratio - 1) * 100;

        if ($ratio > $spikeRatio) {
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