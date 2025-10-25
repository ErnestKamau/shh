<?php

namespace App\Models\Worksheets;

use App\Models\Formulars\FormulaMandatoryField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleWorksheetFormularMandatoryData extends Model
{
    protected $fillable = [
        'worksheet_formular_id',
        'formula_mandatory_field_id',
        'field_value',
    ];

    public function worksheetFormula(): BelongsTo
    {
        return $this->belongsTo(SampleCapturedWorksheetFormula::class, 'worksheet_formular_id');
    }

    public function mandatoryField(): BelongsTo
    {
        return $this->belongsTo(FormulaMandatoryField::class, 'formula_mandatory_field_id');
    }
}
