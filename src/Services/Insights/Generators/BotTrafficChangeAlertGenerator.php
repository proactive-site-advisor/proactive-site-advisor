<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a bot traffic change alert (spike or drop).
 *
 * A single generator handles both directions and resolves the final
 * alert type based on the detected anomaly direction.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class BotTrafficChangeAlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    protected function getMetric(): string
    {
        return MetricConfig::METRIC_BOT;
    }

    /** {@inheritDoc} */
    protected function getDirection(): string
    {
        return MetricConfig::DIRECTION_TWO_SIDED;
    }

    /** {@inheritDoc} */
    protected function isEnabled(): bool
    {
        return (bool)OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_BOT_CHANGE),
            1
        );
    }

    /** {@inheritDoc} */
    protected function resolveAlertType(string $direction): string
    {
        return $direction === MetricConfig::DIRECTION_DROP ? 'bot_drop' : 'bot_spike';
    }

    /** {@inheritDoc} */
    protected function buildMeta(int $today, array $result, array $context): array
    {
        $meta        = $this->buildCommonMeta($today, $result);
        $meta['top'] = $this->topN($context['topBots']);

        return $meta;
    }
}