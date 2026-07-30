<?php
/**
 * Section template: Alerts.
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
                <h5 class="psa-card-title"><?php esc_html_e('Active Alerts', 'proactive-site-advisor'); ?></h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Choose which anomalies you want to be notified about. Disabling an alert type will suppress it even if its threshold is reached.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Human Traffic Drop -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Human Traffic Drop', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-alert-traffic-drop"
                                name="settings[alerts][traffic_drop]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['alerts']['traffic_drop'])); ?>
                            >
                            <label for="psa-alert-traffic-drop" class="psa-form-check-label"><?php esc_html_e('Enable', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Get alerted when human pageviews drop significantly.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Human Traffic Spike -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Human Traffic Spike', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-alert-traffic-spike"
                                name="settings[alerts][traffic_spike]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['alerts']['traffic_spike'])); ?>
                            >
                            <label for="psa-alert-traffic-spike" class="psa-form-check-label"><?php esc_html_e('Enable', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Get alerted when human pageviews suddenly increase.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- 404 Error Surge -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('404 Error Surge', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-alert-404-spike"
                                name="settings[alerts][404_spike]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['alerts']['404_spike'])); ?>
                            >
                            <label for="psa-alert-404-spike" class="psa-form-check-label"><?php esc_html_e('Enable', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Get alerted when 404 errors jump above the normal baseline.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Bot Traffic Spike -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Bot Traffic Spike', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-alert-bot-spike"
                                name="settings[alerts][bot_spike]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['alerts']['bot_spike'])); ?>
                            >
                            <label for="psa-alert-bot-spike" class="psa-form-check-label"><?php esc_html_e('Enable', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Get alerted when bot pageviews show a sudden upward spike.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Bot Traffic Drop -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Bot Traffic Drop', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-alert-bot-drop"
                                name="settings[alerts][bot_drop]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['alerts']['bot_drop'])); ?>
                            >
                            <label for="psa-alert-bot-drop" class="psa-form-check-label"><?php esc_html_e('Enable', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Get alerted when bot pageviews drop below expected levels.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>