<?php
/**
 * Browser Pattern Regex
 *
 * @package ProactiveSiteAdvisor\data
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

return '~^(?!.*HeadlessChrome)(?=.*(?:Chrome|CriOS|Firefox|FxiOS|Edg|OPR|SamsungBrowser|YaBrowser|Whale|Vivaldi|DuckDuckGo|Version/\d+(?:\.\d+)*.*Safari)).+$~i';