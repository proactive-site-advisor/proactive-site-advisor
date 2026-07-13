<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines the contract for bot detection signals.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts
 * @since   1.0.0
 */
interface BotSignalInterface
{
    /** Determines if the request is from a bot. */
    public function isBot(): bool;
}