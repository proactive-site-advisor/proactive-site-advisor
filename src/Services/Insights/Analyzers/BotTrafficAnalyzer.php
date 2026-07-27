<?php

namespace ProactiveSiteAdvisor\Services\Insights\Analyzers;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects abnormal bot traffic changes.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @since   1.0.0
 */
class BotTrafficAnalyzer
{
    /** Analyze today's bot traffic against the baseline average. */
    public function analyze(int $todayBotPv, float $avgBotPv): array
    {
        $spikePercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::BOT_SPIKE_PERCENT),
            100
        );
        $dropPercent  = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::BOT_DROP_PERCENT),
            50
        );

        $spikeRatio = 1 + ($spikePercent / 100);
        $dropRatio  = 1 - ($dropPercent / 100);

        if ($avgBotPv <= 0) {
            return [
                'type'       => null,
                'severity'   => null,
                'change_pct' => 0,
            ];
        }

        $ratio  = $todayBotPv / $avgBotPv;
        $change = ($ratio - 1) * 100;

        if ($ratio > $spikeRatio) {
            return [
                'type'       => 'bot_spike',
                'severity'   => $ratio >= 3.5 ? 'critical' : 'warning',
                'change_pct' => round($change, 2),
            ];
        }

        if ($ratio < $dropRatio) {
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