<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\User;
use App\InventoryDepartment;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use App\ReportingUnit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EquipmentImport;

class EquipmentManager extends Component
{
    use WithPagination, WithFileUploads;

    public bool $embedded = false;

    // Search and Filters
    public $search = '';
    public $statusFilter = '';
    // public $activeTab = 'active'; // Removed legacy disposal tab
    
    // Pagination
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Modal States
    public $showEquipmentModal = false;
    public $showBulkUploadModal = false;
    public $editingEquipment = null;
    public int $currentStep = 1;
    public int $totalSteps = 4;

    // Bulk Upload
    public $bulkFile = null;

    // Equipment Form
    public $equipmentForm = [
        'name' => '',
        'equipment_number' => '',
        'description' => '',
        'make' => '',
        'model' => '',
        'serial_number' => '',
        'barcode_number' => '',
        'manufacturer' => '',
        'status' => 'Active',
        'condition' => '',
        'assigned_department' => null,
        'assigned_employee_id' => null,
        'warranty_date' => '',
        'date_purchased' => '',
        'maintainance_days' => null,
        'maintainance_notification_in_days' => null,
        'preventive_maintainance_period' => null,
        'preventive_maintainance_notification_days' => null,
        'calibration_days' => null,
        'calibration_notification_in_days' => null,
        'asset_type_id' => null,
        'asset_location_id' => null,
        'active' => true,
        'requires_daily_log' => false,
        'daily_log_value_type' => '',
        'daily_log_nature' => '',
        'daily_log_tolerance' => null,
        'daily_log_expected_value' => '',
        'daily_log_expected_min' => null,
        'daily_log_expected_max' => null,
        'daily_log_reporting_unit' => '',
        'daily_log_frequency' => 1,
        'daily_log_frequency_labels' => [],
        'daily_log_monitored_by_another_equipment' => false,
        'daily_log_monitored_equipment_id' => null,
    ];



    // File Upload
    public $photo;

    // Supporting Data
    public $employees = [];
    public $statuses = [];
    public $departments = [];
    public $assetTypes = [];
    public $assetLocations = [];
    public $reportingUnits = [];

    // Searchable Select Properties
    public $employeeSearch = '';
    public $departmentSearch = '';
    public $assetTypeSearch = '';
    public $assetLocationSearch = '';
    public $reportingUnitSearch = '';
    public $monitoredEquipmentSearch = '';

    public $showEmployeeDropdown = false;
    public $showDepartmentDropdown = false;
    public $showAssetTypeDropdown = false;
    public $showAssetLocationDropdown = false;
    public $showReportingUnitDropdown = false;
    public $showMonitoredEquipmentDropdown = false;

    public $selectedEmployeeName = '';
    public $selectedDepartmentName = '';
    public $selectedAssetTypeName = '';
    public $selectedAssetLocationName = '';
    public $selectedReportingUnitName = '';
    public $selectedMonitoredEquipmentLabel = '';

    public $filteredEmployees = [];
    public $filteredDepartments = [];
    public $filteredAssetTypes = [];
    public $filteredAssetLocations = [];
    public $filteredReportingUnits = [];
    public $filteredMonitoredEquipments = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        $rules = [
            'equipmentForm.name' => 'required|string|max:255',
            'equipmentForm.equipment_number' => 'required|string|max:255',
            'equipmentForm.description' => 'required|string',
            'equipmentForm.make' => 'required|string|max:255',
            'equipmentForm.model' => 'required|string|max:255',
            'equipmentForm.serial_number' => 'nullable|string|max:255',
            'equipmentForm.barcode_number' => 'nullable|string|max:255',
            'equipmentForm.manufacturer' => 'nullable|string|max:255',
            'equipmentForm.status' => 'required|string',
            'equipmentForm.condition' => 'required|string|max:255',
            'equipmentForm.assigned_department' => 'required|string',
            'equipmentForm.assigned_employee_id' => 'nullable|string',
            'equipmentForm.warranty_date' => 'required|date',
            'equipmentForm.date_purchased' => 'nullable|date',
            'equipmentForm.maintainance_days' => 'required|integer|min:0',
            'equipmentForm.maintainance_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.calibration_days' => 'required|integer|min:0',
            'equipmentForm.calibration_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.asset_type_id' => 'nullable|string',
            'equipmentForm.asset_location_id' => 'nullable|string',
            'equipmentForm.active' => 'boolean',
            'photo' => 'nullable|image|max:10240', // 10MB max
        ];

        if (!empty($this->equipmentForm['requires_daily_log'])) {
            $type   = $this->equipmentForm['daily_log_value_type'] ?? '';
            $nature = $this->equipmentForm['daily_log_nature'] ?? '';

            $rules['equipmentForm.daily_log_value_type'] = 'required|in:constant,range';
            $rules['equipmentForm.daily_log_nature']     = 'required|in:qualitative,quantitative';

            if ($type === 'constant') {
                $rules['equipmentForm.daily_log_expected_value'] = 'required|string|max:255';
                if ($nature === 'quantitative') {
                    $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
                }
            }

            if ($type === 'range') {
                $rules['equipmentForm.daily_log_expected_min'] = 'required|numeric';
                $rules['equipmentForm.daily_log_expected_max'] = 'required|numeric|gte:equipmentForm.daily_log_expected_min';
                $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
            }

            $rules['equipmentForm.daily_log_frequency'] = 'required|integer|min:1|max:6';
            $rules['equipmentForm.daily_log_reporting_unit'] = 'nullable|string|max:255';
            $rules['equipmentForm.daily_log_monitored_by_another_equipment'] = 'boolean';
            if (!empty($this->equipmentForm['daily_log_monitored_by_another_equipment'])) {
                $rules['equipmentForm.daily_log_monitored_equipment_id'] = 'required|string';
            }
        }

        return $rules;
    }

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;
        $this->loadInitialData();
    }

    public function loadInitialData(): void
    {
        $this->employees = getUsers();
        $this->statuses = getStatus();
        $this->departments = InventoryDepartment::where('module', 'organizational')->get();
        $this->assetTypes = AssetType::where('is_active', 1)->get();
        $this->assetLocations = AssetLocation::where('is_active', 1)->get();
        $this->reportingUnits = ReportingUnit::orderBy('name')->get();
        $this->filteredReportingUnits = $this->reportingUnits;
    }

    public function getEquipmentProperty()
    {
        $query = Equipment::where('company_id', getUserCompany())
            ->orderBy('name', 'asc');

        // Filter by disposal status - REMOVED LEGACY LOGIC
        // if ($this->activeTab === 'active') {
        //     $query->where('is_disposal', 0);
        // } else {
        //     $query->where('is_disposal', 1);
        // }
        // Ensure we only show active equipment (not disposed) unless we want to show everything?
        // For now, let's just show everything that is active in terms of 'active' flag if desired,
        // but the requirement says "turn all disposed equipments to not disposed", so everything is effectively active.
        $query->where('is_disposal', 0); // Force to non-disposed as we reset them


        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('equipment_number', 'like', '%' . $this->search . '%')
                  ->orWhere('make', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }



    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    #[On('equipment-open-create-modal')]
    public function showCreateEquipmentModal(): void
    {
        $this->resetEquipmentForm();
        $this->currentStep = 1;
        $this->showEquipmentModal = true;
        $this->editingEquipment = null;
        $this->photo = null;
    }

    public function showEditEquipmentModal($equipmentId): void
    {
        $equipment = Equipment::findOrFail($equipmentId);
        $this->editingEquipment = $equipment;
        
        $this->equipmentForm = [
            'name' => $equipment->name,
            'equipment_number' => $equipment->equipment_number,
            'description' => $equipment->description ?? '',
            'make' => $equipment->make,
            'model' => $equipment->model,
            'serial_number' => $equipment->serial_number ?? '',
            'barcode_number' => $equipment->barcode_number ?? '',
            'manufacturer' => $equipment->manufacturer ?? '',
            'status' => $equipment->status ?? 'Active',
            'condition' => $equipment->condition ?? '',
            'assigned_department' => $equipment->assigned_department,
            'assigned_employee_id' => $equipment->assigned_employee_id,
            'warranty_date' => $equipment->warranty_date ?? '',
            'date_purchased' => $equipment->date_purchased,
            'maintainance_days' => $equipment->maintainance_days,
            'maintainance_notification_in_days' => $equipment->maintainance_notification_in_days,
            'preventive_maintainance_period' => $equipment->preventive_maintainance_period ?? null,
            'preventive_maintainance_notification_days' => $equipment->preventive_maintainance_notification_days ?? null,
            'calibration_days' => $equipment->calibration_days,
            'calibration_notification_in_days' => $equipment->calibration_notification_in_days,
            'asset_type_id' => $equipment->asset_type_id,
            'asset_location_id' => $equipment->asset_location_id,
            'active' => $equipment->active ?? true,
            'requires_daily_log' => $equipment->requires_daily_log ?? false,
            'daily_log_value_type' => $equipment->daily_log_value_type ?? '',
            'daily_log_nature' => $equipment->daily_log_nature ?? '',
            'daily_log_tolerance' => $equipment->daily_log_tolerance ?? null,
            'daily_log_expected_value' => $equipment->daily_log_expected_value ?? '',
            'daily_log_expected_min' => $equipment->daily_log_expected_min,
            'daily_log_expected_max' => $equipment->daily_log_expected_max,
            'daily_log_reporting_unit' => $equipment->daily_log_reporting_unit ?? '',
            'daily_log_frequency' => $equipment->daily_log_frequency ?? 1,
            'daily_log_frequency_labels' => is_array($equipment->daily_log_frequency_labels ?? null) ? $equipment->daily_log_frequency_labels : [],
            'daily_log_monitored_by_another_equipment' => $equipment->daily_log_monitored_by_another_equipment ?? false,
            'daily_log_monitored_equipment_id' => $equipment->daily_log_monitored_equipment_id ?? null,
        ];

        $this->syncDailyLogFrequencyLabels();

        // Set selected names for searchable selects
        if ($equipment->assigned_employee_id) {
            $employee = User::find($equipment->assigned_employee_id);
            $this->selectedEmployeeName = $employee->name ?? '';
            $this->employeeSearch = $this->selectedEmployeeName;
        }

        if ($equipment->assigned_department) {
            $department = getInventoryDepartmentByid($equipment->assigned_department);
            $this->selectedDepartmentName = $department->name ?? '';
            $this->departmentSearch = $this->selectedDepartmentName;
        }

        if ($equipment->asset_type_id) {
            $assetType = AssetType::find($equipment->asset_type_id);
            $this->selectedAssetTypeName = $assetType ? ($assetType->asset_code . ' (' . $assetType->descripton . ')') : '';
            $this->assetTypeSearch = $this->selectedAssetTypeName;
        }

        if ($equipment->asset_location_id) {
            $assetLocation = AssetLocation::find($equipment->asset_location_id);
            $this->selectedAssetLocationName = $assetLocation->name ?? '';
            $this->assetLocationSearch = $this->selectedAssetLocationName;
        }

        if (!empty($equipment->daily_log_reporting_unit)) {
            $this->selectedReportingUnitName = $equipment->daily_log_reporting_unit;
            $this->reportingUnitSearch = '';
        }

        if (!empty($equipment->daily_log_monitored_equipment_id)) {
            $monitoredEquipment = Equipment::find($equipment->daily_log_monitored_equipment_id);
            $this->selectedMonitoredEquipmentLabel = $monitoredEquipment
                ? (($monitoredEquipment->equipment_number ?? 'N/A') . ' - ' . ($monitoredEquipment->name ?? ''))
                : '';
            $this->monitoredEquipmentSearch = '';
        }

        $this->photo = null;
        $this->currentStep = 1;
        $this->showEquipmentModal = true;
    }

    protected function getStepRules(int $step): array
    {
        $rules = [];

        if ($step === 1) {
            $rules = [
                'equipmentForm.name' => 'required|string|max:255',
                'equipmentForm.equipment_number' => 'required|string|max:255',
                'equipmentForm.description' => 'required|string',
                'equipmentForm.make' => 'required|string|max:255',
                'equipmentForm.model' => 'required|string|max:255',
                'equipmentForm.serial_number' => 'nullable|string|max:255',
                'equipmentForm.barcode_number' => 'nullable|string|max:255',
                'equipmentForm.manufacturer' => 'nullable|string|max:255',
                'photo' => 'nullable|image|max:10240',
            ];
        }

        if ($step === 2) {
            $rules = [
                'equipmentForm.status' => 'required|string',
                'equipmentForm.condition' => 'required|string|max:255',
                'equipmentForm.assigned_department' => 'required|string',
                'equipmentForm.assigned_employee_id' => 'nullable|string',
                'equipmentForm.warranty_date' => 'required|date',
                'equipmentForm.date_purchased' => 'nullable|date',
                'equipmentForm.asset_type_id' => 'nullable|string',
                'equipmentForm.asset_location_id' => 'nullable|string',
                'equipmentForm.active' => 'boolean',
            ];
        }

        if ($step === 3) {
            if (!empty($this->equipmentForm['requires_daily_log'])) {
                $type = $this->equipmentForm['daily_log_value_type'] ?? '';
                $nature = $this->equipmentForm['daily_log_nature'] ?? '';

                $rules['equipmentForm.daily_log_value_type'] = 'required|in:constant,range';
                $rules['equipmentForm.daily_log_nature'] = 'required|in:qualitative,quantitative';
                $rules['equipmentForm.daily_log_frequency'] = 'required|integer|min:1|max:6';
                $rules['equipmentForm.daily_log_reporting_unit'] = 'nullable|string|max:255';
                $rules['equipmentForm.daily_log_monitored_by_another_equipment'] = 'boolean';
                if (!empty($this->equipmentForm['daily_log_monitored_by_another_equipment'])) {
                    $rules['equipmentForm.daily_log_monitored_equipment_id'] = 'required|string';
                }

                if ($type === 'constant') {
                    $rules['equipmentForm.daily_log_expected_value'] = 'required|string|max:255';
                    if ($nature === 'quantitative') {
                        $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
                    }
                }

                if ($type === 'range') {
                    $rules['equipmentForm.daily_log_expected_min'] = 'required|numeric';
                    $rules['equipmentForm.daily_log_expected_max'] = 'required|numeric|gte:equipmentForm.daily_log_expected_min';
                    $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
                }
            }
        }

        if ($step === 4) {
            $rules = [
                'equipmentForm.maintainance_days' => 'required|integer|min:0',
                'equipmentForm.maintainance_notification_in_days' => 'required|integer|min:0',
                'equipmentForm.preventive_maintainance_period' => 'required|integer|min:0',
                'equipmentForm.preventive_maintainance_notification_days' => 'required|integer|min:0',
                'equipmentForm.calibration_days' => 'required|integer|min:0',
                'equipmentForm.calibration_notification_in_days' => 'required|integer|min:0',
            ];
        }

        return $rules;
    }

    protected function validateCurrentStep(): void
    {
        $rules = $this->getStepRules($this->currentStep);
        if (!empty($rules)) {
            $this->validate($rules);
        }
    }

    public function goToStep(int $step): void
    {
        $targetStep = max(1, min($this->totalSteps, $step));

        if ($targetStep > $this->currentStep) {
            $this->validateCurrentStep();
        }

        $this->currentStep = $targetStep;
    }

    public function nextStep(): void
    {
        if ($this->currentStep >= $this->totalSteps) {
            return;
        }

        $this->validateCurrentStep();
        $this->currentStep++;
    }

    public function previousStep(): void
    {
        if ($this->currentStep <= 1) {
            return;
        }

        $this->currentStep--;
    }

    public function saveEquipment(): void
    {
        $this->validate();

        try {
            $data = $this->equipmentForm;
            $data = $this->normalizeEquipmentPayload($data);
            $data['company_id'] = getUserCompany();

            // Handle file upload
            if ($this->photo) {
                $path = $this->photo->store('equipment', 'public');
                $file = explode('/', $path);
                $data['picture'] = '/storage/equipment/' . urlencode(end($file));
            }

            if ($this->editingEquipment) {
                // Don't overwrite picture if no new photo uploaded
                if (!$this->photo) {
                    unset($data['picture']);
                }
                $this->editingEquipment->update($data);
                $this->message = 'Equipment updated successfully!';
            } else {
                // Set default picture if no photo uploaded
                if (!$this->photo) {
                    $data['picture'] = '/images/default-equipment.png';
                }
                Equipment::create($data);
                $this->message = 'Equipment created successfully!';
            }

            $this->messageType = 'success';
            $this->showEquipmentModal = false;
            $this->resetEquipmentForm();
            $this->photo = null;
        } catch (\Exception $e) {
            $this->message = 'Error saving equipment: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }





    public function closeEquipmentModal(): void
    {
        $this->showEquipmentModal = false;
        $this->currentStep = 1;
        $this->resetEquipmentForm();
        $this->photo = null;
    }



    public function resetEquipmentForm(): void
    {
        $this->equipmentForm = [
            'name' => '',
            'equipment_number' => '',
            'description' => '',
            'make' => '',
            'model' => '',
            'serial_number' => '',
            'barcode_number' => '',
            'manufacturer' => '',
            'status' => 'Active',
            'condition' => '',
            'assigned_department' => null,
            'assigned_employee_id' => null,
            'warranty_date' => '',
            'date_purchased' => '',
            'maintainance_days' => null,
            'maintainance_notification_in_days' => null,
            'preventive_maintainance_period' => null,
            'preventive_maintainance_notification_days' => null,
            'calibration_days' => null,
            'calibration_notification_in_days' => null,
            'asset_type_id' => null,
            'asset_location_id' => null,
            'active' => true,
            'requires_daily_log' => false,
            'daily_log_value_type' => '',
            'daily_log_nature' => '',
            'daily_log_tolerance' => null,
            'daily_log_expected_value' => '',
            'daily_log_expected_min' => null,
            'daily_log_expected_max' => null,
            'daily_log_reporting_unit' => '',
            'daily_log_frequency' => 1,
            'daily_log_frequency_labels' => [],
            'daily_log_monitored_by_another_equipment' => false,
            'daily_log_monitored_equipment_id' => null,
        ];

        $this->syncDailyLogFrequencyLabels();

        $this->employeeSearch = '';
        $this->departmentSearch = '';
        $this->assetTypeSearch = '';
        $this->assetLocationSearch = '';
        $this->reportingUnitSearch = '';
        $this->monitoredEquipmentSearch = '';

        $this->selectedEmployeeName = '';
        $this->selectedDepartmentName = '';
        $this->selectedAssetTypeName = '';
        $this->selectedAssetLocationName = '';
        $this->selectedReportingUnitName = '';
        $this->selectedMonitoredEquipmentLabel = '';

        $this->showEmployeeDropdown = false;
        $this->showDepartmentDropdown = false;
        $this->showAssetTypeDropdown = false;
        $this->showAssetLocationDropdown = false;
        $this->showReportingUnitDropdown = false;
        $this->showMonitoredEquipmentDropdown = false;

        $this->editingEquipment = null;
        $this->currentStep = 1;
    }

    public function updatedEquipmentFormRequiresDailyLog(): void
    {
        if (empty($this->equipmentForm['requires_daily_log'])) {
            $this->equipmentForm['daily_log_value_type'] = '';
            $this->equipmentForm['daily_log_nature'] = '';
            $this->equipmentForm['daily_log_tolerance'] = null;
            $this->equipmentForm['daily_log_expected_value'] = '';
            $this->equipmentForm['daily_log_expected_min'] = null;
            $this->equipmentForm['daily_log_expected_max'] = null;
            $this->equipmentForm['daily_log_reporting_unit'] = '';
            $this->equipmentForm['daily_log_frequency'] = 1;
            $this->equipmentForm['daily_log_frequency_labels'] = [];
            $this->equipmentForm['daily_log_monitored_by_another_equipment'] = false;
            $this->equipmentForm['daily_log_monitored_equipment_id'] = null;
            $this->selectedReportingUnitName = '';
            $this->selectedMonitoredEquipmentLabel = '';
            $this->monitoredEquipmentSearch = '';

            $this->syncDailyLogFrequencyLabels();
        }
    }

    public function updatedEquipmentFormDailyLogFrequency(): void
    {
        $this->syncDailyLogFrequencyLabels();
    }

    protected function syncDailyLogFrequencyLabels(): void
    {
        $frequency = max(1, min(6, intval($this->equipmentForm['daily_log_frequency'] ?? 1)));
        $labels = is_array($this->equipmentForm['daily_log_frequency_labels'] ?? null)
            ? $this->equipmentForm['daily_log_frequency_labels']
            : [];

        $normalized = [];
        for ($i = 1; $i <= $frequency; $i++) {
            $key = (string) $i;
            $normalized[$key] = isset($labels[$key]) ? (string) $labels[$key] : '';
        }

        $this->equipmentForm['daily_log_frequency_labels'] = $normalized;
    }

    public function updatedEquipmentFormDailyLogMonitoredByAnotherEquipment(): void
    {
        if (empty($this->equipmentForm['daily_log_monitored_by_another_equipment'])) {
            $this->equipmentForm['daily_log_monitored_equipment_id'] = null;
            $this->selectedMonitoredEquipmentLabel = '';
            $this->monitoredEquipmentSearch = '';
            $this->showMonitoredEquipmentDropdown = false;
        }
    }

    public function updatedEquipmentFormDailyLogValueType(): void
    {
        if (($this->equipmentForm['daily_log_value_type'] ?? '') === 'range') {
            $this->equipmentForm['daily_log_nature'] = 'quantitative';
            $this->equipmentForm['daily_log_expected_value'] = '';
            $this->equipmentForm['daily_log_tolerance'] = null;
        } else {
            $this->equipmentForm['daily_log_expected_min'] = null;
            $this->equipmentForm['daily_log_expected_max'] = null;
        }
    }

    public function updatedEquipmentFormDailyLogNature(): void
    {
        if (($this->equipmentForm['daily_log_nature'] ?? '') !== 'quantitative') {
            $this->equipmentForm['daily_log_tolerance'] = null;
            $this->equipmentForm['daily_log_expected_min'] = null;
            $this->equipmentForm['daily_log_expected_max'] = null;
        } else {
            $this->equipmentForm['daily_log_expected_value'] = '';
        }
    }

    protected function normalizeEquipmentPayload(array $data): array
    {
        // Convert empty strings to null for nullable numeric/uuid columns in PostgreSQL.
        $nullableNumeric = [
            'daily_log_expected_min',
            'daily_log_expected_max',
            'daily_log_tolerance',
            'preventive_maintainance_period',
            'preventive_maintainance_notification_days',
        ];

        $nullableUuid = [
            'assigned_employee_id',
            'asset_type_id',
            'asset_location_id',
            'daily_log_monitored_equipment_id',
        ];

        foreach ($nullableNumeric as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === false) {
                $data[$field] = null;
            }
        }

        foreach ($nullableUuid as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    // Searchable Select Methods
    public function searchEmployees(): void
    {
        $this->showEmployeeDropdown = true;
        $search = $this->employeeSearch;
        
        $this->filteredEmployees = User::where('active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectEmployee($id, $name = null): void
    {
        if ($name === null) {
            $employee = User::find($id);
            $name = $employee?->name ?? '';
        }

        $this->equipmentForm['assigned_employee_id'] = $id;
        $this->selectedEmployeeName = $name;
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = false;
    }

    public function clearEmployee(): void
    {
        $this->equipmentForm['assigned_employee_id'] = null;
        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
    }

    public function searchDepartments(): void
    {
        $this->showDepartmentDropdown = true;
        $search = $this->departmentSearch;
        
        $this->filteredDepartments = InventoryDepartment::where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectDepartment($id, $name = null): void
    {
        if ($name === null) {
            $department = InventoryDepartment::find($id);
            $name = $department?->name ?? '';
        }

        $this->equipmentForm['assigned_department'] = $id;
        $this->selectedDepartmentName = $name;
        $this->departmentSearch = '';
        $this->showDepartmentDropdown = false;
    }

    public function clearDepartment(): void
    {
        $this->equipmentForm['assigned_department'] = null;
        $this->selectedDepartmentName = '';
        $this->departmentSearch = '';
    }

    public function searchAssetTypes(): void
    {
        $this->showAssetTypeDropdown = true;
        $search = $this->assetTypeSearch;
        
        $this->filteredAssetTypes = AssetType::where('is_active', 1)
            ->where(function($q) use ($search) {
                $q->where('asset_code', 'like', '%' . $search . '%')
                  ->orWhere('descripton', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get();
    }

    public function selectAssetType($id, $name): void
    {
        $this->equipmentForm['asset_type_id'] = $id;
        $this->selectedAssetTypeName = $name;
        $this->assetTypeSearch = '';
        $this->showAssetTypeDropdown = false;
    }

    public function clearAssetType(): void
    {
        $this->equipmentForm['asset_type_id'] = null;
        $this->selectedAssetTypeName = '';
        $this->assetTypeSearch = '';
    }

    public function searchAssetLocations(): void
    {
        $this->showAssetLocationDropdown = true;
        $search = $this->assetLocationSearch;
        
        $this->filteredAssetLocations = AssetLocation::where('is_active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectAssetLocation($id, $name = null): void
    {
        if ($name === null) {
            $assetLocation = AssetLocation::find($id);
            $name = $assetLocation?->name ?? '';
        }

        $this->equipmentForm['asset_location_id'] = $id;
        $this->selectedAssetLocationName = $name;
        $this->assetLocationSearch = '';
        $this->showAssetLocationDropdown = false;
    }

    public function clearAssetLocation(): void
    {
        $this->equipmentForm['asset_location_id'] = null;
        $this->selectedAssetLocationName = '';
        $this->assetLocationSearch = '';
    }

    public function searchReportingUnits(): void
    {
        $this->showReportingUnitDropdown = true;
        $search = $this->reportingUnitSearch;

        $this->filteredReportingUnits = ReportingUnit::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectReportingUnit($unitIdOrName): void
    {
        $name = ReportingUnit::query()
            ->where('id', $unitIdOrName)
            ->value('name') ?? $unitIdOrName;

        $this->equipmentForm['daily_log_reporting_unit'] = $name;
        $this->selectedReportingUnitName = $name;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function clearReportingUnit(): void
    {
        $this->equipmentForm['daily_log_reporting_unit'] = '';
        $this->selectedReportingUnitName = '';
        $this->reportingUnitSearch = '';
    }

    public function searchMonitoredEquipments(): void
    {
        $this->showMonitoredEquipmentDropdown = true;
        $search = $this->monitoredEquipmentSearch;

        $query = Equipment::query()
            ->where('company_id', getUserCompany())
            ->where('is_disposal', 0)
            ->where('active', 1);

        if ($this->editingEquipment) {
            $query->where('id', '!=', $this->editingEquipment->id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('equipment_number', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $this->filteredMonitoredEquipments = $query
            ->orderBy('equipment_number')
            ->limit(10)
            ->get(['id', 'equipment_number', 'name']);
    }

    public function selectMonitoredEquipment($id): void
    {
        $equipment = Equipment::find($id);
        $label = $equipment ? (($equipment->equipment_number ?? 'N/A') . ' - ' . ($equipment->name ?? '')) : '';

        $this->equipmentForm['daily_log_monitored_equipment_id'] = $id;
        $this->selectedMonitoredEquipmentLabel = $label;
        $this->monitoredEquipmentSearch = '';
        $this->showMonitoredEquipmentDropdown = false;
    }

    public function clearMonitoredEquipment(): void
    {
        $this->equipmentForm['daily_log_monitored_equipment_id'] = null;
        $this->selectedMonitoredEquipmentLabel = '';
        $this->monitoredEquipmentSearch = '';
    }

    public function getDailyLogFrequencyRowsProperty(): array
    {
        $frequency = max(1, min(6, intval($this->equipmentForm['daily_log_frequency'] ?? 1)));
        $labels = is_array($this->equipmentForm['daily_log_frequency_labels'] ?? null)
            ? $this->equipmentForm['daily_log_frequency_labels']
            : [];

        $rows = [];
        for ($i = 1; $i <= $frequency; $i++) {
            $key = (string) $i;
            $rows[] = [
                'id' => $i,
                'label' => $labels[$key] ?? '',
            ];
        }

        return $rows;
    }

    // Bulk Upload Methods
    #[On('equipment-open-bulk-upload-modal')]
    public function openBulkUploadModal(): void
    {
        $this->showBulkUploadModal = true;
        $this->bulkFile = null;
        $this->dispatch('bulk-upload-modal-opened');
    }

    public function closeBulkUploadModal(): void
    {
        $this->showBulkUploadModal = false;
        $this->bulkFile = null;
    }

    public function downloadTemplate()
    {
        $filename = 'equipment_bulk_import_template.xlsx';
        
        $headers = [
            [
                'Name',
                'Equipment Number',
                'Make',
                'Model',
                'Serial Number',
                'Manufacturer',
                'Assigned Department',
                'Date Purchased',
                'Previous Calibration Date',
                'Calibration Interval (Days)',
                'Previous Maintainance Date',
                'Intermediate Checks Interval (Days)'
            ]
        ];

        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            
            public function __construct($data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data;
            }
        }, $filename);
    }

    public function processBulkUpload(): void
    {
        $this->validate([
            'bulkFile' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ]);

        try {
            $import = new EquipmentImport();
            
            Excel::import($import, $this->bulkFile);

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $this->closeBulkUploadModal();
            
            if ($errorCount > 0) {
                $errorMessage = implode(' | ', array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $errorMessage .= ' ... and ' . (count($errors) - 10) . ' more error(s)';
                }
                $this->message = "{$successCount} equipment item(s) created successfully. {$errorCount} row(s) failed. Errors: " . $errorMessage;
                $this->messageType = 'warning';
            } else {
                $this->message = "{$successCount} equipment item(s) created successfully!";
                $this->messageType = 'success';
            }

        } catch (\Exception $e) {
            $this->message = 'Error processing file: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function render()
    {
        return view('livewire.equipment.equipment-manager');
    }
}


