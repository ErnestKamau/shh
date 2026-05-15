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
                'standard' => 'App\Exports\Templates\Lab\StandardTemplateExporter',
                'sample_condition' => 'App\Exports\Templates\Lab\SampleConditionTemplateExporter',
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
                    'headers' => ['pricelist_code*', 'pricelist_name*', 'is_master', 'currency_code*', 'valid_till', 'item_code*', 'item_description', 'analyte_code*', 'unit_price*'],
                    'examples' => [
                        ['PL-001', 'Standard Pricelist', 1, 'USD', '2027-12-31', 'ITEM-001', 'Analysis Item 1', 'ANALYTE-001', '100.00'],
                    ],
                    'rules' => [
                        'pricelist_code' => 'required|string|max:100',
                        'pricelist_name' => 'required|string|max:255',
                        'is_master' => 'nullable|boolean',
                        'currency_code' => 'required|string|max:3',
                        'valid_till' => 'nullable|date',
                        'item_code' => 'required|string|max:100',
                        'item_description' => 'nullable|string|max:500',
                        'analyte_code' => 'required|string|max:100',
                        'unit_price' => 'required|numeric|min:0',
                    ],
                ],
                'analyte' => [
                    'headers' => ['code*', 'name*', 'decimal_places', 'reporting_symbol', 'reporting_unit', 'equipment_code', 'non_detectable', 'non_accredited'],
                    'examples' => [
                        ['ANALYTE-001', 'Calcium', 2, 'Ca', 'mg/L', 'EQ-001', 0, 0],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100|unique:analytes,code',
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
                    'headers' => ['code*', 'name*', 'is_results_attachable', 'disposal_count', 'report_template_code', 'default_product_code', 'active*'],
                    'examples' => [
                        ['ST-001', 'Sample Type Name', 1, 30, '', ''],
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
                        'code*', 'standard_code*', 'standard_number*', 'number*', 'standard_id*', 'ref_std*', 'ref_std_tzs_iso*', 'id*', 'tzs*',
                        'main_standard*', 'title*', 'standard_name*', 'main_standard_title*', 'standard*', 'standard_title*', 'matrix*', 'category*', 'environment*', 'source*',
                        'is_qc_standard', 'is_qc*', 'qc_standard*',
                        'qc_type', 'type*', 
                        'analyte_codes*', 'analytes*', 'parameters*', 'parameter*', 'analyte*', 'chemical_name*', 'parameter_name*',
                        'standard_value*', 'expected_value*', 'limit*', 'specification*', 'value*', 'max_limit*', 'min_limit*'
                    ],
                    'examples' => [
                        ['STD-001', 'ISO-17043', 1, 'external', 'ANALYTE-001,ANALYTE-002'],
                    ],
                    'rules' => [
                        'code' => 'required|string|max:100|unique:standards,code',
                        'main_standard' => 'required|string|max:255',
                        'is_qc_standard' => 'nullable|boolean',
                        'qc_type' => 'nullable|string|max:50',
                        'analyte_codes' => 'required|string',
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
                    'headers' => ['name*', 'equipment_name*', 'equipment_instrument*', 'instrument*', 'equipment*', 'equipment_number*', 'gcla_code*', 'make*', 'model*', 'serial_number*', 'serial_no*', 'serial#', 'asset_tag', 'asset_type_code*', 'asset_location_code*', 'calibration_days', 'maintenance_days', 'requires_daily_log', 'daily_log_value_type'],
                    'examples' => [
                        ['EQ-001', 'Shimadzu', 'HPLC-2030', 'SN-12345', 'AT-001', 'LOC-001', '365', '180', 1, 'numeric'],
                    ],
                    'rules' => [
                        'equipment_number' => 'required|string|max:100|unique:equipment,equipment_number',
                        'make' => 'required|string|max:100',
                        'model' => 'required|string|max:100',
                        'serial_number' => 'required|string|max:100',
                        'asset_type_code' => 'required|string|max:100',
                        'asset_location_code' => 'required|string|max:100',
                        'calibration_days' => 'nullable|integer|min:0',
                        'maintenance_days' => 'nullable|integer|min:0',
                        'requires_daily_log' => 'nullable|boolean',
                        'daily_log_value_type' => 'nullable|string|max:50',
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
        ];

        return $definitionMap[$module][$formType] ?? [];
    }
}
