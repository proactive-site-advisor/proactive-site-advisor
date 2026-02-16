<?php
/**
 * Promo Card Component
 *
 * Displays a premium upsell card with features list and CTA button.
 * Optionally dismissible with a close button.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $badge Badge text (e.g., "Pro")
 * @var string $title Card title
 * @var string $description Card description
 * @var array $features List of feature strings
 * @var string $buttonText CTA button text
 * @var string $buttonUrl CTA button URL
 * @var bool $dismissible Whether the card can be dismissed
 * @var string $dismissNonce Nonce for dismiss AJAX action
 */

defined('ABSPATH') || exit;

$badge       = $badge ?? __('Pro', 'proactive-site-advisor');
$title       = $title ?? __('Proactive Site Advisor Pro', 'proactive-site-advisor');
$description = $description ?? '';
$features    = $features ?? [];
$note        = $note ?? '';
$buttonText  = $buttonText ?? __('Upgrade to Pro', 'proactive-site-advisor');
$buttonUrl   = $buttonUrl ?? '#';
$dismissible = $dismissible ?? false;
?>

<div class="proactive-site-advisor-card proactive-site-advisor-promo-card"<?php echo $dismissible ? ' data-proactive-site-advisor-dismissible="true"' : ''; ?>>
    <?php if ($dismissible) : ?>
        <button
            type="button"
            class="proactive-site-advisor-promo-card__dismiss"
            data-proactive-site-advisor-action="dismiss-promo"
            aria-label="<?php esc_attr_e('Dismiss', 'proactive-site-advisor'); ?>"
        >
            <span class="proactive-site-advisor-icon--close"></span>
        </button>
    <?php endif; ?>
    <div class="proactive-site-advisor-promo-card__body">
        <?php if (!empty($badge)) : ?>
            <span class="proactive-site-advisor-promo-card__badge">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <polygon points="12,2 15,9 22,9 17,14 19,22 12,17 5,22 7,14 2,9 9,9"/>
                </svg>
                <?php echo esc_html($badge); ?>
            </span>
        <?php endif; ?>
        <h4 class="proactive-site-advisor-promo-card__title"><?php echo esc_html($title); ?></h4>
        <?php if (!empty($description)) : ?>
            <p class="proactive-site-advisor-promo-card__description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php if (!empty($features)) : ?>
            <ul class="proactive-site-advisor-promo-card__features proactive-site-advisor-mb-3">
                <?php foreach ($features as $feature) : ?>
                    <li>
                        <span class="proactive-site-advisor-promo-card__check"></span>
                        <?php echo esc_html($feature); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (!empty($note)) : ?>
            <p class="proactive-site-advisor-promo-card__note">
                <?php echo esc_html($note); ?>
            </p>
        <?php endif; ?>
        <a href="<?php echo esc_url($buttonUrl); ?>" class="proactive-site-advisor-btn proactive-site-advisor-btn--gold" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <polygon points="12,2 15,9 22,9 17,14 19,22 12,17 5,22 7,14 2,9 9,9"/>
            </svg>
            <?php echo esc_html($buttonText); ?>
        </a>
    </div>
</div>
