<?php

namespace App\Models\Formulars;

use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class SampleFormulaStepTableInstance extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'worksheet_formular_id',
        'formula_step_id',
        'status',
    ];

    public function worksheetFormula(): BelongsTo
    {
        return $this->belongsTo(SampleCapturedWorksheetFormula::class, 'worksheet_formular_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FormulaStep::class, 'formula_step_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SampleFormulaStepTableRow::class, 'instance_id');
    }
}
