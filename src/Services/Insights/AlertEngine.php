<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;
use ProactiveSiteAdvisor\Models\Alert;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates traffic and 404-related alerts for a given day.
 *
 * @package ProactiveSiteAdvisor\Services\Insights
 * @since   1.0.0
 */
class AlertEngine
{
    /** Provides daily stats from DB. */
    private DailyStatsDataProvider $dailyStatsDataProvider;

    /** Calculates 7-day averages of traffic + 404. */
    private BaselineCalculator $baselineCalculator;

    /** Detects traffic drops or spikes. */
    private TrafficAnalyzer $trafficAnalyzer;

    /** Detects sudden increases in 404 errors. */
    private Error404Analyzer $error404Analyzer;

    /** Detects bot traffic drops or spikes. */
    private BotTrafficAnalyzer $botTrafficAnalyzer;

    /** Constructor. */
    public function __construct()
    {
        $this->dailyStatsDataProvider = new DailyStatsDataProvider();
        $this->baselineCalculator     = new BaselineCalculator();
        $this->trafficAnalyzer        = new TrafficAnalyzer();
        $this->error404Analyzer       = new Error404Analyzer();
        $this->botTrafficAnalyzer     = new BotTrafficAnalyzer();
    }

    /** Generates alerts for a specific date (YYYY-MM-DD). */
    public function generateForDay(string $date): void
    {
        $base = $this->baselineCalculator->calculate($date);

        if ($base['count'] < 7) {
            return;
        }

        $row = $this->dailyStatsDataProvider->getDailyStatsByDate($date);

        if (!$row) {
            return;
        }

        $todayPv  = (int)$row['pageviews'];
        $today404 = (int)$row['errors_404'];
        $top404   = !empty($row['top_404_json']) ? json_decode($row['top_404_json'], true) : null;

        $traffic = $this->trafficAnalyzer->analyze($todayPv, $base['avg_pageviews']);
        $this->createTrafficAlert($date, $traffic, $todayPv, $base['avg_pageviews']);

        $err = $this->error404Analyzer->analyze($today404, $base['avg_404']);
        $this->create404Alert($date, $err, $today404, $base['avg_404'], $top404);

        $todayBotPv = (int)$row['bot_pageviews'];
        $topBots    = !empty($row['top_bots_json']) ? json_decode($row['top_bots_json'], true) : null;

        $bot = $this->botTrafficAnalyzer->analyze($todayBotPv, $base['avg_bot_pageviews']);
        $this->createBotAlert($date, $bot, $todayBotPv, $base['avg_bot_pageviews'], $topBots);
    }

    /** Creates traffic-related alerts (spike or drop). */
    private function createTrafficAlert(string $date, array $r, int $today, float $avg): void
    {
        if (!$r['type']) {
            return;
        }

        $meta = [
            'today'      => $today,
            'avg7'       => (int)round($avg),
            'change_pct' => $r['change_pct']
        ];

        Alert::createIfNotExists(
            $date,
            $r['type'],
            $r['severity'],
            wp_json_encode($meta)
        );
    }

    /** Creates an alert when 404 errors spike beyond expected average. */
    private function create404Alert(string $date, array $r, int $today, float $avg, ?array $top): void
    {
        if (!$r['type']) {
            return;
        }

        $meta = [
            'today'      => $today,
            'avg7'       => (int)round($avg),
            'change_pct' => $r['change_pct'],
            'top'        => $top
        ];

        Alert::createIfNotExists(
            $date,
            $r['type'],
            $r['severity'],
            wp_json_encode($meta)
        );
    }

    /** Create alert for abnormal bot traffic. */
    private function createBotAlert(string $date, array $r, int $today, float $avg, ?array $top): void
    {
        if (!$r['type']) {
            return;
        }
        $meta = [
            'today'      => $today,
            'avg7'       => (int)round($avg),
            'change_pct' => $r['change_pct'],
            'top'        => $top,
        ];

        Alert::createIfNotExists($date, $r['type'], $r['severity'], wp_json_encode($meta));
    }
}