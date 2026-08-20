<?php
/**
 * Section template: Notifications.
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

    <!-- Daily Digest Settings -->
    <div class="psa-card">
        <div class="psa-card-header">
            <div class="psa-card-header-content">
                <h5 class="psa-card-title">
                    <?php esc_html_e('Daily Digest', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Configure the daily summary email sent after each monitoring cycle.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Enable Daily Digest -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Enable Daily Digest', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-enable-daily-digest"
                                name="settings[notifications][enable_daily_digest]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['notifications']['enable_daily_digest'])); ?>
                            >
                            <label for="psa-enable-daily-digest" class="psa-form-check-label">
                                <?php esc_html_e('Enable', 'proactive-site-advisor'); ?>
                            </label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Send a daily summary email when at least one alert has been recorded.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Recipient Email -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <label for="psa-digest-recipient-email" class="psa-form-label">
                            <?php esc_html_e('Recipient Email', 'proactive-site-advisor'); ?>
                        </label>
                    </div>

                    <div class="psa-settings__field">
                        <input
                            type="text"
                            name="settings[notifications][digest_recipient_email]"
                            placeholder="<?php esc_attr_e('e.g. admin@example.com', 'proactive-site-advisor'); ?>"
                            class="psa-form-control"
                            id="psa-digest-recipient-email"
                            value="<?php echo esc_attr($settings['notifications']['digest_recipient_email'] ?? get_option('admin_email')); ?>"
                        >

                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Email address where the daily digest will be sent. Falls back to the admin email if left empty.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Alert Types to Include -->
    <div class="psa-card">
        <div class="psa-card-header">
            <div class="psa-card-header-content">
                <h5 class="psa-card-title">
                    <?php esc_html_e('Alert Types to Include', 'proactive-site-advisor'); ?>
                </h5>
                <p class="psa-card-subtitle">
                    <?php esc_html_e('Select which alert types should appear in the daily digest email.', 'proactive-site-advisor'); ?>
                </p>
            </div>
        </div>

        <div class="psa-card-body">
            <div class="psa-settings__list">

                <!-- Include Traffic Alerts -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Traffic Alerts', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-digest-include-traffic"
                                name="settings[notifications][digest_include_traffic]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['notifications']['digest_include_traffic'])); ?>
                            >
                            <label for="psa-digest-include-traffic" class="psa-form-check-label">
                                <?php esc_html_e('Include', 'proactive-site-advisor'); ?>
                            </label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Include human traffic spikes and drops in the digest.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Include 404 Alerts -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('404 Alerts', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-digest-include-404"
                                name="settings[notifications][digest_include_404]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['notifications']['digest_include_404'])); ?>
                            >
                            <label for="psa-digest-include-404" class="psa-form-check-label">
                                <?php esc_html_e('Include', 'proactive-site-advisor'); ?>
                            </label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Include 404 error surge alerts in the digest.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

                <!-- Include Bot Alerts -->
                <div class="psa-settings__item">
                    <div class="psa-settings__label">
                        <div class="psa-form-label">
                            <?php esc_html_e('Bot Alerts', 'proactive-site-advisor'); ?>
                        </div>
                    </div>

                    <div class="psa-settings__field">
                        <div class="psa-form-check psa-form-switch">
                            <input
                                type="checkbox"
                                id="psa-digest-include-bot"
                                name="settings[notifications][digest_include_bot]"
                                value="1"
                                class="psa-form-check-input"
                                <?php checked(!empty($settings['notifications']['digest_include_bot'])); ?>
                            >
                            <label for="psa-digest-include-bot" class="psa-form-check-label">
                                <?php esc_html_e('Include', 'proactive-site-advisor'); ?>
                            </label>
                        </div>
                        <div class="psa-settings__text psa-form-text">
                            <?php esc_html_e('Include bot traffic spikes and drops in the digest.', 'proactive-site-advisor'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>