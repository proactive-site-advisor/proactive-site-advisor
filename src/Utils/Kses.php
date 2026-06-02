<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Kses
 *
 * Centralized HTML escaping utility for plugin admin pages.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @version 1.0.0
 */
class Kses
{
    /**
     * Additional allowed HTML tags and attributes.
     *
     * @return array
     */
    private static function customTags(): array
    {
        return [
            'form' => [
                'method'  => true,
                'action'  => true,
                'class'   => true,
                'id'      => true,
                'enctype' => true,
                'data-*'  => true,
            ],

            'input' => [
                'type'             => true,
                'name'             => true,
                'value'            => true,
                'class'            => true,
                'id'               => true,
                'placeholder'      => true,
                'checked'          => true,
                'disabled'         => true,
                'readonly'         => true,
                'required'         => true,
                'size'             => true,
                'maxlength'        => true,
                'min'              => true,
                'max'              => true,
                'step'             => true,
                'aria-label'       => true,
                'aria-describedby' => true,
                'aria-required'    => true,
                'autocomplete'     => true,
                'data-*'           => true,
            ],

            'select' => [
                'name'             => true,
                'id'               => true,
                'class'            => true,
                'multiple'         => true,
                'disabled'         => true,
                'required'         => true,
                'aria-label'       => true,
                'aria-describedby' => true,
                'data-*'           => true,
            ],

            'option' => [
                'value'    => true,
                'selected' => true,
                'disabled' => true,
                'data-*'   => true,
            ],
        ];
    }

    /**
     * Allowed HTML tags and attributes for the plugin.
     *
     * @return array
     */
    public static function allowedHtml(): array
    {
        $allowed = wp_kses_allowed_html('post');

        foreach (self::customTags() as $tag => $attributes) {
            $allowed[$tag] = array_merge(
                $allowed[$tag] ?? [],
                $attributes
            );
        }

        return $allowed;
    }
}