<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects bots based on referrer spam analysis.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class ReferrerSignal implements BotSignalInterface
{
    /** Cached referrer spam list. */
    private static ?array $spamList = null;

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        $referrerUrl = HeaderReader::getReferer();
        $currentHost = HeaderReader::getHost();

        return $this->isSpamReferrer($referrerUrl, $currentHost);
    }

    /** Checks if referrer is spam. */
    private function isSpamReferrer(string $referrerUrl, string $currentHost): bool
    {
        $referrerHost = $this->extractHost($referrerUrl);

        if ($referrerHost === '') {
            return false;
        }

        if ($this->isSelfReferrer($referrerHost, $currentHost)) {
            return false;
        }

        $spamList = $this->getSpamList();

        return in_array($referrerHost, $spamList, true);
    }

    /** Checks if referrer is from the same domain. */
    private function isSelfReferrer(string $referrerHost, string $currentHost): bool
    {
        if ($referrerHost === '' || $currentHost === '') {
            return false;
        }

        $referrerHost = strtolower(preg_replace('/^www\./i', '', $referrerHost));
        $currentHost  = strtolower(preg_replace('/^www\./i', '', $currentHost));

        return $referrerHost === $currentHost || str_ends_with($referrerHost, '.' . $currentHost);
    }

    /** Extracts host from URL. */
    private function extractHost(string $url): string
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

    /** Returns spam list from data file. */
    private function getSpamList(): array
    {
        if (self::$spamList !== null) {
            return self::$spamList;
        }

        self::$spamList = DataLoader::loadReferrerSpamList();

        /**
         * Filters referrer spam list.
         *
         * @param string[] $spamList
         * @since  1.0.0
         */
        self::$spamList = apply_filters('proactive_site_advisor_referrer_spam_list', self::$spamList);

        if (!is_array(self::$spamList)) {
            self::$spamList = [];
        }

        return self::$spamList;
    }
}