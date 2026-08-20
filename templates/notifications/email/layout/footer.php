<?php
/**
 * Email layout: footer.
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\Layout
 * @since   1.0.0
 *
 * @var string $siteUrl
 * @var string $siteHost
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="footer" style="padding:0 24px 24px;text-align:center;">
        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 auto;">
            <tr>
                <td align="center" style="color:#acaab1;font-size:11px;line-height:17px;text-align:center;"><?php esc_html_e('This report was generated automatically by Proactive Site Advisor after your daily site scan.', 'proactive-site-advisor'); ?></td>
            </tr>
            <tr>
                <td align="center" style="padding-top:6px;color:#acaab1;font-size:11px;line-height:17px;text-align:center;"><?php esc_html_e('Your monitoring data stays on your WordPress site. No external analytics service is required.', 'proactive-site-advisor'); ?></td>
            </tr>
            <tr>
                <td align="center" style="padding-top:8px;color:#acaab1;font-size:11px;line-height:17px;text-align:center;">
                    <a href="<?php echo esc_url($siteUrl); ?>" target="_blank" style="color:#3b82f6;"><?php echo esc_html($siteHost); ?></a>
                </td>
            </tr>
        </table>
    </td>
</tr>