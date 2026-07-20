<?php

namespace ProactiveSiteAdvisor\Utils;

use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles rendering of plugin templates with variable injection.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class TemplateUtils
{
    /** Render a template file with variables. */
    public static function renderTemplate(string $templateName, array $variables = [], bool $requireOnce = false)
    {
        if (pathinfo($templateName, PATHINFO_EXTENSION) !== 'php') {
            $templateName .= '.php';
        }

        $slug  = PrefixConfig::SLUG;
        $paths = [
            trailingslashit(get_stylesheet_directory()) . $slug . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $templateName,
            trailingslashit(get_template_directory()) . $slug . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $templateName,
            trailingslashit(PROACTIVE_SITE_ADVISOR_TEMPLATES_PATH) . $templateName,
        ];

        $located = current(array_filter($paths, 'file_exists'));

        if (!$located) {
            return false;
        }

        if (!empty($variables)) {
            extract($variables, EXTR_SKIP);
        }

        ob_start();
        if ($requireOnce) {
            require_once $located;
        } else {
            require $located;
        }

        return ob_get_clean();
    }
}