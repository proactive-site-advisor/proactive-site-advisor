<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analyzes Accept-Encoding header for bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\Fingerprint
 * @since   1.0.0
 */
class AcceptEncodingSignal implements ScoreSignalInterface
{
    /** Score values. */
    private const SCORE_UNKNOWN_ENCODING = 2;
    private const SCORE_DUPLICATE        = 1;
    private const SCORE_INVALID_QVALUE   = 2;

    /** Known HTTP content encodings. */
    private const KNOWN_ENCODINGS = [
        'gzip',
        'deflate',
        'br',
        'zstd',
        'identity',
    ];

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score    = 0;
        $encoding = trim(HeaderReader::getAcceptEncoding());

        if ($encoding === '') {
            return 0;
        }

        if ($this->hasUnknownEncoding($encoding)) {
            $score += self::SCORE_UNKNOWN_ENCODING;
        }

        if ($this->hasDuplicateEncoding($encoding)) {
            $score += self::SCORE_DUPLICATE;
        }

        if ($this->hasInvalidQualityValue($encoding)) {
            $score += self::SCORE_INVALID_QVALUE;
        }

        return $score;
    }

    /** Checks unknown encoding values. */
    private function hasUnknownEncoding(string $header): bool
    {
        foreach ($this->getEncodings($header) as $encoding) {
            if (!in_array($encoding, self::KNOWN_ENCODINGS, true)) {
                return true;
            }
        }

        return false;
    }

    /** Checks duplicated encoding entries. */
    private function hasDuplicateEncoding(string $header): bool
    {
        $encodings = [];

        foreach ($this->getEncodings($header) as $encoding) {
            if (isset($encodings[$encoding])) {
                return true;
            }

            $encodings[$encoding] = true;
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

    /** Extracts encoding names from header. */
    private function getEncodings(string $header): array
    {
        $encodings = [];

        foreach (explode(',', $header) as $item) {
            $encoding = strtolower(trim(explode(';', $item)[0]));

            if ($encoding !== '') {
                $encodings[] = $encoding;
            }
        }

        return $encodings;
    }
}