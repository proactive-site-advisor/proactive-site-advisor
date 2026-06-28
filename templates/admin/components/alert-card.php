<?php
/**
 * Component: Alert card.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var int $id
 * @var string $icon
 * @var string $color
 * @var string $label
 * @var string $title
 * @var string $short
 * @var array $expanded
 * @var string $date
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="psa-card psa-alert-card psa-alert-card--<?php echo esc_attr($color); ?> psa-alert-card--collapsible">
    <div class="psa-alert-card__body">
        <div class="psa-alert-card__icon">
            <span class="<?php echo esc_attr($icon); ?>"></span>
        </div>
        <div class="psa-alert-card__content">
            <div class="psa-alert-card__header">
                <span class="psa-badge psa-badge--<?php echo esc_attr($color); ?>">
                    <?php echo esc_html($label); ?>
                </span>
                <span class="psa-alert-card__date"><?php echo esc_html($date); ?></span>
            </div>
            <h5 class="psa-alert-card__title"><?php echo esc_html($title); ?></h5>
            <p class="psa-alert-card__message"><?php echo esc_html($short); ?></p>

            <div id="<?php echo esc_attr($id); ?>-details" class="psa-alert-card__details" hidden>
                <div class="psa-alert-card__section">
                    <h6 class="psa-alert-card__section-title"><?php esc_html_e('What this means', 'proactive-site-advisor'); ?></h6>
                    <p class="psa-alert-card__section-text"><?php echo esc_html($expanded['meaning']); ?></p>
                </div>

                <div class="psa-alert-card__section">
                    <h6 class="psa-alert-card__section-title"><?php esc_html_e('What you should check next', 'proactive-site-advisor'); ?></h6>
                    <ul class="psa-alert-card__checklist">
                        <?php foreach ($expanded['checks'] as $check) : ?>
                            <li><?php echo esc_html($check); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if (!empty($expanded['topUrls'])): ?>
                    <div class="psa-alert-card__section">
                        <h6 class="psa-alert-card__section-title"><?php esc_html_e('Top 404 URLs', 'proactive-site-advisor'); ?></h6>
                        <ul class="psa-alert-card__url-list">
                            <?php foreach ($expanded['topUrls'] as $urlItem) : ?>
                                <li>
                                    <code class="psa-alert-card__url-path"><?php echo esc_html($urlItem['path']); ?></code>
                                    <span class="psa-alert-card__url-count"><?php echo esc_html(number_format_i18n($urlItem['count'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($expanded['topBots'])): ?>
                    <div class="psa-alert-card__section">
                        <h6 class="psa-alert-card__section-title"><?php esc_html_e('Top Bots', 'proactive-site-advisor'); ?></h6>
                        <ul class="psa-alert-card__url-list">
                            <?php foreach ($expanded['topBots'] as $botItem) : ?>
                                <li>
                                    <span class="psa-alert-card__url-path"><?php echo esc_html($botItem['name']); ?></span>
                                    <span class="psa-alert-card__url-count"><?php echo esc_html(number_format_i18n($botItem['count'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <button
            type="button"
            class="psa-alert-card__toggle"
            aria-expanded="false"
            aria-controls="<?php echo esc_attr($id); ?>-details"
            aria-label="<?php esc_attr_e('Toggle details', 'proactive-site-advisor'); ?>"
        >
            <span class="psa-icon--chevron-down"></span>
        </button>
    </div>
</div>