<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard\Data;

use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\PluginStatus;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prepares dashboard status data for display.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard\Data
 * @since   1.0.0
 */
class DashboardStatus
{
    /** Returns the dashboard top status line text. */
    public function getStatusLine(int $daysWithData): string
    {
        $status = PluginStatus::getStatus($daysWithData);

        if ($status === PluginStatus::STATUS_FRESH) {
            return __('Not checked yet · Monitoring starting', 'proactive-site-advisor');
        }

        if ($status === PluginStatus::STATUS_LIMITED) {
            return __('Collecting baseline data · Alerts will become available soon', 'proactive-site-advisor');
        }

        if ($status === PluginStatus::STATUS_ISSUE) {
            return __('Last checked: over 24 hours ago · Check monitoring status', 'proactive-site-advisor');
        }

        return $this->getNormalStatusLine();
    }

    /** Returns the colored dashboard status block (severity or plugin-status). */
    public function getStatus(int $daysWithData, array $digestStats): array
    {
        $status = PluginStatus::getStatus($daysWithData);

        if ($status !== PluginStatus::STATUS_NORMAL) {
            return $this->getPluginStatus($status, $daysWithData);
        }

        return $this->getSeverityStatus($digestStats);
    }

    /** Returns the block for plugin-level status (fresh, limited, issue). */
    private function getPluginStatus(string $status, int $daysWithData): array
    {
        switch ($status) {

            case PluginStatus::STATUS_FRESH:
                return [
                    'color' => 'info',
                    'title' => __('Getting started', 'proactive-site-advisor'),
                    'text'  => __("We're collecting baseline data. Alerts will become available once enough history has been collected.", 'proactive-site-advisor'),
                ];

            case PluginStatus::STATUS_LIMITED:
                $percentage = min(100, round(($daysWithData / PluginStatus::BASELINE_DAYS) * 100));

                return [
                    'color'    => 'info',
                    'title'    => __('Building history', 'proactive-site-advisor'),
                    'text'     => sprintf(
                    /* translators: 1: Current day number, 2: Total number of baseline days required */
                        __('Collecting baseline data (Day %1$d of %2$d). Monitoring is active. More history is needed before alerts can be generated.', 'proactive-site-advisor'),
                        $daysWithData,
                        PluginStatus::BASELINE_DAYS
                    ),
                    'progress' => $percentage,
                ];

            case PluginStatus::STATUS_ISSUE:
                return [
                    'color' => 'warning',
                    'title' => __('Check monitoring', 'proactive-site-advisor'),
                    'text'  => __('Last check was over 24 hours ago. Verify your site cron is running.', 'proactive-site-advisor'),
                ];
        }

        return [];
    }

    /** Returns status block based on alert severity levels. */
    private function getSeverityStatus(array $stats): array
    {
        $critical = $stats['critical_alerts'] ?? 0;
        $warning  = $stats['warning_alerts'] ?? 0;
        $info     = $stats['info_alerts'] ?? 0;

        if ($critical > 0) {
            return [
                'color' => 'error',
                'title' => __('Critical issues detected', 'proactive-site-advisor'),
                'text'  => sprintf(
                /* translators: %d: Number of critical issues detected in the last 7 days */
                    _n(
                        '%d critical issue detected in the last 7 days.',
                        '%d critical issues detected in the last 7 days.',
                        $critical,
                        'proactive-site-advisor'
                    ),
                    $critical
                ),
            ];
        }

        if ($warning > 0) {
            return [
                'color' => 'warning',
                'title' => __('Warnings detected', 'proactive-site-advisor'),
                'text'  => sprintf(
                /* translators: %d: Number of warnings detected in the last 7 days */
                    _n(
                        '%d warning detected in the last 7 days.',
                        '%d warnings detected in the last 7 days.',
                        $warning,
                        'proactive-site-advisor'
                    ),
                    $warning
                ),
            ];
        }

        if ($info > 0) {
            return [
                'color' => 'info',
                'title' => __('Notices detected', 'proactive-site-advisor'),
                'text'  => sprintf(
                /* translators: %d: Number of notices recorded in the last 7 days */
                    _n(
                        '%d notice recorded in the last 7 days.',
                        '%d notices recorded in the last 7 days.',
                        $info,
                        'proactive-site-advisor'
                    ),
                    $info
                ),
            ];
        }

        return [
            'color' => 'success',
            'title' => __('All clear', 'proactive-site-advisor'),
            'text'  => __("No unusual activity detected in the last 7 days. We'll keep monitoring and surface issues with recommended actions.", 'proactive-site-advisor'),
        ];
    }

    /** Returns the status line for normal operating mode. */
    private function getNormalStatusLine(): string
    {
        $lastRun = PluginStatus::getLastRunTimestamp();
        $now     = DateTimeUtils::timestamp();
        $timeAgo = human_time_diff($lastRun, $now);

        /* translators: %s: Time ago string */
        return sprintf(__('Last checked: %s ago · Baseline: last 7 days', 'proactive-site-advisor'), $timeAgo);
    }
}