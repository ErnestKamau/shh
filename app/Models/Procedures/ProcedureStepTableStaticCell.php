<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class ProcedureStepTableStaticCell extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'static_row_id',
        'column_id',
        'default_value',
    ];

    public function staticRow(): BelongsTo
    {
        return $this->belongsTo(ProcedureStepTableStaticRow::class, 'static_row_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProcedureStepTableColumn::class, 'column_id');
    }
}
