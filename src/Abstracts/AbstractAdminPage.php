<?php

namespace ProactiveSiteAdvisor\Abstracts;

use ProactiveSiteAdvisor\Admin\Notices\AdminNotices;
use ProactiveSiteAdvisor\Config\HeaderConfig;
use ProactiveSiteAdvisor\Utils\Kses;
use ProactiveSiteAdvisor\Utils\TemplateUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides a base structure for admin pages.
 *
 * @package ProactiveSiteAdvisor\Abstracts
 * @since   1.0.0
 */
abstract class AbstractAdminPage extends AbstractSingleton
{
    /** Runs before the page is rendered. */
    protected function boot(): void
    {
    }

    /** Renders the admin page. */
    public function render(): void
    {
        $this->boot();

        $sections = [
            'header'         => TemplateUtils::renderTemplate(
                'admin/layouts/header',
                $this->getHeaderContext()
            ),
            'page_header'    => TemplateUtils::renderTemplate(
                $this->getPageHeaderTemplate(),
                $this->getPageHeaderContext()
            ),
            'inline_notices' => $this->renderNotices(),
            'body'           => TemplateUtils::renderTemplate(
                $this->getTemplate(),
                $this->getBodyContext()
            ),
            'footer'         => TemplateUtils::renderTemplate(
                'admin/layouts/footer',
                $this->getFooterContext()
            ),
        ];

        $output = implode('', $sections);

        echo wp_kses($output, Kses::allowedHtml());
    }

    /** Returns the page header template path. */
    abstract protected function getPageHeaderTemplate(): string;

    /** Returns the body template path. */
    abstract protected function getTemplate(): string;

    /** Returns the context array for the header template. */
    protected function getHeaderContext(): array
    {
        return HeaderConfig::getDefaults();
    }

    /** Returns the context array for the page header. */
    protected function getPageHeaderContext(): array
    {
        return [];
    }

    /** Returns the context array for the body template. */
    protected function getBodyContext(): array
    {
        return [];
    }

    /** Returns the context array for the footer template. */
    protected function getFooterContext(): array
    {
        return [];
    }

    /** Returns rendered admin notices HTML. */
    protected function renderNotices(): string
    {
        ob_start();
        AdminNotices::render();
        return ob_get_clean();
    }
}