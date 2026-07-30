<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes Cookie header behavior for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class CookieBehaviorSignal implements ScoreSignalInterface
{
    /** Score values. */
    private const SCORE_MALFORMED_COOKIE = 2;
    private const SCORE_EXCESSIVE_COOKIE = 1;
    private const SCORE_EMPTY_COOKIE_JAR = 2;

    /** Maximum cookie count threshold. */
    private const MAX_COOKIE_COUNT = 50;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score  = 0;
        $cookie = HeaderReader::getCookie();

        if ($cookie === '') {
            $score += self::SCORE_EMPTY_COOKIE_JAR;
            return $score;
        }

        if ($this->hasMalformedCookie($cookie)) {
            $score += self::SCORE_MALFORMED_COOKIE;
        }

        if ($this->hasExcessiveCookies($cookie)) {
            $score += self::SCORE_EXCESSIVE_COOKIE;
        }

        if ($this->hasCookieJarInconsistency($cookie)) {
            $score += self::SCORE_MALFORMED_COOKIE;
        }

        return $score;
    }

    /** Checks malformed cookie structure. */
    private function hasMalformedCookie(string $cookie): bool
    {
        return preg_match(
                '/^[^=;]+=[^;]*(?:;\s*[^=;]+=[^;]*)*$/',
                $cookie
            ) !== 1;
    }

    /** Checks unusually high cookie count. */
    private function hasExcessiveCookies(string $cookie): bool
    {
        $items = array_filter(
            array_map('trim', explode(';', $cookie))
        );

        return count($items) > self::MAX_COOKIE_COUNT;
    }

    /** Checks if cookie jar is empty or lacks valid name=value pairs. */
    private function hasCookieJarInconsistency(string $cookie): bool
    {
        return preg_match('/[^=;]+=[^=;]+/', $cookie) !== 1;
    }
}