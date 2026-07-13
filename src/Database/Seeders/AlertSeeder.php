<?php

namespace ProactiveSiteAdvisor\Database\Seeders;

use ProactiveSiteAdvisor\Abstracts\AbstractSeeder;
use ProactiveSiteAdvisor\Database\Factories\AlertFactory;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seeds the alerts table with fake alert data.
 *
 * @package ProactiveSiteAdvisor\Database\Seeders
 * @since   1.0.0
 */
class AlertSeeder extends AbstractSeeder
{
    /** Table name. */
    protected string $table = 'alerts';

    /** Factory instance. */
    private AlertFactory $factory;

    /** Constructor. */
    public function __construct()
    {
        $this->factory = new AlertFactory();
    }

    /** Run the seeder. */
    public function run(): int
    {
        $this->factory->setPattern($this->pattern);

        $this->log("Seeding alerts with '$this->pattern' pattern for $this->days days...");

        if ($this->pattern === 'alerts') {
            return $this->seedAlerts();
        }

        return $this->seedRealistic();
    }

    /** Seed with realistic pattern - creates occasional random alerts (~10% of days). */
    private function seedRealistic(): int
    {
        $dates = $this->getDateRange();
        $count = 0;

        foreach ($dates as $date) {
            if (wp_rand(1, 100) <= 10) {
                $alert = $this->factory->randomAlert($date);

                if ($alert !== null) {
                    $count++;
                }
            }
        }

        $this->success("Created $count alert records");

        return $count;
    }

    /** Seed with alerts pattern - creates alerts on specific days to match DailyStatsSeeder pattern. */
    private function seedAlerts(): int
    {
        $dates = $this->getDateRange();
        $count = 0;

        foreach ($dates as $index => $date) {
            $dayIndex = $index + 1;
            $alert    = null;

            if ($dayIndex === 10) {
                $percentDrop = wp_rand(35, 55);
                $alert       = $this->factory->trafficDrop($date, $percentDrop);
            }

            if ($dayIndex === 20) {
                $percentIncrease = wp_rand(70, 110);
                $alert           = $this->factory->trafficSpike($date, $percentIncrease);
            }

            if ($dayIndex === 25) {
                $errorCount = wp_rand(50, 80);
                $average    = wp_rand(10, 15);
                $alert      = $this->factory->error404Spike($date, $errorCount, $average);
            }

            if ($alert !== null) {
                $count++;
            }
        }

        $this->success("Created $count alert records");

        return $count;
    }
}