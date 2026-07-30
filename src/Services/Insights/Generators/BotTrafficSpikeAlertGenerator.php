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
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, PluginSettings::BOT_SPIKE_PERCENT),
            100
        );

        $avgBotPv   = $context['avg_bot_pageviews'] ?? 0;
        $todayBotPv = $context['todayBotPv'] ?? 0;

        if (!$this->isSpikeEligible($avgBotPv, $todayBotPv, $spikePercent)) {
            return null;
        }

        $change   = round((($todayBotPv / $avgBotPv) - 1) * 100, 2);
        $severity = $this->calculateSeverity($change, $spikePercent);

        return [
            'type'       => 'bot_spike',
            'severity'   => $severity,
            'change_pct' => $change,
        ];
    }
}