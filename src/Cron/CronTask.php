<?php

namespace ProactiveSiteAdvisor\Cron;

use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Represents a single cron task configuration.
 *
 * @package ProactiveSiteAdvisor\Cron
 * @since   1.0.0
 */
class CronTask
{
    /** Task hook name. */
    private string $hook;

    /** Callback function. */
    private $callback;

    /** Recurrence interval. */
    private string $recurrence;

    /** First run timestamp. */
    private int $firstRun;

    /** Arguments to pass to callback. */
    private array $args = [];

    /** Whether task is enabled. */
    private bool $enabled = true;

    /** Constructor. */
    public function __construct(string $hook, callable $callback, string $recurrence = 'hourly')
    {
        $this->hook       = $hook;
        $this->callback   = $callback;
        $this->recurrence = $recurrence;
        $this->firstRun   = DateTimeUtils::timestamp();
    }

    /** Create a new cron task. */
    public static function make(string $hook, callable $callback, string $recurrence = 'hourly'): self
    {
        return new self($hook, $callback, $recurrence);
    }

    /** Set the recurrence interval. */
    public function recurrence(string $recurrence): self
    {
        $this->recurrence = $recurrence;
        return $this;
    }

    /** Set hourly recurrence. */
    public function hourly(): self
    {
        $this->recurrence = 'hourly';
        return $this;
    }

    /** Set twice daily recurrence. */
    public function twiceDaily(): self
    {
        $this->recurrence = 'twicedaily';
        return $this;
    }

    /** Set daily recurrence. */
    public function daily(): self
    {
        $this->recurrence = 'daily';
        return $this;
    }

    /** Set weekly recurrence. */
    public function weekly(): self
    {
        $this->recurrence = 'weekly';
        return $this;
    }

    /** Set the first run time. */
    public function startAt(int $timestamp): self
    {
        $this->firstRun = $timestamp;
        return $this;
    }

    /** Set first run to now. */
    public function startNow(): self
    {
        $this->firstRun = DateTimeUtils::timestamp();
        return $this;
    }

    /** Set first run to a specific time today (WordPress timezone aware). */
    public function startTodayAt(int $hour, int $minute = 0): self
    {
        $now         = DateTimeUtils::current();
        $todayTarget = $now->setTime($hour, $minute);

        if ($todayTarget->getTimestamp() < $now->getTimestamp()) {
            $todayTarget = $todayTarget->modify('+1 day');
        }

        $this->firstRun = $todayTarget->getTimestamp();

        return $this;
    }

    /** Set callback arguments. */
    public function args(array $args): self
    {
        $this->args = $args;
        return $this;
    }

    /** Enable the task. */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    /** Disable the task. */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /** Get the hook name. */
    public function getHook(): string
    {
        return $this->hook;
    }

    /** Get the callback. */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /** Get the recurrence. */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    /** Get the first run timestamp. */
    public function getFirstRun(): int
    {
        return $this->firstRun;
    }

    /** Get the callback arguments. */
    public function getArgs(): array
    {
        return $this->args;
    }

    /** Check if task is enabled. */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /** Check if task is scheduled. */
    public function isScheduled(): bool
    {
        return wp_next_scheduled($this->hook, $this->args) !== false;
    }

    /** Get next scheduled run. */
    public function getNextRun()
    {
        return wp_next_scheduled($this->hook, $this->args);
    }
}