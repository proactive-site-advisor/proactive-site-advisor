<?php

namespace ProactiveSiteAdvisor\Models;

use ProactiveSiteAdvisor\Abstracts\AbstractModel;
use ProactiveSiteAdvisor\Database\QueryRunner;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Represents an alert record stored in the alerts table.
 *
 * @package ProactiveSiteAdvisor\Models
 * @since   1.0.0
 */
class Alert extends AbstractModel
{
    /** {@inheritDoc} */
    protected static string $table = 'alerts';

    /** {@inheritDoc} */
    protected static array $fillable = [
        'alert_date',
        'type',
        'severity',
        'meta_json',
    ];

    /** {@inheritDoc} */
    protected static array $casts = [
        'meta_json' => 'json',
    ];

    /**
     * Create an alert only if a record for the same date and type does not already exist.
     *
     * @return static
     */
    public static function createIfNotExists(
        string  $dateYmd,
        string  $type,
        string  $severity,
        ?string $metaJson = null
    ): ?AbstractModel
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
            'meta_json'  => $metaJson,
        ]);
    }

    /** Find alerts by date (Y-m-d format). */
    public static function findByDate(string $dateYmd, array $options = []): array
    {
        return static::where(['alert_date' => $dateYmd], $options);
    }

    /** Find alerts by type. */
    public static function findByType(string $type, array $options = []): array
    {
        return static::where(['type' => $type], $options);
    }

    /** Find alerts by severity. */
    public static function findBySeverity(string $severity, array $options = []): array
    {
        return static::where(['severity' => $severity], $options);
    }

    /** Delete alert records older than the given date. */
    public static function purgeOlderThan(string $dateYmd): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE alert_date < %s",
            $dateYmd
        );
    }

    /** Delete an alert record by date and type. */
    public static function deleteByDateAndType(string $dateYmd, string $type): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE alert_date = %s AND type = %s",
            $dateYmd,
            $type
        );
    }
}