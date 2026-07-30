<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Utils\Request;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detects suspicious HTTP request methods.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @since   1.0.0
 */
class RequestMethodSignal implements BotSignalInterface, ScoreSignalInterface
{
    /** Score values. */
    private const SCORE_OPTIONS_METHOD = 2;
    private const SCORE_HEAD_METHOD    = 1;

    /** Allowed HTTP methods. */
    private const ALLOWED_METHODS = [
        'get',
        'head',
        'post',
        'options',
    ];

    /** {@inheritDoc} */
    public function isBot(): bool
    {
        return $this->hasUnknownMethod();
    }

    /** {@inheritDoc} */
    public function getScore(): int
    {
        $score  = 0;
        $method = Request::method();

        if ($method === 'options') {
            $score += self::SCORE_OPTIONS_METHOD;
        }

        if ($method === 'head') {
            $score += self::SCORE_HEAD_METHOD;
        }

        return $score;
    }

    /** Detects unknown HTTP methods. */
    private function hasUnknownMethod(): bool
    {
        return !in_array(Request::method(), self::ALLOWED_METHODS, true);
    }
}