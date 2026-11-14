<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Insights;

use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Services\Insights\BaselineCalculator;
use WP_UnitTestCase;

/**
 * Integration tests for the BaselineCalculator class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class BaselineCalculatorTest extends WP_UnitTestCase
{
    private BaselineCalculator $calculator;

    /** Create tables once before the test class runs. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        CoreTables::createTables();
    }

    /** Set up calculator and clean stats before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new BaselineCalculator();
        $this->cleanTable();
    }

    /** Clean stats after each test. */
    protected function tearDown(): void
    {
        $this->cleanTable();

        parent::tearDown();
    }

    /** Test no data returns empty stats structure. */
    public function testNoDataReturnsEmptyStructure(): void
    {
        $result = $this->calculator->calculate('2024-02-01');

        $this->assertSame(0, $result['count']);
        $this->assertSame(0.0, $result['pageviews']['median']);
        $this->assertSame(1.0, $result['pageviews']['s']);
        $this->assertSame(1.0, $result['pageviews']['srel']);
        $this->assertArrayHasKey('errors_404', $result);
        $this->assertArrayHasKey('bot_pageviews', $result);
    }

    /** Test median, average, and noise floor computation. */
    public function testComputesMedianAndNoiseFloor(): void
    {
        $values = [10, 20, 30, 40, 50, 60, 70];

        foreach ($values as $i => $value) {
            $this->insertStats(sprintf('2024-01-%02d', $i + 1), $value);
        }

        $result = $this->calculator->calculate('2024-01-08');

        $this->assertSame(7, $result['count']);

        $pv = $result['pageviews'];
        $this->assertSame(40.0, $pv['median']);
        $this->assertSame(40.0, $pv['avg']);
        $this->assertEqualsWithDelta(20.0, $pv['mad'], 0.001);
        $this->assertEqualsWithDelta(29.652, $pv['smad'], 0.001);
        $this->assertSame(0.0, $pv['spois']);
        $this->assertSame(1.0, $pv['srel']);
        $this->assertEqualsWithDelta(29.652, $pv['s'], 0.001);
    }

    /** Test rows on or after the target date are excluded. */
    public function testOnlyRowsBeforeDateCounted(): void
    {
        foreach ([100, 100, 100, 100, 100, 100, 100] as $i => $value) {
            $this->insertStats(sprintf('2024-01-%02d', $i + 1), $value);
        }

        $this->insertStats('2024-01-08', 9999);
        $this->insertStats('2024-01-09', 9999);

        $result = $this->calculator->calculate('2024-01-08');

        $this->assertSame(7, $result['count']);
        $this->assertSame(100.0, $result['pageviews']['median']);
    }

    /** Test days parameter limits the window. */
    public function testDaysParameterLimitsWindow(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->insertStats(sprintf('2024-01-%02d', $i), $i * 10);
        }

        $result = $this->calculator->calculate('2024-01-11', 3);

        $this->assertSame(3, $result['count']);
        // Most recent 3 rows before 2024-01-11: 80, 90, 100 -> median 90.
        $this->assertSame(90.0, $result['pageviews']['median']);
    }

    /** Test all three metric columns are computed. */
    public function testAllMetricsComputed(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->insertStats(sprintf('2024-01-%02d', $i), 100, 5, 10);
        }

        $result = $this->calculator->calculate('2024-01-08');

        $this->assertSame(100.0, $result['pageviews']['median']);
        $this->assertSame(5.0, $result['errors_404']['median']);
        $this->assertSame(10.0, $result['bot_pageviews']['median']);
    }

    /** Test noise floor s is at least 1 for constant zero data. */
    public function testNoiseFloorMinimumForZeroData(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->insertStats(sprintf('2024-01-%02d', $i), 0);
        }

        $result = $this->calculator->calculate('2024-01-08');

        $this->assertSame(0.0, $result['pageviews']['median']);
        $this->assertSame(1.0, $result['pageviews']['s']);
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

    /** Delete all rows from daily_stats. */
    private function cleanTable(): void
    {
        global $wpdb;

        $table = DailyStats::getTableName();

        if ($table !== '') {
            $wpdb->query("DELETE FROM `$table`");
        }
    }
}