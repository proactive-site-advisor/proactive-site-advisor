<?php

namespace ProactiveSiteAdvisor\Services\Insights\Generators;

use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;
use ProactiveSiteAdvisor\Services\Insights\Contracts\AlertGeneratorInterface;
use ProactiveSiteAdvisor\Services\Insights\Detection\RobustAnomalyDetector;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base class for all metric-based alert generators.
 *
 * Handles warm-up, runs the anomaly detector, computes severity,
 * and prepares common meta. Concrete generators only specify
 * which metric, which direction, and how to build the final meta.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Generators
 * @since   1.0.0
 */
abstract class AbstractTrafficAlertGenerator implements AlertGeneratorInterface
{
    /** Minimum days required for baseline. */
    protected const MIN_BASELINE_DAYS = 7;

    /** Anomaly detector instance. */
    protected RobustAnomalyDetector $detector;

    /** Constructor. */
    public function __construct()
    {
        $this->detector = new RobustAnomalyDetector();
    }

    /** {@inheritDoc} */
    public function isEligible(array $context): bool
    {
        if ((int)$context['count'] < self::MIN_BASELINE_DAYS) {
            return false;
        }

        return $this->isEnabled();
    }

    /** {@inheritDoc} */
    public function generate(string $date, array $context): ?array
    {
        $config = MetricConfig::get($this->getMetric());

        $baselineKey = $config['baseline_key'];
        $todayKey    = $config['today_key'];

        $baseline = $context['baseline'][$baselineKey];
        $today    = (int)$context[$todayKey];

        $config['direction'] = $this->getDirection();

        $result = $this->detector->analyze($baseline, $today, $config);

        if (!$result['is_alert']) {
            return null;
        }

        $severity = $this->calculateSeverity($result['z'], $result['threshold']);

        return [
            'type'     => $this->resolveAlertType($result['direction']),
            'severity' => $severity,
            'meta'     => $this->buildMeta($today, $result, $context),
        ];
    }

    /** Which metric this generator watches. */
    abstract protected function getMetric(): string;

    /** Which direction this generator watches. */
    abstract protected function getDirection(): string;

    /** Whether the corresponding alert toggle is enabled. */
    abstract protected function isEnabled(): bool;

    /** Resolve the final alert type string based on anomaly direction. */
    abstract protected function resolveAlertType(string $direction): string;

    /** Build the alert meta array. */
    abstract protected function buildMeta(int $today, array $result, array $context): array;

    /** Compute severity from z and threshold. */
    protected function calculateSeverity(float $z, float $threshold): string
    {
        if ($threshold <= 0) {
            return 'warning';
        }

        $ratio = $z / $threshold;

        if ($ratio >= 2.5) {
            return 'critical';
        }

        if ($ratio >= 1.5) {
            return 'warning';
        }

        return 'info';
    }

    /** Compute change percent from today and average. Returns null when avg is zero. */
    protected function buildChangePercent(int $today, float $avg): ?float
    {
        if ($avg <= 0) {
            return null;
        }

        return round((($today / $avg) - 1) * 100, 2);
    }

    /** Build the common meta keys shared by all alerts. */
    protected function buildCommonMeta(int $today, array $result): array
    {
        return [
            'today'      => $today,
            'avg7'       => (int)round($result['avg']),
            'median7'    => (int)round($result['median']),
            'delta'      => $result['delta'],
            'change_pct' => $this->buildChangePercent($today, $result['avg']),
        ];
    }

    /** Sort an associative array by value descending and keep the top N entries. */
    protected function topN(array $items, int $limit = 3): array
    {
        arsort($items);

        return array_slice($items, 0, $limit, true);
    }
}