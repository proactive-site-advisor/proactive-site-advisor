<?php

namespace ProactiveSiteAdvisor\Database\Seeders;

use ProactiveSiteAdvisor\Abstracts\AbstractSeeder;
use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ReflectionClass;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages and orchestrates database seeders.
 *
 * @package ProactiveSiteAdvisor\Database\Seeders
 * @since   1.0.0
 */
class SeederManager extends AbstractSingleton
{
    /** Registered seeder classes. */
    private array $seeders = [
        DailyStatsSeeder::class,
        AlertSeeder::class,
    ];

    /** Current seeding options. */
    private array $options = [];

    /** Register a seeder class. */
    public function addSeeder(string $seederClass): self
    {
        if (!in_array($seederClass, $this->seeders, true)) {
            $this->seeders[] = $seederClass;
        }

        return $this;
    }

    /** Remove a seeder class. */
    public function removeSeeder(string $seederClass): self
    {
        $this->seeders = array_values(array_filter(
            $this->seeders,
            static function ($class) use ($seederClass) {
                return $class !== $seederClass;
            }
        ));

        return $this;
    }

    /** Get all registered seeders. */
    public function getSeeders(): array
    {
        return $this->seeders;
    }

    /** Set seeding options. */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /** Get current options. */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** Run all registered seeders. */
    public function runAll(array $options = []): array
    {
        $this->setOptions($options);

        $seeders = $this->getSortedSeeders();
        $results = [];

        foreach ($seeders as $seeder) {
            $class           = get_class($seeder);
            $results[$class] = $seeder->run();
        }

        return $results;
    }

    /** Run a specific seeder by short name. */
    public function run(string $shortName, array $options = []): ?int
    {
        $this->setOptions($options);

        $seeder = $this->findSeederByShortName($shortName);

        if ($seeder === null) {
            return null;
        }

        $seeder->setOptions($this->options);

        return $seeder->run();
    }

    /** Clean all tables before seeding. */
    public function cleanAll(): array
    {
        $seeders = $this->getSortedSeeders();
        $results = [];

        $seeders = array_reverse($seeders);

        foreach ($seeders as $seeder) {
            $class           = get_class($seeder);
            $results[$class] = $seeder->clean();
        }

        return $results;
    }

    /** Get sorted seeders by priority. */
    private function getSortedSeeders(): array
    {
        $seederClasses = $this->getSeeders();
        $seeders       = [];

        foreach ($seederClasses as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $seeder = new $class();

            if (!$seeder instanceof AbstractSeeder) {
                continue;
            }

            $seeder->setOptions($this->options);

            $seeders[] = $seeder;
        }

        usort($seeders, static function (AbstractSeeder $a, AbstractSeeder $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $seeders;
    }

    /** Find a seeder by short name. */
    private function findSeederByShortName(string $shortName): ?AbstractSeeder
    {
        $seederClasses  = $this->getSeeders();
        $normalizedName = strtolower($shortName);

        foreach ($seederClasses as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $className      = (new ReflectionClass($class))->getShortName();
            $classShortName = str_replace('Seeder', '', $className);

            if (strtolower($classShortName) === $normalizedName) {
                $seeder = new $class();

                if ($seeder instanceof AbstractSeeder) {
                    return $seeder;
                }
            }
        }

        return null;
    }

    /** Get list of available seeder short names. */
    public function getAvailableSeederNames(): array
    {
        $names = [];

        foreach ($this->getSeeders() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $className = (new ReflectionClass($class))->getShortName();
            $names[]   = str_replace('Seeder', '', $className);
        }

        return $names;
    }
}