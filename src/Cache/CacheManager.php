<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin caching using:
 * - Local runtime cache
 * - WordPress object cache
 * - WordPress transients (fallback)
 *
 * @package ProactiveSiteAdvisor\Cache
 * @version 1.0.0
 */
class CacheManager extends AbstractSingleton
{
    private const VERSION = 'v1';

    /** @var string Current cache group */
    private string $group;

    /** @var int Default cache expiration in seconds */
    private int $defaultExpiration = HOUR_IN_SECONDS;

    /** @var bool Whether persistent object cache is available */
    private bool $objectCacheAvailable;

    /** @var array Cache statistics */
    private array $stats = [
        'hits'   => 0,
        'misses' => 0,
        'writes' => 0,
    ];

    /**
     * Runtime in‑memory cache separated by group.
     *
     * @var array
     */
    private array $localCache = [];

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->objectCacheAvailable = (bool)wp_using_ext_object_cache();
        $this->group                = CacheGroups::DEFAULT;
    }

    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        add_action('switch_theme', [$this, 'flush']);
        add_action('upgrader_process_complete', [$this, 'flush']);
    }

    /**
     * Set active cache group.
     *
     * @param string $group
     * @return self
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get cached value.
     *
     * @param string $key
     * @param mixed|null $default
     * @param string|null $group
     * @return mixed
     */
    public function get(string $key, $default = null, ?string $group = null)
    {
        $group      = $group ?? $this->group;
        $storageKey = $this->buildKey($key, $group);

        if (isset($this->localCache[$group][$storageKey])) {
            $this->stats['hits']++;
            return $this->localCache[$group][$storageKey];
        }

        if ($this->objectCacheAvailable) {
            $cachedValue = wp_cache_get($storageKey, $group, false, $found);

            if ($found) {
                $this->stats['hits']++;
                return $this->localCache[$group][$storageKey] = $cachedValue;
            }
        }

        $transientValue = get_transient($storageKey);

        if ($transientValue !== false) {
            $this->stats['hits']++;
            return $this->localCache[$group][$storageKey] = $transientValue;
        }

        $this->stats['misses']++;

        return $default;
    }

    /**
     * Store value in cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $expiration
     * @param string|null $group
     * @return bool
     */
    public function set(string $key, $value, ?int $expiration = null, ?string $group = null): bool
    {
        $group      = $group ?? $this->group;
        $expiration = $expiration ?? $this->defaultExpiration;
        $storageKey = $this->buildKey($key, $group);

        $this->localCache[$group][$storageKey] = $value;

        if ($this->objectCacheAvailable) {
            wp_cache_set($storageKey, $value, $group, $expiration);
        }

        $result = set_transient($storageKey, $value, $expiration);

        if ($result) {
            $this->stats['writes']++;
        }

        return $result;
    }

    /**
     * Delete cached value.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function delete(string $key, ?string $group = null): bool
    {
        $group      = $group ?? $this->group;
        $storageKey = $this->buildKey($key, $group);

        unset($this->localCache[$group][$storageKey]);

        if ($this->objectCacheAvailable) {
            wp_cache_delete($storageKey, $group);
        }

        return delete_transient($storageKey);
    }

    /**
     * Check if cache key exists.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function has(string $key, ?string $group = null): bool
    {
        $group      = $group ?? $this->group;
        $storageKey = $this->buildKey($key, $group);

        if (isset($this->localCache[$group][$storageKey])) {
            return true;
        }

        if ($this->objectCacheAvailable) {
            wp_cache_get($storageKey, $group, false, $found);
            if ($found) {
                return true;
            }
        }

        return get_transient($storageKey) !== false;
    }

    /**
     * Cache helper similar to Laravel remember().
     *
     * @param string $key
     * @param callable $callback
     * @param int|null $expiration
     * @param string|null $group
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $expiration = null, ?string $group = null)
    {
        $value = $this->get($key, null, $group);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $expiration, $group);

        return $value;
    }

    /**
     * Increment numeric cache value.
     */
    public function increment(string $key, int $amount = 1, ?int $expiration = null, ?string $group = null): int
    {
        $value = $this->get($key, 0, $group);

        if (!is_numeric($value)) {
            $value = 0;
        }

        $newValue = (int)$value + $amount;

        $this->set($key, $newValue, $expiration, $group);

        return $newValue;
    }

    /**
     * Decrement numeric cache value.
     */
    public function decrement(string $key, int $amount = 1, ?int $expiration = null, ?string $group = null): int
    {
        return $this->increment($key, -$amount, $expiration, $group);
    }

    /**
     * Build internal cache key.
     *
     * Format:
     * prefix:version:blog_id:group:key
     */
    private function buildKey(string $key, string $group): string
    {
        $blogId = is_multisite() ? get_current_blog_id() : 1;

        return PrefixConfig::PREFIX
            . ':'
            . self::VERSION
            . ':'
            . $blogId
            . ':'
            . $group
            . ':'
            . $key;
    }

    /**
     * Generate deterministic cache key from arguments.
     */
    public static function makeKey(string $prefix, ...$args): string
    {
        if (!empty($args)) {
            sort($args);
        }

        $hash = md5(wp_json_encode($args));

        return $prefix . '_' . $hash;
    }

    /**
     * Flush all plugin cache.
     */
    public function flush(): bool
    {
        global $wpdb;

        $this->localCache = [];

        if ($this->objectCacheAvailable && function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group($this->group);
        }

        $like = PrefixConfig::PREFIX . ':%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                 OR option_name LIKE %s",
                '_transient_' . $like,
                '_transient_timeout_' . $like
            )
        );

        Logger::info('Plugin cache flushed');

        do_action('proactive_site_advisor_cache_flushed');

        return true;
    }

    /**
     * Flush cache for a specific group.
     *
     * @param string $group
     * @return bool
     */
    public function flushGroup(string $group): bool
    {
        global $wpdb;

        unset($this->localCache[$group]);

        if ($this->objectCacheAvailable && function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group($group);
        }

        $blogId = is_multisite() ? get_current_blog_id() : 1;

        $like = PrefixConfig::PREFIX
            . ':'
            . self::VERSION
            . ':'
            . $blogId
            . ':'
            . $group
            . ':%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                 OR option_name LIKE %s",
                '_transient_' . $like,
                '_transient_timeout_' . $like
            )
        );

        Logger::info("Cache group flushed: {$group}");

        do_action('proactive_site_advisor_cache_group_flushed', $group);

        return true;
    }

    /**
     * Get cache statistics.
     *
     * @return array
     */
    public function getStats(): array
    {
        return array_merge($this->stats, [
            'object_cache' => $this->objectCacheAvailable,
            'local_items'  => array_sum(array_map('count', $this->localCache)),
        ]);
    }
}