<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a 404 error surge alert when errors exceed the threshold.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class Error404AlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    public function isEligible(array $context): bool
    {
        $enabled = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_404_SPIKE),
            1
        );

        return $enabled && $this->passesTrafficEligibility($context);
    }

    /** {@inheritDoc} */
    public function generate(string $date, array $context): ?array
    {
        $spikePercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, PluginSettings::ERROR_404_SPIKE_PERCENT),
            100
        );

        $avg404   = $context['avg_404'] ?? 0;
        $today404 = $context['today404'] ?? 0;

        if (!$this->isSpikeEligible($avg404, $today404, $spikePercent)) {
            return null;
        }

        $change   = round((($today404 / $avg404) - 1) * 100, 2);
        $severity = $this->calculateSeverity($change, $spikePercent);

        $top = $context['top404'];
        arsort($top);
        $top = array_slice($top, 0, 3, true);

        return [
            'type'     => '404_spike',
            'severity' => $severity,
            'meta' => [
                'today'      => $context['today404'],
                'avg7'       => (int)round($context['avg_404']),
                'change_pct' => $change,
                'top'        => $top,
            ],
        ];
    }
}