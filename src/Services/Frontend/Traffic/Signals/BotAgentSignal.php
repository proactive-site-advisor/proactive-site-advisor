<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BotAgentSignal
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class BotAgentSignal implements BotSignalInterface
{
    /**
     * @var array|null
     */
    private static ?array $customPatterns = null;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
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

        if (self::isHeadless()) {
            return true;
        }

        return false;
    }

    /**
     * Returns detected bot name.
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
     * Matches bot name from User-Agent.
     *
     * @return string|null
     */
    private static function matchBotName(): ?string
    {
        $ua = HeaderReader::getUserAgent();

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
     * Fallback detection using keyword list.
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
         * Filter bot fallback keywords.
         *
         * @param string[] $keywords
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
     * Matches custom bot patterns.
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
     * Checks if User-Agent is empty.
     *
     * @return bool
     */
    private static function hasEmptyUserAgent(): bool
    {
        return HeaderReader::getUserAgent() === '';
    }

    /**
     * Checks for suspicious typos in User-Agent.
     *
     * @return bool
     */
    private static function hasSuspiciousTypos(): bool
    {
        $ua = HeaderReader::getUserAgent();

        $typos = [
            'Mozlila', 'Bulid', 'Moblie', 'Appel', 'Windwos',
            'Andriod', 'Safri', 'Chrmoe', 'Gogle'
        ];

        /**
         * Filter suspicious User-Agent typos.
         *
         * @param string[] $typos
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
     * Checks for headless browser indicators.
     *
     * @return bool
     */
    private static function isHeadless(): bool
    {
        $ua = HeaderReader::getUserAgent();

        $patterns = [
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
         * Filter headless browser patterns.
         *
         * @param string[] $patterns
         */
        $patterns = apply_filters('proactive_site_advisor_headless_patterns', $patterns);

        if (!is_array($patterns)) {
            $patterns = [];
        }

        foreach ($patterns as $pattern) {
            if (stripos($ua, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizes bot name.
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
     * Returns custom bot patterns from filter.
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
         * @param string[] $patterns
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