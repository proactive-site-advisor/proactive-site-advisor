<?php

namespace ProactiveSiteAdvisor\Services\Insights\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defines the contract for alert generator classes.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Contracts
 * @since   1.0.0
 */
interface AlertGeneratorInterface
{
    /** Check if the generator should run for the given context. */
    public function isEligible(array $context): bool;

    /** Generate an alert if conditions are met. */
    public function generate(string $date, array $context): ?array;
}