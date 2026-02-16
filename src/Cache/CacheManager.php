<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Central cache manager for the plugin.
 *
 * Provides a unified abstraction layer over:
 * - WordPress Object Cache (if available)
 * - WordPress Transients (fallback)
 * - In-memory runtime cache (per request)
 *
 * Features:
 * - Versioned cache keys
 * - Multisite-safe key generation
 * - Group-based separation
 * - Automatic fallback strategy
 *
 * @package ProactiveSiteAdvisor\Cache
 * @version 1.0.0
 */
class CacheManager extends AbstractSingleton
{
    /**
     * Cache version for global invalidation.
     */
    private const VERSION = 'v1';

    /**
     * Default cache group.
     */
    private string $group;

    /**
     * Default expiration time in seconds.
     */
    private int $defaultExpiration = HOUR_IN_SECONDS;

    /**
     * Whether external object cache is available.
     */
    private bool $objectCacheAvailable;

    /**
     * Runtime statistics.
     */
    private array $stats = [
        'hits'   => 0,
        'misses' => 0,
        'writes' => 0,
    ];

    /**
     * Per-request in-memory cache.
     */
    private array $localCache = [];

    protected function __construct()
    {
        parent::__construct();

        $this->objectCacheAvailable = (bool)wp_using_ext_object_cache();
        $this->group                = CacheGroups::DEFAULT;
    }

    /**
     * Register automatic flush hooks.
     */
    public function register(): void
    {
        add_action('switch_theme', [$this, 'flush']);
        add_action('upgrader_process_complete', [$this, 'flush']);
    }

    /**
     * Set active cache group.
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /* =====================================================
       Core Storage Methods
    ===================================================== */

    /**
     * Retrieve a cached value.
     */
    public function get(string $key, $default = null, ?string $group = null)
    {
        $group      = $group ?? $this->group;
        $storageKey = $this->buildKey($key);

        // Runtime cache
        if (isset($this->localCache[$storageKey])) {
            $this->stats['hits']++;
            return $this->localCache[$storageKey];
        }

        // Object cache
        if ($this->objectCacheAvailable) {
            $cachedValue = wp_cache_get($storageKey, $group, false, $found);

            if ($found) {
                $this->stats['hits']++;
                return $this->localCache[$storageKey] = $cachedValue;
            }
        }

        // Transient fallback
        $transientValue = get_transient($storageKey);

        if ($transientValue !== false) {
            $this->stats['hits']++;
            return $this->localCache[$storageKey] = $transientValue;
        }

        $this->stats['misses']++;

        return $default;
    }

    /**
     * Store a value in cache.
     */
    public function set(string $key, $value, ?int $expiration = null, ?string $group = null): bool
    {
        $group      = $group ?? $this->group;
        $expiration = $expiration ?? $this->defaultExpiration;
        $storageKey = $this->buildKey($key);

        $this->localCache[$storageKey] = $value;

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
     * Delete a cached value.
     */
    public function delete(string $key, ?string $group = null): bool
    {
        $group      = $group ?? $this->group;
        $storageKey = $this->buildKey($key);

        unset($this->localCache[$storageKey]);

        if ($this->objectCacheAvailable) {
            wp_cache_delete($storageKey, $group);
        }

        return delete_transient($storageKey);
    }

    /**
     * Check if a cache key exists.
     */
    public function has(string $key, ?string $group = null): bool
    {
        $group      = $group ?? $this->group;
        $storageKey = $this->buildKey($key);

        if (isset($this->localCache[$storageKey])) {
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
     * Increment a cached numeric value.
     *
     * If the key does not exist, it will be initialized with 0
     * before applying the increment.
     *
     * Note: This method resets the expiration time if provided.
     * If no expiration is given, the default cache expiration is used.
     *
     * @param string $key Cache key.
     * @param int $amount Amount to increment (default: 1).
     * @param int|null $expiration Optional expiration time in seconds.
     * @param string|null $group Optional cache group.
     *
     * @return int The updated numeric value.
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
     * Decrement a cached numeric value.
     *
     * This is a wrapper around increment() using a negative amount.
     *
     * @param string $key Cache key.
     * @param int $amount Amount to decrement (default: 1).
     * @param int|null $expiration Optional expiration time in seconds.
     * @param string|null $group Optional cache group.
     *
     * @return int The updated numeric value.
     */
    public function decrement(string $key, int $amount = 1, ?int $expiration = null, ?string $group = null): int
    {
        return $this->increment($key, -$amount, $expiration, $group);
    }

    /* =====================================================
       Helpers
    ===================================================== */

    /**
     * Build a fully qualified cache key.
     *
     * Structure:
     * prefix:version:blog_id:key
     */
    private function buildKey(string $key): string
    {
        $blogId = is_multisite() ? get_current_blog_id() : 1;

        return PrefixConfig::PREFIX
            . ':'
            . self::VERSION
            . ':'
            . $blogId
            . ':'
            . $key;
    }

    /**
     * Generate a stable hashed cache key segment.
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
     * Flush all plugin-related cache entries.
     */
    public function flush(): bool
    {
        global $wpdb;

        $this->localCache = [];

        if ($this->objectCacheAvailable && function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group($this->group);
        }

        $like = PrefixConfig::PREFIX . ':%';

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
     * Return runtime cache statistics.
     */
    public function getStats(): array
    {
        return array_merge($this->stats, [
            'object_cache' => $this->objectCacheAvailable,
            'local_items'  => count($this->localCache),
        ]);
    }
}
