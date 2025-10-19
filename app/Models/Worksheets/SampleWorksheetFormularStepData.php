<?php

namespace App\Models\Worksheets;

use App\Models\Formulars\FormulaStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleWorksheetFormularStepData extends Model
{
    protected $fillable = [
        'worksheet_formular_id',
        'formula_step_id',
        'step_value',
        'overridden_lookup_table_id',
    ];

    public function worksheetFormula(): BelongsTo
    {
        return $this->belongsTo(SampleCapturedWorksheetFormula::class, 'worksheet_formular_id');
    }

    public function formulaStep(): BelongsTo
    {
        return $this->belongsTo(FormulaStep::class);
    }

    public function overriddenLookupTable(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Formulars\LookupTable::class, 'overridden_lookup_table_id');
    }
}
