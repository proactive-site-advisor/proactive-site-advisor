<?php

namespace ProactiveSiteAdvisor\Services\Insights\Config;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides per-metric configuration for anomaly detection.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Config
 * @since   1.0.0
 */
class MetricConfig
{
    /** Metric identifiers. */
    public const METRIC_TRAFFIC = 'traffic';
    public const METRIC_404     = '404';
    public const METRIC_BOT     = 'bot';

    /** Direction constants. */
    public const DIRECTION_SPIKE     = 'SPIKE';
    public const DIRECTION_DROP      = 'DROP';
    public const DIRECTION_TWO_SIDED = 'TWO_SIDED';

    /** Sensitivity level constants. */
    public const LEVEL_LOW    = 'low';
    public const LEVEL_NORMAL = 'normal';
    public const LEVEL_HIGH   = 'high';

    /** Alpha mapping per sensitivity level. */
    private const ALPHA_MAP = [
        self::LEVEL_LOW    => 0.001,
        self::LEVEL_NORMAL => 0.01,
        self::LEVEL_HIGH   => 0.05,
    ];

    /** minRel mapping per sensitivity level. */
    private const MIN_REL_MAP = [
        self::LEVEL_LOW    => 0.05,
        self::LEVEL_NORMAL => 0.02,
        self::LEVEL_HIGH   => 0.01,
    ];

    /** Direction per metric. */
    private const DIRECTION_MAP = [
        self::METRIC_TRAFFIC => self::DIRECTION_TWO_SIDED,
        self::METRIC_404     => self::DIRECTION_SPIKE,
        self::METRIC_BOT     => self::DIRECTION_TWO_SIDED,
    ];

    /** Baseline key per metric. */
    private const BASELINE_KEY_MAP = [
        self::METRIC_TRAFFIC => 'pageviews',
        self::METRIC_404     => 'errors_404',
        self::METRIC_BOT     => 'bot_pageviews',
    ];

    /** Today key per metric (context). */
    private const TODAY_KEY_MAP = [
        self::METRIC_TRAFFIC => 'todayPv',
        self::METRIC_404     => 'today404',
        self::METRIC_BOT     => 'todayBotPv',
    ];

    /** Get full configuration for a metric. */
    public static function get(string $metric): array
    {
        $level = self::getSensitivityLevel();

        return [
            'metric'         => $metric,
            'direction'      => self::DIRECTION_MAP[$metric],
            'alpha'          => self::ALPHA_MAP[$level],
            'min_rel'        => self::MIN_REL_MAP[$level],
            'auto_tune'      => self::isAutoTune(),
            'min_abs_manual' => self::getMinAbsManual($metric),
            'baseline_key'   => self::BASELINE_KEY_MAP[$metric],
            'today_key'      => self::TODAY_KEY_MAP[$metric],
        ];
    }

    /** Get baseline column name for a metric. */
    public static function baselineKey(string $metric): string
    {
        return self::BASELINE_KEY_MAP[$metric];
    }

    /** Get today context key for a metric. */
    public static function todayKey(string $metric): string
    {
        return self::TODAY_KEY_MAP[$metric];
    }

    /** Get sensitivity level from settings. */
    private static function getSensitivityLevel(): string
    {
        $level = OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_LEVEL),
            self::LEVEL_NORMAL
        );

        return in_array($level, [self::LEVEL_LOW, self::LEVEL_NORMAL, self::LEVEL_HIGH], true)
            ? $level
            : self::LEVEL_NORMAL;
    }

    /** Check if auto-tune is enabled. */
    private static function isAutoTune(): bool
    {
        return (bool)OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_AUTO_TUNE),
            true
        );
    }

    /** Get manual min_abs value for a metric. */
    private static function getMinAbsManual(string $metric): int
    {
        switch ($metric) {
            case self::METRIC_404:
                $key = PluginSettings::ERROR_404_MIN_ABS;
                break;
            case self::METRIC_BOT:
                $key = PluginSettings::BOT_MIN_ABS;
                break;
            case self::METRIC_TRAFFIC:
            default:
                $key = PluginSettings::TRAFFIC_MIN_ABS;
                break;
        }

        return (int)OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, $key)
        );
    }
}