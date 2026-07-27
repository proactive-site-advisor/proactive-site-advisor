<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;
use ProactiveSiteAdvisor\Cache\CacheManager;

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
    /** Version threshold for modern Chrome-family browsers. */
    private const MODERN_CHROME_VERSION_THRESHOLD = 90;

    /** Version threshold for modern Firefox browsers. */
    private const MODERN_FIREFOX_VERSION_THRESHOLD = 90;

    /** Maximum allowed difference between User‑Agent and Sec‑CH‑UA major versions. */
    private const MAX_VERSION_MISMATCH = 5;

    /** Cache lifetime in seconds for IP‑based User‑Agent tracking. */
    private const CACHE_TTL = 15;

    /** Default minimum number of distinct User‑Agents to flag as a definite bot. */
    private const MIN_DISTINCT_UAS_FOR_BOT = 4;

    /** Threshold for the number of distinct User‑Agents to add a suspicion score. */
    private const DISTINCT_UA_THRESHOLD = 3;

    /** Score values for individual signals. */
    private const SCORE_VERSION_MISMATCH         = 3;
    private const SCORE_CLIENT_MOBILE_MISMATCH   = 2;
    private const SCORE_CLIENT_PLATFORM_MISMATCH = 2;
    private const SCORE_NAVIGATION_MISMATCH      = 3;
    private const SCORE_DISTINCT_UA              = 2;
    private const SCORE_EXPLICIT_NON_USER        = 3;

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

        if ($this->hasMultipleDistinctUserAgents()) {
            return true;
        }

        if ($this->hasFetchUserForNonModern($ua)) {
            return true;
        }

        if ($this->isModernChromeFamily($ua) && $this->hasMissingClientHints()) {
            return true;
        }

        if ($this->isModernChromeFamily($ua) && $this->hasInvalidClientHints($ua)) {
            return true;
        }

        if ($this->isModernFirefox($ua) && $this->hasMissingClientHints()) {
            return true;
        }

        if ($this->hasSuspiciousAcceptLanguage()) {
            return true;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score = 0;
        $ua    = HeaderReader::getUserAgent();

        if (!$this->isSafariLike($ua)) {
            if ($this->isModernChromeFamily($ua) && $this->hasVersionMismatch($ua)) {
                $score += self::SCORE_VERSION_MISMATCH;
            }

            if ($this->hasClientMobileMismatch($ua)) {
                $score += self::SCORE_CLIENT_MOBILE_MISMATCH;
            }

            if ($this->hasClientPlatformMismatch($ua)) {
                $score += self::SCORE_CLIENT_PLATFORM_MISMATCH;
            }
        }

        $score += $this->getMissingBrowserHeadersScore();

        if ($this->hasNavigationMismatch()) {
            $score += self::SCORE_NAVIGATION_MISMATCH;
        }

        if ($this->getDistinctUserAgentCount() >= self::DISTINCT_UA_THRESHOLD) {
            $score += self::SCORE_DISTINCT_UA;
        }

        if ($this->hasExplicitNonUserNavigation()) {
            $score += self::SCORE_EXPLICIT_NON_USER;
        }

        return $score;
    }

    /** Checks if Sec-CH-UA header is malformed. */
    private function hasMalformedClientHints(): bool
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

        return (int)$match[2] >= self::MODERN_CHROME_VERSION_THRESHOLD;
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
            '/"([^"]+)";v="\d+(?:\.\d+)*"/',
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

    /** Checks if the browser version in User-Agent contradicts Sec-CH-UA. */
    private function hasVersionMismatch(string $ua): bool
    {
        if (!preg_match('/(?:Chrome|Edg|OPR)\/(\d+)/i', $ua, $uaMatch)) {
            return false;
        }

        $uaVersion = (int)$uaMatch[1];

        $chHeader = HeaderReader::getSecChUa();
        if ($chHeader === '') {
            return false;
        }

        if (stripos($ua, 'Edg') !== false) {
            $patterns = [
                '/"(?:Microsoft Edge|Edge|Edg)";v="(\d+)"/i',
                '/"Chromium";v="(\d+)"/i',
            ];
        } elseif (stripos($ua, 'OPR') !== false) {
            $patterns = [
                '/"(?:Opera|OPR)";v="(\d+)"/i',
                '/"Chromium";v="(\d+)"/i',
            ];
        } else {
            $patterns = [
                '/"(?:Google Chrome|Chrome)";v="(\d+)"/i',
                '/"Chromium";v="(\d+)"/i',
            ];
        }

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $chHeader, $chMatch) === 1) {
                $chVersion = (int)$chMatch[1];

                return abs($uaVersion - $chVersion) > self::MAX_VERSION_MISMATCH;
            }
        }

        return false;
    }

    /**
     * Returns the number of distinct normalized User-Agents for this IP within the last 15 seconds.
     */
    private function getDistinctUserAgentCount(): int
    {
        $ip = HeaderReader::getIp();
        if ($ip === '' || $ip === 'unknown') {
            return 0;
        }

        $currentUa = HeaderReader::getUserAgent();
        if ($currentUa === '') {
            return 0;
        }

        $cache    = CacheManager::instance();
        $cacheKey = CacheKeys::ipUserAgents(md5($ip));

        $recentUas = $cache->get($cacheKey);
        if (!is_array($recentUas)) {
            $recentUas = [];
        }

        $normalized = preg_replace('/\s+/', ' ', trim($currentUa));
        $normalized = preg_replace('/AppleWebKit\/[\d.]+/', '', $normalized);
        $normalized = preg_replace('/Safari\/[\d.]+/', '', $normalized);
        $normalized = preg_replace('/Chrome\/[\d.]+/', 'Chrome', $normalized);
        $normalized = preg_replace('/Edg\/[\d.]+/', 'Edg', $normalized);
        $normalized = preg_replace('/OPR\/[\d.]+/', 'OPR', $normalized);
        $normalized = preg_replace('/Firefox\/[\d.]+/', 'Firefox', $normalized);
        $normalized = trim($normalized);

        if (!in_array($normalized, $recentUas, true)) {
            $recentUas[] = $normalized;
        }

        $cache->set($cacheKey, $recentUas, self::CACHE_TTL);

        return count($recentUas);
    }

    /**
     * Checks if the IP has sent 4 or more distinct User-Agents within the detection window.
     */
    private function hasMultipleDistinctUserAgents(): bool
    {
        /**
         * Filters the minimum number of distinct user-agents required to flag as a definite bot.
         *
         * @param int $minCount
         * @since 1.0.0
         */
        $minCount = apply_filters('proactive_site_advisor_min_distinct_uas_for_bot', self::MIN_DISTINCT_UAS_FOR_BOT);

        return $this->getDistinctUserAgentCount() >= (int)$minCount;
    }

    /** Only flag if Sec-Fetch-User is explicitly ?0 (non-user-initiated). */
    private function hasExplicitNonUserNavigation(): bool
    {
        $ua = HeaderReader::getUserAgent();

        if (!$this->isModernChromeFamily($ua) && !$this->isModernFirefox($ua)) {
            return false;
        }

        $mode = HeaderReader::getSecFetchMode();
        $dest = HeaderReader::getSecFetchDest();
        $user = HeaderReader::getSecFetchUser();

        return ($mode === 'navigate' && $dest === 'document' && $user === '?0');
    }

    /** Determines if User-Agent is Firefox 90 or higher. */
    private function isModernFirefox(string $ua): bool
    {
        if (!preg_match('/Firefox\/(\d+)/i', $ua, $match)) {
            return false;
        }
        return (int)$match[1] >= self::MODERN_FIREFOX_VERSION_THRESHOLD;
    }

    /** Checks if non-modern browsers are sending Sec-Fetch-User header. */
    private function hasFetchUserForNonModern(string $ua): bool
    {
        if ($this->isModernChromeFamily($ua) || $this->isModernFirefox($ua)) {
            return false;
        }

        $user = HeaderReader::getSecFetchUser();

        return $user === '?1';
    }

    /** Checks for suspicious Accept-Language pattern (score 4). */
    private function hasSuspiciousAcceptLanguage(): bool
    {
        $lang = trim(HeaderReader::getAcceptLanguage());

        if ($lang === '') {
            return false;
        }

        return preg_match('/;q=1\.0(?:,|$)/i', $lang) === 1;
    }
}