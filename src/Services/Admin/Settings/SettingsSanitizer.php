<?php

namespace ProactiveSiteAdvisor\Services\Admin\Settings;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Utils\Sanitize;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitizes plugin settings.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Settings
 * @since   1.0.0
 */
class SettingsSanitizer
{
    /** Sanitize all settings sections present in the input. */
    public function all(array $input): array
    {
        $rules = SettingsSanitizationSchema::getRules();
        $clean = [];

        foreach ($rules as $section => $fields) {
            $data = isset($input[$section]) && is_array($input[$section]) ? $input[$section] : [];

            foreach ($fields as $field => $type) {
                if ($type === 'bool' && !array_key_exists($field, $data)) {
                    $data[$field] = 0;
                }
            }

            $clean[$section] = Sanitize::map($data, $fields);
            $clean[$section] = $this->applyPostProcessing($section, $clean[$section]);
        }

        return $clean;
    }

    /** Apply extra constraints after basic sanitization. */
    private function applyPostProcessing(string $section, array $clean): array
    {
        if ($section === PluginSettings::SECTION_ALERTS) {
            foreach ($clean as $field => $value) {
                $clean[$field] = $value ? 1 : 0;
            }
        }

        if ($section === PluginSettings::SECTION_THRESHOLDS) {
            $percentFields = [
                PluginSettings::TRAFFIC_SPIKE_PERCENT,
                PluginSettings::TRAFFIC_DROP_PERCENT,
                PluginSettings::ERROR_404_SPIKE_PERCENT,
                PluginSettings::BOT_SPIKE_PERCENT,
                PluginSettings::BOT_DROP_PERCENT,
            ];

            foreach ($percentFields as $field) {
                if (isset($clean[$field])) {
                    $clean[$field] = max(5, min(100, $clean[$field]));
                }
            }
        }

        if ($section === PluginSettings::SECTION_NOTIFICATIONS) {
            if (empty($clean[PluginSettings::DIGEST_RECIPIENT_EMAIL])) {
                $clean[PluginSettings::DIGEST_RECIPIENT_EMAIL] = get_option('admin_email');
            }

            foreach ($clean as $field => $value) {
                if ($field === PluginSettings::DIGEST_RECIPIENT_EMAIL) {
                    continue;
                }
                $clean[$field] = $value ? 1 : 0;
            }
        }

        return $clean;
    }
}