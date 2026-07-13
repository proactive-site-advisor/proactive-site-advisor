<?php

namespace ProactiveSiteAdvisor\Utils;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility class for handling date and time operations with WordPress timezone support.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class DateTimeUtils
{
    /** Common date format constants. */
    public const FORMAT_DATE     = 'Y-m-d';
    public const FORMAT_TIME     = 'H:i:s';
    public const FORMAT_DATETIME = 'Y-m-d H:i:s';
    public const FORMAT_ISO8601  = 'c';
    public const FORMAT_KEY      = 'Ymd';

    /** Get today's date in the WordPress timezone. */
    public static function today(string $format = self::FORMAT_DATE, bool $utc = true): string
    {
        return self::now($format, $utc);
    }

    /** Get today's date as a key string (Ymd format). */
    public static function todayKey(): string
    {
        return self::today(self::FORMAT_KEY, false);
    }

    /** Get the current date and time in the WordPress timezone. */
    public static function now(string $format = self::FORMAT_DATETIME, bool $utc = true): string
    {
        if ($utc) {
            return gmdate($format);
        }

        return current_datetime()->format($format);
    }

    /** Get the current timestamp adjusted for WordPress timezone. */
    public static function timestamp(): int
    {
        return current_datetime()->getTimestamp();
    }

    /** Get the current date and time as a DateTimeImmutable object in the WordPress timezone. */
    public static function current(): DateTimeImmutable
    {
        return current_datetime();
    }

    /** Get the WordPress timezone as a DateTimeZone object. */
    public static function getTimezone(): DateTimeZone
    {
        return wp_timezone();
    }

    /** Get the WordPress timezone string. */
    public static function getTimezoneString(): string
    {
        return wp_timezone_string();
    }

    /** Format a date/time value to the WordPress timezone. */
    public static function format($date, string $format = self::FORMAT_DATETIME)
    {
        $datetime = self::createDateTime($date);

        if (!$datetime) {
            return false;
        }

        return $datetime->setTimezone(self::getTimezone())->format($format);
    }

    /** Convert a date from one timezone to another. */
    public static function convert($date, string $fromTz, string $toTz, string $format = self::FORMAT_DATETIME)
    {
        try {
            $fromTimezone = new DateTimeZone($fromTz);
            $toTimezone   = $toTz === 'wp' ? self::getTimezone() : new DateTimeZone($toTz);

            $datetime = self::createDateTime($date, $fromTimezone);

            if (!$datetime) {
                return false;
            }

            return $datetime->setTimezone($toTimezone)->format($format);
        } catch (Exception $e) {
            return false;
        }
    }

    /** Convert a UTC date/time to the WordPress timezone. */
    public static function utcToLocal($date, string $format = self::FORMAT_DATETIME)
    {
        return self::convert($date, 'UTC', 'wp', $format);
    }

    /** Convert a WordPress local date/time to UTC. */
    public static function localToUtc($date, string $format = self::FORMAT_DATETIME)
    {
        return self::convert($date, self::getTimezoneString(), 'UTC', $format);
    }

    /** Create a DateTimeImmutable object from various input types. */
    private static function createDateTime($date, ?DateTimeZone $timezone = null)
    {
        try {
            if ($date instanceof DateTimeImmutable) {
                return $date;
            }

            if (is_int($date) || (is_string($date) && ctype_digit($date))) {
                $datetime = new DateTimeImmutable('@' . (int)$date);
                return $timezone ? $datetime->setTimezone($timezone) : $datetime;
            }

            if (is_string($date)) {
                return new DateTimeImmutable($date, $timezone);
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}