<?php

namespace ProactiveSiteAdvisor\Admin\Notices;

use ProactiveSiteAdvisor\Components\AjaxComponent;
use ProactiveSiteAdvisor\Config\UserOptions;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class PromoNotice
 *
 * @package ProactiveSiteAdvisor\Admin
 * @version 1.0.0
 */
class PromoNotice
{
    /**
     * Number of days the notice stays dismissed
     *
     * @var int
     */
    private const DISMISS_DURATION_DAYS = 14;

    /**
     * Whether the class has been initialized
     *
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Register hooks and AJAX handlers.
     *
     * @return void
     */
    public static function register(): void
    {
        if (self::$initialized) {
            return;
        }

        // Register AJAX handler for dismissing the promo notice
        AjaxComponent::register('dismiss_promo_notice', [self::class, 'handleDismiss'], false);

        self::$initialized = true;
    }

    /**
     * AJAX handler for dismissing the promo notice
     *
     * @return void
     */
    public static function handleDismiss(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        // Safe: Only updates current user's data; nonce is verified and user capability is checked in AjaxComponent::register().

        // Calculate dismissal end timestamp
        $dismissDays  = self::getDismissDuration();
        $dismissUntil = DateTimeUtils::timestamp() + ($dismissDays * DAY_IN_SECONDS);

        // Store per-user dismissal
        OptionUtils::setUserOption(UserOptions::PROMO_NOTICE_DISMISSED_UNTIL, $dismissUntil);

        AjaxComponent::sendSuccess([
            'dismissed_until' => $dismissUntil,
            'days'            => $dismissDays,
        ], __('Promo notice dismissed successfully.', 'proactive-site-advisor'));
    }

    /**
     * Check if the promo notice should be shown for the current user
     *
     * @return bool
     */
    public static function shouldShowPromoNotice(): bool
    {
        $dismissedUntil = OptionUtils::getUserOption(UserOptions::PROMO_NOTICE_DISMISSED_UNTIL, 0);

        // Never dismissed
        if (empty($dismissedUntil)) {
            return true;
        }

        // Check if dismissal period has expired
        return DateTimeUtils::timestamp() > (int)$dismissedUntil;
    }

    /**
     * Get the dismiss duration in days
     * Filterable via 'proactive_site_advisor_promo_dismiss_duration' hook
     *
     * @return int
     */
    private static function getDismissDuration(): int
    {
        return (int)apply_filters(
            'proactive_site_advisor_promo_dismiss_duration',
            self::DISMISS_DURATION_DAYS
        );
    }
}
