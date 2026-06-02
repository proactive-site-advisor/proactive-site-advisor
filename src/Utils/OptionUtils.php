<?php

namespace ProactiveSiteAdvisor\Utils;

use ProactiveSiteAdvisor\Config\PluginOptions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class OptionUtils
 *
 * A helper class to manage plugin options and user meta in WordPress.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.0
 */
class OptionUtils
{
    /**
     * Get full option key with prefix
     *
     * @param string $key
     * @return string
     */
    public static function getMetaOptionName(string $key): string
    {
        return PluginOptions::META_PREFIX . $key;
    }

    /**
     * Get default plugin options
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return [];
    }

    /**
     * Get all plugin options (merged with defaults)
     *
     * @return array
     */
    public static function getAllOptions(): array
    {
        $options = get_option(PluginOptions::OPTION_NAME);

        if (!is_array($options)) {
            return self::getDefaults();
        }

        return $options;
    }

    /**
     * Get a single plugin option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
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

    /**
     * Set/update a single plugin option
     *
     * @param string $key
     * @param mixed $value
     */
    public static function setOption(string $key, $value): void
    {
        $options = self::getAllOptions();

        self::put($options, $key, $value);

        self::updateAll($options);
    }

    /**
     * Delete a single plugin option
     *
     * @param string $key
     */
    public static function deleteOption(string $key): void
    {
        $options = get_option(PluginOptions::OPTION_NAME, []);

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

    /**
     * Reset all plugin options to defaults
     */
    public static function resetOptions(): void
    {
        update_option(PluginOptions::OPTION_NAME, self::getDefaults());
    }

    /**
     * Get a single user-specific option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getUserOption(string $key, $default = null)
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return $default;
        }

        $options = get_user_meta($userId, PluginOptions::OPTION_NAME, true) ?: [];
        return $options[$key] ?? $default;
    }

    /**
     * Set/update a single user-specific option
     *
     * @param string $key
     * @param mixed $value
     */
    public static function setUserOption(string $key, $value): void
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        $options       = get_user_meta($userId, PluginOptions::OPTION_NAME, true) ?: [];
        $options[$key] = $value;
        update_user_meta($userId, PluginOptions::OPTION_NAME, $options);
    }

    /**
     * Delete a single user-specific option
     *
     * @param string $key
     */
    public static function deleteUserOption(string $key): void
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        $options = get_user_meta($userId, PluginOptions::OPTION_NAME, true) ?: [];
        if (isset($options[$key])) {
            unset($options[$key]);
            update_user_meta($userId, PluginOptions::OPTION_NAME, $options);
        }
    }

    /**
     * Reset all user-specific options
     */
    public static function resetUserOptions(): void
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        update_user_meta($userId, PluginOptions::OPTION_NAME, []);
    }

    /**
     * Get plugin meta option (standalone option)
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getMeta(string $key, $default = null)
    {
        return get_option(self::getMetaOptionName($key), $default);
    }

    /**
     * Set plugin meta option (standalone option)
     *
     * @param string $key
     * @param mixed $value
     * @param bool|null $autoload
     */
    public static function setMeta(string $key, $value, ?bool $autoload = null): void
    {
        update_option(self::getMetaOptionName($key), $value, $autoload);
    }

    /**
     * Delete plugin meta option
     *
     * @param string $key
     */
    public static function deleteMeta(string $key): void
    {
        delete_option(self::getMetaOptionName($key));
    }

    /**
     * Set a value in a nested array using dot notation.
     *
     * @param array $array
     * @param string $key
     * @param $value
     * @return void
     */
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

    /**
     * Get all options for a specific section.
     *
     * @param string $section
     * @return array
     */
    public static function getSection(string $section): array
    {
        return self::getOption($section, []);
    }

    /**
     * Build a dot notation key for a section option
     *
     * @param string $section
     * @param string $key
     * @return string
     */
    public static function makeKey(string $section, string $key): string
    {
        return $section . '.' . $key;
    }

    /**
     * Set a value in a nested array using dot notation.
     *
     * @param array $array
     * @param string $key
     * @param $value
     * @return void
     */
    public static function setNestedValue(array &$array, string $key, $value): void
    {
        self::put($array, $key, $value);
    }

    /**
     * Persist all plugin settings to the database.
     *
     * @param array $settings
     * @return bool
     */
    public static function updateAll(array $settings): bool
    {
        return update_option(PluginOptions::OPTION_NAME, $settings);
    }
}