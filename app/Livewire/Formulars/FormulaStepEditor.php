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
use App\Enums\Procedures\ProcedureTableRowDriver;
use App\Livewire\Formulars\Concerns\InteractsWithFormulaStepTableConfiguration;
use App\Models\Formulars\FormulaStepTableColumn;
use App\Services\Formulars\FormulaEvaluator;
use App\Services\LogEntryWorksheets\LogEntryDatabaseSchemaService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class FormulaStepEditor extends Component
{
    use InteractsWithFormulaStepTableConfiguration;
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

    public array $stepConfig = [];

    public string $staticTextContent = '';

    public string $checkboxOptionsMode = 'static';

    /** @var list<string> */
    public array $checkboxStaticOptions = [];

    public string $checkboxNewOption = '';

    public string $checkboxPresetModel = 'equipments';

    public string $checkboxDatasetSourceTable = '';

    public string $checkboxDatasetDisplayMode = 'direct';

    public string $checkboxDatasetSourceColumn = '';

    public string $checkboxDatasetFkColumn = '';

    public string $checkboxDatasetReferencedTable = '';

    public string $checkboxDatasetReferencedKeyColumn = 'id';

    public string $checkboxDatasetReferencedDisplayColumn = '';

    public string $table_mode = 'dynamic';

    public string $row_driver = 'captured_result';

    public bool $allow_manual_rows = false;

    public bool $pcrHasStdControlsBuffers = false;

    /** @var list<string> */
    public array $pcrStandards = [];

    /** @var list<string> */
    public array $pcrControls = [];

    /** @var list<string> */
    public array $pcrBuffers = [];

    public string $pcrNewStandard = '';

    public string $pcrNewControl = '';

    public string $pcrNewBuffer = '';

    /**
     * @var array<string, array{kind: string, value: string}>
     */
    public array $pcrPresetWells = [];

    public ?string $pcrConfigActiveWell = null;

    public string $pcrConfigPopoverKind = 'std';

    public string $pcrConfigPopoverValue = '';

    public bool $showConfigureTableModal = false;

    public int $configureTableWizardStep = 1;

    public ?string $configuringStepId = null;

    /** @var list<string> */
    public array $expandedCustomTableStepIds = [];

    /** @var array<int, array<string, mixed>> */
    public array $stepTableColumns = [];

    public bool $showCreateStepColumnModal = false;

    public bool $showEditStepColumnModal = false;

    public bool $showInlineStaticRowForm = false;

    public ?FormulaStepTableColumn $editingStepColumn = null;

    public string $stepColumnLabel = '';

    public string $stepColumnKey = '';

    public string $stepColumnType = 'input';

    public string $stepColumnInputDataType = 'string';

    public string $stepColumnExpression = '';

    public string $stepColumnModelTiedTo = '';

    public string $stepColumnDatasetSourceTable = '';

    public string $stepColumnDatasetDisplayMode = 'direct';

    public string $stepColumnDatasetSourceColumn = '';

    public string $stepColumnDatasetFkColumn = '';

    public string $stepColumnDatasetReferencedTable = '';

    public string $stepColumnDatasetReferencedKeyColumn = 'id';

    public string $stepColumnDatasetReferencedDisplayColumn = '';

    public int $stepColumnOrder = 1;

    public bool $stepColumnIsRequired = false;

    public string $stepColumnHelpText = '';

    public string $stepColumnChoiceControl = 'radio';

    public string $stepColumnStaticOptions = '';

    public ?string $stepColumnValidationMessage = null;

    /** @var array<int, array<string, mixed>> */
    public array $stepStaticRows = [];

    /** @var array<string, array<string, string>> */
    public array $stepStaticCellValues = [];

    public string $newStaticRowLabel = '';

    // Mandatory Fields
    public $mandatoryFields = [];
    public $showCreateFieldModal = false;
    public $showEditFieldModal = false;
    public $showDeleteFieldModal = false;
    public $editingField = null;
    public $deletingField = null;
    public string $mandatoryFieldsPlacement = 'bottom';
    public string $mandatorySectionTab = 'mandatory_fields';
    public string $documentControlNo = '';
    public string $documentControlRevisionNo = '';
    public ?string $documentControlIssueDate = null;

    // Mandatory Field form fields
    public $fieldLabel = '';
    public $fieldType = 'input';
    public $fieldOrder = 1;
    public $fieldHelpText = '';
    public $fieldModelTiedTo = '';
    public $fieldIsRequired = true;
    public $fieldValueName = '';
    public $fieldSearch = '';
    public string $fieldNewChoice = '';

    /** @var array<int, string> */
    public array $fieldChoiceOptions = [];

    public string $fieldFormPlacement = 'bottom';

    public bool $showFieldFormPlacementDropdown = false;

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
        'stepType' => 'required|in:input,derived,lookup,parameter_result,static_text,checkbox,custom_table,pcr_plate_map',
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
            'fieldType' => 'required|in:input,datetime,date,checkbox,dataset_related',
            'fieldValueName' => ['required', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fieldHelpText' => 'nullable|string',
            'fieldOrder' => 'required|integer|min:1',
            'fieldModelTiedTo' => [
                Rule::excludeIf(fn (): bool => $this->fieldType !== 'dataset_related'),
                'required',
                Rule::in(['equipments', 'users', 'methods']),
            ],
            'fieldIsRequired' => 'boolean',
            'fieldFormPlacement' => 'required|in:top,bottom',
            'fieldChoiceOptions' => 'nullable|array',
            'fieldChoiceOptions.*' => 'nullable|string|max:255',
        ];
    }

    public function getFieldFormPlacementOptionsProperty(): array
    {
        return FormulaMandatoryField::formPlacementOptions();
    }

    public function getSelectedFieldFormPlacementLabelProperty(): string
    {
        return $this->fieldFormPlacementOptions[$this->fieldFormPlacement] ?? '';
    }

    public function closeFieldFormPlacementDropdown(): void
    {
        $this->showFieldFormPlacementDropdown = false;
    }

    public function selectFieldFormPlacement(string $placement): void
    {
        if (! array_key_exists($placement, $this->fieldFormPlacementOptions)) {
            return;
        }

        $this->fieldFormPlacement = $placement;
        $this->showFieldFormPlacementDropdown = false;
    }

    public function mount(FormulaVersion $formulaVersion)
    {
        $this->formulaVersion = $formulaVersion;
        $this->mandatoryFieldsPlacement = $formulaVersion->mandatory_fields_placement ?? 'bottom';
        $this->documentControlNo = (string) ($formulaVersion->document_control_no ?? '');
        $this->documentControlRevisionNo = (string) ($formulaVersion->document_control_revision_no ?? '');
        $this->documentControlIssueDate = $formulaVersion->document_control_issue_date
            ? $formulaVersion->document_control_issue_date->format('Y-m-d')
            : null;
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
        $rowDriverOptions = ProcedureTableRowDriver::options();
        $logEntrySchema = app(LogEntryDatabaseSchemaService::class);
        $checkboxDatasetTables = $logEntrySchema->tableOptions();
        
        // Get dataset options
        $equipments = Equipment::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $methods = AnalysisMethod::orderBy('name')->get();
        
        $stepColumnSourceTable = $this->stepColumnModelTiedTo === 'samples'
            ? 'sample_details'
            : $this->stepColumnDatasetSourceTable;
        $sampleDetailForeignKeys = collect($logEntrySchema->foreignKeyOptions('sample_details'))
            ->pluck('column')
            ->map(fn ($column) => (string) $column)
            ->all();
        $sampleDetailDirectColumns = collect($logEntrySchema->columnOptions('sample_details'))
            ->filter(fn (array $column) => ! in_array((string) ($column['value'] ?? ''), $sampleDetailForeignKeys, true))
            ->values()
            ->all();

        return view('livewire.formulars.formula-step-editor', [
            'lookupTables' => $lookupTables,
            'globalVariables' => $globalVariables,
            'availableVariables' => $availableVariables,
            'mandatoryFieldTypes' => FormulaMandatoryField::getFieldTypes(),
            'stepColumnTypeOptions' => [
                'input' => 'Input',
                'derived' => 'Derived',
                'dataset' => 'Dataset',
                'static' => 'Static',
            ],
            'stepTableDatasetOptions' => FormulaStepTableColumn::datasetPresetOptions(),
            'inputDataTypeOptions' => [
                'string' => 'Text',
                'number' => 'Number',
                'date' => 'Date',
                'time' => 'Time',
                'datetime' => 'Date & time',
                'boolean' => 'Yes/No',
                'textarea' => 'Long text',
                'radio' => 'Radio buttons (single choice)',
                'checkbox' => 'Checkboxes (multi choice)',
                'select' => 'Select dropdown (single choice)',
            ],
            'analytes' => $analytes,
            'equipments' => $equipments,
            'users' => $users,
            'methods' => $methods,
            'rowDriverOptions' => $rowDriverOptions,
            'checkboxDatasetTables' => $checkboxDatasetTables,
            'checkboxDatasetColumns' => $this->checkboxDatasetSourceTable !== ''
                ? $logEntrySchema->columnOptions($this->checkboxDatasetSourceTable)
                : [],
            'checkboxDatasetForeignKeys' => $this->checkboxDatasetSourceTable !== ''
                ? $logEntrySchema->foreignKeyOptions($this->checkboxDatasetSourceTable)
                : [],
            'checkboxDatasetReferencedColumns' => $this->checkboxDatasetReferencedTable !== ''
                ? $logEntrySchema->columnOptions($this->checkboxDatasetReferencedTable)
                : [],
            'stepColumnDatasetTables' => $logEntrySchema->tableOptions(),
            'stepColumnDatasetColumns' => $stepColumnSourceTable !== ''
                ? $logEntrySchema->columnOptions($stepColumnSourceTable)
                : [],
            'stepColumnDatasetForeignKeys' => $stepColumnSourceTable !== ''
                ? $logEntrySchema->foreignKeyOptions($stepColumnSourceTable)
                : [],
            'stepColumnDatasetReferencedColumns' => $this->stepColumnDatasetReferencedTable !== ''
                ? $logEntrySchema->columnOptions($this->stepColumnDatasetReferencedTable)
                : [],
            'stepColumnSampleDirectColumns' => $sampleDetailDirectColumns,
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

    public function closeCreateStepModal(): void
    {
        $this->showCreateStepModal = false;
    }

    public function closeEditStepModal(): void
    {
        $this->showEditStepModal = false;
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
        $this->loadStepTypeConfigFromStep($step);
        $this->showEditStepModal = true;
    }

    public function createStep()
    {
        $this->validate();
        if (! $this->validateStepTypeConfig()) {
            return;
        }

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

            FormulaStep::create($this->buildStepPayload($lookupConfig));

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
        if (! $this->validateStepTypeConfig()) {
            return;
        }

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

            $this->editingStep->update($this->buildStepPayload($lookupConfig));

            $this->showEditStepModal = false;
            $this->resetStepForm();
            $this->loadSteps();
            $this->setMessage('Step updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating step: ' . $e->getMessage(), 'error');
        }
    }

    public function openDeleteStepModal(string $stepId): void
    {
        $this->deletingStep = FormulaStep::findOrFail($stepId);

        $shouldCheckUsage = $this->deletingStep->isExpressionVariable()
            && trim((string) $this->deletingStep->variable_name) !== '';

        if ($shouldCheckUsage) {
            $usageCheck = $this->checkVariableUsage($this->deletingStep->variable_name, $this->deletingStep->id);

            if ($usageCheck['is_used']) {
                $this->deletionBlockedReason = $usageCheck['reason'];
                $this->showDeletionBlockedModal = true;
                $this->showDeleteStepModal = false;
                $this->deletingStep = null;

                return;
            }
        }

        $this->showDeleteStepModal = true;
    }

    public function closeDeleteStepModal(): void
    {
        $this->showDeleteStepModal = false;
        $this->deletingStep = null;
    }

    public function deleteStep(): void
    {
        try {
            if ($this->deletingStep) {
                $this->deletingStep->delete();
                $this->closeDeleteStepModal();
                $this->loadSteps();
                $this->setMessage('Step deleted successfully!', 'success');
            }
        } catch (\Exception $e) {
            $this->setMessage('Error deleting step: '.$e->getMessage(), 'error');
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

    public function testFormula(): void
    {
        $missingInputs = $this->getMissingTestInputs();
        if ($missingInputs !== []) {
            $this->setMessage(
                'Please provide values for: ' . implode(', ', $missingInputs),
                'error'
            );
            $this->testResults = [];
            $this->testExecutionData = [];

            return;
        }

        try {
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
        } catch (\Throwable $e) {
            $this->setMessage('Error executing formula: ' . $e->getMessage(), 'error');
            $this->testResults = [];
            $this->testExecutionData = [];
        }
    }

    /**
     * @return list<string>
     */
    protected function getMissingTestInputs(): array
    {
        $missing = [];

        $inputSteps = $this->formulaVersion->formulaSteps()
            ->where('step_type', 'input')
            ->get();

        foreach ($inputSteps as $step) {
            $value = $this->testInputs[$step->variable_name] ?? null;

            if ($value === null || $value === '') {
                $missing[] = $step->label ?: $step->variable_name;
            }
        }

        return $missing;
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

    /**
     * @return array{is_used: bool, reason: string}
     */
    public function checkVariableUsage(string $variableName, ?string $excludeStepId = null): array
    {
        $variableName = trim($variableName);
        if ($variableName === '') {
            return ['is_used' => false, 'reason' => ''];
        }

        $baseQuery = fn () => $this->formulaVersion->formulaSteps()
            ->when($excludeStepId, fn ($query) => $query->where('id', '!=', $excludeStepId));

        $derivedSteps = $baseQuery()
            ->where('step_type', 'derived')
            ->where('expression', 'like', '%'.$variableName.'%')
            ->get();

        if ($derivedSteps->isNotEmpty()) {
            $stepNumbers = $derivedSteps->pluck('step_number')->all();

            return [
                'is_used' => true,
                'reason' => 'This variable is being used in derived step(s) '.implode(', ', $stepNumbers).' and therefore cannot be deleted.',
            ];
        }

        $lookupSteps = $baseQuery()
            ->where('step_type', 'lookup')
            ->get()
            ->filter(fn (FormulaStep $step) => $this->lookupStepUsesVariable($step, $variableName));

        if ($lookupSteps->isNotEmpty()) {
            $stepNumbers = $lookupSteps->pluck('step_number')->all();

            return [
                'is_used' => true,
                'reason' => 'This variable is being used in lookup step(s) '.implode(', ', $stepNumbers).' and therefore cannot be deleted.',
            ];
        }

        return ['is_used' => false, 'reason' => ''];
    }

    protected function lookupStepUsesVariable(FormulaStep $step, string $variableName): bool
    {
        $config = is_array($step->lookup_config) ? $step->lookup_config : [];

        foreach ($config['key_expressions'] ?? [] as $expression) {
            if (is_string($expression) && str_contains($expression, $variableName)) {
                return true;
            }
        }

        foreach ($config['key_values'] ?? [] as $value) {
            if (is_string($value) && str_contains($value, $variableName)) {
                return true;
            }
        }

        return false;
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

    public function updatedStepType(): void
    {
        $this->expression = '';
        $this->lookupTableId = '';
        $this->lookupConfig = [];
        $this->analyteId = null;
        $this->staticTextContent = '';
        $this->checkboxOptionsMode = 'static';
        $this->checkboxStaticOptions = [];
        $this->checkboxNewOption = '';
        $this->checkboxPresetModel = 'equipments';
        $this->resetCheckboxDatasetFields();
        $this->table_mode = 'dynamic';
        $this->row_driver = 'captured_result';
        $this->allow_manual_rows = false;
        $this->resetPcrPlateFields();
    }

    public function updatedCheckboxDatasetSourceTable(): void
    {
        $this->checkboxDatasetSourceColumn = '';
        $this->checkboxDatasetFkColumn = '';
        $this->checkboxDatasetReferencedTable = '';
        $this->checkboxDatasetReferencedDisplayColumn = '';
    }

    public function updatedCheckboxDatasetFkColumn(): void
    {
        if ($this->checkboxDatasetFkColumn === '') {
            return;
        }

        $fks = app(LogEntryDatabaseSchemaService::class)->foreignKeyOptions($this->checkboxDatasetSourceTable);
        $match = collect($fks)->firstWhere('column', $this->checkboxDatasetFkColumn);
        if ($match) {
            $this->checkboxDatasetReferencedTable = $match['referenced_table'];
            $this->checkboxDatasetReferencedKeyColumn = $match['referenced_column'];
            $this->checkboxDatasetReferencedDisplayColumn = '';
        }
    }

    public function updatedStepColumnDatasetSourceTable(): void
    {
        if ($this->stepColumnModelTiedTo === 'samples') {
            $this->stepColumnDatasetSourceTable = 'sample_details';
        }
        $this->stepColumnDatasetSourceColumn = '';
        $this->stepColumnDatasetFkColumn = '';
        $this->stepColumnDatasetReferencedTable = '';
        $this->stepColumnDatasetReferencedDisplayColumn = '';
    }

    public function updatedStepColumnDatasetFkColumn(): void
    {
        $sourceTable = $this->stepColumnModelTiedTo === 'samples'
            ? 'sample_details'
            : $this->stepColumnDatasetSourceTable;
        if ($this->stepColumnDatasetFkColumn === '' || $sourceTable === '') {
            return;
        }

        $fks = app(LogEntryDatabaseSchemaService::class)->foreignKeyOptions($sourceTable);
        $match = collect($fks)->firstWhere('column', $this->stepColumnDatasetFkColumn);
        if ($match) {
            $this->stepColumnDatasetReferencedTable = $match['referenced_table'];
            $this->stepColumnDatasetReferencedKeyColumn = $match['referenced_column'];
            $this->stepColumnDatasetReferencedDisplayColumn = '';
        }
    }

    public function updatedStepColumnModelTiedTo(): void
    {
        if ($this->stepColumnModelTiedTo === 'samples') {
            $this->stepColumnDatasetSourceTable = 'sample_details';
            $this->stepColumnDatasetDisplayMode = 'direct';
            $this->stepColumnDatasetSourceColumn = '';
            $this->stepColumnDatasetFkColumn = '';
            $this->stepColumnDatasetReferencedTable = '';
            $this->stepColumnDatasetReferencedKeyColumn = 'id';
            $this->stepColumnDatasetReferencedDisplayColumn = '';
        }
    }

    public function addPcrStandard(): void
    {
        $value = trim($this->pcrNewStandard);
        if ($value === '' || in_array($value, $this->pcrStandards, true)) {
            return;
        }
        $this->pcrStandards[] = $value;
        $this->pcrNewStandard = '';
    }

    public function removePcrStandard(int $index): void
    {
        if (isset($this->pcrStandards[$index])) {
            $removed = $this->pcrStandards[$index];
            unset($this->pcrStandards[$index]);
            $this->pcrStandards = array_values($this->pcrStandards);
            $this->purgePcrPresetWellsByLabel('std', $removed);
        }
    }

    public function addPcrControl(): void
    {
        $value = trim($this->pcrNewControl);
        if ($value === '' || in_array($value, $this->pcrControls, true)) {
            return;
        }
        $this->pcrControls[] = $value;
        $this->pcrNewControl = '';
    }

    public function removePcrControl(int $index): void
    {
        if (isset($this->pcrControls[$index])) {
            $removed = $this->pcrControls[$index];
            unset($this->pcrControls[$index]);
            $this->pcrControls = array_values($this->pcrControls);
            $this->purgePcrPresetWellsByLabel('control', $removed);
        }
    }

    public function addPcrBuffer(): void
    {
        $value = trim($this->pcrNewBuffer);
        if ($value === '' || in_array($value, $this->pcrBuffers, true)) {
            return;
        }
        $this->pcrBuffers[] = $value;
        $this->pcrNewBuffer = '';
    }

    public function removePcrBuffer(int $index): void
    {
        if (isset($this->pcrBuffers[$index])) {
            $removed = $this->pcrBuffers[$index];
            unset($this->pcrBuffers[$index]);
            $this->pcrBuffers = array_values($this->pcrBuffers);
            $this->purgePcrPresetWellsByLabel('buffer', $removed);
        }
    }

    public function updatedPcrHasStdControlsBuffers(): void
    {
        if (! $this->pcrHasStdControlsBuffers) {
            $this->pcrPresetWells = [];
            $this->closePcrConfigWellEditor();
        }
    }

    public function openPcrConfigWellEditor(string $well): void
    {
        $this->pcrConfigActiveWell = $well;
        $assignment = $this->pcrPresetWells[$well] ?? null;

        if (is_array($assignment)) {
            $this->pcrConfigPopoverKind = (string) ($assignment['kind'] ?? 'std');
            $this->pcrConfigPopoverValue = (string) ($assignment['value'] ?? '');
        } else {
            $this->pcrConfigPopoverKind = 'std';
            $this->pcrConfigPopoverValue = '';
        }
    }

    public function closePcrConfigWellEditor(): void
    {
        $this->pcrConfigActiveWell = null;
        $this->pcrConfigPopoverValue = '';
    }

    public function setPcrConfigPopoverKind(string $kind): void
    {
        if (! in_array($kind, ['std', 'control', 'buffer'], true)) {
            return;
        }

        $this->pcrConfigPopoverKind = $kind;
        $this->pcrConfigPopoverValue = '';
    }

    public function applyPcrConfigWell(): void
    {
        if ($this->pcrConfigActiveWell === null) {
            return;
        }

        $value = trim($this->pcrConfigPopoverValue);
        if ($value === '') {
            $this->addError('pcrConfigPopoverValue', 'Select or enter a label for this well.');

            return;
        }

        $well = $this->pcrConfigActiveWell;
        $this->pcrPresetWells[$well] = [
            'kind' => $this->pcrConfigPopoverKind,
            'value' => $value,
        ];

        $this->closePcrConfigWellEditor();
        $this->resetErrorBag('pcrConfigPopoverValue');
    }

    public function clearPcrConfigWell(): void
    {
        if ($this->pcrConfigActiveWell === null) {
            return;
        }

        unset($this->pcrPresetWells[$this->pcrConfigActiveWell]);
        $this->closePcrConfigWellEditor();
    }

    public function quickAssignPcrConfigWell(string $kind, string $value): void
    {
        if ($this->pcrConfigActiveWell === null) {
            return;
        }

        if (! in_array($kind, ['std', 'control', 'buffer'], true)) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $this->pcrPresetWells[$this->pcrConfigActiveWell] = [
            'kind' => $kind,
            'value' => $value,
        ];

        $this->closePcrConfigWellEditor();
    }

    protected function purgePcrPresetWellsByLabel(string $kind, string $label): void
    {
        foreach ($this->pcrPresetWells as $well => $assignment) {
            if (
                is_array($assignment)
                && ($assignment['kind'] ?? '') === $kind
                && ($assignment['value'] ?? '') === $label
            ) {
                unset($this->pcrPresetWells[$well]);
            }
        }

        if ($this->pcrConfigActiveWell !== null && ! isset($this->pcrPresetWells[$this->pcrConfigActiveWell])) {
            $this->closePcrConfigWellEditor();
        }
    }

    public function addCheckboxStaticOption(): void
    {
        $label = trim($this->checkboxNewOption);
        if ($label === '') {
            return;
        }

        if (! in_array($label, $this->checkboxStaticOptions, true)) {
            $this->checkboxStaticOptions[] = $label;
        }

        $this->checkboxNewOption = '';
    }

    public function removeCheckboxStaticOption(int $index): void
    {
        if (isset($this->checkboxStaticOptions[$index])) {
            unset($this->checkboxStaticOptions[$index]);
            $this->checkboxStaticOptions = array_values($this->checkboxStaticOptions);
        }
    }

    public function openConfigureTableModalForStep(string $stepId): void
    {
        $this->openConfigureTableModal($stepId);
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
        $this->staticTextContent = '';
        $this->checkboxOptionsMode = 'static';
        $this->checkboxStaticOptions = [];
        $this->checkboxNewOption = '';
        $this->resetCheckboxDatasetFields();
        $this->table_mode = 'dynamic';
        $this->row_driver = 'captured_result';
        $this->allow_manual_rows = false;
        $this->resetPcrPlateFields();
        $this->editingStep = null;
    }

    protected function resetPcrPlateFields(): void
    {
        $this->pcrHasStdControlsBuffers = false;
        $this->pcrStandards = [];
        $this->pcrControls = [];
        $this->pcrBuffers = [];
        $this->pcrNewStandard = '';
        $this->pcrNewControl = '';
        $this->pcrNewBuffer = '';
        $this->pcrPresetWells = [];
        $this->pcrConfigActiveWell = null;
        $this->pcrConfigPopoverKind = 'std';
        $this->pcrConfigPopoverValue = '';
    }

    protected function resetCheckboxDatasetFields(): void
    {
        $this->checkboxDatasetSourceTable = '';
        $this->checkboxDatasetDisplayMode = 'direct';
        $this->checkboxDatasetSourceColumn = '';
        $this->checkboxDatasetFkColumn = '';
        $this->checkboxDatasetReferencedTable = '';
        $this->checkboxDatasetReferencedKeyColumn = 'id';
        $this->checkboxDatasetReferencedDisplayColumn = '';
    }

    protected function loadStepTypeConfigFromStep(FormulaStep $step): void
    {
        $config = is_array($step->step_config) ? $step->step_config : [];
        $this->staticTextContent = (string) ($config['content'] ?? '');
        $this->checkboxOptionsMode = (string) ($config['options_mode'] ?? 'static');
        $this->checkboxStaticOptions = array_values($config['static_options'] ?? []);
        $this->checkboxPresetModel = (string) ($config['preset_model'] ?? 'equipments');
        $this->resetCheckboxDatasetFields();
        if (! empty($config['dataset_config']) && is_array($config['dataset_config'])) {
            $dc = $config['dataset_config'];
            $this->checkboxDatasetSourceTable = $dc['source_table'] ?? '';
            $this->checkboxDatasetDisplayMode = $dc['display_mode'] ?? 'direct';
            $this->checkboxDatasetSourceColumn = $dc['source_display_column'] ?? '';
            $this->checkboxDatasetFkColumn = $dc['foreign_key_column'] ?? '';
            $this->checkboxDatasetReferencedTable = $dc['referenced_table'] ?? '';
            $this->checkboxDatasetReferencedKeyColumn = $dc['referenced_key_column'] ?? 'id';
            $this->checkboxDatasetReferencedDisplayColumn = $dc['referenced_display_column'] ?? '';
        }
        $this->table_mode = $step->table_mode ?? 'dynamic';
        $this->row_driver = $step->row_driver ?? 'captured_result';
        $this->allow_manual_rows = (bool) $step->allow_manual_rows;
        $this->pcrHasStdControlsBuffers = (bool) ($config['has_std_controls_buffers'] ?? false);
        $this->pcrStandards = array_values($config['standards'] ?? []);
        $this->pcrControls = array_values($config['controls'] ?? []);
        $this->pcrBuffers = array_values($config['buffers'] ?? []);
        $presetWells = $config['preset_wells'] ?? [];
        $this->pcrPresetWells = is_array($presetWells) ? $presetWells : [];
    }

    protected function validateStepTypeConfig(): bool
    {
        if ($this->stepType === 'static_text' && trim($this->staticTextContent) === '') {
            $this->addError('staticTextContent', 'Enter the static text to display on the worksheet.');

            return false;
        }

        if ($this->stepType === 'checkbox') {
            if ($this->checkboxOptionsMode === 'static' && count($this->checkboxStaticOptions) === 0) {
                $this->addError('checkboxStaticOptions', 'Add at least one checkbox option.');

                return false;
            }

            if ($this->checkboxOptionsMode === 'preset' && $this->checkboxPresetModel === '') {
                $this->addError('checkboxPresetModel', 'Select a preset data source.');

                return false;
            }

            if ($this->checkboxOptionsMode === 'dataset' && $this->checkboxDatasetSourceTable === '') {
                $this->addError('checkboxDatasetSourceTable', 'Select a source table for dataset options.');

                return false;
            }
        }

        if ($this->stepType === 'custom_table') {
            if ($this->table_mode === 'dynamic' && $this->row_driver === '') {
                $this->addError('row_driver', 'Select a row driver for dynamic tables.');

                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildStepConfigPayload(): array
    {
        return match ($this->stepType) {
            'static_text' => ['content' => trim($this->staticTextContent)],
            'checkbox' => [
                'options_mode' => $this->checkboxOptionsMode,
                'static_options' => $this->checkboxOptionsMode === 'static'
                    ? array_values($this->checkboxStaticOptions)
                    : [],
                'preset_model' => $this->checkboxOptionsMode === 'preset'
                    ? $this->checkboxPresetModel
                    : null,
                'dataset_config' => $this->checkboxOptionsMode === 'dataset'
                    ? $this->buildCheckboxDatasetConfig()
                    : null,
            ],
            'pcr_plate_map' => [
                'has_std_controls_buffers' => $this->pcrHasStdControlsBuffers,
                'standards' => array_values(array_filter(array_map('trim', $this->pcrStandards))),
                'controls' => array_values(array_filter(array_map('trim', $this->pcrControls))),
                'buffers' => array_values(array_filter(array_map('trim', $this->pcrBuffers))),
                'preset_wells' => $this->pcrHasStdControlsBuffers ? $this->pcrPresetWells : [],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildCheckboxDatasetConfig(): array
    {
        return [
            'source_table' => $this->checkboxDatasetSourceTable,
            'display_mode' => $this->checkboxDatasetDisplayMode,
            'source_display_column' => $this->checkboxDatasetDisplayMode === 'direct'
                ? $this->checkboxDatasetSourceColumn
                : null,
            'foreign_key_column' => $this->checkboxDatasetDisplayMode === 'foreign_key'
                ? $this->checkboxDatasetFkColumn
                : null,
            'referenced_table' => $this->checkboxDatasetDisplayMode === 'foreign_key'
                ? $this->checkboxDatasetReferencedTable
                : null,
            'referenced_key_column' => $this->checkboxDatasetReferencedKeyColumn ?: 'id',
            'referenced_display_column' => $this->checkboxDatasetDisplayMode === 'foreign_key'
                ? $this->checkboxDatasetReferencedDisplayColumn
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $lookupConfig
     * @return array<string, mixed>
     */
    protected function buildStepPayload(array $lookupConfig): array
    {
        $isCalculable = in_array($this->stepType, ['input', 'derived', 'lookup', 'parameter_result'], true);

        return [
            'formula_version_id' => $this->formulaVersion->id,
            'step_number' => $this->stepNumber,
            'variable_name' => $this->variableName,
            'step_type' => $this->stepType,
            'expression' => $this->stepType === 'derived' ? $this->expression : null,
            'label' => $this->label,
            'description' => $this->description,
            'lookup_config' => $this->stepType === 'lookup' ? $lookupConfig : [],
            'step_config' => $isCalculable ? null : $this->buildStepConfigPayload(),
            'table_mode' => $this->stepType === 'custom_table' ? $this->table_mode : null,
            'row_driver' => $this->stepType === 'custom_table' && $this->table_mode === 'dynamic'
                ? $this->row_driver
                : null,
            'row_driver_filters' => null,
            'allow_manual_rows' => $this->stepType === 'custom_table' && $this->table_mode === 'dynamic'
                ? $this->allow_manual_rows
                : false,
            'analyte_id' => $this->stepType === 'parameter_result' ? $this->analyteId : null,
        ];
    }

    protected function resetTestForm()
    {
        $this->testInputs = [];
        $this->testResults = [];
        $this->testExecutionData = [];
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function isAnyModalOpen(): bool
    {
        return $this->showCreateStepModal
            || $this->showEditStepModal
            || $this->showTestFormulaModal
            || $this->showDeleteStepModal
            || $this->showDeletionBlockedModal
            || $this->showCreateFieldModal
            || $this->showEditFieldModal
            || $this->showDeleteFieldModal
            || $this->showConfigureTableModal
            || $this->showCreateStepColumnModal
            || $this->showEditStepColumnModal;
    }

    /**
     * @return array<string, string>
     */
    public function getStepTypeOptionsProperty(): array
    {
        return [
            'input' => 'Input',
            'derived' => 'Derived',
            'lookup' => 'Lookup',
            'parameter_result' => 'Parameter Result',
            'static_text' => 'Static Text',
            'checkbox' => 'Checkbox',
            'custom_table' => 'Custom Table',
            'pcr_plate_map' => 'PCR Plate Map',
        ];
    }

    public function messageAlertClass(): string
    {
        return match ($this->messageType) {
            'success' => 'success',
            'warning' => 'warning',
            default => 'danger',
        };
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
            if ($step->step_number < $this->stepNumber && $step->isExpressionVariable()) {
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
        $this->showEditFieldModal = false;
        $this->resetFieldForm();
        $this->fieldOrder = count($this->mandatoryFields) + 1;
        $this->fieldFormPlacement = $this->mandatoryFieldsPlacement;
        $this->showFieldFormPlacementDropdown = false;
        $this->showCreateFieldModal = true;
    }

    public function closeMandatoryFieldModal(): void
    {
        $this->showCreateFieldModal = false;
        $this->showEditFieldModal = false;
        $this->showFieldFormPlacementDropdown = false;
    }

    public function showEditFieldModalInit($fieldId)
    {
        $this->showCreateFieldModal = false;
        $field = FormulaMandatoryField::findOrFail($fieldId);
        $this->editingField = $field;
        $this->fieldLabel = $field->label;
        $this->fieldType = $field->field_type;
        $this->fieldOrder = $field->order;
        $this->fieldHelpText = $field->help_text ?? '';
        $this->fieldModelTiedTo = $field->model_tied_to ?? '';
        $this->fieldIsRequired = $field->is_required;
        $this->fieldValueName = $field->field_value_name;
        $options = $field->field_options['options'] ?? [];
        $this->fieldChoiceOptions = is_array($options) ? array_values($options) : [];
        $this->fieldNewChoice = '';
        $this->fieldFormPlacement = $field->form_placement ?? $this->mandatoryFieldsPlacement;
        $this->showFieldFormPlacementDropdown = false;
        $this->showEditFieldModal = true;
    }

    public function updatedMandatoryFieldsPlacement(): void
    {
        $this->validate([
            'mandatoryFieldsPlacement' => 'required|in:top,bottom',
        ]);

        try {
            $this->formulaVersion->update([
                'mandatory_fields_placement' => $this->mandatoryFieldsPlacement,
            ]);
            $this->formulaVersion->refresh();
            $this->setMessage('Mandatory fields placement updated.', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating placement: ' . $e->getMessage(), 'error');
        }
    }

    public function saveDocumentControl(): void
    {
        $hasControlNo = Schema::hasColumn('formula_versions', 'document_control_no');
        $hasRevisionNo = Schema::hasColumn('formula_versions', 'document_control_revision_no');
        $hasIssueDate = Schema::hasColumn('formula_versions', 'document_control_issue_date');

        if (! $hasControlNo && ! $hasRevisionNo && ! $hasIssueDate) {
            $this->setMessage('Document control fields are not available yet. Run migrations first.', 'error');

            return;
        }

        $this->validate([
            'documentControlNo' => 'nullable|string|max:255',
            'documentControlRevisionNo' => 'nullable|string|max:100',
            'documentControlIssueDate' => 'nullable|date',
        ]);

        try {
            $payload = [];

            if ($hasControlNo) {
                $payload['document_control_no'] = $this->documentControlNo !== ''
                    ? trim($this->documentControlNo)
                    : null;
            }

            if ($hasRevisionNo) {
                $payload['document_control_revision_no'] = $this->documentControlRevisionNo !== ''
                    ? trim($this->documentControlRevisionNo)
                    : null;
            }

            if ($hasIssueDate) {
                $payload['document_control_issue_date'] = $this->documentControlIssueDate ?: null;
            }

            $this->formulaVersion->update($payload);
            $this->formulaVersion->refresh();
            $this->setMessage('Document control details saved.', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error saving document control: ' . $e->getMessage(), 'error');
        }
    }

    public function addFieldChoice(): void
    {
        $value = trim($this->fieldNewChoice);
        if ($value === '') {
            $this->addError('fieldNewChoice', 'Enter a choice before adding.');

            return;
        }

        foreach ($this->fieldChoiceOptions as $existing) {
            if (strcasecmp(trim((string) $existing), $value) === 0) {
                $this->addError('fieldNewChoice', 'This choice already exists.');

                return;
            }
        }

        $this->fieldChoiceOptions[] = $value;
        $this->fieldNewChoice = '';
        $this->resetErrorBag('fieldNewChoice');
    }

    public function removeFieldChoice(int $index): void
    {
        if (! array_key_exists($index, $this->fieldChoiceOptions)) {
            return;
        }

        unset($this->fieldChoiceOptions[$index]);
        $this->fieldChoiceOptions = array_values($this->fieldChoiceOptions);
    }

    public function createMandatoryField()
    {
        $this->fieldValueName = $this->normalizedFieldValueName();
        $this->validate($this->mandatoryFieldRules());
        $this->validateChoiceOptionsForCheckbox();

        try {
            FormulaMandatoryField::create($this->buildMandatoryFieldPayload());

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
        $this->fieldValueName = $this->normalizedFieldValueName();
        $this->validate($this->mandatoryFieldRules());
        $this->validateChoiceOptionsForCheckbox();

        try {
            $payload = $this->buildMandatoryFieldPayload();
            unset($payload['formula_version_id']);
            $this->editingField->update($payload);

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
        if ($this->fieldType !== 'dataset_related') {
            $this->fieldModelTiedTo = '';
        }

        if ($this->fieldType !== 'checkbox') {
            $this->fieldChoiceOptions = [];
            $this->fieldNewChoice = '';
        }
    }

    protected function validateChoiceOptionsForCheckbox(): void
    {
        if ($this->fieldType !== 'checkbox') {
            return;
        }

        $choices = array_values(array_filter(array_map(
            fn ($option) => trim((string) $option),
            $this->fieldChoiceOptions
        )));

        if ($choices === []) {
            $this->addError('fieldChoiceOptions', 'Add at least one checkbox option.');

            throw \Illuminate\Validation\ValidationException::withMessages([
                'fieldChoiceOptions' => ['Add at least one checkbox option.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildMandatoryFieldPayload(): array
    {
        $choices = array_values(array_filter(array_map(
            fn ($option) => trim((string) $option),
            $this->fieldChoiceOptions
        )));

        $payload = [
            'formula_version_id' => $this->formulaVersion->id,
            'label' => $this->fieldLabel,
            'field_type' => $this->fieldType,
            'order' => $this->fieldOrder,
            'help_text' => $this->fieldHelpText ?: null,
            'model_tied_to' => $this->fieldType === 'dataset_related' ? $this->fieldModelTiedTo : null,
            'is_required' => $this->fieldIsRequired,
            'field_value_name' => $this->fieldValueName,
        ];

        // Backward-compatible payload while migrations are being applied.
        if (Schema::hasColumn('formula_mandatory_fields', 'form_placement')) {
            $payload['form_placement'] = $this->fieldFormPlacement;
        }

        if (Schema::hasColumn('formula_mandatory_fields', 'field_options')) {
            $payload['field_options'] = $this->fieldType === 'checkbox'
                ? ['options' => $choices]
                : null;
        }

        return $payload;
    }

    protected function normalizedFieldValueName(): string
    {
        $normalized = strtolower(trim($this->fieldValueName));
        $normalized = preg_replace('/[^a-z0-9_]+/', '_', $normalized) ?? '';
        $normalized = trim($normalized, '_');

        return $normalized;
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
        $this->fieldNewChoice = '';
        $this->fieldChoiceOptions = [];
        $this->fieldFormPlacement = $this->mandatoryFieldsPlacement;
        $this->showFieldFormPlacementDropdown = false;
        $this->editingField = null;
    }
}