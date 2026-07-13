<?php

namespace ProactiveSiteAdvisor\Menu;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles registration, normalization, sorting, and rendering of admin menu items.
 *
 * @package ProactiveSiteAdvisor\Menu
 * @since   1.0.0
 */
class MenuManager extends AbstractSingleton
{
    /**
     * List of registered menu items (objects or arrays).
     *
     * @var MenuItem[]|array[]
     */
    private array $menuItems = [];

    /** Initialize menu system by hooking into WordPress admin_menu. */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenus']);
    }

    /** Register a new menu item. */
    public function registerMenuItem($item): void
    {
        $this->menuItems[] = $item;
    }

    /** Collect, filter, normalize, sort, and render all menu items. */
    public function registerMenus(): void
    {
        $menuItems = $this->getFilteredMenuItems();
        $menuItems = $this->normalizeMenuItems($menuItems);
        $menuItems = $this->sortMenuItems($menuItems);

        (new MenuRenderer())->renderAll($menuItems);
    }

    /**
     * Apply WordPress filters to get the final list of menu items.
     */
    private function getFilteredMenuItems(): array
    {
        /**
         * Filters the registered menu items.
         *
         * @param MenuItem[]|array[] $menuItems List of menu items (objects or arrays).
         * @since  1.0.0
         */
        return apply_filters('proactive_site_advisor_menu_items', $this->menuItems);
    }

    /**
     * Normalize raw menu items (arrays or MenuItem objects) to MenuItem objects.
     *
     * Wraps callbacks if they are class names with a 'render' method.
     */
    private function normalizeMenuItems(array $items): array
    {
        $normalized = array_map(function ($item) {
            if ($item instanceof MenuItem) {
                $item->callback = $this->wrapClassCallback($item->callback);
                return $item;
            }

            if (is_array($item)) {
                return new MenuItem(
                    $item['id'] ?? '',
                    $item['title'] ?? '',
                    $item['capability'] ?? 'manage_options',
                    $item['icon'] ?? '',
                    $item['position'] ?? null,
                    $item['parentId'] ?? null,
                    $this->wrapClassCallback($item['callback'] ?? null),
                    $item['conditions'] ?? []
                );
            }

            return null;
        }, $items);

        return array_filter($normalized, static fn($item) => $item instanceof MenuItem);
    }

    /**
     * Sort menu items by hierarchy and position.
     *
     * Top-level menus appear before submenus.
     */
    private function sortMenuItems(array $items): array
    {
        usort($items, static function ($a, $b) {
            if ($a->parentId === null && $b->parentId !== null) {
                return -1;
            }
            if ($a->parentId !== null && $b->parentId === null) {
                return 1;
            }

            return $a->position <=> $b->position;
        });

        return $items;
    }

    /** Wrap a class name as a callable for menu callbacks. */
    private function wrapClassCallback($callback): array
    {
        if (is_string($callback) && class_exists($callback) && method_exists($callback, 'render')) {
            /** @var class-string $callback */
            return [$callback::instance(), 'render'];
        }

        return $callback;
    }
}