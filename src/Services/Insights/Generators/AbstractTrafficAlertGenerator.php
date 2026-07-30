<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Services\Insights\Contracts\AlertGeneratorInterface;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base class for traffic‑related alert generators.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
abstract class AbstractTrafficAlertGenerator implements AlertGeneratorInterface
{
    /** Check common traffic eligibility thresholds. */
    protected function passesTrafficEligibility(array $context): bool
    {
        $minWeeklyAvg = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, PluginSettings::MIN_WEEKLY_AVG),
            3
        );

        $minPageviews = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, PluginSettings::MIN_PAGEVIEWS_FOR_ALERT),
            10
        );

        $avgPageviews = $context['avg_pageviews'] ?? 0;
        $todayPv      = $context['todayPv'] ?? 0;
        $count        = $context['count'] ?? 0;

        return $count >= 7 && $avgPageviews >= $minWeeklyAvg && $todayPv >= $minPageviews;
    }

    /** Check if a drop condition is met. */
    protected function isDropEligible(float $avg, float $today, int $dropPercent): bool
    {
        if ($avg <= 0) {
            return false;
        }

        $dropRatio = 1 - ($dropPercent / 100);

        return $today < $avg * $dropRatio;
    }

    /** Check if a spike condition is met. */
    protected function isSpikeEligible(float $avg, float $today, int $spikePercent): bool
    {
        if ($avg <= 0) {
            return false;
        }

        $spikeRatio = 1 + ($spikePercent / 100);

        return $today > $avg * $spikeRatio;
    }

    /** Calculate severity based on change relative to user threshold. */
    protected function calculateSeverity(float $changePercent, float $thresholdPercent): string
    {
        $absChange        = abs($changePercent);
        $ratioToThreshold = $absChange / $thresholdPercent;

        return $ratioToThreshold >= 2 ? 'critical' : 'warning';
    }
}