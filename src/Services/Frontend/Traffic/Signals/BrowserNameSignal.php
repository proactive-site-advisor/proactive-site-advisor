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
 * Analyzes browser name and version for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class BrowserNameSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Cached browser allowlist. */
    private static ?array $allowlist = null;

    /** Version thresholds for browser plausibility scoring. */
    private const OLD_VERSION_THRESHOLD   = 60;
    private const AGING_VERSION_THRESHOLD = 80;

    /** Score values for different browser age brackets. */
    private const SCORE_AGING_VERSION = 2;

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if ($this->hasInvalidBrowserName($ua)) {
            return true;
        }

        if ($this->hasTooOldBrowserVersion($ua)) {
            return true;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        return $this->getPlausibilityScore();
    }

    /** Checks if browser name is invalid. */
    private function hasInvalidBrowserName(string $ua): bool
    {
        $parsed = $this->parseUserAgent($ua);

        return !$this->isValidBrowserName($parsed['browser']);
    }

    /** Validates browser name against allowlist. */
    private function isValidBrowserName(string $browser): bool
    {
        if ($browser === '') {
            return false;
        }

        $allowlist = $this->getAllowlist();

        return in_array($browser, $allowlist, true);
    }

    /** Parses User-Agent string. */
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

    /** Checks if browser version is too old (score >= 4). */
    private function hasTooOldBrowserVersion(string $ua): bool
    {
        $parsed = $this->parseUserAgent($ua);
        if ($parsed['browser'] === '' || $parsed['version'] === '') {
            return false;
        }

        $majorVersion = (int)explode('.', $parsed['version'])[0];
        $browsers     = ['Chrome', 'Edge', 'Opera', 'Firefox'];

        if (!in_array($parsed['browser'], $browsers, true)) {
            return false;
        }

        return $majorVersion < self::OLD_VERSION_THRESHOLD;
    }

    /** Calculates plausibility score based on browser version. */
    private function getPlausibilityScore(): int
    {
        $parsed = $this->parseUserAgent(HeaderReader::getUserAgent());

        if ($parsed['browser'] === '' || $parsed['version'] === '') {
            return 0;
        }

        $majorVersion = (int)explode('.', $parsed['version'])[0];
        $browsers     = ['Chrome', 'Edge', 'Opera', 'Firefox'];

        if (!in_array($parsed['browser'], $browsers, true)) {
            return 0;
        }

        if ($majorVersion < self::AGING_VERSION_THRESHOLD) {
            return self::SCORE_AGING_VERSION;
        }

        return 0;
    }

    /** Returns browser allowlist. */
    private function getAllowlist(): array
    {
        if (self::$allowlist !== null) {
            return self::$allowlist;
        }

        self::$allowlist = DataLoader::loadBrowserAllowlist();

        /**
         * Filters browser allowlist.
         *
         * @param string[] $allowlist
         * @since  1.0.0
         */
        self::$allowlist = apply_filters('proactive_site_advisor_browser_allowlist', self::$allowlist);

        if (!is_array(self::$allowlist)) {
            self::$allowlist = [];
        }

        return self::$allowlist;
    }
}