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
 * @version 1.0.0
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
     * @param int $errors
     *
     * @return string
     */
    public static function renderHistoryAverage(int $pageviews, int $errors): string
    {
        return sprintf(
        /* translators: 1: average pageviews, 2: average 404 errors */
            esc_html__('Average per day: %1$s pageviews · %2$s page errors (404)', 'proactive-site-advisor'),
            '<strong>' . esc_html(number_format_i18n($pageviews)) . '</strong>',
            '<strong>' . esc_html(number_format_i18n($errors)) . '</strong>'
        );
    }
}