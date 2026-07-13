<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines the contract for suspicion score signals.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts
 * @since   1.0.0
 */
interface ScoreSignalInterface
{
    /** Returns a suspicion score for the current request. */
    public function getScore(): int;
}