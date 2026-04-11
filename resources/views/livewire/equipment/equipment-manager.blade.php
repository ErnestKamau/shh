<div class="container-fluid">
    @if(!$embedded)
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-tools text-primary"></i>
                                Equipment Management
                            </h2>
                            <p class="text-muted mb-0">Manage equipment inventory and maintenance</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="openBulkUploadModal" class="btn btn-success">
                                <i class="mdi mdi-file-excel"></i> Bulk Import
                            </button>
                            <button wire:click="showCreateEquipmentModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Equipment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, number, make, model...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">


                    @if($this->equipment->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->equipment->firstItem() ?? 0 }} to {{ $this->equipment->lastItem() ?? 0 }} of {{ $this->equipment->total() }} entries
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 100px;">Actions</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Equipment Number</th>
                                        <th>Make</th>
                                        <th>Model</th>
                                        <th>Serial Number</th>
                                        <th>Manufacturer</th>
                                        <th>Department</th>
                                        <th>Employee</th>
                                        <th>Purchased On</th>
                                        <th>Calibration Date</th>
                                        <th>Maintenance Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->equipment as $item)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('view-equipment', ['equipmentId' => $item->id]) }}" 
                                                       class="btn btn-sm btn-outline-success mr-2" 
                                                       title="View">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </a>
                                                    <button wire:click="showEditEquipmentModal({{ $item->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-2" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <a href="{{ route('equipment-disposal-home', ['equipment_id' => $item->id]) }}" 
                                                        class="btn btn-sm btn-outline-danger mr-2" 
                                                        title="Request Disposal">
                                                        <i class="mdi mdi-delete-sweep"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <img src="{{ $item->picture }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" alt="{{ $item->name }}">
                                            </td>
                                            <td>
                                                <a href="{{ route('view-equipment', ['equipmentId' => $item->id]) }}">
                                                    {{ $item->name }}
                                                </a>
                                            </td>
                                            <td>{{ $item->equipment_number }}</td>
                                            <td>{{ $item->make }}</td>
                                            <td>{{ $item->model }}</td>
                                            <td>{{ $item->serial_number ?? '-' }}</td>
                                            <td>{{ $item->manufacturer ?? '-' }}</td>
                                            <td>{{ getInventoryDepartmentByid($item->assigned_department)->name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $employee = \App\User::find($item->assigned_employee_id);
                                                @endphp
                                                {{ $employee->name ?? '-' }}
                                            </td>
                                            <td>{{ $item->date_purchased }}</td>
                                            <td>
                                                @php
                                                    $calibration = $item->calibration_date();
                                                @endphp
                                                {{ $calibration['date']->toDateString() }}
                                                <br>
                                                <small class="badge {{ $calibration['status'] }}">
                                                    {{ number_format(intval($calibration['remaining_days'])) }} days
                                                </small>
                                                @if($calibration['status'] == 'badge-warning')
                                                    <br><small class="text-warning">
                                                        <i class="mdi mdi-alert-decagram"></i> Schedule Calibration
                                                    </small>
                                                @endif
                                                @if($calibration['status'] == 'badge-danger')
                                                    <br><small class="text-danger">
                                                        <i class="mdi mdi-alert"></i> Calibration Required
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $maintenance = $item->maintainance_date();
                                                @endphp
                                                {{ $maintenance['date']->toDateString() }}
                                                <br>
                                                <small class="badge {{ $maintenance['status'] }}">
                                                    {{ number_format(intval($maintenance['remaining_days'])) }} days
                                                </small>
                                                @if($maintenance['status'] == 'badge-warning')
                                                    <br><small class="text-warning">
                                                        <i class="mdi mdi-alert-decagram"></i> Schedule Maintenance
                                                    </small>
                                                @endif
                                                @if($maintenance['status'] == 'badge-danger')
                                                    <br><small class="text-danger">
                                                        <i class="mdi mdi-alert"></i> Maintenance Required
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->active == 1)
                                                    <span class="badge badge-success p-2">Active</span>
                                                @else
                                                    <span class="badge badge-secondary p-2">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->equipment->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-tools text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No equipment found</h5>
                            <p class="text-muted">Start by adding your first equipment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @endif

    <!-- Equipment Modal -->
    @if($showEquipmentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background-color: #001a41;">
                        <h5 class="modal-title text-white">
                            <i class="mdi mdi-{{ $editingEquipment ? 'pencil' : 'plus' }}"></i>
                            {{ $editingEquipment ? 'Edit' : 'Create' }} Equipment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEquipmentModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveEquipment">
                            <div class="border-bottom mb-3 pb-1">
                                <small class="text-uppercase font-weight-bold text-muted" style="font-size:11px;letter-spacing:0.09em;"><i class="mdi mdi-information-outline mr-1"></i> Basic Information</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-tag text-primary"></i> Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.name" class="form-control" required>
                                        @error('equipmentForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-identifier text-primary"></i> Equipment Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.equipment_number" class="form-control" required>
                                        @error('equipmentForm.equipment_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-camera text-primary"></i> Photo
                                        </label>
                                        <input type="file" wire:model="photo" class="form-control" accept="image/*">
                                        @if($editingEquipment && $editingEquipment->picture)
                                            <small class="text-muted">Current: <img src="{{ $editingEquipment->picture }}" style="width: 50px; height: 50px; object-fit: cover;"></small>
                                        @endif
                                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-text text-primary"></i> Description <span class="text-danger">*</span>
                                        </label>
                                        <textarea wire:model="equipmentForm.description" class="form-control" rows="3" required></textarea>
                                        @error('equipmentForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="border-bottom mb-3 mt-4 pb-1">
                                <small class="text-uppercase font-weight-bold text-muted" style="font-size:11px;letter-spacing:0.09em;"><i class="mdi mdi-cogs mr-1"></i> Specifications</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-wrench text-primary"></i> Make <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.make" class="form-control" required>
                                        @error('equipmentForm.make') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-cog text-primary"></i> Model <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.model" class="form-control" required>
                                        @error('equipmentForm.model') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Serial Number</label>
                                        <input type="text" wire:model="equipmentForm.serial_number" class="form-control">
                                        @error('equipmentForm.serial_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Barcode Number</label>
                                        <input type="text" wire:model="equipmentForm.barcode_number" class="form-control">
                                        @error('equipmentForm.barcode_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Manufacturer</label>
                                        <input type="text" wire:model="equipmentForm.manufacturer" class="form-control">
                                        @error('equipmentForm.manufacturer') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="border-bottom mb-3 mt-4 pb-1">
                                <small class="text-uppercase font-weight-bold text-muted" style="font-size:11px;letter-spacing:0.09em;"><i class="mdi mdi-map-marker mr-1"></i> Assignment &amp; Location</small>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select wire:model="equipmentForm.status" class="form-select" required>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        @error('equipmentForm.status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-check-circle text-primary"></i> Condition <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.condition" class="form-control" required>
                                        @error('equipmentForm.condition') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-calendar text-primary"></i> Warranty Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" wire:model="equipmentForm.warranty_date" class="form-control" required>
                                        @error('equipmentForm.warranty_date') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-office-building text-primary"></i> Department <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showDepartmentDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($selectedDepartmentName)
                                                    <span class="tag-badge">
                                                        {{ $selectedDepartmentName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearDepartment"></i>
                                                    </span>
                                                @endif
                                                <input type="text" 
                                                       wire:model.live="departmentSearch" 
                                                       wire:keyup="searchDepartments"
                                                       class="tag-input" 
                                                       placeholder="{{ $selectedDepartmentName ? '' : 'Search departments...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showDepartmentDropdown && count($filteredDepartments) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredDepartments as $department)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectDepartment({{ $department->id }}, '{{ $department->name }}')">
                                                            {{ $department->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('equipmentForm.assigned_department') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-account text-primary"></i> Assigned Employee
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showEmployeeDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($selectedEmployeeName)
                                                    <span class="tag-badge">
                                                        {{ $selectedEmployeeName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearEmployee"></i>
                                                    </span>
                                                @endif
                                                <input type="text" 
                                                       wire:model.live="employeeSearch" 
                                                       wire:keyup="searchEmployees"
                                                       class="tag-input" 
                                                       placeholder="{{ $selectedEmployeeName ? '' : 'Search employees...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showEmployeeDropdown && count($filteredEmployees) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredEmployees as $employee)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectEmployee({{ $employee->id }}, '{{ $employee->name }}')">
                                                            {{ $employee->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('equipmentForm.assigned_employee_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Asset Type</label>
                                        <div class="tag-select-container" wire:click="$set('showAssetTypeDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($selectedAssetTypeName)
                                                    <span class="tag-badge">
                                                        {{ $selectedAssetTypeName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearAssetType"></i>
                                                    </span>
                                                @endif
                                                <input type="text" 
                                                       wire:model.live="assetTypeSearch" 
                                                       wire:keyup="searchAssetTypes"
                                                       class="tag-input" 
                                                       placeholder="{{ $selectedAssetTypeName ? '' : 'Search asset types...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showAssetTypeDropdown && count($filteredAssetTypes) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredAssetTypes as $assetType)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectAssetType({{ $assetType->id }}, '{{ $assetType->asset_code }} ({{ $assetType->descripton }})')">
                                                            {{ $assetType->asset_code }} ({{ $assetType->descripton }})
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('equipmentForm.asset_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Asset Location</label>
                                        <div class="tag-select-container" wire:click="$set('showAssetLocationDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($selectedAssetLocationName)
                                                    <span class="tag-badge">
                                                        {{ $selectedAssetLocationName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearAssetLocation"></i>
                                                    </span>
                                                @endif
                                                <input type="text" 
                                                       wire:model.live="assetLocationSearch" 
                                                       wire:keyup="searchAssetLocations"
                                                       class="tag-input" 
                                                       placeholder="{{ $selectedAssetLocationName ? '' : 'Search asset locations...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showAssetLocationDropdown && count($filteredAssetLocations) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredAssetLocations as $assetLocation)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectAssetLocation({{ $assetLocation->id }}, '{{ $assetLocation->name }}')">
                                                            {{ $assetLocation->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('equipmentForm.asset_location_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-calendar text-primary"></i> Date Purchased
                                        </label>
                                        <input type="date" wire:model="equipmentForm.date_purchased" class="form-control">
                                        @error('equipmentForm.date_purchased') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" wire:model="equipmentForm.active" class="form-check-input" id="equipment_active_mgr">
                                            <label class="form-check-label" for="equipment_active_mgr">Active</label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" wire:model.live="equipmentForm.requires_daily_log" class="form-check-input" id="equipment_rdl_mgr">
                                            <label class="form-check-label" for="equipment_rdl_mgr">Requires Daily Log</label>
                                            <small class="form-text text-muted d-block">When checked, this equipment will appear on the Equipment Daily Log page.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($equipmentForm['requires_daily_log']))
                            @php $dlType = $equipmentForm['daily_log_value_type'] ?? ''; $dlNature = $equipmentForm['daily_log_nature'] ?? ''; $dlFreq = intval($equipmentForm['daily_log_frequency'] ?? 1); @endphp
                            <div class="border-bottom mb-3 mt-3 pb-1">
                                <small class="text-uppercase font-weight-bold text-muted" style="font-size:11px;letter-spacing:0.09em;"><i class="mdi mdi-notebook-check-outline mr-1"></i> Daily Log Configuration</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Logging Frequency <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_frequency" class="form-control">
                                            <option value="1">Once a day</option>
                                            <option value="2">Twice a day</option>
                                            <option value="3">Three times a day</option>
                                            <option value="4">Four times a day</option>
                                            <option value="5">Five times a day</option>
                                            <option value="6">Six times a day</option>
                                        </select>
                                        @error('equipmentForm.daily_log_frequency') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if($dlFreq >= 2)
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Time Interval (hours) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_time_interval" class="form-control" min="1" placeholder="e.g. 4">
                                        @error('equipmentForm.daily_log_time_interval') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Number of hours between each reading.</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Value Type <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_value_type" class="form-control">
                                            <option value="">-- Select --</option>
                                            <option value="constant">Constant</option>
                                            <option value="range">Range</option>
                                        </select>
                                        @error('equipmentForm.daily_log_value_type') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Whether the expected value is a single constant or a range.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nature of Result <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_nature" class="form-control" {{ $dlType === 'range' ? 'disabled' : '' }}>
                                            <option value="">-- Select --</option>
                                            <option value="qualitative">Qualitative</option>
                                            <option value="quantitative">Quantitative</option>
                                        </select>
                                        @error('equipmentForm.daily_log_nature') <span class="text-danger">{{ $message }}</span> @enderror
                                        @if($dlType === 'range')
                                            <small class="form-text text-muted"><i class="mdi mdi-information-outline"></i> Range values are always quantitative.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Constant + Qualitative: text expected value --}}
                            @if($dlType === 'constant' && $dlNature === 'qualitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.daily_log_expected_value" class="form-control" placeholder="e.g. Pass, Clear, Present">
                                        @error('equipmentForm.daily_log_expected_value') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Constant + Quantitative: numeric expected value + tolerance --}}
                            @if($dlType === 'constant' && $dlNature === 'quantitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_value" class="form-control" step="any" placeholder="e.g. 7.0">
                                        @error('equipmentForm.daily_log_expected_value') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance (&plusmn;) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_tolerance" class="form-control" min="1" max="100" placeholder="e.g. 2">
                                        @error('equipmentForm.daily_log_tolerance') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Acceptable deviation from the expected value (e.g. &plusmn;2).</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Range + Quantitative: min, max and tolerance --}}
                            @if($dlType === 'range' && $dlNature === 'quantitative')
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Minimum Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_min" class="form-control" step="any" placeholder="e.g. 6.5">
                                        @error('equipmentForm.daily_log_expected_min') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maximum Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_max" class="form-control" step="any" placeholder="e.g. 7.5">
                                        @error('equipmentForm.daily_log_expected_max') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance (&plusmn;) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_tolerance" class="form-control" min="1" max="100" placeholder="e.g. 2">
                                        @error('equipmentForm.daily_log_tolerance') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Acceptable deviation (&plusmn;).</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Reporting unit (shown whenever a value type is selected) --}}
                            @if($dlType !== '')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Reporting Unit</label>
                                        <select wire:model="equipmentForm.daily_log_reporting_unit" class="form-control">
                                            <option value="">-- Select Unit --</option>
                                            @foreach($reportingUnits as $unit)
                                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('equipmentForm.daily_log_reporting_unit') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Unit of measurement for the recorded value.</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                            <div class="border-bottom mb-3 mt-4 pb-1">
                                <small class="text-uppercase font-weight-bold text-muted" style="font-size:11px;letter-spacing:0.09em;"><i class="mdi mdi-calendar-clock mr-1"></i> Maintenance &amp; Calibration Schedule</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maintenance After (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.maintainance_days" class="form-control" min="0" required>
                                        @error('equipmentForm.maintainance_days') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maintenance Notification (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.maintainance_notification_in_days" class="form-control" min="0" required>
                                        @error('equipmentForm.maintainance_notification_in_days') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Calibration After (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.calibration_days" class="form-control" min="0" required>
                                        @error('equipmentForm.calibration_days') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Calibration Notification (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.calibration_notification_in_days" class="form-control" min="0" required>
                                        @error('equipmentForm.calibration_notification_in_days') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEquipmentModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveEquipment">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif



    <!-- Bulk Upload Modal -->
    @if($showBulkUploadModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-excel text-success"></i>
                            Bulk Import Equipment
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeBulkUploadModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>Instructions:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Download the Excel template below</li>
                                <li>Fill in all required fields (marked with *)</li>
                                <li>Upload the completed file</li>
                                <li>Review the import results</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <button wire:click="downloadTemplate" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-download"></i> Download Excel Template
                            </button>
                        </div>

                        <hr>

                        <form wire:submit.prevent="processBulkUpload">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Upload Excel File <span class="text-danger">*</span></label>
                                <input type="file" wire:model="bulkFile" class="form-control" accept=".xlsx,.xls,.csv">
                                @error('bulkFile') <span class="text-danger">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="bulkFile" class="mt-2">
                                    <small class="text-muted">
                                        <i class="mdi mdi-loading mdi-spin"></i> Uploading file...
                                    </small>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <small>
                                    <strong>Note:</strong> Duplicate equipment numbers will be skipped. Maximum file size: 5MB. Supported formats: .xlsx, .xls, .csv
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkUploadModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="processBulkUpload" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="processBulkUpload">
                                <i class="mdi mdi-upload"></i> Upload & Import
                            </span>
                            <span wire:loading wire:target="processBulkUpload">
                                <i class="mdi mdi-loading mdi-spin"></i> Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        .modal.show {
            display: block !important;
        }
    
        body.modal-open {
            overflow: hidden;
        }
    
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }
    
        /* Tag-based Dropdown Styling */
        .tag-select-container {
            position: relative;
            cursor: text;
        }
    
        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
    
        .tag-select-input:hover {
            border-color: #007bff;
        }
    
        .tag-select-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
    
        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background-color: #007bff;
            color: white;
            border-radius: 16px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }
    
        .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
    
        .tag-badge i:hover {
            opacity: 1;
        }
    
        .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 0.9rem;
        }
    
        .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -2px;
        }
    
        .tag-dropdown-item {
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }
    
        .tag-dropdown-item:hover {
            background-color: #f8f9fa;
        }
    
        .tag-dropdown-item:last-child {
            border-bottom: none;
        }
    </style>
    
    @script
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container') && !e.target.closest('.modal')) {
            $wire.set('showEmployeeDropdown', false);
            $wire.set('showDepartmentDropdown', false);
            $wire.set('showAssetTypeDropdown', false);
            $wire.set('showAssetLocationDropdown', false);
        }
    });


    </script>
    @endscript
</div>



