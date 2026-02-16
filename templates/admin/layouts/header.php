<?php
/**
 * Admin Layout: Header
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Set defaults for optional variables
$logoUrl   = $logoUrl ?? '';
$title     = $title ?? esc_html__('Proactive Site Advisor', 'proactive-site-advisor');
$titleLink = $titleLink ?? '';
$navItems  = $navItems ?? [];
$actions   = $actions ?? [];
$version   = $version ?? '';
$theme     = $theme ?? '';
?>

<div class="wrap proactive-site-advisor-wrap" data-proactive-site-advisor-theme="<?php echo esc_attr($theme); ?>">
    <header class="proactive-site-advisor-header">
        <div class="proactive-site-advisor-header-container">
            <!-- Brand Section (Logo/Title) -->
            <div class="proactive-site-advisor-header-brand">
                <?php if (!empty($titleLink)) : ?>
                <a href="<?php echo esc_url($titleLink); ?>" class="proactive-site-advisor-header-brand-link">
                    <?php endif; ?>

                    <?php if (!empty($logoUrl)) : ?>
                        <img
                            src="<?php echo esc_url($logoUrl); ?>"
                            alt="<?php echo esc_attr($title); ?>"
                            class="proactive-site-advisor-header-logo"
                        />
                    <?php endif; ?>

                    <?php if (!empty($title)) : ?>
                        <span class="proactive-site-advisor-header-title"><?php echo esc_html($title); ?></span>
                    <?php endif; ?>

                    <?php if (!empty($version)) : ?>
                        <span class="proactive-site-advisor-badge proactive-site-advisor-badge-secondary proactive-site-advisor-header-version">
                        v<?php echo esc_html($version); ?>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($titleLink)) : ?>
                </a>
            <?php endif; ?>
            </div>

            <!-- Navigation Section -->
            <?php if (!empty($navItems)) : ?>
                <!-- Desktop Navigation (visible on large screens) -->
                <nav class="proactive-site-advisor-header-nav proactive-site-advisor-header-nav-desktop">
                    <ul class="proactive-site-advisor-header-nav-list">
                        <?php foreach ($navItems as $navItem) :
                            $itemClasses = ['proactive-site-advisor-header-nav-item'];
                            if (!empty($navItem['active'])) {
                                $itemClasses[] = 'proactive-site-advisor-active';
                            }
                            ?>
                            <li class="<?php echo esc_attr(implode(' ', $itemClasses)); ?>">
                                <a
                                    href="<?php echo esc_url($navItem['url'] ?? '#'); ?>"
                                    class="proactive-site-advisor-header-nav-link"
                                >
                                    <?php if (!empty($navItem['icon'])) : ?>
                                        <span class="dashicons <?php echo esc_attr($navItem['icon']); ?>"></span>
                                    <?php endif; ?>

                                    <span class="proactive-site-advisor-header-nav-text">
                                        <?php echo esc_html($navItem['label'] ?? ''); ?>
                                    </span>

                                    <?php if (!empty($navItem['badge'])) : ?>
                                        <span class="proactive-site-advisor-badge proactive-site-advisor-badge-primary proactive-site-advisor-badge-sm">
                                            <?php echo esc_html($navItem['badge']); ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <!-- Mobile Navigation (toggle + dropdown) -->
                <div class="proactive-site-advisor-header-nav-wrapper">
                    <button
                        type="button"
                        class="proactive-site-advisor-header-toggle"
                        aria-label="<?php esc_attr_e('Toggle navigation', 'proactive-site-advisor'); ?>"
                        aria-expanded="false"
                        aria-controls="proactive-site-advisor-header-nav"
                    >
                        <span class="proactive-site-advisor-header-toggle-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>

                    <nav class="proactive-site-advisor-header-nav" id="proactive-site-advisor-header-nav">
                        <ul class="proactive-site-advisor-header-nav-list">
                            <?php foreach ($navItems as $navItem) :
                                $itemClasses = ['proactive-site-advisor-header-nav-item'];
                                if (!empty($navItem['active'])) {
                                    $itemClasses[] = 'proactive-site-advisor-active';
                                }
                                ?>
                                <li class="<?php echo esc_attr(implode(' ', $itemClasses)); ?>">
                                    <a
                                        href="<?php echo esc_url($navItem['url'] ?? '#'); ?>"
                                        class="proactive-site-advisor-header-nav-link"
                                    >
                                        <?php if (!empty($navItem['icon'])) : ?>
                                            <span class="dashicons <?php echo esc_attr($navItem['icon']); ?>"></span>
                                        <?php endif; ?>

                                        <span class="proactive-site-advisor-header-nav-text">
                                            <?php echo esc_html($navItem['label'] ?? ''); ?>
                                        </span>

                                        <?php if (!empty($navItem['badge'])) : ?>
                                            <span class="proactive-site-advisor-badge proactive-site-advisor-badge-primary proactive-site-advisor-badge-sm">
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
                <div class="proactive-site-advisor-header-actions">
                    <?php foreach ($actions as $action) :
                        $type = $action['type'] ?? 'button';
                        $variant = $action['variant'] ?? 'primary';
                        $label = $action['label'] ?? '';
                        $url = $action['url'] ?? '#';
                        $icon = $action['icon'] ?? '';
                        $attrs = $action['attrs'] ?? [];

                        // Build class string
                        $btnClass = 'proactive-site-advisor-btn proactive-site-advisor-btn-' . esc_attr($variant);
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
                                <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                            <?php endif; ?>
                            <span class="proactive-site-advisor-btn-text"><?php echo esc_html($label); ?></span>
                        </a>
                    <?php else : ?>
                        <button
                            type="button"
                            class="<?php echo esc_attr($btnClass); ?>"
                            <?php echo $attrsStr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        >
                            <?php if (!empty($icon)) : ?>
                                <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                            <?php endif; ?>
                            <span class="proactive-site-advisor-btn-text"><?php echo esc_html($label); ?></span>
                        </button>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="proactive-site-advisor-content-wrapper">
