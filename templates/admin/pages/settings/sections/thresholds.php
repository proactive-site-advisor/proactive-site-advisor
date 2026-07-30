<?php

/**
 * Section template: Thresholds.
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
                    <?php esc_html_e('Alert Eligibility', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Control the minimum activity levels required for traffic‑related anomaly alerts.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-min-weekly-avg" class="psa-form-label">
                            <?php esc_html_e('Minimum weekly average', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][min_weekly_avg]"
                            placeholder="<?php esc_attr_e('e.g. 3', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-min-weekly-avg"
                            value="<?php echo esc_attr($settings['thresholds']['min_weekly_avg']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('If the average pageview count over the last 7 days is below this number, traffic, 404, and bot alerts will not be generated.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-min-pageviews" class="psa-form-label">
                            <?php esc_html_e('Minimum pageviews for alert', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][min_pageviews_for_alert]"
                            placeholder="<?php esc_attr_e('e.g. 10', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-min-pageviews"
                            value="<?php echo esc_attr($settings['thresholds']['min_pageviews_for_alert']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('If today\'s total pageviews are below this number, traffic, 404, and bot alerts will be suppressed even if the percentage change is large.', 'proactive-site-advisor'); ?>
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
                    <?php esc_html_e('Alert Thresholds', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Set the percentage change thresholds that trigger traffic, 404, and bot alerts.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-traffic-spike" class="psa-form-label">
                            <?php esc_html_e('Human traffic spike', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][traffic_spike_percent]"
                            placeholder="<?php esc_attr_e('50', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-traffic-spike"
                            value="<?php echo esc_attr($settings['thresholds']['traffic_spike_percent']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Alert when today\'s human pageviews exceed the 7‑day average by more than this percentage.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-traffic-drop" class="psa-form-label">
                            <?php esc_html_e('Human traffic drop', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][traffic_drop_percent]"
                            placeholder="<?php esc_attr_e('30', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-traffic-drop"
                            value="<?php echo esc_attr($settings['thresholds']['traffic_drop_percent']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Alert when today\'s human pageviews fall below the 7‑day average by more than this percentage.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-404-spike" class="psa-form-label">
                            <?php esc_html_e('404 error surge', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][404_spike_percent]"
                            placeholder="<?php esc_attr_e('100', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-404-spike"
                            value="<?php echo esc_attr($settings['thresholds']['404_spike_percent']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Alert when today\'s 404 errors exceed the 7‑day average by more than this percentage.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-bot-spike" class="psa-form-label">
                            <?php esc_html_e('Bot traffic spike', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][bot_spike_percent]"
                            placeholder="<?php esc_attr_e('100', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-bot-spike"
                            value="<?php echo esc_attr($settings['thresholds']['bot_spike_percent']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Alert when today\'s bot pageviews exceed the 7‑day average by more than this percentage.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-bot-drop" class="psa-form-label">
                            <?php esc_html_e('Bot traffic drop', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[thresholds][bot_drop_percent]"
                            placeholder="<?php esc_attr_e('50', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-bot-drop"
                            value="<?php echo esc_attr($settings['thresholds']['bot_drop_percent']); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Alert when today\'s bot pageviews fall below the 7‑day average by more than this percentage.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>