<?php

/**
 * Scanner probe URL patterns.
 *
 * @package ProactiveSiteAdvisor\Data
 * @since   1.2.7
 */

if (!defined('ABSPATH')) {
    exit;
}

return [
    '(?:^|/)---+\d*-?/?$',
    '(?:^|/)\d+-{3,}/?$',
];