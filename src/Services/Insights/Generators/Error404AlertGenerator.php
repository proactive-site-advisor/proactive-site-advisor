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
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::ERROR_404_SPIKE_PERCENT),
            100
        );

        $spikeRatio = 1 + ($spikePercent / 100);
        $avg404     = $context['avg_404'] ?? 0;
        $today404   = $context['today404'] ?? 0;

        if ($avg404 <= 0 || $today404 <= $avg404 * $spikeRatio) {
            return null;
        }

        $change = round((($today404 / $avg404) - 1) * 100, 2);

        return [
            'type'       => '404_spike',
            'severity'   => $today404 >= $avg404 * 3 ? 'critical' : 'warning',
            'change_pct' => $change,
        ];
    }
}