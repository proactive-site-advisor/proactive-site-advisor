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

    /** Current seeding options. */
    protected array $options = [];

    /** Set seeding options. */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /** Get seeding options. */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get a seeding option.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
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