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
 * Handles registration and enqueueing of AdminUI assets.
 *
 * @package ProactiveSiteAdvisor\AdminUI\Assets
 * @since   1.0.0
 */
class AssetLoader extends AbstractSingleton
{
    /** Whether assets have been registered. */
    private bool $registered = false;

    /** Whether core assets have been enqueued. */
    private bool $coreEnqueued = false;

    /** Core asset handle. */
    private const CORE_HANDLE = 'admin-ui';

    /** Register the assets manager. */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /** Enqueue core AdminUI assets. */
    public function enqueueAssets(): void
    {
        $this->enqueueCoreAssets();
    }

    /** Enqueue core AdminUI assets. */
    private function enqueueCoreAssets(): void
    {
        if ($this->coreEnqueued) {
            return;
        }

        $handle = AssetsComponent::getHandle(self::CORE_HANDLE);

        if (wp_style_is($handle) || wp_script_is($handle)) {
            $this->coreEnqueued = true;
            return;
        }

        AssetsComponent::registerStyle(self::CORE_HANDLE, 'css/admin.min.css');
        AssetsComponent::enqueueStyle(self::CORE_HANDLE);

        AssetsComponent::registerScript(self::CORE_HANDLE, 'js/admin.min.js', ['jquery'], null, true);
        AssetsComponent::enqueueScript(self::CORE_HANDLE);

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

    /** Get the current user's theme preference. */
    public function getCurrentTheme(): string
    {
        return ThemeSwitcher::instance()->getCurrentTheme();
    }
}