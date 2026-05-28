<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SampleProcedureStepTableCellValue extends Model implements Auditable
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
        return $this->belongsTo(SampleProcedureStepTableRow::class, 'row_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProcedureStepTableColumn::class, 'column_id');
    }
}
