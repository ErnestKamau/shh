<?php

namespace App\Models\Worksheets;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\Formulars\FormulaMandatoryField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleWorksheetFormularMandatoryData extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
