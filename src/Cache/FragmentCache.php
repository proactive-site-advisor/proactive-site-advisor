<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Utils\Kses;
use Throwable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fragment cache for HTML partials.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since   1.0.0
 */
class FragmentCache
{
    /** Default cache expiration (seconds). */
    public const DEFAULT_EXPIRATION = 1800;

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

    /**
     * Render a cached fragment.
     *
     * @throws Throwable
     */
    public function render(string $key, callable $callback, ?int $expiration = null, array $vary = []): void
    {
        $fullKey = $this->buildKey($key, $vary);

        $cached = $this->cache->get($fullKey, null, CacheGroups::FRAGMENT);

        if ($cached !== null) {
            echo wp_kses($cached, Kses::allowedHtml());

            return;
        }

        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        ob_start();

        try {
            $callback();
            $content = (string)ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $this->cache->set(
            $fullKey,
            $content,
            $expiration,
            CacheGroups::FRAGMENT
        );

        echo wp_kses($content, Kses::allowedHtml());
    }

    /** Get cached fragment without rendering. */
    public function get(string $key, array $vary = []): ?string
    {
        $fullKey = $this->buildKey($key, $vary);

        return $this->cache->get($fullKey, null, CacheGroups::FRAGMENT);
    }

    /** Store fragment content. */
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

    /** Delete cached fragment. */
    public function delete(string $key, array $vary = []): bool
    {
        $fullKey = $this->buildKey($key, $vary);

        return $this->cache->delete($fullKey, CacheGroups::FRAGMENT);
    }

    /** Check if fragment exists. */
    public function has(string $key, array $vary = []): bool
    {
        $fullKey = $this->buildKey($key, $vary);

        return $this->cache->has($fullKey, CacheGroups::FRAGMENT);
    }

    /** Build fragment cache key with variations. */
    private function buildKey(string $key, array $vary = []): string
    {
        if (empty($vary)) {
            return $key;
        }

        ksort($vary);

        return CacheManager::makeKey($key, $vary);
    }

    /** Vary by current user ID. */
    public function varyByUser(): array
    {
        return ['user_id' => get_current_user_id()];
    }

    /** Vary by user roles. */
    public function varyByRole(): array
    {
        $user  = wp_get_current_user();
        $roles = $user->roles ?? ['guest'];

        sort($roles);

        return ['roles' => implode(',', $roles)];
    }

    /** Vary by locale. */
    public function varyByLocale(): array
    {
        return ['locale' => get_locale()];
    }

    /** Vary by request URL. */
    public function varyByUrl(): array
    {
        $uri = '';

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
        }

        return ['url' => $uri];
    }

    /** Vary by device type. */
    public function varyByDevice(): array
    {
        $device = (function_exists('wp_is_mobile') && wp_is_mobile())
            ? 'mobile'
            : 'desktop';

        return ['device' => $device];
    }

    /** Combine multiple vary conditions. */
    public function combineVary(array ...$conditions): array
    {
        return $conditions ? array_merge(...$conditions) : [];
    }
}