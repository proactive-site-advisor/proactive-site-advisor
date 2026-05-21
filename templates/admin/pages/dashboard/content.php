<?php

/**
 * Template part: Dashboard page content.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var array $status
 * @var array $stats
 * @var array $latestAlerts
 * @var array $history
 * @var bool $showPromoNotice
 */

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Utils\TemplateUtils;

?>
<?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
echo TemplateUtils::renderTemplate(
    'admin/components/status',
    [
        'color'    => $status['color'],
        'title'    => $status['title'],
        'text'     => $status['text'],
        'progress' => $status['progress'] ?? null,
    ]
);
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<div class="psa-page-content">

    <div class="psa-section">
        <h3 class="psa-section__title"><?php esc_html_e('Weekly Digest', 'proactive-site-advisor'); ?></h3>
        <p class="psa-section__description"><?php esc_html_e('Alert summary for the last 7 days.', 'proactive-site-advisor'); ?></p>
        <div class="psa-row">
            <?php foreach ($stats as $stat) : ?>
                <div class="psa-col-12 psa-col-sm-6 psa-col-lg-3">
                    <?php
                    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo TemplateUtils::renderTemplate(
                        'admin/components/stat-card', [
                            'icon'     => $stat['icon'],
                            'label'    => $stat['label'],
                            'subtitle' => $stat['subtitle'],
                            'value'    => $stat['value'],
                            'color'    => $stat['color'],
                            'link'     => $stat['link'] ?? null,
                        ]
                    );
                    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="psa-section">
        <h3 class="psa-section__title"><?php esc_html_e('Latest Alerts', 'proactive-site-advisor'); ?></h3>
        <p class="psa-section__description"><?php esc_html_e('Most recent alerts triggered on your site.', 'proactive-site-advisor'); ?></p>
        <?php if ($latestAlerts['hasData']): ?>
            <?php
            foreach ($latestAlerts['data'] as $alert) :
                // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                echo TemplateUtils::renderTemplate(
                    'admin/components/alert-card',
                    [
                        'id'       => $alert['id'],
                        'icon'     => $alert['icon'],
                        'color'    => $alert['color'],
                        'label'    => $alert['label'],
                        'title'    => $alert['title'],
                        'short'    => $alert['short'],
                        'expanded' => $alert['expanded'],
                        'date'     => $alert['date'],
                    ]
                );
                // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            endforeach;
            ?>
        <?php else: ?>
            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
            echo TemplateUtils::renderTemplate('admin/components/message-card', [
                'title' => $latestAlerts['title'],
                'text'  => $latestAlerts['text'],
                'icon'  => $latestAlerts['icon'],
                'color' => $latestAlerts['color'],
            ]);
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        <?php endif; ?>
    </div>

    <div class="psa-section">
        <h3 class="psa-section__title"><?php esc_html_e('7-Day History', 'proactive-site-advisor'); ?></h3>
        <p class="psa-section__description"><?php esc_html_e('Daily traffic and error statistics.', 'proactive-site-advisor'); ?></p>
        <?php if ($history['hasData']): ?>
            <p class="psa-page-meta psa-mb-2">
                <span class="psa-icon--traffic"></span>
                <?php echo wp_kses_post($history['average']); ?>
            </p>
            <div class="psa-card psa-table-card ">
                <div class="psa-table-responsive">
                    <?php
                    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo TemplateUtils::renderTemplate('admin/components/table', [
                        'columns' => $history['columns'],
                        'rows'    => $history['rows'],
                        'class'   => $history['class'],
                    ]);
                    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>
            </div>
        <?php else: ?>
            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
            echo TemplateUtils::renderTemplate('admin/components/message-card', [
                'title' => $history['title'],
                'text'  => $history['text'],
                'icon'  => $history['icon'],
                'color' => $history['color'],
            ]);
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        <?php endif; ?>
    </div>

    <?php if ($showPromoNotice) : ?>
        <div class="psa-section">
            <?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
            echo TemplateUtils::renderTemplate('admin/components/notices/promo-notice');
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
    <?php endif; ?>

</div>