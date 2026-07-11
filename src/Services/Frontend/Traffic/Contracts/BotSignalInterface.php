<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface BotSignalInterface
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts
 * @version 1.0.0
 */
interface BotSignalInterface
{
    /**
     * Determines if the request is from a bot.
     *
     * @return bool
     */
    public function isBot(): bool;
}