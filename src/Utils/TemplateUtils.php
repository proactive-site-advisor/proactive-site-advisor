<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TemplateUtils
 *
 * Handles rendering of plugin templates with variable injection.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.0
 */
class TemplateUtils
{
    /**
     * Render a template file with variables.
     *
     * Search order:
     *  1. Child theme: /proactive-site-advisor/templates/
     *  2. Parent theme: /proactive-site-advisor/templates/
     *  3. Plugin default: /templates/
     *
     * @param string $templateName Template filename (e.g., 'example-notice.php').
     * @param array $variables Associative array of variables to extract into the template.
     * @param bool $requireOnce Whether to require_once instead of require.
     *
     * @return string|false Rendered HTML or false if not found.
     */
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

        // Locate first existing template
        $located = current(array_filter($paths, 'file_exists'));

        if (!$located) {
            /**
             * Fires when a ProactiveSiteAdvisor template cannot be found.
             *
             * @param string $templateName Name of the missing template file.
             */
            do_action('proactive_site_advisor_template_missing', $templateName);

            return false;
        }

        // Extract variables safely
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
