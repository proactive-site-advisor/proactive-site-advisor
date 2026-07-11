<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface ScoreSignalInterface
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts
 * @version 1.0.0
 */
interface ScoreSignalInterface
{
    /**
     * Returns a suspicion score for the current request.
     *
     * @return int
     */
    public function getScore(): int;
}