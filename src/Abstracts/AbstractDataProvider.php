<?php

namespace ProactiveSiteAdvisor\Abstracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base class for data providers.
 *
 * @package ProactiveSiteAdvisor\Abstracts
 * @since   1.0.0
 */
abstract class AbstractDataProvider
{
    /** Sanitize and qualify ORDER BY clause. */
    protected function sanitizeSorting(string $orderBy, string $order, string $table, array $allowed): array
    {
        if (!in_array($orderBy, $allowed, true)) {
            $orderBy = $allowed[0];
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        return ["$table.$orderBy", $order];
    }

    /** Convert numeric filter logic into SQL. */
    protected function applyNumericFilter(?string $value, array &$where, array &$params, string $field, string $table): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (str_starts_with($value, 'range:')) {

            $range = substr($value, 6);
            [$min, $max] = array_map('trim', explode('-', $range));

            $where[] = "$table.$field BETWEEN %d AND %d";
            array_push($params, (int)$min, (int)$max);

        } elseif (str_starts_with($value, 'gte:')) {

            $min = (int)substr($value, 4);

            $where[]  = "$table.$field >= %d";
            $params[] = $min;

        } else {

            $where[]  = "$table.$field = %d";
            $params[] = (int)$value;

        }
    }

    /** Apply "less than or equal" integer filter. */
    protected function applyMaxFilter(?string $value, array &$where, array &$params, string $field, string $table): void
    {
        if (!empty($value)) {
            $where[]  = "$table.$field <= %d";
            $params[] = (int)$value;
        }
    }

    /**
     * Build pagination metadata response.
     *
     * @param class-string<AbstractModel> $modelClass
     */
    protected function formatPaginatedResult(array $rows, int $total, int $perPage, string $modelClass): array
    {
        return [
            'items' => array_map(static fn($row) => $modelClass::make($row), $rows),
            'total' => $total,
            'pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
        ];
    }
}