<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\User;
use App\InventoryDepartment;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\File;
use Maatwebsite\Excel\Facades\Excel;

class EquipmentManager extends Component
{
    use WithPagination, WithFileUploads;

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
        'calibration_days' => null,
        'calibration_notification_in_days' => null,
        'asset_type_id' => null,
        'asset_location_id' => null,
        'active' => true,
    ];



    // File Upload
    public $photo;

    // Supporting Data
    public $employees = [];
    public $statuses = [];
    public $departments = [];
    public $assetTypes = [];
    public $assetLocations = [];

    // Searchable Select Properties
    public $employeeSearch = '';
    public $departmentSearch = '';
    public $assetTypeSearch = '';
    public $assetLocationSearch = '';

    public $showEmployeeDropdown = false;
    public $showDepartmentDropdown = false;
    public $showAssetTypeDropdown = false;
    public $showAssetLocationDropdown = false;

    public $selectedEmployeeName = '';
    public $selectedDepartmentName = '';
    public $selectedAssetTypeName = '';
    public $selectedAssetLocationName = '';

    public $filteredEmployees = [];
    public $filteredDepartments = [];
    public $filteredAssetTypes = [];
    public $filteredAssetLocations = [];

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
            'equipmentForm.assigned_department' => 'required|integer',
            'equipmentForm.assigned_employee_id' => 'nullable|integer',
            'equipmentForm.warranty_date' => 'required|date',
            'equipmentForm.date_purchased' => 'required|date',
            'equipmentForm.maintainance_days' => 'required|integer|min:0',
            'equipmentForm.maintainance_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.calibration_days' => 'required|integer|min:0',
            'equipmentForm.calibration_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.asset_type_id' => 'nullable|integer',
            'equipmentForm.asset_location_id' => 'nullable|integer',
            'equipmentForm.active' => 'boolean',
            'photo' => 'nullable|image|max:10240', // 10MB max
        ];

        return $rules;
    }

    public function mount(): void
    {
        $this->loadInitialData();
    }

    public function loadInitialData(): void
    {
        $this->employees = getUsers();
        $this->statuses = getStatus();
        $this->departments = InventoryDepartment::where('module', 'organizational')->get();
        $this->assetTypes = AssetType::where('is_active', 1)->get();
        $this->assetLocations = AssetLocation::where('is_active', 1)->get();
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

    public function showCreateEquipmentModal(): void
    {
        $this->resetEquipmentForm();
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
            'calibration_days' => $equipment->calibration_days,
            'calibration_notification_in_days' => $equipment->calibration_notification_in_days,
            'asset_type_id' => $equipment->asset_type_id,
            'asset_location_id' => $equipment->asset_location_id,
            'active' => $equipment->active ?? true,
        ];

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

        $this->photo = null;
        $this->showEquipmentModal = true;
    }

    public function saveEquipment(): void
    {
        $this->validate();

        try {
            $data = $this->equipmentForm;
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
            'calibration_days' => null,
            'calibration_notification_in_days' => null,
            'asset_type_id' => null,
            'asset_location_id' => null,
            'active' => true,
        ];

        $this->employeeSearch = '';
        $this->departmentSearch = '';
        $this->assetTypeSearch = '';
        $this->assetLocationSearch = '';

        $this->selectedEmployeeName = '';
        $this->selectedDepartmentName = '';
        $this->selectedAssetTypeName = '';
        $this->selectedAssetLocationName = '';

        $this->showEmployeeDropdown = false;
        $this->showDepartmentDropdown = false;
        $this->showAssetTypeDropdown = false;
        $this->showAssetLocationDropdown = false;

        $this->editingEquipment = null;
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

    public function selectEmployee($id, $name): void
    {
        $this->equipmentForm['assigned_employee_id'] = $id;
        $this->selectedEmployeeName = $name;
        $this->employeeSearch = $name;
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
        
        $this->filteredDepartments = InventoryDepartment::where('module', 'organizational')
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectDepartment($id, $name): void
    {
        $this->equipmentForm['assigned_department'] = $id;
        $this->selectedDepartmentName = $name;
        $this->departmentSearch = $name;
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
        $this->assetTypeSearch = $name;
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

    public function selectAssetLocation($id, $name): void
    {
        $this->equipmentForm['asset_location_id'] = $id;
        $this->selectedAssetLocationName = $name;
        $this->assetLocationSearch = $name;
        $this->showAssetLocationDropdown = false;
    }

    public function clearAssetLocation(): void
    {
        $this->equipmentForm['asset_location_id'] = null;
        $this->selectedAssetLocationName = '';
        $this->assetLocationSearch = '';
    }

    // Bulk Upload Methods
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
                'Purchased On',
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
            DB::beginTransaction();

            $path = $this->bulkFile->getRealPath();
            $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array)
                {
                    return $array;
                }
            }, $path);

            if (empty($data) || empty($data[0])) {
                throw new \Exception('The uploaded file is empty.');
            }

            $rows = $data[0];
            $header = array_shift($rows); // Remove header row

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-index
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map Excel columns to data
                $name = trim($row[0] ?? '');
                $equipmentNumber = trim($row[1] ?? '');
                $make = trim($row[2] ?? '');
                $model = trim($row[3] ?? '');
                $serialNumber = trim($row[4] ?? '');
                $manufacturer = trim($row[5] ?? '');
                $departmentName = trim($row[6] ?? '');
                $datePurchased = trim($row[7] ?? '');
                $previousCalibrationDate = trim($row[8] ?? '');
                $calibrationInterval = trim($row[9] ?? '');
                $previousMaintainanceDate = trim($row[10] ?? '');
                $intermediateChecksInterval = trim($row[11] ?? '');

                // Check for duplicate equipment number
                $existingEquipment = Equipment::where('equipment_number', $equipmentNumber)
                    ->where('company_id', getUserCompany())
                    ->first();
                
                if ($existingEquipment) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: Equipment number '{$equipmentNumber}' already exists.";
                    continue;
                }

                // Validate row data
                $validator = Validator::make([
                    'name' => $name,
                    'equipment_number' => $equipmentNumber,
                    'make' => $make,
                    'model' => $model,
                    'department_name' => $departmentName,
                    'date_purchased' => $datePurchased,
                    'calibration_interval' => $calibrationInterval,
                    'intermediate_checks_interval' => $intermediateChecksInterval,
                ], [
                    'name' => 'required|string|max:255',
                    'equipment_number' => 'required|string|max:255',
                    'make' => 'required|string|max:255',
                    'model' => 'required|string|max:255',
                    'department_name' => 'required|string',
                    'date_purchased' => 'required|date',
                    'calibration_interval' => 'required|integer|min:0',
                    'intermediate_checks_interval' => 'required|integer|min:0',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Get or create department
                $department = InventoryDepartment::where('module', 'organizational')
                    ->where('name', $departmentName)
                    ->first();

                if (!$department) {
                    $locationId = null;
                    if (function_exists('getCurrentUserLocation')) {
                        $location = getCurrentUserLocation();
                        $locationId = $location ? $location->id : null;
                    }
                    
                    $department = InventoryDepartment::create([
                        'name' => $departmentName,
                        'module' => 'organizational',
                        'company_id' => getUserCompany(),
                        'location_id' => $locationId,
                        'active' => 1,
                    ]);
                }

                // Prepare equipment data
                $equipmentData = [
                    'name' => $name,
                    'equipment_number' => $equipmentNumber,
                    'description' => $name, // Use name as description
                    'make' => $make,
                    'model' => $model,
                    'serial_number' => $serialNumber ?: null,
                    'manufacturer' => $manufacturer ?: null,
                    'assigned_department' => $department->id,
                    'date_purchased' => $datePurchased,
                    'calibration_days' => (int)$calibrationInterval,
                    'calibration_notification_in_days' => (int)$calibrationInterval,
                    'maintainance_days' => 365, // Default to 365 days
                    'maintainance_notification_in_days' => (int)$intermediateChecksInterval,
                    'status' => 'Active',
                    'condition' => 'Active',
                    'warranty_date' => null, // Nullable
                    'picture' => null, // Nullable for bulk import
                    'active' => true,
                    'company_id' => getUserCompany(),
                    'is_disposal' => 0,
                ];

                // Create equipment
                $equipment = Equipment::create($equipmentData);

                // Create calibration log if previous calibration date provided
                if ($previousCalibrationDate) {
                    try {
                        $calibrationDate = \Carbon\Carbon::parse($previousCalibrationDate)->format('Y-m-d');
                        MaintainanceCalibrationLog::create([
                            'equipment_id' => $equipment->id,
                            'type' => 'Calibration',
                            'date' => $calibrationDate,
                            'notes' => 'from the bulk upload',
                            'overseen_by' => auth()->id(),
                            'edit_by' => auth()->id(),
                            'certificate' => 'no-document',
                        ]);
                    } catch (\Exception $e) {
                        // Skip log creation if date is invalid
                    }
                }

                // Create maintenance log if previous maintenance date provided
                if ($previousMaintainanceDate) {
                    try {
                        $maintenanceDate = \Carbon\Carbon::parse($previousMaintainanceDate)->format('Y-m-d');
                        MaintainanceCalibrationLog::create([
                            'equipment_id' => $equipment->id,
                            'type' => 'Maintainance',
                            'date' => $maintenanceDate,
                            'notes' => 'from the bulk upload',
                            'overseen_by' => auth()->id(),
                            'edit_by' => auth()->id(),
                            'certificate' => 'no-document',
                        ]);
                    } catch (\Exception $e) {
                        // Skip log creation if date is invalid
                    }
                }

                $successCount++;
            }

            DB::commit();

            $this->closeBulkUploadModal();
            
            if ($errorCount > 0) {
                $this->message = "{$successCount} equipment item(s) created successfully. {$errorCount} row(s) failed. Errors: " . implode(' | ', array_slice($errors, 0, 5));
                $this->messageType = 'warning';
            } else {
                $this->message = "{$successCount} equipment item(s) created successfully!";
                $this->messageType = 'success';
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error processing file: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function render()
    {
        return view('livewire.equipment.equipment-manager');
    }
}


