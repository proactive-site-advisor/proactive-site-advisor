<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Environment
 *
 * Detects the current execution environment.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.0
 */
class Environment
{
    /**
     * Check if running in local development environment.
     *
     * @return bool
     */
    public static function isLocal(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $addr = $_SERVER['REMOTE_ADDR'] ?? '';

        $localHosts = ['localhost', '127.0.0.1', '::1'];

        if (in_array($host, $localHosts, true)) {
            return true;
        }

        if (in_array($addr, $localHosts, true)) {
            return true;
        }

        $localSuffixes = ['.local', '.test', '.loc', '.dev'];

        foreach ($localSuffixes as $suffix) {
            if (strpos($host, $suffix) !== false) {
                return true;
            }
        }

        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type() !== 'local';
        }

        return false;
    }
}