<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache group identifiers used by the plugin.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @since   1.0.0
 */
class CacheGroups
{
    /** Prevent instantiation. */
    private function __construct()
    {
    }

    /** Default cache group */
    public const DEFAULT = PrefixConfig::PREFIX . '_cache';

    /** Statistics cache group */
    public const STATS = PrefixConfig::PREFIX . '_stats';

    /** Query/DB cache group */
    public const QUERY = PrefixConfig::PREFIX . '_query';

    /** Fragment (HTML/output) cache group */
    public const FRAGMENT = PrefixConfig::PREFIX . '_fragment';

    /** Return all cache groups. */
    public static function all(): array
    {
        return [
            self::DEFAULT,
            self::STATS,
            self::QUERY,
            self::FRAGMENT,
        ];
    }
}