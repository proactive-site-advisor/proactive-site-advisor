<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates a bot traffic drop alert when bot pageviews fall below the threshold.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
class BotTrafficDropAlertGenerator extends AbstractTrafficAlertGenerator
{
    /** {@inheritDoc} */
    public function isEligible(array $context): bool
    {
        $enabled = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_ALERTS, PluginSettings::ALERT_BOT_DROP),
            1
        );

        return $enabled && $this->passesTrafficEligibility($context);
    }

    /** {@inheritDoc} */
    public function generate(string $date, array $context): ?array
    {
        $dropPercent = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, PluginSettings::BOT_DROP_PERCENT),
            50
        );

        $avgBotPv   = $context['avg_bot_pageviews'] ?? 0;
        $todayBotPv = $context['todayBotPv'] ?? 0;

        if (!$this->isDropEligible($avgBotPv, $todayBotPv, $dropPercent)) {
            return null;
        }

        $change   = round((($todayBotPv / $avgBotPv) - 1) * 100, 2);
        $severity = $this->calculateSeverity($change, $dropPercent);

        return [
            'type'       => 'bot_drop',
            'severity'   => $severity,
            'change_pct' => $change,
        ];
    }
}