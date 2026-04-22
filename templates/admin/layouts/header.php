<?php
/**
 * Admin layout: Header.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var string $title
 * @var string $titleLink
 * @var string $version
 * @var string $logoUrl
 * @var array $navItems
 * @var array $actions
 * @var string $theme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap psa-wrap">
    <header class="psa-header">
        <div class="psa-header-container">
            <!-- Brand Section (Logo/Title) -->
            <div class="psa-header-brand">
                <a href="<?php echo esc_url($titleLink); ?>" class="psa-header-brand-link">
                    <?php if (!empty($logoUrl)) : ?>
                        <img
                            src="<?php echo esc_url($logoUrl); ?>"
                            alt="<?php echo esc_attr($title); ?>"
                            class="psa-header-logo"
                        />
                    <?php endif; ?>
                    <span class="psa-header-title"><?php echo esc_html($title); ?></span>
                    <span class="psa-badge psa-badge--secondary psa-header-version">
                        v<?php echo esc_html($version); ?>
                    </span>
                </a>
            </div>

            <!-- Navigation Section -->
            <?php if (!empty($navItems)) : ?>
                <!-- Desktop Navigation (visible on large screens) -->
                <nav class="psa-header-nav psa-header-nav-desktop">
                    <ul class="psa-header-nav-list">
                        <?php foreach ($navItems as $navItem) :
                            $itemClasses = ['psa-header-nav-item'];
                            if (!empty($navItem['active'])) {
                                $itemClasses[] = 'psa-active';
                            }
                            ?>
                            <li class="<?php echo esc_attr(implode(' ', $itemClasses)); ?>">
                                <a
                                    href="<?php echo esc_url($navItem['url'] ?? '#'); ?>"
                                    class="psa-header-nav-link"
                                >
                                    <?php if (!empty($navItem['icon'])) : ?>
                                        <span class="psa-dashicons <?php echo esc_attr($navItem['icon']); ?>"></span>
                                    <?php endif; ?>

                                    <span class="psa-header-nav-text">
                                        <?php echo esc_html($navItem['label'] ?? ''); ?>
                                    </span>

                                    <?php if (!empty($navItem['badge'])) : ?>
                                        <span class="psa-badge psa-badge-primary psa-badge-sm">
                                            <?php echo esc_html($navItem['badge']); ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <!-- Mobile Navigation (toggle + dropdown) -->
                <div class="psa-header-nav-wrapper">
                    <button
                        type="button"
                        class="psa-header-toggle"
                        aria-label="<?php esc_attr_e('Toggle navigation', 'proactive-site-advisor'); ?>"
                        aria-expanded="false"
                        aria-controls="psa-header-nav"
                    >
                        <span class="psa-header-toggle-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>

                    <nav class="psa-header-nav" id="psa-header-nav">
                        <ul class="psa-header-nav-list">
                            <?php foreach ($navItems as $navItem) :
                                $itemClasses = ['psa-header-nav-item'];
                                if (!empty($navItem['active'])) {
                                    $itemClasses[] = 'psa-active';
                                }
                                ?>
                                <li class="<?php echo esc_attr(implode(' ', $itemClasses)); ?>">
                                    <a
                                        href="<?php echo esc_url($navItem['url'] ?? '#'); ?>"
                                        class="psa-header-nav-link"
                                    >
                                        <?php if (!empty($navItem['icon'])) : ?>
                                            <span class="psa-header-nav-icon <?php echo esc_attr($navItem['icon']); ?>"></span>
                                        <?php endif; ?>

                                        <span class="psa-header-nav-text">
                                            <?php echo esc_html($navItem['label'] ?? ''); ?>
                                        </span>

                                        <?php if (!empty($navItem['badge'])) : ?>
                                            <span class="psa-badge psa-badge-primary psa-badge-sm">
                                                <?php echo esc_html($navItem['badge']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

            <!-- Actions Section -->
            <?php if (!empty($actions)) : ?>
                <div class="psa-header-actions">
                    <?php foreach ($actions as $action) :
                        $type = $action['type'] ?? 'button';
                        $variant = $action['variant'] ?? 'primary';
                        $label = $action['label'] ?? '';
                        $url = $action['url'] ?? '#';
                        $icon = $action['icon'] ?? '';
                        $attrs = $action['attrs'] ?? [];

                        // Build class string
                        $btnClass = 'psa-btn psa-btn-' . esc_attr($variant);
                        if (!empty($action['class'])) {
                            $btnClass .= ' ' . esc_attr($action['class']);
                        }

                        // Build additional attributes string
                        $attrsStr = '';
                        foreach ($attrs as $attrKey => $attrVal) {
                            $attrsStr .= ' ' . esc_attr($attrKey) . '="' . esc_attr($attrVal) . '"';
                        }
                        ?>
                        <?php if ($type === 'link') : ?>
                        <a
                            href="<?php echo esc_url($url); ?>"
                            class="<?php echo esc_attr($btnClass); ?>"
                            <?php echo $attrsStr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        >
                            <?php if (!empty($icon)) : ?>
                                <span class="psa-dashicons <?php echo esc_attr($icon); ?>"></span>
                            <?php endif; ?>
                            <span class="psa-btn-text"><?php echo esc_html($label); ?></span>
                        </a>
                    <?php else : ?>
                        <button
                            type="button"
                            class="<?php echo esc_attr($btnClass); ?>"
                            <?php echo $attrsStr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        >
                            <?php if (!empty($icon)) : ?>
                                <span class="psa-dashicons <?php echo esc_attr($icon); ?>"></span>
                            <?php endif; ?>
                            <span class="psa-btn-text"><?php echo esc_html($label); ?></span>
                        </button>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="psa-content-wrapper">