<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HeaderReader
 *
 * Centralized HTTP header access with security sanitization.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers
 * @version 1.0.0
 */
class HeaderReader
{
    /**
     * Get User-Agent header.
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        return self::getHeader('HTTP_USER_AGENT');
    }

    /**
     * Get Accept header.
     *
     * @return string
     */
    public static function getAccept(): string
    {
        return self::getHeader('HTTP_ACCEPT');
    }

    /**
     * Get Accept-Language header.
     *
     * @return string
     */
    public static function getAcceptLanguage(): string
    {
        return self::getHeader('HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * Get Accept-Encoding header.
     *
     * @return string
     */
    public static function getAcceptEncoding(): string
    {
        return self::getHeader('HTTP_ACCEPT_ENCODING');
    }

    /**
     * Get Referer header.
     *
     * @return string
     */
    public static function getReferer(): string
    {
        return self::getHeader('HTTP_REFERER');
    }

    /**
     * Get Sec-Ch-Ua header.
     *
     * @return string
     */
    public static function getSecChUa(): string
    {
        return self::getHeader('HTTP_SEC_CH_UA');
    }

    /**
     * Get Sec-Ch-Ua-Mobile header.
     *
     * @return string
     */
    public static function getSecChUaMobile(): string
    {
        return self::getHeader('HTTP_SEC_CH_UA_MOBILE');
    }

    /**
     * Get Sec-Ch-Ua-Platform header.
     *
     * @return string
     */
    public static function getSecChUaPlatform(): string
    {
        return self::getHeader('HTTP_SEC_CH_UA_PLATFORM');
    }

    /**
     * Get Sec-Fetch-Site header.
     *
     * @return string
     */
    public static function getSecFetchSite(): string
    {
        return self::getHeader('HTTP_SEC_FETCH_SITE');
    }

    /**
     * Get Sec-Fetch-Mode header.
     *
     * @return string
     */
    public static function getSecFetchMode(): string
    {
        return self::getHeader('HTTP_SEC_FETCH_MODE');
    }

    /**
     * Get Sec-Fetch-Dest header.
     *
     * @return string
     */
    public static function getSecFetchDest(): string
    {
        return self::getHeader('HTTP_SEC_FETCH_DEST');
    }

    /**
     * Get Purpose header.
     *
     * @return string
     */
    public static function getPurpose(): string
    {
        return self::getHeader('HTTP_PURPOSE');
    }

    /**
     * Get Sec-Purpose header.
     *
     * @return string
     */
    public static function getSecPurpose(): string
    {
        return self::getHeader('HTTP_SEC_PURPOSE');
    }

    /**
     * Get Upgrade-Insecure-Requests header.
     *
     * @return string
     */
    public static function getUpgradeInsecureRequests(): string
    {
        return self::getHeader('HTTP_UPGRADE_INSECURE_REQUESTS');
    }

    /**
     * Get HTTP Host header.
     *
     * @return string
     */
    public static function getHost(): string
    {
        return self::getHeader('HTTP_HOST');
    }

    /**
     * Get client IP address from standard headers.
     *
     * @return string
     */
    public static function getIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $value = self::getHeader($header);
            if ($value === '') {
                continue;
            }

            if ($header === 'HTTP_X_FORWARDED_FOR' && strpos($value, ',') !== false) {
                $ips   = explode(',', $value);
                $value = trim($ips[0]);
            }

            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }

        return 'unknown';
    }

    /**
     * Generic header getter with sanitization.
     *
     * @param string $key
     * @return string
     */
    public static function getHeader(string $key): string
    {
        if (!isset($_SERVER[$key]) || !is_string($_SERVER[$key])) {
            return '';
        }

        return sanitize_text_field(wp_unslash($_SERVER[$key]));
    }

    /**
     * Check if header exists and is not empty.
     *
     * @param string $key
     * @return bool
     */
    public static function hasHeader(string $key): bool
    {
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key] !== '';
    }
}