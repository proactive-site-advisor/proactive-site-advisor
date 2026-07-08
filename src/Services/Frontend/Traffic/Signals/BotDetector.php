<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BotDetector
 *
 * Lightweight bot detection based on User-Agent.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class BotDetector
{
    /**
     * Custom block patterns (regex or string)
     *
     * @var array|null
     */
    private static ?array $customPatterns = null;

    /**
     * Check if request is bot
     *
     * @return bool
     */
    public static function isBot(): bool
    {
        if (self::hasEmptyUserAgent()) {
            return true;
        }

        if (self::matchBotName() !== null) {
            return true;
        }

        if (self::hasSuspiciousTypos()) {
            return true;
        }

        return false;
    }

    /**
     * Get bot name (best effort)
     *
     * @return string|null
     */
    public static function getBotName(): ?string
    {
        $name = self::matchBotName();

        if ($name !== null) {
            return self::normalizeBotName($name);
        }

        if (self::hasEmptyUserAgent()) {
            return 'unknown';
        }

        return null;
    }

    /**
     * Match bot and return captured name.
     *
     * @return string|null
     */
    private static function matchBotName(): ?string
    {
        $ua = HeaderReader::getUserAgent();

        if ($ua === '') {
            return null;
        }

        static $pattern = null;

        if ($pattern === null) {
            $pattern = DataLoader::loadBotPatterns();
        }

        if (is_string($pattern) && $pattern !== '') {
            $result = preg_match($pattern, $ua, $matches);

            if ($result === 1) {
                return $matches[1] ?? $matches[0] ?? null;
            }
        }

        $custom = self::matchCustomPattern($ua);

        return $custom ?? self::matchBotNameFallback($ua);
    }

    /**
     * Fallback detection based on common bot keywords.
     *
     * @param string $ua
     * @return string|null
     */
    private static function matchBotNameFallback(string $ua): ?string
    {
        $keywords = [
            'python-requests', 'go-http-client', 'mediapartners',
            'crawler', 'spider', 'scanner', 'validator', 'checkerbot',
            'monitorbot', 'headless', 'slurp', 'crawl', 'curl', 'wget',
            'python', 'okhttp', 'libwww', 'perl', 'java', 'scraper',
            'harvest', 'fetcher', 'extractor', 'grabber', 'PHP/',
            'Apache-HttpClient', 'axios', 'node-fetch',
            'HttpClient', 'python-httpx', 'bot',
        ];

        /**
         * Filter the list of bot fallback keywords.
         *
         * Used when the main regex pattern does not match, as a secondary detection.
         *
         * @param string[] $keywords List of keywords to search for in User-Agent string.
         */
        $keywords = apply_filters('proactive_site_advisor_bot_fallback_keywords', $keywords);

        if (!is_array($keywords)) {
            $keywords = [];
        }

        foreach ($keywords as $kw) {
            if (stripos($ua, $kw) !== false) {
                return $kw;
            }
        }

        return null;
    }

    /**
     * Check for common typos in User-Agent that indicate a bot.
     *
     * @return bool
     */
    public static function hasSuspiciousTypos(): bool
    {
        $ua = HeaderReader::getUserAgent();
        if ($ua === '') {
            return false;
        }

        $typos = [
            'Mozlila', 'Bulid', 'Moblie', 'Appel', 'Windwos',
            'Andriod', 'Safri', 'Chrmoe', 'Gogle'
        ];

        /**
         * Filter the list of suspicious User‑Agent typos.
         *
         * @param string[] $typos Array of typo strings.
         */
        $typos = apply_filters('proactive_site_advisor_suspicious_ua_typos', $typos);

        if (!is_array($typos)) {
            $typos = [];
        }

        foreach ($typos as $typo) {
            if (stripos($ua, $typo) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Normalize bot name for consistent output
     *
     * @param string $name
     * @return string
     */
    private static function normalizeBotName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[\s\-_.]+/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        if (function_exists('mb_strlen')) {
            if (mb_strlen($name) > 60) {
                $name = mb_substr($name, 0, 60);
            }
        } elseif (strlen($name) > 60) {
            $name = substr($name, 0, 60);
        }

        return $name !== '' ? $name : 'unknown';
    }

    /**
     * Check empty UA
     *
     * @return bool
     */
    private static function hasEmptyUserAgent(): bool
    {
        return HeaderReader::getUserAgent() === '';
    }

    /**
     * Check if the User-Agent indicates a headless browser or automation tool.
     *
     * @return bool
     */
    public static function isHeadless(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if ($ua === '') {
            return false;
        }

        $headlessPatterns = [
            'HeadlessChrome',
            'PhantomJS',
            'Puppeteer',
            'Selenium',
            'Playwright',
            'Headless',
            'Electron',
            'CefSharp',
            'QtWebEngine',
            'NW.js',
        ];

        /**
         * Filter the list of headless browser patterns.
         *
         * @param string[] $headlessPatterns Array of strings to look for in the User-Agent.
         */
        $headlessPatterns = apply_filters(
            'proactive_site_advisor_headless_patterns',
            $headlessPatterns
        );

        if (!is_array($headlessPatterns)) {
            $headlessPatterns = [];
        }

        foreach ($headlessPatterns as $pattern) {
            if (stripos($ua, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }


    /**
     * Match user agent against custom patterns.
     *
     * @param string $ua
     * @return string|null
     */
    private static function matchCustomPattern(string $ua): ?string
    {
        foreach (self::getCustomPatterns() as $pattern) {
            if (preg_match('/^\/.*\/[imsxu]*$/', $pattern)) {
                if (@preg_match($pattern, $ua) === 1) {
                    return $pattern;
                }

                continue;
            }

            if (stripos($ua, $pattern) !== false) {
                return $pattern;
            }
        }

        return null;
    }

    /**
     * Get custom bot patterns from WordPress filter.
     *
     * @return string[]
     */
    private static function getCustomPatterns(): array
    {
        if (self::$customPatterns !== null) {
            return self::$customPatterns;
        }

        /**
         * Filter custom bot patterns.
         *
         * @param string[] $patterns Array of regex or string patterns.
         */
        $patterns = apply_filters('proactive_site_advisor_custom_bot_patterns', []);

        if (!is_array($patterns)) {
            $patterns = [];
        }

        self::$customPatterns = array_values(array_filter(
            array_map('trim', $patterns),
            static fn(string $pattern): bool => $pattern !== ''
        ));

        return self::$customPatterns;
    }
}