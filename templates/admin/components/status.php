<?php

/**
 * Component: Status box.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $color
 * @var string $title
 * @var string $text
 * @var mixed $progress
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="psa-status-summary psa-status-summary--<?php echo esc_attr($color); ?>">
    <div class="psa-status-summary__content">
        <strong class="psa-status-summary__title"><?php echo esc_html($title); ?></strong>
        <span class="psa-status-summary__separator">—</span>
        <span class="psa-status-summary__text"><?php echo esc_html($text); ?></span>
        <?php if (!empty($progress)) : ?>
            <div class="psa-status-summary__progress">
                <div class="psa-status-summary__progress-bar" style="width: <?php echo esc_attr($progress); ?>%;"></div>
            </div>
        <?php endif; ?>
    </div>
</div>