<?php
/**
 * Email layout: header.
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\Layout
 * @since   1.0.0
 *
 * @var string $rtl
 * @var string $brandIcon
 * @var string $siteHost
 * @var string $date
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="header" dir="<?php echo esc_attr($rtl); ?>" style="padding:24px;background:#ffffff;direction:<?php echo ($rtl === 'rtl') ? 'rtl' : 'ltr'; ?>;text-align:<?php echo ($rtl === 'rtl') ? 'right' : 'left'; ?>;">
        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td valign="middle">
                    <table border="0" role="presentation" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="40" valign="middle" style="text-align:center;vertical-align:middle;">
                                <table border="0" role="presentation" cellpadding="0" cellspacing="0" width="40" height="40" class="brand-icon-wrapper" style="width:40px;height:40px;background:#f3f2f3;border-radius:8px;">
                                    <tr>
                                        <td align="center" valign="middle" style="width:40px;height:40px;line-height:0;font-size:0;padding:0;margin:0;">
                                            <img src="<?php echo esc_url($brandIcon); ?>"
                                                 alt="<?php esc_attr_e('Proactive Site Advisor', 'proactive-site-advisor'); ?>"
                                                 width="22" height="22"
                                                 style="display:block;margin:0 auto;width:22px;height:22px;">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td valign="middle" style="padding:0; <?php echo ($rtl === 'rtl') ? 'padding-right:10px;' : 'padding-left:10px;'; ?>">
                                <table border="0" role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="brand-name" style="color:#444050;font-size:15px;line-height:20px;font-weight:600;"><?php esc_html_e('Proactive Site Advisor', 'proactive-site-advisor'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="brand-site" style="padding-top:2px;color:#acaab1;font-size:12px;line-height:17px;"><?php echo esc_html($siteHost); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td valign="middle" class="scan-date" style="color:#97959e;font-size:12px;line-height:18px;text-align:<?php echo ($rtl === 'rtl') ? 'left' : 'right'; ?>;"><?php echo esc_html($date); ?></td>
            </tr>
        </table>
    </td>
</tr>