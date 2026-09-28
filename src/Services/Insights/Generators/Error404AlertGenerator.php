<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a 404 error surge alert when errors rise above baseline.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class Error404AlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    protected function getMetric(): string
    {
        return MetricConfig::METRIC_404;
    }

    /** {@inheritDoc} */
    protected function getDirection(): string
    {
        return MetricConfig::DIRECTION_SPIKE;
    }

    /** {@inheritDoc} */
    protected function isEnabled(): bool
    {
        return (bool)OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_404_SPIKE),
            1
        );
    }

    /** {@inheritDoc} */
    protected function resolveAlertType(string $direction): string
    {
        return '404_spike';
    }

    /** {@inheritDoc} */
    protected function buildMeta(int $today, array $result, array $context): array
    {
        $meta        = $this->buildCommonMeta($today, $result);
        $meta['top'] = $this->topN($context['top404']);

        return $meta;
    }
}