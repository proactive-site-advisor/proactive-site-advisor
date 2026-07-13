<?php
/**
 * Browser Pattern Regex
 *
 * Regex pattern for validating legitimate browser user agents.
 *
 * @package ProactiveSiteAdvisor\Data
 * @since   1.0.0
 */
return '~^(?!.*HeadlessChrome)(?=.*(?:Chrome|CriOS|Firefox|FxiOS|Edg|OPR|SamsungBrowser|YaBrowser|Whale|Vivaldi|DuckDuckGo|Version/\d+(?:\.\d+)*.*Safari)).+$~i';