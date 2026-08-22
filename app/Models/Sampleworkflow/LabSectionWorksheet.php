<?php

namespace App\Models\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSectionWorksheet extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'worksheet_number',
        'lab_section_id',
        'calendar_year',
        'sequence',
        'context',
        'submission_form_instance_id',
        'sample_header_id',
        'generated_by',
        'assigned_analyst_ids',
        'test_snapshot',
        'pdf_path',
        'excel_path',
        'issued_at',
        'downloaded_at',
        'imported_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_analyst_ids' => 'array',
            'test_snapshot' => 'array',
            'issued_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'imported_at' => 'datetime',
            'calendar_year' => 'integer',
            'sequence' => 'integer',
        ];
    }

    public function labSection(): BelongsTo
    {
        return $this->belongsTo(SampleAnalysisStage::class, 'lab_section_id');
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
