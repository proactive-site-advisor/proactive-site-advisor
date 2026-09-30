<?php

namespace ProactiveSiteAdvisor\Services\Insights\Detection;

use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects anomalies using robust statistics.
 *
 * Combines median/MAD baseline, count-aware noise floor,
 * robust z-score, and a dual statistical + practical decision.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Detection
 * @since   1.0.0
 */
class RobustAnomalyDetector
{
    /** Continuity correction for count data. */
    private const CONTINUITY_CORRECTION = 0.5;

    /** Auto-tune formula constants. */
    private const AUTO_MIN_ABS_FLOOR = 3;

    /**
     * Analyze a single metric.
     *
     * @param array $baseline Stats array from BaselineCalculator (contains avg, median, mad, smad, spois, srel, s).
     * @param int $today Today's value.
     * @param array $config Configuration from MetricConfig::get().
     *
     * @return array {
     * @type bool $is_alert
     * @type string|null $direction 'SPIKE' | 'DROP' | null
     * @type float $z
     * @type float $delta
     * @type float $required
     * @type bool $statistical
     * @type bool $practical
     * @type float $median
     * @type float $avg
     * }
     */
    public function analyze(array $baseline, int $today, array $config): array
    {
        $median = (float)$baseline['median'];
        $s      = (float)$baseline['s'];
        $avg    = (float)$baseline['avg'];

        $delta = $today - $median;

        $direction = $this->resolveDirection($delta, $config['direction']);

        if ($direction === null) {
            return $this->noAlertResult($median, $avg, $delta);
        }

        $z = (abs($delta) - self::CONTINUITY_CORRECTION) / $s;

        if ($z < 0) {
            $z = 0.0;
        }

        $statistical = $this->isStatistical($z, $config['alpha'], $config['direction']);

        $required  = $this->computeRequired($median, $config);
        $practical = (abs($delta) >= $required);

        $isAlert = $statistical && $practical;

        return [
            'is_alert'    => $isAlert,
            'direction'   => $isAlert ? $direction : null,
            'z'           => $z,
            'threshold'   => $this->getZThreshold($config['alpha'], $this->getSidesForDirection($config['direction'])),
            'delta'       => $delta,
            'required'    => $required,
            'statistical' => $statistical,
            'practical'   => $practical,
            'median'      => $median,
            'avg'         => $avg,
        ];
    }

    /** Resolve the direction of the anomaly, or null if it should be ignored. */
    private function resolveDirection(float $delta, string $configured): ?string
    {
        if ($delta === 0.0) {
            return null;
        }

        if ($configured === MetricConfig::DIRECTION_SPIKE) {
            return $delta > 0 ? MetricConfig::DIRECTION_SPIKE : null;
        }

        if ($configured === MetricConfig::DIRECTION_DROP) {
            return $delta < 0 ? MetricConfig::DIRECTION_DROP : null;
        }

        return $delta > 0 ? MetricConfig::DIRECTION_SPIKE : MetricConfig::DIRECTION_DROP;
    }

    /** Check if z-score passes the statistical threshold. */
    private function isStatistical(float $z, float $alpha, string $direction): bool
    {
        $sides = $this->getSidesForDirection($direction);
        return $z > $this->getZThreshold($alpha, $sides);
    }

    /** Get z-threshold from alpha using explicit branching. */
    private function getZThreshold(float $alpha, string $sides): float
    {
        $twoSided = ($sides === 'two');

        if ($alpha <= 0.001) {
            return $twoSided ? 3.290 : 3.090;
        }

        if ($alpha <= 0.01) {
            return $twoSided ? 2.576 : 2.326;
        }

        return $twoSided ? 1.960 : 1.645;
    }

    /** Compute required practical change. */
    private function computeRequired(float $median, array $config): float
    {
        if ($config['auto_tune']) {
            $minAbs = $this->autoMinAbs($median);
        } else {
            $minAbs = (float)$config['min_abs_manual'];
        }

        $minRelValue = $config['min_rel'] * $median;

        return max($minAbs, $minRelValue);
    }

    /** Auto-tune min_abs formula: max(3, round(sqrt(median))). */
    private function autoMinAbs(float $median): float
    {
        $value = round(sqrt(max($median, 0.0)));

        return max((float)self::AUTO_MIN_ABS_FLOOR, $value);
    }

    /** Return a safe no-alert result. */
    private function noAlertResult(float $median, float $avg, float $delta): array
    {
        return [
            'is_alert'    => false,
            'direction'   => null,
            'z'           => 0.0,
            'threshold'   => 0.0,
            'delta'       => $delta,
            'required'    => 0.0,
            'statistical' => false,
            'practical'   => false,
            'median'      => $median,
            'avg'         => $avg,
        ];
    }

    /** Get 'one' or 'two' for z-threshold lookup. */
    private function getSidesForDirection(string $direction): string
    {
        return $direction === MetricConfig::DIRECTION_TWO_SIDED ? 'two' : 'one';
    }
}