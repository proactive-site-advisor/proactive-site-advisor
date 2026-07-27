<?php

namespace ProactiveSiteAdvisor\Utils;

use ProactiveSiteAdvisor\Config\PluginOptions;
use ProactiveSiteAdvisor\Config\PluginSettings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class to manage plugin options and user meta in WordPress.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class OptionUtils
{
    /** Get full option key with prefix. */
    public static function getMetaOptionName(string $key): string
    {
        return PluginOptions::META_PREFIX . $key;
    }

    /** Get default plugin options. */
    public static function getDefaults(): array
    {
        return [
            PluginSettings::SECTION_ALERT_CONDITIONS => [
                PluginSettings::MIN_WEEKLY_AVG          => 3,
                PluginSettings::MIN_PAGEVIEWS_FOR_ALERT => 10,
                PluginSettings::TRAFFIC_SPIKE_PERCENT   => 50,
                PluginSettings::TRAFFIC_DROP_PERCENT    => 30,
                PluginSettings::ERROR_404_SPIKE_PERCENT => 100,
                PluginSettings::BOT_SPIKE_PERCENT       => 100,
                PluginSettings::BOT_DROP_PERCENT        => 50,
            ],
            PluginSettings::SECTION_ALERTS           => [
                PluginSettings::ALERT_TRAFFIC_DROP  => 1,
                PluginSettings::ALERT_TRAFFIC_SPIKE => 1,
                PluginSettings::ALERT_404_SPIKE     => 1,
                PluginSettings::ALERT_BOT_SPIKE     => 1,
                PluginSettings::ALERT_BOT_DROP      => 1,
            ],
        ];
    }

    /** Get all plugin options (merged with defaults). */
    public static function getAllOptions(): array
    {
        $options = (array)get_option(PluginOptions::OPTION_NAME);

        if (empty($options)) {
            return self::getDefaults();
        }

        return $options;
    }

    /**
     * Get a single plugin option using dot notation.
     */
    public static function getOption(string $key, $default = null)
    {
        $options = self::getAllOptions();

        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($options) || !array_key_exists($segment, $options)) {
                return $default;
            }

            $options = $options[$segment];
        }

        return $options;
    }

    /** Set/update a single plugin option. */
    public static function setOption(string $key, $value): void
    {
        $options = self::getAllOptions();

        self::put($options, $key, $value);

        self::updateAll($options);
    }

    /** Delete a single plugin option. */
    public static function deleteOption(string $key): void
    {
        $options = (array)get_option(PluginOptions::OPTION_NAME, []);

        $keys = explode('.', $key);
        $last = array_pop($keys);

        $ref = &$options;

        foreach ($keys as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                return;
            }

            $ref = &$ref[$segment];
        }

        if (isset($ref[$last])) {
            unset($ref[$last]);
        }

        self::updateAll($options);
    }

    /** Reset all plugin options to defaults. */
    public static function resetOptions(): void
    {
        update_option(PluginOptions::OPTION_NAME, self::getDefaults());
    }

    /** Get a single user-specific option. */
    public static function getUserOption(string $key, $default = null)
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return $default;
        }

        $options = (array)get_user_meta($userId, PluginOptions::OPTION_NAME, true) ?: [];
        return $options[$key] ?? $default;
    }

    /** Set/update a single user-specific option. */
    public static function setUserOption(string $key, $value): void
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        $options       = (array)get_user_meta($userId, PluginOptions::OPTION_NAME, true) ?: [];
        $options[$key] = $value;
        update_user_meta($userId, PluginOptions::OPTION_NAME, $options);
    }

    /** Delete a single user-specific option. */
    public static function deleteUserOption(string $key): void
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        $options = (array)get_user_meta($userId, PluginOptions::OPTION_NAME, true) ?: [];
        if (isset($options[$key])) {
            unset($options[$key]);
            update_user_meta($userId, PluginOptions::OPTION_NAME, $options);
        }
    }

    /** Reset all user-specific options. */
    public static function resetUserOptions(): void
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        update_user_meta($userId, PluginOptions::OPTION_NAME, []);
    }

    /** Get plugin meta option (standalone option). */
    public static function getMeta(string $key, $default = null)
    {
        return get_option(self::getMetaOptionName($key), $default);
    }

    /** Set plugin meta option (standalone option). */
    public static function setMeta(string $key, $value, ?bool $autoload = null): void
    {
        update_option(self::getMetaOptionName($key), $value, $autoload);
    }

    /** Delete plugin meta option. */
    public static function deleteMeta(string $key): void
    {
        delete_option(self::getMetaOptionName($key));
    }

    /** Set a value in a nested array using dot notation. */
    private static function put(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $ref  = &$array;

        while (count($keys) > 1) {
            $segment = array_shift($keys);

            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }

            $ref = &$ref[$segment];
        }

        $ref[array_shift($keys)] = $value;
    }

    /** Get all options for a specific section. */
    public static function getSection(string $section): array
    {
        return self::getOption($section, []);
    }

    /** Build a dot notation key for a section option. */
    public static function makeKey(string $section, string $key): string
    {
        return $section . '.' . $key;
    }

    /** Set a value in a nested array using dot notation. */
    public static function setNestedValue(array &$array, string $key, $value): void
    {
        self::put($array, $key, $value);
    }

    /** Persist all plugin settings to the database. */
    public static function updateAll(array $settings): bool
    {
        return update_option(PluginOptions::OPTION_NAME, $settings);
    }
}