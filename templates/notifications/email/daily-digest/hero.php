<?php
/**
 * Daily digest email: hero section.
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\DailyDigest
 * @since   1.0.0
 *
 * @var int $totalAlerts
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="hero-wrapper" style="padding:0 24px;">
        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0" class="hero" style="width:100%;background:#f3f2f3;border-radius:8px;">
            <tr>
                <td class="hero-inner" style="padding:24px;">
                    <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="hero-label" style="color:#3b82f6;font-size:11px;line-height:16px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;"><?php esc_html_e('Daily Site Scan', 'proactive-site-advisor'); ?></td>
                        </tr>
                        <tr>
                            <td class="hero-title" style="padding-top:6px;color:#444050;font-size:25px;line-height:33px;font-weight:700;"><?php esc_html_e('Your site needs attention', 'proactive-site-advisor'); ?></td>
                        </tr>
                        <tr>
                            <td class="hero-description" style="padding-top:8px;color:#6d6b77;font-size:14px;line-height:21px;">
                                <?php
                                echo esc_html(
                                    sprintf(
                                    /* translators: %1$d: The number of alerts detected. */
                                        _n(
                                            'We detected %1$d unusual pattern during today\'s monitoring cycle. Review the alert below to understand what changed and what you should check next.',
                                            'We detected %1$d unusual patterns during today\'s monitoring cycle. Review the alerts below to understand what changed and what you should check next.',
                                            $totalAlerts,
                                            'proactive-site-advisor'
                                        ),
                                        absint($totalAlerts)
                                    )
                                );
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>