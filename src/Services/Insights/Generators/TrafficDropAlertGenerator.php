<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a human traffic drop alert when pageviews fall below baseline.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class TrafficDropAlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    protected function getMetric(): string
    {
        return MetricConfig::METRIC_TRAFFIC;
    }

    /** {@inheritDoc} */
    protected function getDirection(): string
    {
        return MetricConfig::DIRECTION_DROP;
    }

    /** {@inheritDoc} */
    protected function isEnabled(): bool
    {
        return (bool)OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_TRAFFIC_DROP),
            1
        );
    }

    /** {@inheritDoc} */
    protected function resolveAlertType(string $direction): string
    {
        return 'traffic_drop';
    }

    /** {@inheritDoc} */
    protected function buildMeta(int $today, array $result, array $context): array
    {
        return $this->buildCommonMeta($today, $result);
    }
}