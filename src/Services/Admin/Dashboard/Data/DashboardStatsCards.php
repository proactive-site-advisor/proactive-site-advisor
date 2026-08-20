<?php

namespace ProactiveSiteAdvisor\Services\Admin\Dashboard\Data;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\PluginStatus;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prepares dashboard stats cards data for display.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Dashboard\Data
 * @since   1.0.0
 */
class DashboardStatsCards
{
    /** Returns full card data for the dashboard. */
    public function getCards(array $stats, int $daysWithData): array
    {
        $baseCards = $this->getCardDefinitions();
        $status    = PluginStatus::getStatus($daysWithData);
        $meta      = $this->getCardMeta();

        $finalCards = [];

        foreach ($baseCards as $key => $card) {
            $value = $stats[$key] ?? 0;

            $metaForKey = $meta[$key] ?? [
                'zero'   => __('Last 7 days', 'proactive-site-advisor'),
                'active' => __('Last 7 days', 'proactive-site-advisor'),
            ];

            $state = $this->getCardState($value, $metaForKey, $status);

            $finalCards[$key] = [
                'icon'     => $card['icon'],
                'label'    => $card['label'],
                'color'    => $card['color'],
                'value'    => $state['value'],
                'subtitle' => $state['subtitle'],
            ];
        }

        return $finalCards;
    }

    /** Builds final visual state for a single card. */
    private function getCardState(int $value, array $meta, string $status): array
    {
        if ($status === PluginStatus::STATUS_FRESH) {
            return [
                'value'    => '—',
                'subtitle' => __('Collecting data', 'proactive-site-advisor'),
            ];
        }

        if ($status === PluginStatus::STATUS_LIMITED) {
            return [
                'value'    => '—',
                'subtitle' => __('Limited data available', 'proactive-site-advisor'),
            ];
        }

        if ($value === 0) {
            return [
                'value'    => '0',
                'subtitle' => $meta['zero'],
            ];
        }

        return [
            'value'    => (string)$value,
            'subtitle' => $meta['active'],
        ];
    }

    /** Returns the meta subtitles for each card (i18n-safe). */
    private function getCardMeta(): array
    {
        return [
            'critical_alerts' => [
                'zero'   => __('No critical issues detected', 'proactive-site-advisor'),
                'active' => __('Issues needing attention', 'proactive-site-advisor'),
            ],
            'traffic_alerts'  => [
                'zero'   => __('No unusual traffic detected', 'proactive-site-advisor'),
                'active' => __('Unusual traffic changes detected', 'proactive-site-advisor'),
            ],
            'error_alerts'    => [
                'zero'   => __('No 404 issues detected', 'proactive-site-advisor'),
                'active' => __('Pages returning 404 errors', 'proactive-site-advisor'),
            ],
            'bot_alerts'      => [
                'zero'   => __('No bot activity anomalies', 'proactive-site-advisor'),
                'active' => __('Bot traffic anomalies detected', 'proactive-site-advisor'),
            ],
            'total_alerts'    => [
                'zero'   => __('Last 7 days', 'proactive-site-advisor'),
                'active' => __('Total in last 7 days', 'proactive-site-advisor'),
            ],
        ];
    }

    /** Returns static card definitions (icons, colors, labels). */
    private function getCardDefinitions(): array
    {
        return [
            'critical_alerts' => [
                'icon'  => PrefixConfig::css('icon--critical'),
                'label' => __('Critical Alerts', 'proactive-site-advisor'),
                'color' => 'error',
            ],
            'traffic_alerts'  => [
                'icon'  => PrefixConfig::css('icon--traffic'),
                'label' => __('Traffic Alerts', 'proactive-site-advisor'),
                'color' => 'primary',
            ],
            'error_alerts'    => [
                'icon'  => PrefixConfig::css('icon--error-404'),
                'label' => __('404 Alerts', 'proactive-site-advisor'),
                'color' => 'warning',
            ],
            'bot_alerts'      => [
                'icon'  => PrefixConfig::css('icon--bot'),
                'label' => __('Bot Alerts', 'proactive-site-advisor'),
                'color' => 'info',
            ],
            'total_alerts'    => [
                'icon'  => PrefixConfig::css('icon--alert'),
                'label' => __('Total Alerts', 'proactive-site-advisor'),
                'color' => 'info',
            ],
        ];
    }
}