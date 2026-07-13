<?php

namespace ProactiveSiteAdvisor\Menu;

use ProactiveSiteAdvisor\Utils\MenuUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Responsible for rendering all registered admin menu items.
 *
 * @package ProactiveSiteAdvisor\Menu
 * @since   1.0.0
 */
class MenuRenderer
{
    /** Render all visible menu items. */
    public function renderAll(array $menuItems): void
    {
        foreach ($menuItems as $item) {
            if (!$item->isVisible()) {
                continue;
            }

            if ($item->parentId) {
                $this->addSubmenu($item);
            } else {
                $this->addMenu($item);
            }
        }
    }

    /** Register a top-level menu item. */
    private function addMenu(MenuItem $item): void
    {
        $id = MenuUtils::getSlug($item->id);

        add_menu_page(
            $item->title,
            $item->title,
            $item->capability,
            $id,
            $item->callback,
            $item->icon,
            $item->position
        );
    }

    /** Register a submenu item. */
    private function addSubmenu(MenuItem $item): void
    {
        $id       = MenuUtils::getSlug($item->id);
        $parentId = MenuUtils::getSlug($item->parentId);

        add_submenu_page(
            $parentId,
            $item->title,
            $item->title,
            $item->capability,
            $id,
            $item->callback
        );
    }
}