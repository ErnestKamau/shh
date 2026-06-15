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
                'analyte' => 'App\Exports\Templates\Lab\UnifiedLabHierarchyTemplateExporter',
                'lab' => 'App\Exports\Templates\Lab\LabTemplateExporter',
                'sample_type' => 'App\Exports\Templates\Lab\UnifiedLabHierarchyTemplateExporter',
                'analysis_type' => 'App\Exports\Templates\Lab\AnalysisTypeTemplateExporter',
                'analysis_elements' => 'App\Exports\Templates\Lab\AnalysisElementsTemplateExporter',
                'standard' => 'App\Exports\Templates\Lab\UnifiedLabHierarchyTemplateExporter',
                'sample_condition' => 'App\Exports\Templates\Lab\SampleConditionTemplateExporter',
                'lab_hierarchy' => 'App\Exports\Templates\Lab\UnifiedLabHierarchyTemplateExporter',
                'amspec_parameters' => 'App\Exports\Templates\Lab\AmspecParametersTemplateExporter',
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
                'amspec_parameters' => [
                    'headers' => [
                        'Lab Section*',
                        'Sample Type*',
                        'Analysis Type*',
                        'Parameters*',
                        'Method*',
                        'Reporting Unit*',
                        'Decimal Places*',
                        'Accreditation*',
                        'Equipment*',
                    ],
                    'examples' => [
                        [
                            'Chemical',
                            'Food',
                            'Food and feed',
                            'Moisture and volatile matter',
                            'AMS/C/SOP/024',
                            'g/100g',
                            '2',
                            'Accredited',
                            'Balance/Hot Air Oven',
                        ],
                    ],
                    'rules' => [
                        'lab_section' => 'required|string|max:255',
                        'sample_type' => 'required|string|max:255',
                        'analysis_type' => 'required|string|max:255',
                        'parameters' => 'required|string|max:255',
                        'method' => 'required|string|max:255',
                        'reporting_unit' => 'required|string|max:50',
                        'decimal_places' => 'required|integer|min:0|max:10',
                        'accreditation' => 'required|string|max:50',
                        'equipment' => 'required|string|max:255',
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
                    'headers' => ['equipment_instrument*', 'instrument*', 'equipment*', 'model*', 'serial_number*', 'serial_no*', 'operating_software', 'gcla_code*', 'equipment_number*', 'lab_office_name*', 'lab_name*', 'country_of_origin', 'installation_year', 'power_requirement', 'manual_availability', 'status*'],
                    'examples' => [
                        ['3500xl Genetic Analyzer', 'Applied Biosystems(622-0015)', '31397-071', '3500 Series Data Collection Software', 'TR242*0002028', 'DNA Lab', 'Japan', '2019', '100-240V', 'NO', 'Working'],
                    ],
                    'rules' => [
                        'equipment_instrument' => 'required|string|max:255',
                        'model' => 'required|string|max:100',
                        'gcla_code' => 'required|string|max:100|unique:equipment,equipment_number',
                        'lab_office_name' => 'required|string|max:255',
                        'status' => 'required|string|max:100',
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
