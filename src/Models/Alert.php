<?php

namespace ProactiveSiteAdvisor\Models;

use ProactiveSiteAdvisor\Abstracts\AbstractModel;
use ProactiveSiteAdvisor\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Alert
 *
 * Represents an alert record stored in the alerts table.
 *
 * @package ProactiveSiteAdvisor\Models
 * @version 1.0.0
 */
class Alert extends AbstractModel
{
    /**
     * Table name (without prefix).
     *
     * @var string
     */
    protected static string $table = 'alerts';

    /**
     * Allowed fields for mass assignment.
     *
     * @var array<int, string>
     */
    protected static array $fillable = [
        'alert_date',
        'type',
        'severity',
        'title',
        'meta_json',
    ];

    /**
     * Attribute type casting map.
     *
     * @var array<string, string>
     */
    protected static array $casts = [
        'meta_json' => 'json',
    ];

    /**
     * Create an alert only if a record for the same date and type does not already exist.
     *
     * @param string $dateYmd
     * @param string $type
     * @param string $severity
     * @param string $title
     * @param string|null $metaJson
     *
     * @return static|null
     */
    public static function createIfNotExists(
        string  $dateYmd,
        string  $type,
        string  $severity,
        string  $title,
        ?string $metaJson = null
    ): ?self
    {
        $existing = static::first([
            'alert_date' => $dateYmd,
            'type'       => $type,
        ]);

        if ($existing !== null) {
            return null;
        }

        return static::create([
            'alert_date' => $dateYmd,
            'type'       => $type,
            'severity'   => $severity,
            'title'      => $title,
            'meta_json'  => $metaJson,
        ]);
    }

    /**
     * Find alerts by date (Y-m-d format).
     *
     * @param string $dateYmd
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    public static function findByDate(string $dateYmd, array $options = []): array
    {
        return static::where(['alert_date' => $dateYmd], $options);
    }

    /**
     * Find alerts by type.
     *
     * @param string $type
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    public static function findByType(string $type, array $options = []): array
    {
        return static::where(['type' => $type], $options);
    }

    /**
     * Find alerts by severity.
     *
     * @param string $severity
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    public static function findBySeverity(string $severity, array $options = []): array
    {
        return static::where(['severity' => $severity], $options);
    }

    /**
     * Delete alert records older than the given date.
     *
     * @param string $dateYmd
     *
     * @return void
     */
    public static function purgeOlderThan(string $dateYmd): void
    {
        $table = static::getTableName();

        DatabaseManager::preparedQuery(
            "DELETE FROM {$table} WHERE alert_date < %s",
            $dateYmd
        );
    }

    /**
     * Delete an alert record by date and type.
     *
     * @param string $dateYmd
     * @param string $type
     *
     * @return void
     */
    public static function deleteByDateAndType(string $dateYmd, string $type): void
    {
        $table = static::getTableName();

        DatabaseManager::preparedQuery(
            "DELETE FROM {$table} WHERE alert_date = %s AND type = %s",
            $dateYmd,
            $type
        );
    }
}