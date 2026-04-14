<?php

namespace App\Models\Formulars;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LookupTableEntry extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lookup_table_id',
        'keys',
        'value',
    ];

    protected $casts = [
        'keys' => 'array',
    ];

    /**
     * Scope to find entries by keys array.
     */
    public function scopeWhereKeys($query, array $keys)
    {
        // Sort keys for consistent comparison
        ksort($keys);
        
        // Use JSON comparison - MySQL will handle the JSON column properly
        return $query->whereRaw('JSON_CONTAINS(`keys`, ?) AND JSON_CONTAINS(?, `keys`)', [
            json_encode($keys),
            json_encode($keys)
        ]);
    }

    /**
     * Get the lookup table that owns this entry.
     */
    public function lookupTable(): BelongsTo
    {
        return $this->belongsTo(LookupTable::class);
    }
}
