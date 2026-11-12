<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\TrafficEngine;
use ReflectionProperty;
use WP_UnitTestCase;

/**
 * Integration tests for the TrafficEngine class.
 *
 * @package ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic
 * @since   1.2.8
 */
class TrafficEngineTest extends WP_UnitTestCase
{
    private array $serverBackup = [];
    private $wpQueryBackup;
    private $wpTheQueryBackup;

    /** Set up the test environment and reset the engine state. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup     = $_SERVER;
        $this->wpQueryBackup    = $GLOBALS['wp_query'] ?? null;
        $this->wpTheQueryBackup = $GLOBALS['wp_the_query'] ?? null;

        $_SERVER['HTTP_HOST']      = 'example.com';
        $_SERVER['REMOTE_ADDR']    = '203.0.113.10';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/';

        TrafficEngineFakeBotSignal::$result  = false;
        TrafficEngineFakeScoreSignal::$score = 0;

        $this->resetTrafficEngineCache();
    }

    /** Restore the original environment and reset the engine state. */
    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;

        $GLOBALS['wp_query']     = $this->wpQueryBackup;
        $GLOBALS['wp_the_query'] = $this->wpTheQueryBackup;

        TrafficEngineFakeBotSignal::$result  = false;
        TrafficEngineFakeScoreSignal::$score = 0;

        $this->resetTrafficEngineCache();

        parent::tearDown();
    }

    /** Test human classification when no signals are triggered. */
    public function testIsHumanReturnsTrueWhenNoSignalsTrigger(): void
    {
        $this->withSignals([], [], function (): void {
            $this->assertTrue(TrafficEngine::isHuman());
        });
    }

    /** Test bot classification when a bot signal is triggered. */
    public function testIsHumanReturnsFalseWhenBotSignalTriggers(): void
    {
        TrafficEngineFakeBotSignal::$result = true;

        $this->withSignals(
            [TrafficEngineFakeBotSignal::class],
            [],
            function (): void {
                $this->assertFalse(TrafficEngine::isHuman());
            }
        );
    }

    /** Test bot classification when the suspicion score reaches the threshold. */
    public function testIsHumanReturnsFalseWhenScoreReachesThreshold(): void
    {
        TrafficEngineFakeScoreSignal::$score = 4;

        $this->withSignals(
            [],
            [TrafficEngineFakeScoreSignal::class],
            function (): void {
                $this->assertFalse(TrafficEngine::isHuman());
            }
        );
    }

    /** Test human classification when the suspicion score is below the threshold. */
    public function testScoreBelowMinimumThresholdIsHuman(): void
    {
        TrafficEngineFakeScoreSignal::$score = 3;

        $this->withSignals(
            [],
            [TrafficEngineFakeScoreSignal::class],
            function (): void {
                $this->assertTrue(TrafficEngine::isHuman());
            }
        );
    }

    /** Test that the suspicion threshold filter cannot lower the minimum threshold. */
    public function testSuspicionThresholdFilterCannotGoBelowFour(): void
    {
        TrafficEngineFakeScoreSignal::$score = 3;

        $filter = static function (): int {
            return 1;
        };

        add_filter('proactive_site_advisor_suspicion_threshold', $filter);

        try {
            $this->withSignals(
                [],
                [TrafficEngineFakeScoreSignal::class],
                function (): void {
                    $this->assertTrue(TrafficEngine::isHuman());
                }
            );
        } finally {
            remove_filter('proactive_site_advisor_suspicion_threshold', $filter);
        }
    }

    /** Test that the suspicion threshold filter can increase the minimum threshold. */
    public function testSuspicionThresholdFilterCanRaiseThreshold(): void
    {
        TrafficEngineFakeScoreSignal::$score = 5;

        $filter = static function (): int {
            return 10;
        };

        add_filter('proactive_site_advisor_suspicion_threshold', $filter);

        try {
            $this->withSignals(
                [],
                [TrafficEngineFakeScoreSignal::class],
                function (): void {
                    $this->assertTrue(TrafficEngine::isHuman());
                }
            );
        } finally {
            remove_filter('proactive_site_advisor_suspicion_threshold', $filter);
        }
    }

    /** Test that local environments bypass traffic signals. */
    public function testLocalEnvironmentBypassesSignals(): void
    {
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        TrafficEngineFakeBotSignal::$result = true;

        $this->withSignals(
            [TrafficEngineFakeBotSignal::class],
            [],
            function (): void {
                $this->assertTrue(TrafficEngine::isHuman());
            }
        );
    }

    /** Register temporary signal filters and execute the provided callback. */
    private function withSignals(array $botSignals, array $scoreSignals, callable $callback): void
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

    /** Reset the cached signal instances in the traffic engine. */
    private function resetTrafficEngineCache(): void
    {
        $property = new ReflectionProperty(TrafficEngine::class, 'cache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}

/**
 * Fake bot signal for TrafficEngine integration tests.
 *
 * @package ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic
 * @since   1.2.8
 */
class TrafficEngineFakeBotSignal implements BotSignalInterface
{
    public static bool $result = false;

    /** Return the configured bot detection result. */
    public function isBot(): bool
    {
        return self::$result;
    }
}

/**
 * Fake score signal for TrafficEngine integration tests.
 *
 * @package ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic
 * @since   1.2.8
 */
class TrafficEngineFakeScoreSignal implements ScoreSignalInterface
{
    public static int $score = 0;

    /** Return the configured suspicion score. */
    public function getScore(): int
    {
        return self::$score;
    }
}
