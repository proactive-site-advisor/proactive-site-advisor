<?php

namespace ProactiveSiteAdvisor\Abstracts;

use ProactiveSiteAdvisor\Cron\CronManager;
use ProactiveSiteAdvisor\Cron\CronTask;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base class for creating cron tasks with OOP pattern.
 *
 * @package ProactiveSiteAdvisor\Abstracts
 * @since   1.0.0
 */
abstract class AbstractCronTask
{
    /** Task hook name (must be unique). */
    protected string $hook;

    /** Recurrence interval. */
    protected string $recurrence = 'hourly';

    /** Whether task is enabled. */
    protected bool $enabled = true;

    /** Cron task instance. */
    protected ?CronTask $task = null;

    /** Register the cron task. */
    public function register(): void
    {
        $this->task = CronTask::make($this->hook, [$this, 'handle'], $this->recurrence);

        if (!$this->enabled) {
            $this->task->disable();
        }

        $this->configure($this->task);

        CronManager::instance()->addTask($this->task);
    }

    /** Configure the cron task. */
    protected function configure(CronTask $task): void
    {
    }

    /** Handle the cron task execution. */
    abstract public function handle(): void;

    /** Get the task hook name. */
    public function getHook(): string
    {
        return $this->hook;
    }

    /** Get the recurrence interval. */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    /** Check if task is enabled. */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /** Get the cron task instance. */
    public function getTask(): ?CronTask
    {
        return $this->task;
    }

    /** Check if task is scheduled. */
    public function isScheduled(): bool
    {
        return $this->task && $this->task->isScheduled();
    }

    /** Get next scheduled run. */
    public function getNextRun()
    {
        return $this->task ? $this->task->getNextRun() : false;
    }

    /** Run the task immediately. */
    public function runNow(): void
    {
        $this->handle();
    }

    /** Schedule the task. */
    public function schedule(): bool
    {
        if ($this->task === null) {
            return false;
        }

        return CronManager::instance()->schedule($this->task);
    }

    /** Unschedule the task. */
    public function unschedule(): bool
    {
        return CronManager::instance()->unschedule($this->hook);
    }
}