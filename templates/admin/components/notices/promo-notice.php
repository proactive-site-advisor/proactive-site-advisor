<?php
/**
 * Component: Promo notice.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="psa-card psa-promo-card" data-psa-dismissible="true">
    <button
        type="button"
        class="psa-promo-card__dismiss"
        data-psa-action="dismiss-promo"
        aria-label="<?php esc_attr_e('Dismiss', 'proactive-site-advisor'); ?>"
    >
        <span class="psa-icon--close"></span>
    </button>
    <div class="psa-promo-card__body">
            <span class="psa-promo-card__badge">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <polygon points="12,2 15,9 22,9 17,14 19,22 12,17 5,22 7,14 2,9 9,9"/>
                </svg>
                <?php esc_html_e('Pro', 'proactive-site-advisor'); ?>
            </span>
        <h4 class="psa-promo-card__title"><?php esc_html_e('Never miss a critical issue', 'proactive-site-advisor'); ?></h4>
        <p class="psa-promo-card__description"><?php esc_html_e('Proactive Site Advisor Pro is coming with advanced monitoring and notification features.', 'proactive-site-advisor'); ?></p>
        <ul class="psa-promo-card__features psa-mb-3">
            <li>
                <span class="psa-promo-card__check"></span>
                <?php esc_html_e('Performance slowdown alerting', 'proactive-site-advisor'); ?>
            </li>
            <li>
                <span class="psa-promo-card__check"></span>
                <?php esc_html_e('Email & Slack notifications', 'proactive-site-advisor'); ?>
            </li>
            <li>
                <span class="psa-promo-card__check"></span>
                <?php esc_html_e('Custom alert rules & thresholds', 'proactive-site-advisor'); ?>
            </li>
            <li>
                <span class="psa-promo-card__check"></span>
                <?php esc_html_e('Security alerts for suspicious logins and critical changes', 'proactive-site-advisor'); ?>
            </li>
        </ul>
        <p class="psa-promo-card__note">
            <?php esc_html_e('More advanced features are planned for future versions.', 'proactive-site-advisor'); ?>
        </p>
        <a href="<?php echo esc_url('#'); ?>" class="psa-btn psa-btn--gold" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <polygon points="12,2 15,9 22,9 17,14 19,22 12,17 5,22 7,14 2,9 9,9"/>
            </svg>
            <?php esc_html_e('See what’s coming in Proactive Site Advisor Pro', 'proactive-site-advisor'); ?>
        </a>
    </div>
</div>