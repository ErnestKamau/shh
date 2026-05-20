<?php

namespace App\Models\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\SampleType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisAcceptanceFormLine extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'analysis_acceptance_form_id',
        'line_no',
        'sample_type_id',
        'analysis_type_id',
        'analysis_element_id',
        'parameter_label',
        'unit_amount',
        'number_of_samples',
        'is_approved',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'unit_amount' => 'float',
            'number_of_samples' => 'integer',
            'is_approved' => 'boolean',
            'line_no' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function acceptanceForm(): BelongsTo
    {
        return $this->belongsTo(AnalysisAcceptanceForm::class, 'analysis_acceptance_form_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class, 'analysis_type_id');
    }

    public function analysisElement(): BelongsTo
    {
        return $this->belongsTo(AnalysisElements::class, 'analysis_element_id');
    }
}
