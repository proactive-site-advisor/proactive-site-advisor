<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a traffic drop alert when human pageviews fall below the threshold.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class TrafficDropAlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    public function isEligible(array $context): bool
    {
        $enabled = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_TRAFFIC_DROP),
            1
        );

        return $enabled && $this->passesTrafficEligibility($context);
    }

    /** {@inheritDoc} */
    public function generate(string $date, array $context): ?array
    {
        $dropPercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::TRAFFIC_DROP_PERCENT),
            30
        );

        $dropRatio = 1 - ($dropPercent / 100);
        $avgPv     = $context['avg_pageviews'] ?? 0;
        $todayPv   = $context['todayPv'] ?? 0;

        if ($avgPv <= 0 || $todayPv >= $avgPv * $dropRatio) {
            return null;
        }

        $change = round((($todayPv / $avgPv) - 1) * 100, 2);

        return [
            'type'       => 'traffic_drop',
            'severity'   => abs($change) >= 40 ? 'critical' : 'warning',
            'change_pct' => $change,
        ];
    }
}