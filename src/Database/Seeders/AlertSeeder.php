<?php

namespace ProactiveSiteAdvisor\Database\Seeders;

use ProactiveSiteAdvisor\Abstracts\AbstractSeeder;
use ProactiveSiteAdvisor\Database\Factories\AlertFactory;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

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
        $pattern = $this->option('pattern', 'realistic');
        $this->factory->setPattern($pattern);

        $days = $this->option('days', 30);
        $this->log("Seeding alerts with '$pattern' pattern for $days days...");

        if ($pattern === 'alerts') {
            return $this->seedAlerts($days);
        }

        return $this->seedRealistic($days);
    }

    /** Seed with realistic pattern - creates occasional random alerts (~10% of days). */
    private function seedRealistic(int $days): int
    {
        $dates = $this->getDateRange($days);
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
    private function seedAlerts(int $days): int
    {
        $dates = $this->getDateRange($days);
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

    /** Get date range for seeding. */
    private function getDateRange(int $days): array
    {
        $dates   = [];
        $endDate = DateTimeUtils::current()->modify('-1 day');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date    = $endDate->modify('-' . ($days - 1 - $i) . ' days')->format('Y-m-d');
            $dates[] = $date;
        }

        return $dates;
    }
}