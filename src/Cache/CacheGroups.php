<?php

namespace ProactiveSiteAdvisor\Cache;

use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines all cache group identifiers used by the plugin.
 *
 * Centralized location for cache group constants to avoid
 * hardcoded strings across the codebase.
 *
 * @package ProactiveSiteAdvisor\Cache
 * @version 1.0.0
 */
final class CacheGroups
{
    /** Default cache group */
    public const DEFAULT = PrefixConfig::PREFIX . '_cache';

    /** Statistics-related cache group */
    public const STATS = PrefixConfig::PREFIX . '_stats';

    /** Database/query cache group */
    public const QUERY = PrefixConfig::PREFIX . '_query';

    /** Fragment (HTML/output) cache group */
    public const FRAGMENT = PrefixConfig::PREFIX . '_fragment';

    /**
     * Prevent instantiation.
     */
    private function __construct()
    {
    }
}
