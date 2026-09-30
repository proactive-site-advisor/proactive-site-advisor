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

        if ($section === PluginSettings::SECTION_SENSITIVITY) {
            $clean = $this->sanitizeSensitivity($clean);
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

    /** Sanitize sensitivity section: level, auto_tune, and min_abs fields. */
    private function sanitizeSensitivity(array $clean): array
    {
        $allowedLevels = ['low', 'normal', 'high'];

        $level = $clean[PluginSettings::SENSITIVITY_LEVEL] ?? 'normal';
        if (!in_array($level, $allowedLevels, true)) {
            $level = 'normal';
        }
        $clean[PluginSettings::SENSITIVITY_LEVEL] = $level;

        $clean[PluginSettings::SENSITIVITY_AUTO_TUNE] =
            !empty($clean[PluginSettings::SENSITIVITY_AUTO_TUNE]) ? 1 : 0;

        $minAbsFields = [
            PluginSettings::TRAFFIC_MIN_ABS,
            PluginSettings::ERROR_404_MIN_ABS,
            PluginSettings::BOT_MIN_ABS,
        ];

        foreach ($minAbsFields as $field) {
            $value         = (int)($clean[$field] ?? 0);
            $clean[$field] = max(1, min(100000, $value));
        }

        return $clean;
    }
}