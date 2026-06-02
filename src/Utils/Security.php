<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Security
 *
 * Common utilities for nonce and capability checks.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.0
 */
class Security
{
    /**
     * Check if current request is AJAX.
     *
     * @return bool
     */
    public static function isAjax(): bool
    {
        return function_exists('wp_doing_ajax') && wp_doing_ajax();
    }

    /**
     * Check if current environment is admin.
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return function_exists('is_admin') && is_admin();
    }

    /**
     * Verify a nonce from POST.
     *
     * @param string $action
     * @param string $field
     * @return bool
     */
    public static function verifyNonce(string $action, string $field): bool
    {
        if (!isset($_POST[$field])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[$field]));

        return wp_verify_nonce($nonce, $action) !== false;
    }

    /**
     * Verify an AJAX nonce.
     *
     * @param string $action
     * @param string $field
     * @return bool
     */
    public static function verifyAjaxNonce(string $action, string $field): bool
    {
        return check_ajax_referer($action, $field, false) !== false;
    }

    /**
     * Check if current user has capability.
     *
     * @param string $capability
     * @return bool
     */
    public static function hasCapability(string $capability = 'manage_options'): bool
    {
        return current_user_can($capability);
    }
}