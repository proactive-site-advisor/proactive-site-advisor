<?php

namespace ProactiveSiteAdvisor\AdminUI\Theme;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Components\AjaxComponent;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\UserOptions;
use ProactiveSiteAdvisor\Config\PrefixConfig;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ThemeManager
 *
 * Manages the admin UI theme (light / dark) on a per-user basis.
 * Handles theme persistence, AJAX-based switching, and UI helpers.
 *
 * @package ProactiveSiteAdvisor\AdminUI\Theme
 */
class ThemeManager extends AbstractSingleton
{
    /**
     * Light admin theme identifier.
     */
    public const THEME_LIGHT = 'light';

    /**
     * Dark admin theme identifier.
     */
    public const THEME_DARK = 'dark';

    /**
     * Whether hooks and AJAX handlers have already been registered.
     *
     * Prevents duplicate registration.
     */
    private bool $registered = false;

    /**
     * Register hooks and AJAX handlers for admin theme management.
     *
     * This method is idempotent and safe to call multiple times.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        // Register AJAX handler for theme switching.
        AjaxComponent::register('switch_theme', [$this, 'handleThemeSwitch'], false);

        // Add theme-specific class to the admin body.
        add_action('admin_body_class', [$this, 'addThemeBodyClass']);
    }

    /**
     * Get the currently active admin theme for the current user.
     *
     * Falls back to the light theme if the stored value is invalid
     * or missing.
     *
     * @return string One of THEME_LIGHT or THEME_DARK.
     */
    public function getCurrentTheme(): string
    {
        $theme = OptionUtils::getUserOption(UserOptions::ADMIN_THEME, self::THEME_LIGHT);

        return in_array($theme, [self::THEME_LIGHT, self::THEME_DARK], true)
            ? $theme
            : self::THEME_LIGHT;
    }

    /**
     * Set the admin theme for the current user.
     *
     * @param string $theme Theme identifier.
     *
     * @return bool True on success, false if the theme is invalid.
     */
    public function setTheme(string $theme): bool
    {
        if (!in_array($theme, [self::THEME_LIGHT, self::THEME_DARK], true)) {
            return false;
        }

        OptionUtils::setUserOption(UserOptions::ADMIN_THEME, $theme);
        return true;
    }

    /**
     * Check whether the current admin theme is dark mode.
     *
     * @return bool
     */
    public function isDarkMode(): bool
    {
        return $this->getCurrentTheme() === self::THEME_DARK;
    }

    /**
     * Check whether the current admin theme is light mode.
     *
     * @return bool
     */
    public function isLightMode(): bool
    {
        return $this->getCurrentTheme() === self::THEME_LIGHT;
    }

    /**
     * Handle AJAX requests for switching the admin theme.
     *
     * Expects a `theme` value in the POST payload.
     * Nonce verification is handled by AjaxComponent.
     *
     * @return void
     */
    public function handleThemeSwitch(): void
    {
        // Safe: Only updates current user's data; nonce is verified and user capability is checked in AjaxComponent::register().
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $theme = isset($_POST['theme']) ? sanitize_text_field(wp_unslash($_POST['theme'])) : '';

        if ($this->setTheme($theme)) {
            AjaxComponent::sendSuccess(
                ['theme' => $theme],
                __('Theme switched successfully.', 'proactive-site-advisor')
            );
        } else {
            AjaxComponent::sendError(
                __('Invalid theme.', 'proactive-site-advisor'),
                400
            );
        }
    }

    /**
     * Append the current theme class to the admin body element.
     *
     * @param string $classes Existing admin body classes.
     *
     * @return string Modified body classes.
     */
    public function addThemeBodyClass(string $classes): string
    {

        return $classes . PrefixConfig::css('theme-' . $this->getCurrentTheme());
    }

    /**
     * Static shortcut for retrieving the current admin theme.
     *
     * @return string
     */
    public static function theme(): string
    {
        return self::getInstance()->getCurrentTheme();
    }

    /**
     * Static shortcut for checking dark mode state.
     *
     * @return bool
     */
    public static function isDark(): bool
    {
        return self::getInstance()->isDarkMode();
    }
}