<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Decision;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotDetector;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserValidator;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\IpSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\ReferrerSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\RequestRateSignal;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BrowserFingerprintSignal;

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

        if (self::isBot() || BotDetector::isHeadless()) {
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

        $score = 0;

        if (!BrowserSignal::isBrowser()) {
            $score += 4;
        }

        if (BotDetector::isHeadless()) {
            $score += 5;
        }

        $score += RequestRateSignal::getScore();

        $score += BrowserFingerprintSignal::getScore();

        $score += self::getUnifiedBrowserPlausibilityScore();

        $result = $score >= 7;

        self::$cache[$key] = $result;

        return $result;
    }

    /**
     * Unified browser plausibility score (max 3 points).
     * Combines modern header presence and User-Agent version sanity.
     *
     * @return int
     */
    private static function getUnifiedBrowserPlausibilityScore(): int
    {
        $ua = HeaderReader::getUserAgent();
        if ($ua === '') {
            return 3;
        }

        $hasModernHints = (
            HeaderReader::getSecChUa() !== '' ||
            HeaderReader::getSecChUaMobile() !== '' ||
            HeaderReader::getSecChUaPlatform() !== ''
        );
        $hasLanguage    = HeaderReader::getAcceptLanguage() !== '';

        $score = 0;

        if (preg_match('/(Chrome|Edg|OPR|Firefox)\/(1\d{2,}|[2-9]\d{2,})/i', $ua)) {
            if (!$hasModernHints) {
                $score += 2;
            }
            if (!$hasLanguage) {
                ++$score;
            }
        }

        if (preg_match('/(Chrome|Edg|OPR|Firefox)\/([1-5]\d)\b/i', $ua)) {
            $score = max($score, 3);
        }

        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
            if (!$hasLanguage) {
                ++$score;
            }
            return min($score, 2);
        }

        return min($score, 3);
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
}