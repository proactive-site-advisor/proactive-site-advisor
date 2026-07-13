<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility class for sanitizing various input types.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class Sanitize
{
    /** Sanitize plain text. */
    public static function text($value): string
    {
        return sanitize_text_field(wp_unslash($value));
    }

    /** Sanitize integer value. */
    public static function int($value): int
    {
        return absint($value);
    }

    /** Sanitize float value. */
    public static function float($value): float
    {
        return (float)$value;
    }

    /** Sanitize boolean value. */
    public static function bool($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** Sanitize email address. */
    public static function email($value): string
    {
        return sanitize_email(wp_unslash($value));
    }

    /** Sanitize textarea content. */
    public static function textarea($value): string
    {
        return sanitize_textarea_field(wp_unslash($value));
    }

    /** Sanitize URL. */
    public static function url($value): string
    {
        return esc_url_raw(wp_unslash($value));
    }

    /** Sanitize array of text values. */
    public static function arrayOfText($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        return array_map(static fn($v) => self::text($v), $array);
    }

    /** Sanitize array of integers. */
    public static function arrayOfInt($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        return array_map(static fn($v) => self::int($v), $array);
    }

    /** Sanitize input array using rules. */
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