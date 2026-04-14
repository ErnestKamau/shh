<?php

namespace App\Models\Procedures;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\CapturedResult;

class ProcedureTestKitValue extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory;

    protected $fillable = [
        'captured_result_id',
        'procedure_test_kit_row_id',
        'procedure_test_kit_column_id',
        'value',
    ];

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(CapturedResult::class, 'captured_result_id');
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(ProcedureTestKitRow::class, 'procedure_test_kit_row_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProcedureTestKitColumn::class, 'procedure_test_kit_column_id');
    }
}

