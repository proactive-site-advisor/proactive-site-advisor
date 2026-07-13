<?php

namespace ProactiveSiteAdvisor\Abstracts;

use ProactiveSiteAdvisor\Database\DataStore;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Database\SchemaRegistry;
use Exception;
use RuntimeException;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base model class providing CRUD operations and data access.
 *
 * @package ProactiveSiteAdvisor\Abstracts
 * @since   1.0.0
 */
abstract class AbstractModel
{
    /** Table name (without prefix). */
    protected static string $table = '';

    /** Primary key column name. */
    protected static string $primaryKey = 'id';

    /** Fillable fields (allowed for mass assignment). */
    protected static array $fillable = [];

    /** Hidden fields (excluded from toArray/toJson). */
    protected static array $hidden = [];

    /** Attribute type casts. */
    protected static array $casts = [];

    /** Model attributes (data). */
    protected array $attributes = [];

    /** Original attributes (for dirty checking). */
    protected array $original = [];

    /** Whether the model exists in the database. */
    protected bool $exists = false;

    /** Constructor. */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /** Create a new model instance. */
    public static function make(array $attributes = []): self
    {
        return new static($attributes);
    }

    /** Create a new record in the database. */
    public static function create(array $attributes): ?self
    {
        $model = new static($attributes);

        if ($model->save()) {
            return $model;
        }

        return null;
    }

    /** Find a record by primary key. */
    public static function find(int $id): ?self
    {
        $row = DataStore::getRow(static::$table, $id, static::$primaryKey);

        if ($row === null) {
            return null;
        }

        return static::hydrate($row);
    }

    /**
     * Find a record by primary key or throw exception.
     *
     * @throws Exception If not found.
     */
    public static function findOrFail(int $id): self
    {
        $model = static::find($id);

        if ($model === null) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new RuntimeException(sprintf('Model not found with ID: %d', $id));
        }

        return $model;
    }

    /** Find a record by a specific column value. */
    public static function findBy(string $column, $value): ?self
    {
        $rows = DataStore::getRows(static::$table, [
            'where' => [$column => $value],
            'limit' => 1,
        ]);

        if (empty($rows)) {
            return null;
        }

        return static::hydrate($rows[0]);
    }

    /** Get all records. */
    public static function all(array $options = []): array
    {
        $rows = DataStore::getRows(static::$table, $options);

        return array_map(static function ($row) {
            return static::hydrate($row);
        }, $rows);
    }

    /** Get records with conditions. */
    public static function where(array $where, array $options = []): array
    {
        $options['where'] = $where;

        return static::all($options);
    }

    /** Get the first record matching conditions. */
    public static function first(array $where = []): ?self
    {
        $results = static::where($where, ['limit' => 1]);

        return $results[0] ?? null;
    }

    /** Count records. */
    public static function count(array $where = []): int
    {
        return DataStore::getRowCount(static::$table, $where);
    }

    /** Check if a record exists. */
    public static function exists(array $where): bool
    {
        return static::count($where) > 0;
    }

    /** Hydrate a model from a database row. */
    protected static function hydrate(object $row): self
    {
        $model             = new static();
        $model->attributes = (array)$row;
        $model->original   = $model->attributes;
        $model->exists     = true;

        return $model;
    }

    /** Fill model with attributes. */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /** Check if an attribute is fillable. */
    protected function isFillable(string $key): bool
    {
        if (empty(static::$fillable)) {
            return true;
        }

        return in_array($key, static::$fillable, true);
    }

    /** Set an attribute value. */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /** Get an attribute value. */
    public function getAttribute(string $key, $default = null)
    {
        $value = $this->attributes[$key] ?? $default;

        return $this->castAttribute($key, $value);
    }

    /** Cast an attribute to its defined type. */
    protected function castAttribute(string $key, $value)
    {
        if ($value === null) {
            return null;
        }

        $castType = static::$casts[$key] ?? null;

        if ($castType === null) {
            return $value;
        }

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int)$value;

            case 'float':
            case 'double':
            case 'decimal':
                return (float)$value;

            case 'bool':
            case 'boolean':
                return (bool)$value;

            case 'string':
                return (string)$value;

            case 'array':
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);

            default:
                return $value;
        }
    }

    /** Get the primary key value. */
    public function getId(): ?int
    {
        $id = $this->getAttribute(static::$primaryKey);

        return $id !== null ? (int)$id : null;
    }

    /** Get the full table name with prefix. */
    public static function getTableName(): string
    {
        $schema = SchemaRegistry::getTable(static::$table);
        return $schema ? $schema->getFullName() : '';
    }

    /** Check if the model has been modified. */
    public function isDirty(): bool
    {
        return $this->attributes !== $this->original;
    }

    /** Get the dirty (changed) attributes. */
    public function getDirty(): array
    {
        return array_filter($this->attributes, function ($value, $key) {
            return !array_key_exists($key, $this->original) || $this->original[$key] !== $value;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /** Save the model to the database. */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    /** Perform an insert operation. */
    protected function performInsert(): bool
    {
        $attributes = $this->attributes;

        if (!isset($attributes['created_at'])) {
            $attributes['created_at'] = DateTimeUtils::now();
        }

        $id = DataStore::insert(static::$table, $attributes);

        if ($id === false) {
            return false;
        }

        $this->setAttribute(static::$primaryKey, $id);
        $this->exists = true;
        $this->syncOriginal();

        return true;
    }

    /** Perform an update operation. */
    protected function performUpdate(): bool
    {
        if (!$this->isDirty()) {
            return true;
        }

        $dirty = $this->getDirty();

        $dirty['updated_at'] = DateTimeUtils::now();

        $result = DataStore::update(
            static::$table,
            $dirty,
            [static::$primaryKey => $this->getId()]
        );

        if ($result === false) {
            return false;
        }

        $this->setAttribute('updated_at', $dirty['updated_at']);
        $this->syncOriginal();

        return true;
    }

    /** Sync original attributes with current. */
    protected function syncOriginal(): self
    {
        $this->original = $this->attributes;

        return $this;
    }

    /** Delete the model from the database. */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $result = DataStore::delete(
            static::$table,
            [static::$primaryKey => $this->getId()]
        );

        if ($result === false) {
            return false;
        }

        $this->exists = false;

        return true;
    }

    /** Refresh the model from the database. */
    public function refresh(): self
    {
        if (!$this->exists) {
            return $this;
        }

        $fresh = static::find($this->getId());

        if ($fresh !== null) {
            $this->attributes = $fresh->attributes;
            $this->syncOriginal();
        }

        return $this;
    }

    /** Get all attributes as an array. */
    public function toArray(): array
    {
        $attributes = $this->attributes;

        foreach (static::$hidden as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    /** Get all attributes as JSON. */
    public function toJson(int $options = 0): string
    {
        return wp_json_encode($this->toArray(), $options);
    }

    /** Magic getter for attributes. */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /** Magic setter for attributes. */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /** Magic isset check for attributes. */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /** Convert model to string (JSON). */
    public function __toString(): string
    {
        return $this->toJson();
    }
}