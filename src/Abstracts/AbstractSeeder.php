<?php

namespace ProactiveSiteAdvisor\Abstracts;

use ProactiveSiteAdvisor\Database\DataStore;
use ProactiveSiteAdvisor\Database\TableMaintenance;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base class for database seeders.
 *
 * @see    \WP_CLI
 * @package ProactiveSiteAdvisor\Abstracts
 * @since   1.0.0
 */
abstract class AbstractSeeder
{
    /** The table name this seeder operates on (without prefix). */
    protected string $table = '';

    /** Seeder priority (lower runs first). */
    protected int $priority = 10;

    /** Current seeding pattern. */
    protected string $pattern = 'realistic';

    /** Number of days to seed. */
    protected int $days = 30;

    /** Set the seeding pattern. */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /** Get the seeding pattern. */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /** Set the number of days to seed. */
    public function setDays(int $days): self
    {
        $this->days = max(1, $days);

        return $this;
    }

    /** Get the number of days to seed. */
    public function getDays(): int
    {
        return $this->days;
    }

    /** Run the seeder. */
    abstract public function run(): int;

    /** Clean existing data before seeding. */
    public function clean(): int
    {
        if (empty($this->table)) {
            return 0;
        }

        $count = DataStore::getRowCount($this->table);
        TableMaintenance::truncateTable($this->table);

        $this->log("Truncated $this->table: $count records removed");

        return $count;
    }

    /** Get the seeder priority. */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /** Get the table name. */
    public function getTable(): string
    {
        return $this->table;
    }

    /** Get date range for seeding. */
    protected function getDateRange(): array
    {
        $dates   = [];
        $endDate = current_time('Y-m-d');
        $endTs   = strtotime($endDate);

        for ($i = $this->days - 1; $i >= 0; $i--) {
            $dates[] = gmdate('Y-m-d', strtotime("-$i days", $endTs));
        }

        return $dates;
    }

    /** Log progress message. */
    protected function log(string $message): void
    {
        if (class_exists('WP_CLI')) {
            \WP_CLI::log($message);
        }
    }

    /** Log success message. */
    protected function success(string $message): void
    {
        if (class_exists('WP_CLI')) {
            \WP_CLI::success($message);
        }
    }

    /** Log warning message. */
    protected function warning(string $message): void
    {
        if (class_exists('WP_CLI')) {
            \WP_CLI::warning($message);
        }
    }
}