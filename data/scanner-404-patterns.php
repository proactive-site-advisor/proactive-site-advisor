<?php
/**
 * Scanner 404 URL Patterns
 *
 * Patterns that, when matched against a 404 request URI,
 * indicate a vulnerability scanner or malicious bot.
 *
 * @package ProactiveSiteAdvisor\Data
 * @since   1.0.0
 */

return [
    '\.env$',
    '\.git/config$',
    '\.svn/entries$',
    'wp-config\.php~$',
    'wp-config\.bak$',
    'wp-content/debug\.log',
    '/adminer\.php',
    '/phpmyadmin',
    '/pma/',
    '/mysql/',
    '/dbadmin/',
    '/wp-content/plugins/WordPressCore',
    '/wp-content/et-cache',
    '/wp-content/backups',
    '/wp-content/upgrade',
    '/wp-content/plugins/revslider/',
    '/wp-content/plugins/woocommerce/',
    '/wp-content/plugins/contact-form-7/',
    '/wp-content/plugins/wp-file-manager/',
    '/wp-json/wp/v2/users',
    '/xmlrpc\.php',
    '/wp-trackback\.php',
];