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
 * @version 1.0.3
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
        $bot      = 0;

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

            if ($type === '404_spike') {
                $error++;
            }

            if ($type === 'bot_spike' || $type === 'bot_drop') {
                $bot++;
            }
        }

        return [
            'critical_alerts' => $critical,
            'warning_alerts'  => $warning,
            'info_alerts'     => $info,
            'traffic_alerts'  => $traffic,
            'error_alerts'    => $error,
            'bot_alerts'      => $bot,
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
                'title'    => $this->getTitle($type, $meta),
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

            case 'bot_spike':
                return PrefixConfig::css('icon--bot-spike');

            case 'bot_drop':
                return PrefixConfig::css('icon--bot-drop');

            case '404_spike':
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

            case 'bot_spike':
                return __('Bot spike', 'proactive-site-advisor');

            case 'bot_drop':
                return __('Bot drop', 'proactive-site-advisor');

            case '404_spike':
                return __('404 spike', 'proactive-site-advisor');

            default:
                return __('Alert', 'proactive-site-advisor');
        }
    }

    /**
     * Generate alert title from meta data.
     *
     * @param string $type Alert type.
     * @param array $meta Meta data containing change_pct.
     *
     * @return string Generated title.
     */
    private function getTitle(string $type, array $meta): string
    {
        if ($type === 'traffic_drop') {
            /* translators: %s: The percentage value of traffic drop */
            return sprintf(__('Traffic dropped by %s%%', 'proactive-site-advisor'), abs($meta['change_pct'] ?? 0));
        }

        if ($type === 'traffic_spike') {
            /* translators: %s: The percentage value of traffic increase */
            return sprintf(__('Traffic increased by %s%%', 'proactive-site-advisor'), abs($meta['change_pct'] ?? 0));
        }

        if ($type === 'bot_spike') {
            return sprintf(
            /* translators: %s: percentage */
                __('Bot traffic increased by %s%%', 'proactive-site-advisor'),
                abs($meta['change_pct'] ?? 0)
            );
        }

        if ($type === 'bot_drop') {
            return sprintf(
            /* translators: %s: percentage */
                __('Bot traffic dropped by %s%%', 'proactive-site-advisor'),
                abs($meta['change_pct'] ?? 0)
            );
        }

        if ($type === '404_spike') {
            /* translators: %s: The percentage value of 404 error increase */
            return sprintf(__('404 errors increased by %s%%', 'proactive-site-advisor'), abs($meta['change_pct'] ?? 0));
        }

        return $this->getLabel($type);
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

            case 'bot_spike':
                return __('Unusual bot activity detected – possible scraping or indexing surge.', 'proactive-site-advisor');

            case 'bot_drop':
                return __('Bot visits dropped significantly – search engines may have reduced crawling.', 'proactive-site-advisor');

            case '404_spike':
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

            case 'bot_spike':
                return [
                    'meaning' => __('A sudden increase in bot traffic can indicate aggressive crawling or scraping attempts.', 'proactive-site-advisor'),
                    'checks'  => [
                        __('Check server load and bandwidth usage', 'proactive-site-advisor'),
                        __('Review robots.txt rules and crawling rate in Google Search Console', 'proactive-site-advisor'),
                        __('Consider blocking abusive IPs if the traffic is malicious', 'proactive-site-advisor'),
                    ],
                    'topBots' => $this->normalizeTopBotNames($meta['top'] ?? []),
                ];

            case 'bot_drop':
                return [
                    'meaning' => __('A significant drop in bot activity may signal that search engines are having trouble accessing your site.', 'proactive-site-advisor'),
                    'checks'  => [
                        __('Verify your site is accessible and not blocking crawlers unintentionally', 'proactive-site-advisor'),
                        __('Check Search Console for crawl errors', 'proactive-site-advisor'),
                        __('Ensure your XML sitemap is up-to-date', 'proactive-site-advisor'),
                    ],
                    'topBots' => $this->normalizeTopBotNames($meta['top'] ?? []),
                ];

            case '404_spike':
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
     * Normalize top bot names meta into an array of ['name' => string, 'count' => int].
     *
     * @param array $meta
     * @return array<int, array{name: string, count: int}>
     */
    private function normalizeTopBotNames(array $meta): array
    {
        $topBots = [];
        foreach ($meta as $item) {
            if (is_array($item) && isset($item[0], $item[1])) {
                $topBots[] = [
                    'name'  => (string)$item[0],
                    'count' => (int)$item[1],
                ];
            }
        }
        return $topBots;
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

        $totalPageviews    = 0;
        $totalBotPageviews = 0;
        $total404          = 0;
        $count             = count($rows);

        foreach ($rows as $row) {
            $totalPageviews    += $row['pageviews'];
            $totalBotPageviews += $row['bot_pageviews'];
            $total404          += $row['errors_404'];
        }

        return [
            'pageviews'     => (int)round($totalPageviews / $count),
            'bot_pageviews' => (int)round($totalBotPageviews / $count),
            'errors_404'    => (int)round($total404 / $count),
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
                'date'          => DateTimeUtils::format($row['stats_date'], 'F j, Y'),
                'pageviews'     => $row['pageviews'],
                'bot_pageviews' => $row['bot_pageviews'],
                'errors_404'    => $row['errors_404'],
            ];
        }

        return $formatted;
    }
}