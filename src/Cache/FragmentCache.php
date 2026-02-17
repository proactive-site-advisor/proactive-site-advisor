<?php

namespace ProactiveSiteAdvisor\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class FragmentCache
 *
 * Provides fragment (HTML partial) caching support
 * to improve template rendering performance.
 *
 * Uses CacheManager internally and supports
 * vary-based key generation.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since 1.0.0
 */
class FragmentCache
{
    /**
     * Default expiration time in seconds (30 minutes).
     */
    public const DEFAULT_EXPIRATION = 1800;

    /**
     * Cache manager instance.
     *
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * Currently active fragment key.
     *
     * @var string|null
     */
    private ?string $activeFragment = null;

    /**
     * Indicates whether output buffering has started.
     *
     * @var bool
     */
    private bool $bufferStarted = false;

    /**
     * FragmentCache constructor.
     */
    public function __construct()
    {
        $this->cache = CacheManager::getInstance();
    }

    /**
     * Render a fragment using a callback.
     *
     * @param string $key Fragment key.
     * @param callable $callback Rendering callback.
     * @param int|null $expiration Optional expiration.
     * @param array $vary Context variations.
     *
     * @return void
     * @throws \Throwable
     */
    public function render(string $key, callable $callback, ?int $expiration = null, array $vary = []): void
    {
        $fullKey = $this->buildKey($key, $vary);

        $cached = $this->cache->get($fullKey, null, CacheGroups::FRAGMENT);

        if ($cached !== null) {
            echo wp_kses_post($cached);
            return;
        }

        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        ob_start();

        try {
            $callback();
            $content = (string)ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $this->cache->set(
            $fullKey,
            $content,
            $expiration,
            CacheGroups::FRAGMENT
        );

        echo wp_kses_post($content);
    }

    /**
     * Retrieve a fragment without outputting it.
     *
     * @param string $key Fragment key.
     * @param array $vary Context variations.
     *
     * @return string|null
     */
    public function get(string $key, array $vary = []): ?string
    {
        $fullKey = $this->buildKey($key, $vary);

        return $this->cache->get($fullKey, null, CacheGroups::FRAGMENT);
    }

    /**
     * Store a fragment directly.
     *
     * @param string $key Fragment key.
     * @param string $content HTML content.
     * @param int|null $expiration Optional expiration.
     * @param array $vary Context variations.
     *
     * @return bool
     */
    public function set(string $key, string $content, ?int $expiration = null, array $vary = []): bool
    {
        $fullKey    = $this->buildKey($key, $vary);
        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        return $this->cache->set(
            $fullKey,
            $content,
            $expiration,
            CacheGroups::FRAGMENT
        );
    }

    /**
     * Delete a fragment.
     *
     * @param string $key Fragment key.
     * @param array $vary Context variations.
     *
     * @return bool
     */
    public function delete(string $key, array $vary = []): bool
    {
        $fullKey = $this->buildKey($key, $vary);

        return $this->cache->delete($fullKey, CacheGroups::FRAGMENT);
    }

    /**
     * Determine if a fragment exists.
     *
     * @param string $key Fragment key.
     * @param array $vary Context variations.
     *
     * @return bool
     */
    public function has(string $key, array $vary = []): bool
    {
        $fullKey = $this->buildKey($key, $vary);

        return $this->cache->has($fullKey, CacheGroups::FRAGMENT);
    }

    /**
     * Build a cache key including variation parameters.
     *
     * @param string $key Base key.
     * @param array $vary Context variations.
     *
     * @return string
     */
    private function buildKey(string $key, array $vary = []): string
    {
        if (empty($vary)) {
            return $key;
        }

        ksort($vary);

        return CacheManager::makeKey($key, $vary);
    }

    /**
     * Vary fragment by current user ID.
     *
     * @return array
     */
    public function varyByUser(): array
    {
        return ['user_id' => get_current_user_id()];
    }

    /**
     * Vary fragment by user roles.
     *
     * @return array
     */
    public function varyByRole(): array
    {
        $user  = wp_get_current_user();
        $roles = $user->roles ?? ['guest'];

        sort($roles);

        return ['roles' => implode(',', $roles)];
    }

    /**
     * Vary fragment by locale.
     *
     * @return array
     */
    public function varyByLocale(): array
    {
        return ['locale' => get_locale()];
    }

    /**
     * Vary fragment by request URL.
     *
     * @return array
     */
    public function varyByUrl(): array
    {
        $uri = '';

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
        }

        return ['url' => $uri];
    }

    /**
     * Vary fragment by device type.
     *
     * @return array
     */
    public function varyByDevice(): array
    {
        $device = (function_exists('wp_is_mobile') && wp_is_mobile())
            ? 'mobile'
            : 'desktop';

        return ['device' => $device];
    }

    /**
     * Combine multiple vary conditions.
     *
     * @param array ...$conditions Variation arrays.
     *
     * @return array
     */
    public function combineVary(array ...$conditions): array
    {
        return array_merge(...$conditions);
    }
}
