<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LookupTableEntry extends Model
{
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
     * Get the lookup table that owns this entry.
     */
    public function lookupTable(): BelongsTo
    {
        return $this->belongsTo(LookupTable::class);
    }
}
