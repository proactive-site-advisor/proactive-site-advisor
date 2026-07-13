<?php

namespace ProactiveSiteAdvisor\Abstracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base class for model factories providing fake data generation.
 *
 * @package ProactiveSiteAdvisor\Abstracts
 * @since   1.0.0
 */
abstract class AbstractFactory
{
    /**
     * The model class this factory creates.
     *
     * @var class-string<AbstractModel>
     */
    protected string $model = '';

    /** Current pattern context. */
    protected string $pattern = 'realistic';

    /** Set the pattern context. */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /** Get the current pattern. */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /** Define default attributes for the model. */
    abstract protected function definition(): array;

    /** Make a model instance without saving. */
    public function make(array $attributes = []): object
    {
        $modelClass = $this->model;
        $data       = array_merge($this->definition(), $attributes);

        return $modelClass::make($data);
    }

    /** Create and save a model instance. */
    public function create(array $attributes = []): ?object
    {
        $modelClass = $this->model;
        $data       = array_merge($this->definition(), $attributes);

        return $modelClass::create($data);
    }

    /** Create multiple model instances. */
    public function createMany(int $count, array $attributes = []): array
    {
        $models = [];

        for ($i = 0; $i < $count; $i++) {
            $model = $this->create($attributes);

            if ($model !== null) {
                $models[] = $model;
            }
        }

        return $models;
    }

    /** Generate a random integer within range. */
    protected function randomInt(int $min, int $max): int
    {
        return wp_rand($min, $max);
    }

    /** Generate a random float within range. */
    protected function randomFloat(float $min, float $max, int $decimals = 2): float
    {
        $scale = 10 ** $decimals;
        $value = wp_rand((int)($min * $scale), (int)($max * $scale)) / $scale;

        return round($value, $decimals);
    }

    /** Pick a random element from an array. */
    protected function randomElement(array $items)
    {
        if (empty($items)) {
            return null;
        }

        $index = wp_rand(0, count($items) - 1);

        return $items[$index];
    }

    /** Pick multiple random elements from an array. */
    protected function randomElements(array $items, int $count): array
    {
        if (empty($items) || $count <= 0) {
            return [];
        }

        $shuffled = $items;
        shuffle($shuffled);

        return array_slice($shuffled, 0, min($count, count($items)));
    }

    /** Generate a random date within range. */
    protected function randomDate(string $start, string $end): string
    {
        $startTs = strtotime($start);
        $endTs   = strtotime($end);

        $randomTs = wp_rand($startTs, $endTs);

        return gmdate('Y-m-d', $randomTs);
    }

    /** Generate a date range array. */
    protected function dateRange(int $days, string $endDate = ''): array
    {
        if (empty($endDate)) {
            $endDate = (string)current_time('Y-m-d');
        }

        $dates = [];
        $endTs = strtotime($endDate);

        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = gmdate('Y-m-d', strtotime("-$i days", $endTs));
        }

        return $dates;
    }

    /** Check if a date falls on a weekend. */
    protected function isWeekend(string $date): bool
    {
        $dayOfWeek = (int)gmdate('N', strtotime($date));

        return $dayOfWeek >= 6;
    }
}