<?php
/**
 * Component: Simple notice.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $id
 * @var string $type
 * @var string $message
 * @var string|null $title
 * @var string|null $icon
 * @var bool $dismissible
 * @var array $extraClasses
 */

if (!defined('ABSPATH')) {
    exit;
}

$classes = [
    'psa-notice',
    "psa-notice-{$type}",
];

if (!empty($dismissible)) {
    $classes[] = 'psa-notice-dismissible';
}

if (!empty($extraClasses)) {
    $classes = array_merge($classes, $extraClasses);
}

?>
<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr(implode(' ', $classes)); ?>">

    <?php if (!empty($icon)): ?>
        <span class="psa-notice-icon <?php echo esc_attr($icon); ?>"></span>
    <?php endif; ?>

    <div class="psa-notice-content">
        <?php if (!empty($title)): ?>
            <div class="psa-notice-title">
                <?php echo esc_html($title); ?>
            </div>
        <?php endif; ?>

        <div class="psa-notice-message">
            <?php echo wp_kses_post($message); ?>
        </div>
    </div>

    <?php if (!empty($dismissible)): ?>
        <button
            type="button"
            class="psa-notice-close"
            aria-label="<?php esc_attr_e('Close', 'proactive-site-advisor'); ?>"
        >
            <span class="psa-icon--close"></span>
        </button>
    <?php endif; ?>
</div>