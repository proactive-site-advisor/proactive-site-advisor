<?php
/**
 * Component: Alert card.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor\Templates\Admin\Components
 * @since   1.0.0
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

                <span class="psa-alert-card__date">
                    <?php echo esc_html($date); ?>
                </span>
            </div>

            <h5 class="psa-alert-card__title">
                <?php echo esc_html($title); ?>
            </h5>

            <p class="psa-alert-card__message">
                <?php echo esc_html($short); ?>
            </p>


            <div id="<?php echo esc_attr($id); ?>-details"
                 class="psa-alert-card__details"
                 hidden>


                <!-- Context -->
                <div class="psa-alert-card__section">
                    <h6 class="psa-alert-card__section-title">
                        <?php esc_html_e('What this means', 'proactive-site-advisor'); ?>
                    </h6>

                    <p class="psa-alert-card__section-text">
                        <?php echo esc_html($expanded['context']); ?>
                    </p>
                </div>

                <!-- Severity -->
                <div class="psa-alert-card__section">
                    <h6 class="psa-alert-card__section-title">
                        <?php esc_html_e('Why this alert?', 'proactive-site-advisor'); ?>
                    </h6>

                    <p class="psa-alert-card__section-text">
                        <?php echo esc_html($expanded['severity']['text']); ?>
                    </p>

                    <p class="psa-page-meta psa-mt-2">
                        <?php
                        $metrics     = $expanded['severity']['metrics'];
                        $changeSign  = $metrics['change'] > 0 ? '+' : '';
                        $changeValue = $changeSign . number_format_i18n(round($metrics['change'], 1), 1);

                        printf(
                        /* translators: 1: Today's value, 2: 7-day average value, 3: Percentage change with % sign */
                            esc_html__('Today: %1$s · 7-day average: %2$s · Change: %3$s', 'proactive-site-advisor'),
                            '<strong>' . esc_html(number_format_i18n($metrics['today'])) . '</strong>',
                            '<strong>' . esc_html(number_format_i18n($metrics['avg7'])) . '</strong>',
                            '<strong>' . esc_html($changeValue) . '%</strong>'
                        );
                        ?>
                    </p>
                </div>

                <!-- Pattern -->
                <?php if (!empty($expanded['pattern'])) : ?>
                    <div class="psa-alert-card__section">

                        <h6 class="psa-alert-card__section-title">
                            <?php esc_html_e('Pattern', 'proactive-site-advisor'); ?>
                        </h6>

                        <p class="psa-alert-card__section-text">
                            <?php echo esc_html($expanded['pattern']); ?>
                        </p>

                    </div>
                <?php endif; ?>


                <!-- Concurrent -->
                <?php if (!empty($expanded['concurrent'])) : ?>
                    <div class="psa-alert-card__section">

                        <h6 class="psa-alert-card__section-title">
                            <?php esc_html_e('Related activity', 'proactive-site-advisor'); ?>
                        </h6>

                        <p class="psa-alert-card__section-text">
                            <?php echo esc_html($expanded['concurrent']); ?>
                        </p>

                    </div>
                <?php endif; ?>


                <!-- Checks -->
                <div class="psa-alert-card__section">

                    <h6 class="psa-alert-card__section-title">
                        <?php esc_html_e('What you should check next', 'proactive-site-advisor'); ?>
                    </h6>

                    <ul class="psa-alert-card__checklist">

                        <?php foreach ($expanded['checks'] as $check) : ?>

                            <li>
                                <?php echo esc_html($check); ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>


                <!-- Top 404 URLs -->
                <?php if (!empty($expanded['topUrls'])) : ?>

                    <div class="psa-alert-card__section">

                        <h6 class="psa-alert-card__section-title">
                            <?php esc_html_e('Top 404 URLs', 'proactive-site-advisor'); ?>
                        </h6>

                        <ul class="psa-alert-card__url-list">

                            <?php foreach ($expanded['topUrls'] as $urlItem) : ?>

                                <li>
                                    <code class="psa-alert-card__url-path">
                                        <?php echo esc_html($urlItem['path']); ?>
                                    </code>

                                    <span class="psa-alert-card__url-count">
                                        <?php echo esc_html(number_format_i18n($urlItem['count'])); ?>
                                    </span>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <!-- Top Bots -->
                <?php if (!empty($expanded['topBots'])) : ?>

                    <div class="psa-alert-card__section">

                        <h6 class="psa-alert-card__section-title">
                            <?php esc_html_e('Top Bots', 'proactive-site-advisor'); ?>
                        </h6>

                        <ul class="psa-alert-card__url-list">

                            <?php foreach ($expanded['topBots'] as $botItem) : ?>

                                <li>

                                    <span class="psa-alert-card__url-path">
                                        <?php echo esc_html($botItem['name']); ?>
                                    </span>

                                    <span class="psa-alert-card__url-count">
                                        <?php echo esc_html(number_format_i18n($botItem['count'])); ?>
                                    </span>

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