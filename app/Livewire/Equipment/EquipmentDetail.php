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
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class EquipmentDetail extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $equipmentId;
    public $equipment;
    public $activeTab = 'maintenance';

    // Modal States
    public $showEditModal = false;
    public $showMaintenanceModal = false;
    public $showCalibrationModal = false;
    public $showRepairModal = false;
    public $showVerificationModal = false;
    public $showOperatorModal = false;
    public $showAttachmentModal = false;
    public $showNotificationModal = false;

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
    public $selectedAssetTypeName = '';
    public $selectedAssetLocationName = '';

    public $filteredEmployees = [];
    public $filteredSuppliers = [];
    public $filteredDepartments = [];
    public $filteredAssetTypes = [];
    public $filteredAssetLocations = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    public $fromDailyLog = false;

    public function mount($equipmentId, bool $fromDailyLog = false): void
    {
        $this->equipmentId = $equipmentId;
        $this->fromDailyLog = $fromDailyLog;
        if ($fromDailyLog) {
            $this->activeTab = 'dailylog';
        }
        $this->loadEquipment();
        $this->loadInitialData();
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
    }

    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    // Equipment Management
    public function showEditEquipmentModal(): void
    {
        $this->equipmentForm = [
            'name' => $this->equipment->name,
            'equipment_number' => $this->equipment->equipment_number,
            'description' => $this->equipment->description ?? '',
            'make' => $this->equipment->make,
            'model' => $this->equipment->model,
            'serial_number' => $this->equipment->serial_number ?? '',
            'barcode_number' => $this->equipment->barcode_number ?? '',
            'manufacturer' => $this->equipment->manufacturer ?? '',
            'status' => $this->equipment->status ?? 'Active',
            'condition' => $this->equipment->condition ?? '',
            'assigned_department' => $this->equipment->assigned_department,
            'assigned_employee_id' => $this->equipment->assigned_employee_id,
            'warranty_date' => $this->equipment->warranty_date ?? '',
            'date_purchased' => $this->equipment->date_purchased,
            'maintainance_days' => $this->equipment->maintainance_days,
            'maintainance_notification_in_days' => $this->equipment->maintainance_notification_in_days,
            'calibration_days' => $this->equipment->calibration_days,
            'calibration_notification_in_days' => $this->equipment->calibration_notification_in_days,
            'asset_type_id' => $this->equipment->asset_type_id,
            'asset_location_id' => $this->equipment->asset_location_id,
            'active' => $this->equipment->active ?? true,
            'requires_daily_log' => $this->equipment->requires_daily_log ?? false,
            'daily_log_value_type' => $this->equipment->daily_log_value_type ?? '',
            'daily_log_nature' => $this->equipment->daily_log_nature ?? '',
            'daily_log_tolerance' => $this->equipment->daily_log_tolerance ?? null,
            'daily_log_expected_value' => $this->equipment->daily_log_expected_value ?? '',
            'daily_log_expected_min' => $this->equipment->daily_log_expected_min ?? '',
            'daily_log_expected_max' => $this->equipment->daily_log_expected_max ?? '',
            'daily_log_reporting_unit' => $this->equipment->daily_log_reporting_unit ?? '',
            'daily_log_frequency' => $this->equipment->daily_log_frequency ?? 1,
            'daily_log_time_interval' => $this->equipment->daily_log_time_interval ?? '',
        ];

        // Set selected names for searchable selects
        if ($this->equipment->assigned_employee_id) {
            $employee = User::find($this->equipment->assigned_employee_id);
            $this->selectedEmployeeName = $employee->name ?? '';
            $this->employeeSearch = $this->selectedEmployeeName;
        }

        if ($this->equipment->assigned_department) {
            $department = getInventoryDepartmentByid($this->equipment->assigned_department);
            $this->selectedDepartmentName = $department->name ?? '';
            $this->departmentSearch = $this->selectedDepartmentName;
        }

        if ($this->equipment->asset_type_id) {
            $assetType = AssetType::find($this->equipment->asset_type_id);
            $this->selectedAssetTypeName = $assetType ? ($assetType->asset_code . ' (' . $assetType->descripton . ')') : '';
            $this->assetTypeSearch = $this->selectedAssetTypeName;
        }

        if ($this->equipment->asset_location_id) {
            $assetLocation = AssetLocation::find($this->equipment->asset_location_id);
            $this->selectedAssetLocationName = $assetLocation->name ?? '';
            $this->assetLocationSearch = $this->selectedAssetLocationName;
        }

        $this->photo = null;
        $this->showEditModal = true;
    }

    public function saveEquipment(): void
    {
        $this->validate([
            'equipmentForm.name' => 'required|string|max:255',
            'equipmentForm.equipment_number' => 'required|string|max:255',
            'equipmentForm.description' => 'required|string',
            'equipmentForm.make' => 'required|string|max:255',
            'equipmentForm.model' => 'required|string|max:255',
            'equipmentForm.status' => 'required|string',
            'equipmentForm.condition' => 'required|string|max:255',
            'equipmentForm.assigned_department' => 'required|integer',
            'equipmentForm.warranty_date' => 'required|date',
            'equipmentForm.date_purchased' => 'nullable|date',
            'equipmentForm.maintainance_days' => 'required|integer|min:0',
            'equipmentForm.maintainance_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.calibration_days' => 'required|integer|min:0',
            'equipmentForm.calibration_notification_in_days' => 'required|integer|min:0',
            'photo' => 'nullable|image|max:10240',
        ]);

        // Conditional daily log validation
        if (!empty($this->equipmentForm['requires_daily_log'])) {
            $type   = $this->equipmentForm['daily_log_value_type'] ?? '';
            $nature = $this->equipmentForm['daily_log_nature'] ?? '';

            $this->validate([
                'equipmentForm.daily_log_value_type' => 'required|in:constant,range',
                'equipmentForm.daily_log_nature'     => 'required|in:qualitative,quantitative',
            ]);

            if ($type === 'constant') {
                $this->validate(['equipmentForm.daily_log_expected_value' => 'required|string|max:255']);
                if ($nature === 'quantitative') {
                    $this->validate(['equipmentForm.daily_log_tolerance' => 'required|integer|min:1|max:100']);
                }
            }

            if ($type === 'range') {
                $this->validate([
                    'equipmentForm.daily_log_expected_min' => 'required|numeric',
                    'equipmentForm.daily_log_expected_max' => 'required|numeric',
                    'equipmentForm.daily_log_tolerance'    => 'required|integer|min:1|max:100',
                ]);
            }

            $this->validate(['equipmentForm.daily_log_frequency' => 'required|integer|min:1|max:6']);
            if (($this->equipmentForm['daily_log_frequency'] ?? 1) > 1) {
                $this->validate(['equipmentForm.daily_log_time_interval' => 'required|integer|min:1']);
            }
        }

        try {
            $data = $this->equipmentForm;
            $data['company_id'] = getUserCompany();

            if ($this->photo) {
                $path = $this->photo->store('equipment', 'public');
                $file = explode('/', $path);
                $data['picture'] = '/storage/equipment/' . urlencode(end($file));
            }

            $this->equipment->update($data);
            $this->loadEquipment();
            $this->message = 'Equipment updated successfully!';
            $this->messageType = 'success';
            $this->showEditModal = false;
        } catch (\Exception $e) {
            $this->message = 'Error updating equipment: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    // Maintenance Log Management
    public function updatedEquipmentFormRequiresDailyLog(): void
    {
        if (empty($this->equipmentForm['requires_daily_log'])) {
            $this->equipmentForm['daily_log_value_type'] = '';
            $this->equipmentForm['daily_log_nature'] = '';
            $this->equipmentForm['daily_log_tolerance'] = null;
            $this->equipmentForm['daily_log_expected_value'] = '';
            $this->equipmentForm['daily_log_expected_min'] = '';
            $this->equipmentForm['daily_log_expected_max'] = '';
            $this->equipmentForm['daily_log_reporting_unit'] = '';
            $this->equipmentForm['daily_log_frequency'] = 1;
            $this->equipmentForm['daily_log_time_interval'] = '';
        }
    }

    public function updatedEquipmentFormDailyLogFrequency(): void
    {
        if (($this->equipmentForm['daily_log_frequency'] ?? 1) <= 1) {
            $this->equipmentForm['daily_log_time_interval'] = '';
        }
    }

    public function updatedEquipmentFormDailyLogValueType(): void
    {
        if (($this->equipmentForm['daily_log_value_type'] ?? '') === 'range') {
            $this->equipmentForm['daily_log_nature'] = 'quantitative';
            $this->equipmentForm['daily_log_expected_value'] = '';
            $this->equipmentForm['daily_log_tolerance'] = null;
        } else {
            $this->equipmentForm['daily_log_expected_min'] = '';
            $this->equipmentForm['daily_log_expected_max'] = '';
        }
    }

    public function updatedEquipmentFormDailyLogNature(): void
    {
        if (($this->equipmentForm['daily_log_nature'] ?? '') !== 'quantitative') {
            $this->equipmentForm['daily_log_tolerance'] = null;
            $this->equipmentForm['daily_log_expected_min'] = '';
            $this->equipmentForm['daily_log_expected_max'] = '';
        } else {
            $this->equipmentForm['daily_log_expected_value'] = '';
        }
    }

    // Maintenance Log Management
    public function showCreateMaintenanceModal(): void
    {
        $this->resetMaintenanceForm();
        $this->showMaintenanceModal = true;
        $this->editingLog = null;
    }

    public function showEditMaintenanceModal($logId): void
    {
        $log = MaintainanceCalibrationLog::findOrFail($logId);
        $this->editingLog = $log;
        $this->maintenanceForm = [
            'date' => $log->date,
            'description' => $log->description ?? '',
            'reference_number' => $log->reference_number ?? '',
            'maintainance_type' => $log->maintainance_type ?? 'in-house',
            'employee_id' => $log->employee_id,
            'supplier_id' => $log->supplier_id,
            'notes' => $log->notes,
        ];
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
                $data['edit_by'] = auth()->id();
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

    public function deleteMaintenanceLog($logId): void
    {
        try {
            MaintainanceCalibrationLog::findOrFail($logId)->delete();
            $this->message = 'Maintenance log deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting maintenance log: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    // Calibration Log Management (similar to maintenance)
    public function showCreateCalibrationModal(): void
    {
        $this->resetCalibrationForm();
        $this->showCalibrationModal = true;
        $this->editingLog = null;
    }

    public function showEditCalibrationModal($logId): void
    {
        $log = MaintainanceCalibrationLog::findOrFail($logId);
        $this->editingLog = $log;
        $this->calibrationForm = [
            'date' => $log->date,
            'description' => $log->description ?? '',
            'reference_number' => $log->reference_number ?? '',
            'maintainance_type' => $log->maintainance_type ?? 'in-house',
            'employee_id' => $log->employee_id,
            'supplier_id' => $log->supplier_id,
            'notes' => $log->notes,
        ];
        $this->certificate = null;
        $this->showCalibrationModal = true;
    }

    public function saveCalibrationLog(): void
    {
        $this->validate([
            'calibrationForm.date' => 'required|date',
            'calibrationForm.description' => 'required|string',
            'calibrationForm.reference_number' => 'required|string',
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
                'notes' => $this->calibrationForm['notes'],
                'overseen_by' => auth()->id(),
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
                $data['edit_by'] = auth()->id();
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

    public function deleteCalibrationLog($logId): void
    {
        try {
            MaintainanceCalibrationLog::findOrFail($logId)->delete();
            $this->message = 'Calibration log deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting calibration log: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    // Verification Log Management
    public function showCreateVerificationModal(): void
    {
        $this->resetVerificationForm();
        $this->showVerificationModal = true;
        $this->editingLog = null;
    }

    public function showEditVerificationModal($logId): void
    {
        $log = VerificationLog::findOrFail($logId);
        $this->editingLog = $log;
        $this->verificationForm = [
            'verification_date' => $log->verification_date,
            'reference_standard' => $log->reference_standard ?? '',
            'procedure' => $log->procedure ?? '',
            'response' => $log->response ?? '',
            'remarks' => $log->remarks ?? '',
            'maintainance_type' => $log->maintainance_type ?? 'in-house',
            'operator_id' => $log->operator_id,
            'supplier_id' => $log->supplier_id,
        ];
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
            } else {
                $data['maintainance_type'] = 'in-house';
                $data['operator_id'] = $this->verificationForm['operator_id'];
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

    public function deleteVerificationLog($logId): void
    {
        try {
            $log = VerificationLog::findOrFail($logId);
            $log->update(['is_delete' => 1]);
            $this->message = 'Verification log deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting verification log: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    // Operator Management
    public function showOperatorModal(): void
    {
        $this->operatorForm = ['operators' => []];
        $this->showOperatorModal = true;
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
            $this->showOperatorModal = false;
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

    public function deleteAttachment($attachmentId): void
    {
        try {
            EquipmentAttachment::findOrFail($attachmentId)->delete();
            $this->message = 'Attachment deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting attachment: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    // Notification Management
    public function showCreateNotificationModal(): void
    {
        $this->resetNotificationForm();
        $this->showNotificationModal = true;
        $this->editingNotification = null;
    }

    public function showEditNotificationModal($notificationId): void
    {
        $notification = EquipmentNotifications::findOrFail($notificationId);
        $this->editingNotification = $notification;
        $this->notificationForm = [
            'value' => $notification->value,
            'frequency' => $notification->frequency,
            'notification_type' => $notification->notification_type,
        ];
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

    public function deleteNotification($notificationId): void
    {
        try {
            EquipmentNotifications::findOrFail($notificationId)->delete();
            $this->message = 'Notification deleted successfully!';
            $this->messageType = 'success';
            $this->loadEquipment();
        } catch (\Exception $e) {
            $this->message = 'Error deleting notification: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
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
    }

    public function resetCalibrationForm(): void
    {
        $this->calibrationForm = [
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
        $this->filteredEmployees = User::where('active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectEmployee($id, $name): void
    {
        $this->maintenanceForm['employee_id'] = $id;
        $this->calibrationForm['employee_id'] = $id;
        $this->verificationForm['operator_id'] = $id;
        $this->selectedEmployeeName = $name;
        $this->employeeSearch = $name;
        $this->showEmployeeDropdown = false;
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
        $mins   = [];
        $maxs   = [];
        $means  = [];

        foreach ($entries as $entry) {
            $label    = $freq > 1
                ? Carbon::parse($entry->log_date)->format('M j') . ' #' . $entry->slot_number
                : Carbon::parse($entry->log_date)->format('M j');
            $labels[] = $label;

            if ($type === 'range') {
                $parts   = array_map('trim', explode('–', $entry->recorded_value ?? ''));
                $min     = is_numeric($parts[0] ?? null) ? (float) $parts[0] : null;
                $max     = isset($parts[1]) && is_numeric($parts[1]) ? (float) $parts[1] : null;
                $mean    = ($min !== null && $max !== null) ? round(($min + $max) / 2, 2) : null;
                $mins[]  = $min;
                $maxs[]  = $max;
                $means[] = $mean;
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
            $data['mins']        = $mins;
            $data['maxs']        = $maxs;
            $data['means']       = $means;
            $data['expectedMin'] = (float) $this->equipment->daily_log_expected_min;
            $data['expectedMax'] = (float) $this->equipment->daily_log_expected_max;
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

        $entries = EquipmentDailyLogEntry::where('equipment_id', $this->equipmentId)
            ->whereNotNull('recorded_value')
            ->where('recorded_value', '!=', '')
            ->orderBy('log_date', 'desc')
            ->orderBy('slot_number')
            ->get();

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
                $parts = array_map('trim', explode('–', $entry->recorded_value));
                $rMin  = is_numeric($parts[0] ?? null) ? (float) $parts[0] : null;
                $rMax  = isset($parts[1]) && is_numeric($parts[1]) ? (float) $parts[1] : null;
                $eMin  = (float) $this->equipment->daily_log_expected_min;
                $eMax  = (float) $this->equipment->daily_log_expected_max;
                $loLim = $tol > 0 ? $eMin * (1 - $tol / 100) : $eMin;
                $hiLim = $tol > 0 ? $eMax * (1 + $tol / 100) : $eMax;

                if ($rMin !== null && $rMax !== null && ($rMin < $loLim || $rMax > $hiLim)) {
                    $fail   = true;
                    $reason = 'Recorded ' . $rMin . '–' . $rMax . ' outside '
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
            'non-conformance-' . \Str::slug($name) . '.xlsx'
        );
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
}















