<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Require posted or completed stage before prefill
    |--------------------------------------------------------------------------
    |
    | When true, formula worksheet data is only used if the worksheet was posted
    | (posted_at) or the grouped pipeline run item is marked completed.
    | When false, any saved worksheet with data is used.
    |
    */
    'require_posted_or_completed' => false,

    /*
    |--------------------------------------------------------------------------
    | Pipeline stage label keywords → case file section
    |--------------------------------------------------------------------------
    |
    | First matching section wins. Item labels are matched case-insensitively.
    |
    */
    'stage_label_keywords' => [
        'screening' => ['screening', 'sample screening'],
        'extraction' => ['extraction', 'sample extraction'],
        'quantification' => ['quantification', 'quant'],
        'pcr' => ['pcr', 'amplification', 'polymerase'],
        'injection' => ['injection', 'interpretation', 'capillary'],
        'reporting' => ['reporting', 'report'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Section → primary date field on Case File Review Form
    |--------------------------------------------------------------------------
    */
    'section_date_fields' => [
        'screening' => 'screening_date',
        'extraction' => 'extraction_date',
        'quantification' => 'quantification_date',
        'pcr' => 'pcr_amplification_date',
        'injection' => 'injection_date',
        'reporting' => 'reporting_draft_report_date',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mandatory field aliases (field_value_name or normalized label)
    |--------------------------------------------------------------------------
    |
    | Keys are normalized: lowercase, non-alphanumeric → underscore.
    | Values are case_file_review_forms column names.
    |
    */
    'mandatory_field_aliases' => [
        'screening_date' => 'screening_date',
        'date' => null,
        'method' => 'screening_method',
        'screening_method' => 'screening_method',
        'extraction_date' => 'extraction_date',
        'quantification_date' => 'quantification_date',
        'amplification_date' => 'pcr_amplification_date',
        'pcr_date' => 'pcr_amplification_date',
        'injection_date' => 'injection_date',
        'run_id' => 'injection_run_id',
        'remarks' => null,
        'screening_remarks' => null,
        'quantification_remarks' => 'quantification_remarks',
        'pcr_remarks' => 'pcr_remarks',
        'reporting_remarks' => 'reporting_remarks',
        'screening_results_1' => 'screening_results_1',
        'screening_results_2' => 'screening_results_2',
        'result_1' => 'screening_results_1',
        'result_2' => 'screening_results_2',
        'screening_sample_type_others' => 'screening_sample_type_others',
        'extraction_method_other' => 'extraction_method_other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Formula step aliases (variable_name or normalized label)
    |--------------------------------------------------------------------------
    */
    'step_aliases' => [
        'screening_date' => 'screening_date',
        'screening_method' => 'screening_method',
        'method' => 'screening_method',
        'extraction_date' => 'extraction_date',
        'quantification_date' => 'quantification_date',
        'amplification_date' => 'pcr_amplification_date',
        'pcr_date' => 'pcr_amplification_date',
        'injection_date' => 'injection_date',
        'run_id' => 'injection_run_id',
        'quantification_remarks' => 'quantification_remarks',
        'pcr_remarks' => 'pcr_remarks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkbox / option token → case file boolean or text field
    |--------------------------------------------------------------------------
    |
    | Matched as substring against option label or value (case-insensitive).
    |
    */
    'checkbox_tokens' => [
        'blood' => 'screening_sample_type_blood',
        'object with blood' => 'screening_sample_type_object_with_blood',
        'object_with_blood' => 'screening_sample_type_object_with_blood',
        'semen' => 'screening_sample_type_semen',
        'object with semen' => 'screening_sample_type_object_with_semen',
        'object_with_semen' => 'screening_sample_type_object_with_semen',
        'chelex' => 'extraction_method_chelex',
        'prepfiler' => 'extraction_method_prepfiler',
        'prep filer' => 'extraction_method_prepfiler',
        'quant trio' => 'quantification_kit_used_quant_trio',
        'quant_trio' => 'quantification_kit_used_quant_trio',
        'cycles_40' => 'quantification_no_of_cycles_40',
        '40 cycles' => 'quantification_no_of_cycles_40',
        '28' => 'pcr_no_of_cycles_28',
        '29' => 'pcr_no_of_cycles_29',
        '30' => 'pcr_no_of_cycles_30',
        '32' => 'pcr_no_of_cycles_32',
        'identifiler' => 'pcr_kit_used_identifiler_plus',
        'globalfiler' => 'pcr_kit_used_globalfiler',
        'global filer' => 'pcr_kit_used_globalfiler',
        'yfiler' => 'pcr_kit_used_yfiler_plus',
        'y filer' => 'pcr_kit_used_yfiler_plus',
        '3500' => 'injection_instrument_3500',
        'real time' => 'reporting_attachment_real_time_data',
        'real-time' => 'reporting_attachment_real_time_data',
        'converge' => 'reporting_attachment_converge',
        'statistical' => 'reporting_attachment_statistical_analysis',
        'reviewed' => 'reporting_reviewed',
        'corrected' => 'reporting_corrected',
    ],

    /*
    |--------------------------------------------------------------------------
    | Section-scoped mandatory/step alias overrides
    |--------------------------------------------------------------------------
    |
    | When a field alias maps to null in mandatory_field_aliases, section context
    | can supply the target column (e.g. generic "method" on extraction stage).
    |
    */
    'section_field_aliases' => [
        'screening' => [
            'method' => 'screening_method',
            'date' => 'screening_date',
            'remarks' => null,
        ],
        'extraction' => [
            'method' => 'extraction_method_other',
            'date' => 'extraction_date',
        ],
        'quantification' => [
            'method' => null,
            'date' => 'quantification_date',
            'remarks' => 'quantification_remarks',
        ],
        'pcr' => [
            'method' => null,
            'date' => 'pcr_amplification_date',
            'remarks' => 'pcr_remarks',
        ],
        'injection' => [
            'date' => 'injection_date',
        ],
        'reporting' => [
            'date' => 'reporting_draft_report_date',
            'remarks' => 'reporting_remarks',
        ],
    ],

];
