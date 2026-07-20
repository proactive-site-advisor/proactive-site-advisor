<?php

namespace ProactiveSiteAdvisor\Models;

use ProactiveSiteAdvisor\Abstracts\AbstractModel;
use ProactiveSiteAdvisor\Database\QueryRunner;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Atomic rate counter model for burst detection.
 *
 * @package ProactiveSiteAdvisor\Models
 * @since   1.1.0
 */
class RateCounter extends AbstractModel
{
    /** {@inheritDoc} */
    protected static string $table = 'rate_counters';

    /** {@inheritDoc} */
    protected static array $fillable = [
        'hash',
        'count',
        'expires_at',
    ];

    /** Atomically increment the counter for a fingerprint and return the new value. */
    public static function incrementAndGet(string $hash, int $windowSeconds): int
    {
        global $wpdb;
        $table = static::getTableName();

        $now       = DateTimeUtils::now();
        $newExpiry = gmdate(DateTimeUtils::FORMAT_DATETIME, DateTimeUtils::timestamp() + $windowSeconds);

        QueryRunner::preparedQuery(
            "INSERT INTO $table (hash, count, expires_at)
             VALUES (%s, 1, %s)
             ON DUPLICATE KEY UPDATE
                 count = IF(expires_at > %s, count + 1, 1),
                 expires_at = IF(expires_at > %s, expires_at, %s)",
            $hash,
            $newExpiry,
            $now,
            $now,
            $newExpiry
        );

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT count FROM $table WHERE hash = %s",
            $hash
        ));

        if ($count === null) {
            return 1;
        }

        return (int)$count;
    }

    /** Delete all expired rate counter rows. */
    public static function purgeExpired(): void
    {
        $table = static::getTableName();
        $now   = DateTimeUtils::now();

        QueryRunner::preparedQuery(
            "DELETE FROM $table WHERE expires_at < %s",
            $now
        );
    }
}