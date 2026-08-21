<?php

namespace ProactiveSiteAdvisor\Builders;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds aggregated alert statistics from raw alert data.
 *
 * @package ProactiveSiteAdvisor\Builders
 * @since   1.0.0
 */
class AlertDigestBuilder
{
    /** Build alert digest statistics. */
    public function build(array $rows): array
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
}