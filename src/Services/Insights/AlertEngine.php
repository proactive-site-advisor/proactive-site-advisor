<?php

namespace ProactiveSiteAdvisor\Services\Insights;

use ProactiveSiteAdvisor\DataProviders\DailyStatsDataProvider;
use ProactiveSiteAdvisor\Models\Alert;
use ProactiveSiteAdvisor\Services\Insights\Config\AlertGeneratorConfig;
use ProactiveSiteAdvisor\Services\Insights\Contracts\AlertGeneratorInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates all anomaly alerts for a given day.
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

    /** Constructor. */
    public function __construct()
    {
        $this->dailyStatsDataProvider = new DailyStatsDataProvider();
        $this->baselineCalculator     = new BaselineCalculator();
    }

    /** Generates alerts for a specific date (YYYY-MM-DD). */
    public function generateForDay(string $date): void
    {
        $base = $this->baselineCalculator->calculate($date);
        $row  = $this->dailyStatsDataProvider->getDailyStatsByDate($date);

        if (!$row) {
            return;
        }

        $context = [
            'avg_pageviews'     => (float)$base['avg_pageviews'],
            'avg_404'           => (float)$base['avg_404'],
            'avg_bot_pageviews' => (float)$base['avg_bot_pageviews'],
            'todayPv'           => (int)$row['pageviews'],
            'today404'          => (int)$row['errors_404'],
            'todayBotPv'        => (int)$row['bot_pageviews'],
            'count'             => (int)$base['count'],
            'top404'            => !empty($row['top_404_json']) ? json_decode($row['top_404_json'], true) : [],
            'topBots'           => !empty($row['top_bots_json']) ? json_decode($row['top_bots_json'], true) : [],
        ];

        $generators = AlertGeneratorConfig::getGenerators();

        foreach ($generators as $generatorClass) {
            /** @var AlertGeneratorInterface $generator */
            $generator = new $generatorClass();

            if (!$generator->isEligible($context)) {
                continue;
            }

            $result = $generator->generate($date, $context);

            if ($result === null) {
                continue;
            }

            Alert::createIfNotExists(
                $date,
                $result['type'],
                $result['severity'],
                wp_json_encode($result['meta'])
            );
        }
    }
}