<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class ProcedureStepTableStaticRow extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'procedure_worksheet_step_id',
        'order',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcedureWorksheetStep::class, 'procedure_worksheet_step_id');
    }

    public function cells(): HasMany
    {
        return $this->hasMany(ProcedureStepTableStaticCell::class, 'static_row_id');
    }
}
