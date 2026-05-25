<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseFileReviewForm extends Model
{
    use HasFactory;

    protected $table = 'case_file_review_forms';

    protected $guarded = ['id'];
    
    // Add casting for boolean fields to ensure they are handled properly
    protected $casts = [
        'sample_condition_sealed' => 'boolean',
        'sample_condition_labelled' => 'boolean',
        'screening_sample_type_blood' => 'boolean',
        'screening_sample_type_object_with_blood' => 'boolean',
        'screening_sample_type_semen' => 'boolean',
        'screening_sample_type_object_with_semen' => 'boolean',
        'extraction_method_chelex' => 'boolean',
        'extraction_method_prepfiler' => 'boolean',
        'quantification_no_of_cycles_40' => 'boolean',
        'quantification_kit_used_quant_trio' => 'boolean',
        'pcr_no_of_cycles_28' => 'boolean',
        'pcr_no_of_cycles_29' => 'boolean',
        'pcr_no_of_cycles_30' => 'boolean',
        'pcr_no_of_cycles_32' => 'boolean',
        'pcr_kit_used_identifiler_plus' => 'boolean',
        'pcr_kit_used_globalfiler' => 'boolean',
        'pcr_kit_used_yfiler_plus' => 'boolean',
        'injection_instrument_3500' => 'boolean',
        'reporting_reviewed' => 'boolean',
        'reporting_corrected' => 'boolean',
        'reporting_attachment_real_time_data' => 'boolean',
        'reporting_attachment_converge' => 'boolean',
        'reporting_attachment_statistical_analysis' => 'boolean',
        'manager_review_technical' => 'boolean',
        'manager_review_administrative' => 'boolean',
        'manager_comments_verified' => 'boolean',
        'manager_comments_not_verified' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(\App\SampleHeader::class, 'batch_id', 'id');
    }
}
