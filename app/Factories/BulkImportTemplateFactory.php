<?php

namespace App\Factories;

class BulkImportTemplateFactory
{
    /**
     * Create a template generator for the given module and form type.
     */
    public function createTemplateGenerator(string $module, string $formType)
    {
        $generatorMap = [
            'lab' => [
                'pricelist' => 'App\Exports\Templates\Lab\PricelistTemplateExporter',
                'analyte' => 'App\Exports\Templates\Lab\AnalyteTemplateExporter',
                'lab' => 'App\Exports\Templates\Lab\LabTemplateExporter',
                'sample_type' => 'App\Exports\Templates\Lab\SampleTypeTemplateExporter',
                'analysis_type' => 'App\Exports\Templates\Lab\AnalysisTypeTemplateExporter',
                'analysis_elements' => 'App\Exports\Templates\Lab\AnalysisElementsTemplateExporter',
                'standard' => 'App\Exports\Templates\Lab\UnifiedLabHierarchyTemplateExporter',
                'sample_condition' => 'App\Exports\Templates\Lab\SampleConditionTemplateExporter',
                'lab_hierarchy' => 'App\Exports\Templates\Lab\UnifiedLabHierarchyTemplateExporter',
                'amspec_parameters' => 'App\Exports\Templates\Lab\AmspecParametersTemplateExporter',
                'analysis_method' => 'App\Exports\Templates\Lab\AnalysisMethodTemplateExporter',
            ],
            'equipment' => [
                'asset_type' => 'App\Exports\Templates\Equipment\AssetTypeTemplateExporter',
                'asset_location' => 'App\Exports\Templates\Equipment\AssetLocationTemplateExporter',
                'equipment' => 'App\Exports\Templates\Equipment\EquipmentTemplateExporter',
            ],
            'personnel' => [
                'department' => 'App\Exports\Templates\Personnel\DepartmentTemplateExporter',
                'user' => 'App\Exports\Templates\Personnel\UserTemplateExporter',
            ],
            'crm' => [
                'customer' => 'App\Exports\Templates\CRM\CRMCustomerTemplateExporter',
            ],
            'inventory' => [
                'inventory' => 'App\Exports\Templates\Inventory\InventoryItemTemplateExporter',
            ],
        ];

        $generatorClass = $generatorMap[$module][$formType] ?? null;

        if (!$generatorClass || !class_exists($generatorClass)) {
            throw new \Exception("Template generator not found for {$module}/{$formType}");
        }

        return new $generatorClass();
    }

    /**
     * Get template definition (headers, examples, rules) for a form type.
     */
    public function getTemplateDefinition(string $module, string $formType): array
    {
        $definitionMap = [
            'lab' => [
                'pricelist' => [
                    'headers' => ['pricelist_code*', 'pricelist_name*', 'is_master', 'currency_code*', 'valid_till', 'sample_type*', 'parameter*', 'price*'],
                    'examples' => [
                        ['GCLA/P/7', 'GCLA Price List v6', 'Yes', 'TZS', '2025-08-22', 'Non Alcoholic Beverages', 'Physical examination', '21200'],
                    ],
                    'rules' => [
                        'pricelist_code' => 'required|string|max:100',
                        'pricelist_name' => 'required|string|max:255',
                        'is_master' => 'nullable|string|max:50',
                        'currency_code' => 'required|string|max:3',
                        'valid_till' => 'nullable|date',
                        'sample_type' => 'required|string|max:255',
                        'parameter' => 'required|string|max:255',
                        'price' => 'required|string|max:100',
                    ],
                ],
                'analyte' => [
                    'headers' => ['code*', 'name*', 'decimal_places', 'reporting_symbol', 'reporting_unit', 'equipment_code', 'non_detectable', 'non_accredited'],
                    'examples' => [
                        ['ANALYTE-001', 'Calcium', '2', 'Ca', 'mg/L', 'EQ-001', '0', '0'],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100',
                        'name' => 'required|string|max:255',
                        'decimal_places' => 'nullable|integer|min:0|max:10',
                        'reporting_symbol' => 'nullable|string|max:50',
                        'reporting_unit' => 'nullable|string|max:100',
                        'equipment_code' => 'nullable|string|max:100',
                        'non_detectable' => 'nullable|boolean',
                        'non_accredited' => 'nullable|boolean',
                    ],
                ],
                'lab' => [
                    'headers' => ['directorate_name*', 'zone_name*', 'lab_code*', 'lab_name*', 'address', 'phone1', 'manager_email', 'start_sample_no', 'internal_or_external'],
                    'examples' => [
                        ['MAIN DIRECTORATE', 'Zone A', 'LAB-001', 'Lab A', '123 Main St', '+1234567890', 'manager@company.com', 1000, 'internal'],
                    ],
                    'rules' => [
                        'directorate_name' => 'required|string|max:255',
                        'zone_name' => 'required|string|max:255',
                        'lab_code' => 'required|string|max:100|unique:labs,code',
                        'lab_name' => 'required|string|max:255',
                        'address' => 'nullable|string|max:500',
                        'phone1' => 'nullable|string|max:50',
                        'manager_email' => 'nullable|email|max:255',
                        'start_sample_no' => 'nullable|integer|min:0',
                        'internal_or_external' => 'nullable|in:internal,external',
                    ],
                ],
                'sample_type' => [
                    'headers' => ['code*', 'name*', 'is_results_attachable', 'disposal_count', 'report_template_code', 'default_product_code'],
                    'examples' => [
                        ['ST-001', 'Water Material', '1', '30', '', ''],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100',
                        'name' => 'required|string|max:255',
                        'is_results_attachable' => 'nullable|boolean',
                        'disposal_count' => 'nullable|integer|min:0',
                        'report_template_code' => 'nullable|string|max:100',
                        'default_product_code' => 'nullable|string|max:100',
                    ],
                ],
                'analysis_type' => [
                    'headers' => ['code*', 'name*', 'sample_type_code*', 'lab_code*', 'has_no_result', 'reporting_time'],
                    'examples' => [
                        ['AT-001', 'Water Analysis', 'ST-001', 'LAB-001', 0, '2'],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100|unique:analysis_types,code',
                        'name' => 'required|string|max:255',
                        'sample_type_code' => 'required|string|max:100',
                        'lab_code' => 'required|string|max:100',
                        'has_no_result' => 'nullable|boolean',
                        'reporting_time' => 'nullable|string|max:50',
                    ],
                ],
                'analysis_elements' => [
                    'headers' => ['analysis_type_code*', 'analyte_code*', 'lab_section_code*', 'equipment_code', 'lod', 'hod', 'level', 'method_sequence_name', 'procedure_worksheet_name'],
                    'examples' => [
                        ['AT-001', 'ANALYTE-001', 'LS-001', 'EQ-001', '0.01', '100', 'high', '', ''],
                    ],
                    'rules' => [
                        'analysis_type_code' => 'required|string|max:100',
                        'analyte_code' => 'required|string|max:100',
                        'lab_section_code' => 'required|string|max:100',
                        'equipment_code' => 'nullable|string|max:100',
                        'lod' => 'nullable|numeric|min:0',
                        'hod' => 'nullable|numeric|min:0',
                        'level' => 'nullable|string|max:50',
                        'method_sequence_name' => 'nullable|string|max:255',
                        'procedure_worksheet_name' => 'nullable|string|max:255',
                    ],
                ],
                'standard' => [
                    'headers' => [
                        'sample_type_code*', 'sample_type_name*', 'is_results_attachable', 'disposal_count',
                        'analysis_type_code*', 'analysis_type_name*', 'lab_code*', 'has_no_result', 'reporting_time',
                        'lab_section_code*', 'equipment_code', 'lod', 'hod', 'level', 'method_sequence_name', 'procedure_worksheet_name',
                        'analyte_code*', 'analyte_name*', 'decimal_places', 'reporting_symbol', 'reporting_unit', 'non_detectable', 'non_accredited',
                        'standard_code', 'standard_name', 'is_qc_standard', 'qc_type',
                        'standard_value_code', 'standard_value_name', 'standard_value_type',
                        'standard_low', 'standard_high', 'standard_matrix_operator', 'standard_value'
                    ],
                    'examples' => [
                        [
                            'ST-WATER', 'Water Material', '1', '30',
                            'AT-POTABLE', 'Potable Water Analysis', 'LAB-01', '0', '2',
                            'LS-PHYSCHEM', 'EQ-PH01', '0.01', '14.0', 'high', 'SEQ-PH', 'PROC-PH',
                            'AN-PH', 'pH Level', '2', 'pH', 'units', '0', '0',
                            'STD-TBS-WATER', 'TBS Potable Water Standard', '0', 'chemical',
                            'VAL-PH', 'pH Limit', 'range', '6.5', '8.5', '', ''
                        ],
                    ],
                    'rules' => [
                        'sample_type_code' => 'required|string|max:100',
                        'sample_type_name' => 'required|string|max:255',
                        'is_results_attachable' => 'nullable|boolean',
                        'disposal_count' => 'nullable|integer|min:0',
                        'analysis_type_code' => 'required|string|max:100',
                        'analysis_type_name' => 'required|string|max:255',
                        'lab_code' => 'required|string|max:100',
                        'has_no_result' => 'nullable|boolean',
                        'reporting_time' => 'nullable|string|max:50',
                        'lab_section_code' => 'required|string|max:100',
                        'equipment_code' => 'nullable|string|max:100',
                        'lod' => 'nullable|numeric|min:0',
                        'hod' => 'nullable|numeric|min:0',
                        'level' => 'nullable|string|max:50',
                        'method_sequence_name' => 'nullable|string|max:255',
                        'procedure_worksheet_name' => 'nullable|string|max:255',
                        'analyte_code' => 'required|string|max:100',
                        'analyte_name' => 'required|string|max:255',
                        'decimal_places' => 'nullable|integer|min:0|max:10',
                        'reporting_symbol' => 'nullable|string|max:50',
                        'reporting_unit' => 'nullable|string|max:100',
                        'non_detectable' => 'nullable|boolean',
                        'non_accredited' => 'nullable|boolean',
                        'standard_code' => 'nullable|string|max:100',
                        'standard_name' => 'nullable|string|max:255',
                        'is_qc_standard' => 'nullable|boolean',
                        'qc_type' => 'nullable|string|max:100',
                        'standard_value_code' => 'nullable|string|max:100',
                        'standard_value_name' => 'nullable|string|max:255',
                        'standard_value_type' => 'nullable|string|max:100',
                        'standard_low' => 'nullable|string|max:100',
                        'standard_high' => 'nullable|string|max:100',
                        'standard_matrix_operator' => 'nullable|string|max:100',
                        'standard_value' => 'nullable|string|max:100',
                    ],
                ],
                'sample_condition' => [
                    'headers' => ['sample_type_code*', 'condition_name*', 'short_name', 'reporting_time'],
                    'examples' => [
                        ['ST-001', 'Room Temperature', 'RT', ''],
                    ],
                    'rules' => [
                        'sample_type_code' => 'required|string|max:100',
                        'condition_name' => 'required|string|max:255',
                        'short_name' => 'nullable|string|max:50',
                        'reporting_time' => 'nullable|string|max:50',
                    ],
                ],
                'lab_hierarchy' => [
                    'headers' => [
                        'sample_type_code*', 'sample_type_name*', 'is_results_attachable', 'disposal_count',
                        'analysis_type_code*', 'analysis_type_name*', 'lab_code*', 'has_no_result', 'reporting_time',
                        'lab_section_code*', 'equipment_code', 'lod', 'loq', 'level', 'method', 'method_sequence_name', 'procedure_worksheet_name',
                        'analyte_code', 'analyte_name*', 'decimal_places', 'reporting_symbol', 'reporting_unit', 'non_detectable', 'non_accredited',
                        'standard_code', 'standard_name', 'is_qc_standard', 'qc_type',
                        'standard_value_code', 'standard_value_name', 'standard_value_type',
                        'standard_low', 'standard_high', 'standard_matrix_operator', 'standard_value'
                    ],
                    'examples' => [
                        [
                            'Food & Feed', 'Food & Feed', '1', '30',
                            'General Foods', 'General Foods', 'AMSPEC', '0', '',
                            'Microbiology', '', '', '10', '', 'ISO 4833-1', '', '',
                            '', 'Mesophilic Aerobic Plate Count in Food Samples', '1', '', 'CFU/g', '0', 'No',
                            '', '', '', '',
                            '', '', '', '', '', ''
                        ],
                    ],
                    'rules' => [
                        'sample_type_code' => 'required|string|max:100',
                        'sample_type_name' => 'required|string|max:255',
                        'is_results_attachable' => 'nullable|boolean',
                        'disposal_count' => 'nullable|integer|min:0',
                        'analysis_type_code' => 'required|string|max:100',
                        'analysis_type_name' => 'required|string|max:255',
                        'lab_code' => 'required|string|max:100',
                        'has_no_result' => 'nullable|boolean',
                        'reporting_time' => 'nullable|string|max:50',
                        'lab_section_code' => 'required|string|max:100',
                        'equipment_code' => 'nullable|string|max:100',
                        'lod' => 'nullable|numeric|min:0',
                        'loq' => 'nullable|numeric|min:0',
                        'hod' => 'nullable|numeric|min:0',
                        'level' => 'nullable|string|max:50',
                        'method' => 'nullable|string|max:255',
                        'method_sequence_name' => 'nullable|string|max:255',
                        'procedure_worksheet_name' => 'nullable|string|max:255',
                        'analyte_code' => 'nullable|string|max:100',
                        'analyte_name' => 'required|string|max:255',
                        'decimal_places' => 'nullable|integer|min:0|max:10',
                        'reporting_symbol' => 'nullable|string|max:50',
                        'reporting_unit' => 'nullable|string|max:100',
                        'non_detectable' => 'nullable|boolean',
                        'non_accredited' => 'nullable|boolean',
                        'standard_code' => 'nullable|string|max:100',
                        'standard_name' => 'nullable|string|max:255',
                        'is_qc_standard' => 'nullable|boolean',
                        'qc_type' => 'nullable|string|max:100',
                        'standard_value_code' => 'nullable|string|max:100',
                        'standard_value_name' => 'nullable|string|max:255',
                        'standard_value_type' => 'nullable|string|max:100',
                        'standard_low' => 'nullable|string|max:100',
                        'standard_high' => 'nullable|string|max:100',
                        'standard_matrix_operator' => 'nullable|string|max:100',
                        'standard_value' => 'nullable|string|max:100',
                    ],
                ],
                'amspec_parameters' => [
                    'headers' => [
                        'sample_type_code',
                        'sample_type_name*',
                        'analysis_type_code',
                        'analysis_type_name*',
                        'lab_name*',
                        'lab_section_name*',
                        'analyte_name*',
                        'reporting_unit',
                        'decimal_places',
                        'lod',
                        'loq',
                        'non_accredited',
                        'equipment_name',
                        'equipment_number',
                        'method',
                    ],
                    'examples' => [
                        [
                            'FOOD',
                            'Food',
                            'FOOD_AND_FEED',
                            'Food and Feed',
                            'Microbiology Lab',
                            'Microbiology',
                            'Mesophilic Aerobic Plate Count',
                            'CFU/g',
                            '1',
                            '',
                            '10',
                            'FALSE',
                            'Incubator, Biosafety Cabinet, Colony counter',
                            'AMS/M/INS/037, AMS/M/INS/051, AMS/M/INS/034',
                            'AMS/M/SOP/023',
                        ],
                    ],
                    'rules' => [
                        'sample_type_code' => 'nullable|string|max:100',
                        'sample_type_name' => 'required|string|max:255',
                        'analysis_type_code' => 'nullable|string|max:100',
                        'analysis_type_name' => 'required|string|max:255',
                        'lab_name' => 'required|string|max:255',
                        'lab_section_name' => 'required|string|max:255',
                        'analyte_name' => 'required|string|max:255',
                        'reporting_unit' => 'nullable|string|max:100',
                        'decimal_places' => 'nullable|integer|min:0|max:10',
                        'lod' => 'nullable|numeric|min:0',
                        'loq' => 'nullable|numeric|min:0',
                        'non_accredited' => 'nullable|boolean',
                        'equipment_name' => 'nullable|string|max:500',
                        'equipment_number' => 'nullable|string|max:500',
                        'method' => 'nullable|string|max:255',
                    ],
                ],
                'analysis_method' => [
                    'headers' => [
                        'Method Name*',
                        'Method Code*',
                        'Description',
                        'Method Category*',
                        'Reference Method',
                        'Method Version',
                        'Active',
                    ],
                    'examples' => [
                        [
                            'CMMEF 5th Edition Chapter 8',
                            'CMMEF 5th Edition Chapter 8',
                            'Mesophilic aerobic plate count reference method',
                            'Reference Method',
                            '',
                            '',
                            'Yes',
                        ],
                        [
                            'AMS/M/SOP/023',
                            'AMS/M/SOP/023',
                            'In house MAPC procedure',
                            'Laboratory Test Method',
                            'CMMEF 5th Edition Chapter 8',
                            'Rev.00',
                            'Yes',
                        ],
                    ],
                    'rules' => [
                        'method_name' => 'required|string|max:255',
                        'method_code' => 'required|string|max:255',
                        'description' => 'nullable|string',
                        'method_category' => 'required|string|max:100',
                        'reference_method' => 'nullable|string|max:255',
                        'method_version' => 'nullable|string|max:100',
                        'active' => 'nullable|boolean',
                    ],
                ],
            ],
            'equipment' => [
                'asset_type' => [
                    'headers' => ['code*', 'description*', 'is_active'],
                    'examples' => [
                        ['AT-001', 'HPLC Machine', 1],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100|unique:asset_types,asset_code',
                        'description' => 'required|string|max:500',
                        'is_active' => 'nullable|boolean',
                    ],
                ],
                'asset_location' => [
                    'headers' => ['code*', 'name*', 'is_active'],
                    'examples' => [
                        ['LOC-001', 'Lab Building A', 1],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100|unique:asset_locations,location_code',
                        'name' => 'required|string|max:255',
                        'is_active' => 'nullable|boolean',
                    ],
                ],
                'equipment' => [
                    // Primary AmSpec master-list columns first (aligned with template examples),
                    // then legacy aliases for header detection on older spreadsheets.
                    'headers' => [
                        'equipment_name*',
                        'equipment_id*',
                        'serial*',
                        'model*',
                        'manufacturer*',
                        'department*',
                        'calibration_duration_months',
                        'calibration_date',
                        'calibration_due_date',
                        'operational_status',
                        'equipment_instrument*',
                        'equipment_number*',
                        'gcla_code*',
                        'serial_number*',
                        'serial_no*',
                        'make',
                        'assigned_department',
                        'lab_office_name*',
                        'lab_name*',
                        'calibration_interval_days',
                        'previous_calibration_date',
                        'status*',
                        'operating_software',
                        'country_of_origin',
                        'installation_year',
                        'power_requirement',
                        'manual_availability',
                        'instrument*',
                        'name*',
                        'equipment*',
                        'code*',
                    ],
                    'examples' => [
                        [
                            'Example GC Analyzer',
                            'AMS/C/INS/000',
                            'SN-EXAMPLE-001',
                            'MODEL-001',
                            'Example Manufacturer',
                            'Chemistry',
                            '12',
                            '2025-01-15',
                            '2026-01-15',
                            'In Use',
                        ],
                    ],
                    'rules' => [
                        'equipment_name' => 'required|string|max:255',
                        'equipment_id' => 'required|string|max:100|unique:equipment,equipment_number',
                        'model' => 'required|string|max:100',
                        'department' => 'required|string|max:255',
                        'operational_status' => 'nullable|string|max:100',
                    ],
                ],
            ],
            'personnel' => [
                'department' => [
                    'headers' => ['name*', 'is_active'],
                    'examples' => [
                        ['Quality Assurance', 1],
                    ],
                    'rules' => [
                        'name' => 'required|string|max:255',
                        'is_active' => 'nullable|boolean',
                    ],
                ],
                'user' => [
                    'headers' => ['first_name*', 'last_name*', 'full_name', 'email*', 'zone_code', 'zone_name', 'department_name', 'position'],
                    'examples' => [
                        ['John', 'Doe', 'John Doe', 'john@example.com', 'ZONE-001', 'Main Zone', 'Quality Assurance', 'Analyst'],
                    ],
                    'rules' => [
                        'first_name' => 'required|string|max:100',
                        'last_name' => 'required|string|max:100',
                        'email' => 'required|email|max:255|unique:users,email',
                        'zone_code' => 'nullable|string|max:100',
                        'zone_name' => 'nullable|string|max:255',
                        'department_name' => 'nullable|string|max:255',
                        'position' => 'nullable|string|max:255',
                    ],
                ],
            ],
            'crm' => [
                'customer' => [
                    'headers' => ['name*', 'customer_code*', 'physical_address', 'email*', 'phone1*', 'phone2', 'country_code*', 'vat_no', 'credit_days', 'currency_code*', 'lpos_required'],
                    'examples' => [
                        ['Acme Corporation', 'CUST-001', '123 Business St', 'contact@acme.com', '+1234567890', '+0987654321', 'US', '123456789', '30', 'USD', 1],
                    ],
                    'rules' => [
                        'name' => 'required|string|max:255',
                        'customer_code' => 'required|string|max:100|unique:crm_customers,code',
                        'physical_address' => 'nullable|string|max:500',
                        'email' => 'required|email|max:255',
                        'phone1' => 'required|string|max:50',
                        'phone2' => 'nullable|string|max:50',
                        'country_code' => 'required|string|max:2',
                        'vat_no' => 'nullable|string|max:50',
                        'credit_days' => 'nullable|integer|min:0',
                        'currency_code' => 'required|string|max:3',
                        'lpos_required' => 'nullable|boolean',
                    ],
                ],
            ],
            'inventory' => [
                'inventory' => [
                    'headers' => ['name*', 'description', 'category*', 'volume_unit*', 'qty*'],
                    'examples' => [
                        ['Calcium Sulphate', 'Calcium Sulphate Reagent', 'Chemical', '500gms', '1'],
                    ],
                    'rules' => [
                        'name' => 'required|string|max:255',
                        'description' => 'nullable|string',
                        'category' => 'required|string|max:255',
                        'volume_unit' => 'required|string|max:100',
                        'qty' => 'required|numeric|min:0',
                    ],
                ],
            ],
        ];

        $definition = $definitionMap[$module][$formType] ?? [];
        if (!empty($definition)) {
            // Remove asterisk from headers
            if (isset($definition['headers'])) {
                $definition['headers'] = array_map(function($header) {
                    return str_replace('*', '', $header);
                }, $definition['headers']);
            }
            // Make all rules nullable
            if (isset($definition['rules'])) {
                $definition['rules'] = array_map(function($rule) {
                    if (is_string($rule)) {
                        $rule = str_replace('required', 'nullable', $rule);
                    }
                    return $rule;
                }, $definition['rules']);
            }
        }
        return $definition;
    }
}
