<?php

namespace ProactiveSiteAdvisor\Cron;

use ProactiveSiteAdvisor\Abstracts\AbstractCronTask;
use ProactiveSiteAdvisor\Utils\Logger;
use Throwable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Central cron handler that runs daily at midnight.
 *
 * @package ProactiveSiteAdvisor\Cron
 * @since   1.0.0
 */
class DailyCronHandler extends AbstractCronTask
{
    /** Task hook name. */
    protected string $hook = 'proactive_site_advisor_daily_cron';

    /** Recurrence interval. */
    protected string $recurrence = 'daily';

    /** Services to run daily. */
    private array $services = [];

    /** Configure the cron task. */
    protected function configure(CronTask $task): void
    {
        $task->startTodayAt(0);
    }

    /** Execute all registered daily services. */
    public function handle(): void
    {
        /**
         * Filters the daily cron services before execution.
         *
         * @param array $services Array of service class names.
         * @since  1.0.0
         */
        $services = apply_filters('proactive_site_advisor_daily_cron_services', $this->services);

        Logger::debug('Daily cron started', ['service_count' => count($services)]);

        /**
         * Fires before any daily cron services run.
         *
         * @since 1.0.0
         */
        do_action('proactive_site_advisor_daily_cron_before_dispatch');

        $results = [];

        foreach ($services as $serviceClass) {
            $result                 = $this->executeService($serviceClass);
            $results[$serviceClass] = $result;
        }

        /**
         * Fires after all daily cron services complete.
         *
         * @param array $results Array of service results [class => ['success' => bool, 'duration' => float]].
         * @since  1.0.0
         */
        do_action('proactive_site_advisor_daily_cron_after_dispatch', $results);

        Logger::debug('Daily cron completed', ['results' => $results]);
    }

    /**
     * Execute a single service with error handling.
     */
    private function executeService(string $serviceClass): array
    {
        $serviceId = $this->getServiceId($serviceClass);

        /**
         * Filters whether a specific service should run.
         *
         * @param bool $enabled Whether the service is enabled.
         * @param string $serviceId The service identifier.
         * @since  1.0.0
         */
        $enabled = (bool)apply_filters('proactive_site_advisor_daily_cron_service_enabled', true, $serviceId);

        if (!$enabled) {
            Logger::debug("Service skipped (disabled): $serviceId");
            return ['success' => true, 'duration' => 0, 'skipped' => true];
        }

        if (!class_exists($serviceClass)) {
            Logger::error("Service class not found: $serviceClass");
            return ['success' => false, 'duration' => 0, 'error' => 'Class not found'];
        }

        try {
            /**
             * Fires before a daily cron service executes.
             *
             * @param string $serviceId The service identifier.
             * @since  1.0.0
             */
            do_action('proactive_site_advisor_daily_cron_before_service', $serviceId);

            $startTime = microtime(true);
            $service   = method_exists($serviceClass, 'instance') ? $serviceClass::instance() : new $serviceClass();
            $service->run();
            $duration = microtime(true) - $startTime;

            /**
             * Fires after a daily cron service completes successfully.
             *
             * @param string $serviceId The service identifier.
             * @param bool $success Whether the service succeeded.
             * @param float $duration Execution time in seconds.
             * @since  1.0.0
             */
            do_action('proactive_site_advisor_daily_cron_after_service', $serviceId, true, $duration);

            Logger::debug("Service completed: $serviceId", ['duration' => round($duration, 4)]);

            return ['success' => true, 'duration' => $duration];
        } catch (Throwable $e) {
            $duration = microtime(true) - ($startTime ?? microtime(true));

            Logger::error("Service failed: $serviceId", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            /**
             * Fires after a daily cron service fails.
             *
             * @param string $serviceId The service identifier.
             * @param bool $success Whether the service succeeded.
             * @param float $duration Execution time in seconds.
             * @since  1.0.0
             */
            do_action('proactive_site_advisor_daily_cron_after_service', $serviceId, false, $duration);

            return ['success' => false, 'duration' => $duration, 'error' => $e->getMessage()];
        }
    }

    /** Get a readable service identifier from class name. */
    private function getServiceId(string $serviceClass): string
    {
        return basename(str_replace('\\', '/', $serviceClass));
    }

    /** Get registered services. */
    public function getServices(): array
    {
        return $this->services;
    }

    /** Add a service to the daily cron. */
    public function addService(string $serviceClass): self
    {
        if (!in_array($serviceClass, $this->services, true)) {
            $this->services[] = $serviceClass;
        }
        return $this;
    }

    /** Remove a service from the daily cron. */
    public function removeService(string $serviceClass): self
    {
        $this->services = array_filter(
            $this->services,
            static fn($service) => $service !== $serviceClass
        );
        return $this;
    }
}