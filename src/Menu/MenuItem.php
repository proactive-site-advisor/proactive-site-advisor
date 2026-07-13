<?php

namespace ProactiveSiteAdvisor\Menu;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Represents a single admin menu item.
 *
 * @package ProactiveSiteAdvisor\Menu
 * @since   1.0.0
 */
class MenuItem
{
    /** Unique identifier for the menu item. */
    public string $id;

    /** Display title of the menu item. */
    public string $title;

    /** Required capability to access this menu item. */
    public string $capability;

    /** Dash icon or custom icon class. */
    public string $icon;

    /** Menu position in WordPress admin. */
    public ?float $position;

    /** Parent menu ID if this is a submenu. */
    public ?string $parentId;

    /** Function to render menu page content. */
    public $callback;

    /** List of callable conditions to check visibility. */
    public array $conditions;

    /** Constructor. */
    public function __construct(
        string    $id,
        string    $title,
        string    $capability = 'manage_options',
        string    $icon = 'dashicons-admin-generic',
        ?float    $position = 20.0,
        ?string   $parentId = null,
        ?callable $callback = null,
        array     $conditions = []
    )
    {
        $this->id         = $id;
        $this->title      = $title;
        $this->capability = $capability;
        $this->icon       = $icon;
        $this->position   = $position;
        $this->parentId   = $parentId;
        $this->callback   = $callback;
        $this->conditions = $conditions;
    }

    /** Check whether the menu item should be visible. */
    public function isVisible(): bool
    {
        foreach ($this->conditions as $condition) {
            if (!is_callable($condition)) {
                continue;
            }

            if (!$condition()) {
                return false;
            }
        }
        return true;
    }
}