<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BotDetector
 *
 * Lightweight bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic
 * @version 1.0.0
 */
class BotDetector
{
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
     * Match bot and return captured name
     *
     * @return string|null
     */
    private static function matchBotName(): ?string
    {
        $ua = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''));

        if ($ua === '') {
            return null;
        }

        static $pattern = null;

        if ($pattern === null) {
            $pattern = require PROACTIVE_SITE_ADVISOR_PATH . 'data/bot-patterns.php';
        }

        if (!is_string($pattern) || $pattern === '') {
            return null;
        }

        $result = preg_match($pattern, $ua, $matches);
        if ($result === 1) {
            return $matches[1] ?? $matches[0] ?? null;
        }

        return self::matchBotNameFallback($ua);
    }

    /**
     * Fallback detection based on common bot keywords.
     * Called only when regex didn't match.
     *
     * @param string $ua User-Agent string
     *
     * @return string|null
     */
    private static function matchBotNameFallback(string $ua): ?string
    {
        $keywords = [
            'python-requests',
            'go-http-client',
            'mediapartners',
            'crawler',
            'spider',
            'scanner',
            'validator',
            'checkerbot',
            'monitorbot',
            'headless',
            'slurp',
            'crawl',
            'curl',
            'wget',
            'python',
            'okhttp',
            'libwww',
            'perl',
            'java',
            'bot',
        ];

        foreach ($keywords as $kw) {
            if (stripos($ua, $kw) !== false) {
                return $kw;
            }
        }

        return null;
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
        $ua = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''));

        return $ua === '';
    }

    /**
     * Check if the User-Agent indicates a headless browser or automation tool.
     * This is a specific pattern check beyond the general bot detection.
     *
     * @return bool
     */
    public static function isHeadless(): bool
    {
        $ua = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''));

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
        ];

        foreach ($headlessPatterns as $pattern) {
            if (stripos($ua, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}