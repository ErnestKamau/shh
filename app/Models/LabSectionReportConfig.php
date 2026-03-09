<?php

namespace App\Models;

use App\ReportFormat;
use App\SampleAnalysisStage;
use Illuminate\Database\Eloquent\Model;

class LabSectionReportConfig extends Model
{
    protected $table = 'report_format_sample_analysis_stage';

    protected $fillable = [
        'sample_analysis_stage_id',
        'report_format_id',
        'document_code',
        'issue_date',
        'revision_number',
        'is_default',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'is_default' => 'boolean',
    ];

    /**
     * The Lab Section (SampleAnalysisStage) this config belongs to.
     */
    public function labSection()
    {
        return $this->belongsTo(SampleAnalysisStage::class, 'sample_analysis_stage_id');
    }

    /**
     * The Report Format this config references.
     */
    public function reportFormat()
    {
        return $this->belongsTo(ReportFormat::class, 'report_format_id');
    }
}
