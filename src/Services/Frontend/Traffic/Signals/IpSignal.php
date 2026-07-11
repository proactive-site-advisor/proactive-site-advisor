<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class IpSignal
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class IpSignal implements BotSignalInterface
{
    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        $ip = HeaderReader::getIp();

        if ($ip === 'unknown' || $ip === '') {
            return true;
        }

        if ($this->isPrivateIp($ip)) {
            return false;
        }

        if ($this->isDatacenterIp($ip)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if IP is private or reserved.
     *
     * @param string $ip
     * @return bool
     */
    private function isPrivateIp(string $ip): bool
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
     * Checks if IP is from a known datacenter.
     *
     * @param string $ip
     * @return bool
     */
    private function isDatacenterIp(string $ip): bool
    {
        $ranges = $this->getDatacenterRanges();

        foreach ($ranges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns filterable datacenter IP ranges.
     *
     * @return array
     */
    private function getDatacenterRanges(): array
    {
        /**
         * Filter datacenter IP ranges.
         *
         * @param string[] $ranges List of CIDR ranges.
         */
        $ranges = apply_filters('proactive_site_advisor_datacenter_ip_ranges', []);

        return is_array($ranges) ? $ranges : [];
    }

    /**
     * Checks if IP is within a CIDR range.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private function ipInRange(string $ip, string $range): bool
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