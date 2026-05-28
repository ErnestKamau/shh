<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SampleFormulaStepTableCellValue extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'row_id',
        'column_id',
        'value',
    ];

    public function row(): BelongsTo
    {
        return $this->belongsTo(SampleFormulaStepTableRow::class, 'row_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(FormulaStepTableColumn::class, 'column_id');
    }
}
