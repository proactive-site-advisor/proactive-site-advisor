<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Referrer Spam Blacklist
 *
 * Sourced from Matomo referrer-spam-list
 * https://github.com/matomo-org/referrer-spam-blacklist
 *
 * @package ProactiveSiteAdvisor\Data
 * @since   1.0.0
 */

return file(__DIR__ . '/referrer-spam-domains.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);