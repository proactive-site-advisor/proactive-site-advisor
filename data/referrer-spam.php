<?php
/**
 * Referrer Spam Blacklist
 *
 * https://github.com/matomo-org/referrer-spam-blacklist
 *
 * @package ProactiveSiteAdvisor\data
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

return file(__DIR__ . '/referrer-spam-domains.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);