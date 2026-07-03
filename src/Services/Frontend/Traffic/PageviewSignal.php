<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class PageviewSignal
 *
 * Determines whether the current request represents a real pageview signal.
 *
 * A valid pageview signal must:
 * - Reach the template rendering stage
 * - Belong to the main query (user intent)
 * - Not be an admin, system, or background request
 * - Not target a static file or asset
 *
 * This class is intentionally final and static:
 * it represents a fixed domain rule used for anomaly detection,
 * * not a customizable tracking behavior.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class PageviewSignal
{
    /**
     * Check if the current request should be collected as a pageview signal.
     *
     * @return bool
     */
    public static function shouldCollect(): bool
    {
        if (
            !isset($_SERVER['REQUEST_METHOD']) ||
            sanitize_key(wp_unslash($_SERVER['REQUEST_METHOD'])) !== 'get'
        ) {
            return false;
        }

        if (!did_action('wp')) {
            return false;
        }

        if (!is_main_query()) {
            return false;
        }

        if (is_trackback()) {
            return false;
        }

        if (
            (defined('REST_REQUEST') && REST_REQUEST) ||
            (defined('DOING_AJAX') && DOING_AJAX) ||
            (defined('DOING_CRON') && DOING_CRON) ||
            is_admin() ||
            is_favicon() ||
            is_feed() ||
            is_preview()
        ) {
            return false;
        }

        if (self::isExcludedUser()) {
            return false;
        }

        $uri       = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $path      = wp_parse_url($uri, PHP_URL_PATH);
        $extension = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));

        return $path !== null && $extension === '';
    }

    /**
     * Check if the current user should be excluded from pageview tracking.
     *
     * Excludes logged-in users with administrative or content creation roles
     * to prevent artificial traffic inflation from site staff.
     *
     * @return bool True if the user should be excluded.
     */
    private static function isExcludedUser(): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();

        $defaultRoles = [
            'administrator',
            'editor',
            'author',
            'contributor',
            'shop_manager',
            'shop_worker',
            'shop_staff',
            'bbp_moderator',
            'bbp_keymaster',
            'wpseo_manager',
            'wpseo_editor',
            's2member_admin',
            'member_admin',
        ];

        /**
         * Filter the list of user roles that should be excluded from pageview tracking.
         *
         * @param string[] $defaultRoles
         * @since 1.0.0
         *
         */
        $excludedRoles = apply_filters(
            'proactive_site_advisor_excluded_user_roles',
            $defaultRoles
        );

        return array_intersect($excludedRoles, (array)$user->roles) !== [];
    }
}