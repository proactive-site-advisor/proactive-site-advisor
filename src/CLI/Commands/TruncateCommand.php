<?php

namespace ProactiveSiteAdvisor\CLI\Commands;

use ProactiveSiteAdvisor\Database\Seeders\SeederManager;
use ReflectionClass;
use ReflectionException;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WP-CLI command for truncating database tables.
 *
 * @see    \WP_CLI
 * @package ProactiveSiteAdvisor\CLI
 * @since   1.0.0
 */
class TruncateCommand
{
    /**
     * Truncate database tables.
     *
     * ## OPTIONS
     *
     * [--seeder=<seeder>]
     * : Truncate only a specific table (e.g., 'DailyStats' or 'Alert').
     *
     * [--yes]
     * : Skip confirmation prompt.
     *
     * ## EXAMPLES
     *
     *     # Truncate all tables
     *     wp proactive-site-advisor truncate
     *
     *     # Truncate only alerts table
     *     wp proactive-site-advisor truncate --seeder=Alert
     *
     *     # Truncate only stats table
     *     wp proactive-site-advisor truncate --seeder=DailyStats
     *
     *     # Skip confirmation
     *     wp proactive-site-advisor truncate --yes
     *
     * @throws ReflectionException
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        $manager          = SeederManager::instance();
        $seeder           = $assocArgs['seeder'] ?? '';
        $skipConfirmation = isset($assocArgs['yes']);

        \WP_CLI::log('');
        \WP_CLI::log('Proactive Site Advisor Database Truncate');
        \WP_CLI::log('==============================');
        \WP_CLI::log('');

        if (!empty($seeder)) {
            $available = $manager->getAvailableSeederNames();

            if (!in_array($seeder, $available, true) && !in_array(ucfirst($seeder), $available, true)) {
                $availableList = implode(', ', $available);
                \WP_CLI::error("Seeder '$seeder' not found. Available: $availableList");

                return;
            }

            $target = "'$seeder' table";
        } else {
            $target = 'all plugin tables';
        }

        if (!$skipConfirmation) {
            \WP_CLI::confirm("Are you sure you want to truncate $target? This cannot be undone.");
        }

        $startTime = microtime(true);

        if (!empty($seeder)) {
            $this->truncateSpecific($manager, $seeder);
        } else {
            $this->truncateAll($manager);
        }

        $elapsed = round(microtime(true) - $startTime, 2);

        \WP_CLI::log('');
        \WP_CLI::success("Truncation completed in {$elapsed}s");
    }

    /** Truncate a specific table. */
    private function truncateSpecific(SeederManager $manager, string $seederName): void
    {
        $seeders = $manager->getSeeders();

        foreach ($seeders as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $className      = (new ReflectionClass($class))->getShortName();
            $classShortName = str_replace('Seeder', '', $className);

            if (strtolower($classShortName) === strtolower($seederName)) {
                $seeder  = new $class();
                $deleted = $seeder->clean();

                if (class_exists('WP_CLI')) {
                    \WP_CLI::log("Truncated $classShortName: $deleted records deleted");
                }

                return;
            }
        }
    }

    /**
     * Truncate all tables.
     *
     * @throws ReflectionException
     */
    private function truncateAll(SeederManager $manager): void
    {
        $results = $manager->cleanAll();

        foreach ($results as $class => $deleted) {
            $className = (new ReflectionClass($class))->getShortName();
            $shortName = str_replace('Seeder', '', $className);

            if (class_exists('WP_CLI')) {
                \WP_CLI::log("Truncated $shortName: $deleted records deleted");
            }
        }
    }
}