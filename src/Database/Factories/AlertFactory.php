<?php

namespace ProactiveSiteAdvisor\Database\Factories;

use ProactiveSiteAdvisor\Abstracts\AbstractFactory;
use ProactiveSiteAdvisor\Models\Alert;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AlertFactory
 *
 * Factory for creating Alert records with fake data.
 *
 * @package ProactiveSiteAdvisor\Database\Factories
 * @version 1.0.4
 */
class AlertFactory extends AbstractFactory
{
    /**
     * Model class.
     *
     * @var string
     */
    protected string $model = Alert::class;

    /**
     * Define default attributes.
     *
     * @return array
     */
    protected function definition(): array
    {
        return [
            'alert_date' => current_time('Y-m-d'),
            'type'       => 'traffic_drop',
            'severity'   => 'warning',
            'meta_json'  => null,
        ];
    }

    /**
     * Create a traffic drop alert, following TrafficAnalyzer rules.
     *
     * @param string $date
     * @param int $percentDrop
     * @return Alert|null
     */
    public function trafficDrop(string $date, int $percentDrop = 35): ?Alert
    {
        if ($percentDrop <= 30) {
            $percentDrop = 31;
        }

        $today = $this->randomInt(300, 600);
        $avg7  = (int)round(($today / (1 - ($percentDrop / 100))));

        $ratio     = $today / $avg7;
        $changePct = round(($ratio - 1) * 100, 2);
        $severity  = abs($changePct) >= 40 ? 'critical' : 'warning';

        $metaJson = wp_json_encode([
            'today'      => $today,
            'avg7'       => $avg7,
            'change_pct' => $changePct,
        ]);

        return Alert::createIfNotExists(
            $date,
            'traffic_drop',
            $severity,
            $metaJson
        );
    }

    /**
     * Create a traffic spike alert, following TrafficAnalyzer rules.
     *
     * @param string $date
     * @param int $percentIncrease
     * @return Alert|null
     */
    public function trafficSpike(string $date, int $percentIncrease = 75): ?Alert
    {
        if ($percentIncrease <= 50) {
            $percentIncrease = 51;
        }

        $avg7  = $this->randomInt(800, 1200);
        $today = (int)($avg7 * (1 + ($percentIncrease / 100)));

        $ratio     = $today / $avg7;
        $changePct = round(($ratio - 1) * 100, 2);

        $metaJson = wp_json_encode([
            'today'      => $today,
            'avg7'       => $avg7,
            'change_pct' => $changePct,
        ]);

        return Alert::createIfNotExists(
            $date,
            'traffic_spike',
            'info',
            $metaJson
        );
    }

    /**
     * Create a bot spike alert, following BotTrafficAnalyzer rules.
     *
     * @param string $date
     * @param int $percentIncrease
     * @return Alert|null
     */
    public function botSpike(string $date, int $percentIncrease = 180): ?Alert
    {
        if ($percentIncrease <= 150) {
            $percentIncrease = 151;
        }

        $avg7  = $this->randomInt(200, 500);
        $today = (int)($avg7 * (1 + ($percentIncrease / 100)));

        $ratio     = $today / $avg7;
        $changePct = round(($ratio - 1) * 100, 2);
        $severity  = $ratio >= 4 ? 'critical' : 'warning';

        $topBots = [
            ['Googlebot', $this->randomInt(50, 80)],
            ['Bingbot', $this->randomInt(30, 60)],
            ['AhrefsBot', $this->randomInt(20, 40)],
        ];

        $metaJson = wp_json_encode([
            'today'      => $today,
            'avg7'       => $avg7,
            'change_pct' => $changePct,
            'top'        => $topBots,
        ]);

        return Alert::createIfNotExists(
            $date,
            'bot_spike',
            $severity,
            $metaJson
        );
    }

    /**
     * Create a bot drop alert, following BotTrafficAnalyzer rules.
     *
     * @param string $date
     * @param int $percentDrop
     * @return Alert|null
     */
    public function botDrop(string $date, int $percentDrop = 80): ?Alert
    {
        if ($percentDrop <= 70) {
            $percentDrop = 71;
        }

        $avg7  = $this->randomInt(200, 500);
        $today = (int)($avg7 * (1 - ($percentDrop / 100)));

        $ratio     = $today / $avg7;
        $changePct = round(($ratio - 1) * 100, 2);

        $topBots = [
            ['Googlebot', $this->randomInt(2, 5)],
            ['Bingbot', $this->randomInt(1, 4)],
        ];

        $metaJson = wp_json_encode([
            'today'      => $today,
            'avg7'       => $avg7,
            'change_pct' => $changePct,
            'top'        => $topBots,
        ]);

        return Alert::createIfNotExists(
            $date,
            'bot_drop',
            'info',
            $metaJson
        );
    }

    /**
     * Create a 404 spike alert, compatible with Error404Analyzer logic.
     *
     * @param string $date
     * @param int $errorCount
     * @param int $average
     * @return Alert|null
     */
    public function error404Spike(string $date, int $errorCount = 50, int $average = 15): ?Alert
    {
        $ratio = $average > 0 ? $errorCount / $average : 0;
        if ($ratio <= 2) {
            $factor     = $this->randomFloat(2.1, 4.0);
            $errorCount = (int)($average * $factor);
            $ratio      = $factor;
        }

        $severity  = $ratio >= 3 ? 'critical' : 'warning';
        $changePct = round(($ratio - 1) * 100, 2);

        $topPaths = [
            ['/wp-login.php', $this->randomInt(10, 20)],
            ['/xmlrpc.php', $this->randomInt(5, 15)],
            ['/.env', $this->randomInt(3, 10)],
        ];

        $metaJson = wp_json_encode([
            'today'      => $errorCount,
            'avg7'       => $average,
            'change_pct' => $changePct,
            'top'        => $topPaths,
        ]);

        return Alert::createIfNotExists(
            $date,
            '404_spike',
            $severity,
            $metaJson
        );
    }

    /**
     * Create a random alert for realistic pattern.
     *
     * @param string $date
     * @return Alert|null
     */
    public function randomAlert(string $date): ?Alert
    {
        $types = ['traffic_drop', 'traffic_spike', '404_spike', 'bot_spike', 'bot_drop'];
        $type  = $this->randomElement($types);

        switch ($type) {
            case 'traffic_drop':
                $percentDrop = $this->randomInt(31, 50);
                return $this->trafficDrop($date, $percentDrop);

            case 'traffic_spike':
                $percentIncrease = $this->randomInt(60, 120);
                return $this->trafficSpike($date, $percentIncrease);

            case 'bot_spike':
                $percentIncrease = $this->randomInt(160, 300);
                return $this->botSpike($date, $percentIncrease);

            case 'bot_drop':
                $percentDrop = $this->randomInt(71, 95);
                return $this->botDrop($date, $percentDrop);

            case '404_spike':
                $average    = $this->randomInt(10, 20);
                $factor     = $this->randomFloat(2.1, 4.0);
                $errorCount = (int)($average * $factor);
                return $this->error404Spike($date, $errorCount, $average);

            default:
                return null;
        }
    }
}