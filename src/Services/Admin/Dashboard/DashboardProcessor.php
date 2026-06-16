<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DashboardProcessor
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @version 1.0.1
 */
class DashboardProcessor
{
    /**
     * Build digest statistics from raw alert rows.
     *
     * @param array $rows
     *
     * @return array
     */
    public function buildDigest(array $rows): array
    {
        $critical = 0;
        $warning  = 0;
        $info     = 0;
        $traffic  = 0;
        $error    = 0;

        foreach ($rows as $row) {

            $severity = $row['severity'];
            $type     = $row['type'];

            if ($severity === 'critical') {
                $critical++;
            } elseif ($severity === 'warning') {
                $warning++;
            } else {
                $info++;
            }

            if ($severity === 'critical') {
                continue;
            }

            if ($type === 'traffic_drop' || $type === 'traffic_spike') {
                $traffic++;
            }

            if ($type === 'error_404_spike') {
                $error++;
            }
        }

        return [
            'critical_alerts' => $critical,
            'warning_alerts'  => $warning,
            'info_alerts'     => $info,
            'traffic_alerts'  => $traffic,
            'error_alerts'    => $error,
            'total_alerts'    => $critical + $warning + $info,
        ];
    }

    /**
     * Build alert cards for UI.
     *
     * @param array $alerts
     *
     * @return array
     */
    public function buildAlerts(array $alerts): array
    {
        if (empty($alerts)) {
            return [];
        }

        usort($alerts, [$this, 'sortBySeverity']);

        $result = [];

        foreach ($alerts as $alert) {

            $type     = $alert['type'];
            $severity = $alert['severity'];

            $meta = $this->decodeMeta($alert['meta_json']);

            $result[] = [
                'id'       => $alert['id'],
                'icon'     => $this->getIcon($type),
                'color'    => $this->getColor($severity),
                'label'    => $this->getLabel($type),
                'title'    => $alert['title'],
                'short'    => $this->getShortMessage($type),
                'expanded' => $this->getExpandedContent($type, $meta),
                'date'     => DateTimeUtils::format($alert['alert_date'], 'F j, Y'),
            ];
        }

        return $result;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    private function sortBySeverity($a, $b): int
    {
        $order = [
            'critical' => 0,
            'warning'  => 1,
            'info'     => 2,
        ];

        $aVal = $order[$a['severity']];
        $bVal = $order[$b['severity']];

        return $aVal - $bVal;
    }

    /**
     * @param $meta
     *
     * @return array
     */
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

    /**
     * @param $type
     *
     * @return string
     */
    private function getIcon($type): string
    {
        switch ($type) {

            case 'traffic_drop':
                return PrefixConfig::css('icon--traffic-drop');

            case 'traffic_spike':
                return PrefixConfig::css('icon--traffic-spike');

            case 'error_404_spike':
                return PrefixConfig::css('icon--error-404');

            default:
                return PrefixConfig::css('icon--alert');
        }
    }

    /**
     * @param $severity
     *
     * @return string
     */
    private function getColor($severity): string
    {
        switch ($severity) {

            case 'critical':
                return 'error';

            case 'warning':
                return 'warning';

            default:
                return 'info';
        }
    }

    /**
     * @param $type
     *
     * @return string|null
     */
    private function getLabel($type): ?string
    {
        switch ($type) {

            case 'traffic_drop':
                return __('Traffic drop', 'proactive-site-advisor');

            case 'traffic_spike':
                return __('Traffic spike', 'proactive-site-advisor');

            case 'error_404_spike':
                return __('404 spike', 'proactive-site-advisor');

            default:
                return __('Alert', 'proactive-site-advisor');
        }
    }

    /**
     * @param $type
     *
     * @return string|null
     */
    private function getShortMessage($type): ?string
    {
        switch ($type) {

            case 'traffic_drop':
                return __('Traffic dropped unexpectedly compared to recent days.', 'proactive-site-advisor');

            case 'traffic_spike':
                return __('Traffic increased significantly compared to recent days.', 'proactive-site-advisor');

            case 'error_404_spike':
                return __('Visitors are reaching pages that no longer exist.', 'proactive-site-advisor');

            default:
                return __('Unusual activity was detected.', 'proactive-site-advisor');
        }
    }

    /**
     * @param $type
     * @param array $meta
     *
     * @return array
     */
    private function getExpandedContent($type, array $meta): array
    {
        switch ($type) {

            case 'traffic_drop':
                return [
                    'meaning' => __('Sudden traffic drops are often caused by downtime or recent changes.', 'proactive-site-advisor'),
                    'checks'  => [
                        __('Check if your site is reachable', 'proactive-site-advisor'),
                        __('Review recent plugin or theme changes', 'proactive-site-advisor'),
                        __('Look for increases in 404 errors', 'proactive-site-advisor'),
                    ],
                ];

            case 'traffic_spike':
                return [
                    'meaning' => __('Traffic spikes can indicate viral content, marketing success, or bot activity.', 'proactive-site-advisor'),
                    'checks'  => [
                        __('Check analytics for sources', 'proactive-site-advisor'),
                        __('Review server load and performance', 'proactive-site-advisor'),
                        __('Look for unusual referrers', 'proactive-site-advisor'),
                    ],
                ];

            case 'error_404_spike':
                return [
                    'meaning' => __('Missing pages can frustrate visitors and affect SEO.', 'proactive-site-advisor'),
                    'checks'  => [
                        __('Add redirects for missing pages', 'proactive-site-advisor'),
                        __('Fix internal links pointing to missing URLs', 'proactive-site-advisor'),
                        __('Review permalink changes', 'proactive-site-advisor'),
                    ],
                    'topUrls' => $this->normalizeTop404Urls($meta['top'] ?? []),
                ];

            default:
                return [
                    'meaning' => __('This issue may require your attention.', 'proactive-site-advisor'),
                    'checks'  => [
                        __('Review recent changes to your site', 'proactive-site-advisor'),
                        __('Check your site for visible issues', 'proactive-site-advisor'),
                    ],
                ];
        }
    }

    /**
     * Normalize "top 404" URLs meta into an array of ['path' => string, 'count' => int].
     *
     * @param mixed $meta
     *
     * @return array<int, array{path: string, count: int}>
     */
    private function normalizeTop404Urls(array $meta): array
    {
        $topUrls = [];

        foreach ($meta as $item) {
            if (is_array($item) && isset($item[0], $item[1])) {
                $topUrls[] = [
                    'path'  => (string)$item[0],
                    'count' => (int)$item[1],
                ];
            }
        }

        return $topUrls;
    }

    /**
     * Build average pageviews / errors_404 from history rows.
     *
     * @param array<int, array{pageviews:int, errors_404:int}> $rows
     *
     * @return array{pageviews:int, errors_404:int}|null
     */
    public function calculateHistoryAverage(array $rows): ?array
    {
        if (empty($rows)) {
            return null;
        }

        $totalPageviews = 0;
        $total404       = 0;
        $count          = count($rows);

        foreach ($rows as $row) {
            $totalPageviews += $row['pageviews'];
            $total404       += $row['errors_404'];
        }

        return [
            'pageviews'  => (int)round($totalPageviews / $count),
            'errors_404' => (int)round($total404 / $count),
        ];
    }

    /**
     * Convert raw DB rows into table rows for template.
     *
     * @param array<int, array<string,mixed>> $rows
     *
     * @return array
     */
    public function formatHistoryRows(array $rows): array
    {
        $formatted = [];

        foreach ($rows as $row) {
            $formatted[] = [
                'date'       => DateTimeUtils::format($row['stats_date'], 'F j, Y'),
                'pageviews'  => $row['pageviews'],
                'errors_404' => $row['errors_404'],
            ];
        }

        return $formatted;
    }
}