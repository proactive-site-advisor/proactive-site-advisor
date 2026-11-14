<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Insights\Generators;

use ProactiveSiteAdvisor\Config\PluginOptions;
use ProactiveSiteAdvisor\Services\Insights\Generators\BotTrafficChangeAlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\Error404AlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\TrafficDropAlertGenerator;
use ProactiveSiteAdvisor\Services\Insights\Generators\TrafficSpikeAlertGenerator;
use WP_UnitTestCase;

/**
 * Integration tests for alert generators.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class AlertGeneratorsTest extends WP_UnitTestCase
{
    /** Clean plugin options before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PluginOptions::OPTION_NAME);
    }

    /** Clean plugin options after each test. */
    protected function tearDown(): void
    {
        delete_option(PluginOptions::OPTION_NAME);

        parent::tearDown();
    }

    /** Test spike generator eligibility requires 7 baseline days. */
    public function testSpikeGeneratorEligibility(): void
    {
        $generator = new TrafficSpikeAlertGenerator();

        $this->assertFalse($generator->isEligible($this->context(['count' => 6])));
        $this->assertTrue($generator->isEligible($this->context(['count' => 7])));
    }

    /** Test spike generator returns null without anomaly. */
    public function testSpikeGeneratorReturnsNullWithoutAnomaly(): void
    {
        $generator = new TrafficSpikeAlertGenerator();

        $result = $generator->generate('2024-01-08', $this->context(['todayPv' => 100]));

        $this->assertNull($result);
    }

    /** Test spike generator returns alert with common meta. */
    public function testSpikeGeneratorReturnsAlert(): void
    {
        $generator = new TrafficSpikeAlertGenerator();

        $result = $generator->generate('2024-01-08', $this->context([
            'todayPv'  => 200,
            'baseline' => [
                'pageviews'     => $this->stats(100.0, 2.0),
                'errors_404'    => $this->stats(0.0, 1.0),
                'bot_pageviews' => $this->stats(0.0, 1.0),
            ],
        ]));

        $this->assertNotNull($result);
        $this->assertSame('traffic_spike', $result['type']);
        $this->assertSame('critical', $result['severity']);

        $meta = $result['meta'];
        $this->assertSame(200, $meta['today']);
        $this->assertSame(100, $meta['median7']);
        $this->assertEqualsWithDelta(100.0, $meta['change_pct'], 0.001);
    }

    /** Test drop generator returns traffic_drop type. */
    public function testDropGeneratorReturnsAlert(): void
    {
        $generator = new TrafficDropAlertGenerator();

        $result = $generator->generate('2024-01-08', $this->context([
            'todayPv'  => 20,
            'baseline' => [
                'pageviews'     => $this->stats(100.0, 2.0),
                'errors_404'    => $this->stats(0.0, 1.0),
                'bot_pageviews' => $this->stats(0.0, 1.0),
            ],
        ]));

        $this->assertNotNull($result);
        $this->assertSame('traffic_drop', $result['type']);
    }

    /** Test 404 generator includes top URLs limited to 3. */
    public function testError404GeneratorMetaTop(): void
    {
        $generator = new Error404AlertGenerator();

        $result = $generator->generate('2024-01-08', $this->context([
            'today404' => 50,
            'top404'   => ['/a' => 5, '/b' => 3, '/c' => 2, '/d' => 1],
            'baseline' => [
                'pageviews'     => $this->stats(100.0, 2.0),
                'errors_404'    => $this->stats(5.0, 1.0),
                'bot_pageviews' => $this->stats(0.0, 1.0),
            ],
        ]));

        $this->assertNotNull($result);
        $this->assertSame('404_spike', $result['type']);
        $this->assertSame(['/a', '/b', '/c'], array_keys($result['meta']['top']));
    }

    /** Test bot generator resolves spike type. */
    public function testBotGeneratorSpike(): void
    {
        $generator = new BotTrafficChangeAlertGenerator();

        $result = $generator->generate('2024-01-08', $this->context([
            'todayBotPv' => 100,
            'topBots'    => ['bot_a' => 90],
            'baseline'   => [
                'pageviews'     => $this->stats(100.0, 2.0),
                'errors_404'    => $this->stats(0.0, 1.0),
                'bot_pageviews' => $this->stats(10.0, 1.0),
            ],
        ]));

        $this->assertNotNull($result);
        $this->assertSame('bot_spike', $result['type']);
        $this->assertSame(['bot_a'], array_keys($result['meta']['top']));
    }

    /** Test bot generator resolves drop type. */
    public function testBotGeneratorDrop(): void
    {
        $generator = new BotTrafficChangeAlertGenerator();

        $result = $generator->generate('2024-01-08', $this->context([
            'todayBotPv' => 0,
            'baseline'   => [
                'pageviews'     => $this->stats(100.0, 2.0),
                'errors_404'    => $this->stats(0.0, 1.0),
                'bot_pageviews' => $this->stats(10.0, 1.0),
            ],
        ]));

        $this->assertNotNull($result);
        $this->assertSame('bot_drop', $result['type']);
    }

    /** Test severity calculation boundaries via probe subclass. */
    public function testSeverityCalculation(): void
    {
        $generator = new SeverityProbeGenerator();

        $this->assertSame('critical', $generator->probeSeverity(10.0, 2.576)); // ratio ~3.9
        $this->assertSame('warning', $generator->probeSeverity(4.0, 2.576));  // ratio ~1.55
        $this->assertSame('info', $generator->probeSeverity(3.0, 2.576));     // ratio ~1.16
        $this->assertSame('warning', $generator->probeSeverity(1.0, 0.0));    // threshold <= 0
    }

    /** Test change percent returns null for unstable baselines. */
    public function testChangePercentNullCases(): void
    {
        $generator = new SeverityProbeGenerator();

        $this->assertNull($generator->probeChangePercent(150, ['median' => 0.0, 's' => 1.0]));
        $this->assertNull($generator->probeChangePercent(150, ['median' => 100.0, 's' => 60.0]));
        $this->assertSame(50.0, $generator->probeChangePercent(150, ['median' => 100.0, 's' => 2.0]));
    }

    /** Build a generator context array. */
    private function context(array $overrides = []): array
    {
        return array_merge([
            'count'      => 7,
            'todayPv'    => 100,
            'today404'   => 0,
            'todayBotPv' => 0,
            'baseline'   => [
                'pageviews'     => $this->stats(100.0, 2.0),
                'errors_404'    => $this->stats(0.0, 1.0),
                'bot_pageviews' => $this->stats(0.0, 1.0),
            ],
            'top404'     => [],
            'topBots'    => [],
        ], $overrides);
    }

    /** Build a baseline stats array for one metric. */
    private function stats(float $median, float $s, float $avg = 0.0): array
    {
        return [
            'avg'    => $avg,
            'median' => $median,
            'mad'    => 0.0,
            'smad'   => 0.0,
            'spois'  => 0.0,
            'srel'   => 1.0,
            's'      => $s,
        ];
    }
}

/**
 * Probe subclass exposing protected helpers for testing.
 */
class SeverityProbeGenerator extends TrafficSpikeAlertGenerator
{
    /** Expose calculateSeverity. */
    public function probeSeverity(float $z, float $threshold): string
    {
        return $this->calculateSeverity($z, $threshold);
    }

    /** Expose buildChangePercent. */
    public function probeChangePercent(int $today, array $result): ?float
    {
        return $this->buildChangePercent($today, $result);
    }
}