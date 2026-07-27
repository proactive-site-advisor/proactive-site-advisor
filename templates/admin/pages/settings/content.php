<?php

/**
 * Template part: Settings page content.
 *
 * All output is captured by TemplateUtils::renderTemplate() and
 * escaped late via wp_kses() in AbstractAdminPage::render().
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor\Templates\Admin\Pages\Settings
 * @since   1.0.0
 *
 * @var array $sections
 * @var array $settings
 * @var string $nonceAction
 * @var string $nonceName
 * @var string $formAction
 * @var string $formName
 */

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Utils\TemplateUtils;

?>
<div class="psa-page-content">
    <div class="psa-settings-panel">
        <!-- Sidebar: vertical menu -->
        <div class="psa-settings-sidebar">
            <div class="psa-settings-nav">
                <?php foreach ($sections as $sectionId => $section) : ?>
                    <button
                        type="button"
                        class="psa-settings-sidebar-nav-item"
                        data-psa-section="<?php echo esc_attr($sectionId); ?>"
                    >
                        <span class="psa-settings-sidebar-nav-item__icon <?php echo esc_attr($section['icon']); ?>"></span>
                        <span class="psa-settings-sidebar-nav-item__text"><?php echo esc_html($section['title']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="psa-section psa-section-flex">
            <div class="psa-settings">
                <form method="post" action="<?php echo esc_url($formAction); ?>" class="psa-settings-form">
                    <?php
                    foreach ($sections as $sectionId => $section) {
                        echo TemplateUtils::renderTemplate(
                            $section['template'],
                            [
                                'sectionId' => $sectionId,
                                'section'   => $section,
                                'settings'  => $settings,
                            ]
                        );
                    }
                    ?>

                    <p>
                        <?php wp_nonce_field($nonceAction, $nonceName); ?>
                        <input type="hidden" name="action" value="<?php echo esc_attr($formName); ?>">
                        <button
                            class="psa-btn psa-btn-primary"
                            type="submit"
                        >
                            <?php esc_html_e('Save Changes', 'proactive-site-advisor'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>