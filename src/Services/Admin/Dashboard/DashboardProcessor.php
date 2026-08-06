<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Services\Admin\AlertMessageBuilder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Processes raw alert data for dashboard display.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard
 * @since   1.0.0
 */
class DashboardProcessor
{
    /** Build digest statistics from raw alert rows. */
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

    /** Build alert cards for UI. */
    public function buildAlerts(array $alerts, array $repetitionData, array $concurrencyData): array
    {
        if (empty($alerts)) {
            return [];
        }

        usort($alerts, [$this, 'sortBySeverity']);

        $builder = new AlertMessageBuilder();
        $result  = [];

        foreach ($alerts as $alert) {
            $id       = $alert['id'];
            $type     = $alert['type'];
            $severity = $alert['severity'];
            $date     = $alert['alert_date'];
            $meta     = $this->decodeMeta($alert['meta_json']);

            $meta['severity'] = $severity;

            $repetitionCount = $repetitionData[$id] ?? 0;
            $concurrentTypes = $concurrencyData[$id] ?? [];

            $alertData = $builder->buildForDashboard(
                $type,
                $meta,
                $repetitionCount,
                $concurrentTypes
            );

            $expanded = $alertData['expanded'];

            if ($type === '404_spike' && !empty($meta['top'])) {
                $expanded['topUrls'] = $this->normalizeTop404Urls($meta['top']);
            }

            if (($type === 'bot_spike' || $type === 'bot_drop') && !empty($meta['top'])) {
                $expanded['topBots'] = $this->normalizeTopBotNames($meta['top']);
            }

            $result[] = [
                'id'       => $id,
                'icon'     => $this->getIcon($type),
                'color'    => $this->getColor($severity),
                'label'    => $alertData['label'],
                'title'    => $alertData['title'],
                'short'    => $alertData['short'],
                'expanded' => $expanded,
                'date'     => DateTimeUtils::format($date, 'F j, Y'),
            ];
        }

        return $result;
    }

    /** Sort alerts by severity (critical first). */
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

    /** Normalize "top 404" URLs meta. */
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

    /** Normalize top bot names meta. */
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

    /** Get icon class for alert type. */
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

    /** Get color for severity level. */
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

    /** Build average pageviews / errors_404 from history rows. */
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
            $totalPageviews    += (int)$row['pageviews'];
            $totalBotPageviews += (int)$row['bot_pageviews'];
            $total404          += (int)$row['errors_404'];
        }

        return [
            'pageviews'     => (int)round($totalPageviews / $count),
            'bot_pageviews' => (int)round($totalBotPageviews / $count),
            'errors_404'    => (int)round($total404 / $count),
        ];
    }

    /** Convert raw DB rows into table rows for template. */
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