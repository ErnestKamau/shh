<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Recurring AmSpec personnel who sample TRFs. Powers the Name/Employee ID typeahead
 * on the "Submit & sign" step so the same person doesn't get retyped every time.
 */
class AmspecSampler extends Model
{
    protected $fillable = [
        'name',
        'employee_id',
        'use_count',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'use_count' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'ilike', '%'.$term.'%')
                ->orWhere('employee_id', 'ilike', '%'.$term.'%');
        });
    }

    /**
     * Record (or bump) a sampler seen on a submitted TRF.
     */
    public static function remember(string $name, ?string $employeeId): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }

        $employeeId = trim((string) $employeeId);
        $employeeId = $employeeId !== '' ? $employeeId : null;

        $query = static::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
        $sampler = $employeeId !== null
            ? $query->where('employee_id', $employeeId)->first()
            : $query->whereNull('employee_id')->first();

        if ($sampler !== null) {
            $sampler->increment('use_count');
            $sampler->update(['last_used_at' => now()]);

            return;
        }

        static::query()->create([
            'name' => $name,
            'employee_id' => $employeeId,
            'use_count' => 1,
            'last_used_at' => now(),
        ]);
    }
}
