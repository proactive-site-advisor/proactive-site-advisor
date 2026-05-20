<?php

/**
 * Component: Stat card.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $icon
 * @var string $value
 * @var string $label
 * @var string $subtitle
 * @var string $color
 * @var string $link
 */

if (!defined('ABSPATH')) {
    exit;
}

$tag   = $link ? 'a' : 'div';
$attrs = $link ? 'href=' . esc_url($link) : '';
?>
<<?php echo esc_html($tag); ?> class="psa-stat-card" <?php echo esc_attr($attrs); ?>>
<div class="psa-stat-card__body">

    <div class="psa-stat-card__icon psa-stat-card__icon--<?php echo esc_attr($color); ?>">
        <span class="<?php echo esc_attr($icon); ?>"></span>
    </div>

    <div class="psa-stat-card__content">
        <p class="psa-stat-card__label"><?php echo esc_html($label); ?></p>
        <h4 class="psa-stat-card__value"><?php echo esc_html($value); ?></h4>
        <p class="psa-stat-card__subtitle"><?php echo esc_html($subtitle); ?></p>
    </div>

</div>
</<?php echo esc_html($tag); ?>>