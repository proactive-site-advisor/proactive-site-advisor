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
 * Promotional notice with dismissal support.
 *
 * phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is handled inside AjaxComponent::register().
 *
 * @package ProactiveSiteAdvisor\Admin\Notices
 * @since   1.0.0
 */
class PromoNotice
{
    /** Number of days the notice stays dismissed. */
    private const DISMISS_DURATION_DAYS = 14;

    /** Whether the class has been initialized. */
    private static bool $initialized = false;

    /** Register hooks and AJAX handlers. */
    public static function register(): void
    {
        if (self::$initialized) {
            return;
        }

        AjaxComponent::register('dismiss_promo_notice', [self::class, 'handleDismiss'], false);

        self::$initialized = true;
    }

    /** AJAX handler for dismissing the promo notice. */
    public static function handleDismiss(): void
    {
        $dismissDays  = self::getDismissDuration();
        $dismissUntil = DateTimeUtils::timestamp() + ($dismissDays * DAY_IN_SECONDS);

        OptionUtils::setUserOption(UserOptions::PROMO_NOTICE_DISMISSED_UNTIL, $dismissUntil);

        AjaxComponent::sendSuccess([
            'dismissed_until' => $dismissUntil,
            'days'            => $dismissDays,
        ], __('Promo notice dismissed successfully.', 'proactive-site-advisor'));
    }

    /** Check if the promo notice should be shown for the current user. */
    public static function shouldShowPromoNotice(): bool
    {
        $dismissedUntil = (int)OptionUtils::getUserOption(UserOptions::PROMO_NOTICE_DISMISSED_UNTIL, 0);

        if (empty($dismissedUntil)) {
            return true;
        }

        return DateTimeUtils::timestamp() > $dismissedUntil;
    }

    /** Get the dismiss duration in days. */
    private static function getDismissDuration(): int
    {
        /**
         * Filters the promo notice dismiss duration.
         *
         * @param int $days Number of days before notice reappears.
         * @return int
         * @since  1.0.0
         */
        return (int)apply_filters(
            'proactive_site_advisor_promo_dismiss_duration',
            self::DISMISS_DURATION_DAYS
        );
    }
}