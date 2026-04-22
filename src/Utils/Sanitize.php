<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Sanitize
 *
 * Utility class for sanitizing various input types.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.0
 */
class Sanitize
{
    /**
     * Sanitize plain text.
     *
     * @param $value
     * @return string
     */
    public static function text($value): string
    {
        return sanitize_text_field(wp_unslash($value));
    }

    /**
     * Sanitize integer value.
     *
     * @param $value
     * @return int
     */
    public static function int($value): int
    {
        return absint($value);
    }

    /**
     * Sanitize float value.
     *
     * @param $value
     * @return float
     */
    public static function float($value): float
    {
        return (float)$value;
    }

    /**
     * Sanitize boolean value.
     *
     * @param $value
     * @return bool
     */
    public static function bool($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sanitize email address.
     *
     * @param $value
     * @return string
     */
    public static function email($value): string
    {
        return sanitize_email(wp_unslash($value));
    }

    /**
     * Sanitize textarea content.
     *
     * @param $value
     * @return string
     */
    public static function textarea($value): string
    {
        return sanitize_textarea_field(wp_unslash($value));
    }

    /**
     * Sanitize URL.
     *
     * @param $value
     * @return string
     */
    public static function url($value): string
    {
        return esc_url_raw(wp_unslash($value));
    }

    /**
     * Sanitize array of text values.
     *
     * @param $array
     * @return array
     */
    public static function arrayOfText($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        return array_map(static fn($v) => self::text($v), $array);
    }

    /**
     * Sanitize array of integers.
     *
     * @param $array
     * @return array
     */
    public static function arrayOfInt($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        return array_map(static fn($v) => self::int($v), $array);
    }

    /**
     * Sanitize input array using rules.
     *
     * @param array $input
     * @param array $rules
     * @return array
     */
    public static function map(array $input, array $rules): array
    {
        $clean = [];

        foreach ($rules as $field => $type) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $value = $input[$field];

            switch ($type) {
                case 'int':
                    $clean[$field] = self::int($value);
                    break;

                case 'bool':
                    $clean[$field] = self::bool($value);
                    break;

                case 'float':
                    $clean[$field] = self::float($value);
                    break;

                case 'email':
                    $clean[$field] = self::email($value);
                    break;

                case 'url':
                    $clean[$field] = self::url($value);
                    break;

                case 'textarea':
                    $clean[$field] = self::textarea($value);
                    break;

                case 'text':
                default:
                    $clean[$field] = self::text($value);
                    break;
            }
        }

        return $clean;
    }
}