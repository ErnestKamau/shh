<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\FormulaVersion;
use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\LookupTable;
use App\Models\Formulars\GlobalVariable;
use App\Models\Formulars\FormulaMandatoryField;
use App\Analyte;
use App\Models\Equipments\Equipment;
use App\User;
use App\AnalysisMethod;
use App\Services\Formulars\FormulaEvaluator;
use Livewire\Component;
use Livewire\WithPagination;

class FormulaStepEditor extends Component
{
    use WithPagination;

    public $formulaVersion;
    public $steps = [];
    public $showCreateStepModal = false;
    public $showEditStepModal = false;
    public $showDeleteStepModal = false;
    public $showTestFormulaModal = false;
    public $showDeletionBlockedModal = false;
    public $editingStep = null;
    public $deletingStep = null;
    public $deletionBlockedReason = '';

    // Step form fields
    public $stepNumber = 1;
    public $variableName = '';
    public $stepType = 'input';
    public $expression = '';
    public $label = '';
    public $description = '';
    public $lookupTableId = '';
    public $lookupConfig = [];
    public $analyteId = null;

    // Mandatory Fields
    public $mandatoryFields = [];
    public $showCreateFieldModal = false;
    public $showEditFieldModal = false;
    public $showDeleteFieldModal = false;
    public $editingField = null;
    public $deletingField = null;
    
    // Mandatory Field form fields
    public $fieldLabel = '';
    public $fieldType = 'input';
    public $fieldOrder = 1;
    public $fieldHelpText = '';
    public $fieldModelTiedTo = '';
    public $fieldIsRequired = true;
    public $fieldValueName = '';
    public $fieldSearch = '';

    // Search and Filter
    public $search = '';
    public $typeFilter = '';
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Test Formula
    public $testInputs = [];
    public $testResults = [];
    public $testExecutionData = [];

    // Messages
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'stepNumber' => 'required|integer|min:1',
        'variableName' => 'required|string|max:255',
        'stepType' => 'required|in:input,derived,lookup,parameter_result',
        'expression' => 'nullable|string',
        'label' => 'required|string|max:255',
        'description' => 'nullable|string',
        'lookupTableId' => 'nullable|exists:lookup_tables,id',
        'analyteId' => 'nullable|exists:analytes,id',
    ];

    protected function mandatoryFieldRules(): array
    {
        return [
            'fieldLabel' => 'required|string|max:255',
            'fieldType' => 'required|in:input,datetime,date,dataset_related',
            'fieldValueName' => 'required|string|max:255',
            'fieldHelpText' => 'nullable|string',
            'fieldModelTiedTo' => 'nullable|required_if:fieldType,dataset_related|in:equipments,users,methods',
            'fieldIsRequired' => 'boolean',
        ];
    }

    public function mount(FormulaVersion $formulaVersion)
    {
        $this->formulaVersion = $formulaVersion;
        $this->loadSteps();
        $this->loadMandatoryFields();
    }

    public function render()
    {
        $this->loadSteps();
        $this->loadMandatoryFields();
        $lookupTables = LookupTable::where('is_active', true)->get();
        $globalVariables = GlobalVariable::active()->get();
        $availableVariables = $this->getAvailableVariables();
        $analytes = Analyte::orderBy('name')->get();
        
        // Get dataset options
        $equipments = Equipment::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $methods = AnalysisMethod::orderBy('name')->get();
        
        return view('livewire.formulars.formula-step-editor', [
            'lookupTables' => $lookupTables,
            'globalVariables' => $globalVariables,
            'availableVariables' => $availableVariables,
            'analytes' => $analytes,
            'equipments' => $equipments,
            'users' => $users,
            'methods' => $methods,
        ]);
    }

    public function loadSteps()
    {
        $query = $this->formulaVersion->formulaSteps()
            ->orderBy('step_number');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('variable_name', 'like', '%' . $this->search . '%')
                  ->orWhere('label', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply type filter
        if ($this->typeFilter) {
            $query->where('step_type', $this->typeFilter);
        }

        $this->steps = $query->get()->toArray();
    }

    public function showCreateStepModalInit()
    {
        $this->resetStepForm();
        $this->stepNumber = count($this->steps) + 1;
        $this->stepType = 'input'; // Set default step type
        $this->showCreateStepModal = true;
    }

    public function showEditStepModalInit($stepId)
    {
        $step = FormulaStep::findOrFail($stepId);
        $this->editingStep = $step;
        $this->stepNumber = $step->step_number;
        $this->variableName = $step->variable_name;
        $this->stepType = $step->step_type;
        $this->expression = $step->expression ?? '';
        $this->label = $step->label;
        $this->description = $step->description ?? '';
        $this->lookupConfig = $step->lookup_config ?? [];
        $this->lookupTableId = $step->lookup_config['lookup_table_id'] ?? '';
        $this->analyteId = $step->analyte_id;
        $this->showEditStepModal = true;
    }

    public function createStep()
    {
        $this->validate();

        try {
            // Check for duplicate variable name
            $existingStep = $this->formulaVersion->formulaSteps()
                ->where('variable_name', $this->variableName)
                ->first();

            if ($existingStep) {
                $this->setMessage('Variable name already exists in this formula version!', 'error');
                return;
            }

            // Prepare lookup config for lookup steps
            $lookupConfig = [];
            if ($this->stepType === 'lookup' && $this->lookupTableId) {
                $lookupTable = LookupTable::find($this->lookupTableId);
                
                if ($lookupTable && $lookupTable->isRangeBased()) {
                    // Range-based lookup configuration
                    $lookupConfig = [
                        'lookup_table_id' => $this->lookupTableId,
                        'range_variable' => $this->lookupConfig['range_variable'] ?? '',
                        'return_interpretation' => $this->lookupConfig['return_interpretation'] ?? false,
                    ];
                } else {
                    // Key-value lookup configuration
                    $lookupConfig = [
                        'lookup_table_id' => $this->lookupTableId,
                        'key_expressions' => $this->lookupConfig['key_expressions'] ?? [],
                        'key_values' => $this->lookupConfig['key_values'] ?? [],
                    ];
                }
            }

            FormulaStep::create([
                'formula_version_id' => $this->formulaVersion->id,
                'step_number' => $this->stepNumber,
                'variable_name' => $this->variableName,
                'step_type' => $this->stepType,
                'expression' => $this->expression,
                'label' => $this->label,
                'description' => $this->description,
                'lookup_config' => $lookupConfig,
                'analyte_id' => $this->stepType === 'parameter_result' ? $this->analyteId : null,
            ]);

            $this->showCreateStepModal = false;
            $this->resetStepForm();
            $this->loadSteps();
            $this->setMessage('Step created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating step: ' . $e->getMessage(), 'error');
        }
    }

    public function updateStep()
    {
        $this->validate();

        try {
            // Check for duplicate variable name (excluding current step)
            $existingStep = $this->formulaVersion->formulaSteps()
                ->where('variable_name', $this->variableName)
                ->where('id', '!=', $this->editingStep->id)
                ->first();

            if ($existingStep) {
                $this->setMessage('Variable name already exists in this formula version!', 'error');
                return;
            }

            // Prepare lookup config for lookup steps
            $lookupConfig = [];
            if ($this->stepType === 'lookup' && $this->lookupTableId) {
                $lookupTable = LookupTable::find($this->lookupTableId);
                
                if ($lookupTable && $lookupTable->isRangeBased()) {
                    // Range-based lookup configuration
                    $lookupConfig = [
                        'lookup_table_id' => $this->lookupTableId,
                        'range_variable' => $this->lookupConfig['range_variable'] ?? '',
                        'return_interpretation' => $this->lookupConfig['return_interpretation'] ?? false,
                    ];
                } else {
                    // Key-value lookup configuration
                    $lookupConfig = [
                        'lookup_table_id' => $this->lookupTableId,
                        'key_expressions' => $this->lookupConfig['key_expressions'] ?? [],
                        'key_values' => $this->lookupConfig['key_values'] ?? [],
                    ];
                }
            }

            $this->editingStep->update([
                'step_number' => $this->stepNumber,
                'variable_name' => $this->variableName,
                'step_type' => $this->stepType,
                'expression' => $this->expression,
                'label' => $this->label,
                'description' => $this->description,
                'lookup_config' => $lookupConfig,
                'analyte_id' => $this->stepType === 'parameter_result' ? $this->analyteId : null,
            ]);

            $this->showEditStepModal = false;
            $this->resetStepForm();
            $this->loadSteps();
            $this->setMessage('Step updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating step: ' . $e->getMessage(), 'error');
        }
    }

    public function showDeleteStepModal($stepId)
    {
        $this->deletingStep = FormulaStep::findOrFail($stepId);
        
        // Check if variable is used in other steps
        $usageCheck = $this->checkVariableUsage($this->deletingStep->variable_name, $this->deletingStep->id);
        
        if ($usageCheck['is_used']) {
            $this->deletionBlockedReason = $usageCheck['reason'];
            $this->showDeletionBlockedModal = true;
            $this->showDeleteStepModal = false;
        } else {
            $this->showDeleteStepModal = true;
        }
    }

    public function deleteStep()
    {
        try {
            if ($this->deletingStep) {
                $this->deletingStep->delete();
                $this->showDeleteStepModal = false;
                $this->deletingStep = null;
                $this->loadSteps();
                $this->setMessage('Step deleted successfully!', 'success');
            }
        } catch (\Exception $e) {
            $this->setMessage('Error deleting step: ' . $e->getMessage(), 'error');
        }
    }

    public function closeDeletionBlockedModal()
    {
        $this->showDeletionBlockedModal = false;
        $this->deletionBlockedReason = '';
    }

    public function showTestFormulaModalInit()
    {
        $this->resetTestForm();
        $this->prepareTestInputs();
        $this->showTestFormulaModal = true;
    }

    public function testFormula()
    {
        try {
            // Convert string inputs to appropriate data types for lookup matching
            $convertedInputs = $this->convertInputTypes($this->testInputs);
            
            $evaluator = app(FormulaEvaluator::class);
            $result = $evaluator->execute($this->formulaVersion, $convertedInputs);
            
            $this->testResults = $result['variables'];
            $this->testExecutionData = $result['execution_data'];
            
            // Check for null lookup results and add warnings
            $warnings = [];
            foreach ($this->formulaVersion->formulaSteps as $step) {
                if ($step->step_type === 'lookup' && isset($this->testResults[$step->variable_name])) {
                    if ($this->testResults[$step->variable_name] === null) {
                        $warnings[] = "Lookup '{$step->label}' ({$step->variable_name}) returned no match. Check lookup table entries.";
                    }
                }
            }
            
            if (!empty($warnings)) {
                $this->testExecutionData['warnings'] = $warnings;
                $this->setMessage('Formula executed with warnings. Check lookup results.', 'warning');
            } else {
                $this->setMessage('Formula executed successfully!', 'success');
            }
        } catch (\Exception $e) {
            $this->setMessage('Error executing formula: ' . $e->getMessage(), 'error');
            $this->testResults = [];
            $this->testExecutionData = [];
        }
    }

    protected function convertInputTypes(array $inputs): array
    {
        $converted = [];
        
        foreach ($inputs as $key => $value) {
            // Try to convert to integer first (for numeric values)
            if (is_numeric($value)) {
                // Convert to integer if it's a whole number, otherwise keep as float
                $converted[$key] = (float)$value == (int)$value ? (int)$value : (float)$value;
            } else {
                // Keep as string for non-numeric values
                $converted[$key] = $value;
            }
        }
        
        return $converted;
    }

    protected function prepareTestInputs()
    {
        $this->testInputs = [];
        
        // Get all input steps and prepare empty inputs
        $inputSteps = $this->formulaVersion->formulaSteps()
            ->where('step_type', 'input')
            ->get();
            
        foreach ($inputSteps as $step) {
            $this->testInputs[$step->variable_name] = '';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->loadSteps();
    }

    public function updateStepOrder($stepIds)
    {
        try {
            // First, reset all step numbers to avoid conflicts
            $this->formulaVersion->formulaSteps()
                ->update(['step_number' => 9999]);
            
            // Then set the new order
            foreach ($stepIds as $index => $stepId) {
                FormulaStep::where('id', $stepId)
                    ->update(['step_number' => $index + 1]);
            }
            
            $this->loadSteps();
            $this->setMessage('Steps reordered successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error reordering steps: ' . $e->getMessage(), 'error');
        }
    }

    public function checkVariableUsage($variableName, $excludeStepId = null)
    {
        $query = $this->formulaVersion->formulaSteps()
            ->where('id', '!=', $excludeStepId);

        // Check if variable is used in derived step expressions
        $derivedSteps = $query->where('step_type', 'derived')
            ->where('expression', 'like', '%' . $variableName . '%')
            ->get();

        if ($derivedSteps->count() > 0) {
            $stepNumbers = $derivedSteps->pluck('step_number')->toArray();
            return [
                'is_used' => true,
                'reason' => "This variable is being used in derived step(s) " . implode(', ', $stepNumbers) . " and therefore cannot be deleted."
            ];
        }

        // Check if variable is used in lookup step key expressions
        $lookupSteps = $query->where('step_type', 'lookup')
            ->whereJsonContains('lookup_config->key_expressions', $variableName)
            ->get();

        if ($lookupSteps->count() > 0) {
            $stepNumbers = $lookupSteps->pluck('step_number')->toArray();
            return [
                'is_used' => true,
                'reason' => "This variable is being used in lookup step(s) " . implode(', ', $stepNumbers) . " and therefore cannot be deleted."
            ];
        }

        return [
            'is_used' => false,
            'reason' => ''
        ];
    }

    public function validateExpression()
    {
        if ($this->stepType !== 'derived' || !$this->expression) {
            return;
        }

        try {
            $evaluator = app(FormulaEvaluator::class);
            $availableVariables = $evaluator->getAvailableVariables($this->formulaVersion);
            
            $result = $evaluator->validateExpression($this->expression, $availableVariables);
            
            if ($result['valid']) {
                $this->setMessage('Expression is valid!', 'success');
            } else {
                $this->setMessage('Expression error: ' . $result['message'], 'error');
            }
        } catch (\Exception $e) {
            $this->setMessage('Error validating expression: ' . $e->getMessage(), 'error');
        }
    }

    public function updatedStepType()
    {
        // Reset fields when step type changes
        $this->expression = '';
        $this->lookupTableId = '';
        $this->lookupConfig = [];
        $this->analyteId = null;
    }

    public function updatedLookupTableId()
    {
        if ($this->lookupTableId) {
            $lookupTable = LookupTable::find($this->lookupTableId);
            if ($lookupTable) {
                if ($lookupTable->isRangeBased()) {
                    // Range-based: single variable selector
                    $this->lookupConfig = [
                        'lookup_table_id' => $lookupTable->id,
                        'range_variable' => '',
                        'return_interpretation' => false,
                    ];
                } else {
                    // Key-value: existing multi-key configuration
                    $this->lookupConfig = [
                        'lookup_table_id' => $lookupTable->id,
                        'key_expressions' => array_fill_keys($lookupTable->key_columns, ''),
                        'key_values' => array_fill_keys($lookupTable->key_columns, ''),
                    ];
                }
            }
        }
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    protected function resetStepForm()
    {
        $this->stepNumber = 1;
        $this->variableName = '';
        $this->stepType = 'input';
        $this->expression = '';
        $this->label = '';
        $this->description = '';
        $this->lookupTableId = '';
        $this->lookupConfig = [];
        $this->analyteId = null;
        $this->editingStep = null;
    }

    protected function resetTestForm()
    {
        $this->testInputs = [];
        $this->testResults = [];
        $this->testExecutionData = [];
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function backToFormulaManagement()
    {
        return redirect()->route('formulars.manage');
    }

    protected function getAvailableVariables(): array
    {
        $variables = [];
        
        // Get variables from previous steps
        foreach ($this->formulaVersion->formulaSteps as $step) {
            if ($step->step_number < $this->stepNumber) {
                $variables[$step->variable_name] = [
                    'name' => $step->variable_name,
                    'label' => $step->label,
                    'type' => $step->step_type,
                    'description' => $step->description,
                ];
            }
        }
        
        return $variables;
    }

    // ==================== Mandatory Fields Methods ====================

    public function loadMandatoryFields()
    {
        $query = $this->formulaVersion->mandatoryFields()
            ->orderBy('order');

        // Apply search filter
        if ($this->fieldSearch) {
            $query->where(function ($q) {
                $q->where('label', 'like', '%' . $this->fieldSearch . '%')
                  ->orWhere('field_value_name', 'like', '%' . $this->fieldSearch . '%')
                  ->orWhere('help_text', 'like', '%' . $this->fieldSearch . '%');
            });
        }

        $this->mandatoryFields = $query->get()->toArray();
    }

    public function showCreateFieldModalInit()
    {
        $this->resetFieldForm();
        $this->fieldOrder = count($this->mandatoryFields) + 1;
        $this->showCreateFieldModal = true;
    }

    public function showEditFieldModalInit($fieldId)
    {
        $field = FormulaMandatoryField::findOrFail($fieldId);
        $this->editingField = $field;
        $this->fieldLabel = $field->label;
        $this->fieldType = $field->field_type;
        $this->fieldOrder = $field->order;
        $this->fieldHelpText = $field->help_text ?? '';
        $this->fieldModelTiedTo = $field->model_tied_to ?? '';
        $this->fieldIsRequired = $field->is_required;
        $this->fieldValueName = $field->field_value_name;
        $this->showEditFieldModal = true;
    }

    public function createMandatoryField()
    {
        $this->validate($this->mandatoryFieldRules());

        try {
            FormulaMandatoryField::create([
                'formula_version_id' => $this->formulaVersion->id,
                'label' => $this->fieldLabel,
                'field_type' => $this->fieldType,
                'order' => $this->fieldOrder,
                'help_text' => $this->fieldHelpText,
                'model_tied_to' => $this->fieldType === 'dataset_related' ? $this->fieldModelTiedTo : null,
                'is_required' => $this->fieldIsRequired,
                'field_value_name' => $this->fieldValueName,
            ]);

            $this->showCreateFieldModal = false;
            $this->resetFieldForm();
            $this->loadMandatoryFields();
            $this->setMessage('Mandatory field created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating mandatory field: ' . $e->getMessage(), 'error');
        }
    }

    public function updateMandatoryField()
    {
        $this->validate($this->mandatoryFieldRules());

        try {
            $this->editingField->update([
                'label' => $this->fieldLabel,
                'field_type' => $this->fieldType,
                'order' => $this->fieldOrder,
                'help_text' => $this->fieldHelpText,
                'model_tied_to' => $this->fieldType === 'dataset_related' ? $this->fieldModelTiedTo : null,
                'is_required' => $this->fieldIsRequired,
                'field_value_name' => $this->fieldValueName,
            ]);

            $this->showEditFieldModal = false;
            $this->resetFieldForm();
            $this->loadMandatoryFields();
            $this->setMessage('Mandatory field updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating mandatory field: ' . $e->getMessage(), 'error');
        }
    }

    public function showDeleteFieldModal($fieldId)
    {
        $this->deletingField = FormulaMandatoryField::findOrFail($fieldId);
        $this->showDeleteFieldModal = true;
    }

    public function deleteMandatoryField()
    {
        try {
            if ($this->deletingField) {
                $this->deletingField->delete();
                $this->showDeleteFieldModal = false;
                $this->deletingField = null;
                $this->loadMandatoryFields();
                $this->setMessage('Mandatory field deleted successfully!', 'success');
            }
        } catch (\Exception $e) {
            $this->setMessage('Error deleting mandatory field: ' . $e->getMessage(), 'error');
        }
    }

    public function updateFieldOrder($fieldIds)
    {
        try {
            // First, reset all field orders to avoid conflicts
            $this->formulaVersion->mandatoryFields()
                ->update(['order' => 9999]);
            
            // Then set the new order
            foreach ($fieldIds as $index => $fieldId) {
                FormulaMandatoryField::where('id', $fieldId)
                    ->update(['order' => $index + 1]);
            }
            
            $this->loadMandatoryFields();
            $this->setMessage('Fields reordered successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error reordering fields: ' . $e->getMessage(), 'error');
        }
    }

    public function clearFieldSearch()
    {
        $this->fieldSearch = '';
        $this->loadMandatoryFields();
    }

    public function updatedFieldType()
    {
        // Reset model_tied_to when field type changes
        if ($this->fieldType !== 'dataset_related') {
            $this->fieldModelTiedTo = '';
        }
    }

    protected function resetFieldForm()
    {
        $this->fieldLabel = '';
        $this->fieldType = 'input';
        $this->fieldOrder = 1;
        $this->fieldHelpText = '';
        $this->fieldModelTiedTo = '';
        $this->fieldIsRequired = true;
        $this->fieldValueName = '';
        $this->editingField = null;
    }
}