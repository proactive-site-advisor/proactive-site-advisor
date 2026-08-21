<?php
/**
 * Template: Daily digest email.
 *
 * All output is captured by TemplateUtils::renderTemplate() and
 * escaped late via wp_kses() in AbstractAdminPage::render().
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email
 * @since   1.0.0
 *
 * @var string $date
 * @var string $siteUrl
 * @var string $siteHost
 * @var string $dashboardUrl
 * @var string $settingsUrl
 * @var array $alerts
 * @var int $totalAlerts
 * @var string $rtl
 * @var string $locale
 * @var string $brandIcon
 */

use ProactiveSiteAdvisor\Utils\TemplateUtils;

if (!defined('ABSPATH')) {
    exit;
}

echo TemplateUtils::renderTemplate('notifications/email/layout/html-open', [
    'locale'      => $locale,
    'rtl'         => $rtl,
    'date'        => $date,
    'totalAlerts' => $totalAlerts,
]);

echo TemplateUtils::renderTemplate('notifications/email/layout/header', [
    'rtl'       => $rtl,
    'brandIcon' => $brandIcon,
    'siteHost'  => $siteHost,
    'date'      => $date,
]);

echo TemplateUtils::renderTemplate('notifications/email/daily-digest/hero', [
    'totalAlerts' => $totalAlerts,
]);
?>
    <!-- ALERTS -->
    <tr>
        <td class="alerts-wrapper" style="padding:18px 24px 8px;text-align:<?php echo ($rtl === 'rtl') ? 'right' : 'left'; ?>;">
            <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="section-title" style="color:#444050;font-size:16px;line-height:22px;font-weight:600;"><?php esc_html_e('What we found', 'proactive-site-advisor'); ?></td>
                </tr>
                <tr>
                    <td class="section-description" style="padding-top:4px;color:#acaab1;font-size:12px;line-height:18px;"><?php esc_html_e('These patterns are unusual compared with your recent site activity.', 'proactive-site-advisor'); ?></td>
                </tr>
            </table>

            <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="height:12px;line-height:12px;font-size:0;">&nbsp;</td>
                </tr>
            </table>

            <?php foreach ($alerts as $alert) : ?>
                <?php echo TemplateUtils::renderTemplate('notifications/email/daily-digest/alert-item', [
                    'alert' => $alert,
                    'rtl'   => $rtl,
                ]); ?>
            <?php endforeach; ?>
        </td>
    </tr>
<?php

echo TemplateUtils::renderTemplate('notifications/email/daily-digest/cta', [
    'dashboardUrl' => $dashboardUrl,
    'settingsUrl'  => $settingsUrl,
    'rtl'          => $rtl,
]);

echo TemplateUtils::renderTemplate('notifications/email/layout/footer', [
    'siteUrl'  => $siteUrl,
    'siteHost' => $siteHost,
]);

echo TemplateUtils::renderTemplate('notifications/email/layout/html-close');