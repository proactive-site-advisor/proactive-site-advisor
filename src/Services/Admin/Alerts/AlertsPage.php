<?php

namespace ProactiveSiteAdvisor\Services\Admin\Alerts;

use ProactiveSiteAdvisor\Abstracts\AbstractAdminPage;
use ProactiveSiteAdvisor\Admin\PromoBanner;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AlertsPage
 *
 * Admin page for displaying dashboard.
 *
 * @package ProactiveSiteAdvisor\Services\Admin\Alerts
 * @version 1.0.0
 */
class AlertsPage extends AbstractAdminPage
{
    /**
     * Returns the template path for the alerts page.
     *
     * @return string
     */
    protected function getTemplate(): string
    {
        return 'admin/pages/alerts';
    }

    /**
     * Returns the context array for the alerts page body.
     *
     * Uses AlertsPageContext to build state-aware section data.
     *
     * @return array
     */
    protected function getBodyContext(): array
    {
        $context = new AlertsPageContext();

        // Determine whether promo banner should be shown (disabled for initial release)
        $showPromoBanner = PromoBanner::shouldShowBanner();

        return [
            'pageTitle'       => __('Proactive Site Advisor', 'proactive-site-advisor'),
            'pageSubtitle'    => __('Unusual activity on your site — with recommended actions.', 'proactive-site-advisor'),
            'statusLine'      => $context->getStatusLine(),
            'statusSummary'   => $context->getStatusSummary(),
            'digestCards'     => $context->getDigestCards(),
            'latestAlerts'    => $context->getLatestAlerts(),
            'history'         => $context->getHistory(),
            // Promo banner intentionally disabled for the initial public release
            'showPromoBanner' => false,
        ];
    }
}