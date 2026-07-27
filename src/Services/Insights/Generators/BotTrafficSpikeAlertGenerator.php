<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a bot traffic spike alert when bot pageviews exceed the threshold.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class BotTrafficSpikeAlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    public function isEligible(array $context): bool
    {
        $enabled = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_BOT_SPIKE),
            1
        );

        return $enabled && $this->passesTrafficEligibility($context);
    }

    /** {@inheritDoc} */
    public function generate(string $date, array $context): ?array
    {
        $spikePercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERT_CONDITIONS, PluginSettings::BOT_SPIKE_PERCENT),
            100
        );

        $spikeRatio = 1 + ($spikePercent / 100);
        $avgBotPv   = $context['avg_bot_pageviews'] ?? 0;
        $todayBotPv = $context['todayBotPv'] ?? 0;

        if ($avgBotPv <= 0 || $todayBotPv <= $avgBotPv * $spikeRatio) {
            return null;
        }

        $change = round((($todayBotPv / $avgBotPv) - 1) * 100, 2);

        return [
            'type'       => 'bot_spike',
            'severity'   => $todayBotPv >= $avgBotPv * 3.5 ? 'critical' : 'warning',
            'change_pct' => $change,
        ];
    }
}