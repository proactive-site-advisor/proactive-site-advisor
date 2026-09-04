<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a traffic spike alert when human pageviews exceed the threshold.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class TrafficSpikeAlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    public function isEligible(array $context): bool
    {
        $enabled = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_TRAFFIC_SPIKE),
            1
        );

        return $enabled && $this->passesTrafficEligibility($context);
    }

    /** {@inheritDoc} */
    public function generate(string $date, array $context): ?array
    {
        $spikePercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, PluginSettings::TRAFFIC_SPIKE_PERCENT),
            50
        );

        $spikeRatio = 1 + ($spikePercent / 100);
        $avgPv      = $context['avg_pageviews'] ?? 0;
        $todayPv    = $context['todayPv'] ?? 0;

        if ($avgPv <= 0 || $todayPv <= $avgPv * $spikeRatio) {
            return null;
        }

        $change   = round((($todayPv / $avgPv) - 1) * 100, 2);
        $severity = $this->calculateSpikeSeverity($change, $spikePercent);

        return [
            'type'     => 'traffic_spike',
            'severity' => $severity,
            'meta'     => [
                'today'      => $context['todayPv'],
                'avg7'       => (int)round($context['avg_pageviews']),
                'change_pct' => $change,
            ],
        ];
    }
}