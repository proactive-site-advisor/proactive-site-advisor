<?php
/**
 * Email layout: styles.
 *
 * @package ProactiveSiteAdvisor\Templates\Notifications\Email\Layout
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: 100% !important;
    }

    body {
        background: #f8f7fa;
        color: #6d6b77;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 15px;
        line-height: 1.375;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }

    table {
        border-spacing: 0;
        border-collapse: collapse;
    }

    td, th {
        border: 0;
    }

    img {
        border: 0;
        outline: none;
        text-decoration: none;
    }

    a {
        color: #3b82f6;
        text-decoration: none;
    }

    .email-wrapper {
        width: 100%;
        background: #f8f7fa;
    }

    .email-container {
        width: 600px;
        max-width: 600px;
    }

    .main-card {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e6e6e8;
        border-radius: 8px;
        box-shadow: 0 3px 12px 0 rgba(47, 43, 61, 0.14);
    }

    .header {
        padding: 24px;
        background: #ffffff;
    }

    .brand-name {
        color: #444050;
        font-size: 15px;
        line-height: 20px;
        font-weight: 600;
    }

    .brand-site {
        padding-top: 2px;
        color: #acaab1;
        font-size: 12px;
        line-height: 17px;
    }

    .scan-date {
        color: #97959e;
        font-size: 12px;
        line-height: 18px;
        text-align: right;
    }

    .brand-icon-wrapper {
        width: 40px;
        height: 40px;
        background: #f3f2f3;
        border-radius: 8px;
    }

    .hero-wrapper {
        padding: 0 24px;
    }

    .hero {
        width: 100%;
        background: #f3f2f3;
        border-radius: 8px;
    }

    .hero-inner {
        padding: 24px;
    }

    .hero-label {
        color: #3b82f6;
        font-size: 11px;
        line-height: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .hero-title {
        padding-top: 6px;
        color: #444050;
        font-size: 25px;
        line-height: 33px;
        font-weight: 700;
    }

    .hero-description {
        padding-top: 8px;
        color: #6d6b77;
        font-size: 14px;
        line-height: 21px;
    }

    .alerts-wrapper {
        padding: 18px 24px 8px;
    }

    .section-title {
        color: #444050;
        font-size: 16px;
        line-height: 22px;
        font-weight: 600;
    }

    .section-description {
        padding-top: 4px;
        color: #acaab1;
        font-size: 12px;
        line-height: 18px;
    }

    .alert-compact {
        background: #ffffff;
        border: 1px solid #e6e6e8;
        border-radius: 8px;
        border-collapse: separate;
        border-spacing: 0;
        box-shadow: 0 1px 3px 0 rgba(47, 43, 61, 0.08);
        margin-bottom: 12px;
    }

    .alert-compact .alert-title {
        color: #444050;
        font-size: 15px;
        line-height: 21px;
        font-weight: 600;
    }

    .alert-compact .alert-description {
        padding-top: 4px;
        color: #6d6b77;
        font-size: 12px;
        line-height: 18px;
    }

    .severity-badge {
        display: inline-block;
        font-size: 10px;
        line-height: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0 8px;
        border-radius: 4px;
        white-space: nowrap;
    }

    .cta {
        padding: 22px 24px 30px;
        text-align: center;
    }

    .footer {
        padding: 0 24px 24px;
        text-align: center;
    }

    .cta-button {
        display: inline-block;
        padding: 8px 20px;
        background: #3b82f6;
        border-radius: 6px;
        color: #ffffff !important;
        font-size: 13px;
        line-height: 20px;
        font-weight: 600;
        text-decoration: none;
    }

    [dir="rtl"] {
        text-align: right;
    }

    [dir="rtl"] .scan-date {
        text-align: left;
    }

    /* Responsive */
    @media screen and (max-width: 640px) {
        .email-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .main-card {
            border-radius: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
        }

        .header,
        .alerts-wrapper,
        .cta,
        .footer {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .hero-wrapper {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .hero-inner {
            padding: 20px !important;
        }

        .hero-title {
            font-size: 23px !important;
            line-height: 31px !important;
        }
    }
</style>