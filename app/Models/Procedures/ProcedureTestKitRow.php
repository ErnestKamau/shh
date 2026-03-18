<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcedureTestKitRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_worksheet_id',
        'captured_result_id',
        'row_index',
    ];

    protected $casts = ['row_index' => 'integer'];

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(ProcedureWorksheet::class, 'procedure_worksheet_id');
    }

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(\App\CapturedResult::class, 'captured_result_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProcedureTestKitValue::class, 'procedure_test_kit_row_id');
    }
}

