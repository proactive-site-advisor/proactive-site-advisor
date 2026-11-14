<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Database\Schemas\CoreTables;
use ProactiveSiteAdvisor\Models\DailyFingerprint;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders\NavigationBehaviorRecorder;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders\NotFoundRecorder;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders\PageviewRecorder;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\PageviewSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\TrafficEngine;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ReflectionProperty;
use WP_Query;
use WP_UnitTestCase;

/**
 * Integration tests for traffic collection services and recorders.
 *
 * @package ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic
 * @since   1.2.8
 */
class TrafficCollectionTest extends WP_UnitTestCase
{
    private array $serverBackup = [];
    private $wpQueryBackup;
    private $wpTheQueryBackup;

    /** Create the required database tables before running the tests. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        CoreTables::createTables();
    }

    /** Set up the WordPress request environment and reset test state. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup     = $_SERVER;
        $this->wpQueryBackup    = $GLOBALS['wp_query'] ?? null;
        $this->wpTheQueryBackup = $GLOBALS['wp_the_query'] ?? null;

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';

        $this->primeWordPressRequest();
        $this->setGetRequest();

        wp_set_current_user(0);

        $this->cleanTables();
        $this->resetStatics();

        TrafficCollectionFakeBotSignal::$result = false;
    }

    /** Restore the original request environment after each test. */
    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;

        $GLOBALS['wp_query']     = $this->wpQueryBackup;
        $GLOBALS['wp_the_query'] = $this->wpTheQueryBackup;

        TrafficCollectionFakeBotSignal::$result = false;

        parent::tearDown();
    }

    /** Test that pageview collection is disabled for POST requests. */
    public function testPageviewSignalReturnsFalseForPostRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->assertFalse(PageviewSignal::shouldCollect());
    }

    /** Test that pageview collection is enabled for standard GET page requests. */
    public function testPageviewSignalReturnsTrueForStandardGetPage(): void
    {
        $this->assertTrue(PageviewSignal::shouldCollect());
    }

    /** Test that pageview collection is disabled for logged-in administrators. */
    public function testPageviewSignalReturnsFalseForLoggedInAdministrator(): void
    {
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        $this->assertFalse(PageviewSignal::shouldCollect());
    }

    /** Test that pageview collection is disabled for requests targeting file extensions. */
    public function testPageviewSignalReturnsFalseForFileExtension(): void
    {
        $this->setGetRequest('/style.css');

        $this->assertFalse(PageviewSignal::shouldCollect());
    }

    /** Test that pageview collection is disabled for speculative requests. */
    public function testPageviewSignalReturnsFalseForSpeculativeRequest(): void
    {
        $_SERVER['HTTP_SEC_FETCH_DEST'] = 'iframe';
        $_SERVER['HTTP_PURPOSE']        = 'prefetch';

        $this->assertFalse(PageviewSignal::shouldCollect());
    }

    /** Test that the navigation behavior recorder tracks visited and repeated pages. */
    public function testNavigationBehaviorRecorderTracksPagesAndRepeats(): void
    {
        $this->setGetRequest('/', [
            'REMOTE_ADDR'          => '203.0.113.20',
            'HTTP_USER_AGENT'      => 'TestBrowser/1.0',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US',
            'HTTP_SEC_CH_UA'       => '"TestBrowser";v="1"',
        ]);

        $fingerprint = HeaderReader::getFingerprint();
        $cache       = CacheManager::instance();
        $cacheKey    = CacheKeys::navigationBehavior($fingerprint);

        $cache->delete($cacheKey);

        (new NavigationBehaviorRecorder())->record();
        $_SERVER['REQUEST_URI'] = '/about';
        (new NavigationBehaviorRecorder())->record();
        $_SERVER['REQUEST_URI'] = '/';
        (new NavigationBehaviorRecorder())->record();

        $state = $cache->get($cacheKey);

        $this->assertIsArray($state);
        $this->assertSame(3, $state['pages']);
        $this->assertSame(2, $state['unique_pages']);
        $this->assertSame(1, $state['repeat_pages']);
        $this->assertCount(3, $state['timestamps']);
        $this->assertCount(2, $state['intervals']);
    }

    /** Test that the pageview recorder stores human pageviews correctly. */
    public function testPageviewRecorderRecordsHumanPageview(): void
    {
        $this->setGetRequest('/');

        $this->withTrafficSignals([], [], function (): void {
            (new PageviewRecorder())->maybeRecord();
        });

        $row = $this->getDailyStatsRow();

        $this->assertSame(1, (int)($row['pageviews'] ?? 0));
        $this->assertSame(0, (int)($row['bot_pageviews'] ?? 0));

        $fingerprint = HeaderReader::getFingerprint();
        $today       = $this->today();

        $this->assertFalse(DailyFingerprint::isBot($fingerprint, $today));
        $this->assertSame(1, $this->getFingerprintPageviewCount($fingerprint, $today));
    }

    /** Test that human pageviews are transferred when a visitor is later detected as a bot. */
    public function testPageviewRecorderTransfersHumanPageviewsWhenBotDetected(): void
    {
        $this->setGetRequest('/');

        $this->withTrafficSignals([], [], function (): void {
            (new PageviewRecorder())->maybeRecord();
        });

        $this->resetPageviewRecorder();
        $this->resetTrafficEngineCache();

        TrafficCollectionFakeBotSignal::$result = true;

        $this->withTrafficSignals(
            [TrafficCollectionFakeBotSignal::class],
            [],
            function (): void {
                (new PageviewRecorder())->maybeRecord();
            }
        );

        $row = $this->getDailyStatsRow();

        $this->assertSame(0, (int)($row['pageviews'] ?? 0));
        $this->assertSame(2, (int)($row['bot_pageviews'] ?? 0));

        $fingerprint = HeaderReader::getFingerprint();
        $today       = $this->today();

        $this->assertTrue(DailyFingerprint::isBot($fingerprint, $today));
    }

    /** Test that the not-found recorder stores only human 404 requests. */
    public function testNotFoundRecorderRecordsOnlyHuman404(): void
    {
        $this->setGetRequest('/missing-page');
        $GLOBALS['wp_query']->is_404 = true;

        $this->withTrafficSignals([], [], function (): void {
            (new NotFoundRecorder())->maybeRecord();
        });

        $row = $this->getDailyStatsRow();
        $top = !empty($row['top_404_json']) ? json_decode($row['top_404_json'], true) : [];

        $this->assertSame(1, (int)($row['errors_404'] ?? 0));
        $this->assertSame(1, (int)($top['/missing-page'] ?? 0));

        $this->cleanTables();
        $this->resetStatics();

        TrafficCollectionFakeBotSignal::$result = true;

        $this->withTrafficSignals(
            [TrafficCollectionFakeBotSignal::class],
            [],
            function (): void {
                (new NotFoundRecorder())->maybeRecord();
            }
        );

        $row = $this->getDailyStatsRow();

        $this->assertSame(0, (int)($row['errors_404'] ?? 0));
    }

    /** Initialize the WordPress query environment for request simulation. */
    private function primeWordPressRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/';

        $GLOBALS['wp_query']     = new WP_Query();
        $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];

        do_action('wp');
    }

    /** Configure a simulated GET request with default and custom headers. */
    private function setGetRequest(string $uri = '/', array $headers = []): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = $uri;

        $defaults = [
            'HTTP_SEC_FETCH_MODE'     => 'navigate',
            'HTTP_SEC_FETCH_DEST'     => 'document',
            'HTTP_SEC_FETCH_SITE'     => 'none',
            'HTTP_SEC_FETCH_USER'     => '?1',
            'HTTP_ACCEPT'             => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE'    => 'en-US,en;q=0.9',
            'HTTP_ACCEPT_ENCODING'    => 'gzip, deflate, br',
            'HTTP_USER_AGENT'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'HTTP_SEC_CH_UA'          => '"Chromium";v="120", "Google Chrome";v="120"',
            'HTTP_SEC_CH_UA_MOBILE'   => '?0',
            'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
        ];

        foreach (array_merge($defaults, $headers) as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    /** Register temporary traffic signal filters and execute the provided callback. */
    private function withTrafficSignals(array $botSignals, array $scoreSignals, callable $callback): void
    {
        $botFilter = static function () use ($botSignals): array {
            return $botSignals;
        };

        $scoreFilter = static function () use ($scoreSignals): array {
            return $scoreSignals;
        };

        add_filter('proactive_site_advisor_bot_signals', $botFilter);
        add_filter('proactive_site_advisor_score_signals', $scoreFilter);

        try {
            $callback();
        } finally {
            remove_filter('proactive_site_advisor_bot_signals', $botFilter);
            remove_filter('proactive_site_advisor_score_signals', $scoreFilter);
        }
    }

    /** Delete existing traffic statistics and fingerprint records. */
    private function cleanTables(): void
    {
        global $wpdb;

        foreach ([DailyStats::getTableName(), DailyFingerprint::getTableName()] as $table) {
            if ($table !== '') {
                $wpdb->query("DELETE FROM `$table`");
            }
        }
    }

    /** Reset all static state used by traffic services and recorders. */
    private function resetStatics(): void
    {
        $this->resetTrafficEngineCache();
        $this->resetPageviewRecorder();
        $this->resetNotFoundRecorder();
    }

    /** Reset the cached signal instances in the traffic engine. */
    private function resetTrafficEngineCache(): void
    {
        $this->setStaticProperty(TrafficEngine::class, 'cache', []);
    }

    /** Reset the pageview recorder execution state. */
    private function resetPageviewRecorder(): void
    {
        $this->setStaticProperty(PageviewRecorder::class, 'hasRun', false);
    }

    /** Reset the not-found recorder execution state. */
    private function resetNotFoundRecorder(): void
    {
        $this->setStaticProperty(NotFoundRecorder::class, 'hasRun', false);
    }

    /** Set the value of a static property using reflection. */
    private function setStaticProperty(string $class, string $property, $value): void
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue(null, $value);
    }

    /** Retrieve the daily traffic statistics row for the current date. */
    private function getDailyStatsRow(): array
    {
        global $wpdb;

        $table = DailyStats::getTableName();
        $today = $this->today();

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT pageviews, errors_404, bot_pageviews, top_404_json, top_bots_json
                 FROM `$table`
                 WHERE stats_date = %s",
                $today
            ),
            ARRAY_A
        );

        return is_array($row) ? $row : [];
    }

    /** Retrieve the pageview count for a specific fingerprint and date. */
    private function getFingerprintPageviewCount(string $fingerprint, string $today): int
    {
        global $wpdb;

        $table = DailyFingerprint::getTableName();

        return (int)$wpdb->get_var(
            $wpdb->prepare(
                "SELECT pageview_count
                 FROM `$table`
                 WHERE fingerprint = %s AND record_date = %s",
                $fingerprint,
                $today
            )
        );
    }

    /** Get the current date using the plugin date and time utility. */
    private function today(): string
    {
        return DateTimeUtils::current()->format('Y-m-d');
    }
}

/**
 * Fake bot signal for traffic collection integration tests.
 *
 * @package ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic
 * @since   1.2.8
 */
class TrafficCollectionFakeBotSignal implements BotSignalInterface
{
    public static bool $result = false;

    /** Return the configured bot detection result. */
    public function isBot(): bool
    {
        return self::$result;
    }
}
