<?php

namespace ProactiveSiteAdvisor\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @package ProactiveSiteAdvisor\Config
 * @version 1.0.0
 */
class PluginOptions
{
    /**
     * Main option key stored in wp_options.
     */
    public const OPTION_NAME = PrefixConfig::PREFIX;

    /**
     * Prefix for standalone meta options.
     */
    public const META_PREFIX = PrefixConfig::PREFIX . '_';
}