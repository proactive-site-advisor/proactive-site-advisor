<?php

namespace ProactiveSiteAdvisor\Database;

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Config\PrefixConfig;

/**
 * Fluent interface for building database table schemas.
 *
 * @package ProactiveSiteAdvisor\Database
 * @since   1.0.0
 */
class TableSchema
{
    /** Table name (without prefix) */
    private string $name;

    /** Table columns */
    private array $columns = [];

    /** Current column being defined */
    private ?string $currentColumn = null;

    /** Primary key column */
    private ?string $primaryKey = null;

    /** Table indexes */
    private array $indexes = [];

    /** Unique indexes */
    private array $uniqueIndexes = [];

    /** Constructor. */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /** Add an auto-incrementing primary key column. */
    public function id(string $name = 'id'): self
    {
        $this->columns[$name] = [
            'type'           => 'BIGINT',
            'length'         => 20,
            'unsigned'       => true,
            'nullable'       => false,
            'auto_increment' => true,
        ];
        $this->primaryKey     = $name;
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a BIGINT column. */
    public function bigInt(string $name, bool $unsigned = true): self
    {
        $this->columns[$name] = [
            'type'     => 'BIGINT',
            'length'   => 20,
            'unsigned' => $unsigned,
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add an INT column. */
    public function int(string $name, bool $unsigned = true): self
    {
        $this->columns[$name] = [
            'type'     => 'INT',
            'length'   => 11,
            'unsigned' => $unsigned,
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a TINYINT column. */
    public function tinyInt(string $name, bool $unsigned = true): self
    {
        $this->columns[$name] = [
            'type'     => 'TINYINT',
            'length'   => 4,
            'unsigned' => $unsigned,
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a VARCHAR column. */
    public function varchar(string $name, int $length = 255): self
    {
        $this->columns[$name] = [
            'type'     => 'VARCHAR',
            'length'   => $length,
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a TEXT column. */
    public function text(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'TEXT',
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a LONGTEXT column. */
    public function longText(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'LONGTEXT',
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a DATETIME column. */
    public function datetime(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'DATETIME',
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a DATE column. */
    public function date(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'DATE',
            'nullable' => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a TIMESTAMP column. */
    public function timestamp(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'TIMESTAMP',
            'nullable' => false,
            'default'  => 'CURRENT_TIMESTAMP',
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a BOOLEAN column (TINYINT(1)). */
    public function boolean(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'TINYINT',
            'length'   => 1,
            'unsigned' => true,
            'nullable' => false,
            'default'  => 0,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a DECIMAL column. */
    public function decimal(string $name, int $precision = 10, int $scale = 2): self
    {
        $this->columns[$name] = [
            'type'      => 'DECIMAL',
            'precision' => $precision,
            'scale'     => $scale,
            'nullable'  => false,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Add a JSON column (LONGTEXT for compatibility). */
    public function json(string $name): self
    {
        $this->columns[$name] = [
            'type'     => 'LONGTEXT',
            'nullable' => true,
        ];
        $this->currentColumn  = $name;

        return $this;
    }

    /** Make the current column nullable. */
    public function nullable(): self
    {
        if ($this->currentColumn) {
            $this->columns[$this->currentColumn]['nullable'] = true;
        }

        return $this;
    }

    /** Set a default value for the current column. */
    public function default($value): self
    {
        if ($this->currentColumn) {
            $this->columns[$this->currentColumn]['default'] = $value;
        }

        return $this;
    }

    /** Make the current column unsigned. */
    public function unsigned(): self
    {
        if ($this->currentColumn) {
            $this->columns[$this->currentColumn]['unsigned'] = true;
        }

        return $this;
    }

    /** Set the primary key. */
    public function primaryKey(string $column): self
    {
        $this->primaryKey = $column;

        return $this;
    }

    /** Add an index. */
    public function index(string $name, array $columns): self
    {
        $this->indexes[$name] = $columns;

        return $this;
    }

    /** Add a unique index. */
    public function unique(string $name, array $columns): self
    {
        $this->uniqueIndexes[$name] = $columns;

        return $this;
    }

    /** Add created_at and updated_at timestamp columns. */
    public function timestamps(): self
    {
        $this->columns['created_at'] = [
            'type'     => 'DATETIME',
            'nullable' => false,
            'default'  => 'CURRENT_TIMESTAMP',
        ];

        $this->columns['updated_at'] = [
            'type'     => 'DATETIME',
            'nullable' => true,
        ];

        return $this;
    }

    /** Get the table name (without prefix). */
    public function getName(): string
    {
        return $this->name;
    }

    /** Get the full table name with WordPress prefix. */
    public function getFullName(): string
    {
        global $wpdb;

        return $wpdb->prefix . PrefixConfig::table($this->name);
    }

    /** Build the SQL CREATE TABLE statement. */
    public function toSql(): string
    {
        global $wpdb;

        $charset = $wpdb->charset ?? 'utf8mb4';
        $collate = $wpdb->collate ?? 'utf8mb4_unicode_ci';

        $tableName = $this->getFullName();
        $lines     = [];

        foreach ($this->columns as $name => $definition) {
            $lines[] = $this->buildColumnSql($name, $definition);
        }

        if ($this->primaryKey) {
            $lines[] = "PRIMARY KEY ($this->primaryKey)";
        }

        foreach ($this->indexes as $name => $columns) {
            $columnList = implode(', ', $columns);
            $lines[]    = "INDEX $name ($columnList)";
        }

        foreach ($this->uniqueIndexes as $name => $columns) {
            $columnList = implode(', ', $columns);
            $lines[]    = "UNIQUE KEY $name ($columnList)";
        }

        $columnsSql = implode(",\n  ", $lines);

        return "CREATE TABLE $tableName (\n  $columnsSql\n) DEFAULT CHARACTER SET $charset COLLATE $collate;";
    }

    /** Build SQL for a single column. */
    private function buildColumnSql(string $name, array $definition): string
    {
        $sql = $name . ' ';

        if (isset($definition['precision'], $definition['scale'])) {
            $sql .= "{$definition['type']}({$definition['precision']},{$definition['scale']})";
        } elseif (isset($definition['length'])) {
            $sql .= "{$definition['type']}({$definition['length']})";
        } else {
            $sql .= $definition['type'];
        }

        if (!empty($definition['unsigned'])) {
            $sql .= ' UNSIGNED';
        }

        $sql .= $definition['nullable'] ? ' NULL' : ' NOT NULL';

        if (!empty($definition['auto_increment'])) {
            $sql .= ' AUTO_INCREMENT';
        }

        if (array_key_exists('default', $definition)) {
            $default = $definition['default'];
            if ($default === 'CURRENT_TIMESTAMP') {
                $sql .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif (is_null($default)) {
                $sql .= ' DEFAULT NULL';
            } elseif (is_bool($default)) {
                $sql .= ' DEFAULT ' . ($default ? '1' : '0');
            } elseif (is_numeric($default)) {
                $sql .= ' DEFAULT ' . $default;
            } else {
                $sql .= " DEFAULT '" . esc_sql($default) . "'";
            }
        }

        return $sql;
    }
}