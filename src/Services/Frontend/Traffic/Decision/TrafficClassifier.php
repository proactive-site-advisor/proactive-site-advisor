<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Decision;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotDetector;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserValidator;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\IpSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\ReferrerSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\RequestRateSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TrafficClassifier
 *
 * Centralized decision maker combining all traffic signals.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Decision
 * @version 1.0.0
 */
class TrafficClassifier
{
    /**
     * Runtime cache for decision results.
     *
     * @var array
     */
    private static array $cache = [];

    /**
     * Determine if the current request is from a real human user.
     *
     * @return bool
     */
    public static function isRealHuman(): bool
    {
        $key = 'is_real_human';
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $result = true;

        if (self::isBot()) {
            $result = false;
        } elseif (self::isSuspicious()) {
            $result = false;
        } elseif (IpSignal::isSuspiciousIp()) {
            $result = false;
        } else {
            $referrerUrl = HeaderReader::getReferer();
            $currentHost = HeaderReader::getHost();
            if (ReferrerSignal::isSpamReferrer($referrerUrl, $currentHost)) {
                $result = false;
            }
        }

        self::$cache[$key] = $result;
        return $result;
    }

    /**
     * Determine if the current request is a bot.
     *
     * @return bool
     */
    public static function isBot(): bool
    {
        $key = 'is_bot';
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $result = false;

        if (BotDetector::isBot()) {
            $result = true;
        } else {
            $ua = HeaderReader::getUserAgent();
            if (BrowserValidator::hasInvalidBrowserName($ua)) {
                $result = true;
            }
        }

        self::$cache[$key] = $result;
        return $result;
    }

    /**
     * Determine if the current request is suspicious.
     *
     * @return bool
     */
    public static function isSuspicious(): bool
    {
        $key = 'is_suspicious';
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $result = false;

        if (BotDetector::isHeadless()) {
            $result = true;
        } elseif (RequestRateSignal::isBotLike()) {
            $result = true;
        } elseif (self::isUserAgentImplausible()) {
            $result = true;
        } else {
            $score = self::calculateSuspicionScore();
            if ($score >= 4) {
                $result = true;
            }
        }

        self::$cache[$key] = $result;
        return $result;
    }

    /**
     * Determine if 404 should be tracked.
     *
     * @return bool
     */
    public static function shouldTrack404(): bool
    {
        $key = 'should_track_404';
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $result = true;

        if (self::isBot()) {
            $result = false;
        } elseif (self::isSuspicious()) {
            $result = false;
        } elseif (IpSignal::isSuspiciousIp()) {
            $result = false;
        } elseif (!BrowserSignal::isBrowser()) {
            $result = false;
        }

        self::$cache[$key] = $result;
        return $result;
    }

    /**
     * Check if the User-Agent version is implausibly old.
     *
     * @return bool
     */
    private static function isUserAgentImplausible(): bool
    {
        $ua              = HeaderReader::getUserAgent();
        $secChUa         = HeaderReader::getSecChUa();
        $secChUaMobile   = HeaderReader::getSecChUaMobile();
        $secChUaPlatform = HeaderReader::getSecChUaPlatform();
        $acceptLanguage  = HeaderReader::getAcceptLanguage();

        if ($ua === '') {
            return false;
        }

        $score          = 0;
        $hasModernHints = $secChUa !== '' || $secChUaMobile !== '' || $secChUaPlatform !== '';

        $isChrome  = preg_match('/Chrome\/(\d+)/i', $ua, $chromeMatch) && stripos($ua, 'Edg') === false && stripos($ua, 'OPR') === false;
        $isEdge    = preg_match('/Edg\/(\d+)/i', $ua, $edgeMatch);
        $isOpera   = preg_match('/OPR\/(\d+)/i', $ua, $operaMatch);
        $isFirefox = preg_match('/Firefox\/(\d+)/i', $ua, $firefoxMatch);
        $isSafari  = preg_match('/Version\/(\d+)/i', $ua, $safariMatch) || preg_match('/OS (\d+)_/i', $ua, $osMatch);

        if ($isChrome) {
            $version = (int)$chromeMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 90) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else {
                if (!$hasModernHints) {
                    $score += 3;
                }
                if ($hasModernHints && $secChUa !== '' && stripos($secChUa, 'Chrome') === false && stripos($secChUa, 'Chromium') === false) {
                    $score += 2;
                }
            }
        }

        if ($isEdge) {
            $version = (int)$edgeMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 90) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else {
                if (!$hasModernHints) {
                    $score += 3;
                }
                if ($hasModernHints && $secChUa !== '' && stripos($secChUa, 'Edg') === false && stripos($secChUa, 'Edge') === false) {
                    $score += 2;
                }
            }
        }

        if ($isOpera) {
            $version = (int)$operaMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 90) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else {
                if (!$hasModernHints) {
                    $score += 3;
                }
                if ($hasModernHints && $secChUa !== '' && stripos($secChUa, 'Opera') === false && stripos($secChUa, 'OPR') === false) {
                    $score += 2;
                }
            }
        }

        if ($isFirefox) {
            $version = (int)$firefoxMatch[1];
            if ($version < 60) {
                $score += 3;
            } elseif ($version < 100) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else if (!$hasModernHints) {
                $score += 3;
            }
        }

        if ($isSafari) {
            $version = (int)($safariMatch[1] ?? $osMatch[1] ?? 0);
            if ($version < 15) {
                $score += 3;
            } elseif ($version < 17) {
                if (!$hasModernHints && $acceptLanguage === '') {
                    $score += 2;
                }
            } else if (!$hasModernHints) {
                $score += 3;
            }
        }

        if ($acceptLanguage === '') {
            ++$score;
        }

        return $score >= 4;
    }

    /**
     * Calculate suspicion score based on headers.
     *
     * @return int
     */
    private static function calculateSuspicionScore(): int
    {
        $ua             = HeaderReader::getUserAgent();
        $accept         = HeaderReader::getAccept();
        $acceptLanguage = HeaderReader::getAcceptLanguage();
        $acceptEncoding = HeaderReader::getAcceptEncoding();
        $secChUa        = HeaderReader::getSecChUa();
        $secFetchSite   = HeaderReader::getSecFetchSite();
        $secFetchMode   = HeaderReader::getSecFetchMode();
        $secFetchDest   = HeaderReader::getSecFetchDest();

        $score = 0;

        $isChrome = stripos($ua, 'Chrome') !== false
            && stripos($ua, 'Edg') === false
            && stripos($ua, 'OPR') === false;

        $isSafari = stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false;
        $isEdge   = stripos($ua, 'Edg') !== false;
        $isOpera  = stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false;

        if (stripos($accept, 'text/html') === false) {
            $score += 3;
        }

        $acceptTypes = $accept !== ''
            ? array_filter(array_map('trim', explode(',', $accept)))
            : [];
        if (count($acceptTypes) < 2) {
            $score += 2;
        }

        if ($acceptLanguage === '') {
            ++$score;
        }

        if ($acceptEncoding === '' || trim($acceptEncoding) === 'identity') {
            ++$score;
        }

        if (($isChrome || $isEdge || $isOpera) && $secChUa === '') {
            $score += 2;
        }

        if ($isSafari && $acceptLanguage === '') {
            $score += 2;
        }

        if (!in_array($secFetchSite, ['none', 'same-origin', 'same-site', 'cross-site'], true)) {
            ++$score;
        }

        if ($secFetchMode === 'navigate' && $secFetchDest === 'document') {
            $score -= 2;
        }

        $score += self::getHeaderInconsistencyScore($ua, $acceptLanguage, $secChUa);

        return $score;
    }

    /**
     * Calculate header inconsistency score.
     *
     * @param string $ua
     * @param string $acceptLanguage
     * @param string $secChUa
     * @return int
     */
    private static function getHeaderInconsistencyScore(
        string $ua,
        string $acceptLanguage,
        string $secChUa
    ): int
    {
        $score = 0;

        if ($secChUa !== '') {
            $isChromeFamily = (
                stripos($ua, 'Chrome') !== false ||
                stripos($ua, 'Edg') !== false ||
                stripos($ua, 'OPR') !== false
            );

            if ($isChromeFamily) {
                $hasChromeHint = (
                    stripos($secChUa, 'Chrome') !== false ||
                    stripos($secChUa, 'Google Chrome') !== false ||
                    stripos($secChUa, 'Chromium') !== false
                );

                if (!$hasChromeHint) {
                    $score += 2;
                }
            }
        }

        if ($acceptLanguage === '') {
            ++$score;
        } else {
            $firstLang = strtok($acceptLanguage, ',;');
            $firstLang = trim($firstLang);
            if (!preg_match('/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]{1,8})*$/', $firstLang)) {
                ++$score;
            }
        }

        $acceptEncoding = HeaderReader::getAcceptEncoding();
        if ($acceptEncoding !== '') {
            $isModernUA = preg_match(
                '/(Chrome|Firefox|Edg|Safari)\/\d{2,}/',
                $ua
            );

            if ($isModernUA && stripos($acceptEncoding, 'br') === false) {
                ++$score;
            }
        }

        return $score;
    }
}