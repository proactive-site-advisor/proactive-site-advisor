<?php

/**
 * Plugin Name:         Proactive Site Advisor – Privacy‑First Anomaly Alerts
 * Plugin URI:          https://github.com/proactive-site-advisor/proactive-site-advisor
 * Description:         Get early warnings on anomalies like traffic drops, 404 surges, and bot spikes. Privacy‑friendly local monitoring with actionable next steps.
 * Version:             1.0.8
 * Author:              Mohammad Yari
 * Author URI:          https://github.com/proactive-site-advisor
 * Text Domain:         proactive-site-advisor
 * Domain Path:         /languages
 * Requires at least:   6.1
 * Requires PHP:        7.4
 * License:             GPL-2.0-or-later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 */

# Exit if accessed directly
defined('ABSPATH') || exit;

/** Plugin slug. */
if (!defined('PROACTIVE_SITE_ADVISOR_SLUG')) {
    define('PROACTIVE_SITE_ADVISOR_SLUG', 'proactive-site-advisor');
}

/** Main plugin file. */
if (!defined('PROACTIVE_SITE_ADVISOR_PLUGIN_FILE')) {
    define('PROACTIVE_SITE_ADVISOR_PLUGIN_FILE', __FILE__);
}

/** Main plugin path. */
if (!defined('PROACTIVE_SITE_ADVISOR_PATH')) {
    define('PROACTIVE_SITE_ADVISOR_PATH', plugin_dir_path(PROACTIVE_SITE_ADVISOR_PLUGIN_FILE));
}

/** Plugin URL. */
if (!defined('PROACTIVE_SITE_ADVISOR_URL')) {
    define('PROACTIVE_SITE_ADVISOR_URL', plugin_dir_url(PROACTIVE_SITE_ADVISOR_PLUGIN_FILE));
}

/** Default plugin templates path. */
if (!defined('PROACTIVE_SITE_ADVISOR_TEMPLATES_PATH')) {
    define('PROACTIVE_SITE_ADVISOR_TEMPLATES_PATH', PROACTIVE_SITE_ADVISOR_PATH . 'templates/');
}

/** Plugin assets URL. */
if (!defined('PROACTIVE_SITE_ADVISOR_ASSETS')) {
    define('PROACTIVE_SITE_ADVISOR_ASSETS', PROACTIVE_SITE_ADVISOR_URL . 'assets/');
}

/** Plugin version. */
if (!defined('PROACTIVE_SITE_ADVISOR_VERSION')) {
    define('PROACTIVE_SITE_ADVISOR_VERSION', '1.0.8');
}

/** Database schema version. */
if (!defined('PROACTIVE_SITE_ADVISOR_DB_VERSION')) {
    define('PROACTIVE_SITE_ADVISOR_DB_VERSION', '1.0.3');
}

/** Autoload all classes using Composer autoloader. */
require_once __DIR__ . '/vendor/autoload.php';

use ProactiveSiteAdvisor\Core;
use ProactiveSiteAdvisor\Lifecycle\ActivationHandler;
use ProactiveSiteAdvisor\Lifecycle\DeactivationHandler;

/** Register activation hook. */
ActivationHandler::register();

/** Register deactivation hook. */
DeactivationHandler::register();

/** Returns the main instance of the plugin Core class. */
if (!function_exists('proactiveSiteAdvisor')) {
    function proactiveSiteAdvisor(): ?Core
    {
        return Core::instance();
    }
}

/** Initialize the plugin. */
proactiveSiteAdvisor();