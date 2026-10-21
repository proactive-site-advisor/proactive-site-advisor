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
    '(?:^|/)-{1,2}/?$',
    '(?:^|/)-{1,2}(ajax|json|backup|xmlrpc|env|sql)-*[a-z0-9_-]*/?$',
];