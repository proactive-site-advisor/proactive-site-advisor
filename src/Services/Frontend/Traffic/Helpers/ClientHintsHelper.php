<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper for analyzing Client Hints headers.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers
 * @since   1.0.0
 */
class ClientHintsHelper
{
    /** Cached client hint brands. */
    private static ?array $brands = null;

    /** Returns extracted Sec-CH-UA brands. */
    public static function getBrands(): array
    {
        if (self::$brands !== null) {
            return self::$brands;
        }

        $header = HeaderReader::getSecChUa();

        if ($header === '') {
            return self::$brands = [];
        }

        preg_match_all(
            '/"([^"]+)";v="\d+(?:\.\d+)*"/',
            $header,
            $matches
        );

        return self::$brands = ($matches[1] ?? []);
    }

    /** Checks if the expected brand exists in the list. */
    public static function containsBrand(array $expected): bool
    {
        $brands = self::getBrands();

        foreach ($brands as $brand) {
            foreach ($expected as $item) {
                if (stripos($brand, $item) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Validates Sec-CH-UA brands against User-Agent. */
    public static function hasInvalidClientHints(string $ua): bool
    {
        $brands = self::getBrands();

        if ($brands === []) {
            return false;
        }

        if (stripos($ua, 'Edg') !== false) {
            return !self::containsBrand(['Edge', 'Edg', 'Microsoft Edge']);
        }

        if (stripos($ua, 'OPR') !== false) {
            return !self::containsBrand(['Opera', 'OPR']);
        }

        if (stripos($ua, 'Chrome') !== false) {
            return !self::containsBrand(['Chrome', 'Chromium']);
        }

        return false;
    }

    /** Checks if the browser version in User-Agent contradicts Sec-CH-UA. */
    public static function hasVersionMismatch(string $ua, int $maxDifference = 5): bool
    {
        $parsed = self::parseUserAgentForVersionMismatch($ua);

        if ($parsed === null) {
            return false;
        }

        $chHeader = HeaderReader::getSecChUa();
        if ($chHeader === '') {
            return false;
        }

        preg_match_all(
            '/"([^"]+)"\s*;\s*v="(\d+(?:\.\d+)?)"/i',
            $chHeader,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $brand   = trim($match[1]);
            $version = (int)$match[2];

            if (
                in_array(
                    strtolower($brand),
                    array_map('strtolower', $parsed['brands']),
                    true
                )
            ) {
                return abs($parsed['version'] - $version) > $maxDifference;
            }
        }

        return false;
    }

    /** Parses browser name and major version from User-Agent. */
    private static function parseUserAgentForVersionMismatch(string $ua): ?array
    {
        $browsers = [
            [
                'pattern' => '/Edg\/(\d+)/i',
                'brands'  => [
                    'Microsoft Edge',
                    'Edge'
                ]
            ],
            [
                'pattern' => '/OPR\/(\d+)/i',
                'brands'  => [
                    'Opera'
                ]
            ],
            [
                'pattern' => '/SamsungBrowser\/(\d+)/i',
                'brands'  => [
                    'Samsung Internet'
                ]
            ],
            [
                'pattern' => '/Firefox\/(\d+)/i',
                'brands'  => [
                    'Firefox'
                ]
            ],
            [
                'pattern' => '/Chrome\/(\d+)/i',
                'brands'  => [
                    'Google Chrome',
                    'Chromium',
                    'Chrome'
                ]
            ],
            [
                'pattern' => '/Version\/(\d+).+Safari/i',
                'brands'  => [
                    'Safari'
                ]
            ],
        ];

        foreach ($browsers as $browser) {
            if (preg_match($browser['pattern'], $ua, $match)) {
                return [
                    'version' => (int)$match[1],
                    'brands'  => $browser['brands'],
                ];
            }
        }

        return null;
    }

    /** Checks if all client hints are missing. */
    public static function hasMissingClientHints(): bool
    {
        return (
            HeaderReader::getSecChUa() === '' &&
            HeaderReader::getSecChUaPlatform() === '' &&
            HeaderReader::getSecChUaMobile() === ''
        );
    }

    /** Checks if client hints are incomplete (some headers present, platform missing). */
    public static function hasIncompleteClientHints(): bool
    {
        $hasUa       = HeaderReader::getSecChUa() !== '';
        $hasMobile   = HeaderReader::getSecChUaMobile() !== '';
        $hasPlatform = HeaderReader::getSecChUaPlatform() !== '';

        if (!$hasUa && !$hasMobile && !$hasPlatform) {
            return false;
        }

        return $hasUa && $hasMobile && !$hasPlatform;
    }

    /** Checks if Sec-CH-UA header is malformed. */
    public static function hasMalformedClientHints(): bool
    {
        $header = HeaderReader::getSecChUa();
        if ($header === '') {
            return false;
        }

        return preg_match(
                '/^"[^"]+";v="\d+(?:\.\d+)*"(?:,\s*"[^"]+";v="\d+(?:\.\d+)*")*$/i',
                $header
            ) !== 1;
    }

    /** Checks client mobile mismatch. */
    public static function hasClientMobileMismatch(string $ua): bool
    {
        $mobile = HeaderReader::getSecChUaMobile();

        if ($mobile === '') {
            return false;
        }

        if (stripos($ua, 'iPhone') !== false || stripos($ua, 'Android') !== false) {
            return $mobile !== '?1';
        }

        if (stripos($ua, 'Windows') !== false || stripos($ua, 'Macintosh') !== false || stripos($ua, 'Linux') !== false) {
            return $mobile === '?1';
        }

        return false;
    }

    /** Checks client platform mismatch. */
    public static function hasClientPlatformMismatch(string $ua): bool
    {
        $platform = HeaderReader::getSecChUaPlatform();

        if ($platform === '') {
            return false;
        }

        if (stripos($ua, 'Windows') !== false && stripos($platform, 'Windows') === false) {
            return true;
        }

        if (stripos($ua, 'Macintosh') !== false && stripos($platform, 'Mac') === false) {
            return true;
        }

        if (stripos($ua, 'Android') !== false && stripos($platform, 'Android') === false) {
            return true;
        }

        if (
            stripos($ua, 'Linux') !== false &&
            stripos($ua, 'Android') === false &&
            stripos($platform, 'Linux') === false
        ) {
            return true;
        }

        if (stripos($ua, 'CrOS') !== false && stripos($platform, 'Chrome') === false) {
            return true;
        }

        if (stripos($platform, 'iOS') === false) {
            if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
                return true;
            }
        }

        return false;
    }
}