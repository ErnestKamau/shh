<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LookupTableEntry extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

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
        
        // Use JSON comparison for the PostgreSQL JSON column.
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
