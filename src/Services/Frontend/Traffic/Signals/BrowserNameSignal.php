<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BrowserNameSignal
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class BrowserNameSignal implements BotSignalInterface, ScoreSignalInterface
{
    /**
     * @var array|null
     */
    private static ?array $allowlist = null;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        $ua = HeaderReader::getUserAgent();

        return $this->hasInvalidBrowserName($ua);
    }

    /**
     * {@inheritDoc}
     */
    public function getScore(): int
    {
        return $this->getPlausibilityScore();
    }

    /**
     * Checks if browser name is invalid.
     *
     * @param string $ua
     * @return bool
     */
    private function hasInvalidBrowserName(string $ua): bool
    {
        $parsed = $this->parseUserAgent($ua);

        return !$this->isValidBrowserName($parsed['browser']);
    }

    /**
     * Validates browser name against allowlist.
     *
     * @param string $browser
     * @return bool
     */
    private function isValidBrowserName(string $browser): bool
    {
        if ($browser === '') {
            return false;
        }

        $allowlist = $this->getAllowlist();

        return in_array($browser, $allowlist, true);
    }

    /**
     * Parses User-Agent string.
     *
     * @param string $ua
     * @return array{browser: string, version: string, platform: string}
     */
    private function parseUserAgent(string $ua): array
    {
        $browser  = '';
        $version  = '';
        $platform = '';

        if (preg_match('/(Windows|Macintosh|Android|iPhone|iPad|Linux|Chrome OS|CrOS)/i', $ua, $match)) {
            $platform = $match[1];
        }

        $patterns = [
            '/Edg\/([\d.]+)/i'            => 'Edge',
            '/OPR\/([\d.]+)/i'            => 'Opera',
            '/SamsungBrowser\/([\d.]+)/i' => 'SamsungBrowser',
            '/HeadlessChrome\/([\d.]+)/i' => 'HeadlessChrome',
            '/CriOS\/([\d.]+)/i'          => 'Chrome iOS',
            '/FxiOS\/([\d.]+)/i'          => 'Firefox iOS',
            '/Firefox\/([\d.]+)/i'        => 'Firefox',
            '/Chrome\/([\d.]+)/i'         => 'Chrome',
            '/Safari\/([\d.]+)/i'         => 'Safari',
        ];

        foreach ($patterns as $pattern => $name) {
            if (preg_match($pattern, $ua, $match)) {
                $browser = $name;
                $version = $match[1];
                break;
            }
        }

        return [
            'browser'  => $browser,
            'version'  => $version,
            'platform' => $platform,
        ];
    }

    /**
     * Calculates plausibility score based on browser version.
     *
     * @return int
     */
    private function getPlausibilityScore(): int
    {
        $parsed = $this->parseUserAgent(HeaderReader::getUserAgent());

        if ($parsed['browser'] === '' || $parsed['version'] === '') {
            return 0;
        }

        $majorVersion = (int)explode('.', $parsed['version'])[0];

        $browsers = ['Chrome', 'Edge', 'Opera', 'Firefox'];

        if (!in_array($parsed['browser'], $browsers, true)) {
            return 0;
        }

        if ($majorVersion < 40) {
            return 5;
        }

        if ($majorVersion < 60) {
            return 4;
        }

        if ($majorVersion < 80) {
            return 2;
        }

        return 0;
    }

    /**
     * Returns browser allowlist.
     *
     * @return array
     */
    private function getAllowlist(): array
    {
        if (self::$allowlist !== null) {
            return self::$allowlist;
        }

        self::$allowlist = DataLoader::loadBrowserAllowlist();

        /**
         * Filter browser allowlist.
         *
         * @param string[] $allowlist
         */
        self::$allowlist = apply_filters('proactive_site_advisor_browser_allowlist', self::$allowlist);

        if (!is_array(self::$allowlist)) {
            self::$allowlist = [];
        }

        return self::$allowlist;
    }
}