<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;
use ProactiveSiteAdvisor\Models\RateCounter;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects bots based on request rate analysis.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class RateSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Rate window in seconds. */
    private const WINDOW = 10;

    /** Maximum allowed burst requests. */
    private const BURST_LIMIT = 5;

    /** Burst detection window in seconds. */
    private const BURST_WINDOW = 2;

    /** Score thresholds. */
    private const SCORE_ZERO_THRESHOLD     = 5;
    private const SCORE_MODERATE_THRESHOLD = 10;

    /** Score values. */
    private const SCORE_MODERATE = 2;
    private const SCORE_HIGH     = 3;

    /** Blatant bot detection threshold. */
    private const BLATANT_BOT_THRESHOLD = 10;

    /** IPv4 subnet prefix length. */
    private const IPV4_SUBNET_PREFIX = '/24';

    /** IPv6 subnet prefix length. */
    private const IPV6_SUBNET_PREFIX = '/64';

    /** Minimum number of parts for an IPv4 address. */
    private const MIN_IPV4_OCTETS = 3;

    /** Minimum number of parts for an IPv6 address. */
    private const MIN_IPV6_GROUPS = 4;

    /** Number of groups to keep for an IPv6 subnet. */
    private const IPV6_SUBNET_GROUPS = 4;

    /** Cached request count. */
    private static ?int $count = null;

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        if ($this->hasHighBurstRate()) {
            return true;
        }

        if ($this->isBlatantBot()) {
            return true;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $count = $this->count();

        if ($count <= self::SCORE_ZERO_THRESHOLD) {
            return 0;
        }

        if ($count <= self::SCORE_MODERATE_THRESHOLD) {
            return self::SCORE_MODERATE;
        }

        return self::SCORE_HIGH;
    }

    /** Checks if request rate is blatantly a bot. */
    private function isBlatantBot(): bool
    {
        return $this->count() > self::BLATANT_BOT_THRESHOLD;
    }

    /** Checks if request burst rate exceeds limit. */
    private function hasHighBurstRate(): bool
    {
        static $burstCount = null;

        if ($burstCount !== null) {
            return $burstCount > self::BURST_LIMIT;
        }

        $hash  = HeaderReader::getFingerprint() . '|burst|';
        $count = RateCounter::incrementAndGet($hash, self::BURST_WINDOW);

        $burstCount = $count;

        return $burstCount > self::BURST_LIMIT;
    }

    /** Returns request count within the window. */
    private function count(): int
    {
        if (self::$count !== null) {
            return self::$count;
        }

        $ip = HeaderReader::getIp();

        if ($ip === '' || $ip === 'unknown' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $key         = md5('invalid_ip' . uniqid('', true));
            $count       = RateCounter::incrementAndGet($key, self::WINDOW);
            self::$count = $count;
            return $count;
        }

        $subnet = $ip;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            if (count($parts) >= self::MIN_IPV4_OCTETS) {
                $subnet = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . self::IPV4_SUBNET_PREFIX;
            }
        } else {
            $parts = explode(':', $ip);
            if (count($parts) >= self::MIN_IPV6_GROUPS) {
                $subnet = implode(':', array_slice($parts, 0, self::IPV6_SUBNET_GROUPS)) . self::IPV6_SUBNET_PREFIX;
            }
        }

        $ua = HeaderReader::getUserAgent();

        /**
         * Filters the list of browser identifiers used for rate-limiting keys.
         *
         * @param string $browserPattern Regex alternation of browser names.
         * @since 1.0.0
         */
        $browserPattern = apply_filters('proactive_site_advisor_rate_signal_browsers', '(Chrome|Firefox|Safari|Edg|OPR)');

        preg_match('/' . $browserPattern . '/i', $ua, $match);
        $browser = $match[1] ?? 'unknown';
        $key     = md5($subnet . '|' . $browser);

        $count       = RateCounter::incrementAndGet($key, self::WINDOW);
        self::$count = $count;
        return $count;
    }
}