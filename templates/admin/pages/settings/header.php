<?php

/**
 * Template part: Settings page header.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor\Templates\Admin\Pages\Settings
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Page Header -->
<div class="psa-page-header">
    <div class="psa-title-description-wrapper">
        <h1 class="psa-page-title">
            <?php esc_html_e('Settings', 'proactive-site-advisor'); ?>
        </h1>
        <p class="psa-page-description">
            <?php esc_html_e('Control when and how you receive anomaly alerts for your site.', 'proactive-site-advisor'); ?>
        </p>
    </div>
</div>