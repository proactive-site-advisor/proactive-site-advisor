<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes Accept-Language header for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class AcceptLanguageSignal implements ScoreSignalInterface
{
    /** Score values. */
    private const SCORE_SIMPLE_LANGUAGE = 1;
    private const SCORE_INVALID_FORMAT  = 2;
    private const SCORE_DUPLICATE       = 1;
    private const SCORE_INVALID_QVALUE  = 2;

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score = 0;
        $lang  = trim(HeaderReader::getAcceptLanguage());

        if ($lang === '') {
            return 0;
        }

        if ($this->hasSimpleLanguage($lang)) {
            $score += self::SCORE_SIMPLE_LANGUAGE;
        }

        if ($this->hasInvalidFormat($lang)) {
            $score += self::SCORE_INVALID_FORMAT;
        }

        if ($this->hasDuplicateLanguages($lang)) {
            $score += self::SCORE_DUPLICATE;
        }

        if ($this->hasInvalidQualityValue($lang)) {
            $score += self::SCORE_INVALID_QVALUE;
        }

        return $score;
    }

    /** Checks simple language without region. */
    private function hasSimpleLanguage(string $header): bool
    {
        return preg_match('/^[a-z]{2,3}(?:;q=[\d.]+)?$/i', $header) === 1;
    }

    /** Checks invalid Accept-Language format. */
    private function hasInvalidFormat(string $header): bool
    {
        return preg_match(
                '/^[a-z]{1,8}(?:-[A-Za-z]{2,8})?(?:\s*;\s*q=(?:0(?:\.\d{1,3})?|1(?:\.0{1,3})?))?(?:\s*,\s*[a-z]{1,8}(?:-[A-Za-z]{2,8})?(?:\s*;\s*q=(?:0(?:\.\d{1,3})?|1(?:\.0{1,3})?))?)*$/',
                $header
            ) !== 1;
    }

    /** Checks duplicated language entries. */
    private function hasDuplicateLanguages(string $header): bool
    {
        $languages = [];

        foreach (explode(',', $header) as $item) {
            $language = strtolower(trim(explode(';', $item)[0]));

            if ($language === '') {
                continue;
            }

            if (isset($languages[$language])) {
                return true;
            }

            $languages[$language] = true;
        }

        return false;
    }

    /** Checks invalid quality values. */
    private function hasInvalidQualityValue(string $header): bool
    {
        foreach (explode(',', $header) as $item) {
            $parts = explode(';q=', strtolower(trim($item)));

            if (count($parts) < 2) {
                continue;
            }

            $quality = trim($parts[1]);

            if (!preg_match('/^(0(\.\d{1,3})?|1(\.0{1,3})?)$/', $quality)) {
                return true;
            }
        }

        return false;
    }
}