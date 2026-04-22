<?php
/**
 * Component: Message card.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $title
 * @var string $text
 * @var string $helper
 * @var string $icon
 * @var string $color
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="psa-card psa-message-card psa-message-card--<?php echo esc_attr($color); ?>">
    <div class="psa-message-card__body">
        <div class="psa-message-card__icon">
            <span class="<?php echo esc_attr($icon); ?>"></span>
        </div>
        <div class="psa-message-card__content">
            <h5 class="psa-message-card__title"><?php echo esc_html($title); ?></h5>
            <p class="psa-message-card__text"><?php echo esc_html($text); ?></p>
            <?php if (!empty($helper)) : ?>
                <p class="psa-message-card__helper"><?php echo esc_html($helper); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
