<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class CaseInsensitiveSearch
{
    /**
     * Apply a case-insensitive LIKE/ILIKE filter across columns.
     * Uses ILIKE on PostgreSQL; LOWER(column) LIKE on other drivers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Query\Builder  $query
     * @param  array<int, string>  $columns
     */
    public static function apply($query, array $columns, string $term): void
    {
        $term = trim($term);

        if ($term === '' || $columns === []) {
            return;
        }

        $isPgsql = self::isPgsql();
        $like = self::likePattern($term);

        $query->where(function ($builder) use ($columns, $like, $isPgsql): void {
            foreach ($columns as $index => $column) {
                if ($isPgsql) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'ilike', $like);
                    continue;
                }

                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $builder->{$method}('LOWER('.$column.') LIKE ?', [$like]);
            }
        });
    }

    /**
     * Apply a case-insensitive equality filter on a single column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Query\Builder  $query
     */
    public static function equals($query, string $column, string $value): void
    {
        $value = trim($value);

        if ($value === '' || $column === '') {
            return;
        }

        if (self::isPgsql()) {
            $query->where($column, 'ilike', $value);

            return;
        }

        $query->whereRaw('LOWER('.$column.') = ?', [mb_strtolower($value)]);
    }

    public static function isPgsql(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    /**
     * Build a LIKE/ILIKE pattern (%term%) with driver-aware casing.
     */
    public static function likePattern(string $term): string
    {
        $term = trim($term);

        if ($term === '') {
            return '';
        }

        return '%'.(self::isPgsql() ? $term : mb_strtolower($term)).'%';
    }

    /**
     * SQL fragment for a single column case-insensitive LIKE comparison (with ? binding).
     */
    public static function columnLikeSql(string $column): string
    {
        return self::isPgsql()
            ? $column.' ILIKE ?'
            : 'LOWER('.$column.') LIKE ?';
    }
}
