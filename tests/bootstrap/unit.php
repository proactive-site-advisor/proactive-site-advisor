<?php
/**
 * PHPUnit bootstrap file for unit tests.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since      1.2.8
 */

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    throw new RuntimeException(
        'Composer autoload file not found. Run composer install first.'
    );
}

require_once $autoload;

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 3) . '/');
}