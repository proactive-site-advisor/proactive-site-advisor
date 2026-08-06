<?php

namespace ProactiveSiteAdvisor\Services\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds dynamic alert messages for dashboard, email, and widgets.
 *
 * @package ProactiveSiteAdvisor\Services\Admin
 * @since   1.0.0
 */
class AlertMessageBuilder
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

    /** Build full alert data for dashboard. */
    public function buildForDashboard(
        string $type,
        array  $meta,
        int    $repetitionCount,
        array  $concurrentTypes
    ): array
    {
        $changePct = $meta['change_pct'];

        return [
            'label'    => $this->getLabel($type),
            'title'    => $this->getTitle($type, $changePct),
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

    /** Build alert title with severity label. */
    private function getTitle(string $type, float $changePct): string
    {
        $abs = round(abs($changePct), 1);

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

    /** Build expanded content. */
    private function getExpandedContent(
        string $type,
        int    $repetitionCount,
        array  $concurrentTypes,
        array  $meta
    ): array
    {
        $level = $meta['severity'];

        return [
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
    }

    /** Get alert context description. */
    private function getContext(string $type): string
    {
        return $this->alerts[$type]['context'];
    }

    /** Get severity explanation text. */
    private function getSeverityExplanation(string $type, string $level, array $meta): array
    {
        $avg7   = $meta['avg7'];
        $today  = $meta['today'];
        $change = $meta['change_pct'];

        return [
            'text'    => $this->alerts['severity_text'][$type][$level],
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
            $checks = array_merge($checks, $this->getConcurrencyChecks($type, $concurrentTypes));
        }

        if ($level !== 'info') {
            $severityChecks = $config[$level];
            $checks         = array_merge($checks, $severityChecks);
        }

        if ($repetitionCount >= 3) {
            $checks[] = $this->alerts['common']['pattern_continue'];
        }

        $baseChecks = $config['base'];
        $checks     = array_merge($checks, $baseChecks);

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
}