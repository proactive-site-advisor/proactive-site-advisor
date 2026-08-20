<?php
/**
 * Email layout: opening HTML.
 *
 * All output is captured by TemplateUtils::renderTemplate() and
 * escaped late via wp_kses() in AbstractAdminPage::render().
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\Layout
 * @since   1.0.0
 *
 * @var string $locale
 * @var string $rtl
 * @var string $date
 * @var int $totalAlerts
 */

use ProactiveSiteAdvisor\Utils\TemplateUtils;

if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html lang="<?php echo esc_attr($locale); ?>" dir="<?php echo esc_attr($rtl); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title><?php esc_html_e('Proactive Site Advisor — Daily Digest', 'proactive-site-advisor'); ?></title>
    <?php echo TemplateUtils::renderTemplate('notifications/email/layout/styles'); ?>
</head>

<body dir="<?php echo esc_attr($rtl); ?>" style="direction: <?php echo ($rtl === 'rtl') ? 'rtl' : 'ltr'; ?>; text-align: <?php echo ($rtl === 'rtl') ? 'right' : 'left'; ?>; background:#f8f7fa; margin:0; padding:0;">

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;opacity:0;color:#f8f7fa;">
    <?php
    echo esc_html(
        sprintf(
        /* translators: 1: Number of alerts, 2: Date (Y-m-d). */
            _n(
                '%1$d alert detected on %2$s',
                '%1$d alerts detected on %2$s',
                $totalAlerts,
                'proactive-site-advisor'
            ),
            absint($totalAlerts),
            esc_html($date)
        )
    );
    ?>
</div>

<table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-wrapper" dir="<?php echo esc_attr($rtl); ?>" style="background:#f8f7fa;width:100%;direction:<?php echo ($rtl === 'rtl') ? 'rtl' : 'ltr'; ?>;">
    <tr>
        <td align="center" valign="top" style="padding:32px 12px;">
            <table border="0" role="presentation" width="600" cellpadding="0" cellspacing="0" class="email-container" style="width:600px;max-width:600px;">
                <tr>
                    <td>
                        <table border="0" role="presentation" width="100%" cellpadding="0" cellspacing="0" class="main-card" dir="<?php echo esc_attr($rtl); ?>" style="background:#ffffff;border:1px solid #e6e6e8;border-radius:8px;box-shadow:0 3px 12px 0 rgba(47,43,61,0.14);direction:<?php echo ($rtl === 'rtl') ? 'rtl' : 'ltr'; ?>;">