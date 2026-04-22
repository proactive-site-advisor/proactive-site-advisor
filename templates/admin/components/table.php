<?php

/**
 * Component: Table.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are locally scoped via include.
 *
 * @package ProactiveSiteAdvisor
 * @version 1.0.0
 *
 * @var array $columns
 * @var array $rows
 * @var string $class
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="psa-table <?php echo esc_attr($class); ?>">
    <thead>
    <tr>
        <?php foreach ($columns as $column) : ?>
            <th class="<?php echo esc_attr($column['class'] ?? ''); ?>"><?php echo esc_html($column['label'] ?? ''); ?></th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row) : ?>
        <tr>
            <?php foreach ($columns as $column) :

                $key = $column['key'] ?? '';
                $type = $column['type'] ?? 'text';
                $value = $row[$key] ?? null;
                $formatter = $column['formatter'] ?? null;
                $decimals = $column['decimals'] ?? 2;
                $suffix = $column['suffix'] ?? '';
                $class = $column['class'] ?? '';
                $labels = $column['labels'] ?? [];

                ?>
                <td class="<?php echo esc_attr($class); ?>">

                    <?php
                    /**
                     * Formatter has highest priority
                     */
                    if (is_callable($formatter)) {
                        echo wp_kses_post($formatter($value, $row));
                        continue;
                    }

                    /**
                     * Type-based render
                     */
                    switch ($type) {

                        case 'link':
                            if (is_array($value)) {
                                $label = $value['label'] ?? '';
                                $link  = $value['link'] ?? '#';

                                echo '<a href="' . esc_url($link) . '">';
                                echo wp_kses_post($label);
                                echo '</a>';
                            }
                            break;

                        case 'number':

                            $intValue = (int)$value;

                            if ($intValue < 0 && isset($labels['negative'])) {
                                echo '<span class="psa-negative">';
                                echo esc_html($intValue) . ' (' . esc_html($labels['negative']) . ')';
                                echo '</span>';
                            } else {
                                echo esc_html($intValue);
                            }

                            break;

                        case 'decimal':

                            $floatVal = (float)$value;

                            if ($floatVal <= 0 && isset($labels['zero'])) {
                                echo esc_html($labels['zero']);
                            } else {
                                echo esc_html(number_format_i18n($floatVal, (int)$decimals));
                                echo esc_html($suffix);
                            }

                            break;

                        case 'days':

                            if ($value === null) {
                                echo '—';
                            } elseif ($value <= 0 && isset($labels['empty'])) {
                                echo esc_html($labels['empty']);
                            } else {
                                echo esc_html((int)$value);

                                if (isset($labels['unit'])) {
                                    echo ' ' . esc_html($labels['unit']);
                                }
                            }

                            break;

                        case 'badge':

                            if (is_array($value)) {
                                $label = $value['label'] ?? '';
                                $class = $value['class'] ?? '';

                                echo '<span class="' . esc_attr($class) . '">';
                                echo esc_html($label);
                                echo '</span>';
                            }

                            break;

                        default:
                            echo esc_html((string)$value);
                    }
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>