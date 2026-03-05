<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureTestKitColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_worksheet_id',
        'label',
        'key',
        'type',
        'order',
        'is_required',
        'help_text',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(ProcedureWorksheet::class, 'procedure_worksheet_id');
    }
}

