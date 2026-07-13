<?php

namespace ProactiveSiteAdvisor\Components;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\Security;
use Throwable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility class to simplify AJAX handling in WordPress plugins.
 *
 * @package ProactiveSiteAdvisor\Components
 * @since   1.0.0
 */
class AjaxComponent
{
    /** Register an AJAX action with automatic nonce verification and error handling. */
    public static function register(string $action, callable $callback, bool $public = true, bool $verifyNonce = true, string $nonceAction = 'nonce', string $nonceField = 'security', string $capability = 'manage_options'): void
    {
        $wrapped = static function () use ($callback, $verifyNonce, $nonceAction, $nonceField, $capability) {
            try {
                if ($verifyNonce) {
                    self::verifyNonce($nonceAction, $nonceField);
                }

                if ($capability) {
                    self::checkCapability($capability);
                }

                $callback();

            } catch (Throwable $e) {
                self::sendError(esc_html__('AJAX error: ', 'proactive-site-advisor') . $e->getMessage());
            }
        };

        $action = self::getActionName($action);
        add_action("wp_ajax_$action", $wrapped);
        if ($public) {
            add_action("wp_ajax_nopriv_$action", $wrapped);
        }
    }

    /** Create a WordPress nonce for a given (unprefixed) nonce identifier. */
    public static function createNonce(string $nonce = 'nonce'): string
    {
        return wp_create_nonce(self::getNonceAction($nonce));
    }

    /** Verify the WordPress AJAX nonce. */
    public static function verifyNonce(string $nonceAction = 'nonce', string $nonceField = 'security'): void
    {
        $nonceAction = self::getNonceAction($nonceAction);

        if (!Security::verifyAjaxNonce($nonceAction, $nonceField)) {
            self::sendError(
                esc_html__('Invalid security token.', 'proactive-site-advisor'),
                403
            );
        }
    }

    /** Check if the current user has the required capability. */
    public static function checkCapability(string $capability = 'manage_options'): void
    {
        if (!Security::hasCapability($capability)) {
            self::sendError(
                esc_html__('Unauthorized.', 'proactive-site-advisor'),
                403
            );
        }
    }

    /** Send a standardized JSON success response. */
    public static function sendSuccess(array $data = [], string $message = ''): void
    {
        wp_send_json([
            'success' => true,
            'data'    => $data,
            'message' => $message
        ]);
    }

    /** Send a standardized JSON error response. */
    public static function sendError(string $message, int $code = 400): void
    {
        wp_send_json([
            'success' => false,
            'data'    => null,
            'message' => $message
        ], $code);
    }

    /** Build the fully qualified AJAX action name. */
    public static function getActionName(string $action): string
    {
        return PrefixConfig::ajaxAction(sanitize_key($action));
    }

    /** Build the prefixed nonce action name. */
    public static function getNonceAction(string $nonce): string
    {
        return PrefixConfig::nonce(sanitize_key($nonce));
    }
}