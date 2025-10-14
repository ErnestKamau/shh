<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LookupTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'key_columns',
        'value_column',
        'key_label',
        'value_label',
        'is_active',
    ];

    protected $casts = [
        'key_columns' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the lookup table entries for this table.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(LookupTableEntry::class);
    }

    /**
     * Scope for active lookup tables.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get a value from the lookup table by keys.
     */
    public function getValue(array $keys): ?string
    {
        // Sort keys for consistent comparison
        ksort($keys);
        
        // Use JSON comparison - MySQL will handle the JSON column properly
        $entry = $this->entries()
            ->whereRaw('JSON_CONTAINS(`keys`, ?) AND JSON_CONTAINS(?, `keys`)', [
                json_encode($keys),
                json_encode($keys)
            ])
            ->first();

        return $entry ? $entry->value : null;
    }
}
