<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BrowserFingerprintSignal
 *
 * Scores browser fingerprint inconsistencies.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class BrowserFingerprintSignal
{
    /**
     * Runtime cached score.
     *
     * @var int|null
     */
    private static ?int $score = null;

    /**
     * Cached client hint brands.
     *
     * @var array|null
     */
    private static ?array $brands = null;

    /**
     * Calculate browser fingerprint suspicion score.
     *
     * @return int
     */
    public static function getScore(): int
    {
        if (self::$score !== null) {
            return self::$score;
        }

        $score = 0;

        $ua = HeaderReader::getUserAgent();

        if ($ua === '') {
            return self::$score = 3;
        }

        if (self::hasMissingAllFetchHeaders()) {
            $score += 3;
        }

        if (!self::isSafariLike($ua)) {

            if (self::isModernChromeFamily($ua)) {

                if (self::hasMissingClientHints()) {
                    $score += 2;
                }

                if (self::hasInvalidClientHints($ua)) {
                    $score += 3;
                }
            }

            if (self::hasClientMobileMismatch($ua)) {
                ++$score;
            }

            if (self::hasClientPlatformMismatch($ua)) {
                ++$score;
            }
        }

        $score += self::getMissingBrowserHeadersScore();

        if (self::hasNavigationMismatch()) {
            $score += 2;
        }

        if (self::hasMissingUserNavigation()) {
            ++$score;
        }

        return self::$score = $score;
    }

    /**
     * Check if Sec-CH-UA header is present but malformed.
     * Real browsers always send properly formatted strings.
     *
     * @return bool
     */
    public static function hasMalformedClientHints(): bool
    {
        $header = HeaderReader::getSecChUa();
        if ($header === '') {
            return false;
        }

        return preg_match(
                '/^"([^"]+)";v="\d+"(,\s*"([^"]+)";v="\d+")*$/i',
                $header
            ) !== 1;
    }

    /**
     * Check if all Sec-Fetch-* headers are empty.
     *
     * A real browser always sends at least one of them on navigation.
     *
     * @return bool
     */
    private static function hasMissingAllFetchHeaders(): bool
    {
        return HeaderReader::getSecFetchSite() === ''
            && HeaderReader::getSecFetchMode() === ''
            && HeaderReader::getSecFetchDest() === '';
    }

    /**
     * Check modern Chromium browsers.
     *
     * @param string $ua
     * @return bool
     */
    private static function isModernChromeFamily(string $ua): bool
    {
        if (!preg_match('/(Chrome|Edg|OPR)\/(\d+)/i', $ua, $match)) {
            return false;
        }

        return (int)$match[2] >= 90;
    }

    /**
     * Check missing client hints.
     *
     * @return bool
     */
    private static function hasMissingClientHints(): bool
    {
        return (
            HeaderReader::getSecChUa() === '' &&
            HeaderReader::getSecChUaPlatform() === '' &&
            HeaderReader::getSecChUaMobile() === ''
        );
    }

    /**
     * Validate Sec-CH-UA brands.
     *
     * @param string $ua
     * @return bool
     */
    private static function hasInvalidClientHints(string $ua): bool
    {
        $brands = self::getClientHintBrands();

        if ($brands === []) {
            return false;
        }

        if (stripos($ua, 'Edg') !== false) {
            return !self::containsBrand(
                $brands,
                [
                    'Edge',
                    'Edg',
                    'Microsoft Edge'
                ]
            );
        }

        if (stripos($ua, 'OPR') !== false) {
            return !self::containsBrand(
                $brands,
                [
                    'Opera',
                    'OPR'
                ]
            );
        }

        if (stripos($ua, 'Chrome') !== false) {
            return !self::containsBrand(
                $brands,
                [
                    'Chrome',
                    'Chromium'
                ]
            );
        }

        return false;
    }

    /**
     * Extract Sec-CH-UA brands.
     *
     * @return string[]
     */
    private static function getClientHintBrands(): array
    {
        if (self::$brands !== null) {
            return self::$brands;
        }

        $header = HeaderReader::getSecChUa();

        if ($header === '') {
            return self::$brands = [];
        }

        preg_match_all(
            '/"([^"]+)";v="\d+"/',
            $header,
            $matches
        );

        return self::$brands = ($matches[1] ?? []);
    }

    /**
     * Check expected brand exists.
     *
     * @param array $brands
     * @param array $expected
     * @return bool
     */
    private static function containsBrand(
        array $brands,
        array $expected
    ): bool
    {
        foreach ($brands as $brand) {
            foreach ($expected as $item) {
                if (stripos($brand, $item) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check navigation fingerprint.
     *
     * @return bool
     */
    private static function hasNavigationMismatch(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();

        if ($mode === '' && $dest === '') {
            return false;
        }

        return !(
            $mode === 'navigate' &&
            $dest === 'document'
        );
    }

    /**
     * Check user initiated navigation.
     *
     * @return bool
     */
    private static function hasMissingUserNavigation(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();
        $user = HeaderReader::getSecFetchUser();


        if (
            $mode === 'navigate' &&
            $dest === 'document'
        ) {
            return $user !== '?1';
        }


        return false;
    }

    /**
     * Calculate missing browser headers score.
     *
     * @return int
     */
    private static function getMissingBrowserHeadersScore(): int
    {
        $score = 0;


        if (HeaderReader::getAcceptLanguage() === '') {
            ++$score;
        }


        if (HeaderReader::getAcceptEncoding() === '') {
            ++$score;
        }


        return $score;
    }

    /**
     * Check mobile hint mismatch.
     *
     * @param string $ua
     * @return bool
     */
    private static function hasClientMobileMismatch(string $ua): bool
    {
        $mobile = HeaderReader::getSecChUaMobile();

        if ($mobile === '') {
            return false;
        }

        if (
            stripos($ua, 'iPhone') !== false ||
            stripos($ua, 'Android') !== false
        ) {
            return $mobile !== '?1';
        }

        if (
            stripos($ua, 'Windows') !== false ||
            stripos($ua, 'Macintosh') !== false ||
            stripos($ua, 'Linux') !== false
        ) {
            return $mobile === '?1';
        }

        return false;
    }

    /**
     * Check platform mismatch.
     *
     * @param string $ua
     * @return bool
     */
    private static function hasClientPlatformMismatch(string $ua): bool
    {
        $platform = HeaderReader::getSecChUaPlatform();

        if ($platform === '') {
            return false;
        }

        if (
            stripos($ua, 'Windows') !== false &&
            stripos($platform, 'Windows') === false
        ) {
            return true;
        }

        if (
            stripos($ua, 'Macintosh') !== false &&
            stripos($platform, 'Mac') === false
        ) {
            return true;
        }

        if (
            stripos($ua, 'Android') !== false &&
            stripos($platform, 'Android') === false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Detect Safari-like browsers.
     *
     * @param string $ua
     * @return bool
     */
    private static function isSafariLike(string $ua): bool
    {
        if (stripos($ua, 'FxiOS') !== false) {
            return true;
        }

        return (
            stripos($ua, 'Safari') !== false &&
            stripos($ua, 'Chrome') === false &&
            stripos($ua, 'CriOS') === false &&
            stripos($ua, 'Edg') === false
        );
    }
}