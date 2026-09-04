<?php

namespace ProactiveSiteAdvisor\Builders;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds normalized alert data from raw alert data.
 *
 * @package ProactiveSiteAdvisor\Builders
 * @since   1.0.0
 */
class AlertBuilder
{
    /** Alert definitions loaded from data file. */
    private array $alerts = [];

    /** Load alert definitions. */
    public function __construct()
    {
        $file = PROACTIVE_SITE_ADVISOR_PATH . 'data/alerts.php';

        if (file_exists($file)) {
            $this->alerts = require $file;
        }
    }

    /** Build normalized alert data. */
    public function build(
        array $alert,
        int   $repetitionCount = 0,
        array $concurrentTypes = []
    ): array
    {
        $type     = $alert['type'];
        $severity = $alert['severity'];
        $meta     = $this->decodeMeta($alert['meta_json']);

        $meta['severity'] = $severity;

        return [
            'id'       => (int)($alert['id']),
            'type'     => $type,
            'severity' => $severity,
            'date'     => $alert['alert_date'],
            'label'    => $this->getLabel($type),
            'title'    => $this->getTitle($type, (float)($meta['change_pct'])),
            'short'    => $this->getShortMessage($type, $meta),
            'expanded' => $this->getExpandedContent(
                $type,
                $repetitionCount,
                $concurrentTypes,
                $meta
            ),
        ];
    }

    /** Get alert label. */
    private function getLabel(string $type): string
    {
        $customLabels = $this->alerts['badge_labels'];

        return $customLabels[$type];
    }

    /** Build alert title with change percentage. */
    private function getTitle(string $type, float $changePct): string
    {
        $abs = number_format_i18n(round(abs($changePct), 1), 1);

        $templates = $this->alerts['title_templates'];
        $template  = $templates[$type];

        return sprintf($template, $abs);
    }

    /** Get short message based on type and severity. */
    private function getShortMessage(string $type, array $meta): string
    {
        $level = $meta['severity'];

        return $this->alerts[$type]['short'][$level];
    }

    /** Build expanded alert content. */
    private function getExpandedContent(
        string $type,
        int    $repetitionCount,
        array  $concurrentTypes,
        array  $meta
    ): array
    {
        $level = $meta['severity'];

        $expanded = [
            'context'    => $this->getContext($type),
            'severity'   => $this->getSeverityExplanation($type, $level, $meta),
            'pattern'    => $this->getRepetitionText($repetitionCount),
            'concurrent' => $this->getConcurrencyText($concurrentTypes),
            'checks'     => $this->getChecks(
                $type,
                $level,
                $repetitionCount,
                $concurrentTypes
            ),
        ];

        if ($type === '404_spike' && !empty($meta['top'])) {
            $expanded['topUrls'] = $this->normalizeTop404Urls($meta['top']);
        }

        if (($type === 'bot_spike' || $type === 'bot_drop') && !empty($meta['top'])) {
            $expanded['topBots'] = $this->normalizeTopBotNames($meta['top']);
        }

        return $expanded;
    }

    /** Get alert context description. */
    private function getContext(string $type): string
    {
        return $this->alerts[$type]['context'];
    }

    /** Get severity explanation text and metrics. */
    private function getSeverityExplanation(string $type, string $level, array $meta): array
    {
        $avg7   = $meta['avg7'];
        $today  = $meta['today'];
        $change = $meta['change_pct'];

        $threshold = $this->getThresholdPercent($type);

        $text = sprintf(
            $this->alerts['severity_text'][$type][$level],
            $threshold
        );

        return [
            'text'    => $text,
            'metrics' => [
                'avg7'   => $avg7,
                'today'  => $today,
                'change' => $change,
            ],
        ];
    }

    /** Build checklist with priority ordering and limit to 4 items. */
    private function getChecks(
        string $type,
        string $level,
        int    $repetitionCount,
        array  $concurrentTypes
    ): array
    {
        $config = $this->alerts[$type]['checks'];
        $checks = [];

        if (!empty($concurrentTypes)) {
            $checks = array_merge(
                $checks,
                $this->getConcurrencyChecks($type, $concurrentTypes)
            );
        }

        if ($level !== 'info' && !empty($config[$level])) {
            $checks = array_merge($checks, $config[$level]);
        }

        if ($repetitionCount >= 3) {
            $checks[] = $this->alerts['common']['pattern_continue'];
        }

        $checks = array_merge($checks, $config['base']);

        return array_slice(array_values(array_unique($checks)), 0, 4);
    }

    /** Get repetition text based on count. */
    private function getRepetitionText(int $count): string
    {
        if ($count < 1) {
            return '';
        }

        if ($count === 1) {
            return ' ' . $this->alerts['common']['repetition_second_day'];
        }

        return ' ' . $this->alerts['common']['repetition_trend'];
    }

    /** Get concurrency text based on concurrent types. */
    private function getConcurrencyText(array $types): string
    {
        if (empty($types)) {
            return '';
        }

        $labels = array_map([$this, 'getLabel'], $types);

        return ' ' . sprintf(
                $this->alerts['common']['concurrent_with'],
                implode(', ', $labels)
            );
    }

    /** Get concurrency-specific checks. */
    private function getConcurrencyChecks(string $type, array $types): array
    {
        $checks = [];

        foreach ($types as $item) {
            if ($type === 'traffic_drop' && $item === '404_spike') {
                $checks[] = $this->alerts['common']['check_fix_broken_links'];
            }

            if ($type === 'traffic_spike' && $item === 'bot_spike') {
                $checks[] = $this->alerts['common']['check_automated_traffic'];
            }
        }

        return $checks;
    }

    /** Decode meta JSON string or array. */
    private function decodeMeta($meta): array
    {
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($meta)) {
            return $meta;
        }

        return [];
    }

    /** Normalize top 404 URLs meta. */
    private function normalizeTop404Urls(array $meta): array
    {
        $topUrls = [];

        foreach ($meta as $path => $count) {
            $topUrls[] = [
                'path'  => (string)$path,
                'count' => (int)$count,
            ];
        }

        return $topUrls;
    }

    /** Normalize top bot names meta. */
    private function normalizeTopBotNames(array $meta): array
    {
        $topBots = [];

        foreach ($meta as $name => $count) {
            $topBots[] = [
                'name'  => (string)$name,
                'count' => (int)$count,
            ];
        }

        return $topBots;
    }

    /**
     * Get threshold percentage for an alert type.
     */
    private function getThresholdPercent(string $type): float
    {
        $map = [
            'traffic_drop'  => PluginSettings::TRAFFIC_DROP_PERCENT,
            'traffic_spike' => PluginSettings::TRAFFIC_SPIKE_PERCENT,
            '404_spike'     => PluginSettings::ERROR_404_SPIKE_PERCENT,
            'bot_spike'     => PluginSettings::BOT_SPIKE_PERCENT,
            'bot_drop'      => PluginSettings::BOT_DROP_PERCENT,
        ];

        $key = $map[$type];

        $defaults = OptionUtils::getDefaults();
        $default  = $defaults[PluginSettings::SECTION_THRESHOLDS][$key];

        return OptionUtils::getOption(
            OptionUtils::makeKey(PluginSettings::SECTION_THRESHOLDS, $key),
            $default
        );
    }
}