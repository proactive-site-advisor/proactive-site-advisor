<?php
/**
 * Menu Utilities
 *
 * Helper functions for menu-related operations like slug prefixing and URL generation.
 *
 * @package ProactiveSiteAdvisor\Utils
 */

namespace ProactiveSiteAdvisor\Utils;

use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MenuUtils
 *
 * Utility class for menu-related operations.
 */
class MenuUtils
{
    /**
     * Get the prefixed menu slug.
     *
     * @param string $id Menu ID (without prefix)
     * @return string Prefixed menu slug
     */
    public static function getSlug(string $id): string
    {
        $base = PrefixConfig::SLUG;
        return strpos($id, $base) === 0 ? $id : $base . '-' . $id;
    }

    /**
     * Get the admin URL for a menu page.
     *
     * @param string $id Menu ID (without prefix)
     * @param array $args Optional query arguments to append
     * @return string Full admin URL
     */
    public static function getUrl(string $id, array $args = []): string
    {
        $baseUrl = admin_url('admin.php?page=' . self::getSlug($id));

        if (!empty($args)) {
            $baseUrl = add_query_arg($args, $baseUrl);
        }

        return $baseUrl;
    }
}
