<?php
/**
 * PHPUnit bootstrap file for integration tests.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */

define('TESTS_PLUGIN_DIR', dirname(__DIR__, 2));
define('UNIT_TESTS_DATA_PLUGIN_DIR', TESTS_PLUGIN_DIR . '/tests/Data/');

/** Resolve the WordPress core directory. */
if (!defined('WP_CORE_DIR')) {
    $wp_core_dir = getenv('WP_CORE_DIR');

    if (!$wp_core_dir) {
        $wp_core_dir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wordpress';
    }

    define('WP_CORE_DIR', $wp_core_dir);
}

/** Resolve the WordPress test library directory. */
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wordpress-tests-lib';
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    echo 'Could not find WordPress test library. Have you run bin/install-wp-tests.sh?' . PHP_EOL;
    exit(1);
}

/** Validate the WordPress core directory. */
$_wp_core_dir = realpath(WP_CORE_DIR);

if (
    false === $_wp_core_dir ||
    !file_exists($_wp_core_dir . '/wp-includes/version.php')
) {
    echo 'Could not find WordPress core directory: ' . WP_CORE_DIR . PHP_EOL;
    exit(1);
}

/** Normalize the WordPress core path for cross-platform compatibility. */
$_wp_core_dir = rtrim(str_replace('\\', '/', $_wp_core_dir), '/') . '/';

/** Synchronize ABSPATH in the generated WordPress test configuration. */
$_config_file = $_tests_dir . '/wp-tests-config.php';

if (!file_exists($_config_file)) {
    echo 'Could not find WordPress test configuration: ' . $_config_file . PHP_EOL;
    exit(1);
}

$_config_contents = file_get_contents($_config_file);

if (false === $_config_contents) {
    echo 'Could not read WordPress test configuration.' . PHP_EOL;
    exit(1);
}

$_config_replacements = 0;

$_updated_config = preg_replace_callback(
    '/define\s*\(\s*([\'"])ABSPATH\1\s*,\s*([\'"])[^\'"]*\2\s*\)\s*;/',
    static function () use ($_wp_core_dir) {
        return "define('ABSPATH', " . var_export($_wp_core_dir, true) . ');';
    },
    $_config_contents,
    1,
    $_config_replacements
);

if (
    null === $_updated_config ||
    1 !== $_config_replacements
) {
    echo 'Could not update ABSPATH in WordPress test configuration.' . PHP_EOL;
    exit(1);
}

if (($_updated_config !== $_config_contents) && false === file_put_contents($_config_file, $_updated_config)) {
    echo 'Could not update WordPress test configuration.' . PHP_EOL;
    exit(1);
}

/** Give access to tests_add_filter() function. */
require_once $_tests_dir . '/includes/functions.php';

/** Manually load the plugin being tested. */
function _manually_load_plugin()
{
    require TESTS_PLUGIN_DIR . '/proactive-site-advisor.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

/** Start up the WordPress testing environment. */
require $_tests_dir . '/includes/bootstrap.php';
