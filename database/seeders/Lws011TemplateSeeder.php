<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateField;
use App\Models\Monitoring\MonitoringTemplateConfiguredField;
use App\Models\Monitoring\MonitoringReadingStep;
use App\Models\Equipments\Equipment;
use App\Lab;
use App\LabSection;
use App\User;
use App\Company;

class Lws011TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetName = 'TEMPERATURE MONITORING RECORD FOR CHILLERS, REFRIGERATORS, AND FREEZERS';
        $targetCode = 'LWS-011';

        // Find or create the template
        $template = MonitoringTemplate::where('document_control_number', $targetCode)
            ->orWhere('name', $targetName)
            ->first();

        if ($template) {
            // Delete old configurations to prevent duplicates on re-run
            $template->fields()->delete();
            $template->configuredFields()->delete();
            $template->readingSteps()->delete();
            $template->formulaRules()->delete();
        } else {
            $template = new MonitoringTemplate();
        }

        $lab = Lab::first();
        $section = LabSection::where('does_environmental_analysis', true)->where('active', true)->first()
            ?? LabSection::first();
        $equipment = Equipment::first();
        $user = User::first();
        $company = Company::first();

        $template->fill([
            'name' => $targetName,
            'document_control_number' => $targetCode,
            'version' => 1,
            'effective_date' => '2026-06-15',
            'monitoring_category' => 'environmental',
            'status' => 'published',
            'is_active' => true,
            'lab_id' => $lab?->id,
            'company_id' => $company?->id,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);
        $template->save();

        // 1. Seed Reading Steps & Corresponding Fields
        $steps = [
            [
                'step_number' => 1,
                'variable_name' => 'chiller_temp_min',
                'variable_slug' => 'chiller_temp_min',
                'step_type' => 'input',
                'label' => 'Chiller Refrigerator Temp (Min)',
                'description' => 'Minimum temperature recorded for chiller/refrigerator',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'number', 'required' => true],
            ],
            [
                'step_number' => 2,
                'variable_name' => 'chiller_temp_max',
                'variable_slug' => 'chiller_temp_max',
                'step_type' => 'input',
                'label' => 'Chiller Refrigerator Temp (Max)',
                'description' => 'Maximum temperature recorded for chiller/refrigerator',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'number', 'required' => true],
            ],
            [
                'step_number' => 3,
                'variable_name' => 'chiller_temp',
                'variable_slug' => 'chiller_temp',
                'step_type' => 'input',
                'label' => 'Chiller Refrigerator Temp (Observed)',
                'description' => 'Observed / actual temperature recorded for chiller/refrigerator',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'number', 'required' => true],
            ],
            [
                'step_number' => 4,
                'variable_name' => 'freezer_temp_min',
                'variable_slug' => 'freezer_temp_min',
                'step_type' => 'input',
                'label' => 'Freezer Temp (Min)',
                'description' => 'Minimum temperature recorded for freezer',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'number', 'required' => true],
            ],
            [
                'step_number' => 5,
                'variable_name' => 'freezer_temp_max',
                'variable_slug' => 'freezer_temp_max',
                'step_type' => 'input',
                'label' => 'Freezer Temp (Max)',
                'description' => 'Maximum temperature recorded for freezer',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'number', 'required' => true],
            ],
            [
                'step_number' => 6,
                'variable_name' => 'freezer_temp',
                'variable_slug' => 'freezer_temp',
                'step_type' => 'input',
                'label' => 'Freezer Temp (Observed)',
                'description' => 'Observed / actual temperature recorded for freezer',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'number', 'required' => true],
            ],
            [
                'step_number' => 7,
                'variable_name' => 'calibration_valid',
                'variable_slug' => 'calibration_valid',
                'step_type' => 'input',
                'label' => 'Calibration Status',
                'description' => 'Is calibration status valid? (Yes/No)',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'select', 'options' => ['Yes' => 'Yes', 'No' => 'No'], 'required' => true],
            ],
            [
                'step_number' => 8,
                'variable_name' => 'wire_plug_condition',
                'variable_slug' => 'wire_plug_condition',
                'step_type' => 'input',
                'label' => 'Plug & Wire Condition',
                'description' => 'Is plug & wire condition acceptable? (Yes/No)',
                'show_in_monitoring_logs' => true,
                'input_config' => ['type' => 'select', 'options' => ['Yes' => 'Yes', 'No' => 'No'], 'required' => true],
            ],
        ];

        $sortOrder = 1;
        foreach ($steps as $step) {
            $readingStep = MonitoringReadingStep::create([
                'template_id' => $template->id,
                'step_number' => $step['step_number'],
                'variable_name' => $step['variable_name'],
                'step_type' => $step['step_type'],
                'label' => $step['label'],
                'description' => $step['description'],
                'variable_slug' => $step['variable_slug'],
                'show_in_monitoring_logs' => $step['show_in_monitoring_logs'],
                'input_config' => $step['input_config'],
            ]);

            $fieldType = ($step['input_config']['type'] ?? 'number') === 'select' ? 'select' : 'number';

            MonitoringTemplateField::create([
                'template_id' => $template->id,
                'field_key' => $step['variable_name'],
                'label' => $step['label'],
                'field_type' => $fieldType,
                'is_required' => true,
                'is_readonly' => false,
                'sort_order' => $sortOrder++,
                'field_config' => [
                    'reading_step_id' => $readingStep->id,
                    'step_type' => 'input',
                    'variable_slug' => $step['variable_slug'],
                    'show_in_monitoring_logs' => true,
                    'input_config' => $step['input_config'],
                ],
            ]);
        }

        // 2. Seed System Meta Data Field
        MonitoringTemplateField::create([
            'template_id' => $template->id,
            'field_key' => '__meta_scope_items',
            'label' => 'System Meta Data',
            'field_type' => 'metadata',
            'is_required' => false,
            'is_readonly' => true,
            'sort_order' => 9999,
            'field_config' => [
                'labs' => $lab ? [(string)$lab->id] : [],
                'sections' => $section ? [(string)$section->id] : [],
                'equipment' => $equipment ? [(string)$equipment->id] : [],
            ],
        ]);

        // 3. Seed Configured Fields (Top / Bottom parameters)
        $configuredFields = [
            [
                'placement' => 'top',
                'label' => 'Month/Year',
                'field_type' => 'month_day',
                'order' => 1,
                'help_text' => 'Select month and year of monitoring',
                'is_required' => true,
                'field_value_name' => 'month_year',
                'field_config' => null,
            ],
            [
                'placement' => 'top',
                'label' => 'Equipment ID',
                'field_type' => 'monitoring_equipment',
                'order' => 2,
                'help_text' => 'Assigned Equipment ID',
                'is_required' => true,
                'field_value_name' => 'equipment_id',
                'field_config' => ['source' => 'template_scope'],
            ],
            [
                'placement' => 'top',
                'label' => 'Location',
                'field_type' => 'lab_section_select',
                'order' => 3,
                'help_text' => 'Lab section location',
                'is_required' => true,
                'field_value_name' => 'location',
                'field_config' => ['source' => 'template_scope'],
            ],
            [
                'placement' => 'top',
                'label' => 'Thermometer ID',
                'field_type' => 'input',
                'order' => 4,
                'help_text' => 'Assigned Thermometer ID',
                'is_required' => false,
                'field_value_name' => 'thermometer_id',
                'field_config' => null,
            ],
            [
                'placement' => 'top',
                'label' => 'Equipment Name',
                'field_type' => 'input',
                'order' => 5,
                'help_text' => 'Name of the equipment',
                'is_required' => false,
                'field_value_name' => 'equipment_name',
                'field_config' => null,
            ],
            [
                'placement' => 'top',
                'label' => 'Tolerance / Limit',
                'field_type' => 'scope_expected_limits',
                'order' => 6,
                'help_text' => 'Acceptable limits',
                'is_required' => false,
                'field_value_name' => 'tolerance',
                'field_config' => ['limits_source' => 'lab_section', 'resolve_mode' => 'auto'],
            ],
            [
                'placement' => 'bottom',
                'label' => 'Prepared By',
                'field_type' => 'user_signature',
                'order' => 1,
                'help_text' => 'Operator signature',
                'is_required' => false,
                'field_value_name' => 'prepared_by',
                'field_config' => ['manager_source' => 'lab_section', 'auto_resolve' => true],
            ],
            [
                'placement' => 'bottom',
                'label' => 'Reviewed & Approved By',
                'field_type' => 'user_signature',
                'order' => 2,
                'help_text' => 'Verifier/Manager signature',
                'is_required' => false,
                'field_value_name' => 'approved_by',
                'field_config' => ['manager_source' => 'lab_section', 'auto_resolve' => true],
            ],
        ];

        foreach ($configuredFields as $field) {
            MonitoringTemplateConfiguredField::create([
                'template_id' => $template->id,
                'placement' => $field['placement'],
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'order' => $field['order'],
                'help_text' => $field['help_text'],
                'is_required' => $field['is_required'],
                'field_value_name' => $field['field_value_name'],
                'field_config' => $field['field_config'],
            ]);
        }
    }
}
