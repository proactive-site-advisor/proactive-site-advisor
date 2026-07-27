<?php

namespace ProactiveSiteAdvisor\Services\Insights\Config;

use ProactiveSiteAdvisor\Services\Insights\Generators\BotTrafficDropAlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\BotTrafficSpikeAlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\Error404AlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\TrafficDropAlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\TrafficSpikeAlertGenerator;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuration for alert generators.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Config
 * @since   1.0.0
 */
class AlertGeneratorConfig
{
    /** Returns list of alert generator classes. */
    public static function getGenerators(): array
    {
        $generators = [
            TrafficDropAlertGenerator::class,
            TrafficSpikeAlertGenerator::class,
            Error404AlertGenerator::class,
            BotTrafficSpikeAlertGenerator::class,
            BotTrafficDropAlertGenerator::class,
        ];

        /**
         * Filters the list of alert generator classes.
         *
         * @param string[] $generators Array of fully qualified class names.
         * @since  1.0.0
         */
        return apply_filters('proactive_site_advisor_alert_generators', $generators);
    }
}