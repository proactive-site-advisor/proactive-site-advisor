<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes browser fingerprinting signals for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class FingerprintSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Cached suspicion score. */
    private static ?int $score = null;

    /** Cached client hint brands. */
    private static ?array $brands = null;

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if ($this->hasMalformedClientHints()) {
            return true;
        }

        if ($this->hasMissingAllFetchHeaders()) {
            return true;
        }

        if ($this->isScannerClientHints($ua)) {
            return true;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        if (self::$score !== null) {
            return self::$score;
        }

        $score = 0;
        $ua    = HeaderReader::getUserAgent();

        if (!$this->isSafariLike($ua)) {
            if ($this->isModernChromeFamily($ua)) {
                if ($this->hasMissingClientHints()) {
                    $score += 5;
                }

                if ($this->hasInvalidClientHints($ua)) {
                    $score += 5;
                }
            }

            if ($this->hasClientMobileMismatch($ua)) {
                $score += 2;
            }

            if ($this->hasClientPlatformMismatch($ua)) {
                $score += 2;
            }
        }

        $score += $this->getMissingBrowserHeadersScore();

        if ($this->hasNavigationMismatch()) {
            $score += 3;
        }

        if ($this->hasMissingUserNavigation()) {
            ++$score;
        }

        return self::$score = $score;
    }

    /** Checks if Sec-CH-UA header is malformed. */
    private function hasMalformedClientHints(): bool
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

    /** Checks if all Sec-Fetch-* headers are missing. */
    private function hasMissingAllFetchHeaders(): bool
    {
        return HeaderReader::getSecFetchSite() === ''
            && HeaderReader::getSecFetchMode() === ''
            && HeaderReader::getSecFetchDest() === '';
    }

    /** Checks if the request is a scanner based on client hints + referrer. */
    private function isScannerClientHints(string $ua): bool
    {
        if (!$this->hasInvalidClientHints($ua)) {
            return false;
        }

        if (HeaderReader::getReferer() !== '') {
            return false;
        }

        $site = HeaderReader::getSecFetchSite();

        return !($site !== '' && $site !== 'none');
    }

    /** Checks for modern Chromium browsers. */
    private function isModernChromeFamily(string $ua): bool
    {
        if (!preg_match('/(Chrome|Edg|OPR)\/(\d+)/i', $ua, $match)) {
            return false;
        }

        return (int)$match[2] >= 90;
    }

    /** Checks missing client hints. */
    private function hasMissingClientHints(): bool
    {
        return (
            HeaderReader::getSecChUa() === '' &&
            HeaderReader::getSecChUaPlatform() === '' &&
            HeaderReader::getSecChUaMobile() === ''
        );
    }

    /** Validates Sec-CH-UA brands against UA. */
    private function hasInvalidClientHints(string $ua): bool
    {
        $brands = $this->getClientHintBrands();

        if ($brands === []) {
            return false;
        }

        if (stripos($ua, 'Edg') !== false) {
            return !$this->containsBrand($brands, ['Edge', 'Edg', 'Microsoft Edge']);
        }

        if (stripos($ua, 'OPR') !== false) {
            return !$this->containsBrand($brands, ['Opera', 'OPR']);
        }

        if (stripos($ua, 'Chrome') !== false) {
            return !$this->containsBrand($brands, ['Chrome', 'Chromium']);
        }

        return false;
    }

    /** Extracts Sec-CH-UA brands. */
    private function getClientHintBrands(): array
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

    /** Checks if expected brand exists. */
    private function containsBrand(array $brands, array $expected): bool
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

    /** Checks navigation mismatch. */
    private function hasNavigationMismatch(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();

        if ($mode === '' && $dest === '') {
            return false;
        }

        return !($mode === 'navigate' && $dest === 'document');
    }

    /** Checks missing user navigation. */
    private function hasMissingUserNavigation(): bool
    {
        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();
        $user = HeaderReader::getSecFetchUser();

        if ($mode === 'navigate' && $dest === 'document') {
            return $user !== '?1';
        }

        return false;
    }

    /** Calculates missing browser headers score. */
    private function getMissingBrowserHeadersScore(): int
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

    /** Checks client mobile mismatch. */
    private function hasClientMobileMismatch(string $ua): bool
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
    private function hasClientPlatformMismatch(string $ua): bool
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

        return false;
    }

    /** Detects Safari-like browsers. */
    private function isSafariLike(string $ua): bool
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