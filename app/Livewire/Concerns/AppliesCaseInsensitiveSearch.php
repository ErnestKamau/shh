<?php

namespace App\Livewire\Concerns;

use App\Support\CaseInsensitiveSearch;

trait AppliesCaseInsensitiveSearch
{
    /**
     * Apply a case-insensitive LIKE/ILIKE filter across columns.
     * Uses ILIKE on PostgreSQL; LOWER(column) LIKE on other drivers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Query\Builder  $query
     * @param  array<int, string>  $columns
     */
    protected function applyCaseInsensitiveSearch($query, array $columns, string $term): void
    {
        CaseInsensitiveSearch::apply($query, $columns, $term);
    }
}
