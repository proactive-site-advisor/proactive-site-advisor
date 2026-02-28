<?php
/**
 * Admin Page: Alerts Dashboard
 *
 * Displays state-aware digest statistics, status summary, latest alerts, and history.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $pageTitle Page title
 * @var string $pageSubtitle Page subtitle/description
 * @var string $statusLine Status line for header (e.g., "Last checked: 2 minutes ago")
 * @var array $statusSummary Status summary box: ['color', 'title', 'text']
 * @var array $digestCards Array of stat card configurations
 * @var array $latestAlerts Latest alerts data: ['type' => 'message'|'alerts', ...]
 * @var array $history History data: ['showTable', 'average', 'rows', 'emptyMessage']
 * @var bool $showPromoBanner Whether to show the promo banner
 * @var string $promoDismissNonce Nonce for promo banner dismiss action
 */

defined('ABSPATH') || exit;

// Set defaults for optional variables
$pageTitle         = $pageTitle ?? esc_html__('Proactive Site Advisor', 'proactive-site-advisor');
$pageSubtitle      = $pageSubtitle ?? esc_html__('Get actionable insights and proactive alerts for your site.', 'proactive-site-advisor');
$statusLine        = $statusLine ?? '';
$statusSummary     = $statusSummary ?? [];
$digestCards       = $digestCards ?? [];
$latestAlerts      = $latestAlerts ?? ['type' => 'message', 'title' => '', 'text' => '', 'helper' => ''];
$history           = $history ?? ['showTable' => true, 'average' => null, 'rows' => [], 'emptyMessage' => ''];
$showPromoBanner   = $showPromoBanner ?? true;
$promoDismissNonce = $promoDismissNonce ?? '';

// Template utility for rendering components
use ProactiveSiteAdvisor\Utils\TemplateUtils;

?>

<!-- Page Header -->
<div class="proactive-site-advisor-page-header">
    <h1 class="proactive-site-advisor-page-title"><?php echo esc_html($pageTitle); ?></h1>
    <?php if (!empty($pageSubtitle)) : ?>
        <p class="proactive-site-advisor-page-description"><?php echo esc_html($pageSubtitle); ?></p>
    <?php endif; ?>
    <?php if (!empty($statusLine)) : ?>
        <p class="proactive-site-advisor-page-status">
            <span class="proactive-site-advisor-icon--clock"></span>
            <?php echo esc_html($statusLine); ?>
        </p>
    <?php endif; ?>
</div>

<!-- Status Summary Box -->
<?php if (!empty($statusSummary['title']) || !empty($statusSummary['text'])) : ?>
    <div class="proactive-site-advisor-status-summary proactive-site-advisor-status-summary--<?php echo esc_attr($statusSummary['color'] ?? 'info'); ?>">
        <div class="proactive-site-advisor-status-summary__content">
            <?php if (!empty($statusSummary['title'])) : ?>
                <strong class="proactive-site-advisor-status-summary__title"><?php echo esc_html($statusSummary['title']); ?></strong>
                <span class="proactive-site-advisor-status-summary__separator">—</span>
            <?php endif; ?>
            <?php if (!empty($statusSummary['text'])) : ?>
                <span class="proactive-site-advisor-status-summary__text"><?php echo esc_html($statusSummary['text']); ?></span>
            <?php endif; ?>

            <?php if (isset($statusSummary['progress'])) : ?>
                <div class="proactive-site-advisor-status-summary__progress">
                    <div class="proactive-site-advisor-status-summary__progress-bar" style="width: <?php echo esc_attr($statusSummary['progress']); ?>%;"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="proactive-site-advisor-page-content">
    <!-- Weekly Digest Section -->
    <div class="proactive-site-advisor-section">
        <h3 class="proactive-site-advisor-section__title"><?php esc_html_e('Weekly Digest', 'proactive-site-advisor'); ?></h3>
        <p class="proactive-site-advisor-section__description"><?php esc_html_e('Alert summary for the last 7 days.', 'proactive-site-advisor'); ?></p>
        <div class="proactive-site-advisor-row">
            <?php foreach ($digestCards as $cardKey => $card) : ?>
                <div class="proactive-site-advisor-col-12 proactive-site-advisor-col-sm-6 proactive-site-advisor-col-lg-3">
                    <?php
                    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in template
                    echo TemplateUtils::renderTemplate('admin/components/stat-card', [
                        'iconClass' => $card['iconClass'] ?? 'proactive-site-advisor-icon--alert',
                        'value'     => $card['value'] ?? '0',
                        'label'     => $card['label'] ?? '',
                        'subtitle'  => $card['subtitle'] ?? '',
                        'color'     => $card['color'] ?? 'primary',
                    ]);
                    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Latest Alerts Section -->
    <div class="proactive-site-advisor-section">
        <h3 class="proactive-site-advisor-section__title"><?php esc_html_e('Latest Alerts', 'proactive-site-advisor'); ?></h3>
        <p class="proactive-site-advisor-section__description"><?php esc_html_e('Most recent alerts triggered on your site.', 'proactive-site-advisor'); ?></p>

        <?php if ($latestAlerts['type'] === 'message') : ?>
            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in template
            echo TemplateUtils::renderTemplate('admin/components/message-card', [
                'title'  => $latestAlerts['title'] ?? '',
                'text'   => $latestAlerts['text'] ?? '',
                'helper' => $latestAlerts['helper'] ?? '',
                'icon'   => $latestAlerts['icon'] ?? 'proactive-site-advisor-icon--info',
                'color'  => $latestAlerts['color'] ?? 'info',
            ]);
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        <?php elseif (!empty($latestAlerts['alerts'])) : ?>
            <div class="proactive-site-advisor-alerts-list">
                <?php
                foreach ($latestAlerts['alerts'] as $alert) :
                    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in template
                    echo TemplateUtils::renderTemplate('admin/components/alert-card', [
                        'iconClass'     => $alert['icon_class'] ?? 'proactive-site-advisor-icon--alert',
                        'severityClass' => $alert['severity_class'] ?? 'proactive-site-advisor-badge--info',
                        'typeLabel'     => $alert['type_label'] ?? __('Alert', 'proactive-site-advisor'),
                        'severity'      => $alert['severity'] ?? 'info',
                        'title'         => $alert['title'] ?? '',
                        'shortMessage'  => $alert['short_message'] ?? ($alert['message'] ?? ''),
                        'expanded'      => $alert['expanded'] ?? [],
                        'alertDate'     => $alert['alert_date'] ?? '',
                    ]);
                    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                endforeach;
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 7-Day History Section -->
    <div class="proactive-site-advisor-section">
        <h3 class="proactive-site-advisor-section__title"><?php esc_html_e('7-Day History', 'proactive-site-advisor'); ?></h3>
        <p class="proactive-site-advisor-section__description"><?php esc_html_e('Daily traffic and error statistics.', 'proactive-site-advisor'); ?></p>

        <?php if ($history['showTable']) : ?>
            <?php if (!empty($history['staleWarning'])) : ?>
                <p class="proactive-site-advisor-history-stale proactive-site-advisor-text-muted proactive-site-advisor-text-sm">
                    <span class="proactive-site-advisor-icon--clock"></span>
                    <?php esc_html_e('Data may be outdated. Last checked over 24 hours ago.', 'proactive-site-advisor'); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($history['average'])) : ?>
                <p class="proactive-site-advisor-history-average">
                    <span class="proactive-site-advisor-icon--traffic"></span>
                    <?php
                    printf(
                    /* translators: 1: average pageviews, 2: average 404 errors */
                        esc_html__('Average per day: %1$s pageviews · %2$s page errors (404)', 'proactive-site-advisor'),
                        '<strong>' . esc_html(number_format_i18n($history['average']['pageviews'])) . '</strong>',
                        '<strong>' . esc_html(number_format_i18n($history['average']['errors_404'])) . '</strong>'
                    );
                    ?>
                </p>
            <?php endif; ?>

            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in template
            echo TemplateUtils::renderTemplate('admin/components/table', [
                'columns'      => [
                    ['key' => 'stats_date', 'label' => __('Date', 'proactive-site-advisor'), 'type' => 'date'],
                    ['key' => 'pageviews', 'label' => __('Pageviews', 'proactive-site-advisor'), 'type' => 'number'],
                    ['key' => 'errors_404', 'label' => __('404 Errors', 'proactive-site-advisor'), 'type' => 'number'],
                ],
                'rows'         => $history['rows'],
                'tableClass'   => 'proactive-site-advisor-table--striped',
                'emptyMessage' => $history['emptyMessage'],
            ]);
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        <?php else : ?>
            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in template
            echo TemplateUtils::renderTemplate('admin/components/message-card', [
                'title'  => $history['title'] ?? __('Building history', 'proactive-site-advisor'),
                'text'   => $history['emptyMessage'],
                'helper' => '',
                'icon'   => $history['icon'] ?? 'proactive-site-advisor-icon--traffic',
                'color'  => 'info',
            ]);
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        <?php endif; ?>
    </div>

    <!-- Pro Box Section (conditionally shown) -->
    <?php if ($showPromoBanner) : ?>
        <div class="proactive-site-advisor-section">
            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in template
            echo TemplateUtils::renderTemplate('admin/components/promo-card', [
                'badge'       => __('Pro', 'proactive-site-advisor'),
                'title'       => __('Never miss a critical issue', 'proactive-site-advisor'),
                'description' => __('Proactive Site Advisor Pro is coming with advanced monitoring and notification features.', 'proactive-site-advisor'),
                'features'    => [
                    __('Performance slowdown alerting', 'proactive-site-advisor'),
                    __('Email & Slack notifications', 'proactive-site-advisor'),
                    __('Custom alert rules & thresholds', 'proactive-site-advisor'),
                    __('Security alerts for suspicious logins and critical changes', 'proactive-site-advisor'),
                ],
                'note'        => __('More advanced features are planned for future versions.', 'proactive-site-advisor'),
                'buttonText'  => __('See what’s coming in Proactive Site Advisor Pro', 'proactive-site-advisor'),
                'buttonUrl'   => '#',
                'dismissible' => true,
            ]);
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
    <?php endif; ?>
</div>
