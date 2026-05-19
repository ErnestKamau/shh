<?php

namespace App\Livewire\Monitoring;

use App\Lab;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateField;
use App\Models\Monitoring\MonitoringVariable;
use App\Models\Monitoring\MonitoringFormulaRule;
use App\Services\Monitoring\FormulaEngineService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditMonitoringTemplate extends Component
{
    // Step management
    public int $currentStep = 1;

    // Template type
    public string $templateType = 'environmental'; // 'environmental' or 'equipment'
    
    public ?MonitoringTemplate $template = null;

    // Step 1: Basic Info
    public string $name = '';
    public string $documentControlNumber = '';
    public string $version = '1';
    public string $effectiveDate = '';
    public string $status = 'draft';

    // Step 2: Lab Selection
    public array $selectedLabIds = [];

    // Step 3: Section/Equipment Selection
    public array $selectedSectionIds = [];
    public array $selectedEquipmentIds = [];

    // Step 4: Column Structure
    public array $columnStructure = [];

    // Inline variable definition modal state
    public array $newVariable = [
        'name' => '',
        'slug' => '',
        'variable_type' => 'constant',
        'constant_value' => '',
        'description' => '',
    ];

    public bool $showVariableModal = false;

    public function mount(MonitoringTemplate $template): void
    {
        $this->template = $template;
        $this->name = $template->name;
        $this->documentControlNumber = $template->document_control_number ?? '';
        $this->version = (string) $template->version;
        $this->effectiveDate = $template->effective_date ? \Carbon\Carbon::parse($template->effective_date)->format('Y-m-d') : '';
        $this->status = $template->status;
        $this->templateType = $template->monitoring_category ?? 'environmental';
        $this->selectedLabIds = $template->lab_id ? [$template->lab_id] : [];
        
        $this->loadColumnStructure();
    }
    
    protected function loadColumnStructure()
    {
        $fields = MonitoringTemplateField::with('formulaRule')->where('template_id', $this->template->id)->orderBy('sort_order')->get();
        
        $structure = [];
        foreach ($fields as $field) {
            if ($field->field_key === '__meta_scope_items') {
                $meta = is_string($field->field_config) ? json_decode($field->field_config, true) : ($field->field_config ?? []);
                $this->selectedSectionIds = $meta['sections'] ?? [];
                $this->selectedEquipmentIds = $meta['equipment'] ?? [];
                continue;
            }

            $config = is_string($field->field_config) ? json_decode($field->field_config, true) : ($field->field_config ?? []);
            $colId = $config['column_id'] ?? 'col_' . uniqid();
            $colName = $config['column_name'] ?? 'Column';
            $rowId = $config['row_id'] ?? 'row_' . uniqid();
            $rowLabel = $config['row_label'] ?? $field->label;
            
            if (!isset($structure[$colId])) {
                $structure[$colId] = [
                    'id' => $colId,
                    'name' => $colName,
                    'frequency' => 'daily',
                    'rows' => []
                ];
            }
            
            $rowType = $field->field_type === 'formula' ? 'formula' : 'input';
            $variableSlug = $config['variable_slug'] ?? '';
            $expression = '';
            
            if ($rowType === 'formula' && $field->formulaRule) {
                $expression = $field->formulaRule->expression;
            }
            
            $structure[$colId]['rows'][] = [
                'id' => $rowId,
                'label' => $rowLabel,
                'field_key' => $field->field_key,
                'type' => $rowType,
                'variable_slug' => $variableSlug,
                'formula_expression' => $expression,
            ];
        }
        
        $this->columnStructure = array_values($structure);
    }

    public function getAssignedLabsProperty()
    {
        return Lab::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getEnvironmentalSectionsByLabProperty()
    {
        if (empty($this->selectedLabIds)) {
            return collect();
        }

        return LabSection::query()
            ->whereIn('lab_id', $this->selectedLabIds)
            ->where('does_environmental_analysis', true)
            ->where('active', true)
            ->orderBy('lab_id')
            ->orderBy('name')
            ->with(['lab', 'equipment', 'equipment.latestCalibration', 'reportingUnit'])
            ->get();
    }

    public function getEquipmentByLabProperty()
    {
        if (empty($this->selectedLabIds)) {
            return collect();
        }

        return Equipment::query()
            ->whereIn('lab_id', $this->selectedLabIds)
            ->where('requires_daily_log', true)
            ->where('active', true)
            ->orderBy('lab_id')
            ->orderBy('name')
            ->with(['latestCalibration'])
            ->get();
    }

    public function getSelectedSectionsProperty()
    {
        if (empty($this->selectedSectionIds)) {
            return collect();
        }

        return $this->environmentalSectionsByLab->whereIn('id', $this->selectedSectionIds);
    }

    public function getSelectedEquipmentsProperty()
    {
        if (empty($this->selectedEquipmentIds)) {
            return collect();
        }

        return $this->equipmentByLab->whereIn('id', $this->selectedEquipmentIds);
    }

    public function nextStep(): void
    {
        $this->validate($this->getStepValidationRules());

        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function setTemplateType(string $type): void
    {
        if (in_array($type, ['environmental', 'equipment'], true)) {
            $this->templateType = $type;
        }
    }

    public function toggleLab(string $labId): void
    {
        if (in_array($labId, $this->selectedLabIds)) {
            $this->selectedLabIds = array_values(array_filter(
                $this->selectedLabIds,
                fn($id) => $id !== $labId
            ));
        } else {
            $this->selectedLabIds[] = $labId;
        }

        // Reset selection for next step
        $this->selectedSectionIds = [];
        $this->selectedEquipmentIds = [];
    }

    public function toggleSection(string $sectionId): void
    {
        if (in_array($sectionId, $this->selectedSectionIds)) {
            $this->selectedSectionIds = array_values(array_filter(
                $this->selectedSectionIds,
                fn($id) => $id !== $sectionId
            ));
        } else {
            $this->selectedSectionIds[] = $sectionId;
        }
    }

    public function toggleEquipment(string $equipmentId): void
    {
        if (in_array($equipmentId, $this->selectedEquipmentIds)) {
            $this->selectedEquipmentIds = array_values(array_filter(
                $this->selectedEquipmentIds,
                fn($id) => $id !== $equipmentId
            ));
        } else {
            $this->selectedEquipmentIds[] = $equipmentId;
        }
    }

    public function getAvailableVariablesProperty()
    {
        $dbVars = MonitoringVariable::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name', 'variable_type']);

        $predefined = collect([
            [
                'slug' => 'correction_factor',
                'name' => 'Equipment Correction Factor (Dynamic)',
                'variable_type' => 'dynamic',
            ],
            [
                'slug' => 'uncertainty_of_measure',
                'name' => 'Equipment Uncertainty of Measure (Dynamic)',
                'variable_type' => 'dynamic',
            ],
        ]);

        return $predefined->concat($dbVars);
    }

    public function openVariableModal(): void
    {
        $this->newVariable = [
            'name' => '',
            'slug' => '',
            'variable_type' => 'constant',
            'constant_value' => '',
            'description' => '',
        ];
        $this->resetErrorBag(['newVariable.name', 'newVariable.slug', 'newVariable.constant_value']);
        $this->showVariableModal = true;
    }

    public function closeVariableModal(): void
    {
        $this->showVariableModal = false;
    }

    public function createCustomVariable(): void
    {
        $this->validate([
            'newVariable.name' => 'required|string|max:255',
            'newVariable.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                function ($attribute, $value, $fail) {
                    if (in_array($value, ['correction_factor', 'uncertainty_of_measure'], true)) {
                        $fail('The slug matches a predefined system variable.');
                    }
                    $exists = MonitoringVariable::where('slug', $value)->exists();
                    if ($exists) {
                        $fail('The variable slug has already been taken.');
                    }
                }
            ],
            'newVariable.constant_value' => 'required|string|max:255',
            'newVariable.description' => 'nullable|string|max:1000',
        ], [], [
            'newVariable.name' => 'variable name',
            'newVariable.slug' => 'variable slug',
            'newVariable.constant_value' => 'value',
        ]);

        MonitoringVariable::create([
            'name' => $this->newVariable['name'],
            'slug' => $this->newVariable['slug'],
            'variable_type' => 'constant',
            'value' => ['constant_value' => $this->newVariable['constant_value']],
            'description' => $this->newVariable['description'] ?: null,
            'is_active' => true,
            'company_id' => Auth::user()?->company_id,
        ]);

        $this->showVariableModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Custom variable "' . $this->newVariable['name'] . '" defined successfully!']);
    }

    public function addColumn(): void
    {
        $newColumn = [
            'id' => 'col_' . uniqid(),
            'name' => '',
            'frequency' => 'daily',
            'rows' => [
                [
                    'id' => 'row_' . uniqid(),
                    'label' => 'Initial Value',
                    'field_key' => 'initial_value',
                    'type' => 'input',
                    'variable_slug' => '',
                    'formula_expression' => '',
                ],
                [
                    'id' => 'row_' . uniqid(),
                    'label' => 'Final Value',
                    'field_key' => 'final_value',
                    'type' => 'input',
                    'variable_slug' => '',
                    'formula_expression' => '',
                ],
            ],
        ];

        $this->columnStructure[] = $newColumn;
    }

    public function removeColumn(string $columnId): void
    {
        $this->columnStructure = array_values(array_filter(
            $this->columnStructure,
            fn($col) => $col['id'] !== $columnId
        ));
    }

    public function updateColumnName(string $columnId, string $name): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                $col['name'] = $name;
                break;
            }
        }
    }

    public function addRowToColumn(string $columnId): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                $col['rows'][] = [
                    'id' => 'row_' . uniqid(),
                    'label' => '',
                    'field_key' => '',
                    'type' => 'input',
                    'variable_slug' => '',
                    'formula_expression' => '',
                ];
                break;
            }
        }
    }

    public function removeRowFromColumn(string $columnId, string $rowId): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                $col['rows'] = array_values(array_filter(
                    $col['rows'],
                    fn($row) => $row['id'] !== $rowId
                ));
                break;
            }
        }
    }

    public function updateRowLabel(string $columnId, string $rowId, string $label): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                foreach ($col['rows'] as &$row) {
                    if ($row['id'] === $rowId) {
                        $row['label'] = $label;
                        if (empty($row['field_key'])) {
                            $colClean = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $col['name'])));
                            $rowClean = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $label)));
                            $row['field_key'] = trim($colClean . '_' . $rowClean, '_');
                        }
                        break;
                    }
                }
                break;
            }
        }
    }

    public function updateRowType(string $columnId, string $rowId, string $type): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                foreach ($col['rows'] as &$row) {
                    if ($row['id'] === $rowId) {
                        $row['type'] = $type;
                        break;
                    }
                }
                break;
            }
        }
    }

    public function updateRowFormulaExpression(string $columnId, string $rowId, string $expression): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                foreach ($col['rows'] as &$row) {
                    if ($row['id'] === $rowId) {
                        $row['formula_expression'] = $expression;
                        // Reset validation state when expression changes
                        unset($row['expression_valid']);
                        unset($row['expression_message']);
                        $this->columnStructure = $this->columnStructure; // Force re-render
                        break;
                    }
                }
                break;
            }
        }
    }

    public function getAvailableRowVariables(string $columnId, string $rowId): array
    {
        $variables = [];
        
        // Add predefined and dynamic variables
        foreach ($this->availableVariables as $var) {
            $variables[] = [
                'name' => $var['slug'],
                'label' => $var['name'],
                'type' => $var['variable_type'],
            ];
        }

        // Add variables from previous rows in the matrix
        foreach ($this->columnStructure as $col) {
            foreach ($col['rows'] as $row) {
                // Skip the current row being edited
                if ($row['id'] === $rowId) continue;
                
                if (!empty($row['field_key'])) {
                    $variables[] = [
                        'name' => $row['field_key'],
                        'label' => ($col['name'] ?: 'Column') . ' - ' . $row['label'],
                        'type' => $row['type'] ?? 'input',
                    ];
                }
            }
        }

        return $variables;
    }

    public function validateRowExpression(string $columnId, string $rowId, FormulaEngineService $formulaEngine): void
    {
        $expression = '';
        $rowRef = null;

        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                foreach ($col['rows'] as &$row) {
                    if ($row['id'] === $rowId) {
                        $expression = $row['formula_expression'] ?? '';
                        $rowRef = &$row;
                        break;
                    }
                }
            }
        }

        if (!$rowRef || empty($expression)) {
            return;
        }

        $availableVars = $this->getAvailableRowVariables($columnId, $rowId);
        $availableVarNames = array_column($availableVars, 'name');

        $result = $formulaEngine->validateExpression($expression, $availableVarNames);

        $rowRef['expression_valid'] = $result['valid'];
        $rowRef['expression_message'] = $result['message'];
        
        $this->columnStructure = $this->columnStructure; // Force Livewire to detect deep array changes

        if ($result['valid']) {
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Expression is valid!']);
        } else {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Expression invalid. See details below.']);
        }
    }

    public function updateRowVariable(string $columnId, string $rowId, string $variableSlug): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                foreach ($col['rows'] as &$row) {
                    if ($row['id'] === $rowId) {
                        $row['variable_slug'] = $variableSlug;
                        break;
                    }
                }
                break;
            }
        }
    }

    public function saveTemplate()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'documentControlNumber' => 'nullable|string|max:255',
            'version' => 'required|integer|min:1',
            'effectiveDate' => 'nullable|date',
            'selectedLabIds' => 'required|array|min:1',
            'selectedSectionIds' => $this->templateType === 'environmental' ? 'required|array|min:1' : 'nullable',
            'selectedEquipmentIds' => $this->templateType === 'equipment' ? 'required|array|min:1' : 'nullable',
            'columnStructure' => 'required|array|min:1',
        ]);

        $this->template->update([
            'name' => $this->name,
            'document_control_number' => $this->documentControlNumber ?: null,
            'version' => (int) $this->version,
            'effective_date' => $this->effectiveDate ?: null,
            'monitoring_category' => $this->templateType,
            'status' => $this->status,
            'lab_id' => $this->selectedLabIds[0] ?? null, // Primary lab
            'updated_by' => Auth::id(),
        ]);

        // Delete existing fields and rules before inserting new ones
        MonitoringTemplateField::where('template_id', $this->template->id)->delete();
        MonitoringFormulaRule::where('template_id', $this->template->id)->delete();

        // Create individual MonitoringTemplateField rows for every column & row cell
        $sortOrder = 1;
        $usedKeys = [];
        foreach ($this->columnStructure as $column) {
            foreach ($column['rows'] as $row) {
                // Use explicit field_key or fallback
                $fieldKey = !empty($row['field_key']) ? $row['field_key'] : ('col_' . $sortOrder);

                // Ensure it is unique in this template
                $baseKey = $fieldKey;
                $counter = 1;
                while (in_array($fieldKey, $usedKeys) || MonitoringTemplateField::where('template_id', $this->template->id)->where('field_key', $fieldKey)->exists()) {
                    $fieldKey = $baseKey . '_' . $counter++;
                }
                $usedKeys[] = $fieldKey;

                $isFormula = ($row['type'] ?? 'input') === 'formula';
                $formulaId = null;

                if ($isFormula && !empty($row['formula_expression'])) {
                    $createdFormula = MonitoringFormulaRule::create([
                        'template_id' => $this->template->id,
                        'name' => ($column['name'] ?: 'Column') . ' - ' . ($row['label'] ?: 'Computed'),
                        'output_key' => $fieldKey,
                        'expression' => $row['formula_expression'],
                        'pass_condition_expression' => null, // Kept simple per row UI requirements
                        'is_active' => true,
                        'company_id' => Auth::user()?->company_id,
                    ]);
                    $formulaId = $createdFormula->id;
                }

                MonitoringTemplateField::create([
                    'template_id' => $this->template->id,
                    'formula_rule_id' => $formulaId,
                    'field_key' => $fieldKey,
                    'label' => ($column['name'] ?: 'Column') . ' - ' . ($row['label'] ?: 'Row'),
                    'field_type' => $isFormula ? 'formula' : 'number',
                    'is_required' => !$isFormula, // Formula fields are auto-calculated, so they are generally not required to be manually input
                    'is_readonly' => $isFormula,
                    'sort_order' => $sortOrder++,
                    'field_config' => [
                        'column_id' => $column['id'],
                        'column_name' => $column['name'],
                        'row_id' => $row['id'],
                        'row_label' => $row['label'],
                        'variable_slug' => $isFormula ? '' : ($row['variable_slug'] ?? ''),
                    ],
                ]);
            }
        }

        // Save meta scope items
        MonitoringTemplateField::create([
            'template_id' => $this->template->id,
            'field_key' => '__meta_scope_items',
            'label' => 'System Meta Data',
            'field_type' => 'metadata',
            'is_required' => false,
            'is_readonly' => true,
            'sort_order' => 9999,
            'field_config' => [
                'sections' => $this->selectedSectionIds,
                'equipment' => $this->selectedEquipmentIds,
            ],
        ]);

        session()->flash('success', 'Monitoring template "' . $this->name . '" updated successfully.');

        return redirect()->route('livewire.monitoring');
    }

    public function cancel()
    {
        return redirect()->route('livewire.monitoring');
    }

    protected function getStepValidationRules(): array
    {
        return match ($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'documentControlNumber' => 'nullable|string|max:255',
                'version' => 'required|integer|min:1',
                'templateType' => 'required|in:environmental,equipment',
            ],
            2 => [
                'selectedLabIds' => 'required|array|min:1',
            ],
            3 => $this->templateType === 'environmental' ?
                ['selectedSectionIds' => 'nullable'] :
                ['selectedEquipmentIds' => 'nullable'],
            4 => [
                'columnStructure' => 'required|array|min:1',
            ],
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.monitoring.edit-monitoring-template');
    }
}
