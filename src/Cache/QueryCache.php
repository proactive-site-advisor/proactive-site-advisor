<?php

namespace ProactiveSiteAdvisor\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Query caching layer for database operations.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since   1.0.0
 */
class QueryCache
{
    /** Default cache expiration (seconds). */
    public const DEFAULT_EXPIRATION = 900;

    /**
     * Cache manager instance.
     *
     * @var CacheManager
     */
    private CacheManager $cache;

    /** Constructor. */
    public function __construct()
    {
        $this->cache = CacheManager::instance();
    }

    /** Cache raw SQL query result. */
    public function remember(string $sql, callable $callback, ?int $expiration = null)
    {
        $key        = $this->generateSqlKey($sql);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->remember(
            $key,
            $callback,
            $expiration,
            CacheGroups::QUERY
        );
    }

    /** Cache prepared SQL query result. */
    public function rememberPrepared(string $sql, array $args, callable $callback, ?int $expiration = null)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $preparedSql = empty($args) ? $sql : $wpdb->prepare($sql, ...$args);

        if ($preparedSql === false) {
            return $callback();
        }

        $key        = $this->generateSqlKey($preparedSql);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->remember(
            $key,
            $callback,
            $expiration,
            CacheGroups::QUERY
        );
    }

    /** Cache WP_Query result. */
    public function rememberPostQuery(array $args, callable $callback, ?int $expiration = null)
    {
        $key        = 'posts_' . $this->hashArgs($args);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->remember(
            $key,
            $callback,
            $expiration,
            CacheGroups::QUERY
        );
    }

    /** Cache term query result. */
    public function rememberTermQuery(array $args, callable $callback, ?int $expiration = null)
    {
        $key        = 'terms_' . $this->hashArgs($args);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->remember(
            $key,
            $callback,
            $expiration,
            CacheGroups::QUERY
        );
    }

    /** Cache user query result. */
    public function rememberUserQuery(array $args, callable $callback, ?int $expiration = null)
    {
        $key        = 'users_' . $this->hashArgs($args);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->remember(
            $key,
            $callback,
            $expiration,
            CacheGroups::QUERY
        );
    }

    /** Cache custom table query result. */
    public function rememberTableQuery(string $table, array $args, callable $callback, ?int $expiration = null)
    {
        $key        = 'table_' . $table . '_' . $this->hashArgs($args);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->remember(
            $key,
            $callback,
            $expiration,
            CacheGroups::QUERY
        );
    }

    /** Invalidate entire query cache group. */
    public function invalidate(): bool
    {
        return $this->cache->flushGroup(CacheGroups::QUERY);
    }

    /** Invalidate post-related queries. */
    public function invalidatePost(): void
    {
        $this->invalidate();
    }

    /** Invalidate term-related queries. */
    public function invalidateTerm(): void
    {
        $this->invalidate();
    }

    /** Invalidate custom table queries. */
    public function invalidateTable(): void
    {
        $this->invalidate();
    }

    /** Register WordPress cache invalidation hooks. */
    public function registerInvalidationHooks(): void
    {
        add_action('save_post', [$this, 'invalidatePost']);
        add_action('delete_post', [$this, 'invalidatePost']);
        add_action('trashed_post', [$this, 'invalidatePost']);

        add_action('created_term', [$this, 'invalidateTerm']);
        add_action('edited_term', [$this, 'invalidateTerm']);
        add_action('delete_term', [$this, 'invalidateTerm']);
    }

    /** Generate stable hash for query arguments. */
    private function hashArgs(array $args): string
    {
        ksort($args);

        return md5(wp_json_encode($args));
    }

    /** Generate SQL cache key. */
    private function generateSqlKey(string $sql): string
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        return 'sql_' . md5($sql);
    }
}