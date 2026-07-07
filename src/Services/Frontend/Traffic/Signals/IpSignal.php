<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class IpSignal
 *
 * Analyzes IP addresses for suspicious patterns.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class IpSignal
{
    /**
     * Check if IP is suspicious
     *
     * @return bool
     */
    public static function isSuspiciousIp(): bool
    {
        $ip = HeaderReader::getIp();

        if ($ip === 'unknown' || $ip === '') {
            return true;
        }

        if (self::isPrivateIp($ip)) {
            return false;
        }

        if (self::isDatacenterIp($ip)) {
            return true;
        }

        return false;
    }

    /**
     * Check if IP is private/reserved
     *
     * @param string $ip
     * @return bool
     */
    public static function isPrivateIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Check if IP is from known datacenter
     *
     * @param string $ip
     * @return bool
     */
    public static function isDatacenterIp(string $ip): bool
    {
        $ranges = self::getDatacenterRanges();

        foreach ($ranges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get datacenter IP ranges (filterable, default empty).
     *
     * @return array
     */
    private static function getDatacenterRanges(): array
    {
        /**
         * Filter datacenter IP ranges.
         *
         * To add ranges, use:
         * add_filter('proactive_site_advisor_datacenter_ip_ranges', function($ranges) {
         *     $ranges[] = '3.0.0.0/8';
         *     $ranges[] = '52.0.0.0/8';
         *     return $ranges;
         * });
         *
         * @param string[] $ranges List of CIDR ranges.
         */
        $ranges = apply_filters('proactive_site_advisor_datacenter_ip_ranges', []);

        return is_array($ranges) ? $ranges : [];
    }

    /**
     * Check if IP is in range (CIDR)
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    public static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range, 2);
        $bits = (int)$bits;

        $ipBin     = inet_pton($ip);
        $subnetBin = inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false) {
            return false;
        }

        if (strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;

        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $mask = str_repeat("\xff", intdiv($bits, 8));

        if ($bits % 8) {
            $mask .= chr((0xff << (8 - ($bits % 8))) & 0xff);
        }

        $mask = str_pad($mask, strlen($ipBin), "\0");

        return (($ipBin & $mask) === ($subnetBin & $mask));
    }
}