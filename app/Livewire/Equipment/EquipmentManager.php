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
use App\Livewire\Equipment\Concerns\InteractsWithEquipmentFormWizard;

class EquipmentManager extends Component
{
    use InteractsWithEquipmentFormWizard;
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

    // Bulk Upload
    public $bulkFile = null;
    public $selectedZoneId = null;
    public $zones = [];

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

    public $showEmployeeDropdown = false;
    public $showDepartmentDropdown = false;
    public $showAssetTypeDropdown = false;
    public $showAssetLocationDropdown = false;
    public $showReportingUnitDropdown = false;

    public $selectedEmployeeName = '';
    public $selectedDepartmentName = '';
    public $selectedAssetTypeName = '';
    public $selectedAssetLocationName = '';
    public $selectedReportingUnitName = '';

    public $filteredEmployees = [];
    public $filteredDepartments = [];
    public $filteredAssetTypes = [];
    public $filteredAssetLocations = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

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
        $this->zones = \App\Zone::orderBy('value')->get();
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

    public function showEditEquipmentModal(string $equipmentId): void
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

    public function saveEquipment(): void
    {
        $this->validate($this->getEquipmentFormSaveValidationRules());

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

    // Bulk Upload Methods
    #[On('equipment-open-bulk-upload-modal')]
    public function openBulkUploadModal(): void
    {
        $this->showBulkUploadModal = true;
        $this->bulkFile = null;
        $this->selectedZoneId = null;
        $this->dispatch('bulk-upload-modal-opened');
    }

    public function closeBulkUploadModal(): void
    {
        $this->showBulkUploadModal = false;
        $this->bulkFile = null;
        $this->selectedZoneId = null;
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
            $batch = \App\Models\BulkImportBatch::create([
                'company_id' => getUserCompany(),
                'user_id' => auth()->id(),
                'module' => 'inventory',
                'form_type' => 'equipment',
                'status' => 'started',
                'started_at' => now(),
            ]);

            $import = new EquipmentImport($batch, $this->selectedZoneId);
            
            Excel::import($import, $this->bulkFile);

            $batch->markAsCompleted();

            $this->closeBulkUploadModal();
            
            if ($batch->error_rows > 0) {
                $errors = $batch->getErrorSummary();
                $errorMessage = implode(' | ', array_map(fn($e) => $e['message'], array_slice($errors, 0, 5)));
                if (count($errors) > 5) {
                    $errorMessage .= ' ... and more';
                }
                $this->message = "{$batch->imported_rows} equipment item(s) created successfully. {$batch->error_rows} row(s) failed. Errors: " . $errorMessage;
                $this->messageType = 'warning';
            } else {
                $this->message = "{$batch->imported_rows} equipment item(s) created successfully!";
                $this->messageType = 'success';
            }

        } catch (\Throwable $e) {
            \Log::error("Bulk Upload Error: " . $e->getMessage());
            $this->message = 'Error processing file: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function render()
    {
        return view('livewire.equipment.equipment-manager');
    }
}


