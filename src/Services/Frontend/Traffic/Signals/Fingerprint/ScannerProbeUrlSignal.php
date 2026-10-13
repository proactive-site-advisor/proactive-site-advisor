<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects bots using known scanner probe URL patterns.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.2.7
 */
class ScannerProbeUrlSignal implements BotSignalInterface
{
    /**
     * Cached, validated scanner probe patterns.
     *
     * @var string[]|null
     */
    private static ?array $patterns = null;

    /**
     * Determines whether the current request matches a scanner probe pattern.
     *
     * @return bool
     */
    public function isBot(): bool
    {
        if (is_user_logged_in()) {
            return false;
        }

        $uri = HeaderReader::getRequestUri();

        if ($uri === '') {
            return false;
        }

        $path = strtok($uri, '?');

        if ($path === false || $path === '') {
            return false;
        }

        $path = trim($path, '/');

        if ($path === '') {
            return false;
        }

        foreach ($this->getPatterns() as $pattern) {
            $result = preg_match('#' . $pattern . '#i', $path);

            if ($result === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns validated scanner probe patterns.
     *
     * Patterns are provided by the data loader and may be modified
     * through the proactive_site_advisor_scanner_probe_patterns filter.
     *
     * @return string[]
     */
    private function getPatterns(): array
    {
        if (self::$patterns !== null) {
            return self::$patterns;
        }

        $patterns = DataLoader::loadScannerProbePatterns();

        /**
         * Filters scanner probe patterns.
         *
         * Each item must be a valid regular expression body that can
         * be safely delimited using # and the case-insensitive modifier.
         *
         * @param string[] $patterns Scanner probe pattern expressions.
         * @since 1.2.7
         */
        $patterns = apply_filters(
            'proactive_site_advisor_scanner_probe_patterns',
            $patterns
        );

        if (!is_array($patterns)) {
            self::$patterns = [];

            return self::$patterns;
        }

        $validated = [];

        foreach ($patterns as $pattern) {
            if (!is_string($pattern)) {
                continue;
            }

            $pattern = trim($pattern);

            if ($pattern === '') {
                continue;
            }

            if (@preg_match('#' . $pattern . '#i', '') === false) {
                continue;
            }

            $validated[] = $pattern;
        }

        self::$patterns = array_values(array_unique($validated));

        return self::$patterns;
    }
}