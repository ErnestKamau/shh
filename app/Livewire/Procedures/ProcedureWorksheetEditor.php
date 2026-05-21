<?php

namespace App\Livewire\Procedures;

use Livewire\Component;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Procedures\ProcedureConfigField;
use App\Models\Procedures\ProcedureTestKitColumn;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use Carbon\Carbon;
use App\ReportingUnit;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        'value_type' => 'required|in:text,number,time,datetime,date,method_select,equipment_select,custom_select',
        'select_options' => 'nullable|array',
        'select_options.*' => 'nullable|string|max:255',
        'default_value' => 'nullable|string|max:255',
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
            'number', 'text', 'method_select', 'equipment_select', 'custom_select' => $value,
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
            'configFieldType' => 'required|in:input,datetime,date,number,checkbox,textarea,dataset,dataset_multiselect',
            'configFieldValueName' => 'required|string|max:255',
            'configFieldHelpText' => 'nullable|string',
            'configFieldIsRequired' => 'boolean',
            'configFieldModelTiedTo' => 'nullable|required_if:configFieldType,dataset|required_if:configFieldType,dataset_multiselect|in:users,sample_details,sample_types,methods,captured_results,report_formats',
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
        $this->loadTestKitColumns();
        $this->loadDocumentControl();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
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

        $steps = $query->orderBy('order', 'asc')->paginate($this->perPage);

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
        ]);
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

        ProcedureWorksheetStep::updateOrCreate(
            ['id' => $this->editingStepId],
            [
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
            ]
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
            $sourceSteps = $sourceWorksheet->steps()->orderBy('order', 'asc')->get();
            $currentMaxOrder = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->worksheetId)->max('order') ?? 0;
            foreach ($sourceSteps as $step) {
                $currentMaxOrder++;
                ProcedureWorksheetStep::create([
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
                    'order' => $currentMaxOrder,
                ]);
            }
            if ($sourceSteps->count() > 0) $importedSomething = true;
        }

        if ($this->importTypeConfig) {
            $sourceConfigs = ProcedureConfigField::where('procedure_worksheet_id', $this->selectedImportWorksheetId)->orderBy('order', 'asc')->get();
            $currentMaxOrder = ProcedureConfigField::where('procedure_worksheet_id', $this->worksheetId)->max('order') ?? 0;
            foreach ($sourceConfigs as $config) {
                $currentMaxOrder++;
                ProcedureConfigField::create([
                    'procedure_worksheet_id' => $this->worksheetId,
                    'label' => $config->label,
                    'field_type' => $config->field_type,
                    'model_tied_to' => $config->model_tied_to,
                    'order' => $currentMaxOrder,
                    'help_text' => $config->help_text,
                    'is_required' => $config->is_required,
                    'field_value_name' => $config->field_value_name,
                ]);
            }
            if ($sourceConfigs->count() > 0) $importedSomething = true;
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

    public function toggleMeasurand($id)
    {
        if (in_array($id, $this->default_measurand_ids)) {
            $this->default_measurand_ids = array_diff($this->default_measurand_ids, [$id]);
        } else {
            $this->default_measurand_ids[] = $id;
        }
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
    }

    public function showCreateConfigFieldModalInit(): void
    {
        $this->resetConfigFieldForm();
        $this->configFieldOrder = count($this->configFields) + 1;
        $this->activeTab = 'config';
        $this->showCreateConfigFieldModal = true;
    }

    public function showEditConfigFieldModalInit(int $fieldId): void
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

        $this->activeTab = 'config';
        $this->showEditConfigFieldModal = true;
    }

    public function createConfigField(): void
    {
        $this->validate($this->configFieldRules());

        ProcedureConfigField::create([
            'procedure_worksheet_id' => $this->worksheetId,
            'label' => $this->configFieldLabel,
            'field_type' => $this->configFieldType,
            'order' => $this->configFieldOrder,
            'help_text' => $this->configFieldHelpText,
            'is_required' => $this->configFieldIsRequired,
            'field_value_name' => $this->configFieldValueName,
            'model_tied_to' => in_array($this->configFieldType, ['dataset', 'dataset_multiselect']) ? $this->configFieldModelTiedTo : null,
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
            'model_tied_to' => in_array($this->configFieldType, ['dataset', 'dataset_multiselect']) ? $this->configFieldModelTiedTo : null,
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
        if ($this->configFieldType !== 'dataset' && $this->configFieldType !== 'dataset_multiselect') {
            $this->configFieldModelTiedTo = '';
        }
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
}
