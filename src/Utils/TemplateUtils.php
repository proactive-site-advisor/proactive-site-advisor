<?php

namespace ProactiveSiteAdvisor\Utils;

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

        $paths = [
            get_stylesheet_directory() . "/proactive-site-advisor/templates/{$templateName}",
            get_template_directory() . "/proactive-site-advisor/templates/{$templateName}",
            PROACTIVE_SITE_ADVISOR_TEMPLATES_PATH . $templateName,
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