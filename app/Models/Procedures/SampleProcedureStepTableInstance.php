<?php

namespace App\Models\Procedures;

use App\SampleHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class SampleProcedureStepTableInstance extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'procedure_worksheet_step_id',
        'status',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcedureWorksheetStep::class, 'procedure_worksheet_step_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SampleProcedureStepTableRow::class, 'instance_id');
    }
}
