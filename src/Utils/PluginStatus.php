<?php

namespace ProactiveSiteAdvisor\Utils;

use ProactiveSiteAdvisor\Config\PluginMeta;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility class for determining the current plugin/monitoring status.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class PluginStatus
{
    /** Status constants */
    public const STATUS_FRESH   = 'fresh';
    public const STATUS_LIMITED = 'limited';
    public const STATUS_NORMAL  = 'normal';
    public const STATUS_ISSUE   = 'issue';

    /** Baseline period in days. */
    public const BASELINE_DAYS = 7;

    /** Hours threshold for issue status. */
    public const ISSUE_THRESHOLD_HOURS = 24;

    /** Get the current monitoring status. */
    public static function getStatus(int $days): string
    {
        if (self::isFreshInstall()) {
            return self::STATUS_FRESH;
        }

        $lastRun = self::getLastRunTimestamp();
        if ($lastRun !== null) {
            $hoursSinceLastRun = (DateTimeUtils::timestamp() - $lastRun) / 3600;
            if ($hoursSinceLastRun > self::ISSUE_THRESHOLD_HOURS) {
                return self::STATUS_ISSUE;
            }
        }

        if (!self::isBaselineComplete($days)) {
            return self::STATUS_LIMITED;
        }

        return self::STATUS_NORMAL;
    }

    /** Check if this is a fresh install (no cron has run yet). */
    public static function isFreshInstall(): bool
    {
        return self::getLastRunTimestamp() === null;
    }

    /** Check if baseline period is complete (7+ days of data). */
    public static function isBaselineComplete(int $days): bool
    {
        return $days >= self::BASELINE_DAYS;
    }

    /** Get the timestamp of the last cron run. */
    public static function getLastRunTimestamp(): ?int
    {
        $timestamp = OptionUtils::getMeta(PluginMeta::LAST_DAILY_RUN);

        if ($timestamp === null || $timestamp === false || $timestamp === '') {
            return null;
        }

        return (int)$timestamp;
    }

    /** Check if data collection has started (at least one cron has run). */
    public static function hasStartedCollecting(): bool
    {
        return !self::isFreshInstall();
    }
}