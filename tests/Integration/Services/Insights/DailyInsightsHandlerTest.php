<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Insights;

use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Models\Alert;
use ProactiveSiteAdvisor\Models\DailyFingerprint;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Models\RateCounter;
use ProactiveSiteAdvisor\Services\Insights\DailyInsightsHandler;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use WP_UnitTestCase;

/**
 * Integration tests for the DailyInsightsHandler class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class DailyInsightsHandlerTest extends WP_UnitTestCase
{
    private const TARGET_DATE = '2024-01-08';

    /** Create tables once before the test class runs. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        CoreTables::createTables();
    }

    /** Clean all plugin tables before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanTables();
    }

    /** Clean all plugin tables after each test. */
    protected function tearDown(): void
    {
        $this->cleanTables();
        remove_all_actions('proactive_site_advisor_after_daily_insights');

        parent::tearDown();
    }

    /** Test handle fires action after alerts and purges old data. */
    public function testHandleOrderAndPurge(): void
    {
        $this->seedBaselineAndSpike();

        // Old row that must be purged (retention = today - 7 days).
        $this->insertStats('2000-01-01', 1, 0, 0);

        // Recent row that must be kept.
        $recentDate = DateTimeUtils::current()->modify('-1 day')->format('Y-m-d');
        $this->insertStats($recentDate, 50, 0, 0);

        $captured               = [];
        $alertCountDuringAction = null;

        add_action('proactive_site_advisor_after_daily_insights', function (string $date) use (&$captured, &$alertCountDuringAction): void {
            $captured[]             = $date;
            $alertCountDuringAction = Alert::count(['alert_date' => self::TARGET_DATE]);
        });

        (new DailyInsightsHandler())->handle(self::TARGET_DATE);

        // Action fired with correct date, after alerts were generated.
        $this->assertSame([self::TARGET_DATE], $captured);
        $this->assertGreaterThanOrEqual(1, $alertCountDuringAction);

        // Old data purged, recent data kept.
        $this->assertNull($this->getStatsRow('2000-01-01'));
        $this->assertNotNull($this->getStatsRow($recentDate));

        // 2024 baseline rows are also older than retention -> purged.
        $this->assertNull($this->getStatsRow(self::TARGET_DATE));
    }

    /** Test handle works with no data and still fires action. */
    public function testHandleWithNoData(): void
    {
        $fired = false;

        add_action('proactive_site_advisor_after_daily_insights', function () use (&$fired): void {
            $fired = true;
        });

        (new DailyInsightsHandler())->handle(self::TARGET_DATE);

        $this->assertTrue($fired);
    }

    /** Seed 7 baseline days plus a spike day. */
    private function seedBaselineAndSpike(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->insertStats(sprintf('2024-01-%02d', $i), 100, 0, 0);
        }

        $this->insertStats(self::TARGET_DATE, 500, 0, 0);
    }

    /** Insert a daily_stats row directly. */
    private function insertStats(string $date, int $pageviews, int $errors404 = 0, int $botPageviews = 0): void
    {
        global $wpdb;

        $wpdb->insert(DailyStats::getTableName(), [
            'stats_date'    => $date,
            'pageviews'     => $pageviews,
            'errors_404'    => $errors404,
            'bot_pageviews' => $botPageviews,
        ]);
    }

    /** Get a daily_stats row. */
    private function getStatsRow(string $date): ?array
    {
        global $wpdb;

        $table = DailyStats::getTableName();

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `$table` WHERE stats_date = %s", $date),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    /** Delete all rows from all plugin tables. */
    private function cleanTables(): void
    {
        global $wpdb;

        foreach ([
                     DailyStats::getTableName(),
                     DailyFingerprint::getTableName(),
                     RateCounter::getTableName(),
                     Alert::getTableName(),
                 ] as $table) {
            if ($table !== '') {
                $wpdb->query("DELETE FROM `$table`");
            }
        }
    }
}