<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard\Data;

use ProactiveSiteAdvisor\Builders\AlertBuilder;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\PluginStatus;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prepares dashboard alerts data for display.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard\Data
 * @since   1.0.0
 */
class DashboardAlerts
{
    /** Alert builder instance. */
    private AlertBuilder $alertBuilder;

    /** Alerts data provider instance. */
    private AlertsDataProvider $alertsDataProvider;

    /** Constructor. */
    public function __construct()
    {
        $this->alertBuilder       = new AlertBuilder();
        $this->alertsDataProvider = new AlertsDataProvider();
    }

    /** Get latest alerts for dashboard. */
    public function getAlerts(int $daysWithData): array
    {
        $status = PluginStatus::getStatus($daysWithData);

        if ($status === PluginStatus::STATUS_FRESH) {
            return [
                'hasData' => false,
                'title'   => __('Getting started', 'proactive-site-advisor'),
                'text'    => __("We're collecting baseline data for your site.", 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--info'),
                'color'   => 'info',
            ];
        }

        if ($status === PluginStatus::STATUS_LIMITED) {
            return [
                'hasData' => false,
                'title'   => __('Limited history', 'proactive-site-advisor'),
                'text'    => __('Monitoring is active, but more history is needed before unusual activity can be detected.', 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--clock'),
                'color'   => 'warning',
            ];
        }

        $rawAlerts = $this->alertsDataProvider->getLatestAlerts(20);

        if (empty($rawAlerts)) {
            return [
                'hasData' => false,
                'title'   => __('All clear', 'proactive-site-advisor'),
                'text'    => __("No unusual activity detected. We'll continue monitoring your site.", 'proactive-site-advisor'),
                'icon'    => PrefixConfig::css('icon--check-circle'),
                'color'   => 'success',
            ];
        }

        $repetitionData  = [];
        $concurrencyData = [];

        foreach ($rawAlerts as $alert) {
            $id   = $alert['id'];
            $type = $alert['type'];
            $date = $alert['alert_date'];

            $repetitionData[$id]  = $this->alertsDataProvider->getRepetitionCount($type, $date);
            $concurrencyData[$id] = $this->alertsDataProvider->getConcurrentTypes($date, $type);
        }

        usort($rawAlerts, [$this, 'sortAlertsBySeverity']);

        $result = [];

        foreach ($rawAlerts as $alert) {
            $alertData = $this->alertBuilder->build(
                $alert,
                $repetitionData[$alert['id']],
                $concurrencyData[$alert['id']]
            );

            $result[] = [
                'id'       => $alertData['id'],
                'icon'     => $this->getAlertIcon($alert['type']),
                'color'    => $this->getAlertColor($alert['severity']),
                'label'    => $alertData['label'],
                'title'    => $alertData['title'],
                'short'    => $alertData['short'],
                'expanded' => $alertData['expanded'],
                'date'     => DateTimeUtils::format($alertData['date'], 'F j, Y'),
            ];
        }

        return [
            'hasData' => true,
            'data'    => $result,
        ];
    }

    /** Sort alerts by severity (critical first). */
    private function sortAlertsBySeverity($a, $b): int
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

    /** Get alert icon class for dashboard. */
    private function getAlertIcon(string $type): string
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

    /** Get alert color for dashboard. */
    private function getAlertColor(string $severity): string
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
}