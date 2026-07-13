<?php

namespace ProactiveSiteAdvisor\Utils;

use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility class for menu-related operations.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class MenuUtils
{
    /** Get the prefixed menu slug. */
    public static function getSlug(string $id): string
    {
        $base = PrefixConfig::SLUG;
        return strpos($id, $base) === 0 ? $id : $base . '-' . $id;
    }

    /** Get the admin URL for a menu page. */
    public static function getUrl(string $id, array $args = []): string
    {
        $baseUrl = admin_url('admin.php?page=' . self::getSlug($id));

        if (!empty($args)) {
            $baseUrl = add_query_arg($args, $baseUrl);
        }

        return $baseUrl;
    }
}