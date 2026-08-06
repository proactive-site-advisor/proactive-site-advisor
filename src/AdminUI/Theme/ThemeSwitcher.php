<?php

namespace ProactiveSiteAdvisor\AdminUI\Theme;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Components\AjaxComponent;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\UserOptions;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\PluginUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages the admin UI theme (light / dark) on a per-user basis.
 *
 * phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is handled inside AjaxComponent::register().
 *
 * @package ProactiveSiteAdvisor\AdminUI\Theme
 * @since   1.0.0
 */
class ThemeSwitcher extends AbstractSingleton
{
    /** Light admin theme identifier. */
    public const THEME_LIGHT = 'light';

    /** Dark admin theme identifier. */
    public const THEME_DARK = 'dark';

    /** Whether hooks and AJAX handlers have already been registered. */
    private bool $registered = false;

    /** Register hooks and AJAX handlers for admin theme management. */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        AjaxComponent::register('switch_theme', [$this, 'handleThemeSwitch'], false);

        add_action('admin_body_class', [$this, 'addThemeBodyClass']);
    }

    /** Get the currently active admin theme for the current user. */
    public function getCurrentTheme(): string
    {
        $theme = OptionUtils::getUserOption(UserOptions::ADMIN_THEME, self::THEME_LIGHT);

        return in_array($theme, [self::THEME_LIGHT, self::THEME_DARK], true)
            ? $theme
            : self::THEME_LIGHT;
    }

    /** Set the admin theme for the current user. */
    public function setTheme(string $theme): bool
    {
        if (!in_array($theme, [self::THEME_LIGHT, self::THEME_DARK], true)) {
            return false;
        }

        OptionUtils::setUserOption(UserOptions::ADMIN_THEME, $theme);
        return true;
    }

    /** Check whether the current admin theme is dark mode. */
    public function isDarkMode(): bool
    {
        return $this->getCurrentTheme() === self::THEME_DARK;
    }

    /** Check whether the current admin theme is light mode. */
    public function isLightMode(): bool
    {
        return $this->getCurrentTheme() === self::THEME_LIGHT;
    }

    /** Handle AJAX requests for switching the admin theme. */
    public function handleThemeSwitch(): void
    {
        $theme = isset($_POST['theme']) ? sanitize_text_field(wp_unslash($_POST['theme'])) : '';

        if ($this->setTheme($theme)) {
            AjaxComponent::sendSuccess(
                ['theme' => $theme],
                __('Theme switched successfully.', 'proactive-site-advisor')
            );
        } else {
            AjaxComponent::sendError(__('Invalid theme.', 'proactive-site-advisor'));
        }
    }

    /** Append the current theme class to the admin body element. */
    public function addThemeBodyClass(string $classes): string
    {
        if (!PluginUtils::isPluginScreen()) {
            return $classes;
        }

        return $classes . ' ' . PrefixConfig::css('ui') . ' ' . PrefixConfig::css('theme-' . $this->getCurrentTheme());
    }

    /** Static shortcut for retrieving the current admin theme. */
    public static function theme(): string
    {
        return self::instance()->getCurrentTheme();
    }

    /** Static shortcut for checking dark mode state. */
    public static function isDark(): bool
    {
        return self::instance()->isDarkMode();
    }
}