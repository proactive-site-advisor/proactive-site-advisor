<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DisplayUtils.
 *
 * Utility class for rendering display strings used in the plugin.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.4
 */
class DisplayUtils
{
    /**
     * Render an admin-style severity badge for alert counts.
     *
     * @param int $count
     * @param string $severity
     *
     * @return string
     */
    public static function renderSeverityBadge(int $count, string $severity): string
    {
        if ($count <= 0) {
            return '';
        }

        $colors = [
            'critical' => '#ff4c51',
            'warning'  => '#ff9f43',
            'info'     => '#00bad1',
        ];

        $color = $colors[$severity] ?? '#3b82f6';

        return sprintf(
            '<span class="update-plugins count-%1$d" style="background-color:%2$s;"><span class="plugin-count">%1$d</span></span>',
            $count,
            esc_attr($color)
        );
    }

    /**
     * Render history average text.
     *
     * @param int $pageviews
     * @param int $botPageviews
     * @param int $errors
     *
     * @return string
     */
    public static function renderHistoryAverage(int $pageviews, int $botPageviews, int $errors): string
    {
        $parts = [];

        if ($pageviews > 0) {
            $parts[] = sprintf(
            /* translators: %s: average pageviews count */
                __('%s pageviews', 'proactive-site-advisor'),
                '<strong>' . esc_html(number_format_i18n($pageviews)) . '</strong>'
            );
        }

        if ($botPageviews > 0) {
            $parts[] = sprintf(
            /* translators: %s: average bot pageviews count */
                __('%s bot pageviews', 'proactive-site-advisor'),
                '<strong>' . esc_html(number_format_i18n($botPageviews)) . '</strong>'
            );
        }

        if ($errors > 0) {
            $parts[] = sprintf(
            /* translators: %s: average 404 errors count */
                __('%s page errors (404)', 'proactive-site-advisor'),
                '<strong>' . esc_html(number_format_i18n($errors)) . '</strong>'
            );
        }

        if (empty($parts)) {
            return esc_html__('Average per day: No data yet', 'proactive-site-advisor');
        }

        return sprintf(
        /* translators: %s: list of average stats separated by middot */
            esc_html__('Average per day: %s', 'proactive-site-advisor'),
            implode(' · ', $parts)
        );
    }
}