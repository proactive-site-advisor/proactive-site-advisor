<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Utils\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Saves plugin settings.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsSaver
{
    /** Settings sanitizer instance. */
    private SettingsSanitizer $sanitizer;

    /** Initializes the settings saver. */
    public function __construct()
    {
        $this->sanitizer = new SettingsSanitizer();
    }

    /** Saves plugin settings. */
    public function save(array $input): void
    {
        $nonceAction = PrefixConfig::nonce('settings');
        $nonceField  = PrefixConfig::nonce('settings_nonce');

        if (!Security::verifyNonce($nonceAction, $nonceField)) {
            wp_die(esc_html__('Security check failed. Please refresh the page and try again.', 'proactive-site-advisor'));
        }

        $settings = OptionUtils::getAllOptions();

        $cleanSections = $this->sanitizer->all($input);

        foreach ($cleanSections as $section => $values) {
            foreach ($values as $key => $value) {
                $fullKey = OptionUtils::makeKey($section, $key);
                OptionUtils::setNestedValue($settings, $fullKey, $value);
            }
        }

        OptionUtils::updateAll($settings);
    }
}