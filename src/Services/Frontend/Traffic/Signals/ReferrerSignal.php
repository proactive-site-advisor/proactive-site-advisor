<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ReferrerSignal
 *
 * Detects suspicious referrer patterns.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class ReferrerSignal
{
    /**
     * Cached spam list
     *
     * @var array|null
     */
    private static ?array $spamList = null;

    /**
     * Check if referrer is spam
     *
     * @param string $referrerUrl
     * @param string $currentHost
     * @return bool
     */
    public static function isSpamReferrer(string $referrerUrl, string $currentHost): bool
    {
        $referrerHost = self::extractHost($referrerUrl);

        if ($referrerHost === '') {
            return false;
        }

        if (self::isSelfReferrer($referrerHost, $currentHost)) {
            return false;
        }

        $spamList = self::getSpamList();

        return in_array($referrerHost, $spamList, true);
    }

    /**
     * Check if referrer is from the same domain
     *
     * @param string $referrerHost
     * @param string $currentHost
     * @return bool
     */
    public static function isSelfReferrer(string $referrerHost, string $currentHost): bool
    {
        if ($referrerHost === '' || $currentHost === '') {
            return false;
        }

        $referrerHost = preg_replace('/^www\./i', '', $referrerHost);
        $currentHost  = preg_replace('/^www\./i', '', $currentHost);

        return str_starts_with($referrerHost, $currentHost);
    }

    /**
     * Extract host from URL
     *
     * @param string $url
     * @return string
     */
    public static function extractHost(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $parsed = wp_parse_url($url);

        if ($parsed === false || !isset($parsed['host'])) {
            return '';
        }

        return strtolower($parsed['host']);
    }

    /**
     * Get spam list from data file
     *
     * @return array
     */
    private static function getSpamList(): array
    {
        if (self::$spamList !== null) {
            return self::$spamList;
        }

        self::$spamList = DataLoader::loadReferrerSpamList();

        /**
         * Filter the list of spam referrer hosts.
         *
         * Hosts in this list will be flagged as spam and excluded from tracking.
         *
         * @param string[] $spamList Array of hostnames (e.g., 'example.com').
         */
        self::$spamList = apply_filters('proactive_site_advisor_referrer_spam_list', self::$spamList);

        return self::$spamList;
    }
}