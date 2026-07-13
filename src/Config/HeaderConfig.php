<?php

namespace ProactiveSiteAdvisor\Config;

use ProactiveSiteAdvisor\Utils\MenuUtils;
use ProactiveSiteAdvisor\AdminUI\Theme\ThemeSwitcher;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Centralized configuration for admin header defaults.
 *
 * @package ProactiveSiteAdvisor\Config
 * @since   1.0.0
 */
class HeaderConfig
{
    /** Get default header configuration. */
    public static function getDefaults(): array
    {
        return [
            'title'     => self::getDefaultTitle(),
            'titleLink' => self::getDefaultTitleLink(),
            'version'   => self::getDefaultVersion(),
            'logoUrl'   => self::getDefaultLogoUrl(),
            'navItems'  => self::getDefaultNavItems(),
            'actions'   => self::getDefaultActions(),
            'theme'     => self::getDefaultTheme(),
        ];
    }

    /** Get default plugin title. */
    public static function getDefaultTitle(): string
    {
        return esc_html__('Proactive Site Advisor', 'proactive-site-advisor');
    }

    /** Get default title link (main plugin page). */
    public static function getDefaultTitleLink(): string
    {
        return MenuUtils::getUrl(PROACTIVE_SITE_ADVISOR_SLUG);
    }

    /** Get default version string. */
    public static function getDefaultVersion(): string
    {
        return defined('PROACTIVE_SITE_ADVISOR_VERSION') ? PROACTIVE_SITE_ADVISOR_VERSION : '1.0.0';
    }

    /** Get default logo URL. */
    public static function getDefaultLogoUrl(): string
    {
        if (is_rtl()) {
            return PROACTIVE_SITE_ADVISOR_ASSETS . 'img/header-logo/header-logo-rtl.svg';
        }

        return PROACTIVE_SITE_ADVISOR_ASSETS . 'img/header-logo/header-logo.svg';
    }

    /** Get default navigation items. */
    public static function getDefaultNavItems(): array
    {
        return [];
    }

    /** Get default action buttons. */
    public static function getDefaultActions(): array
    {
        return [];
    }

    /** Get the default theme for the header. */
    public static function getDefaultTheme(): string
    {
        return ThemeSwitcher::instance()->getCurrentTheme();
    }

    /** Merge page-specific context with defaults. */
    public static function merge(array $overrides, ?array $defaults = null): array
    {
        $defaults = $defaults ?? self::getDefaults();
        $result   = $defaults;

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === false) {
                unset($result[$key]);
                continue;
            }

            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                $result[$key] = self::mergeArrayItems($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /** Merge array items for navItems and actions. */
    private static function mergeArrayItems(array $defaults, array $overrides): array
    {
        $result = $defaults;

        foreach ($overrides as $key => $item) {
            if ($item === null || $item === false) {
                unset($result[$key]);
                continue;
            }

            $result[$key] = $item;
        }

        return array_values($result);
    }

    /** Get defaults with specific items disabled. */
    public static function getDefaultsWithout(array $disableNavItems = [], array $disableActions = []): array
    {
        $defaults = self::getDefaults();

        foreach ($disableNavItems as $key) {
            unset($defaults['navItems'][$key]);
        }

        foreach ($disableActions as $key) {
            unset($defaults['actions'][$key]);
        }

        $defaults['navItems'] = array_values($defaults['navItems']);
        $defaults['actions']  = array_values($defaults['actions']);

        return $defaults;
    }

    /** Get minimal header (brand only, no nav or actions). */
    public static function getMinimal(): array
    {
        return [
            'title'     => self::getDefaultTitle(),
            'titleLink' => self::getDefaultTitleLink(),
            'version'   => self::getDefaultVersion(),
            'logoUrl'   => self::getDefaultLogoUrl(),
            'navItems'  => [],
            'actions'   => [],
        ];
    }

    /** Add a navigation item to defaults. */
    public static function addNavItem(string $key, array $item, ?string $after = null): array
    {
        $navItems = self::getDefaultNavItems();

        if ($after === null || !isset($navItems[$after])) {
            $navItems[$key] = $item;
        } else {
            $result = [];
            foreach ($navItems as $k => $v) {
                $result[$k] = $v;
                if ($k === $after) {
                    $result[$key] = $item;
                }
            }
            $navItems = $result;
        }

        return $navItems;
    }

    /** Add an action button to defaults. */
    public static function addAction(string $key, array $action, bool $prepend = false): array
    {
        $actions = self::getDefaultActions();

        if ($prepend) {
            $actions = [$key => $action] + $actions;
        } else {
            $actions[$key] = $action;
        }

        return $actions;
    }
}