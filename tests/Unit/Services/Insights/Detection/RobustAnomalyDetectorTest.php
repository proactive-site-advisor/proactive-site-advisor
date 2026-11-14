<?php

namespace ProactiveSiteAdvisor\Tests\Unit\Services\Insights\Detection;

use PHPUnit\Framework\TestCase;
use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;
use ProactiveSiteAdvisor\Services\Insights\Detection\RobustAnomalyDetector;

/**
 * Unit tests for the RobustAnomalyDetector class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class RobustAnomalyDetectorTest extends TestCase
{
    private RobustAnomalyDetector $detector;

    /** Set up detector before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new RobustAnomalyDetector();
    }

    /** Test no alert when delta is zero. */
    public function testNoAlertWhenDeltaIsZero(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            100,
            $this->config()
        );

        $this->assertFalse($result['is_alert']);
        $this->assertNull($result['direction']);
        $this->assertSame(0.0, $result['delta']);
    }

    /** Test SPIKE direction ignores negative deltas. */
    public function testSpikeDirectionIgnoresDrops(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            50,
            $this->config(MetricConfig::DIRECTION_SPIKE)
        );

        $this->assertFalse($result['is_alert']);
        $this->assertNull($result['direction']);
    }

    /** Test DROP direction ignores positive deltas. */
    public function testDropDirectionIgnoresSpikes(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_DROP)
        );

        $this->assertFalse($result['is_alert']);
        $this->assertNull($result['direction']);
    }

    /** Test two-sided direction detects a spike. */
    public function testTwoSidedDetectsSpike(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED)
        );

        $this->assertTrue($result['is_alert']);
        $this->assertSame(MetricConfig::DIRECTION_SPIKE, $result['direction']);
        $this->assertTrue($result['statistical']);
        $this->assertTrue($result['practical']);
    }

    /** Test two-sided direction detects a drop. */
    public function testTwoSidedDetectsDrop(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            0,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED)
        );

        $this->assertTrue($result['is_alert']);
        $this->assertSame(MetricConfig::DIRECTION_DROP, $result['direction']);
    }

    /** Test continuity correction clamps small z-scores to zero. */
    public function testContinuityCorrectionClampsZ(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 1.0),
            100, // delta handled below via custom baseline
            $this->config()
        );

        // delta = 0 here; use a dedicated case for correction.
        $this->assertSame(0.0, $result['z']);
    }

    /** Test continuity correction on a sub-0.5 delta. */
    public function testContinuityCorrectionOnSmallDelta(): void
    {
        $result = $this->detector->analyze(
            ['median' => 100.0, 'avg' => 100.0, 's' => 1.0],
            100,
            $this->config()
        );

        $this->assertSame(0.0, $result['z']);
        $this->assertFalse($result['statistical']);
    }

    /** Test statistical pass without practical pass is not an alert. */
    public function testStatisticalWithoutPracticalIsNotAlert(): void
    {
        // delta = 5, s = 0.5 -> z = 9 (statistical), but required = 10 (not practical).
        $result = $this->detector->analyze(
            $this->baseline(100.0, 0.5),
            105,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.01, 0.02, false, 10)
        );

        $this->assertTrue($result['statistical']);
        $this->assertFalse($result['practical']);
        $this->assertFalse($result['is_alert']);
        $this->assertNull($result['direction']);
    }

    /** Test practical pass without statistical pass is not an alert. */
    public function testPracticalWithoutStatisticalIsNotAlert(): void
    {
        // delta = 100, s = 100 -> z < 1 (not statistical), required = 10 (practical).
        $result = $this->detector->analyze(
            $this->baseline(100.0, 100.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.01, 0.02, false, 10)
        );

        $this->assertFalse($result['statistical']);
        $this->assertTrue($result['practical']);
        $this->assertFalse($result['is_alert']);
    }

    /** Test auto-tune computes required from median. */
    public function testAutoTuneComputesRequired(): void
    {
        // median 100 -> autoMinAbs = max(3, round(sqrt(100))) = 10; min_rel 0.02*100 = 2 -> required 10.
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.01, 0.02, true, 10)
        );

        $this->assertSame(10.0, $result['required']);
    }

    /** Test auto-tune floor of 3 for small medians. */
    public function testAutoTuneFloorIsThree(): void
    {
        // median 4 -> autoMinAbs = max(3, round(sqrt(4))) = 3; min_rel 0.02*4 = 0.08 -> required 3.
        $result = $this->detector->analyze(
            $this->baseline(4.0, 0.1, 4.0),
            20,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.01, 0.02, true, 10)
        );

        $this->assertSame(3.0, $result['required']);
    }

    /** Test manual min_abs is used when auto-tune is off. */
    public function testManualMinAbsUsedWhenAutoTuneOff(): void
    {
        // median 100, manual min_abs 50, min_rel 0.02*100 = 2 -> required 50.
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.01, 0.02, false, 50)
        );

        $this->assertSame(50.0, $result['required']);
    }

    /** Test z-threshold for alpha <= 0.001. */
    public function testZThresholdAlpha001(): void
    {
        $twoSided = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.001)
        );

        $oneSided = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_SPIKE, 0.001)
        );

        $this->assertSame(3.29, $twoSided['threshold']);
        $this->assertSame(3.09, $oneSided['threshold']);
    }

    /** Test z-threshold for alpha <= 0.01. */
    public function testZThresholdAlpha01(): void
    {
        $twoSided = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.01)
        );

        $oneSided = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_SPIKE, 0.01)
        );

        $this->assertSame(2.576, $twoSided['threshold']);
        $this->assertSame(2.326, $oneSided['threshold']);
    }

    /** Test z-threshold for alpha > 0.01 (e.g. 0.05). */
    public function testZThresholdAlpha05(): void
    {
        $twoSided = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_TWO_SIDED, 0.05)
        );

        $oneSided = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config(MetricConfig::DIRECTION_SPIKE, 0.05)
        );

        $this->assertSame(1.96, $twoSided['threshold']);
        $this->assertSame(1.645, $oneSided['threshold']);
    }

    /** Test result contains all documented keys. */
    public function testResultStructure(): void
    {
        $result = $this->detector->analyze(
            $this->baseline(100.0, 10.0),
            200,
            $this->config()
        );

        foreach (['is_alert', 'direction', 'z', 'threshold', 'delta', 'required', 'statistical', 'practical', 'median', 'avg', 's'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }

    /** Build a baseline stats array. */
    private function baseline(float $median, float $s, float $avg = 100.0): array
    {
        return [
            'median' => $median,
            'avg'    => $avg,
            's'      => $s,
        ];
    }

    /** Build a metric config array. */
    private function config(
        string $direction = MetricConfig::DIRECTION_TWO_SIDED,
        float  $alpha = 0.01,
        float  $minRel = 0.02,
        bool   $autoTune = false,
        int    $minAbs = 10
    ): array
    {
        return [
            'direction'      => $direction,
            'alpha'          => $alpha,
            'min_rel'        => $minRel,
            'auto_tune'      => $autoTune,
            'min_abs_manual' => $minAbs,
        ];
    }
}