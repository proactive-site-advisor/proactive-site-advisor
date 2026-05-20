<?php

/**
 * Template part: Dashboard page header.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $statusLine
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="psa-page-header">
    <div class="psa-title-description-wrapper">
        <h1 class="psa-page-title">
            <?php esc_html_e('Dashboard', 'proactive-site-advisor'); ?>
        </h1>
        <p class="psa-page-description">
            <?php esc_html_e('Unusual activity on your site — with recommended actions.', 'proactive-site-advisor'); ?>
        </p>
        <p class="psa-page-meta">
            <span class="psa-icon--clock"></span>
            <?php echo esc_html($statusLine); ?>
        </p>
    </div>
</div>