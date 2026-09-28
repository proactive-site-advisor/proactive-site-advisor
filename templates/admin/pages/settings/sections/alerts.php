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
                <h5 class="psa-card-title">
                    <?php esc_html_e('Human Traffic', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Watch the real visitors on your site — the people who read, click, and buy.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Traffic Drop -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Traffic Drop', 'proactive-site-advisor'); ?>
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
                            <label for="psa-alert-traffic-drop" class="psa-form-check-label"><?php esc_html_e('Detect', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Sudden drops in human pageviews. Often the first sign something is broken.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Traffic Spike -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Traffic Spike', 'proactive-site-advisor'); ?>
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
                            <label for="psa-alert-traffic-spike" class="psa-form-check-label"><?php esc_html_e('Detect', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Sudden increases in human pageviews. Could be a viral moment — or an attack.', 'proactive-site-advisor'); ?>
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
                    <?php esc_html_e('404 Errors', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e("Catch broken links before your visitors do — pages they tried to reach but couldn't.", 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

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
                            <label for="psa-alert-404-spike" class="psa-form-check-label"><?php esc_html_e('Detect', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('A rising number of 404 errors. Usually means broken links, deleted content, or a bad redirect.', 'proactive-site-advisor'); ?>
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
                    <?php esc_html_e('Bot Traffic', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Track the automated visitors on your site — search engine crawlers, AI scrapers, and everything in between.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Bot Traffic Change -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Bot Traffic Change', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-alert-bot-change"
                                name="settings[alerts][bot_change]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['alerts']['bot_change'])); ?>
                            >
                            <label for="psa-alert-bot-change" class="psa-form-check-label"><?php esc_html_e('Detect', 'proactive-site-advisor'); ?></label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Unusual spikes or drops in bot activity. Often signals new scrapers, AI crawlers, or a sudden block or attack.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <p class="psa-settings__text psa-form-text">
        <?php esc_html_e('Turning any alert off stops it from being detected anywhere — dashboard, email, and any connected channel.', 'proactive-site-advisor'); ?>
    </p>
</div>