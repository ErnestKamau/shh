<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureTestKitValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_test_kit_row_id',
        'procedure_test_kit_column_id',
        'value',
    ];

    public function row(): BelongsTo
    {
        return $this->belongsTo(ProcedureTestKitRow::class, 'procedure_test_kit_row_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProcedureTestKitColumn::class, 'procedure_test_kit_column_id');
    }
}

