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
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are trusted internal identifiers.
 * phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Database identifiers are generated internally.
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

    /** Delete alert records older than the given date. */
    public static function purgeOlderThan(string $dateYmd): void
    {
        $table = static::getTableName();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE alert_date < %s",
            $dateYmd
        );
    }
}