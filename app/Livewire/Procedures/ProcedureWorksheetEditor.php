<?php

namespace App\Livewire\Procedures;

use Livewire\Component;
use App\Enums\Procedures\ProcedureStepTableColumnType;
use App\Enums\Procedures\ProcedureTableRowDriver;
use App\Models\Procedures\ProcedureStepTableColumn;
use App\Models\Procedures\ProcedureStepTableStaticCell;
use App\Models\Procedures\ProcedureStepTableStaticRow;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Procedures\ProcedureConfigField;
use App\Models\Procedures\ProcedureConfigFieldSection;
use App\Models\Procedures\ProcedureWorksheetStepGroup;
use App\Models\Procedures\ProcedureTestKitColumn;
use App\Services\Formulars\FormulaEvaluator;
use App\Services\LogEntryWorksheets\LogEntryDatabaseSchemaService;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use Carbon\Carbon;
use App\ReportingUnit;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class ProcedureWorksheetEditor extends Component
{
    use WithPagination;

    public $worksheetId;
    public $search = '';
    public $perPage = 25;
    public $activeTab = 'steps';
    
    // Modal properties
    public $showModal = false;
    public $editingStepId = null;
    public $showDeleteModal = false;
    public $stepIdToDelete = null;
    
    // Import properties
    public $showImportModal = false;
    public $importWorksheetSearch = '';
    public $selectedImportWorksheetId = null;
    public $importableWorksheets = [];
    public $importTypeSteps = true;
    public $importTypeConfig = false;
    public $importTypeTestKit = false;

    // Form properties
    public $step = '';
    public $is_active = true;
    public $is_result_step = false;
    public $attracts_equipment_logbook = false;
    public $logbook_equipment_ids = [];
    public $default_equipment_id = null;
    public $default_analyst_id = null;
    public $default_measurand_ids = [];
    public $value_type = 'text';
    public $default_value = '';
    public $default_measurand_values = [];
    public $select_options = [];
    public $newSelectOption = '';

    public string $table_mode = 'dynamic';

    public string $row_driver = 'captured_result';

    public bool $allow_manual_rows = false;

    public string $config_fields_placement = 'top';

    public bool $showConfigureTableModal = false;

    public int $configureTableWizardStep = 1;

    public ?string $configuringStepId = null;

    /** @var list<string> */
    public array $expandedCustomTableStepIds = [];

    public array $stepTableColumns = [];

    public bool $showCreateStepColumnModal = false;

    public bool $showEditStepColumnModal = false;

    public bool $showInlineStaticRowForm = false;

    public ?ProcedureStepTableColumn $editingStepColumn = null;

    public string $stepColumnLabel = '';

    public string $stepColumnKey = '';

    public string $stepColumnType = 'input';

    public string $stepColumnInputDataType = 'string';

    public string $stepColumnExpression = '';

    public string $stepColumnModelTiedTo = '';

    public int $stepColumnOrder = 1;

    public bool $stepColumnIsRequired = false;

    public string $stepColumnHelpText = '';

    public string $stepColumnChoiceControl = 'radio';

    public string $stepColumnStaticOptions = '';

    public ?string $stepColumnValidationMessage = null;

    public array $stepStaticRows = [];

    public array $stepStaticCellValues = [];

    public string $newStaticRowLabel = '';

    // Search properties for dropdowns
    public $equipmentSearch = '';
    public $logbookEquipmentSearch = '';
    public $analystSearch = '';
    public $measurandSearch = '';
    public $methodSearch = '';
    public $defaultValueEquipmentSearch = '';

    // Dropdown visibility
    public $showEquipmentDropdown = false;
    public $showLogbookEquipmentDropdown = false;
    public $showAnalystDropdown = false;
    public $showMeasurandDropdown = false;
    public $showMethodDropdown = false;
    public $showDefaultValueEquipmentDropdown = false;

    // Configurable Fields state
    public $configFields = [];
    public $configFieldSearch = '';
    public $showCreateConfigFieldModal = false;
    public $showEditConfigFieldModal = false;
    public $showDeleteConfigFieldModalOpen = false;
    public $editingConfigField = null;
    public $deletingConfigField = null;

    // Configurable Field form properties
    public $configFieldLabel = '';
    public $configFieldType = 'input';
    public $configFieldModelTiedTo = '';
    public $configFieldOrder = 1;
    public $configFieldHelpText = '';
    public $configFieldIsRequired = true;
    public $configFieldValueName = '';

    public string $configFieldSampleColumn = '';

    public string $configFieldSampleRelationColumn = '';

    public string $configFieldSampleDisplayMode = 'direct';

    public string $configFieldDatasetDisplayMode = 'direct';

    public string $configFieldDatasetSourceColumn = '';

    public string $configFieldDatasetFkColumn = '';

    public string $configFieldDatasetReferencedTable = '';

    public string $configFieldDatasetReferencedDisplayColumn = '';

    public ?string $configFieldSectionId = null;

    public array $configFieldSections = [];

    public bool $showCreateConfigSectionModal = false;

    public bool $showEditConfigSectionModal = false;

    public bool $showDeleteConfigSectionModalOpen = false;

    public ?ProcedureConfigFieldSection $editingConfigSection = null;

    public ?ProcedureConfigFieldSection $deletingConfigSection = null;

    public string $configSectionTitle = '';

    public string $configSectionDescription = '';

    public int $configSectionOrder = 1;

    public ?string $stepGroupId = null;

    public array $stepGroups = [];

    public bool $showCreateStepGroupModal = false;

    public bool $showEditStepGroupModal = false;

    public bool $showDeleteStepGroupModalOpen = false;

    public ?ProcedureWorksheetStepGroup $editingStepGroup = null;

    public ?ProcedureWorksheetStepGroup $deletingStepGroup = null;

    public string $stepGroupTitle = '';

    public string $stepGroupDescription = '';

    public int $stepGroupOrder = 1;

    // Test Kit Columns state
    public $testKitColumns = [];
    public $testKitColumnSearch = '';
    public $showCreateTestKitColumnModal = false;
    public $showEditTestKitColumnModal = false;
    public $showDeleteTestKitColumnModalOpen = false;
    public $editingTestKitColumn = null;
    public $deletingTestKitColumn = null;

    // Test Kit Column form properties
    public $testKitColumnLabel = '';
    public $testKitColumnKey = '';
    public $testKitColumnType = 'string';
    public $testKitColumnOrder = 1;
    public $testKitColumnIsRequired = false;
    public $testKitColumnHelpText = '';

    // Toast state
    public $toastMessage = '';
    public $toastType = 'success';

    // Document Control (worksheet-level)
    public $documentControlNo = '';
    public $revision = '';
    public $issueDate = '';

    protected $rules = [
        'step' => 'required|string|max:255',
        'value_type' => 'required|in:text,number,time,datetime,date,method_select,equipment_select,custom_select,custom_table,static_text',
        'table_mode' => 'nullable|required_if:value_type,custom_table|in:dynamic,static',
        'row_driver' => 'nullable|required_if:table_mode,dynamic|in:sample_header,sample_detail,captured_result,method,equipment',
        'allow_manual_rows' => 'boolean',
        'select_options' => 'nullable|array',
        'select_options.*' => 'nullable|string|max:255',
        'default_value' => 'nullable|string|max:10000',
        'default_measurand_values' => 'nullable|array',
        'default_measurand_values.*' => 'nullable|string|max:255',
        'default_equipment_id' => 'nullable|exists:equipment,id',
        'default_analyst_id' => 'nullable|exists:users,id',
        'default_measurand_ids' => 'nullable|array',
        'is_active' => 'boolean',
        'is_result_step' => 'boolean',
        'attracts_equipment_logbook' => 'boolean',
        'logbook_equipment_ids' => 'nullable|array',
        'logbook_equipment_ids.*' => 'exists:equipment,id',
    ];

    /**
     * Normalize the default value to a canonical format compatible with the
     * browser input types we will render in the worksheet.
     */
    protected function normalizeDefaultValue(?string $raw, string $valueType): ?string
    {
        if ($raw === null) {
            return null;
        }

        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        return match ($valueType) {
            'date' => $this->normalizeDateValue($value),
            'time' => $this->normalizeTimeValue($value),
            'datetime' => $this->normalizeDateTimeValue($value),
            'number', 'text', 'static_text', 'method_select', 'equipment_select', 'custom_select' => $value,
            default => $value,
        };
    }

    public function updatedValueType(): void
    {
        $this->default_value = '';
        $this->default_measurand_values = [];
        $this->methodSearch = '';
        $this->defaultValueEquipmentSearch = '';
        $this->showMethodDropdown = false;
        $this->showDefaultValueEquipmentDropdown = false;

        if ($this->value_type !== 'custom_select') {
            $this->select_options = [];
            $this->newSelectOption = '';
        }

        if ($this->value_type === 'custom_table' || $this->value_type === 'static_text') {
            $this->is_result_step = false;
        }
    }

    public function addSelectOption(): void
    {
        $label = trim($this->newSelectOption);
        if ($label === '') {
            return;
        }

        if (! in_array($label, $this->select_options, true)) {
            $this->select_options[] = $label;
        }

        $this->newSelectOption = '';
    }

    public function removeSelectOption(int $index): void
    {
        if (! isset($this->select_options[$index])) {
            return;
        }

        $removed = $this->select_options[$index];
        unset($this->select_options[$index]);
        $this->select_options = array_values($this->select_options);

        if ((string) $this->default_value === (string) $removed) {
            $this->default_value = '';
        }
    }

    public function selectDefaultMethod(string $id): void
    {
        $this->default_value = $id;
        $this->showMethodDropdown = false;
        $this->methodSearch = '';
    }

    public function clearDefaultMethod(): void
    {
        $this->default_value = '';
        $this->methodSearch = '';
    }

    public function selectDefaultValueEquipment($id): void
    {
        $this->default_value = (string) $id;
        $this->showDefaultValueEquipmentDropdown = false;
        $this->defaultValueEquipmentSearch = '';
    }

    public function clearDefaultValueEquipment(): void
    {
        $this->default_value = '';
        $this->defaultValueEquipmentSearch = '';
    }

    protected function normalizeDateValue(string $value): string
    {
        if (\preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function normalizeTimeValue(string $value): string
    {
        if (\preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }
        if (\preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            try {
                return Carbon::parse($value)->format('H:i');
            } catch (\Throwable) {
                return $value;
            }
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function normalizeDateTimeValue(string $value): string
    {
        if (\preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }
        if (\preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            try {
                return Carbon::parse($value)->format('Y-m-d\TH:i');
            } catch (\Throwable) {
                return $value;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function configFieldRules(): array
    {
        return [
            'configFieldLabel' => 'required|string|max:255',
            'configFieldType' => 'required|in:input,datetime,date,number,checkbox,textarea,dataset,dataset_multiselect,customer_select,lab_select,sample_select',
            'configFieldValueName' => 'required|string|max:255',
            'configFieldHelpText' => 'nullable|string',
            'configFieldIsRequired' => 'boolean',
            'configFieldModelTiedTo' => 'nullable|required_if:configFieldType,dataset|required_if:configFieldType,dataset_multiselect|string|max:2000',
            'configFieldDatasetDisplayMode' => 'nullable|in:direct,foreign_key',
            'configFieldDatasetSourceColumn' => 'nullable|string|max:191',
            'configFieldDatasetFkColumn' => 'nullable|string|max:191',
            'configFieldDatasetReferencedDisplayColumn' => 'nullable|string|max:191',
            'configFieldSampleColumn' => 'nullable|required_if:configFieldType,sample_select|string|max:191',
            'configFieldSampleRelationColumn' => 'nullable|string|max:191',
            'configFieldSampleDisplayMode' => 'nullable|in:direct,foreign_key',
            'configFieldSectionId' => Schema::hasTable('procedure_config_field_sections')
                ? 'nullable|uuid|exists:procedure_config_field_sections,id'
                : 'nullable',
        ];
    }

    protected function testKitColumnRules(): array
    {
        return [
            'testKitColumnLabel' => 'required|string|max:255',
            'testKitColumnKey' => 'required|string|max:255',
            'testKitColumnType' => 'required|in:string,number,date,boolean',
            'testKitColumnHelpText' => 'nullable|string',
            'testKitColumnIsRequired' => 'boolean',
        ];
    }

    public function mount($procedureWorksheet)
    {
        $this->worksheetId = $procedureWorksheet->id;
        $this->loadConfigFields();
        $this->loadConfigFieldSections();
        $this->loadStepGroups();
        $this->loadTestKitColumns();
        $this->loadDocumentControl();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;

        if ($tab === 'config') {
            $this->loadConfigFieldsCaptureSettings();
            $this->loadConfigFieldSections();
        }

        if ($tab === 'steps') {
            $this->loadStepGroups();
        }
    }

    public function clearToast(): void
    {
        $this->toastMessage = '';
    }

    public function openMethodDropdown(): void
    {
        $this->showMethodDropdown = true;
    }

    public function closeMethodDropdown(): void
    {
        $this->showMethodDropdown = false;
    }

    public function openDefaultValueEquipmentDropdown(): void
    {
        $this->showDefaultValueEquipmentDropdown = true;
    }

    public function closeDefaultValueEquipmentDropdown(): void
    {
        $this->showDefaultValueEquipmentDropdown = false;
    }

    public function openEquipmentDropdown(): void
    {
        $this->closeMeasurandDropdown();
        $this->closeLogbookEquipmentDropdown();
        $this->showEquipmentDropdown = true;
    }

    public function closeEquipmentDropdown(): void
    {
        $this->showEquipmentDropdown = false;
    }

    public function openLogbookEquipmentDropdown(): void
    {
        $this->closeMeasurandDropdown();
        $this->closeEquipmentDropdown();
        $this->closeAnalystDropdown();
        $this->showLogbookEquipmentDropdown = true;
    }

    public function closeLogbookEquipmentDropdown(): void
    {
        $this->showLogbookEquipmentDropdown = false;
    }

    public function openAnalystDropdown(): void
    {
        $this->closeMeasurandDropdown();
        $this->closeLogbookEquipmentDropdown();
        $this->showAnalystDropdown = true;
    }

    public function closeAnalystDropdown(): void
    {
        $this->showAnalystDropdown = false;
    }

    public function openMeasurandDropdown(): void
    {
        $this->closeEquipmentDropdown();
        $this->closeAnalystDropdown();
        $this->closeLogbookEquipmentDropdown();
        $this->showMeasurandDropdown = true;
    }

    public function closeMeasurandDropdown(): void
    {
        $this->showMeasurandDropdown = false;
    }

    public function clearDefaultEquipment(): void
    {
        $this->default_equipment_id = null;
    }

    public function clearDefaultAnalyst(): void
    {
        $this->default_analyst_id = null;
    }

    public function selectImportWorksheet(string $worksheetId): void
    {
        $this->selectedImportWorksheetId = $worksheetId;
    }

    public function clearSelectedImportWorksheet(): void
    {
        $this->selectedImportWorksheetId = null;
    }

    public function dismissDeleteConfigFieldModal(): void
    {
        $this->showDeleteConfigFieldModalOpen = false;
    }

    public function dismissDeleteTestKitColumnModal(): void
    {
        $this->showDeleteTestKitColumnModalOpen = false;
    }

    public function setDefaultMeasurandValue(string $measurandId, ?string $value): void
    {
        if ($value === null || $value === '') {
            unset($this->default_measurand_values[$measurandId]);

            return;
        }

        $this->default_measurand_values[$measurandId] = $value;
    }

    public function updatedWorksheetId($value): void
    {
        $this->resetPage();
        $this->resetForm();

        $this->configFieldSearch = '';
        $this->testKitColumnSearch = '';

        $this->loadConfigFields();
        $this->loadTestKitColumns();
        $this->loadDocumentControl();

        $this->activeTab = 'steps';
    }

    public function render()
    {
        $worksheet = ProcedureWorksheet::findOrFail($this->worksheetId);
        
        $query = $worksheet->steps();

        if ($this->search) {
            $query->where('step', 'like', '%' . $this->search . '%');
        }

        $steps = $query
            ->with([
                'tableColumns',
                'staticRows.cells',
            ])
            ->orderBy('order', 'asc')
            ->get();

        $stepDisplayBlocks = $this->buildStepDisplayBlocks($steps);

        $configFieldDisplayBlocks = $this->buildConfigFieldDisplayBlocks();

        // Fetch data for dropdowns
        $equipments = [];
        if ($this->showEquipmentDropdown) {
            $equipments = Equipment::where('name', 'like', '%' . $this->equipmentSearch . '%')
                ->orWhere('equipment_number', 'like', '%' . $this->equipmentSearch . '%')
                ->limit(10)->get();
        }

        $analysts = [];
        if ($this->showAnalystDropdown) {
            $analysts = User::where('name', 'like', '%' . $this->analystSearch . '%')
                ->where('active', 1)
                ->limit(10)->get();
        }

        $measurands = $this->showMeasurandDropdown
            ? $this->searchMeasurands($this->measurandSearch)
            : collect();

        $logbookEquipments = $this->showLogbookEquipmentDropdown
            ? $this->searchEquipments($this->logbookEquipmentSearch)
            : collect();

        // Normalize potential JSON/array IDs (from json columns) to a single scalar ID
        $equipmentId = is_array($this->default_equipment_id)
            ? (reset($this->default_equipment_id) ?: null)
            : $this->default_equipment_id;

        $analystId = is_array($this->default_analyst_id)
            ? (reset($this->default_analyst_id) ?: null)
            : $this->default_analyst_id;

        $selectedEquipment = $equipmentId ? Equipment::find($equipmentId) : null;
        $selectedAnalyst = $analystId ? User::find($analystId) : null;
        $selectedMeasurands = ReportingUnit::whereIn('id', $this->default_measurand_ids)->get();
        $selectedLogbookEquipments = Equipment::whereIn('id', $this->logbook_equipment_ids)->get();

        $methods = [];
        if ($this->showMethodDropdown) {
            $methods = AnalysisMethod::query()
                ->where('active', 1)
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->methodSearch . '%')
                        ->orWhere('code', 'like', '%' . $this->methodSearch . '%');
                })
                ->orderBy('name')
                ->limit(12)
                ->get();
        }

        $defaultValueEquipments = [];
        if ($this->showDefaultValueEquipmentDropdown) {
            $defaultValueEquipments = Equipment::query()
                ->where('name', 'like', '%' . $this->defaultValueEquipmentSearch . '%')
                ->orWhere('equipment_number', 'like', '%' . $this->defaultValueEquipmentSearch . '%')
                ->orderBy('name')
                ->limit(12)
                ->get();
        }

        $selectedDefaultMethod = ($this->value_type === 'method_select' && $this->default_value !== '')
            ? AnalysisMethod::find($this->default_value)
            : null;

        $selectedDefaultValueEquipment = ($this->value_type === 'equipment_select' && $this->default_value !== '')
            ? Equipment::find($this->default_value)
            : null;

        $methodOptionsList = ($this->showModal && $this->value_type === 'method_select')
            ? AnalysisMethod::query()->where('active', 1)->orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        $equipmentOptionsList = ($this->showModal && $this->value_type === 'equipment_select')
            ? Equipment::query()->orderBy('name')->get(['id', 'name', 'equipment_number'])
            : collect();

        return view('livewire.procedures.procedure-worksheet-editor', [
            'worksheet' => $worksheet,
            'steps' => $steps,
            'equipments' => $equipments,
            'analysts' => $analysts,
            'measurands' => $measurands,
            'logbookEquipments' => $logbookEquipments,
            'selectedEquipment' => $selectedEquipment,
            'selectedLogbookEquipments' => $selectedLogbookEquipments,
            'selectedAnalyst' => $selectedAnalyst,
            'selectedMeasurands' => $selectedMeasurands,
            'methods' => $methods,
            'defaultValueEquipments' => $defaultValueEquipments,
            'selectedDefaultMethod' => $selectedDefaultMethod,
            'selectedDefaultValueEquipment' => $selectedDefaultValueEquipment,
            'methodOptionsList' => $methodOptionsList,
            'equipmentOptionsList' => $equipmentOptionsList,
            'configFields' => $this->configFields,
            'testKitColumns' => $this->testKitColumns,
            'rowDriverOptions' => ProcedureTableRowDriver::options(),
            'stepColumnTypeOptions' => array_merge(
                ProcedureStepTableColumnType::options(),
                ['static' => 'Static']
            ),
            'stepTableDatasetOptions' => ProcedureStepTableColumn::datasetPresetOptions(),
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
            'stepDisplayBlocks' => $stepDisplayBlocks,
            'configFieldDisplayBlocks' => $configFieldDisplayBlocks,
            'stepGroupOptions' => $this->stepGroups,
            'configSectionOptions' => $this->configFieldSections,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ProcedureWorksheetStep>  $steps
     * @return array<int, array{type: string, step_group?: ProcedureWorksheetStepGroup|null, steps?: \Illuminate\Support\Collection, step?: ProcedureWorksheetStep}>
     */
    protected function buildStepDisplayBlocks(Collection $steps): array
    {
        $blocks = [];
        $scalarBuffer = collect();
        $lastGroupId = null;
        $groupsById = ProcedureWorksheetStepGroup::where('procedure_worksheet_id', $this->worksheetId)
            ->get()
            ->keyBy('id');

        $flushScalar = function () use (&$blocks, &$scalarBuffer, &$lastGroupId, $groupsById) {
            if ($scalarBuffer->isEmpty()) {
                return;
            }

            $blocks[] = [
                'type' => 'scalar',
                'step_group' => $lastGroupId ? $groupsById->get($lastGroupId) : null,
                'steps' => $scalarBuffer->values(),
            ];
            $scalarBuffer = collect();
        };

        foreach ($steps as $step) {
            $groupId = $step->procedure_worksheet_step_group_id;

            if ($step->isCustomTable()) {
                $flushScalar();
                $blocks[] = [
                    'type' => 'custom_table',
                    'step_group' => $groupId ? $groupsById->get($groupId) : null,
                    'step' => $step,
                ];
                $lastGroupId = $groupId;
                continue;
            }

            if ($groupId !== $lastGroupId && $scalarBuffer->isNotEmpty()) {
                $flushScalar();
            }

            $lastGroupId = $groupId;
            $scalarBuffer->push($step);
        }

        $flushScalar();

        return $blocks;
    }

    /**
     * @return array<int, array{section: ProcedureConfigFieldSection|null, fields: array<int, array<string, mixed>>}>
     */
    protected function buildConfigFieldDisplayBlocks(): array
    {
        $blocks = [];
        $fields = collect($this->configFields);
        $sections = ProcedureConfigFieldSection::where('procedure_worksheet_id', $this->worksheetId)
            ->orderBy('order')
            ->get();

        foreach ($sections as $section) {
            $sectionFields = $fields
                ->filter(fn (array $field) => (string) ($field['procedure_config_field_section_id'] ?? '') === (string) $section->id)
                ->values()
                ->all();
            if (count($sectionFields) > 0) {
                $blocks[] = [
                    'section' => $section,
                    'fields' => $sectionFields,
                ];
            }
        }

        $ungrouped = $fields
            ->filter(fn (array $field) => empty($field['procedure_config_field_section_id']))
            ->values()
            ->all();
        if (count($ungrouped) > 0) {
            $blocks[] = [
                'section' => null,
                'fields' => $ungrouped,
            ];
        }

        return $blocks;
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $step = ProcedureWorksheetStep::findOrFail($id);
        $this->editingStepId = $id;
        $this->step = $step->step;
        $this->is_active = $step->is_active;
        $this->is_result_step = (bool) $step->is_result_step;
        $this->attracts_equipment_logbook = (bool) $step->attracts_equipment_logbook;
        $this->logbook_equipment_ids = $step->logbook_equipment_ids ?? [];
        $this->default_equipment_id = $step->default_equipment_id;
        $this->default_analyst_id = $step->default_analyst_id;
        $this->default_measurand_ids = $step->default_measurand_ids ?? [];
        $this->value_type = $step->value_type ?: 'text';
        $this->default_value = $step->default_value ?? '';
        $this->default_measurand_values = $step->default_measurand_values ?? [];
        $this->select_options = $step->select_options ?? [];
        $this->table_mode = $step->table_mode ?? 'dynamic';
        $this->row_driver = $step->row_driver ?? 'captured_result';
        $this->allow_manual_rows = (bool) $step->allow_manual_rows;
        $this->stepGroupId = $step->procedure_worksheet_step_group_id;
        if ($this->value_type === 'custom_table' || $this->value_type === 'static_text') {
            $this->is_result_step = false;
        }
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->attracts_equipment_logbook) {
            $this->logbook_equipment_ids = array_values(array_unique(array_map(
                'strval',
                $this->logbook_equipment_ids ?? []
            )));

            if (count($this->logbook_equipment_ids) === 0) {
                $this->addError('logbook_equipment_ids', 'Select at least one piece of equipment for the logbook.');

                return;
            }
        } else {
            $this->logbook_equipment_ids = [];
        }

        if ($this->value_type === 'custom_select') {
            $this->select_options = array_values(array_filter(array_map(
                fn ($option) => trim((string) $option),
                $this->select_options
            )));

            if (count($this->select_options) === 0) {
                $this->addError('select_options', 'Add at least one option for custom select.');

                return;
            }

            if ($this->default_value !== '' && ! in_array($this->default_value, $this->select_options, true)) {
                $this->addError('default_value', 'Default value must be one of the custom options.');

                return;
            }
        } else {
            $this->select_options = [];
        }

        if ($this->value_type === 'static_text') {
            if (trim((string) $this->default_value) === '') {
                $this->addError('default_value', 'Enter the static text to display on the worksheet.');

                return;
            }

            $this->is_result_step = false;
            $this->default_measurand_values = [];
        }

        if ($this->value_type === 'custom_table') {
            $this->is_result_step = false;
            $this->attracts_equipment_logbook = false;
            $this->logbook_equipment_ids = [];
            $this->default_equipment_id = null;
            $this->default_analyst_id = null;
            $this->default_measurand_ids = [];
            $this->default_value = '';
            $this->default_measurand_values = [];
        } else {
            $this->table_mode = null;
            $this->row_driver = null;
            $this->allow_manual_rows = false;
        }

        $normalizedDefault = $this->normalizeDefaultValue(
            $this->default_value,
            (string) $this->value_type
        );

        $allowedMeasurandIds = array_map('strval', $this->default_measurand_ids ?? []);
        $normalizedMeasurandDefaults = [];
        if (is_array($this->default_measurand_values)) {
            foreach ($this->default_measurand_values as $measurandId => $rawValue) {
                $mid = (string) $measurandId;
                if (! in_array($mid, $allowedMeasurandIds, true)) {
                    continue;
                }

                $normalized = $this->normalizeDefaultValue(
                    is_string($rawValue) ? $rawValue : (string) $rawValue,
                    (string) $this->value_type
                );

                if ($normalized === null || trim((string) $normalized) === '') {
                    continue;
                }

                $normalizedMeasurandDefaults[$mid] = $normalized;
            }
        }

        $stepPayload = [
            'procedure_worksheet_id' => $this->worksheetId,
            'step' => $this->step,
            'is_active' => $this->is_active,
            'is_result_step' => $this->is_result_step,
            'attracts_equipment_logbook' => $this->attracts_equipment_logbook,
            'logbook_equipment_ids' => $this->attracts_equipment_logbook ? $this->logbook_equipment_ids : null,
            'default_equipment_id' => $this->default_equipment_id,
            'default_analyst_id' => $this->default_analyst_id,
            'default_measurand_ids' => $this->default_measurand_ids,
            'value_type' => $this->value_type,
            'default_value' => $normalizedDefault,
            'default_measurand_values' => $normalizedMeasurandDefaults,
            'select_options' => $this->value_type === 'custom_select' ? $this->select_options : null,
            'procedure_worksheet_step_group_id' => $this->stepGroupId ?: null,
        ];

        if (Schema::hasColumn('procedure_worksheet_steps', 'table_mode')) {
            $stepPayload['table_mode'] = $this->value_type === 'custom_table' ? $this->table_mode : null;
        }

        if (Schema::hasColumn('procedure_worksheet_steps', 'row_driver')) {
            $stepPayload['row_driver'] = $this->value_type === 'custom_table' && $this->table_mode === 'dynamic'
                ? $this->row_driver
                : null;
        }

        if (Schema::hasColumn('procedure_worksheet_steps', 'row_driver_filters')) {
            $stepPayload['row_driver_filters'] = null;
        }

        if (Schema::hasColumn('procedure_worksheet_steps', 'allow_manual_rows')) {
            $stepPayload['allow_manual_rows'] = $this->value_type === 'custom_table' && $this->table_mode === 'dynamic'
                ? $this->allow_manual_rows
                : false;
        }

        ProcedureWorksheetStep::updateOrCreate(
            ['id' => $this->editingStepId],
            $stepPayload
        );

        $this->showModal = false;
        $this->resetForm();
        session()->flash('message', 'Step saved successfully.');
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->stepIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteStep()
    {
        if ($this->stepIdToDelete) {
            $step = ProcedureWorksheetStep::find($this->stepIdToDelete);
            if ($step) {
                $step->delete();
                session()->flash('message', 'Step deleted successfully.');
            }
        }
        $this->showDeleteModal = false;
        $this->stepIdToDelete = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->stepIdToDelete = null;
    }

    public function toggleActive($id)
    {
        $step = ProcedureWorksheetStep::find($id);
        $step->is_active = !$step->is_active;
        $step->save();
    }

    /**
     * @return Collection<int, ReportingUnit>
     */
    protected function searchMeasurands(string $search): Collection
    {
        $query = ReportingUnit::query()->orderBy('name');

        $term = trim($search);
        if ($term !== '') {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $query->where('name', 'ilike', '%' . $term . '%');
            } else {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($term) . '%']);
            }
        }

        return $query->limit(15)->get();
    }

    /**
     * @return Collection<int, Equipment>
     */
    protected function searchEquipments(string $search): Collection
    {
        $query = Equipment::query()->orderBy('name');

        $term = trim($search);
        if ($term !== '') {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'ilike', '%' . $term . '%')
                        ->orWhere('equipment_number', 'ilike', '%' . $term . '%');
                });
            } else {
                $like = '%' . mb_strtolower($term) . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(equipment_number) LIKE ?', [$like]);
                });
            }
        }

        return $query->limit(15)->get();
    }

    public function resetForm()
    {
        $this->editingStepId = null;
        $this->step = '';
        $this->is_active = true;
        $this->is_result_step = false;
        $this->attracts_equipment_logbook = false;
        $this->logbook_equipment_ids = [];
        $this->default_equipment_id = null;
        $this->default_analyst_id = null;
        $this->default_measurand_ids = [];
        $this->value_type = 'text';
        $this->default_value = '';
        $this->default_measurand_values = [];
        $this->select_options = [];
        $this->newSelectOption = '';
        $this->table_mode = 'dynamic';
        $this->row_driver = 'captured_result';
        $this->allow_manual_rows = false;
        $this->stepGroupId = null;
        $this->equipmentSearch = '';
        $this->logbookEquipmentSearch = '';
        $this->analystSearch = '';
        $this->measurandSearch = '';
        $this->methodSearch = '';
        $this->defaultValueEquipmentSearch = '';
        $this->showMethodDropdown = false;
        $this->showDefaultValueEquipmentDropdown = false;
        $this->showMeasurandDropdown = false;
        $this->showLogbookEquipmentDropdown = false;
    }

    public function updatedAttractsEquipmentLogbook($value): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $this->logbook_equipment_ids = [];
            $this->logbookEquipmentSearch = '';
            $this->closeLogbookEquipmentDropdown();
        }
    }

    public function toggleLogbookEquipment(string $id): void
    {
        $id = (string) $id;

        if (in_array($id, $this->logbook_equipment_ids, true)) {
            $this->logbook_equipment_ids = array_values(array_filter(
                $this->logbook_equipment_ids,
                fn ($existingId) => (string) $existingId !== $id
            ));
        } else {
            $this->logbook_equipment_ids[] = $id;
        }

        $this->closeLogbookEquipmentDropdown();
    }

    public function removeLogbookEquipment(string $id): void
    {
        $id = (string) $id;
        $this->logbook_equipment_ids = array_values(array_filter(
            $this->logbook_equipment_ids,
            fn ($existingId) => (string) $existingId !== $id
        ));
    }

    public function selectEquipment($id)
    {
        $this->default_equipment_id = $id;
        $this->showEquipmentDropdown = false;
    }

    public function showImportModalInit()
    {
        $this->importWorksheetSearch = '';
        $this->selectedImportWorksheetId = null;
        $this->importableWorksheets = [];
        $this->importTypeSteps = true;
        $this->importTypeConfig = false;
        $this->importTypeTestKit = false;
        $this->showImportModal = true;
    }

    public function updatedImportWorksheetSearch()
    {
        if (strlen($this->importWorksheetSearch) > 1) {
            $this->importableWorksheets = ProcedureWorksheet::where('name', 'like', '%' . $this->importWorksheetSearch . '%')
                ->where('id', '!=', $this->worksheetId)
                ->limit(10)
                ->get();
        } else {
            $this->importableWorksheets = [];
        }
    }

    public function importData()
    {
        $this->validate([
            'selectedImportWorksheetId' => 'required|exists:procedure_worksheets,id'
        ], [
            'selectedImportWorksheetId.required' => 'Please select a source worksheet to import from.'
        ]);

        $sourceWorksheet = ProcedureWorksheet::find($this->selectedImportWorksheetId);
        if (!$sourceWorksheet) return;

        $importedSomething = false;

        if ($this->importTypeSteps) {
            $stepGroupIdMap = [];
            if (Schema::hasTable('procedure_worksheet_step_groups')) {
                $sourceGroups = ProcedureWorksheetStepGroup::where('procedure_worksheet_id', $this->selectedImportWorksheetId)
                    ->orderBy('order')
                    ->get();
                foreach ($sourceGroups as $group) {
                    $newGroup = ProcedureWorksheetStepGroup::create([
                        'procedure_worksheet_id' => $this->worksheetId,
                        'title' => $group->title,
                        'description' => $group->description,
                        'order' => $group->order,
                    ]);
                    $stepGroupIdMap[$group->id] = $newGroup->id;
                }
            }

            $sourceSteps = $sourceWorksheet->steps()->orderBy('order', 'asc')->get();
            $currentMaxOrder = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->worksheetId)->max('order') ?? 0;
            foreach ($sourceSteps as $step) {
                $currentMaxOrder++;
                $mappedGroupId = $step->procedure_worksheet_step_group_id
                    ? ($stepGroupIdMap[$step->procedure_worksheet_step_group_id] ?? null)
                    : null;
                $newStep = ProcedureWorksheetStep::create([
                    'procedure_worksheet_id' => $this->worksheetId,
                    'step' => $step->step,
                    'is_active' => $step->is_active,
                    'is_result_step' => (bool) $step->is_result_step,
                    'attracts_equipment_logbook' => (bool) $step->attracts_equipment_logbook,
                    'logbook_equipment_ids' => $step->logbook_equipment_ids,
                    'default_equipment_id' => $step->default_equipment_id,
                    'default_analyst_id' => $step->default_analyst_id,
                    'default_measurand_ids' => $step->default_measurand_ids,
                    'value_type' => $step->value_type ?: 'text',
                    'default_value' => $step->default_value,
                    'default_measurand_values' => $step->default_measurand_values,
                    'select_options' => $step->select_options,
                    'table_mode' => $step->table_mode,
                    'row_driver' => $step->row_driver,
                    'row_driver_filters' => $step->row_driver_filters,
                    'allow_manual_rows' => $step->allow_manual_rows,
                    'procedure_worksheet_step_group_id' => $mappedGroupId,
                    'order' => $currentMaxOrder,
                ]);

                if ($step->isCustomTable()) {
                    $this->copyStepTableDefinition($step, $newStep);
                }
            }
            if ($sourceSteps->count() > 0) {
                $importedSomething = true;
            }
        }

        if ($this->importTypeConfig) {
            $configSectionIdMap = [];
            if (Schema::hasTable('procedure_config_field_sections')) {
                $sourceSections = ProcedureConfigFieldSection::where('procedure_worksheet_id', $this->selectedImportWorksheetId)
                    ->orderBy('order')
                    ->get();
                foreach ($sourceSections as $section) {
                    $newSection = ProcedureConfigFieldSection::create([
                        'procedure_worksheet_id' => $this->worksheetId,
                        'title' => $section->title,
                        'description' => $section->description,
                        'order' => $section->order,
                    ]);
                    $configSectionIdMap[$section->id] = $newSection->id;
                }
            }

            $sourceConfigs = ProcedureConfigField::where('procedure_worksheet_id', $this->selectedImportWorksheetId)->orderBy('order', 'asc')->get();
            $currentMaxOrder = ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)->max('order') ?? 0;
            foreach ($sourceConfigs as $config) {
                $currentMaxOrder++;
                $mappedSectionId = $config->procedure_config_field_section_id
                    ? ($configSectionIdMap[$config->procedure_config_field_section_id] ?? null)
                    : null;
                ProcedureConfigField::create([
                    'procedure_worksheet_id' => $this->worksheetId,
                    'label' => $config->label,
                    'field_type' => $config->field_type,
                    'model_tied_to' => $config->model_tied_to,
                    'order' => $currentMaxOrder,
                    'help_text' => $config->help_text,
                    'is_required' => $config->is_required,
                    'field_value_name' => $config->field_value_name,
                    'procedure_config_field_section_id' => $mappedSectionId,
                ]);
            }
            if ($sourceConfigs->count() > 0) {
                $importedSomething = true;
            }
        }

        if ($this->importTypeTestKit) {
            $sourceColumns = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->selectedImportWorksheetId)->orderBy('order', 'asc')->get();
            $currentMaxOrder = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->worksheetId)->max('order') ?? 0;
            foreach ($sourceColumns as $column) {
                $currentMaxOrder++;
                ProcedureTestKitColumn::create([
                    'procedure_worksheet_id' => $this->worksheetId,
                    'label' => $column->label,
                    'key' => $column->key,
                    'type' => $column->type,
                    'order' => $currentMaxOrder,
                    'is_required' => $column->is_required,
                    'help_text' => $column->help_text,
                ]);
            }
            if ($sourceColumns->count() > 0) $importedSomething = true;
        }

        $this->showImportModal = false;
        $this->selectedImportWorksheetId = null;
        $this->loadConfigFields();
        $this->loadConfigFieldSections();
        $this->loadStepGroups();
        $this->loadTestKitColumns();
        $this->resetPage();
        
        $this->toastType = $importedSomething ? 'success' : 'info';
        $this->toastMessage = $importedSomething ? 'Worksheet data imported successfully.' : 'No data was found to import.';
    }

    public function cancelImport()
    {
        $this->showImportModal = false;
        $this->selectedImportWorksheetId = null;
        $this->importWorksheetSearch = '';
    }

    public function selectAnalyst($id)
    {
        $this->default_analyst_id = $id;
        $this->showAnalystDropdown = false;
    }

    public function toggleMeasurand($id): void
    {
        if (in_array($id, $this->default_measurand_ids)) {
            $this->default_measurand_ids = array_diff($this->default_measurand_ids, [$id]);
        } else {
            $this->default_measurand_ids[] = $id;
        }

        $this->closeMeasurandDropdown();
    }
    
    public function removeMeasurand($id)
    {
        $this->default_measurand_ids = array_diff($this->default_measurand_ids, [$id]);
    }

    public function updateStepOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            if ($step = ProcedureWorksheetStep::find($id)) {
                $step->order = $index + 1;
                $step->save();
            }
        }
    }

    // ==================== Configurable Fields Methods ====================

    public function updatedConfigFieldsPlacement(): void
    {
        $this->saveConfigFieldsCaptureSettings();
    }

    public function saveConfigFieldsCaptureSettings(): void
    {
        if (! Schema::hasColumn('procedure_worksheets', 'config_fields_placement')) {
            $this->toastType = 'warning';
            $this->toastMessage = 'Run migrations to enable configurable field placement (config_fields_placement column missing).';

            return;
        }

        $this->validate([
            'config_fields_placement' => 'required|in:top,bottom',
        ]);

        ProcedureWorksheet::where('id', $this->worksheetId)->update([
            'config_fields_placement' => $this->config_fields_placement,
        ]);

        $this->toastType = 'success';
        $this->toastMessage = 'Capture layout saved.';
    }

    public function loadConfigFieldsCaptureSettings(): void
    {
        $worksheet = ProcedureWorksheet::find($this->worksheetId);
        if (! $worksheet) {
            return;
        }

        if (Schema::hasColumn('procedure_worksheets', 'config_fields_placement')) {
            $this->config_fields_placement = $worksheet->config_fields_placement ?? 'top';
        }
    }

    public function loadConfigFields(): void
    {
        $query = ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)
            ->orderBy('order');

        if ($this->configFieldSearch) {
            $query->where(function ($q) {
                $q->where('label', 'like', '%' . $this->configFieldSearch . '%')
                    ->orWhere('field_value_name', 'like', '%' . $this->configFieldSearch . '%')
                    ->orWhere('help_text', 'like', '%' . $this->configFieldSearch . '%');
            });
        }

        $this->configFields = $query->get()->toArray();
        $this->loadConfigFieldsCaptureSettings();
    }

    public function showCreateConfigFieldModalInit(): void
    {
        $this->resetConfigFieldForm();
        $this->configFieldOrder = count($this->configFields) + 1;
        $this->activeTab = 'config';
        $this->loadConfigFieldSections();
        $this->loadConfigFieldsCaptureSettings();
        $this->showCreateConfigFieldModal = true;
    }

    public function showEditConfigFieldModalInit(string $fieldId): void
    {
        $field = ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($fieldId);

        $this->editingConfigField = $field;
        $this->configFieldLabel = $field->label;
        $this->configFieldType = $field->field_type;
        $this->configFieldOrder = $field->order;
        $this->configFieldHelpText = $field->help_text ?? '';
        $this->configFieldIsRequired = $field->is_required;
        $this->configFieldValueName = $field->field_value_name;
        $this->configFieldModelTiedTo = $field->model_tied_to ?? '';
        $this->configFieldSectionId = $field->procedure_config_field_section_id
            ? (string) $field->procedure_config_field_section_id
            : null;
        $this->parseSampleSelectPath($field->field_type === 'sample_select' ? ($field->model_tied_to ?? '') : '');
        $this->parseConfigFieldDatasetModel($field->model_tied_to ?? '');

        $this->activeTab = 'config';
        $this->loadConfigFieldSections();
        $this->loadConfigFieldsCaptureSettings();
        $this->showEditConfigFieldModal = true;
    }

    public function createConfigField(): void
    {
        $this->validate($this->configFieldRules());

        if (! $this->validateConfigFieldDatasetSettings()) {
            return;
        }

        ProcedureConfigField::create([
            'procedure_worksheet_id' => $this->worksheetId,
            'procedure_config_field_section_id' => $this->configFieldSectionId ?: null,
            'label' => $this->configFieldLabel,
            'field_type' => $this->configFieldType,
            'order' => $this->configFieldOrder,
            'help_text' => $this->configFieldHelpText,
            'is_required' => $this->configFieldIsRequired,
            'field_value_name' => $this->configFieldValueName,
            'model_tied_to' => $this->configFieldModelTiedToForSave(),
        ]);

        $this->showCreateConfigFieldModal = false;
        $this->resetConfigFieldForm();
        $this->loadConfigFields();
        $this->toastType = 'success';
        $this->toastMessage = 'Configurable field saved successfully.';
    }

    public function updateConfigField(): void
    {
        $this->validate($this->configFieldRules());

        if (! $this->validateConfigFieldDatasetSettings()) {
            return;
        }

        if (!$this->editingConfigField) {
            return;
        }

        $this->editingConfigField->update([
            'label' => $this->configFieldLabel,
            'field_type' => $this->configFieldType,
            'order' => $this->configFieldOrder,
            'help_text' => $this->configFieldHelpText,
            'is_required' => $this->configFieldIsRequired,
            'field_value_name' => $this->configFieldValueName,
            'model_tied_to' => $this->configFieldModelTiedToForSave(),
            'procedure_config_field_section_id' => $this->configFieldSectionId ?: null,
        ]);

        $this->showEditConfigFieldModal = false;
        $this->resetConfigFieldForm();
        $this->loadConfigFields();
        $this->toastType = 'success';
        $this->toastMessage = 'Configurable field updated successfully.';
    }

    public function showDeleteConfigFieldModal(int $fieldId): void
    {
        $this->deletingConfigField = ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($fieldId);
        $this->showDeleteConfigFieldModalOpen = true;
    }

    public function deleteConfigField(): void
    {
        if ($this->deletingConfigField) {
            $this->deletingConfigField->delete();
        }

        $this->showDeleteConfigFieldModalOpen = false;
        $this->deletingConfigField = null;
        $this->loadConfigFields();
    }

    public function updateConfigFieldOrder(array $fieldIds): void
    {
        ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)
            ->update(['order' => 9999]);

        foreach ($fieldIds as $index => $fieldId) {
            ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)
                ->where('id', $fieldId)
                ->update(['order' => $index + 1]);
        }

        $this->loadConfigFields();
    }

    public function clearConfigFieldSearch(): void
    {
        $this->configFieldSearch = '';
        $this->loadConfigFields();
    }

    protected function resetConfigFieldForm(): void
    {
        $this->configFieldLabel = '';
        $this->configFieldType = 'input';
        $this->configFieldModelTiedTo = '';
        $this->configFieldOrder = 1;
        $this->configFieldHelpText = '';
        $this->configFieldIsRequired = true;
        $this->configFieldValueName = '';
        $this->configFieldSectionId = null;
        $this->configFieldSampleColumn = '';
        $this->configFieldSampleRelationColumn = '';
        $this->configFieldSampleDisplayMode = 'direct';
        $this->configFieldDatasetDisplayMode = 'direct';
        $this->configFieldDatasetSourceColumn = '';
        $this->configFieldDatasetFkColumn = '';
        $this->configFieldDatasetReferencedTable = '';
        $this->configFieldDatasetReferencedDisplayColumn = '';
        $this->editingConfigField = null;
    }

    public function closeConfigFieldModal(): void
    {
        $this->showCreateConfigFieldModal = false;
        $this->showEditConfigFieldModal = false;
        $this->resetConfigFieldForm();
    }

    public function updatedConfigFieldType(): void
    {
        if (! in_array($this->configFieldType, ['dataset', 'dataset_multiselect'], true)) {
            $this->configFieldModelTiedTo = '';
            $this->configFieldDatasetDisplayMode = 'direct';
            $this->configFieldDatasetSourceColumn = '';
            $this->configFieldDatasetFkColumn = '';
            $this->configFieldDatasetReferencedTable = '';
            $this->configFieldDatasetReferencedDisplayColumn = '';
        }

        if ($this->configFieldType !== 'sample_select') {
            $this->configFieldSampleColumn = '';
            $this->configFieldSampleRelationColumn = '';
            $this->configFieldSampleDisplayMode = 'direct';
        }
    }

    public function updatedConfigFieldSampleDisplayMode(): void
    {
        $this->configFieldSampleColumn = '';
        $this->configFieldSampleRelationColumn = '';
    }

    public function updatedConfigFieldSampleColumn(): void
    {
        $this->configFieldSampleRelationColumn = '';
    }

    public function updatedConfigFieldModelTiedTo(): void
    {
        if (! in_array($this->configFieldType, ['dataset', 'dataset_multiselect'], true)) {
            return;
        }

        $this->configFieldDatasetSourceColumn = '';
        $this->configFieldDatasetFkColumn = '';
        $this->configFieldDatasetReferencedTable = '';
        $this->configFieldDatasetReferencedDisplayColumn = '';
    }

    public function updatedConfigFieldDatasetFkColumn(): void
    {
        if (! in_array($this->configFieldType, ['dataset', 'dataset_multiselect'], true)) {
            return;
        }

        if ($this->configFieldDatasetFkColumn === '' || $this->configFieldModelTiedTo === '') {
            $this->configFieldDatasetReferencedTable = '';
            $this->configFieldDatasetReferencedDisplayColumn = '';

            return;
        }

        $schema = app(LogEntryDatabaseSchemaService::class);
        $sourceTable = $this->configFieldDatasetSourceTableName();
        if ($sourceTable === '') {
            return;
        }

        $match = collect($schema->foreignKeyOptions($sourceTable))
            ->firstWhere('column', $this->configFieldDatasetFkColumn);

        if (is_array($match)) {
            $this->configFieldDatasetReferencedTable = (string) ($match['referenced_table'] ?? '');
            $this->configFieldDatasetReferencedDisplayColumn = '';
        }
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getConfigSectionSelectOptionsProperty(): array
    {
        return collect($this->configFieldSections)
            ->map(fn (array $section) => [
                'value' => (string) ($section['id'] ?? ''),
                'label' => (string) ($section['title'] ?? ''),
            ])
            ->filter(fn (array $option) => $option['label'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function getConfigFieldSampleRelationOptionsProperty(): array
    {
        if ($this->configFieldSampleColumn === '') {
            return [];
        }

        return \App\Services\Procedures\ProcedureConfigFieldSampleCatalog::relationDisplayColumnsFor($this->configFieldSampleColumn);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getConfigFieldSampleColumnOptionsProperty(): array
    {
        $isForeignKeyMode = $this->configFieldSampleDisplayMode === 'foreign_key';

        return collect(\App\Services\Procedures\ProcedureConfigFieldSampleCatalog::sampleDetailColumnOptions())
            ->filter(fn (string $label, string $column) => $isForeignKeyMode
                ? \App\Services\Procedures\ProcedureConfigFieldSampleCatalog::isForeignKeyColumn($column)
                : ! \App\Services\Procedures\ProcedureConfigFieldSampleCatalog::isForeignKeyColumn($column))
            ->map(fn (string $label, string $value) => [
                'value' => (string) $value,
                'label' => (string) $label . ' (' . (string) $value . ')',
            ])
            ->prepend(['value' => '', 'label' => 'Select column...'])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string, type: ?string}>
     */
    public function getConfigFieldDatasetSourceColumnOptionsProperty(): array
    {
        $sourceTable = $this->configFieldDatasetSourceTableName();
        if ($sourceTable === '') {
            return [];
        }

        return app(LogEntryDatabaseSchemaService::class)->columnOptions($sourceTable);
    }

    /**
     * @return array<int, array{column: string, referenced_table: string, referenced_column: string, label: string}>
     */
    public function getConfigFieldDatasetForeignKeyOptionsProperty(): array
    {
        $sourceTable = $this->configFieldDatasetSourceTableName();
        if ($sourceTable === '') {
            return [];
        }

        return app(LogEntryDatabaseSchemaService::class)->foreignKeyOptions($sourceTable);
    }

    /**
     * @return array<int, array{value: string, label: string, type: ?string}>
     */
    public function getConfigFieldDatasetReferencedColumnOptionsProperty(): array
    {
        if ($this->configFieldDatasetReferencedTable === '') {
            return [];
        }

        return app(LogEntryDatabaseSchemaService::class)->columnOptions($this->configFieldDatasetReferencedTable);
    }

    protected function configFieldModelTiedToForSave(): ?string
    {
        if (in_array($this->configFieldType, ['dataset', 'dataset_multiselect'], true)) {
            if ($this->configFieldModelTiedTo === '') {
                return null;
            }

            $config = [
                'preset' => $this->configFieldModelTiedTo,
                'display_mode' => $this->configFieldDatasetDisplayMode,
                'source_display_column' => $this->configFieldDatasetDisplayMode === 'direct'
                    ? $this->configFieldDatasetSourceColumn
                    : null,
                'foreign_key_column' => $this->configFieldDatasetDisplayMode === 'foreign_key'
                    ? $this->configFieldDatasetFkColumn
                    : null,
                'referenced_table' => $this->configFieldDatasetDisplayMode === 'foreign_key'
                    ? $this->configFieldDatasetReferencedTable
                    : null,
                'referenced_display_column' => $this->configFieldDatasetDisplayMode === 'foreign_key'
                    ? $this->configFieldDatasetReferencedDisplayColumn
                    : null,
                'referenced_key_column' => $this->configFieldDatasetDisplayMode === 'foreign_key'
                    ? 'id'
                    : null,
            ];

            if (
                $config['display_mode'] === 'direct'
                && blank($config['source_display_column'])
            ) {
                return $this->configFieldModelTiedTo;
            }

            return json_encode($config, JSON_UNESCAPED_SLASHES) ?: $this->configFieldModelTiedTo;
        }

        if ($this->configFieldType === 'sample_select') {
            if ($this->configFieldSampleColumn === '') {
                return null;
            }

            if ($this->configFieldSampleRelationColumn !== '') {
                return $this->configFieldSampleColumn . '.' . $this->configFieldSampleRelationColumn;
            }

            return $this->configFieldSampleColumn;
        }

        return null;
    }

    protected function parseSampleSelectPath(string $path): void
    {
        $this->configFieldSampleColumn = '';
        $this->configFieldSampleRelationColumn = '';
        $this->configFieldSampleDisplayMode = 'direct';

        if ($path === '') {
            return;
        }

        $parts = explode('.', $path, 2);
        $this->configFieldSampleColumn = $parts[0];
        $this->configFieldSampleRelationColumn = $parts[1] ?? '';
        if (\App\Services\Procedures\ProcedureConfigFieldSampleCatalog::isForeignKeyColumn($this->configFieldSampleColumn)) {
            $this->configFieldSampleDisplayMode = 'foreign_key';
        }
    }

    protected function parseConfigFieldDatasetModel(?string $rawModelTiedTo): void
    {
        $this->configFieldDatasetDisplayMode = 'direct';
        $this->configFieldDatasetSourceColumn = '';
        $this->configFieldDatasetFkColumn = '';
        $this->configFieldDatasetReferencedTable = '';
        $this->configFieldDatasetReferencedDisplayColumn = '';

        $raw = (string) ($rawModelTiedTo ?? '');
        if ($raw === '' || ! in_array($this->configFieldType, ['dataset', 'dataset_multiselect'], true)) {
            return;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return;
        }

        $preset = (string) ($decoded['preset'] ?? '');
        if ($preset !== '') {
            $this->configFieldModelTiedTo = $preset;
        }

        $this->configFieldDatasetDisplayMode = (string) ($decoded['display_mode'] ?? 'direct');
        $this->configFieldDatasetSourceColumn = (string) ($decoded['source_display_column'] ?? '');
        $this->configFieldDatasetFkColumn = (string) ($decoded['foreign_key_column'] ?? '');
        $this->configFieldDatasetReferencedTable = (string) ($decoded['referenced_table'] ?? '');
        $this->configFieldDatasetReferencedDisplayColumn = (string) ($decoded['referenced_display_column'] ?? '');
    }

    protected function configFieldDatasetSourceTableName(): string
    {
        return match ((string) $this->configFieldModelTiedTo) {
            'methods' => 'analysis_methods',
            default => (string) $this->configFieldModelTiedTo,
        };
    }

    protected function validateConfigFieldDatasetSettings(): bool
    {
        if (! in_array($this->configFieldType, ['dataset', 'dataset_multiselect'], true)) {
            return true;
        }

        if ($this->configFieldDatasetDisplayMode === 'foreign_key') {
            if ($this->configFieldDatasetFkColumn === '') {
                $this->addError('configFieldDatasetFkColumn', 'Please select a foreign key column.');

                return false;
            }

            if ($this->configFieldDatasetReferencedDisplayColumn === '') {
                $this->addError('configFieldDatasetReferencedDisplayColumn', 'Please select a referenced display column.');

                return false;
            }

            return true;
        }

        if ($this->configFieldDatasetSourceColumn === '') {
            $this->addError('configFieldDatasetSourceColumn', 'Please select a source display column.');

            return false;
        }

        return true;
    }

    public function loadDocumentControl(): void
    {
        $worksheet = ProcedureWorksheet::find($this->worksheetId);
        if ($worksheet) {
            $this->documentControlNo = $worksheet->document_control_no ?? '';
            $this->revision = $worksheet->revision ?? '';
            $this->issueDate = $worksheet->issue_date ? $worksheet->issue_date->format('Y-m-d') : '';
        }
    }

    public function saveDocumentControl(): void
    {
        $this->validate([
            'documentControlNo' => 'nullable|string|max:255',
            'revision' => 'nullable|string|max:255',
            'issueDate' => 'nullable|date',
        ]);

        ProcedureWorksheet::where('id', $this->worksheetId)->update([
            'document_control_no' => $this->documentControlNo ?: null,
            'revision' => $this->revision ?: null,
            'issue_date' => $this->issueDate ?: null,
        ]);

        $this->toastType = 'success';
        $this->toastMessage = 'Document control saved successfully.';
    }

    // ==================== Test Kit Columns Methods ====================

    public function loadTestKitColumns(): void
    {
        $query = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->worksheetId)
            ->orderBy('order');

        if ($this->testKitColumnSearch) {
            $query->where(function ($q) {
                $q->where('label', 'like', '%' . $this->testKitColumnSearch . '%')
                    ->orWhere('key', 'like', '%' . $this->testKitColumnSearch . '%')
                    ->orWhere('help_text', 'like', '%' . $this->testKitColumnSearch . '%');
            });
        }

        $this->testKitColumns = $query->get()->toArray();
    }

    public function showCreateTestKitColumnModalInit(): void
    {
        $this->resetTestKitColumnForm();
        $this->testKitColumnOrder = count($this->testKitColumns) + 1;
        $this->activeTab = 'test_kit';
        $this->showCreateTestKitColumnModal = true;
    }

    public function showEditTestKitColumnModalInit(int $columnId): void
    {
        $column = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($columnId);

        $this->editingTestKitColumn = $column;
        $this->testKitColumnLabel = $column->label;
        $this->testKitColumnKey = $column->key;
        $this->testKitColumnType = $column->type;
        $this->testKitColumnOrder = $column->order;
        $this->testKitColumnIsRequired = $column->is_required;
        $this->testKitColumnHelpText = $column->help_text ?? '';

        $this->activeTab = 'test_kit';
        $this->showEditTestKitColumnModal = true;
    }

    public function createTestKitColumn(): void
    {
        $this->validate($this->testKitColumnRules());

        ProcedureTestKitColumn::create([
            'procedure_worksheet_id' => $this->worksheetId,
            'label' => $this->testKitColumnLabel,
            'key' => $this->testKitColumnKey,
            'type' => $this->testKitColumnType,
            'order' => $this->testKitColumnOrder,
            'is_required' => $this->testKitColumnIsRequired,
            'help_text' => $this->testKitColumnHelpText,
        ]);

        $this->showCreateTestKitColumnModal = false;
        $this->resetTestKitColumnForm();
        $this->loadTestKitColumns();
        $this->toastType = 'success';
        $this->toastMessage = 'Test kit column saved successfully.';
    }

    public function updateTestKitColumn(): void
    {
        $this->validate($this->testKitColumnRules());

        if (!$this->editingTestKitColumn) {
            return;
        }

        $this->editingTestKitColumn->update([
            'label' => $this->testKitColumnLabel,
            'key' => $this->testKitColumnKey,
            'type' => $this->testKitColumnType,
            'order' => $this->testKitColumnOrder,
            'is_required' => $this->testKitColumnIsRequired,
            'help_text' => $this->testKitColumnHelpText,
        ]);

        $this->showEditTestKitColumnModal = false;
        $this->resetTestKitColumnForm();
        $this->loadTestKitColumns();
        $this->toastType = 'success';
        $this->toastMessage = 'Test kit column updated successfully.';
    }

    public function showDeleteTestKitColumnModal(int $columnId): void
    {
        $this->deletingTestKitColumn = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($columnId);
        $this->showDeleteTestKitColumnModalOpen = true;
    }

    public function deleteTestKitColumn(): void
    {
        if ($this->deletingTestKitColumn) {
            $this->deletingTestKitColumn->delete();
        }

        $this->showDeleteTestKitColumnModalOpen = false;
        $this->deletingTestKitColumn = null;
        $this->loadTestKitColumns();
    }

    public function updateTestKitColumnOrder(array $columnIds): void
    {
        ProcedureTestKitColumn::where('procedure_worksheet_id', $this->worksheetId)
            ->update(['order' => 9999]);

        foreach ($columnIds as $index => $columnId) {
            ProcedureTestKitColumn::where('procedure_worksheet_id', $this->worksheetId)
                ->where('id', $columnId)
                ->update(['order' => $index + 1]);
        }

        $this->loadTestKitColumns();
    }

    public function clearTestKitColumnSearch(): void
    {
        $this->testKitColumnSearch = '';
        $this->loadTestKitColumns();
    }

    protected function resetTestKitColumnForm(): void
    {
        $this->testKitColumnLabel = '';
        $this->testKitColumnKey = '';
        $this->testKitColumnType = 'string';
        $this->testKitColumnOrder = 1;
        $this->testKitColumnIsRequired = false;
        $this->testKitColumnHelpText = '';
        $this->editingTestKitColumn = null;
    }

    public function closeTestKitColumnModal(): void
    {
        $this->showCreateTestKitColumnModal = false;
        $this->showEditTestKitColumnModal = false;
        $this->resetTestKitColumnForm();
    }

    // ==================== Config field sections ====================

    public function loadConfigFieldSections(): void
    {
        if (! Schema::hasTable('procedure_config_field_sections')) {
            $this->configFieldSections = [];

            return;
        }

        $this->configFieldSections = ProcedureConfigFieldSection::where('procedure_worksheet_id', $this->worksheetId)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function showCreateConfigSectionModalInit(): void
    {
        $this->resetConfigSectionForm();
        $this->configSectionOrder = count($this->configFieldSections) + 1;
        $this->showCreateConfigSectionModal = true;
    }

    public function showEditConfigSectionModalInit(string $sectionId): void
    {
        $section = ProcedureConfigFieldSection::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($sectionId);
        $this->editingConfigSection = $section;
        $this->configSectionTitle = $section->title;
        $this->configSectionDescription = $section->description ?? '';
        $this->configSectionOrder = $section->order;
        $this->showEditConfigSectionModal = true;
    }

    public function createConfigSection(): void
    {
        $this->validate([
            'configSectionTitle' => 'required|string|max:255',
            'configSectionDescription' => 'nullable|string',
            'configSectionOrder' => 'required|integer|min:1',
        ]);

        ProcedureConfigFieldSection::create([
            'procedure_worksheet_id' => $this->worksheetId,
            'title' => $this->configSectionTitle,
            'description' => $this->configSectionDescription ?: null,
            'order' => $this->configSectionOrder,
        ]);

        $this->showCreateConfigSectionModal = false;
        $this->resetConfigSectionForm();
        $this->loadConfigFieldSections();
        $this->dispatch('config-sections-updated');
        $this->toastType = 'success';
        $this->toastMessage = 'Field section created.';
    }

    public function updateConfigSection(): void
    {
        $this->validate([
            'configSectionTitle' => 'required|string|max:255',
            'configSectionDescription' => 'nullable|string',
            'configSectionOrder' => 'required|integer|min:1',
        ]);

        $this->editingConfigSection?->update([
            'title' => $this->configSectionTitle,
            'description' => $this->configSectionDescription ?: null,
            'order' => $this->configSectionOrder,
        ]);

        $this->showEditConfigSectionModal = false;
        $this->resetConfigSectionForm();
        $this->loadConfigFieldSections();
        $this->toastType = 'success';
        $this->toastMessage = 'Field section updated.';
    }

    public function showDeleteConfigSectionModal(string $sectionId): void
    {
        $this->deletingConfigSection = ProcedureConfigFieldSection::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($sectionId);
        $this->showDeleteConfigSectionModalOpen = true;
    }

    public function deleteConfigSection(): void
    {
        if ($this->deletingConfigSection) {
            ProcedureConfigField::where('procedure_config_field_section_id', $this->deletingConfigSection->id)
                ->update(['procedure_config_field_section_id' => null]);
            $this->deletingConfigSection->delete();
        }

        $this->showDeleteConfigSectionModalOpen = false;
        $this->deletingConfigSection = null;
        $this->loadConfigFieldSections();
        $this->loadConfigFields();
        $this->toastType = 'success';
        $this->toastMessage = 'Field section deleted. Its fields are now ungrouped.';
    }

    public function closeConfigSectionModal(): void
    {
        $this->showCreateConfigSectionModal = false;
        $this->showEditConfigSectionModal = false;
        $this->resetConfigSectionForm();
    }

    protected function resetConfigSectionForm(): void
    {
        $this->configSectionTitle = '';
        $this->configSectionDescription = '';
        $this->configSectionOrder = 1;
        $this->editingConfigSection = null;
    }

    // ==================== Step groups ====================

    public function loadStepGroups(): void
    {
        if (! Schema::hasTable('procedure_worksheet_step_groups')) {
            $this->stepGroups = [];

            return;
        }

        $this->stepGroups = ProcedureWorksheetStepGroup::where('procedure_worksheet_id', $this->worksheetId)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function showCreateStepGroupModalInit(): void
    {
        $this->resetStepGroupForm();
        $this->stepGroupOrder = count($this->stepGroups) + 1;
        $this->showCreateStepGroupModal = true;
    }

    public function showEditStepGroupModalInit(string $groupId): void
    {
        $group = ProcedureWorksheetStepGroup::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($groupId);
        $this->editingStepGroup = $group;
        $this->stepGroupTitle = $group->title;
        $this->stepGroupDescription = $group->description ?? '';
        $this->stepGroupOrder = $group->order;
        $this->showEditStepGroupModal = true;
    }

    public function createStepGroup(): void
    {
        $this->validate([
            'stepGroupTitle' => 'required|string|max:255',
            'stepGroupDescription' => 'nullable|string',
            'stepGroupOrder' => 'required|integer|min:1',
        ]);

        ProcedureWorksheetStepGroup::create([
            'procedure_worksheet_id' => $this->worksheetId,
            'title' => $this->stepGroupTitle,
            'description' => $this->stepGroupDescription ?: null,
            'order' => $this->stepGroupOrder,
        ]);

        $this->showCreateStepGroupModal = false;
        $this->resetStepGroupForm();
        $this->loadStepGroups();
        $this->toastType = 'success';
        $this->toastMessage = 'Step group created.';
    }

    public function updateStepGroup(): void
    {
        $this->validate([
            'stepGroupTitle' => 'required|string|max:255',
            'stepGroupDescription' => 'nullable|string',
            'stepGroupOrder' => 'required|integer|min:1',
        ]);

        $this->editingStepGroup?->update([
            'title' => $this->stepGroupTitle,
            'description' => $this->stepGroupDescription ?: null,
            'order' => $this->stepGroupOrder,
        ]);

        $this->showEditStepGroupModal = false;
        $this->resetStepGroupForm();
        $this->loadStepGroups();
        $this->toastType = 'success';
        $this->toastMessage = 'Step group updated.';
    }

    public function showDeleteStepGroupModal(string $groupId): void
    {
        $this->deletingStepGroup = ProcedureWorksheetStepGroup::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($groupId);
        $this->showDeleteStepGroupModalOpen = true;
    }

    public function deleteStepGroup(): void
    {
        if ($this->deletingStepGroup) {
            ProcedureWorksheetStep::where('procedure_worksheet_step_group_id', $this->deletingStepGroup->id)
                ->update(['procedure_worksheet_step_group_id' => null]);
            $this->deletingStepGroup->delete();
        }

        $this->showDeleteStepGroupModalOpen = false;
        $this->deletingStepGroup = null;
        $this->loadStepGroups();
        $this->toastType = 'success';
        $this->toastMessage = 'Step group deleted. Its steps are now ungrouped.';
    }

    public function closeStepGroupModal(): void
    {
        $this->showCreateStepGroupModal = false;
        $this->showEditStepGroupModal = false;
        $this->resetStepGroupForm();
    }

    protected function resetStepGroupForm(): void
    {
        $this->stepGroupTitle = '';
        $this->stepGroupDescription = '';
        $this->stepGroupOrder = 1;
        $this->editingStepGroup = null;
    }

    public function toggleCustomTableStepExpanded(string $stepId): void
    {
        if (in_array($stepId, $this->expandedCustomTableStepIds, true)) {
            $this->expandedCustomTableStepIds = array_values(array_filter(
                $this->expandedCustomTableStepIds,
                fn (string $id): bool => $id !== $stepId
            ));
        } else {
            $this->expandedCustomTableStepIds[] = $stepId;
        }
    }

    public function isCustomTableStepExpanded(string $stepId): bool
    {
        return in_array($stepId, $this->expandedCustomTableStepIds, true);
    }

    public function openConfigureTableModal(string $stepId): void
    {
        if (! $this->customStepTablesAvailable()) {
            $this->toastType = 'warning';
            $this->toastMessage = 'Run migrations to enable custom step tables.';

            return;
        }

        $step = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->worksheetId)->findOrFail($stepId);
        if (! $step->isCustomTable()) {
            return;
        }

        $this->configuringStepId = $stepId;
        $this->configureTableWizardStep = 1;
        $this->loadStepTableColumns();
        $this->loadStepStaticRows();
        $this->showConfigureTableModal = true;
    }

    public function closeConfigureTableModal(): void
    {
        $this->showConfigureTableModal = false;
        $this->configuringStepId = null;
        $this->configureTableWizardStep = 1;
        $this->resetStepColumnForm();
    }

    public function configureTableWizardGoTo(int $step): void
    {
        if ($step < 1 || $step > 3) {
            return;
        }

        if ($step >= 2 && count($this->stepTableColumns) === 0) {
            $this->toastType = 'warning';
            $this->toastMessage = 'Add at least one column before continuing.';

            return;
        }

        if ($step >= 2) {
            $this->loadStepStaticRows();
            $this->closeStepColumnModal();
        }

        $this->configureTableWizardStep = $step;
    }

    public function configureTableWizardNext(): void
    {
        if ($this->configureTableWizardStep >= 3) {
            $this->closeConfigureTableModal();

            return;
        }

        $this->configureTableWizardGoTo($this->configureTableWizardStep + 1);
    }

    public function configureTableWizardBack(): void
    {
        if ($this->configureTableWizardStep <= 1) {
            return;
        }

        $this->configureTableWizardGoTo($this->configureTableWizardStep - 1);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public function stepTableColumnIsStaticConfigurable(array $column): bool
    {
        $config = is_array($column['dataset_config'] ?? null) ? $column['dataset_config'] : [];

        return ($config['column_mode'] ?? '') === 'fixed';
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public function stepTableColumnDisplayTypeLabel(array $column): string
    {
        if ($this->stepTableColumnIsStaticConfigurable($column)) {
            return 'Static';
        }

        $columnType = (string) ($column['column_type'] ?? 'input');
        if ($columnType === 'derived') {
            return 'Derived';
        }
        if ($columnType === 'dataset') {
            return 'Dataset';
        }

        $config = is_array($column['dataset_config'] ?? null) ? $column['dataset_config'] : [];
        $inputKey = (string) ($config['input_data_type'] ?? $column['input_data_type'] ?? 'string');

        $labels = [
            'string' => 'Text',
            'number' => 'Number',
            'date' => 'Date',
            'time' => 'Time',
            'datetime' => 'Date & time',
            'boolean' => 'Yes/No',
            'textarea' => 'Long text',
            'radio' => 'Radio',
            'checkbox' => 'Checkbox',
            'select' => 'Dropdown',
        ];

        return $labels[$inputKey] ?? ucfirst($inputKey);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public function stepTableColumnDisplayTypeIcon(array $column): string
    {
        if ($this->stepTableColumnIsStaticConfigurable($column)) {
            return 'mdi-table-edit';
        }

        $columnType = (string) ($column['column_type'] ?? 'input');
        if ($columnType === 'derived') {
            return 'mdi-function';
        }
        if ($columnType === 'dataset') {
            return 'mdi-database-outline';
        }

        $config = is_array($column['dataset_config'] ?? null) ? $column['dataset_config'] : [];
        $inputKey = (string) ($config['input_data_type'] ?? $column['input_data_type'] ?? 'string');

        return match ($inputKey) {
            'number' => 'mdi-numeric',
            'date', 'datetime' => 'mdi-calendar',
            'time' => 'mdi-clock-outline',
            'boolean' => 'mdi-toggle-switch-outline',
            'textarea' => 'mdi-text-box-outline',
            'radio' => 'mdi-radiobox-marked',
            'checkbox' => 'mdi-checkbox-multiple-marked-outline',
            'select' => 'mdi-form-dropdown',
            default => 'mdi-form-textbox-outline',
        };
    }

    public function loadStepTableColumns(): void
    {
        if (! $this->configuringStepId || ! $this->customStepTablesAvailable()) {
            $this->stepTableColumns = [];

            return;
        }

        $this->stepTableColumns = ProcedureStepTableColumn::where('procedure_worksheet_step_id', $this->configuringStepId)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function loadStepStaticRows(): void
    {
        if (! $this->configuringStepId || ! $this->customStepTablesAvailable()) {
            $this->stepStaticRows = [];
            $this->stepStaticCellValues = [];

            return;
        }

        $rows = ProcedureStepTableStaticRow::where('procedure_worksheet_step_id', $this->configuringStepId)
            ->with('cells')
            ->orderBy('order')
            ->get();

        $this->stepStaticRows = $rows->map(fn ($r) => [
            'id' => $r->id,
            'order' => $r->order,
            'label' => $r->label,
        ])->all();

        $this->stepStaticCellValues = [];
        foreach ($rows as $row) {
            foreach ($row->cells as $cell) {
                $this->stepStaticCellValues[$row->id][$cell->column_id] = $cell->default_value ?? '';
            }
        }
    }

    public function showCreateStepColumnModalInit(): void
    {
        $this->resetStepColumnForm();
        $this->stepColumnOrder = count($this->stepTableColumns) + 1;
        $this->showCreateStepColumnModal = true;
        $this->showEditStepColumnModal = false;
    }

    public function showEditStepColumnModalInit(string $columnId): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $column = ProcedureStepTableColumn::where('procedure_worksheet_step_id', $this->configuringStepId)
            ->findOrFail($columnId);

        $this->editingStepColumn = $column;
        $this->stepColumnLabel = $column->label;
        $this->stepColumnKey = $column->key;
        $this->stepColumnType = $column->column_type;
        $this->stepColumnInputDataType = $column->input_data_type;
        $this->stepColumnExpression = $column->expression ?? '';
        $this->stepColumnModelTiedTo = $column->model_tied_to ?? '';
        $this->stepColumnOrder = $column->order;
        $this->stepColumnIsRequired = $column->is_required;
        $this->stepColumnHelpText = $column->help_text ?? '';
        $config = is_array($column->dataset_config) ? $column->dataset_config : [];
        if (($column->column_type ?? '') === 'input' && (($config['column_mode'] ?? '') === 'fixed')) {
            $this->stepColumnType = 'static';
        } elseif (($column->column_type ?? '') === 'input' && (($config['column_mode'] ?? '') === 'static')) {
            $this->stepColumnType = 'input';
            $this->stepColumnInputDataType = (string) ($config['input_data_type'] ?? 'radio');
            $this->stepColumnStaticOptions = implode(
                PHP_EOL,
                array_map('strval', is_array($config['static_options'] ?? null) ? $config['static_options'] : [])
            );
        } elseif (($column->column_type ?? '') === 'input' && (($config['column_mode'] ?? '') === 'choices')) {
            $this->stepColumnType = 'input';
            $this->stepColumnInputDataType = (string) ($config['input_data_type'] ?? 'radio');
            $this->stepColumnStaticOptions = implode(
                PHP_EOL,
                array_map('strval', is_array($config['static_options'] ?? null) ? $config['static_options'] : [])
            );
        }
        $this->showCreateStepColumnModal = false;
        $this->showEditStepColumnModal = true;
    }

    public function createStepColumn(): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $this->validate($this->stepColumnRules());
        $this->validateStepColumnExpression();

        [$storageColumnType, $storageInputType, $datasetConfig] = $this->stepColumnStoragePayload();
        if ($this->getErrorBag()->has('stepColumnStaticOptions')) {
            return;
        }

        ProcedureStepTableColumn::create([
            'procedure_worksheet_step_id' => $this->configuringStepId,
            'label' => $this->stepColumnLabel,
            'key' => $this->stepColumnKey,
            'column_type' => $storageColumnType,
            'input_data_type' => $storageInputType,
            'expression' => $this->stepColumnType === 'derived' ? $this->stepColumnExpression : null,
            'model_tied_to' => $this->stepColumnType === 'dataset' ? $this->stepColumnModelTiedTo : null,
            'dataset_config' => $datasetConfig,
            'order' => $this->stepColumnOrder,
            'is_required' => $this->stepColumnIsRequired,
            'help_text' => $this->stepColumnHelpText ?: null,
        ]);

        $this->showCreateStepColumnModal = false;
        $this->resetStepColumnForm();
        $this->loadStepTableColumns();
        $this->toastType = 'success';
        $this->toastMessage = 'Table column saved.';
    }

    public function updateStepColumn(): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $this->validate($this->stepColumnRules());
        $this->validateStepColumnExpression();

        if (! $this->editingStepColumn) {
            return;
        }

        [$storageColumnType, $storageInputType, $datasetConfig] = $this->stepColumnStoragePayload();
        if ($this->getErrorBag()->has('stepColumnStaticOptions')) {
            return;
        }

        $this->editingStepColumn->update([
            'label' => $this->stepColumnLabel,
            'key' => $this->stepColumnKey,
            'column_type' => $storageColumnType,
            'input_data_type' => $storageInputType,
            'expression' => $this->stepColumnType === 'derived' ? $this->stepColumnExpression : null,
            'model_tied_to' => $this->stepColumnType === 'dataset' ? $this->stepColumnModelTiedTo : null,
            'dataset_config' => $datasetConfig,
            'order' => $this->stepColumnOrder,
            'is_required' => $this->stepColumnIsRequired,
            'help_text' => $this->stepColumnHelpText ?: null,
        ]);

        $this->showEditStepColumnModal = false;
        $this->resetStepColumnForm();
        $this->loadStepTableColumns();
        $this->toastType = 'success';
        $this->toastMessage = 'Table column updated.';
    }

    public function deleteStepColumn(string $columnId): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        ProcedureStepTableColumn::where('procedure_worksheet_step_id', $this->configuringStepId)
            ->where('id', $columnId)
            ->delete();

        $this->loadStepTableColumns();
        $this->loadStepStaticRows();
        $this->toastType = 'success';
        $this->toastMessage = 'Table column deleted.';
    }

    public function addStaticRow(): void
    {
        if (! $this->configuringStepId || ! $this->customStepTablesAvailable()) {
            return;
        }

        $maxOrder = (int) ProcedureStepTableStaticRow::where('procedure_worksheet_step_id', $this->configuringStepId)->max('order');
        $row = ProcedureStepTableStaticRow::create([
            'procedure_worksheet_step_id' => $this->configuringStepId,
            'order' => $maxOrder + 1,
            'label' => trim($this->newStaticRowLabel) !== '' ? trim($this->newStaticRowLabel) : 'Row '.($maxOrder + 1),
        ]);

        foreach (ProcedureStepTableColumn::where('procedure_worksheet_step_id', $this->configuringStepId)->get() as $column) {
            ProcedureStepTableStaticCell::create([
                'static_row_id' => $row->id,
                'column_id' => $column->id,
                'default_value' => null,
            ]);
        }

        $this->loadStepStaticRows();
        $this->closeInlineStaticRowForm();
    }

    public function showInlineStaticRowFormInit(): void
    {
        $this->newStaticRowLabel = '';
        $this->showInlineStaticRowForm = true;
    }

    public function closeInlineStaticRowForm(): void
    {
        $this->newStaticRowLabel = '';
        $this->showInlineStaticRowForm = false;
    }

    public function saveStaticRowLabel(string $rowId, ?string $label): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        ProcedureStepTableStaticRow::where('id', $rowId)
            ->where('procedure_worksheet_step_id', $this->configuringStepId)
            ->update(['label' => $label !== null ? trim($label) : null]);

        $this->loadStepStaticRows();
    }

    public function removeStaticRow(string $rowId): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        ProcedureStepTableStaticCell::where('static_row_id', $rowId)->delete();
        ProcedureStepTableStaticRow::where('id', $rowId)
            ->where('procedure_worksheet_step_id', $this->configuringStepId)
            ->delete();

        $this->loadStepStaticRows();
    }

    public function saveStaticCell(string $rowId, string $columnId, ?string $value): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        ProcedureStepTableStaticCell::updateOrCreate(
            [
                'static_row_id' => $rowId,
                'column_id' => $columnId,
            ],
            ['default_value' => $value],
        );
    }

    public function updatedStepColumnLabel(): void
    {
        if ($this->stepColumnKey === '' && $this->stepColumnLabel !== '') {
            $this->stepColumnKey = Str::snake(Str::ascii($this->stepColumnLabel));
        }
    }

    public function updatedStepColumnType(): void
    {
        if ($this->stepColumnType !== 'dataset') {
            $this->stepColumnModelTiedTo = '';
        }
        if ($this->stepColumnType !== 'derived') {
            $this->stepColumnExpression = '';
        }
        if ($this->stepColumnType !== 'static') {
            $this->stepColumnChoiceControl = 'radio';
            if (! in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true)) {
                $this->stepColumnStaticOptions = '';
            }
        }
    }

    public function updatedStepColumnInputDataType(): void
    {
        if (! in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true) && $this->stepColumnType !== 'static') {
            $this->stepColumnStaticOptions = '';
        }
    }

    public function validateStepColumnExpressionPreview(): void
    {
        $this->stepColumnValidationMessage = null;
        if ($this->stepColumnType !== 'derived' || $this->stepColumnExpression === '') {
            return;
        }

        $priorKeys = collect($this->stepTableColumns)
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->stepColumnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->stepColumnExpression, $priorKeys);
        $this->stepColumnValidationMessage = $result['valid']
            ? 'Expression is valid.'
            : $result['message'];
    }

    public function closeStepColumnModal(): void
    {
        $this->showCreateStepColumnModal = false;
        $this->showEditStepColumnModal = false;
        $this->resetStepColumnForm();
    }

    protected function validateStepColumnExpression(): void
    {
        if ($this->stepColumnType !== 'derived' || $this->stepColumnExpression === '') {
            return;
        }

        $priorKeys = collect($this->stepTableColumns)
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->stepColumnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->stepColumnExpression, $priorKeys);
        if (! $result['valid']) {
            $this->addError('stepColumnExpression', $result['message']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function stepColumnRules(): array
    {
        return [
            'stepColumnLabel' => 'required|string|max:255',
            'stepColumnKey' => 'required|string|max:255|regex:/^[a-z][a-z0-9_]*$/',
            'stepColumnType' => 'required|in:input,derived,dataset,static',
            'stepColumnInputDataType' => 'required_if:stepColumnType,input|in:string,number,date,time,datetime,boolean,textarea,radio,checkbox,select',
            'stepColumnExpression' => 'nullable|required_if:stepColumnType,derived|string',
            'stepColumnModelTiedTo' => 'nullable|required_if:stepColumnType,dataset|string',
            'stepColumnChoiceControl' => 'nullable|in:radio,checkbox,select',
            'stepColumnStaticOptions' => 'nullable|string',
            'stepColumnOrder' => 'required|integer|min:1',
            'stepColumnIsRequired' => 'boolean',
            'stepColumnHelpText' => 'nullable|string',
        ];
    }

    protected function resetStepColumnForm(): void
    {
        $this->editingStepColumn = null;
        $this->stepColumnLabel = '';
        $this->stepColumnKey = '';
        $this->stepColumnType = 'input';
        $this->stepColumnInputDataType = 'string';
        $this->stepColumnExpression = '';
        $this->stepColumnModelTiedTo = '';
        $this->stepColumnOrder = 1;
        $this->stepColumnIsRequired = false;
        $this->stepColumnHelpText = '';
        $this->stepColumnChoiceControl = 'radio';
        $this->stepColumnStaticOptions = '';
        $this->stepColumnValidationMessage = null;
        $this->showInlineStaticRowForm = false;
        $this->newStaticRowLabel = '';
    }

    /**
     * @return array{0: string, 1: string, 2: array<string, mixed>|null}
     */
    protected function stepColumnStoragePayload(): array
    {
        if ($this->stepColumnType === 'static') {
            return [
                'input',
                'string',
                [
                    'column_mode' => 'fixed',
                ],
            ];
        }

        $inputType = $this->stepColumnType === 'input'
            ? (in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true) ? 'string' : $this->stepColumnInputDataType)
            : 'string';

        $datasetConfig = null;
        if ($this->stepColumnType === 'input' && in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true)) {
            $options = collect(preg_split('/\r\n|\r|\n/', $this->stepColumnStaticOptions) ?: [])
                ->map(fn ($option) => trim((string) $option))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($options) === 0) {
                $this->addError('stepColumnStaticOptions', 'Add at least one option for this input type.');

                return ['input', 'string', null];
            }

            $datasetConfig = [
                'column_mode' => 'choices',
                'choice_control' => $this->stepColumnInputDataType,
                'static_options' => $options,
                'input_data_type' => $this->stepColumnInputDataType,
            ];
        }

        return [$this->stepColumnType, $inputType, $datasetConfig];
    }

    protected function copyStepTableDefinition(ProcedureWorksheetStep $source, ProcedureWorksheetStep $target): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $columnIdMap = [];
        foreach ($source->tableColumns as $column) {
            $newColumn = ProcedureStepTableColumn::create([
                'procedure_worksheet_step_id' => $target->id,
                'label' => $column->label,
                'key' => $column->key,
                'column_type' => $column->column_type,
                'input_data_type' => $column->input_data_type,
                'expression' => $column->expression,
                'model_tied_to' => $column->model_tied_to,
                'dataset_config' => $column->dataset_config,
                'order' => $column->order,
                'is_required' => $column->is_required,
                'help_text' => $column->help_text,
            ]);
            $columnIdMap[$column->id] = $newColumn->id;
        }

        foreach ($source->staticRows as $staticRow) {
            $newRow = ProcedureStepTableStaticRow::create([
                'procedure_worksheet_step_id' => $target->id,
                'order' => $staticRow->order,
                'label' => $staticRow->label,
            ]);

            foreach ($staticRow->cells as $cell) {
                $newColumnId = $columnIdMap[$cell->column_id] ?? null;
                if ($newColumnId) {
                    ProcedureStepTableStaticCell::create([
                        'static_row_id' => $newRow->id,
                        'column_id' => $newColumnId,
                        'default_value' => $cell->default_value,
                    ]);
                }
            }
        }
    }

    protected function customStepTablesAvailable(): bool
    {
        return Schema::hasTable('procedure_step_table_columns')
            && Schema::hasTable('procedure_step_table_static_rows')
            && Schema::hasTable('procedure_step_table_static_cells');
    }
}
