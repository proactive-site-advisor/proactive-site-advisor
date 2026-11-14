<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Insights;

use ProactiveSiteAdvisor\Config\PluginOptions;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Models\Alert;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Services\Insights\AlertEngine;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use WP_UnitTestCase;

/**
 * Integration tests for the AlertEngine class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class AlertEngineTest extends WP_UnitTestCase
{
    private const TARGET_DATE = '2024-01-08';

    private AlertEngine $engine;

    /** Create tables once before the test class runs. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        CoreTables::createTables();
    }

    /** Set up engine and clean data before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->engine = new AlertEngine();
        delete_option(PluginOptions::OPTION_NAME);
        $this->cleanTables();
    }

    /** Clean data after each test. */
    protected function tearDown(): void
    {
        delete_option(PluginOptions::OPTION_NAME);
        $this->cleanTables();

        parent::tearDown();
    }

    /** Test generateForDay does nothing when no stats row exists. */
    public function testNoStatsRowDoesNothing(): void
    {
        $this->engine->generateForDay(self::TARGET_DATE);

        $this->assertSame(0, Alert::count());
    }

    /** Test traffic spike creates an alert with correct meta. */
    public function testTrafficSpikeCreatesAlert(): void
    {
        $this->seedBaseline(100, 0, 0);
        $this->insertStats(self::TARGET_DATE, 500, 0, 0);

        $this->engine->generateForDay(self::TARGET_DATE);

        $alert = Alert::first(['alert_date' => self::TARGET_DATE, 'type' => 'traffic_spike']);

        $this->assertNotNull($alert);
        $this->assertSame('critical', $alert->getAttribute('severity'));

        $meta = $alert->getAttribute('meta_json');
        $this->assertSame(500, $meta['today']);
        $this->assertSame(100, $meta['median7']);
    }

    /** Test disabled alert generator is skipped. */
    public function testDisabledGeneratorSkipped(): void
    {
        OptionUtils::setOption('alerts.traffic_spike', 0);

        $this->seedBaseline(100, 0, 0);
        $this->insertStats(self::TARGET_DATE, 500, 0, 0);

        $this->engine->generateForDay(self::TARGET_DATE);

        $this->assertSame(0, Alert::count(['type' => 'traffic_spike']));
    }

    /** Test warm-up period blocks alerts with fewer than 7 baseline days. */
    public function testWarmUpPeriodBlocksAlerts(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->insertStats(sprintf('2024-01-%02d', $i), 100, 0, 0);
        }

        $this->insertStats(self::TARGET_DATE, 500, 0, 0);

        $this->engine->generateForDay(self::TARGET_DATE);

        $this->assertSame(0, Alert::count());
    }

    /** Test generateForDay is idempotent for duplicate alerts. */
    public function testGenerateForDayIsIdempotent(): void
    {
        $this->seedBaseline(100, 0, 0);
        $this->insertStats(self::TARGET_DATE, 500, 0, 0);

        $this->engine->generateForDay(self::TARGET_DATE);
        $this->engine->generateForDay(self::TARGET_DATE);

        $this->assertSame(1, Alert::count(['alert_date' => self::TARGET_DATE, 'type' => 'traffic_spike']));
    }

    /** Test 404 spike creates alert with top URLs in meta. */
    public function testError404SpikeCreatesAlert(): void
    {
        $this->seedBaseline(100, 5, 0);
        $this->insertStats(self::TARGET_DATE, 100, 50, 0, [' /missing' => 40]);

        $this->engine->generateForDay(self::TARGET_DATE);

        $alert = Alert::first(['alert_date' => self::TARGET_DATE, 'type' => '404_spike']);

        $this->assertNotNull($alert);

        $meta = $alert->getAttribute('meta_json');
        $this->assertSame(50, $meta['today']);
        $this->assertArrayHasKey('top', $meta);
    }

    /** Test bot traffic spike creates alert. */
    public function testBotSpikeCreatesAlert(): void
    {
        $this->seedBaseline(100, 0, 10);
        $this->insertStats(self::TARGET_DATE, 100, 0, 100);

        $this->engine->generateForDay(self::TARGET_DATE);

        $alert = Alert::first(['alert_date' => self::TARGET_DATE, 'type' => 'bot_spike']);

        $this->assertNotNull($alert);
        $this->assertSame(100, $alert->getAttribute('meta_json')['today']);
    }

    /** Seed 7 baseline days before the target date. */
    private function seedBaseline(int $pageviews, int $errors404, int $botPageviews): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->insertStats(sprintf('2024-01-%02d', $i), $pageviews, $errors404, $botPageviews);
        }
    }

    /** Insert a daily_stats row directly. */
    private function insertStats(string $date, int $pageviews, int $errors404 = 0, int $botPageviews = 0, array $top404 = []): void
    {
        global $wpdb;

        $wpdb->insert(DailyStats::getTableName(), [
            'stats_date'    => $date,
            'pageviews'     => $pageviews,
            'errors_404'    => $errors404,
            'bot_pageviews' => $botPageviews,
            'top_404_json'  => !empty($top404) ? wp_json_encode($top404) : null,
        ]);
    }

    /** Delete all rows from plugin tables. */
    private function cleanTables(): void
    {
        global $wpdb;

        foreach ([DailyStats::getTableName(), Alert::getTableName()] as $table) {
            if ($table !== '') {
                $wpdb->query("DELETE FROM `$table`");
            }
        }
    }
}