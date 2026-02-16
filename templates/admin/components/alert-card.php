<?php
/**
 * Alert Card Component
 *
 * Displays a collapsible alert with icon, severity badge, title, short message,
 * and expandable details section.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $iconClass CSS class for the icon
 * @var string $severityClass CSS class for severity badge
 * @var string $typeLabel Human-readable type label (e.g., 'Traffic Drop')
 * @var string $severity Alert severity: 'info', 'warning', 'critical'
 * @var string $title Alert title
 * @var string $message Alert message/description (legacy, use short_message for new code)
 * @var string $shortMessage Short collapsed message
 * @var array  $expanded Expanded content: ['meaning' => string, 'checks' => array]
 * @var string $alertDate Date of the alert (Y-m-d format)
 */

defined('ABSPATH') || exit;

$iconClass     = $iconClass ?? 'proactive-site-advisor-icon--alert';
$severityClass = $severityClass ?? 'proactive-site-advisor-badge--info';
$typeLabel     = $typeLabel ?? __('Alert', 'proactive-site-advisor');
$severity      = $severity ?? 'info';
$title         = $title ?? '';
$message       = $message ?? '';
$shortMessage  = $shortMessage ?? $message;
$expanded      = $expanded ?? [];
$alertDate     = $alertDate ?? '';

// Format the date for display
$formattedDate = '';
if (!empty($alertDate)) {
    $timestamp = strtotime($alertDate);
    if ($timestamp !== false) {
        $formattedDate = wp_date(get_option('date_format'), $timestamp);
    }
}

// Check if card has expandable content
$hasExpanded = !empty($expanded['meaning']) || !empty($expanded['checks']) || !empty($expanded['topUrls']);

// Generate unique ID for accessibility
$cardId = 'proactive-site-advisor-alert-' . wp_unique_id();
?>

<div class="proactive-site-advisor-card proactive-site-advisor-alert-card proactive-site-advisor-alert-card--<?php echo esc_attr($severity); ?><?php echo $hasExpanded ? ' proactive-site-advisor-alert-card--collapsible' : ''; ?>">
    <div class="proactive-site-advisor-alert-card__body">
        <div class="proactive-site-advisor-alert-card__icon">
            <span class="<?php echo esc_attr($iconClass); ?>"></span>
        </div>
        <div class="proactive-site-advisor-alert-card__content">
            <div class="proactive-site-advisor-alert-card__header">
                <span class="proactive-site-advisor-badge <?php echo esc_attr($severityClass); ?>">
                    <?php echo esc_html($typeLabel); ?>
                </span>
                <?php if (!empty($formattedDate)) : ?>
                    <span class="proactive-site-advisor-alert-card__date"><?php echo esc_html($formattedDate); ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($title)) : ?>
                <h5 class="proactive-site-advisor-alert-card__title"><?php echo esc_html($title); ?></h5>
            <?php endif; ?>
            <?php if (!empty($shortMessage)) : ?>
                <p class="proactive-site-advisor-alert-card__message"><?php echo esc_html($shortMessage); ?></p>
            <?php endif; ?>

            <?php if ($hasExpanded) : ?>
                <div id="<?php echo esc_attr($cardId); ?>-details" class="proactive-site-advisor-alert-card__details" hidden>
                    <?php if (!empty($expanded['meaning'])) : ?>
                        <div class="proactive-site-advisor-alert-card__section">
                            <h6 class="proactive-site-advisor-alert-card__section-title"><?php esc_html_e('What this means', 'proactive-site-advisor'); ?></h6>
                            <p class="proactive-site-advisor-alert-card__section-text"><?php echo esc_html($expanded['meaning']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($expanded['checks']) && is_array($expanded['checks'])) : ?>
                        <div class="proactive-site-advisor-alert-card__section">
                            <h6 class="proactive-site-advisor-alert-card__section-title"><?php esc_html_e('What you should check next', 'proactive-site-advisor'); ?></h6>
                            <ul class="proactive-site-advisor-alert-card__checklist">
                                <?php foreach ($expanded['checks'] as $check) : ?>
                                    <li><?php echo esc_html($check); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($expanded['topUrls']) && is_array($expanded['topUrls'])) : ?>
                        <div class="proactive-site-advisor-alert-card__section">
                            <h6 class="proactive-site-advisor-alert-card__section-title"><?php esc_html_e('Top 404 URLs', 'proactive-site-advisor'); ?></h6>
                            <ul class="proactive-site-advisor-alert-card__url-list">
                                <?php foreach ($expanded['topUrls'] as $urlItem) : ?>
                                    <li>
                                        <code class="proactive-site-advisor-alert-card__url-path"><?php echo esc_html($urlItem['path']); ?></code>
                                        <span class="proactive-site-advisor-alert-card__url-count"><?php echo esc_html(number_format_i18n($urlItem['count'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($hasExpanded) : ?>
            <button
                type="button"
                class="proactive-site-advisor-alert-card__toggle"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr($cardId); ?>-details"
                aria-label="<?php esc_attr_e('Toggle details', 'proactive-site-advisor'); ?>"
            >
                <span class="proactive-site-advisor-icon--chevron-down"></span>
            </button>
        <?php endif; ?>
    </div>
</div>
