<?php
/**
 * Daily digest email: single alert item.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\DailyDigest
 * @since   1.0.0
 *
 * @var array $alert
 * @var string $rtl
 */

if (!defined('ABSPATH')) {
    exit;
}

$severity = $alert['severity'];

$severityConfig = [
    'critical' => [
        'color' => '#ff4c51',
        'bg'    => '#fff0f1',
    ],
    'warning'  => [
        'color' => '#ff9f43',
        'bg'    => '#fff4e8',
    ],
    'info'     => [
        'color' => '#00bad1',
        'bg'    => '#e9fbfd',
    ],
];

$config        = $severityConfig[$severity];
$severityColor = $config['color'];
$severityBg    = $config['bg'];
?>
<table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0" class="alert-compact" style="background:#ffffff;border:1px solid #e6e6e8;border-radius:8px;border-collapse:separate;border-spacing:0;box-shadow:0 1px 3px 0 rgba(47,43,61,0.08);margin-bottom:12px;">
    <tr>
        <td style="padding:12px 14px;">
            <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="36" valign="top" style="width:36px;text-align:center;vertical-align:top;padding-top:2px;padding-right:<?php echo ($rtl === 'rtl') ? '0' : '10px'; ?>;padding-left:<?php echo ($rtl === 'rtl') ? '10px' : '0'; ?>;">
                        <img src="<?php echo esc_url($alert['icon_url']); ?>" alt="<?php echo esc_attr($alert['label']); ?>" width="20" height="20" style="display:block;margin:0 auto;width:20px;height:20px;">
                    </td>
                    <td valign="top" style="vertical-align:top;">
                        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="alert-title" style="color:#444050;font-size:15px;line-height:21px;font-weight:600;padding-right:<?php echo ($rtl === 'rtl') ? '8px' : '0'; ?>;">
                                    <?php echo esc_html($alert['title']); ?>
                                </td>
                                <td align="<?php echo ($rtl === 'rtl') ? 'left' : 'right'; ?>" style="text-align:<?php echo ($rtl === 'rtl') ? 'left' : 'right'; ?>;vertical-align:top;padding-left:<?php echo ($rtl === 'rtl') ? '0' : '8px'; ?>;padding-right:<?php echo ($rtl === 'rtl') ? '8px' : '0'; ?>;">
                                    <span class="severity-badge" style="display:inline-block;background:<?php echo esc_attr($severityBg); ?>;color:<?php echo esc_attr($severityColor); ?>;font-size:10px;line-height:16px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;padding:0 8px;border-radius:4px;">
                                        <?php echo esc_html($alert['label']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="alert-description" style="padding-top:4px;color:#6d6b77;font-size:12px;line-height:18px;">
                                    <?php echo esc_html($alert['short']); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>