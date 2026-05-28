<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\Models\Equipments\VerificationLog;
use App\Models\Equipments\EquipmentOperator;
use App\Models\Equipments\EquipmentNotifications;
use App\Models\Equipments\PartsRepaired;
use App\Models\Equipments\EquipmentDailyLogEntry;
use App\EquipmentAttachment;
use App\User;
use App\Supplier;
use App\InventoryDepartment;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use App\ReportingUnit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Livewire\Equipment\Concerns\InteractsWithEquipmentDepreciationWizard;
use App\Livewire\Equipment\Concerns\InteractsWithEquipmentFormWizard;
use App\Models\Equipments\EquipmentAccessory;
use App\Models\Equipments\EquipmentSparePart;
use App\Models\Equipments\EquipmentAnnualMaintenance;
use App\Models\Equipments\EquipmentPreventiveMaintenance;
use App\Models\Equipments\EquipmentMaintenanceRegister;

class EquipmentDetail extends Component
{
    use InteractsWithEquipmentFormWizard;
    use InteractsWithEquipmentDepreciationWizard;
    use WithPagination;
    use WithFileUploads;

    public $equipmentId;
    public $equipment;
    public $activeTab = 'details';

    public string $activeDetailsSection = 'basic';

    // New Tabs Properties
    public bool $showAccessoryModal = false;
    public bool $showSparePartModal = false;
    public array $accessoryForm = ['id' => '', 'name' => '', 'description' => '', 'serial_number' => '', 'part_number' => ''];
    public array $sparePartForm = ['id' => '', 'name' => '', 'description' => '', 'serial_number' => '', 'part_number' => ''];

    // Equipment Maintenance properties
    public bool $fromEquipmentMaintenance = false;

    // Annual Maintenance Form Properties (TSU/F/06)
    public bool $showAnnualModal = false;
    public $annualId;
    public $annual_serviced_date = '';
    public $annual_status = '';
    public $annual_next_service = '';
    public $annual_remark = '';

    // Preventive Maintenance Form Properties (TSU/F/05)
    public bool $showPreventiveModal = false;
    public $preventiveId;
    public $preventive_date = '';
    public $preventive_notes = '';

    // Maintenance Register Form Properties
    public bool $showRegisterModal = false;
    public $registerId;
    public $register_year = '';
    public $register_service_provider = '';
    public $register_service_type = '';
    public $register_cost_usd = '';
    public $register_cost_tzs = '';

    // Modal States
    public $showEditModal = false;
    public $showMaintenanceModal = false;
    public bool $showDeleteMaintenanceConfirmModal = false;
    public ?string $pendingDeleteMaintenanceLogId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $pendingDeleteMaintenancePreview = null;

    public $showCalibrationModal = false;
    public bool $showDeleteCalibrationConfirmModal = false;
    public ?string $pendingDeleteCalibrationLogId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $pendingDeleteCalibrationPreview = null;
    public $showRepairModal = false;
    public $showVerificationModal = false;
    public bool $showDeleteVerificationConfirmModal = false;
    public ?string $pendingDeleteVerificationLogId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $pendingDeleteVerificationPreview = null;
    public $showOperatorModal = false;
    public bool $showOperatorDropdown = false;
    public $showAttachmentModal = false;
    public bool $showDeleteAttachmentConfirmModal = false;
    public ?string $pendingDeleteAttachmentId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $pendingDeleteAttachmentPreview = null;
    public $showNotificationModal = false;
    public bool $showDeleteNotificationConfirmModal = false;
    public ?string $pendingDeleteNotificationId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $pendingDeleteNotificationPreview = null;

    public bool $showLogNotesModal = false;

    public string $logNotesModalKind = '';

    /** @var array<string, mixed> */
    public array $logNotesModalPayload = [];

    public $showStatusDropdown = false;
    public $showReportingUnitDropdown = false;
    public $showDailyLogFrequencyDropdown = false;
    public $showDailyLogValueTypeDropdown = false;
    public $showDailyLogNatureDropdown = false;
    public $showNotificationTypeDropdown = false;
    public $showNotificationFrequencyDropdown = false;
    public $showNonConformancePerPageDropdown = false;

    // Editing States
    public $editingLog = null;
    public $editingAttachment = null;
    public $editingNotification = null;

    // Equipment Form
    public $equipmentForm = [];
    public $photo;

    // Log Forms
    public $maintenanceForm = [
        'date' => '',
        'description' => '',
        'reference_number' => '',
        'maintainance_type' => 'in_house',
        'employee_id' => null,
        'supplier_id' => null,
        'notes' => '',
    ];
    public $certificate;

    public $calibrationForm = [
        'date' => '',
        'description' => '',
        'reference_number' => '',
        'correction_factor' => '',
        'uncertainty_of_measure' => '',
        'maintainance_type' => 'in_house',
        'employee_id' => null,
        'supplier_id' => null,
        'notes' => '',
    ];

    public $repairForm = [
        'date' => '',
        'description' => '',
        'reference_number' => '',
        'maintainance_type' => 'in_house',
        'employee_id' => null,
        'supplier_id' => null,
        'notes' => '',
        'parts' => [],
    ];

    public $verificationForm = [
        'verification_date' => '',
        'reference_standard' => '',
        'procedure' => '',
        'response' => '',
        'remarks' => '',
        'maintainance_type' => 'in_house',
        'operator_id' => null,
        'supplier_id' => null,
    ];

    public $operatorForm = [
        'operators' => [],
    ];

    public $attachmentForm = [
        'title' => '',
        'description' => '',
    ];
    public $attachmentFile;

    public $notificationForm = [
        'value' => '',
        'frequency' => 'days',
        'notification_type' => 'calibration',
    ];

    // Supporting Data
    public $employees = [];
    public $suppliers = [];
    public $departments = [];
    public $statuses = [];
    public $parts = [];
    public $assetTypes = [];
    public $assetLocations = [];
    public $reportingUnits = [];
    
    // Search Properties    // Search
    public $maintenanceSearch = '';
    public $calibrationSearch = '';
    public $verificationSearch = '';
    public $attachmentSearch = '';
    
    protected $paginationTheme = 'bootstrap';

    // Searchable Select Properties
    public $employeeSearch = '';
    public $supplierSearch = '';
    public $departmentSearch = '';
    public $assetTypeSearch = '';
    public $assetLocationSearch = '';

    public $showEmployeeDropdown = false;
    public $showSupplierDropdown = false;
    public $showDepartmentDropdown = false;
    public $showAssetTypeDropdown = false;
    public $showAssetLocationDropdown = false;

    public $selectedEmployeeName = '';
    public $selectedSupplierName = '';
    public $selectedDepartmentName = '';
    public $selectedStatusName = '';
    public $selectedAssetTypeName = '';
    public $selectedAssetLocationName = '';
    public $selectedReportingUnitName = '';
    public $selectedDailyLogFrequencyLabel = '';
    public $selectedDailyLogValueTypeLabel = '';
    public $selectedDailyLogNatureLabel = '';
    public $selectedNotificationTypeLabel = '';
    public $selectedNotificationFrequencyLabel = '';

    public $filteredEmployees = [];
    public $filteredSuppliers = [];
    public $filteredDepartments = [];
    public $filteredAssetTypes = [];
    public $filteredAssetLocations = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    public $fromEquipmentChecks = false;

    public $nonConformanceFromDate = '';
    public $nonConformanceToDate = '';
    public $nonConformancePerPage = 10;
    public $nonConformancePage = 1;

    public function mount($equipmentId, bool $fromEquipmentChecks = false, bool $fromEquipmentMaintenance = false): void
    {
        $this->equipmentId = $equipmentId;
        $this->fromEquipmentChecks = $fromEquipmentChecks;
        $this->fromEquipmentMaintenance = $fromEquipmentMaintenance;
        if ($fromEquipmentChecks) {
            $this->activeTab = 'equipment-checks';
        }
        if ($fromEquipmentMaintenance) {
            $this->activeTab = 'annual-maintenance';
        }
        $tab = request()->query('tab');
        if (is_string($tab) && $tab !== '') {
            $this->activeTab = $tab;
        }
        $this->loadEquipment();
        $this->loadInitialData();

        if ($this->activeTab === 'details') {
            $this->ensureActiveDetailsSectionKey();
        }
    }

    public function loadEquipment(): void
    {
        $this->equipment = Equipment::findOrFail($this->equipmentId);
    }

    public function loadInitialData(): void
    {
        $this->employees = getUsers();
        $this->suppliers = Supplier::all();
        $this->departments = InventoryDepartment::where('module', 'organizational')->get();
        $this->statuses = getStatus();
        $this->parts = PartsRepaired::all();
        $this->assetTypes = AssetType::where('is_active', 1)->get();
        $this->assetLocations = AssetLocation::where('is_active', 1)->get();
        $this->reportingUnits = ReportingUnit::orderBy('name')->get();
        $this->filteredReportingUnits = $this->reportingUnits;
    }

    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;

        if ($tab === 'details') {
            $this->ensureActiveDetailsSectionKey();
        }
    }

    public function setActiveDetailsSection(string $key): void
    {
        $this->activeDetailsSection = $key;
    }

    /**
     * @return array{key: string, icon: string, title: string, fields: list<array<string, mixed>>}|null
     */
    public function getActiveEquipmentDetailSectionProperty(): ?array
    {
        foreach ($this->equipmentDetailSections as $section) {
            if (($section['key'] ?? '') === $this->activeDetailsSection) {
                return $section;
            }
        }

        return $this->equipmentDetailSections[0] ?? null;
    }

    private function ensureActiveDetailsSectionKey(): void
    {
        $keys = collect($this->equipmentDetailSections)
            ->pluck('key')
            ->filter()
            ->values()
            ->all();

        if ($keys === [] || in_array($this->activeDetailsSection, $keys, true)) {
            return;
        }

        $this->activeDetailsSection = (string) $keys[0];
    }

    public function updatedNonConformanceFromDate(): void
    {
        $this->nonConformancePage = 1;
    }

    public function updatedNonConformanceToDate(): void
    {
        $this->nonConformancePage = 1;
    }

    public function updatedNonConformancePerPage($value): void
    {
        $this->nonConformancePerPage = max(10, (int) $value);
        $this->nonConformancePage = 1;
        $this->showNonConformancePerPageDropdown = false;
    }

    public function previousNonConformancePage(): void
    {
        $this->nonConformancePage = max(1, (int) $this->nonConformancePage - 1);
    }

    public function nextNonConformancePage(): void
    {
        $this->nonConformancePage = min($this->nonConformanceTotalPages, (int) $this->nonConformancePage + 1);
    }

    // Equipment Management
    public function showEditEquipmentModal(): void
    {
        $e = $this->equipment;
        $this->equipmentForm = [
            'name' => $e->name,
            'equipment_number' => $e->equipment_number,
            'description' => $e->description ?? '',
            'make' => $e->make,
            'model' => $e->model,
            'serial_number' => $e->serial_number ?? '',
            'barcode_number' => $e->barcode_number ?? '',
            'manufacturer' => $e->manufacturer ?? '',
            'status' => $e->status ?? 'Active',
            'condition' => $e->condition ?? '',
            'assigned_department' => $e->assigned_department,
            'assigned_employee_id' => $e->assigned_employee_id,
            'warranty_date' => $e->warranty_date ? Carbon::parse($e->warranty_date)->format('Y-m-d') : '',
            'date_purchased' => $e->date_purchased ? Carbon::parse($e->date_purchased)->format('Y-m-d') : '',
            'maintainance_days' => $e->maintainance_days,
            'maintainance_notification_in_days' => $e->maintainance_notification_in_days,
            'preventive_maintainance_period' => $e->preventive_maintainance_period ?? null,
            'preventive_maintainance_notification_days' => $e->preventive_maintainance_notification_days ?? null,
            'calibration_days' => $e->calibration_days,
            'calibration_notification_in_days' => $e->calibration_notification_in_days,
            'asset_type_id' => $e->asset_type_id,
            'asset_location_id' => $e->asset_location_id,
            'active' => $e->active ?? true,
            'requires_daily_log' => $e->requires_daily_log ?? false,
            'has_logbook_tracking' => $e->has_logbook_tracking ?? false,
            'daily_log_value_type' => $e->daily_log_value_type ?? '',
            'daily_log_nature' => $e->daily_log_nature ?? '',
            'daily_log_tolerance' => $e->daily_log_tolerance ?? null,
            'daily_log_expected_value' => $e->daily_log_expected_value ?? '',
            'daily_log_expected_min' => $e->daily_log_expected_min,
            'daily_log_expected_max' => $e->daily_log_expected_max,
            'daily_log_reporting_unit' => $e->daily_log_reporting_unit ?? '',
            'daily_log_frequency' => $e->daily_log_frequency ?? 1,
            'daily_log_frequency_labels' => is_array($e->daily_log_frequency_labels ?? null) ? $e->daily_log_frequency_labels : [],
            'daily_log_monitored_by_another_equipment' => $e->daily_log_monitored_by_another_equipment ?? false,
            'daily_log_monitored_equipment_id' => $e->daily_log_monitored_equipment_id ?? null,
        ];

        $this->syncDailyLogFrequencyLabels();

        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        if ($e->assigned_employee_id) {
            $employee = User::find($e->assigned_employee_id);
            $this->selectedEmployeeName = $employee->name ?? '';
            $this->employeeSearch = $this->selectedEmployeeName;
        }

        $this->selectedDepartmentName = '';
        $this->departmentSearch = '';
        if ($e->assigned_department) {
            $department = getInventoryDepartmentByid($e->assigned_department);
            $this->selectedDepartmentName = $department->name ?? '';
            $this->departmentSearch = $this->selectedDepartmentName;
        }

        $this->selectedStatusName = (string) ($this->equipmentForm['status'] ?? '');

        $this->selectedAssetTypeName = '';
        $this->assetTypeSearch = '';
        if ($e->asset_type_id) {
            $assetType = AssetType::find($e->asset_type_id);
            $this->selectedAssetTypeName = $assetType ? ($assetType->asset_code . ' (' . $assetType->descripton . ')') : '';
            $this->assetTypeSearch = $this->selectedAssetTypeName;
        }

        $this->selectedAssetLocationName = '';
        $this->assetLocationSearch = '';
        if ($e->asset_location_id) {
            $assetLocation = AssetLocation::find($e->asset_location_id);
            $this->selectedAssetLocationName = $assetLocation->name ?? '';
            $this->assetLocationSearch = $this->selectedAssetLocationName;
        }

        $this->selectedReportingUnitName = '';
        $this->reportingUnitSearch = '';
        if (! empty($e->daily_log_reporting_unit)) {
            $this->selectedReportingUnitName = (string) $e->daily_log_reporting_unit;
        }

        $this->selectedMonitoredEquipmentLabel = '';
        $this->monitoredEquipmentSearch = '';
        if (! empty($e->daily_log_monitored_equipment_id)) {
            $monitored = Equipment::find($e->daily_log_monitored_equipment_id);
            $this->selectedMonitoredEquipmentLabel = $monitored
                ? (($monitored->equipment_number ?? 'N/A') . ' - ' . ($monitored->name ?? ''))
                : '';
        }

        $this->equipment->load('depreciationConfig');
        $this->loadDepreciationFormFromEquipment($this->equipment);
        $this->photo = null;
        $this->currentStep = 1;
        $this->showEditModal = true;
    }

    public function closeEditEquipmentModal(): void
    {
        $this->showEditModal = false;
        $this->currentStep = 1;
        $this->photo = null;
    }

    public function updatedEmployeeSearch(): void
    {
        if ($this->employeeSearch === '') {
            $this->filteredEmployees = [];
            return;
        }

        $this->searchEmployees();
    }

    public function updatedSupplierSearch(): void
    {
        if ($this->supplierSearch === '') {
            $this->filteredSuppliers = [];
            return;
        }

        $this->searchSuppliers();
    }

    public function saveEquipment(): void
    {
        $rules = array_merge(
            $this->getEquipmentFormSaveValidationRules(),
            $this->getDepreciationStepRules()
        );
        $this->validate($rules);

        try {
            $data = $this->equipmentForm;
            $data = $this->normalizeEquipmentPayload($data);
            $data['company_id'] = getUserCompany();

            if ($this->photo) {
                $path = $this->photo->store('equipment', 'public');
                $file = explode('/', $path);
                $data['picture'] = '/storage/equipment/' . urlencode(end($file));
            } else {
                unset($data['picture']);
            }

            $this->equipment->update($data);
            $this->persistDepreciationConfig($this->equipment->fresh(), false);
            $this->loadEquipment();
            $this->message = 'Equipment updated successfully!';
            $this->messageType = 'success';
            $this->closeEditEquipmentModal();
        } catch (\Exception $e) {
            $this->message = 'Error updating equipment: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }
    public function showCreateMaintenanceModal(): void
    {
        $this->resetMaintenanceForm();
        $this->showMaintenanceModal = true;
        $this->editingLog = null;
    }

    public function showEditMaintenanceModal(string $logId): void
    {
        $log = MaintainanceCalibrationLog::findOrFail($logId);
        $this->editingLog = $log;
        $serviceType = $log->maintainance_type ?? 'in_house';
        $serviceType = str_replace('-', '_', (string) $serviceType);
        if (! in_array($serviceType, ['in_house', 'external'], true)) {
            $serviceType = 'in_house';
        }
        $dateRaw = $log->date;
        $dateFormatted = $dateRaw
            ? Carbon::parse($dateRaw)->format('Y-m-d')
            : '';

        $this->maintenanceForm = [
            'date' => $dateFormatted,
            'description' => (string) ($log->description ?? ''),
            'reference_number' => (string) ($log->reference_number ?? ''),
            'maintainance_type' => $serviceType,
            'employee_id' => $log->employee_id,
            'supplier_id' => $log->supplier_id,
            'notes' => (string) ($log->notes ?? ''),
        ];

        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = false;
        $this->selectedSupplierName = '';
        $this->supplierSearch = '';
        $this->showSupplierDropdown = false;

        if ($serviceType === 'in_house' && $log->employee_id) {
            $employee = User::find($log->employee_id);
            $this->selectedEmployeeName = $employee->name ?? '';
        }

        if ($serviceType === 'external' && $log->supplier_id) {
            $supplier = Supplier::find($log->supplier_id);
            $this->selectedSupplierName = $supplier->name ?? '';
        }

        $this->certificate = null;
        $this->showMaintenanceModal = true;
    }

    public function saveMaintenanceLog(): void
    {
        $this->validate([
            'maintenanceForm.date' => 'required|date',
            'maintenanceForm.description' => 'required|string',
            'maintenanceForm.notes' => 'required|string',
            'certificate' => 'nullable|file|max:10240',
        ]);

        try {
            $data = [
                'equipment_id' => $this->equipmentId,
                'type' => 'Maintainance',
                'date' => $this->maintenanceForm['date'],
                'description' => $this->maintenanceForm['description'],
                'reference_number' => $this->maintenanceForm['reference_number'],
                'notes' => $this->maintenanceForm['notes'],
                'overseen_by' => auth()->id(),
                'edit_by' => auth()->id(),
            ];

            if ($this->maintenanceForm['maintainance_type'] === 'external') {
                $data['maintainance_type'] = 'external';
                $data['supplier_id'] = $this->maintenanceForm['supplier_id'];
            } else {
                $data['maintainance_type'] = 'in-house';
                $data['employee_id'] = $this->maintenanceForm['employee_id'];
            }

            if ($this->certificate) {
                $path = $this->certificate->store('certificate', 'public');
                $file = explode('/', $path);
                $data['certificate'] = '/storage/certificate/' . urlencode(end($file));
            }

            if ($this->editingLog) {
                $this->editingLog->update($data);
                $this->message = 'Maintenance log updated successfully!';
            } else {
                MaintainanceCalibrationLog::create($data);
                $this->message = 'Maintenance log created successfully!';
            }

            $this->messageType = 'success';
            $this->loadEquipment();
            $this->showMaintenanceModal = false;
            $this->resetMaintenanceForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving maintenance log: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function openDeleteMaintenanceConfirmModal(string $logId): void
    {
        $log = MaintainanceCalibrationLog::where('equipment_id', $this->equipmentId)
            ->where('type', 'Maintainance')
            ->findOrFail($logId);
        $this->pendingDeleteMaintenanceLogId = $logId;
        $this->pendingDeleteMaintenancePreview = $this->previewFromMaintainanceCalibrationLog($log, includeCalibrationMetrics: false);
        $this->showDeleteMaintenanceConfirmModal = true;
    }

    public function closeDeleteMaintenanceConfirmModal(): void
    {
        $this->showDeleteMaintenanceConfirmModal = false;
        $this->pendingDeleteMaintenanceLogId = null;
        $this->pendingDeleteMaintenancePreview = null;
    }

    public function confirmDeleteMaintenanceLog(): void
    {
        if ($this->pendingDeleteMaintenanceLogId === null) {
            return;
        }

        try {
            MaintainanceCalibrationLog::findOrFail($this->pendingDeleteMaintenanceLogId)->delete();
            $this->message = 'Maintenance log deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting maintenance log: ' . $e->getMessage();
            $this->messageType = 'danger';
        } finally {
            $this->closeDeleteMaintenanceConfirmModal();
        }
    }

    // Calibration Log Management (similar to maintenance)
    public function showCreateCalibrationModal(): void
    {
        $this->resetCalibrationForm();
        $this->showCalibrationModal = true;
        $this->editingLog = null;
    }

    public function showEditCalibrationModal(string $logId): void
    {
        $log = MaintainanceCalibrationLog::findOrFail($logId);
        $this->editingLog = $log;
        $serviceType = $log->maintainance_type ?? 'in_house';
        $serviceType = str_replace('-', '_', (string) $serviceType);
        if (! in_array($serviceType, ['in_house', 'external'], true)) {
            $serviceType = 'in_house';
        }
        $dateRaw = $log->date;
        $dateFormatted = $dateRaw
            ? Carbon::parse($dateRaw)->format('Y-m-d')
            : '';

        $this->calibrationForm = [
            'date' => $dateFormatted,
            'description' => (string) ($log->description ?? ''),
            'reference_number' => (string) ($log->reference_number ?? ''),
            'correction_factor' => $log->correction_factor ?? '',
            'uncertainty_of_measure' => $log->uncertainty_of_measure ?? '',
            'maintainance_type' => $serviceType,
            'employee_id' => $log->employee_id,
            'supplier_id' => $log->supplier_id,
            'notes' => (string) ($log->notes ?? ''),
        ];

        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = false;
        $this->selectedSupplierName = '';
        $this->supplierSearch = '';
        $this->showSupplierDropdown = false;

        if ($serviceType === 'in_house' && $log->employee_id) {
            $employee = User::find($log->employee_id);
            $this->selectedEmployeeName = $employee->name ?? '';
        }

        if ($serviceType === 'external' && $log->supplier_id) {
            $supplier = Supplier::find($log->supplier_id);
            $this->selectedSupplierName = $supplier->name ?? '';
        }

        $this->certificate = null;
        $this->showCalibrationModal = true;
    }

    public function saveCalibrationLog(): void
    {
        $this->validate([
            'calibrationForm.date' => 'required|date',
            'calibrationForm.description' => 'required|string',
            'calibrationForm.reference_number' => 'required|string',
            'calibrationForm.correction_factor' => 'required|numeric',
            'calibrationForm.uncertainty_of_measure' => 'required|numeric',
            'calibrationForm.notes' => 'required|string',
            'certificate' => 'nullable|file|max:10240',
        ]);

        try {
            $data = [
                'equipment_id' => $this->equipmentId,
                'type' => 'Calibration',
                'date' => $this->calibrationForm['date'],
                'description' => $this->calibrationForm['description'],
                'reference_number' => $this->calibrationForm['reference_number'],
                'correction_factor' => $this->calibrationForm['correction_factor'],
                'uncertainty_of_measure' => $this->calibrationForm['uncertainty_of_measure'],
                'notes' => $this->calibrationForm['notes'],
                'overseen_by' => auth()->id(),
                'edit_by' => auth()->id(),
            ];

            if ($this->calibrationForm['maintainance_type'] === 'external') {
                $data['maintainance_type'] = 'external';
                $data['supplier_id'] = $this->calibrationForm['supplier_id'];
            } else {
                $data['maintainance_type'] = 'in-house';
                $data['employee_id'] = $this->calibrationForm['employee_id'];
            }

            if ($this->certificate) {
                $path = $this->certificate->store('certificate', 'public');
                $file = explode('/', $path);
                $data['certificate'] = '/storage/certificate/' . urlencode(end($file));
            }

            if ($this->editingLog) {
                $this->editingLog->update($data);
                $this->message = 'Calibration log updated successfully!';
            } else {
                MaintainanceCalibrationLog::create($data);
                $this->message = 'Calibration log created successfully!';
            }

            $this->messageType = 'success';
            $this->loadEquipment();
            $this->showCalibrationModal = false;
            $this->resetCalibrationForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving calibration log: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function openDeleteCalibrationConfirmModal(string $logId): void
    {
        $log = MaintainanceCalibrationLog::where('equipment_id', $this->equipmentId)
            ->where('type', 'Calibration')
            ->findOrFail($logId);
        $this->pendingDeleteCalibrationLogId = $logId;
        $this->pendingDeleteCalibrationPreview = $this->previewFromMaintainanceCalibrationLog($log, includeCalibrationMetrics: true);
        $this->showDeleteCalibrationConfirmModal = true;
    }

    public function closeDeleteCalibrationConfirmModal(): void
    {
        $this->showDeleteCalibrationConfirmModal = false;
        $this->pendingDeleteCalibrationLogId = null;
        $this->pendingDeleteCalibrationPreview = null;
    }

    public function confirmDeleteCalibrationLog(): void
    {
        if ($this->pendingDeleteCalibrationLogId === null) {
            return;
        }

        try {
            MaintainanceCalibrationLog::findOrFail($this->pendingDeleteCalibrationLogId)->delete();
            $this->message = 'Calibration log deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting calibration log: ' . $e->getMessage();
            $this->messageType = 'danger';
        } finally {
            $this->closeDeleteCalibrationConfirmModal();
        }
    }

    // Verification Log Management
    public function showCreateVerificationModal(): void
    {
        $this->resetVerificationForm();
        $this->showVerificationModal = true;
        $this->editingLog = null;
    }

    public function showEditVerificationModal(string $logId): void
    {
        $log = VerificationLog::findOrFail($logId);
        $this->editingLog = $log;
        $serviceType = $log->maintainance_type ?? 'in_house';
        $serviceType = str_replace('-', '_', (string) $serviceType);
        if (! in_array($serviceType, ['in_house', 'external'], true)) {
            $serviceType = 'in_house';
        }
        $this->verificationForm = [
            'verification_date' => $log->verification_date instanceof Carbon
                ? $log->verification_date->format('Y-m-d')
                : (string) $log->verification_date,
            'reference_standard' => $log->reference_standard ?? '',
            'procedure' => $log->procedure ?? '',
            'response' => $log->response ?? '',
            'remarks' => $log->remarks ?? '',
            'maintainance_type' => $serviceType,
            'operator_id' => $log->operator_id,
            'supplier_id' => $log->supplier_id,
        ];

        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = false;
        $this->selectedSupplierName = '';
        $this->supplierSearch = '';
        $this->showSupplierDropdown = false;

        if ($serviceType === 'in_house' && $log->operator_id) {
            $operator = User::find($log->operator_id);
            $this->selectedEmployeeName = $operator->name ?? '';
        }

        if ($serviceType === 'external' && $log->supplier_id) {
            $supplier = Supplier::find($log->supplier_id);
            $this->selectedSupplierName = $supplier->name ?? '';
        }

        $this->showVerificationModal = true;
    }

    public function saveVerificationLog(): void
    {
        $this->validate([
            'verificationForm.verification_date' => 'required|date',
            'verificationForm.reference_standard' => 'required|string',
            'verificationForm.procedure' => 'required|string',
            'verificationForm.response' => 'required|string',
            'verificationForm.remarks' => 'required|string',
        ]);

        try {
            $data = [
                'equipment_id' => $this->equipmentId,
                'verification_date' => $this->verificationForm['verification_date'],
                'reference_standard' => $this->verificationForm['reference_standard'],
                'procedure' => $this->verificationForm['procedure'],
                'response' => $this->verificationForm['response'],
                'remarks' => $this->verificationForm['remarks'],
            ];

            if ($this->verificationForm['maintainance_type'] === 'external') {
                $data['maintainance_type'] = 'external';
                $data['supplier_id'] = $this->verificationForm['supplier_id'];
                $data['operator_id'] = null;
            } else {
                $data['maintainance_type'] = 'in-house';
                $data['operator_id'] = $this->verificationForm['operator_id'];
                $data['supplier_id'] = null;
            }

            if ($this->editingLog) {
                $this->editingLog->update($data);
                $this->message = 'Verification log updated successfully!';
            } else {
                VerificationLog::create($data);
                $this->message = 'Verification log created successfully!';
            }

            $this->messageType = 'success';
            $this->loadEquipment();
            $this->showVerificationModal = false;
            $this->resetVerificationForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving verification log: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function openDeleteVerificationConfirmModal(string $logId): void
    {
        $log = VerificationLog::where('equipment_id', $this->equipmentId)
            ->where('is_delete', 0)
            ->findOrFail($logId);
        $this->pendingDeleteVerificationLogId = $logId;
        $this->pendingDeleteVerificationPreview = $this->previewFromVerificationLog($log);
        $this->showDeleteVerificationConfirmModal = true;
    }

    public function closeDeleteVerificationConfirmModal(): void
    {
        $this->showDeleteVerificationConfirmModal = false;
        $this->pendingDeleteVerificationLogId = null;
        $this->pendingDeleteVerificationPreview = null;
    }

    public function confirmDeleteVerificationLog(): void
    {
        if ($this->pendingDeleteVerificationLogId === null) {
            return;
        }

        try {
            $log = VerificationLog::findOrFail($this->pendingDeleteVerificationLogId);
            $log->update(['is_delete' => 1]);
            $this->message = 'Verification log deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting verification log: ' . $e->getMessage();
            $this->messageType = 'danger';
        } finally {
            $this->closeDeleteVerificationConfirmModal();
        }
    }

    public function openMaintenanceNotesModal(string $logId): void
    {
        $log = MaintainanceCalibrationLog::query()
            ->where('equipment_id', $this->equipmentId)
            ->where('type', 'Maintainance')
            ->findOrFail($logId);

        $this->logNotesModalKind = 'maintenance';
        $this->logNotesModalPayload = [
            'text' => (string) ($log->notes ?? ''),
        ];
        $this->showLogNotesModal = true;
    }

    public function openCalibrationNotesModal(string $logId): void
    {
        $log = MaintainanceCalibrationLog::query()
            ->where('equipment_id', $this->equipmentId)
            ->where('type', 'Calibration')
            ->findOrFail($logId);

        $this->logNotesModalKind = 'calibration';
        $this->logNotesModalPayload = [
            'text' => (string) ($log->notes ?? ''),
        ];
        $this->showLogNotesModal = true;
    }

    public function openVerificationDetailsModal(string $logId): void
    {
        $log = VerificationLog::query()
            ->where('equipment_id', $this->equipmentId)
            ->where('is_delete', 0)
            ->findOrFail($logId);

        $this->logNotesModalKind = 'verification';
        $this->logNotesModalPayload = [
            'procedure' => (string) ($log->procedure ?? ''),
            'response' => (string) ($log->response ?? ''),
            'remarks' => (string) ($log->remarks ?? ''),
        ];
        $this->showLogNotesModal = true;
    }

    public function closeLogNotesModal(): void
    {
        $this->showLogNotesModal = false;
        $this->logNotesModalKind = '';
        $this->logNotesModalPayload = [];
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromMaintainanceCalibrationLog(MaintainanceCalibrationLog $log, bool $includeCalibrationMetrics): array
    {
        $serviceType = $this->normalizeMaintainanceServiceType($log->maintainance_type);
        $isExternal = $serviceType === 'external';

        $employeeName = '';
        if ($log->employee_id) {
            $employeeName = (string) (User::find($log->employee_id)?->name ?? '');
        }

        $supplierName = '';
        if ($log->supplier_id) {
            $supplierName = (string) (Supplier::find($log->supplier_id)?->name ?? '');
        }

        $dateRaw = $log->date;
        $dateFormatted = $dateRaw
            ? Carbon::parse($dateRaw)->toDateString()
            : '—';

        $preview = [
            'date' => $dateFormatted,
            'is_external' => $isExternal,
            'service_type' => $isExternal ? __('equipment.external') : __('equipment.in_house'),
            'employee_name' => $isExternal ? null : ($employeeName !== '' ? $employeeName : null),
            'supplier_name' => $isExternal ? ($supplierName !== '' ? $supplierName : null) : null,
            'notes' => (string) ($log->notes ?? ''),
        ];

        if ($includeCalibrationMetrics) {
            $preview['correction_factor'] = $this->nullableNumericDisplay($log->correction_factor);
            $preview['uncertainty_of_measure'] = $this->nullableNumericDisplay($log->uncertainty_of_measure);
        }

        return $preview;
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromVerificationLog(VerificationLog $log): array
    {
        $serviceType = $this->normalizeMaintainanceServiceType($log->maintainance_type);
        $isExternal = $serviceType === 'external';

        $operatorName = '';
        if ($log->operator_id) {
            $operatorName = (string) (User::find($log->operator_id)?->name ?? '');
        }

        $supplierName = '';
        if ($log->supplier_id) {
            $supplierName = (string) (Supplier::find($log->supplier_id)?->name ?? '');
        }

        $dateRaw = $log->verification_date;
        $dateFormatted = $dateRaw instanceof Carbon
            ? $dateRaw->format('Y-m-d')
            : ($dateRaw ? Carbon::parse($dateRaw)->toDateString() : '—');

        return [
            'date' => $dateFormatted,
            'is_external' => $isExternal,
            'service_type' => $isExternal ? __('equipment.external') : __('equipment.in_house'),
            'operator_name' => $isExternal ? null : ($operatorName !== '' ? $operatorName : null),
            'supplier_name' => $isExternal ? ($supplierName !== '' ? $supplierName : null) : null,
            'remarks' => (string) ($log->remarks ?? ''),
            'reference_standard' => trim((string) ($log->reference_standard ?? '')),
        ];
    }

    private function normalizeMaintainanceServiceType(?string $raw): string
    {
        $serviceType = $raw ?? 'in_house';
        $serviceType = str_replace('-', '_', (string) $serviceType);
        if (! in_array($serviceType, ['in_house', 'external'], true)) {
            return 'in_house';
        }

        return $serviceType;
    }

    private function nullableNumericDisplay(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    // Operator Management
    public function openOperatorModal(): void
    {
        $this->operatorForm = ['operators' => []];
        $this->showOperatorDropdown = false;
        $this->showOperatorModal = true;
    }

    public function closeOperatorModal(): void
    {
        $this->showOperatorModal = false;
        $this->showOperatorDropdown = false;
        $this->operatorForm = ['operators' => []];
    }

    public function saveOperators(): void
    {
        $this->validate([
            'operatorForm.operators' => 'required|array|min:1',
        ]);

        try {
            foreach ($this->operatorForm['operators'] as $operatorId) {
                EquipmentOperator::firstOrCreate([
                    'user_id' => $operatorId,
                    'equipment_id' => $this->equipmentId,
                ]);
            }
            $this->message = 'Operators added successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
            $this->closeOperatorModal();
        } catch (\Exception $e) {
            $this->message = 'Error adding operators: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function removeOperator($operatorId): void
    {
        try {
            EquipmentOperator::findOrFail($operatorId)->delete();
            $this->message = 'Operator removed successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error removing operator: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    // Attachment Management
    public function showCreateAttachmentModal(): void
    {
        $this->resetAttachmentForm();
        $this->showAttachmentModal = true;
        $this->editingAttachment = null;
    }

    public function showEditAttachmentModal($attachmentId): void
    {
        $attachment = EquipmentAttachment::findOrFail($attachmentId);
        $this->editingAttachment = $attachment;
        $this->attachmentForm = [
            'title' => $attachment->title,
            'description' => $attachment->description ?? '',
        ];
        $this->attachmentFile = null;
        $this->showAttachmentModal = true;
    }

    public function saveAttachment(): void
    {
        $this->validate([
            'attachmentForm.title' => 'required|string|max:255',
            'attachmentFile' => $this->editingAttachment ? 'nullable|file|max:10240' : 'required|file|max:10240',
        ]);

        try {
            $data = [
                'equipment_id' => $this->equipmentId,
                'title' => $this->attachmentForm['title'],
                'description' => $this->attachmentForm['description'],
            ];

            if (!$this->editingAttachment) {
                $data['upload_by'] = auth()->id();
            } else {
                $data['edit_by'] = auth()->id();
            }

            if ($this->attachmentFile) {
                $path = $this->attachmentFile->store('equipmentAttachment', 'public');
                $file = explode('/', $path);
                $data['attachment'] = '/storage/equipmentAttachment/' . urlencode(end($file));
            }

            if ($this->editingAttachment) {
                $this->editingAttachment->update($data);
                $this->message = 'Attachment updated successfully!';
            } else {
                EquipmentAttachment::create($data);
                $this->message = 'Attachment created successfully!';
            }

            $this->messageType = 'success';
            $this->loadEquipment();
            $this->showAttachmentModal = false;
            $this->resetAttachmentForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving attachment: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function openDeleteAttachmentConfirmModal(string $attachmentId): void
    {
        $attachment = EquipmentAttachment::where('equipment_id', $this->equipmentId)
            ->findOrFail($attachmentId);
        $this->pendingDeleteAttachmentId = $attachmentId;
        $this->pendingDeleteAttachmentPreview = $this->previewFromEquipmentAttachment($attachment);
        $this->showDeleteAttachmentConfirmModal = true;
    }

    public function closeDeleteAttachmentConfirmModal(): void
    {
        $this->showDeleteAttachmentConfirmModal = false;
        $this->pendingDeleteAttachmentId = null;
        $this->pendingDeleteAttachmentPreview = null;
    }

    public function confirmDeleteAttachment(): void
    {
        if ($this->pendingDeleteAttachmentId === null) {
            return;
        }

        try {
            EquipmentAttachment::where('equipment_id', $this->equipmentId)
                ->findOrFail($this->pendingDeleteAttachmentId)
                ->delete();
            $this->message = 'Attachment deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting attachment: ' . $e->getMessage();
            $this->messageType = 'danger';
        } finally {
            $this->closeDeleteAttachmentConfirmModal();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromEquipmentAttachment(EquipmentAttachment $attachment): array
    {
        $uploaderName = '';
        if ($attachment->upload_by) {
            $uploaderName = (string) (User::find($attachment->upload_by)?->name ?? '');
        }

        $uploadedAt = $attachment->created_at
            ? Carbon::parse($attachment->created_at)->toDateString()
            : '—';

        $filePath = (string) ($attachment->attachment ?? '');
        $fileName = $filePath !== '' ? basename(urldecode($filePath)) : null;

        return [
            'title' => (string) ($attachment->title ?? ''),
            'uploaded_at' => $uploadedAt,
            'uploaded_by' => $uploaderName !== '' ? $uploaderName : null,
            'description' => (string) ($attachment->description ?? ''),
            'file_name' => $fileName,
        ];
    }

    // Notification Management
    public function showCreateNotificationModal(): void
    {
        $this->resetNotificationForm();
        $this->showNotificationModal = true;
        $this->editingNotification = null;
    }

    public function showEditNotificationModal(string $notificationId): void
    {
        $notification = EquipmentNotifications::where('equipment_id', $this->equipmentId)
            ->findOrFail($notificationId);
        $this->editingNotification = $notification;
        $this->notificationForm = [
            'value' => $notification->value,
            'frequency' => $notification->frequency,
            'notification_type' => $notification->notification_type,
        ];

        $this->selectedNotificationTypeLabel = match ($notification->notification_type) {
            'calibration' => 'Calibration',
            'maintanance' => 'Maintenance',
            'verification' => 'Verification',
            default => ucwords((string) $notification->notification_type),
        };

        $this->selectedNotificationFrequencyLabel = match ($notification->frequency) {
            'days' => 'Days',
            'weeks' => 'Weeks',
            'months' => 'Months',
            default => ucwords((string) $notification->frequency),
        };

        $this->showNotificationTypeDropdown = false;
        $this->showNotificationFrequencyDropdown = false;
        $this->showNotificationModal = true;
    }

    public function saveNotification(): void
    {
        $this->validate([
            'notificationForm.value' => 'required|integer|min:1',
            'notificationForm.frequency' => 'required|string',
            'notificationForm.notification_type' => 'required|string|in:calibration,maintanance,verification',
        ]);

        try {
            $equipment = $this->equipment;
            $type = $this->notificationForm['notification_type'];
            $value = (int)$this->notificationForm['value'];

            // Validation based on type
            if ($type === 'calibration') {
                if (!$equipment->calibration_days) {
                    throw new \Exception('Kindly set equipment calibration days');
                }
                if ($equipment->calibration_days < $value) {
                    throw new \Exception('Ensure the notification days is less than the equipment calibration days');
                }
                $daysToAdd = $equipment->calibration_days - $value;
                $lastLog = MaintainanceCalibrationLog::where('equipment_id', $this->equipmentId)
                    ->where('type', 'Calibration')
                    ->orderBy('id', 'DESC')
                    ->first();
                $baseDate = $lastLog ? Carbon::parse($lastLog->date) : Carbon::parse($equipment->date_purchased);
            } elseif ($type === 'maintanance') {
                if (!$equipment->maintainance_days) {
                    throw new \Exception('Kindly set equipment maintainance days');
                }
                if ($equipment->maintainance_days < $value) {
                    throw new \Exception('Ensure the notification days is less than the equipment maintainance days');
                }
                $daysToAdd = $equipment->maintainance_days - $value;
                $lastLog = MaintainanceCalibrationLog::where('equipment_id', $this->equipmentId)
                    ->where('type', 'Maintainance')
                    ->orderBy('id', 'DESC')
                    ->first();
                $baseDate = $lastLog ? Carbon::parse($lastLog->date) : Carbon::parse($equipment->date_purchased);
            } else { // verification
                if (!$equipment->verification_days) {
                    throw new \Exception('Kindly set equipment verification days');
                }
                if ($equipment->verification_days < $value) {
                    throw new \Exception('Ensure the notification days is less than the equipment verification days');
                }
                $daysToAdd = $equipment->verification_days - $value;
                $lastLog = VerificationLog::where('equipment_id', $this->equipmentId)
                    ->orderBy('id', 'DESC')
                    ->first();
                $baseDate = $lastLog ? Carbon::parse($lastLog->verification_date) : Carbon::parse($equipment->date_purchased);
            }

            $data = [
                'equipment_id' => $this->equipmentId,
                'value' => $this->notificationForm['value'],
                'frequency' => $this->notificationForm['frequency'],
                'notification_type' => $type,
                'next_date' => $baseDate->addDays($daysToAdd)->toDateString(),
            ];

            if ($this->editingNotification) {
                $this->editingNotification->update($data);
                $this->message = 'Notification updated successfully!';
            } else {
                EquipmentNotifications::create($data);
                $this->message = 'Notification created successfully!';
            }

            $this->messageType = 'success';
            $this->loadEquipment();
            $this->showNotificationModal = false;
            $this->resetNotificationForm();
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function openDeleteNotificationConfirmModal(string $notificationId): void
    {
        $notification = EquipmentNotifications::where('equipment_id', $this->equipmentId)
            ->findOrFail($notificationId);
        $this->pendingDeleteNotificationId = $notificationId;
        $this->pendingDeleteNotificationPreview = $this->previewFromEquipmentNotification($notification);
        $this->showDeleteNotificationConfirmModal = true;
    }

    public function closeDeleteNotificationConfirmModal(): void
    {
        $this->showDeleteNotificationConfirmModal = false;
        $this->pendingDeleteNotificationId = null;
        $this->pendingDeleteNotificationPreview = null;
    }

    public function confirmDeleteNotification(): void
    {
        if ($this->pendingDeleteNotificationId === null) {
            return;
        }

        try {
            EquipmentNotifications::where('equipment_id', $this->equipmentId)
                ->findOrFail($this->pendingDeleteNotificationId)
                ->delete();
            $this->message = 'Notification deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting notification: ' . $e->getMessage();
            $this->messageType = 'danger';
        } finally {
            $this->closeDeleteNotificationConfirmModal();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromEquipmentNotification(EquipmentNotifications $notification): array
    {
        $typeLabel = match ($notification->notification_type) {
            'calibration' => 'Calibration',
            'maintanance' => 'Maintenance',
            'verification' => 'Verification',
            default => ucwords((string) $notification->notification_type),
        };

        return [
            'notification_type' => $typeLabel,
            'frequency_display' => trim($notification->value . ' ' . $notification->frequency),
            'next_date' => (string) ($notification->next_date ?? '—'),
            'status' => $notification->is_sent ? __('equipment.sent') : __('equipment.not_sent'),
        ];
    }

    // Reset Methods
    public function resetMaintenanceForm(): void
    {
        $this->maintenanceForm = [
            'date' => '',
            'description' => '',
            'reference_number' => '',
            'maintainance_type' => 'in_house',
            'employee_id' => null,
            'supplier_id' => null,
            'notes' => '',
        ];
        $this->certificate = null;
        $this->editingLog = null;
        $this->showEmployeeDropdown = false;
        $this->showSupplierDropdown = false;
        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        $this->selectedSupplierName = '';
        $this->supplierSearch = '';
    }

    public function resetCalibrationForm(): void
    {
        $this->calibrationForm = [
            'date' => '',
            'description' => '',
            'reference_number' => '',
            'correction_factor' => '',
            'uncertainty_of_measure' => '',
            'maintainance_type' => 'in_house',
            'employee_id' => null,
            'supplier_id' => null,
            'notes' => '',
        ];
        $this->certificate = null;
        $this->editingLog = null;
        $this->showEmployeeDropdown = false;
        $this->showSupplierDropdown = false;
        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        $this->selectedSupplierName = '';
        $this->supplierSearch = '';
    }

    public function resetVerificationForm(): void
    {
        $this->verificationForm = [
            'verification_date' => '',
            'reference_standard' => '',
            'procedure' => '',
            'response' => '',
            'remarks' => '',
            'maintainance_type' => 'in_house',
            'operator_id' => null,
            'supplier_id' => null,
        ];
        $this->editingLog = null;
        $this->showEmployeeDropdown = false;
        $this->showSupplierDropdown = false;
        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
        $this->selectedSupplierName = '';
        $this->supplierSearch = '';
    }

    public function resetAttachmentForm(): void
    {
        $this->attachmentForm = [
            'title' => '',
            'description' => '',
        ];
        $this->attachmentFile = null;
        $this->editingAttachment = null;
    }

    public function resetNotificationForm(): void
    {
        $this->notificationForm = [
            'value' => '',
            'frequency' => 'days',
            'notification_type' => 'calibration',
        ];
        $this->editingNotification = null;
        $this->selectedNotificationTypeLabel = '';
        $this->selectedNotificationFrequencyLabel = '';
        $this->showNotificationTypeDropdown = false;
        $this->showNotificationFrequencyDropdown = false;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    // Searchable Select Methods (similar to EquipmentManager)
    public function searchEmployees(): void
    {
        $this->showEmployeeDropdown = true;
        $search = $this->employeeSearch;

        $this->filteredEmployees = User::query()
            ->where('active', 1)
            ->where('company_id', getUserCompany())
            ->where('is_support_staff', 0)
            ->where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('location_id', getCurrentUserLocation()->id)
            ->where('name', 'like', '%' . $search . '%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectEmployee($id, $name = null): void
    {
        if ($name === null) {
            $employee = User::find($id);
            $name = $employee?->name ?? '';
        }

        if ($this->showEditModal) {
            $this->equipmentForm['assigned_employee_id'] = $id;
            $this->selectedEmployeeName = $name;
            $this->employeeSearch = '';
            $this->showEmployeeDropdown = false;

            return;
        }

        $this->maintenanceForm['employee_id'] = $id;
        $this->calibrationForm['employee_id'] = $id;
        $this->verificationForm['operator_id'] = $id;
        $this->selectedEmployeeName = $name;
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = false;
    }

    public function clearEmployee(): void
    {
        if (! $this->showEditModal) {
            return;
        }

        $this->equipmentForm['assigned_employee_id'] = null;
        $this->selectedEmployeeName = '';
        $this->employeeSearch = '';
    }

    public function searchSuppliers(): void
    {
        $this->showSupplierDropdown = true;
        $search = $this->supplierSearch;
        $this->filteredSuppliers = Supplier::where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectSupplier($id, $name): void
    {
        $this->maintenanceForm['supplier_id'] = $id;
        $this->calibrationForm['supplier_id'] = $id;
        $this->verificationForm['supplier_id'] = $id;
        $this->selectedSupplierName = $name;
        $this->supplierSearch = $name;
        $this->showSupplierDropdown = false;
    }

    public function searchDepartments(): void
    {
        $this->showDepartmentDropdown = true;
        $search = $this->departmentSearch;
        $this->filteredDepartments = InventoryDepartment::where('module', 'organizational')
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function updatedDepartmentSearch(): void
    {
        if ($this->departmentSearch === '') {
            $this->filteredDepartments = [];
            return;
        }

        $this->searchDepartments();
    }

    public function selectDepartment($id, $name = null): void
    {
        if ($name === null) {
            $department = InventoryDepartment::find($id);
            $name = $department?->name ?? '';
        }

        if ($this->showEditModal) {
            $this->equipmentForm['assigned_department'] = $id;
            $this->selectedDepartmentName = $name;
            $this->departmentSearch = '';
            $this->showDepartmentDropdown = false;

            return;
        }

        $this->equipmentForm['assigned_department'] = $id;
        $this->selectedDepartmentName = $name;
        $this->departmentSearch = $name;
        $this->showDepartmentDropdown = false;
    }

    public function clearDepartment(): void
    {
        if (! $this->showEditModal) {
            return;
        }

        $this->equipmentForm['assigned_department'] = null;
        $this->selectedDepartmentName = '';
        $this->departmentSearch = '';
    }

    public function selectStatus(string $value): void
    {
        $this->equipmentForm['status'] = $value;
        $this->selectedStatusName = $value;
        $this->showStatusDropdown = false;
    }

    public function selectDailyLogFrequency(int $value, string $label): void
    {
        $this->equipmentForm['daily_log_frequency'] = $value;
        $this->selectedDailyLogFrequencyLabel = $label;
        $this->showDailyLogFrequencyDropdown = false;
        $this->updatedEquipmentFormDailyLogFrequency();
    }

    public function selectDailyLogValueType(string $value, string $label): void
    {
        $this->equipmentForm['daily_log_value_type'] = $value;
        $this->selectedDailyLogValueTypeLabel = $label;
        $this->showDailyLogValueTypeDropdown = false;
        $this->updatedEquipmentFormDailyLogValueType();
    }

    public function selectDailyLogNature(string $value, string $label): void
    {
        $this->equipmentForm['daily_log_nature'] = $value;
        $this->selectedDailyLogNatureLabel = $label;
        $this->showDailyLogNatureDropdown = false;
        $this->updatedEquipmentFormDailyLogNature();
    }

    public function selectNotificationType(string $value, string $label): void
    {
        $this->notificationForm['notification_type'] = $value;
        $this->selectedNotificationTypeLabel = $label;
        $this->showNotificationTypeDropdown = false;
    }

    public function selectNotificationFrequency(string $value, string $label): void
    {
        $this->notificationForm['frequency'] = $value;
        $this->selectedNotificationFrequencyLabel = $label;
        $this->showNotificationFrequencyDropdown = false;
    }

    public function toggleOperatorSelection(int|string $employeeId): void
    {
        $operatorIds = $this->operatorForm['operators'] ?? [];

        if (in_array($employeeId, $operatorIds)) {
            $this->operatorForm['operators'] = array_values(array_filter(
                $operatorIds,
                fn ($id) => (string) $id !== (string) $employeeId
            ));
            return;
        }

        $this->operatorForm['operators'][] = $employeeId;
    }

    public function render()
    {
        return view('livewire.equipment.equipment-detail');
    }

    // -------------------------------------------------------------------------
    // Daily Log Analytics
    // -------------------------------------------------------------------------

    public function getDailyLogChartDataProperty(): array
    {
        if (!$this->equipment || !$this->equipment->requires_daily_log) {
            return [];
        }

        $entries = EquipmentDailyLogEntry::where('equipment_id', $this->equipmentId)
            ->where('log_date', '>=', now()->subDays(59)->toDateString())
            ->orderBy('log_date')
            ->orderBy('slot_number')
            ->get();

        if ($entries->isEmpty()) {
            return [];
        }

        $freq   = (int) ($this->equipment->daily_log_frequency ?? 1);
        $type   = $this->equipment->daily_log_value_type;
        $labels = [];
        $values = [];
        $legacyMins   = [];
        $legacyMaxs   = [];
        $legacyMeans  = [];
        $withinFlags  = [];

        foreach ($entries as $entry) {
            $label    = $freq > 1
                ? Carbon::parse($entry->log_date)->format('M j') . ' #' . $entry->slot_number
                : Carbon::parse($entry->log_date)->format('M j');
            $labels[] = $label;

            if ($type === 'range') {
                $parsed = $this->parseRangeRecordedValue($entry->recorded_value);
                $reading = $parsed['reading'];
                $values[] = $reading;
                $legacyMins[] = $parsed['min'];
                $legacyMaxs[] = $parsed['max'];
                $legacyMeans[] = $parsed['mean'];

                if ($reading !== null
                    && $this->equipment->daily_log_expected_min !== null
                    && $this->equipment->daily_log_expected_max !== null) {
                    $withinFlags[] = $reading >= (float) $this->equipment->daily_log_expected_min
                        && $reading <= (float) $this->equipment->daily_log_expected_max;
                } else {
                    $withinFlags[] = null;
                }
            } else {
                $values[] = is_numeric($entry->recorded_value) ? (float) $entry->recorded_value : null;
            }
        }

        $data = [
            'type'   => $type,
            'nature' => $this->equipment->daily_log_nature,
            'unit'   => $this->equipment->daily_log_reporting_unit ?? '',
            'labels' => $labels,
        ];

        if ($type === 'range') {
            $data['values']      = $values;
            $data['withinFlags'] = $withinFlags;
            $data['expectedMin'] = (float) $this->equipment->daily_log_expected_min;
            $data['expectedMax'] = (float) $this->equipment->daily_log_expected_max;
            // Backward-compatibility datasets for historical min-max style entries
            $data['legacyMins']  = $legacyMins;
            $data['legacyMaxs']  = $legacyMaxs;
            $data['legacyMeans'] = $legacyMeans;
        } else {
            $data['values']        = $values;
            $data['expectedValue'] = $this->equipment->daily_log_expected_value;
            $tol = (int) ($this->equipment->daily_log_tolerance ?? 0);
            if ($this->equipment->daily_log_nature === 'quantitative'
                && is_numeric($this->equipment->daily_log_expected_value)
                && $tol > 0) {
                $ev              = (float) $this->equipment->daily_log_expected_value;
                $data['tolHigh'] = round($ev * (1 + $tol / 100), 4);
                $data['tolLow']  = round($ev * (1 - $tol / 100), 4);
            }
        }

        return $data;
    }

    public function getNonConformanceReportProperty(): array
    {
        if (!$this->equipment || !$this->equipment->requires_daily_log) {
            return [];
        }

        if (!empty($this->nonConformanceFromDate) && !empty($this->nonConformanceToDate)
            && $this->nonConformanceFromDate > $this->nonConformanceToDate) {
            return [];
        }

        $entriesQuery = EquipmentDailyLogEntry::where('equipment_id', $this->equipmentId)
            ->whereNotNull('recorded_value')
            ->where('recorded_value', '!=', '')
            ->orderBy('log_date', 'desc')
            ->orderBy('slot_number');

        if (!empty($this->nonConformanceFromDate)) {
            $entriesQuery->whereDate('log_date', '>=', $this->nonConformanceFromDate);
        }

        if (!empty($this->nonConformanceToDate)) {
            $entriesQuery->whereDate('log_date', '<=', $this->nonConformanceToDate);
        }

        $entries = $entriesQuery->get();

        $type   = $this->equipment->daily_log_value_type;
        $nature = $this->equipment->daily_log_nature;
        $tol    = (int) ($this->equipment->daily_log_tolerance ?? 0);
        $rows   = [];

        foreach ($entries as $entry) {
            $fail   = false;
            $reason = '';

            if ($type === 'constant') {
                if ($nature === 'qualitative') {
                    $expected = $this->equipment->daily_log_expected_value;
                    if (strtolower(trim($entry->recorded_value)) !== strtolower(trim($expected))) {
                        $fail   = true;
                        $reason = 'Does not match expected "' . $expected . '"';
                    }
                } elseif ($nature === 'quantitative' && is_numeric($entry->recorded_value)) {
                    $ev  = (float) $this->equipment->daily_log_expected_value;
                    $val = (float) $entry->recorded_value;
                    if ($tol > 0) {
                        $lo = $ev * (1 - $tol / 100);
                        $hi = $ev * (1 + $tol / 100);
                        if ($val < $lo || $val > $hi) {
                            $fail   = true;
                            $reason = 'Value ' . $val . ' outside tolerance ±' . $tol . '% of ' . $ev;
                        }
                    } else {
                        if ($val != $ev) {
                            $fail   = true;
                            $reason = 'Recorded ' . $val . ', expected ' . $ev;
                        }
                    }
                }
            } elseif ($type === 'range') {
                $parsed = $this->parseRangeRecordedValue($entry->recorded_value);
                $reading = $parsed['reading'];
                $eMin  = (float) $this->equipment->daily_log_expected_min;
                $eMax  = (float) $this->equipment->daily_log_expected_max;
                $loLim = $tol > 0 ? $eMin * (1 - $tol / 100) : $eMin;
                $hiLim = $tol > 0 ? $eMax * (1 + $tol / 100) : $eMax;

                if ($reading !== null && ($reading < $loLim || $reading > $hiLim)) {
                    $fail   = true;
                    $reason = 'Recorded ' . $reading . ' outside '
                        . $eMin . '–' . $eMax
                        . ($tol > 0 ? ' (±' . $tol . '% tol)' : '');
                }
            }

            if ($fail) {
                $rows[] = [
                    'date'     => Carbon::parse($entry->log_date)->format('D, M j Y'),
                    'slot'     => $entry->slot_number,
                    'recorded' => $entry->recorded_value,
                    'reason'   => $reason,
                ];
            }
        }

        return $rows;
    }

    public function getNonConformanceReportPageProperty(): array
    {
        $rows = $this->nonConformanceReport;
        $perPage = max(10, (int) $this->nonConformancePerPage);
        $totalPages = max(1, (int) ceil(count($rows) / $perPage));
        $currentPage = min(max(1, (int) $this->nonConformancePage), $totalPages);

        if ($currentPage !== (int) $this->nonConformancePage) {
            $this->nonConformancePage = $currentPage;
        }

        return array_slice($rows, ($currentPage - 1) * $perPage, $perPage);
    }

    public function getNonConformanceTotalPagesProperty(): int
    {
        $perPage = max(10, (int) $this->nonConformancePerPage);

        return max(1, (int) ceil(count($this->nonConformanceReport) / $perPage));
    }

    public function exportNonConformanceReport()
    {
        $rows    = $this->nonConformanceReport;
        $headers = ['Date', 'Slot #', 'Recorded Value', 'Reason'];
        $data    = array_map(fn ($r) => [
            $r['date'],
            $r['slot'],
            $r['recorded'],
            $r['reason'],
        ], $rows);

        $name = $this->equipment->name ?? 'equipment';

        return Excel::download(
            new class($headers, $data) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(
                    private array $headers,
                    private array $data,
                ) {}

                public function headings(): array { return $this->headers; }
                public function array(): array    { return $this->data; }
            },
            'non-conformance-' . Str::slug($name) . '.xlsx'
        );
    }

    private function parseRangeRecordedValue(?string $raw): array
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return ['reading' => null, 'min' => null, 'max' => null, 'mean' => null];
        }

        if (is_numeric($raw)) {
            $reading = (float) $raw;

            return ['reading' => $reading, 'min' => null, 'max' => null, 'mean' => null];
        }

        $parts = preg_split('/\s*[–-]\s*/', $raw);
        $min = is_numeric($parts[0] ?? null) ? (float) $parts[0] : null;
        $max = is_numeric($parts[1] ?? null) ? (float) $parts[1] : null;
        $mean = ($min !== null && $max !== null) ? round(($min + $max) / 2, 4) : null;

        return [
            'reading' => $mean,
            'min' => $min,
            'max' => $max,
            'mean' => $mean,
        ];
    }

    public function getMaintenanceLogsProperty()
    {
        return MaintainanceCalibrationLog::where('equipment_id', $this->equipmentId)
            ->where('type', 'Maintainance')
            ->when($this->maintenanceSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->maintenanceSearch . '%')
                      ->orWhere('reference_number', 'like', '%' . $this->maintenanceSearch . '%')
                      ->orWhere('notes', 'like', '%' . $this->maintenanceSearch . '%');
                });
            })
            ->orderBy('date', 'desc')
            ->paginate(25, ['*'], 'maintenancePage');
    }

    public function getCalibrationLogsProperty()
    {
        return MaintainanceCalibrationLog::where('equipment_id', $this->equipmentId)
            ->where('type', 'Calibration')
            ->when($this->calibrationSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->calibrationSearch . '%')
                      ->orWhere('reference_number', 'like', '%' . $this->calibrationSearch . '%')
                      ->orWhere('notes', 'like', '%' . $this->calibrationSearch . '%');
                });
            })
            ->orderBy('date', 'desc')
            ->paginate(25, ['*'], 'calibrationPage');
    }

    public function getVerificationLogsProperty()
    {
        return VerificationLog::where('equipment_id', $this->equipmentId)
            ->where('is_delete', 0)
            ->when($this->verificationSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference_standard', 'like', '%' . $this->verificationSearch . '%')
                      ->orWhere('procedure', 'like', '%' . $this->verificationSearch . '%')
                      ->orWhere('remarks', 'like', '%' . $this->verificationSearch . '%')
                      ->orWhere('response', 'like', '%' . $this->verificationSearch . '%');
                });
            })
            ->orderBy('verification_date', 'desc')
            ->paginate(25, ['*'], 'verificationPage');
    }

    public function getAttachmentsProperty()
    {
        return \App\EquipmentAttachment::where('equipment_id', $this->equipmentId)
            ->when($this->attachmentSearch, function ($query) {
                $query->where('title', 'like', '%' . $this->attachmentSearch . '%')
                      ->orWhere('description', 'like', '%' . $this->attachmentSearch . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(25, ['*'], 'attachmentPage');
    }

    public function getNotificationsProperty()
    {
        return EquipmentNotifications::where('equipment_id', $this->equipmentId)
            ->orderBy('next_date', 'asc')
            ->get();
    }

    /**
     * @return list<array{key: string, icon: string, title: string, fields: list<array<string, mixed>>}>
     */
    public function getEquipmentDetailSectionsProperty(): array
    {
        $equipment = $this->equipment;
        if (! $equipment) {
            return [];
        }

        $calibration = $equipment->calibration_date();
        $maintenance = $equipment->maintainance_date();

        $departmentName = '—';
        if ($equipment->assigned_department) {
            $department = getInventoryDepartmentByid($equipment->assigned_department);
            $departmentName = $department->name ?? '—';
        }

        $assignedEmployeeName = '—';
        if ($equipment->assigned_employee_id) {
            $assignedEmployeeName = (string) (User::find($equipment->assigned_employee_id)?->name ?? '—');
        }

        $assetTypeLabel = '—';
        if ($equipment->asset_type_id) {
            $assetType = AssetType::find($equipment->asset_type_id);
            $assetTypeLabel = $assetType
                ? trim(($assetType->asset_code ?? '') . ($assetType->descripton ? ' (' . $assetType->descripton . ')' : ''))
                : '—';
        }

        $assetLocationLabel = '—';
        if ($equipment->asset_location_id) {
            $assetLocationLabel = (string) (AssetLocation::find($equipment->asset_location_id)?->name ?? '—');
        }

        $verificationDays = $equipment->verification_days ?? $equipment->verificaction_days ?? null;

        $sections = [
            [
                'key' => 'basic',
                'icon' => 'mdi-card-text-outline',
                'title' => __('equipment.basic_information'),
                'fields' => [
                    ['label' => __('equipment.name'), 'value' => $equipment->name, 'highlight' => true, 'wide' => true],
                    ['label' => __('equipment.equipment_number'), 'value' => $equipment->equipment_number, 'mono' => true],
                    ['label' => __('equipment.description'), 'value' => $equipment->description, 'wide' => true, 'multiline' => true],
                    [
                        'label' => __('equipment.status'),
                        'value' => $equipment->status ?? '—',
                        'badge' => true,
                        'badge_class' => ($equipment->active ?? false) ? 'eq-details-badge--success' : 'eq-details-badge--muted',
                    ],
                    [
                        'label' => __('equipment.active_status'),
                        'value' => ($equipment->active ?? false) ? __('equipment.active') : __('equipment.inactive'),
                        'badge' => true,
                        'badge_class' => ($equipment->active ?? false) ? 'eq-details-badge--success' : 'eq-details-badge--warning',
                    ],
                ],
            ],
            [
                'key' => 'specifications',
                'icon' => 'mdi-cog-outline',
                'title' => __('equipment.specifications'),
                'fields' => [
                    ['label' => __('equipment.make'), 'value' => $equipment->make],
                    ['label' => __('equipment.model'), 'value' => $equipment->model],
                    ['label' => __('equipment.serial_number'), 'value' => $equipment->serial_number],
                    ['label' => __('equipment.barcode_number'), 'value' => $equipment->barcode_number],
                    ['label' => __('equipment.manufacturer'), 'value' => $equipment->manufacturer],
                    ['label' => __('equipment.condition'), 'value' => $equipment->condition],
                    ['label' => __('equipment.warranty_date'), 'value' => $this->formatEquipmentDetailDate($equipment->warranty_date)],
                    ['label' => __('equipment.date_purchased'), 'value' => $this->formatEquipmentDetailDate($equipment->date_purchased)],
                ],
            ],
            [
                'key' => 'procurement_technical',
                'icon' => 'mdi-cash-multiple',
                'title' => 'Procurement & Technical Specs',
                'fields' => [
                    ['label' => 'Purchase Price', 'value' => $equipment->purchase_price ? number_format($equipment->purchase_price, 2) : '—'],
                    ['label' => 'Installation Date', 'value' => $this->formatEquipmentDetailDate($equipment->installation_date)],
                    ['label' => 'Commissioning Date', 'value' => $this->formatEquipmentDetailDate($equipment->commissioning_date)],
                    ['label' => 'Detection Limit', 'value' => $equipment->detection_limit ?? '—'],
                    ['label' => 'Tolerance Limit', 'value' => $equipment->tolerance_limit ?? '—'],
                    ['label' => 'Supplier Name', 'value' => $equipment->supplier_name ?? '—'],
                    ['label' => 'Warranty Duration', 'value' => $equipment->warranty ?? '—'],
                    ['label' => 'Operating Environment', 'value' => $equipment->environment ?? '—'],
                    ['label' => 'End of Life Date', 'value' => $this->formatEquipmentDetailDate($equipment->end_of_life)],
                    ['label' => 'End of Service Date', 'value' => $this->formatEquipmentDetailDate($equipment->end_of_service)],
                ],
            ],
            [
                'key' => 'assignment',
                'icon' => 'mdi-map-marker-outline',
                'title' => __('equipment.assignment_location'),
                'fields' => [
                    ['label' => __('equipment.department'), 'value' => $departmentName],
                    ['label' => __('equipment.employee'), 'value' => $assignedEmployeeName],
                    ['label' => __('equipment.asset_types'), 'value' => $assetTypeLabel, 'wide' => true],
                    ['label' => __('equipment.asset_locations'), 'value' => $assetLocationLabel, 'wide' => true],
                ],
            ],
            [
                'key' => 'schedule',
                'icon' => 'mdi-calendar-clock',
                'title' => __('equipment.maintenance_calibration_schedule'),
                'fields' => [
                    ['label' => __('equipment.maintenance_after_days'), 'value' => $this->formatEquipmentDetailNumber($equipment->maintainance_days, 'days')],
                    ['label' => __('equipment.maintenance_notification_days'), 'value' => $this->formatEquipmentDetailNumber($equipment->maintainance_notification_in_days, 'days')],
                    ['label' => __('equipment.next_maintenance'), 'value' => $maintenance['date']->toDateString(), 'badge' => true, 'badge_class' => $this->mapEquipmentScheduleBadgeClass($maintenance['status'])],
                    ['label' => __('equipment.calibration_days'), 'value' => $this->formatEquipmentDetailNumber($equipment->calibration_days, 'days')],
                    ['label' => __('equipment.calibration_notification_days'), 'value' => $this->formatEquipmentDetailNumber($equipment->calibration_notification_in_days, 'days')],
                    ['label' => __('equipment.next_calibration'), 'value' => $calibration['date']->toDateString(), 'badge' => true, 'badge_class' => $this->mapEquipmentScheduleBadgeClass($calibration['status'])],
                    ['label' => __('equipment.preventive_maintainance_period'), 'value' => $this->formatEquipmentDetailNumber($equipment->preventive_maintainance_period, 'days')],
                    ['label' => __('equipment.preventive_maintainance_notification_days'), 'value' => $this->formatEquipmentDetailNumber($equipment->preventive_maintainance_notification_days, 'days')],
                ],
            ],
        ];

        if ($verificationDays !== null || $equipment->verification_notification_in_days !== null) {
            $sections[] = [
                'key' => 'verification',
                'icon' => 'mdi-clipboard-check-outline',
                'title' => __('equipment.verification_log'),
                'fields' => array_filter([
                    $verificationDays !== null
                        ? ['label' => __('equipment.verification_interval_days'), 'value' => $this->formatEquipmentDetailNumber($verificationDays, 'days')]
                        : null,
                    $equipment->verification_notification_in_days !== null
                        ? ['label' => __('equipment.verification_notification_days'), 'value' => $this->formatEquipmentDetailNumber($equipment->verification_notification_in_days, 'days')]
                        : null,
                ]),
            ];
        }

        if ($equipment->requires_daily_log) {
            $sections[] = [
                'key' => 'daily_log',
                'icon' => 'mdi-notebook-check-outline',
                'title' => __('equipment.daily_log_config'),
                'fields' => $this->buildDailyLogDetailFields($equipment),
            ];
        }

        if ($equipment->is_disposal || $equipment->dispose_date || $equipment->comment) {
            $disposeEmployee = '—';
            if ($equipment->employee_dispose_id) {
                $disposeEmployee = (string) (User::find($equipment->employee_dispose_id)?->name ?? '—');
            }

            $sections[] = [
                'key' => 'disposal',
                'icon' => 'mdi-archive-alert-outline',
                'title' => __('equipment.disposal_management'),
                'fields' => [
                    [
                        'label' => __('equipment.disposed'),
                        'value' => $equipment->is_disposal ? __('equipment.yes') : 'No',
                        'badge' => true,
                        'badge_class' => $equipment->is_disposal ? 'eq-details-badge--warning' : 'eq-details-badge--muted',
                    ],
                    ['label' => __('equipment.dispose_date'), 'value' => $this->formatEquipmentDetailDate($equipment->dispose_date)],
                    ['label' => __('equipment.requested_by'), 'value' => $disposeEmployee],
                    ['label' => __('equipment.comment'), 'value' => $equipment->comment, 'wide' => true, 'multiline' => true],
                ],
            ];
        }

        $sections[] = [
            'key' => 'record',
            'icon' => 'mdi-history',
            'title' => __('equipment.record_metadata'),
            'fields' => [
                ['label' => __('equipment.created_at'), 'value' => $this->formatEquipmentDetailDateTime($equipment->created_at)],
                ['label' => __('equipment.last_updated'), 'value' => $this->formatEquipmentDetailDateTime($equipment->updated_at)],
            ],
        ];

        return $sections;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildDailyLogDetailFields(Equipment $equipment): array
    {
        $freqLabels = [
            1 => __('equipment.once_a_day'),
            2 => __('equipment.twice_a_day'),
            3 => __('equipment.three_times_a_day'),
            4 => __('equipment.four_times_a_day'),
            5 => __('equipment.five_times_a_day'),
            6 => __('equipment.six_times_a_day'),
        ];
        $equipFreq = (int) ($equipment->daily_log_frequency ?? 1);

        $natureLabel = match ($equipment->daily_log_nature) {
            'qualitative' => __('equipment.qualitative'),
            'quantitative' => __('equipment.quantitative'),
            default => '—',
        };

        $typeLabel = match ($equipment->daily_log_value_type) {
            'constant' => __('equipment.constant'),
            'range' => __('equipment.range'),
            default => '—',
        };

        $fields = [
            [
                'label' => __('equipment.requires_daily_log'),
                'value' => __('equipment.yes'),
                'badge' => true,
                'badge_class' => 'eq-details-badge--info',
            ],
            ['label' => __('equipment.value_type'), 'value' => $typeLabel],
            ['label' => __('equipment.nature_of_results'), 'value' => $natureLabel],
            ['label' => __('equipment.reporting_unit'), 'value' => $equipment->daily_log_reporting_unit],
            ['label' => __('equipment.logging_frequency'), 'value' => $freqLabels[$equipFreq] ?? __('equipment.once_a_day')],
        ];

        if ($equipFreq >= 2 && $equipment->daily_log_time_interval) {
            $fields[] = ['label' => __('equipment.time_interval_hours'), 'value' => $equipment->daily_log_time_interval . ' h'];
        }

        if ($equipment->daily_log_monitored_by_another_equipment) {
            $monitoredLabel = '—';
            if ($equipment->daily_log_monitored_equipment_id) {
                $monitored = Equipment::find($equipment->daily_log_monitored_equipment_id);
                $monitoredLabel = $monitored
                    ? (($monitored->equipment_number ?? 'N/A') . ' — ' . ($monitored->name ?? ''))
                    : '—';
            }
            $fields[] = ['label' => 'Monitored by equipment', 'value' => $monitoredLabel, 'wide' => true];
        }

        if ($equipment->daily_log_value_type === 'constant') {
            $fields[] = ['label' => __('equipment.expected_value'), 'value' => $equipment->daily_log_expected_value];
            if ($equipment->daily_log_nature === 'quantitative') {
                $fields[] = [
                    'label' => 'Tolerance',
                    'value' => $equipment->daily_log_tolerance !== null ? '±' . $equipment->daily_log_tolerance . '%' : null,
                ];
            }
        }

        if ($equipment->daily_log_value_type === 'range') {
            $fields[] = [
                'label' => __('equipment.acceptable_range'),
                'value' => $equipment->daily_log_expected_min !== null && $equipment->daily_log_expected_max !== null
                    ? $equipment->daily_log_expected_min . ' – ' . $equipment->daily_log_expected_max
                    : null,
            ];
            $fields[] = [
                'label' => 'Tolerance',
                'value' => $equipment->daily_log_tolerance !== null ? '±' . $equipment->daily_log_tolerance . '%' : null,
            ];
        }

        return $fields;
    }

    private function formatEquipmentDetailDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatEquipmentDetailDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('M j, Y g:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatEquipmentDetailNumber(mixed $value, string $suffix = ''): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $formatted = is_numeric($value) ? number_format((float) $value, 0) : (string) $value;

        return $suffix !== '' ? trim($formatted . ' ' . $suffix) : $formatted;
    }

    private function mapEquipmentScheduleBadgeClass(string $status): string
    {
        return match ($status) {
            'badge-success' => 'eq-details-badge--success',
            'badge-warning' => 'eq-details-badge--warning',
            'badge-danger' => 'eq-details-badge--danger',
            default => 'eq-details-badge--muted',
        };
    }

    // Accessory CRUD
    public function showAddAccessoryModal(): void
    {
        $this->accessoryForm = [
            'id' => '',
            'name' => '',
            'description' => '',
            'serial_number' => '',
            'part_number' => '',
        ];
        $this->showAccessoryModal = true;
    }

    public function editAccessory(string $id): void
    {
        $accessory = EquipmentAccessory::findOrFail($id);
        $this->accessoryForm = [
            'id' => $accessory->id,
            'name' => $accessory->name,
            'description' => $accessory->description ?? '',
            'serial_number' => $accessory->serial_number ?? '',
            'part_number' => $accessory->part_number ?? '',
        ];
        $this->showAccessoryModal = true;
    }

    public function saveAccessory(): void
    {
        $this->validate([
            'accessoryForm.name' => 'required|string|max:255',
            'accessoryForm.description' => 'nullable|string',
            'accessoryForm.serial_number' => 'nullable|string|max:255',
            'accessoryForm.part_number' => 'nullable|string|max:255',
        ]);

        $payload = [
            'equipment_id' => $this->equipmentId,
            'name' => $this->accessoryForm['name'],
            'description' => $this->accessoryForm['description'],
            'serial_number' => $this->accessoryForm['serial_number'],
            'part_number' => $this->accessoryForm['part_number'],
        ];

        if (!empty($this->accessoryForm['id'])) {
            EquipmentAccessory::findOrFail($this->accessoryForm['id'])->update($payload);
            session()->flash('message', 'Accessory updated successfully.');
        } else {
            EquipmentAccessory::create($payload);
            session()->flash('message', 'Accessory added successfully.');
        }

        $this->showAccessoryModal = false;
        $this->equipment = Equipment::find($this->equipmentId);
    }

    public function deleteAccessory(string $id): void
    {
        EquipmentAccessory::findOrFail($id)->delete();
        session()->flash('message', 'Accessory deleted successfully.');
        $this->equipment = Equipment::find($this->equipmentId);
    }

    // Spare Part CRUD
    public function showAddSparePartModal(): void
    {
        $this->sparePartForm = [
            'id' => '',
            'name' => '',
            'description' => '',
            'serial_number' => '',
            'part_number' => '',
        ];
        $this->showSparePartModal = true;
    }

    public function editSparePart(string $id): void
    {
        $sparePart = EquipmentSparePart::findOrFail($id);
        $this->sparePartForm = [
            'id' => $sparePart->id,
            'name' => $sparePart->name,
            'description' => $sparePart->description ?? '',
            'serial_number' => $sparePart->serial_number ?? '',
            'part_number' => $sparePart->part_number ?? '',
        ];
        $this->showSparePartModal = true;
    }

    public function saveSparePart(): void
    {
        $this->validate([
            'sparePartForm.name' => 'required|string|max:255',
            'sparePartForm.description' => 'nullable|string',
            'sparePartForm.serial_number' => 'nullable|string|max:255',
            'sparePartForm.part_number' => 'nullable|string|max:255',
        ]);

        $payload = [
            'equipment_id' => $this->equipmentId,
            'name' => $this->sparePartForm['name'],
            'description' => $this->sparePartForm['description'],
            'serial_number' => $this->sparePartForm['serial_number'],
            'part_number' => $this->sparePartForm['part_number'],
        ];

        if (!empty($this->sparePartForm['id'])) {
            EquipmentSparePart::findOrFail($this->sparePartForm['id'])->update($payload);
            session()->flash('message', 'Spare part updated successfully.');
        } else {
            EquipmentSparePart::create($payload);
            session()->flash('message', 'Spare part added successfully.');
        }

        $this->showSparePartModal = false;
        $this->equipment = Equipment::find($this->equipmentId);
    }

    public function deleteSparePart(string $id): void
    {
        EquipmentSparePart::findOrFail($id)->delete();
        session()->flash('message', 'Spare part deleted successfully.');
        $this->equipment = Equipment::find($this->equipmentId);
    }

    // --- ANNUAL MAINTENANCE CRUD (TSU/F/06) ---
    public function openAnnualModal($id = null)
    {
        $this->resetAnnualFields();
        if ($id) {
            $record = EquipmentAnnualMaintenance::findOrFail($id);
            $this->annualId = $record->id;
            $this->annual_serviced_date = $record->serviced_date ? $record->serviced_date->format('Y-m-d') : '';
            $this->annual_status = $record->status;
            $this->annual_next_service = $record->next_service ? $record->next_service->format('Y-m-d') : '';
            $this->annual_remark = $record->remark;
        }
        $this->showAnnualModal = true;
    }

    public function saveAnnual()
    {
        $this->validate([
            'annual_serviced_date' => 'nullable|date',
            'annual_status' => 'nullable|string|max:255',
            'annual_next_service' => 'nullable|date',
            'annual_remark' => 'nullable|string',
        ]);

        $payload = [
            'equipment_id' => $this->equipmentId,
            'serviced_date' => $this->annual_serviced_date ?: null,
            'status' => $this->annual_status,
            'next_service' => $this->annual_next_service ?: null,
            'remark' => $this->annual_remark,
        ];

        if ($this->annualId) {
            EquipmentAnnualMaintenance::findOrFail($this->annualId)->update($payload);
            session()->flash('message', 'Annual Maintenance record updated successfully.');
        } else {
            EquipmentAnnualMaintenance::create($payload);
            session()->flash('message', 'Annual Maintenance record added successfully.');
        }

        $this->showAnnualModal = false;
        $this->resetAnnualFields();
        $this->equipment = Equipment::find($this->equipmentId);
    }

    public function deleteAnnual($id)
    {
        EquipmentAnnualMaintenance::findOrFail($id)->delete();
        session()->flash('message', 'Annual Maintenance record deleted successfully.');
        $this->equipment = Equipment::find($this->equipmentId);
    }

    private function resetAnnualFields()
    {
        $this->annualId = null;
        $this->annual_serviced_date = '';
        $this->annual_status = '';
        $this->annual_next_service = '';
        $this->annual_remark = '';
    }

    // --- PREVENTIVE MAINTENANCE CRUD (TSU/F/05) ---
    public function openPreventiveModal($id = null)
    {
        $this->resetPreventiveFields();
        if ($id) {
            $record = EquipmentPreventiveMaintenance::findOrFail($id);
            $this->preventiveId = $record->id;
            
            if ($record->is_serviced) {
                $this->preventive_date = $record->serviced_date ? $record->serviced_date->format('Y-m-d') : '';
            } else {
                $company = \App\Company::where('active', 1)->first();
                $y = $company->maintenance_start_year ?? date('Y');
                $m = str_pad($record->scheduled_month ?? 1, 2, '0', STR_PAD_LEFT);
                $this->preventive_date = "{$y}-{$m}-01";
            }
            $this->preventive_notes = $record->notes;
        } else {
            $company = \App\Company::where('active', 1)->first();
            $y = $company->maintenance_start_year ?? date('Y');
            $m = str_pad($company->maintenance_start_month ?? 1, 2, '0', STR_PAD_LEFT);
            $this->preventive_date = "{$y}-{$m}-01";
            $this->preventive_notes = '';
        }
        $this->showPreventiveModal = true;
    }

    public function savePreventive()
    {
        $this->validate([
            'preventive_date' => 'required|date',
            'preventive_notes' => 'nullable|string',
        ]);

        $date = Carbon::parse($this->preventive_date);
        $isFuture = $date->isFuture();

        $payload = [
            'equipment_id' => $this->equipmentId,
            'scheduled_month' => $date->month,
            'is_serviced' => !$isFuture,
            'serviced_date' => $isFuture ? null : $date->format('Y-m-d'),
            'notes' => $this->preventive_notes,
        ];

        if ($this->preventiveId) {
            EquipmentPreventiveMaintenance::findOrFail($this->preventiveId)->update($payload);
            session()->flash('message', 'Preventive Maintenance record updated successfully.');
        } else {
            EquipmentPreventiveMaintenance::create($payload);
            session()->flash('message', 'Preventive Maintenance record added successfully.');
        }

        $this->showPreventiveModal = false;
        $this->resetPreventiveFields();
        $this->equipment = Equipment::find($this->equipmentId);
    }

    public function deletePreventive($id)
    {
        EquipmentPreventiveMaintenance::findOrFail($id)->delete();
        session()->flash('message', 'Preventive Maintenance record deleted successfully.');
        $this->equipment = Equipment::find($this->equipmentId);
    }

    private function resetPreventiveFields()
    {
        $this->preventiveId = null;
        $this->preventive_date = '';
        $this->preventive_notes = '';
    }

    // --- MAINTENANCE REGISTER CRUD ---
    public function openRegisterModal($id = null)
    {
        $this->resetRegisterFields();
        if ($id) {
            $record = EquipmentMaintenanceRegister::findOrFail($id);
            $this->registerId = $record->id;
            $this->register_year = $record->year;
            $this->register_service_provider = $record->service_provider;
            $this->register_service_type = $record->service_type;
            $this->register_cost_usd = $record->cost_usd;
            $this->register_cost_tzs = $record->cost_tzs;
        } else {
            $this->register_year = date('Y') . '/' . (date('Y') + 1);
        }
        $this->showRegisterModal = true;
    }

    public function saveRegister()
    {
        $this->validate([
            'register_year' => 'nullable|string|max:255',
            'register_service_provider' => 'nullable|string|max:255',
            'register_service_type' => 'nullable|string|max:255',
            'register_cost_usd' => 'nullable|numeric',
            'register_cost_tzs' => 'nullable|numeric',
        ]);

        $payload = [
            'equipment_id' => $this->equipmentId,
            'year' => $this->register_year ?: null,
            'service_provider' => $this->register_service_provider,
            'service_type' => $this->register_service_type,
            'cost_usd' => $this->register_cost_usd !== '' ? $this->register_cost_usd : 0,
            'cost_tzs' => $this->register_cost_tzs !== '' ? $this->register_cost_tzs : 0,
        ];

        if ($this->registerId) {
            EquipmentMaintenanceRegister::findOrFail($this->registerId)->update($payload);
            session()->flash('message', 'Maintenance Register record updated successfully.');
        } else {
            EquipmentMaintenanceRegister::create($payload);
            session()->flash('message', 'Maintenance Register record added successfully.');
        }

        $this->showRegisterModal = false;
        $this->resetRegisterFields();
        $this->equipment = Equipment::find($this->equipmentId);
    }

    public function deleteRegister($id)
    {
        EquipmentMaintenanceRegister::findOrFail($id)->delete();
        session()->flash('message', 'Maintenance Register record deleted successfully.');
        $this->equipment = Equipment::find($this->equipmentId);
    }

    private function resetRegisterFields()
    {
        $this->registerId = null;
        $this->register_year = '';
        $this->register_service_provider = '';
        $this->register_service_type = '';
        $this->register_cost_usd = '';
        $this->register_cost_tzs = '';
    }

    /**
     * Helper to compute dynamic quarters from the start date.
     */
    public function calculateQuarters($yearStart): array
    {
        if (!$yearStart) {
            return ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
        }

        $start = Carbon::parse($yearStart);
        
        $q1 = $start->format('M Y') . ' - ' . $start->copy()->addMonths(2)->format('M Y');
        $q2 = $start->copy()->addMonths(3)->format('M Y') . ' - ' . $start->copy()->addMonths(5)->format('M Y');
        $q3 = $start->copy()->addMonths(6)->format('M Y') . ' - ' . $start->copy()->addMonths(8)->format('M Y');
        $q4 = $start->copy()->addMonths(9)->format('M Y') . ' - ' . $start->copy()->addMonths(11)->format('M Y');

        return [$q1, $q2, $q3, $q4];
    }
}















