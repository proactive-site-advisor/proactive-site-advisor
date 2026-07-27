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

        foreach ($input as $section => $data) {
            if (!is_array($data) || !isset($rules[$section])) {
                continue;
            }

            $clean[$section] = Sanitize::map($data, $rules[$section]);
            $clean[$section] = $this->applyPostProcessing($section, $clean[$section]);
        }

        return $clean;
    }

    /** Apply extra constraints after basic sanitization. */
    private function applyPostProcessing(string $section, array $clean): array
    {
        if ($section === PluginSettings::SECTION_ALERT_CONDITIONS) {
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

        if ($section === PluginSettings::SECTION_ALERTS) {
            foreach ($clean as $field => $value) {
                $clean[$field] = $value ? 1 : 0;
            }
        }

        return $clean;
    }
}