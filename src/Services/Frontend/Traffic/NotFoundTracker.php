<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Utils\Request;
use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NotFoundTracker
 *
 * Tracks 404 errors on frontend requests.
 * Stores total count and a pruned map of paths that triggered 404s.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class NotFoundTracker
{
    /**
     * Transient TTL in seconds (10 days).
     */
    private const TRANSIENT_TTL = DAY_IN_SECONDS * 10;

    /**
     * Maximum number of paths to keep in the map.
     */
    private const MAX_PATHS = 30;

    /**
     * Track 404 if this is a valid 404 request.
     *
     * @return void
     */
    public function maybeTrack404(): void
    {
        if (!PageviewSignal::shouldCollect()) {
            return;
        }
        
        if (!is_404()) {
            return;
        }

        $cache = CacheManager::instance();

        // Increase total 404 count
        $totalKey = CacheKeys::notFoundTotalToday();
        $cache->increment($totalKey, 1, self::TRANSIENT_TTL);

        // Track top paths
        $path = Request::getRequestPath();
        if ($path === '') {
            return;
        }

        $mapKey = CacheKeys::notFoundMapToday();
        $map    = $this->getMap($mapKey);

        $map[$path] = isset($map[$path]) ? ((int)$map[$path] + 1) : 1;

        $map = $this->pruneMap($map);

        $cache->set($mapKey, wp_json_encode($map), self::TRANSIENT_TTL);
    }

    /**
     * Get the path map from cache.
     *
     * @param string $mapKey The cache key.
     * @return array The path → count map.
     */
    private function getMap(string $mapKey): array
    {
        $raw = CacheManager::instance()->get($mapKey);
        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Prune the map to keep only top paths by count.
     *
     * @param array $map The path → count map.
     * @return array The pruned map.
     */
    private function pruneMap(array $map): array
    {
        if (count($map) <= self::MAX_PATHS) {
            return $map;
        }

        arsort($map); // highest first
        return array_slice($map, 0, self::MAX_PATHS, true);
    }
}
