<?php
/**
 * Daily digest email: CTA section.
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\DailyDigest
 * @since   1.0.0
 *
 * @var string $dashboardUrl
 * @var string $settingsUrl
 * @var string $rtl
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td style="padding:22px 24px 30px;text-align:center;">
        <table border="0" role="presentation" cellpadding="0" cellspacing="0" align="center">
            <tr>
                <td align="center" style="padding:0;">
                    <a href="<?php echo esc_url($dashboardUrl); ?>" target="_blank" dir="<?php echo esc_attr($rtl); ?>" class="cta-button" style="display:inline-block;padding:8px 20px;background:#3b82f6 !important;border-radius:6px;color:#ffffff !important;font-size:13px;line-height:20px;font-weight:600;text-decoration:none;unicode-bidi:plaintext;outline:none !important;-ms-high-contrast-adjust:none !important;">
                        <?php esc_html_e('Open Site Advisor', 'proactive-site-advisor'); ?>
                    </a>
                </td>
            </tr>
        </table>

        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="height:10px;line-height:10px;font-size:0;">&nbsp;</td>
            </tr>
        </table>

        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding:0;">
                    <a href="<?php echo esc_url($settingsUrl); ?>" target="_blank" style="color:#3b82f6;font-size:12px;text-decoration:none;">
                        <?php esc_html_e('Notification settings', 'proactive-site-advisor'); ?>
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>