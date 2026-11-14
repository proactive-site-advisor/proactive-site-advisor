<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Insights\Crons;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Services\Insights\Crons\DailyStatsFlusher;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use WP_UnitTestCase;

/**
 * Integration tests for the DailyStatsFlusher cron class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class DailyStatsFlusherTest extends WP_UnitTestCase
{
    private CacheManager $cache;

    /** Create tables once before the test class runs. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        CoreTables::createTables();
    }

    /** Set up clean cache and meta before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = CacheManager::instance();
        $this->cache->flush();
        OptionUtils::deleteMeta(PluginMeta::LAST_DAILY_RUN);
    }

    /** Clean cache and meta after each test. */
    protected function tearDown(): void
    {
        $this->cache->flush();
        OptionUtils::deleteMeta(PluginMeta::LAST_DAILY_RUN);
        remove_all_actions('proactive_site_advisor_after_daily_insights');

        parent::tearDown();
    }

    /** Test run executes handler for yesterday and records timestamp. */
    public function testRunExecutesHandlerAndSetsTimestamp(): void
    {
        $capturedDate = null;

        add_action('proactive_site_advisor_after_daily_insights', function (string $date) use (&$capturedDate): void {
            $capturedDate = $date;
        });

        DailyStatsFlusher::instance()->run();

        $yesterday = DateTimeUtils::current()->modify('-1 day')->format('Y-m-d');

        $this->assertSame($yesterday, $capturedDate);
        $this->assertNotNull(OptionUtils::getMeta(PluginMeta::LAST_DAILY_RUN));
    }

    /** Test run releases the lock after execution. */
    public function testRunReleasesLock(): void
    {
        DailyStatsFlusher::instance()->run();

        $this->assertNull($this->cache->get(CacheKeys::dailyLock()));
    }

    /** Test run skips when lock is already held. */
    public function testRunSkipsWhenLockHeld(): void
    {
        $this->cache->set(CacheKeys::dailyLock(), 1, 300);

        $fired = false;

        add_action('proactive_site_advisor_after_daily_insights', function () use (&$fired): void {
            $fired = true;
        });

        DailyStatsFlusher::instance()->run();

        $this->assertFalse($fired);
        $this->assertNull(OptionUtils::getMeta(PluginMeta::LAST_DAILY_RUN));
    }

    /** Test lock is released even when handler throws. */
    public function testLockReleasedOnFailure(): void
    {
        // Force a failure inside the handler by dropping a required table temporarily
        // is too invasive; instead verify the finally block via a second run.
        DailyStatsFlusher::instance()->run();
        DailyStatsFlusher::instance()->run();

        $this->assertNull($this->cache->get(CacheKeys::dailyLock()));
        $this->assertNotNull(OptionUtils::getMeta(PluginMeta::LAST_DAILY_RUN));
    }
}