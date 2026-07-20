<?php

namespace ProactiveSiteAdvisor\Database\Seeders;

use ProactiveSiteAdvisor\Abstracts\AbstractSeeder;
use ProactiveSiteAdvisor\Database\Factories\DailyStatsFactory;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seeds the daily_stats table with fake traffic data.
 *
 * @package ProactiveSiteAdvisor\Database\Seeders
 * @since   1.0.0
 */
class DailyStatsSeeder extends AbstractSeeder
{
    /** Table name. */
    protected string $table = 'daily_stats';

    /** Seeder priority (runs first, alerts depend on stats). */
    protected int $priority = 5;

    /** Factory instance. */
    private DailyStatsFactory $factory;

    /** Constructor. */
    public function __construct()
    {
        $this->factory = new DailyStatsFactory();
    }

    /** Run the seeder. */
    public function run(): int
    {
        $pattern = $this->option('pattern', 'realistic');
        $this->factory->setPattern($pattern);

        $days = $this->option('days', 30);
        $this->log("Seeding daily_stats with '$pattern' pattern for $days days...");

        $dates = $this->getDateRange($days);
        $count = 0;

        foreach ($dates as $index => $date) {
            $dayIndex = $index + 1;
            $record   = $this->factory->forDate($date, $dayIndex);

            if ($record !== null) {
                $count++;
            }
        }

        $this->success("Created $count daily_stats records");

        return $count;
    }

    /** Get date range for seeding. */
    private function getDateRange(int $days): array
    {
        $dates   = [];
        $endDate = current_time('Y-m-d');
        $endTs   = strtotime($endDate);

        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = gmdate('Y-m-d', strtotime("-$i days", $endTs));
        }

        return $dates;
    }
}