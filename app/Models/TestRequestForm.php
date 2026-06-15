<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestRequestForm extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'sample_type_id',
        'form_fields',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'form_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function sampleType()
    {
        return $this->belongsTo(\App\SampleType::class, 'sample_type_id');
    }

    public function getFlatFields()
    {
        $fields = $this->form_fields;
        if (!is_array($fields)) {
            return [];
        }
        if (isset($fields['sections'])) {
            $flat = [];
            foreach ($fields['sections'] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    $flat[] = $field;
                }
            }
            return $flat;
        }
        return $fields;
    }

    public static function seedDefaults()
    {
        // Ensure Waste Water sample type exists
        $company = \App\Company::first();
        $companyId = $company ? $company->id : null;

        $wasteWaterType = \App\SampleType::where('name', 'like', '%Waste Water%')
            ->orWhere('code', 'SMP-WWTR')
            ->first();
        if (!$wasteWaterType) {
            $wasteWaterType = \App\SampleType::create([
                'name' => 'Waste Water',
                'code' => 'SMP-WWTR',
                'description' => 'Waste Water samples',
                'company_id' => $companyId,
                'active' => true,
                'is_results_attachable' => true,
            ]);
        }

        $sampleTypes = \App\SampleType::all();
        foreach ($sampleTypes as $sampleType) {
            $isFood = stripos($sampleType->name, 'Food') !== false || stripos($sampleType->code, 'FOOD') !== false;
            $isWasteWater = stripos($sampleType->name, 'Waste Water') !== false || stripos($sampleType->code, 'WWTR') !== false;
            $isWater = !$isWasteWater && (stripos($sampleType->name, 'Water') !== false || stripos($sampleType->code, 'WTR') !== false);

            // For generic sample types (non Food/Water/WasteWater), skip if one already exists
            if (!$isFood && !$isWater && !$isWasteWater) {
                $existing = self::where('sample_type_id', $sampleType->id)->first();
                if ($existing) {
                    continue;
                }
            }

            $fields = [];
            if ($isFood) {
                $fields = [
                    'sections' => [
                        [
                            'title' => 'CUSTOMER DETAILS',
                            'fields' => [
                                ['name' => 'customer_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                ['name' => 'customer_address', 'label' => 'Address', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_phone', 'label' => 'Tel/ Fax No.', 'type' => 'text', 'required' => false],
                                ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text', 'required' => false],
                                ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => false],
                                ['name' => 'job_number', 'label' => 'JOB NUMBER', 'type' => 'text', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'SAMPLE COLLECTION DATA',
                            'fields' => [
                                ['name' => 'sampling_date', 'label' => 'Sampling Date', 'type' => 'date', 'required' => true],
                                ['name' => 'sampling_time', 'label' => 'Sampling Time', 'type' => 'text', 'required' => false],
                                ['name' => 'sampling_location', 'label' => 'Sampling Location', 'type' => 'text', 'required' => false],
                                ['name' => 'sampling_apparatus', 'label' => 'Sampling Apparatus', 'type' => 'select', 'options' => ['STERILE BAG', 'AIR SAMPLER', 'STERILE BOTTLE', 'GRABBER', 'STERILE SWAB', 'OTHERS'], 'required' => false],
                                ['name' => 'thermometer_id', 'label' => 'Thermometer ID', 'type' => 'text', 'required' => false],
                                ['name' => 'method_of_sampling', 'label' => 'Method of Sampling', 'type' => 'select', 'options' => ['APHA', 'US FDA', 'SASO', 'CCFRA', 'ASTM', 'DM', 'SOP', 'OTHERS'], 'required' => false],
                                ['name' => 'reason_of_collection', 'label' => 'Reason of Collection', 'type' => 'select', 'options' => ['CONTRACT', 'NON-CONTRACT', 'HACCP REQUIREMENT', 'DISPUTED/AUDIT'], 'required' => false],
                                ['name' => 'transport_condition', 'label' => 'Transport Condition', 'type' => 'select', 'options' => ['CHILLER VEHICLE', 'FROZEN', 'AMBIENT'], 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'STATEMENT OF CONFORMITY & SIGNATURES',
                            'fields' => [
                                ['name' => 'statement_of_conformity', 'label' => 'Statement of Conformity Required in Reports', 'type' => 'select', 'options' => ['YES', 'No', 'As per Contract', 'As per Email'], 'required' => false],
                                ['name' => 'sampled_by', 'label' => 'Sampled By: Name and Employee ID', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_rep_name', 'label' => 'Customer Representative Name/Sign.', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_rep_contact', 'label' => 'Customer Representative Contact Number', 'type' => 'text', 'required' => false],
                                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'FOR LAB USE ONLY',
                            'fields' => [
                                ['name' => 'lab_received_datetime', 'label' => 'Received Date & Time', 'type' => 'datetime-local', 'required' => false],
                                ['name' => 'lab_received_by', 'label' => 'Received by', 'type' => 'text', 'required' => false],
                                ['name' => 'lab_sample_condition', 'label' => 'Sample Condition', 'type' => 'select', 'options' => ['Acceptable', 'Not Acceptable'], 'required' => false],
                            ]
                        ]
                    ]
                ];
            } elseif ($isWater) {
                $fields = [
                    'sections' => [
                        [
                            'title' => 'CUSTOMER DETAILS',
                            'fields' => [
                                ['name' => 'customer_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                ['name' => 'customer_address', 'label' => 'Address', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_phone', 'label' => 'Tel/ Fax No.', 'type' => 'text', 'required' => false],
                                ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text', 'required' => false],
                                ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => false],
                                ['name' => 'job_number', 'label' => 'JOB NUMBER', 'type' => 'text', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'SAMPLE COLLECTION DATA',
                            'fields' => [
                                ['name' => 'sampling_date', 'label' => 'Sampling Date', 'type' => 'date', 'required' => true],
                                ['name' => 'sampling_time', 'label' => 'Sampling Time', 'type' => 'text', 'required' => false],
                                ['name' => 'sampling_location', 'label' => 'Sampling Location', 'type' => 'text', 'required' => false],
                                ['name' => 'sampling_apparatus', 'label' => 'Sampling Apparatus', 'type' => 'select', 'options' => ['STERILE BAG', 'GRABBER', 'STERILE BOTTLE', 'OTHERS'], 'required' => false],
                                ['name' => 'thermometer_id', 'label' => 'Thermometer ID', 'type' => 'text', 'required' => false],
                                ['name' => 'method_of_sampling', 'label' => 'Method of Sampling', 'type' => 'select', 'options' => ['APHA', 'US FDA', 'SASO', 'CCFRA', 'ASTM', 'DM', 'SOP', 'OTHERS'], 'required' => false],
                                ['name' => 'reason_of_collection', 'label' => 'Reason of Collection', 'type' => 'select', 'options' => ['CONTRACT', 'NON-CONTRACT', 'HACCP REQUIREMENT', 'DISPUTED/AUDIT'], 'required' => false],
                                ['name' => 'transport_condition', 'label' => 'Transport Condition', 'type' => 'select', 'options' => ['CHILLER VEHICLE', 'FROZEN', 'AMBIENT'], 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'STATEMENT OF CONFORMITY & SIGNATURES',
                            'fields' => [
                                ['name' => 'statement_of_conformity', 'label' => 'Statement of Conformity Required in Reports', 'type' => 'select', 'options' => ['YES', 'No', 'As per Contract', 'As per Email'], 'required' => false],
                                ['name' => 'sampled_by', 'label' => 'Sampled By: Name and Employee ID', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_rep_name', 'label' => 'Customer Representative Name/Sign.', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_rep_contact', 'label' => 'Customer Representative Number', 'type' => 'text', 'required' => false],
                                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'FOR LAB USE ONLY',
                            'fields' => [
                                ['name' => 'lab_received_datetime', 'label' => 'Received Date & Time', 'type' => 'datetime-local', 'required' => false],
                                ['name' => 'lab_received_by', 'label' => 'Received by', 'type' => 'text', 'required' => false],
                                ['name' => 'lab_sample_condition', 'label' => 'Sample Condition', 'type' => 'select', 'options' => ['Acceptable', 'Not Acceptable'], 'required' => false],
                            ]
                        ]
                    ]
                ];
            } elseif ($isWasteWater) {
                $fields = [
                    'sections' => [
                        [
                            'title' => 'CUSTOMER DETAILS',
                            'fields' => [
                                ['name' => 'customer_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                ['name' => 'customer_address', 'label' => 'Address', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_phone', 'label' => 'Tel/ Fax No.', 'type' => 'text', 'required' => false],
                                ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text', 'required' => false],
                                ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => false],
                                ['name' => 'job_number', 'label' => 'JOB NUMBER', 'type' => 'text', 'required' => false],
                                ['name' => 'sample_number', 'label' => 'SAMPLE NUMBER', 'type' => 'text', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'SAMPLE DETAILS',
                            'fields' => [
                                ['name' => 'sampling_date', 'label' => 'Sampling Date', 'type' => 'date', 'required' => true],
                                ['name' => 'sampling_time', 'label' => 'Sampling Time', 'type' => 'text', 'required' => false],
                                ['name' => 'sampling_location', 'label' => 'Sampling Location', 'type' => 'text', 'required' => false],
                                ['name' => 'sample_description', 'label' => 'SAMPLE & SAMPLING POINT DESCRIPTION', 'type' => 'textarea', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'SAMPLING APPARATUS & TECHNIQUES',
                            'fields' => [
                                ['name' => 'sampling_apparatus', 'label' => 'Sampling Apparatus', 'type' => 'select', 'options' => ['STERILE BOTTLE', 'BOTTLE CATCHER', 'OTHERS'], 'required' => false],
                                ['name' => 'thermometer_id', 'label' => 'Thermometer ID AMS/C/INS/136', 'type' => 'text', 'required' => false],
                                ['name' => 'ph_meter_id', 'label' => 'pH METER AMS/C/INS/134', 'type' => 'text', 'required' => false],
                                ['name' => 'chlorine_meter_id', 'label' => 'CHLORINE METER ID AMS/C/INS/079', 'type' => 'text', 'required' => false],
                                ['name' => 'sampling_apparatus_others', 'label' => 'Sampling Apparatus Others', 'type' => 'text', 'required' => false],
                                ['name' => 'method_of_sampling', 'label' => 'Method of Sampling', 'type' => 'select', 'options' => ['APHA', 'US FDA', 'EPA', 'CCFRA', 'DM', 'SOP', 'OTHERS'], 'required' => false],
                                ['name' => 'reason_of_collection', 'label' => 'Reason of Collection', 'type' => 'select', 'options' => ['CONTRACT', 'NON-CONTRACT', 'DM REQUIREMENT', 'DISPUTED/AUDIT'], 'required' => false],
                                ['name' => 'sampling_technique', 'label' => 'Sampling Technique', 'type' => 'select', 'options' => ['GRAB', 'COMPOSITE', 'OTHER'], 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'SAMPLING SOURCE & TYPES',
                            'fields' => [
                                ['name' => 'sampling_source', 'label' => 'Sampling Source', 'type' => 'select', 'options' => ['TANK', 'HOLDING TANK', 'IND./DOMESTIC EFFLUENT', 'POOL WATER', 'DISCHARGE TO MARINE', 'GROUND WATER', 'STP', 'MUNICIPAL TAP WATER'], 'required' => false],
                                ['name' => 'sample_types_ww', 'label' => 'Sample Types', 'type' => 'select', 'options' => ['LIQUID', 'SEMI SOLID', 'SLUDGE', 'MARINE SEDIMENT'], 'required' => false],
                                ['name' => 'transport_condition', 'label' => 'Transport Condition', 'type' => 'select', 'options' => ['CHILLER VEHICLE', 'FROZEN', 'AMBIENT'], 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'FIELD DATA & REQUIREMENTS',
                            'fields' => [
                                ['name' => 'field_data_quantity', 'label' => 'QUANTITY', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_appearance', 'label' => 'APPEARANCE', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_color', 'label' => 'COLOR', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_odor', 'label' => 'ODOR', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_ph', 'label' => 'pH', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_temperature', 'label' => 'TEMPERATURE', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_free_chlorine', 'label' => 'FREE CHLORINE', 'type' => 'text', 'required' => false],
                                ['name' => 'field_data_requirements', 'label' => 'Requirements', 'type' => 'select', 'options' => ['MICROBIOLOGY', 'CHEMISTRY', 'MICROBIOLOGY + CHEMISTRY'], 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'STATEMENT OF CONFORMITY & SIGNATURES',
                            'fields' => [
                                ['name' => 'statement_of_conformity', 'label' => 'Statement of Conformity Required in Reports', 'type' => 'select', 'options' => ['YES', 'No', 'As per Contract', 'As per Email'], 'required' => false],
                                ['name' => 'sampled_by', 'label' => 'Sampled By: Name and Employee ID', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_rep_name', 'label' => 'Customer Representative Name/Sign.', 'type' => 'text', 'required' => false],
                                ['name' => 'customer_rep_contact', 'label' => 'Customer Representative Number', 'type' => 'text', 'required' => false],
                                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
                            ]
                        ],
                        [
                            'title' => 'FOR LAB USE ONLY',
                            'fields' => [
                                ['name' => 'lab_received_datetime', 'label' => 'Received Date & Time', 'type' => 'datetime-local', 'required' => false],
                                ['name' => 'lab_received_by', 'label' => 'Received by', 'type' => 'text', 'required' => false],
                                ['name' => 'lab_sample_condition', 'label' => 'Sample Condition', 'type' => 'select', 'options' => ['Acceptable', 'Not Acceptable'], 'required' => false],
                            ]
                        ]
                    ]
                ];
            } else {
                // Find a SubmissionForm template linked to this sample type
                $submissionForm = \App\Models\SubmissionForm::where('is_active', true)
                    ->whereHas('sampleTypes', function ($query) use ($sampleType) {
                        $query->where('sample_types.id', $sampleType->id);
                    })->first();

                if ($submissionForm) {
                    foreach ($submissionForm->sections as $section) {
                        foreach ($section->elementHolders as $holder) {
                            if ($holder->elements) {
                                foreach ($holder->elements as $element) {
                                    $fields[] = [
                                        'name' => $element->name,
                                        'label' => $element->label,
                                        'type' => $element->element_type ?? 'text',
                                        'required' => (bool)($element->is_required ?? false),
                                        'options' => $element->options ?? [],
                                    ];
                                }
                            }
                        }
                    }
                }

                if (empty($fields)) {
                    $fields = [
                        ['name' => 'sample_name', 'label' => 'Sample Name', 'type' => 'text', 'required' => true],
                        ['name' => 'collection_date', 'label' => 'Collection Date/Time', 'type' => 'datetime-local', 'required' => true],
                        ['name' => 'sample_description', 'label' => 'Sample Description', 'type' => 'textarea', 'required' => false],
                        ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
                    ];
                }
            }

            if ($isFood || $isWater || $isWasteWater) {
                // Use updateOrCreate to avoid destroying existing TRFI foreign-key references
                $existing = self::where('sample_type_id', $sampleType->id)->first();
                if ($existing) {
                    $existing->update([
                        'name' => $sampleType->name . ' Test Request Form',
                        'description' => 'Dynamic test request form for ' . $sampleType->name,
                        'form_fields' => $fields,
                        'is_active' => true,
                    ]);
                } else {
                    self::create([
                        'name' => $sampleType->name . ' Test Request Form',
                        'code' => 'TRF-' . strtoupper(substr(str_replace(' ', '', $sampleType->name), 0, 5)) . '-' . rand(100, 999),
                        'description' => 'Dynamic test request form for ' . $sampleType->name,
                        'sample_type_id' => $sampleType->id,
                        'form_fields' => $fields,
                        'is_active' => true,
                    ]);
                }
            } else {
                self::create([
                    'name' => $sampleType->name . ' Test Request Form',
                    'code' => 'TRF-' . strtoupper(substr(str_replace(' ', '', $sampleType->name), 0, 5)) . '-' . rand(100, 999),
                    'description' => 'Dynamic test request form for ' . $sampleType->name,
                    'sample_type_id' => $sampleType->id,
                    'form_fields' => $fields,
                    'is_active' => true,
                ]);
            }
        }
    }
}
