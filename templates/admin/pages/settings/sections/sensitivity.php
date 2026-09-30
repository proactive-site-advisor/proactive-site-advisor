<?php

/**
 * Section template: Sensitivity.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor\Templates\Admin\Pages\Settings\Sections
 * @since   1.0.0
 *
 * @var array $settings
 * @var string $sectionId
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="psa-section-<?php echo esc_attr($sectionId); ?>" class="psa-settings__section">

    <div class="psa-card">
        <div class="psa-card-header">
            <div class="psa-card-header-content">
                <h5 class="psa-card-title">
                    <?php esc_html_e('Alert Sensitivity', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Choose how strict the alert engine should be. Higher sensitivity catches smaller changes but may produce more noise.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Low -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Low', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check">
                            <input
                                type="radio"
                                id="psa-sensitivity-level-low"
                                name="settings[sensitivity][level]"
                                value="low"
                                class="psa-form-check-input"
                                <?php checked(($settings['sensitivity']['level']) === 'low'); ?>
                            >
                            <label for="psa-sensitivity-level-low" class="psa-form-check-label"><?php esc_html_e('Use this', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Only extreme changes trigger alerts. Best for sites with naturally high day-to-day variation.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Normal -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Normal (recommended)', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check">
                            <input
                                type="radio"
                                id="psa-sensitivity-level-normal"
                                name="settings[sensitivity][level]"
                                value="normal"
                                class="psa-form-check-input"
                                <?php checked(($settings['sensitivity']['level']) === 'normal'); ?>
                            >
                            <label for="psa-sensitivity-level-normal" class="psa-form-check-label"><?php esc_html_e('Use this', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Balanced between catching real issues and avoiding noise. Works well for most sites.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- High -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('High', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check">
                            <input
                                type="radio"
                                id="psa-sensitivity-level-high"
                                name="settings[sensitivity][level]"
                                value="high"
                                class="psa-form-check-input"
                                <?php checked(($settings['sensitivity']['level']) === 'high'); ?>
                            >
                            <label for="psa-sensitivity-level-high" class="psa-form-check-label"><?php esc_html_e('Use this', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Even small changes trigger alerts. Best for very stable sites where every shift matters.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="psa-card">
        <div class="psa-card-header">
            <div class="psa-card-header-content">
                <h5 class="psa-card-title">
                    <?php esc_html_e('Advanced', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e("Fine-tune the minimum change that matters for each metric. Most sites don't need to touch this.", 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Auto-tune thresholds -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Auto-tune thresholds', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-sensitivity-auto-tune"
                                name="settings[sensitivity][auto_tune]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['sensitivity']['auto_tune'])); ?>
                            >
                            <label for="psa-sensitivity-auto-tune" class="psa-form-check-label"><?php esc_html_e('Enable', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('We automatically adjust the minimum change thresholds based on your site\'s traffic scale. Recommended for most sites.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Manual thresholds (shown when auto-tune is off) -->
                <div class="psa-sensitivity-manual<?php echo !empty($settings['sensitivity']['auto_tune']) ? ' psa-d-none' : ''; ?>">

                    <!-- Traffic min abs -->
                    <div class="psa-settings__item">
                        <div class="psa-settings__label">
                            <label for="psa-sensitivity-traffic-min-abs" class="psa-form-label">
                                <?php esc_html_e('Minimum meaningful traffic change', 'proactive-site-advisor'); ?>
                            </label>
                        </div>

                        <div class="psa-settings__field">
                            <input
                                type="text"
                                name="settings[sensitivity][traffic_min_abs]"
                                placeholder="<?php esc_attr_e('10', 'proactive-site-advisor'); ?>"
                                class="psa-form-control"
                                id="psa-sensitivity-traffic-min-abs"
                                value="<?php echo esc_attr($settings['sensitivity']['traffic_min_abs']); ?>"
                            >

                            <div class="psa-settings__text psa-form-text">
                                <?php esc_html_e('Ignore daily human traffic changes smaller than this number.', 'proactive-site-advisor'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- 404 min abs -->
                    <div class="psa-settings__item">
                        <div class="psa-settings__label">
                            <label for="psa-sensitivity-404-min-abs" class="psa-form-label">
                                <?php esc_html_e('Minimum meaningful 404 change', 'proactive-site-advisor'); ?>
                            </label>
                        </div>

                        <div class="psa-settings__field">
                            <input
                                type="text"
                                name="settings[sensitivity][error_404_min_abs]"
                                placeholder="<?php esc_attr_e('3', 'proactive-site-advisor'); ?>"
                                class="psa-form-control"
                                id="psa-sensitivity-404-min-abs"
                                value="<?php echo esc_attr($settings['sensitivity']['error_404_min_abs']); ?>"
                            >

                            <div class="psa-settings__text psa-form-text">
                                <?php esc_html_e('Ignore daily 404 changes smaller than this number.', 'proactive-site-advisor'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Bot min abs -->
                    <div class="psa-settings__item">
                        <div class="psa-settings__label">
                            <label for="psa-sensitivity-bot-min-abs" class="psa-form-label">
                                <?php esc_html_e('Minimum meaningful bot change', 'proactive-site-advisor'); ?>
                            </label>
                        </div>

                        <div class="psa-settings__field">
                            <input
                                type="text"
                                name="settings[sensitivity][bot_min_abs]"
                                placeholder="<?php esc_attr_e('10', 'proactive-site-advisor'); ?>"
                                class="psa-form-control"
                                id="psa-sensitivity-bot-min-abs"
                                value="<?php echo esc_attr($settings['sensitivity']['bot_min_abs']); ?>"
                            >

                            <div class="psa-settings__text psa-form-text">
                                <?php esc_html_e('Ignore daily bot traffic changes smaller than this number.', 'proactive-site-advisor'); ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>