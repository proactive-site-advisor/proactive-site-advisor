<?php
/**
 * List Card Component
 *
 * Displays a card with title and a simple list of items.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $iconClass Optional icon class for header
 * @var string $title Card title
 * @var string $description Optional description text
 * @var array $items Array of items with 'label' and optional 'value' keys
 * @var string $emptyMessage Message when list is empty
 */

defined('ABSPATH') || exit;

$iconClass    = $iconClass ?? '';
$title        = $title ?? '';
$description  = $description ?? '';
$items        = $items ?? [];
$emptyMessage = $emptyMessage ?? __('No data available.', 'proactive-site-advisor');
?>

<div class="proactive-site-advisor-card proactive-site-advisor-list-card">
    <?php if (!empty($title)) : ?>
        <div class="proactive-site-advisor-list-card__header">
            <?php if (!empty($iconClass)) : ?>
                <span class="proactive-site-advisor-list-card__icon <?php echo esc_attr($iconClass); ?>"></span>
            <?php endif; ?>
            <div class="proactive-site-advisor-list-card__header-content">
                <h4 class="proactive-site-advisor-list-card__title"><?php echo esc_html($title); ?></h4>
                <?php if (!empty($description)) : ?>
                    <p class="proactive-site-advisor-list-card__description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="proactive-site-advisor-list-card__body">
        <?php if (!empty($items)) : ?>
            <ul class="proactive-site-advisor-list-card__list">
                <?php foreach ($items as $item) : ?>
                    <li class="proactive-site-advisor-list-card__item">
                        <span class="proactive-site-advisor-list-card__label"><?php echo esc_html($item['label'] ?? ''); ?></span>
                        <?php if (isset($item['value'])) : ?>
                            <span class="proactive-site-advisor-list-card__value"><?php echo esc_html($item['value']); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="proactive-site-advisor-list-card__empty"><?php echo esc_html($emptyMessage); ?></p>
        <?php endif; ?>
    </div>
</div>
