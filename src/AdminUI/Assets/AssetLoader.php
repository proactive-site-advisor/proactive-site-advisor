<?php

namespace ProactiveSiteAdvisor\AdminUI\Assets;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Components\AssetsComponent;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Components\AjaxComponent;
use ProactiveSiteAdvisor\AdminUI\Theme\ThemeSwitcher;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AssetLoader
 *
 * Handles the registration and enqueueing of AdminUI assets including
 * core styles/scripts and third-party vendor libraries.
 *
 * @package ProactiveSiteAdvisor\AdminUI\Assets
 * @version 1.0.0
 */
class AssetLoader extends AbstractSingleton
{
    /**
     * Whether assets have been registered.
     *
     * @var bool
     */
    private bool $registered = false;

    /**
     * Whether core assets have been enqueued.
     *
     * @var bool
     */
    private bool $coreEnqueued = false;

    /**
     * Core asset handle.
     */
    private const CORE_HANDLE = 'admin-ui';

    /**
     * Register the assets manager.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue core AdminUI assets.
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        $this->enqueueCoreAssets();
    }

    /**
     * Enqueue core AdminUI assets.
     *
     * @return void
     */
    private function enqueueCoreAssets(): void
    {
        // Prevent duplicate enqueuing
        if ($this->coreEnqueued) {
            return;
        }

        $handle = AssetsComponent::getHandle(self::CORE_HANDLE);

        // Check if already enqueued by WordPress
        if (wp_style_is($handle) || wp_script_is($handle)) {
            $this->coreEnqueued = true;
            return;
        }

        // Register and enqueue styles
        AssetsComponent::registerStyle(self::CORE_HANDLE, 'css/admin.min.css');
        AssetsComponent::enqueueStyle(self::CORE_HANDLE);

        // Register and enqueue scripts
        AssetsComponent::registerScript(self::CORE_HANDLE, 'js/admin.min.js', ['jquery'], null, true);
        AssetsComponent::enqueueScript(self::CORE_HANDLE);

        // Localize script with config
        AssetsComponent::localizeScript(self::CORE_HANDLE, PrefixConfig::CONFIG_OBJECT, [
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => AjaxComponent::createNonce(),
            'restUrl'   => rest_url(PrefixConfig::SLUG . '/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'theme'     => $this->getCurrentTheme(),
            'i18n'      => [],
        ]);

        $this->coreEnqueued = true;
    }

    /**
     * Get the current user's theme preference.
     *
     * @return string Theme name ('light' or 'dark').
     */
    public function getCurrentTheme(): string
    {
        return ThemeSwitcher::instance()->getCurrentTheme();
    }
}