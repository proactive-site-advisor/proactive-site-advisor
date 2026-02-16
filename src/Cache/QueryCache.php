<?php

namespace ProactiveSiteAdvisor\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class QueryCache
 *
 * Specialized caching layer for database queries.
 * Provides stable key generation and grouped invalidation.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since 1.0.0
 */
class QueryCache
{
    /**
     * Default cache expiration (15 minutes).
     */
    public const DEFAULT_EXPIRATION = 900;

    /**
     * Cache manager instance.
     *
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cache = CacheManager::getInstance();
    }

    /**
     * Cache raw SQL query result.
     *
     * @param string $sql
     * @param callable $callback
     * @param int|null $expiration
     * @return mixed
     */
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

    /**
     * Cache prepared SQL query result.
     *
     * @param string $sql
     * @param array $args
     * @param callable $callback
     * @param int|null $expiration
     * @return mixed
     */
    public function rememberPrepared(string $sql, array $args, callable $callback, ?int $expiration = null)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $preparedSql = $wpdb->prepare($sql, ...$args);

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

    /**
     * Cache WP_Query result.
     *
     * @param array $args
     * @param callable $callback
     * @param int|null $expiration
     * @return mixed
     */
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

    /**
     * Cache term query result.
     */
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

    /**
     * Cache user query result.
     */
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

    /**
     * Cache custom table query result.
     */
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

    /**
     * Invalidate entire query cache group.
     *
     * @return bool
     */
    public function invalidate(): bool
    {
        return $this->cache->flushGroup(CacheGroups::QUERY);
    }

    /**
     * Invalidate all post-related queries.
     *
     * @param int $postId
     * @return void
     */
    public function invalidatePost(int $postId): void
    {
        $this->invalidate();
    }

    /**
     * Invalidate all term-related queries.
     *
     * @param int $termId
     * @return void
     */
    public function invalidateTerm(int $termId): void
    {
        $this->invalidate();
    }

    /**
     * Invalidate table-related cache.
     *
     * @param string $table
     * @return void
     */
    public function invalidateTable(string $table): void
    {
        $this->invalidate();
    }

    /**
     * Register WordPress invalidation hooks.
     *
     * @return void
     */
    public function registerInvalidationHooks(): void
    {
        add_action('save_post', [$this, 'invalidatePost']);
        add_action('delete_post', [$this, 'invalidatePost']);
        add_action('trashed_post', [$this, 'invalidatePost']);

        add_action('created_term', [$this, 'invalidateTerm']);
        add_action('edited_term', [$this, 'invalidateTerm']);
        add_action('delete_term', [$this, 'invalidateTerm']);
    }

    /**
     * Generate stable hash from query arguments.
     *
     * @param array $args
     * @return string
     */
    private function hashArgs(array $args): string
    {
        ksort($args);

        return md5(wp_json_encode($args));
    }

    /**
     * Generate SQL cache key.
     *
     * @param string $sql
     * @return string
     */
    private function generateSqlKey(string $sql): string
    {
        return 'sql_' . md5($sql);
    }
}
