<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ReferrerSignal
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class ReferrerSignal implements BotSignalInterface
{
    /**
     * @var array|null
     */
    private static ?array $spamList = null;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        $referrerUrl = HeaderReader::getReferer();
        $currentHost = HeaderReader::getHost();

        return $this->isSpamReferrer($referrerUrl, $currentHost);
    }

    /**
     * Checks if referrer is spam.
     *
     * @param string $referrerUrl
     * @param string $currentHost
     * @return bool
     */
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

    /**
     * Checks if referrer is from the same domain.
     *
     * @param string $referrerHost
     * @param string $currentHost
     * @return bool
     */
    private function isSelfReferrer(string $referrerHost, string $currentHost): bool
    {
        if ($referrerHost === '' || $currentHost === '') {
            return false;
        }

        $referrerHost = strtolower(preg_replace('/^www\./i', '', $referrerHost));
        $currentHost  = strtolower(preg_replace('/^www\./i', '', $currentHost));

        return $referrerHost === $currentHost || str_ends_with($referrerHost, '.' . $currentHost);
    }

    /**
     * Extracts host from URL.
     *
     * @param string $url
     * @return string
     */
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

    /**
     * Returns spam list from data file.
     *
     * @return array
     */
    private function getSpamList(): array
    {
        if (self::$spamList !== null) {
            return self::$spamList;
        }

        self::$spamList = DataLoader::loadReferrerSpamList();

        /**
         * Filter referrer spam list.
         *
         * @param string[] $spamList
         */
        self::$spamList = apply_filters('proactive_site_advisor_referrer_spam_list', self::$spamList);

        if (!is_array(self::$spamList)) {
            self::$spamList = [];
        }

        return self::$spamList;
    }
}