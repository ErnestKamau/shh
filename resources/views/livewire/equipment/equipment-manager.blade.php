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
                                {{ __('equipment.equipment_management') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.equipment_management_subtitle') }}</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button wire:click="openBulkUploadModal" class="btn btn-outline-success" style="border-radius: 8px;margin-right: 10px !important;">
                                <i class="mdi mdi-file-excel"></i> {{ __('equipment.import_equipment') }}
                            </button>
                            <button wire:click="showCreateEquipmentModal" class="btn btn-outline-primary" style="border-radius: 8px;">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_equipment') }}
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
                        <i class="mdi mdi-filter-variant"></i> {{ __('equipment.filter_options') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.search') }}</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('equipment.search_by_name_number_make_model') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.status') }}</label>
                                <details class="tag-select-container status-filter-container">
                                    <summary class="tag-select-input">
                                        @if($statusFilter)
                                            <span class="tag-badge status-filter-badge">
                                                {{ $statusFilter }}
                                                <i class="mdi mdi-close" wire:click.prevent="$set('statusFilter', '')"></i>
                                            </span>
                                        @endif
                                        <input
                                            type="text"
                                            class="tag-input status-filter-input"
                                            readonly
                                            value=""
                                            placeholder="{{ $statusFilter ? '' : __('equipment.all_status') }}"
                                        >
                                    </summary>
                                    <div class="tag-dropdown">
                                        <button type="button" class="tag-dropdown-item status-filter-option w-100 text-start" wire:click="$set('statusFilter', '')">
                                            {{ __('equipment.all_status') }}
                                        </button>
                                        @foreach($statuses as $status)
                                            <button type="button" class="tag-dropdown-item status-filter-option w-100 text-start" wire:click='$set("statusFilter", @js($status))'>
                                                {{ $status }}
                                            </button>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> {{ __('equipment.clear') }}
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
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">{{ __('equipment.show') }}:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 100px;">{{ __('equipment.actions') }}</th>
                                        <th>{{ __('equipment.photo') }}</th>
                                        <th>{{ __('equipment.name') }}</th>
                                        <th>{{ __('equipment.equipment_number') }}</th>
                                        <th>{{ __('equipment.make') }}</th>
                                        <th>{{ __('equipment.model') }}</th>
                                        <th>{{ __('equipment.serial_number') }}</th>
                                        <th>{{ __('equipment.manufacturer') }}</th>
                                        <th>{{ __('equipment.department') }}</th>
                                        <th>{{ __('equipment.employee') }}</th>
                                        <th>{{ __('equipment.purchased_on') }}</th>
                                        <th>{{ __('equipment.calibration_date') }}</th>
                                        <th>{{ __('equipment.maintenance_date') }}</th>
                                        <th>{{ __('equipment.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->equipment as $item)
                                        <tr>
                                            <td class="equipment-actions-cell">
                                                <div class="d-flex">
                                                    <a href="{{ route('view-equipment', ['equipmentId' => $item->id]) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="{{ __('equipment.view') }}">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </a>
                                                    <button wire:click="showEditEquipmentModal({{ $item->id }})"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="{{ __('equipment.edit') }}">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <a href="{{ route('equipment-disposal-home', ['equipment_id' => $item->id]) }}"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                        title="{{ __('equipment.request_disposal') }}">
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
                                                    <span class="badge badge-success p-2">{{ __('equipment.active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary p-2">{{ __('equipment.inactive') }}</span>
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
                            <h5 class="text-muted mt-3">{{ __('equipment.no_equipment_found') }}</h5>
                            <p class="text-muted">{{ __('equipment.add_first_equipment_hint') }}</p>
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
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingEquipment ? 'pencil' : 'plus' }}"></i>
                            {{ $editingEquipment ? 'Edit' : 'Create' }} Equipment
                        </h5>
                        <button type="button" class="btn-close" wire:click.prevent.stop="closeEquipmentModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Step Indicator -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-center flex-grow-1">
                                    <div class="step-indicator {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}" wire:click="goToStep(1)" style="cursor: pointer;">
                                        <span class="step-number">1</span>
                                    </div>
                                    <small class="d-block mt-2 fw-bold">Basic Info</small>
                                </div>
                                <div class="step-line {{ $currentStep > 1 ? 'completed' : '' }}"></div>
                                <div class="text-center flex-grow-1">
                                    <div class="step-indicator {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}" wire:click="goToStep(2)" style="cursor: pointer;">
                                        <span class="step-number">2</span>
                                    </div>
                                    <small class="d-block mt-2 fw-bold">Assignment</small>
                                </div>
                                <div class="step-line {{ $currentStep > 2 ? 'completed' : '' }}"></div>
                                <div class="text-center flex-grow-1">
                                    <div class="step-indicator {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}" wire:click="goToStep(3)" style="cursor: pointer;">
                                        <span class="step-number">3</span>
                                    </div>
                                    <small class="d-block mt-2 fw-bold">Monitoring</small>
                                </div>
                                <div class="step-line {{ $currentStep > 3 ? 'completed' : '' }}"></div>
                                <div class="text-center flex-grow-1">
                                    <div class="step-indicator {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}">
                                        <span class="step-number">4</span>
                                    </div>
                                    <small class="d-block mt-2 fw-bold">Maintenance</small>
                                </div>
                            </div>
                        </div>

                        <style>
                            .step-indicator {
                                width: 45px;
                                height: 45px;
                                border-radius: 50%;
                                background-color: #e9ecef;
                                border: 2px solid #dee2e6;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin: 0 auto;
                                transition: all 0.3s ease;
                                position: relative;
                                z-index: 1;
                            }
                            .step-indicator.active {
                                background-color: #0066cc;
                                border-color: #0066cc;
                                color: white;
                            }
                            .step-indicator.completed {
                                background-color: #28a745;
                                border-color: #28a745;
                                color: white;
                            }
                            .step-indicator .step-number {
                                font-weight: bold;
                                font-size: 18px;
                            }
                            .step-line {
                                flex-grow: 1;
                                height: 2px;
                                background-color: #dee2e6;
                                margin: 20px -10px 0 -10px;
                                z-index: 0;
                            }
                            .step-line.completed {
                                background-color: #28a745;
                            }
                        </style>

                        <form class="eq-form">
                            <!-- STEP 1: Basic Information & Specifications -->
                            @if($currentStep === 1)
                            <div class="step-content">
                                <div class="eq-section-header">
                                    <i class="mdi mdi-information-outline"></i> Basic Information
                                </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.name" class="form-control" required>
                                        @error('equipmentForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Equipment Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.equipment_number" class="form-control" required>
                                        @error('equipmentForm.equipment_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            {{ __('equipment.description') }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea wire:model="equipmentForm.description" class="form-control" rows="2" required></textarea>
                                        @error('equipmentForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Photo
                                        </label>
                                        <input type="file" wire:model="photo" class="form-control" accept="image/*">
                                        @if($editingEquipment && $editingEquipment->picture)
                                            <small class="text-muted">Current: <img src="{{ $editingEquipment->picture }}" style="width: 50px; height: 50px; object-fit: cover;"></small>
                                        @endif
                                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="eq-section-header mt-4">
                                <i class="mdi mdi-cogs"></i> Specifications
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Make <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.make" class="form-control" required>
                                        @error('equipmentForm.make') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Model <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.model" class="form-control" required>
                                        @error('equipmentForm.model') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Serial Number</label>
                                        <input type="text" wire:model="equipmentForm.serial_number" class="form-control">
                                        @error('equipmentForm.serial_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Barcode Number</label>
                                        <input type="text" wire:model="equipmentForm.barcode_number" class="form-control">
                                        @error('equipmentForm.barcode_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Manufacturer</label>
                                        <input type="text" wire:model="equipmentForm.manufacturer" class="form-control">
                                        @error('equipmentForm.manufacturer') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            </div>
                            @endif

                            <!-- STEP 2: Assignment & Location -->
                            @if($currentStep === 2)
                            <div class="step-content">
                                <div class="eq-section-header">
                                    <i class="mdi mdi-map-marker"></i> Assignment &amp; Location
                                </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select wire:model="equipmentForm.status" class="form-control" required>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        @error('equipmentForm.status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Condition <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="equipmentForm.condition" class="form-control" required>
                                        @error('equipmentForm.condition') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Warranty Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" wire:model="equipmentForm.warranty_date" class="form-control" required>
                                        @error('equipmentForm.warranty_date') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Date Purchased
                                        </label>
                                        <input type="date" wire:model="equipmentForm.date_purchased" class="form-control">
                                        @error('equipmentForm.date_purchased') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            Department <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="searchDepartments">
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
                                                          placeholder="{{ $selectedDepartmentName ? '' : __('equipment.search_departments') }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showDepartmentDropdown && count($filteredDepartments) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredDepartments as $department)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectDepartment('{{ $department->id }}')">
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
                                            Assigned Employee
                                        </label>
                                        <div class="tag-select-container" wire:click="searchEmployees">
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
                                                          placeholder="{{ $selectedEmployeeName ? '' : __('equipment.search_employees') }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showEmployeeDropdown && count($filteredEmployees) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredEmployees as $employee)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectEmployee('{{ $employee->id }}')">
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
                                                          placeholder="{{ $selectedAssetTypeName ? '' : __('equipment.search_asset_types') }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showAssetTypeDropdown && count($filteredAssetTypes) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredAssetTypes as $assetType)
                                                        <div class="tag-dropdown-item" wire:click.stop='selectAssetType(@js($assetType->id), @js($assetType->asset_code . " (" . $assetType->descripton . ")"))'>
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
                                        <div class="tag-select-container" wire:click="searchAssetLocations">
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
                                                          placeholder="{{ $selectedAssetLocationName ? '' : __('equipment.search_asset_locations') }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showAssetLocationDropdown && count($filteredAssetLocations) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredAssetLocations as $assetLocation)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectAssetLocation('{{ $assetLocation->id }}')">
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
                                        <label class="form-label">{{ __('equipment.active') }}</label>
                                        <div class="d-flex align-items-center" style="height:38px;">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model="equipmentForm.active" class="form-check-input" id="equipment_active_mgr">
                                                <label class="form-check-label" for="equipment_active_mgr">{{ __('equipment.mark_this_equipment_as_active') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            @endif

                            <!-- STEP 3: Monitoring (Daily Logs) -->
                            @if($currentStep === 3)
                            <div class="step-content">
                                <div class="eq-section-header">
                                    <i class="mdi mdi-monitor-dashboard"></i> Monitoring
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('equipment.requires_daily_log') }}</label>
                                    <div class="d-flex align-items-center" style="height:38px;">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model.live="equipmentForm.requires_daily_log" class="form-check-input" id="equipment_rdl_mgr">
                                            <label class="form-check-label" for="equipment_rdl_mgr">{{ __('equipment.equipment_appears_on_daily_log_page') }}</label>
                                        </div>
                                    </div>
                                </div>
                            @if(!empty($equipmentForm['requires_daily_log']))
                            @php $dlType = $equipmentForm['daily_log_value_type'] ?? ''; $dlNature = $equipmentForm['daily_log_nature'] ?? ''; $dlFreq = intval($equipmentForm['daily_log_frequency'] ?? 1); @endphp
                            <div class="eq-section-header mt-4">
                                <i class="mdi mdi-notebook-check-outline"></i> {{ __('equipment.daily_log_configuration') }}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.logging_frequency') }} <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_frequency" class="form-control">
                                            <option value="1">{{ __('equipment.once_a_day') }}</option>
                                            <option value="2">{{ __('equipment.twice_a_day') }}</option>
                                            <option value="3">{{ __('equipment.three_times_a_day') }}</option>
                                            <option value="4">{{ __('equipment.four_times_a_day') }}</option>
                                            <option value="5">{{ __('equipment.five_times_a_day') }}</option>
                                            <option value="6">{{ __('equipment.six_times_a_day') }}</option>
                                        </select>
                                        @error('equipmentForm.daily_log_frequency') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Frequency Schedule</label>
                                        <div class="frequency-schedule-card">
                                            <div class="table-responsive">
                                            <table class="table table-sm mb-0 frequency-schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 120px;">Frequency ID</th>
                                                        <th>Label</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($this->dailyLogFrequencyRows as $row)
                                                        <tr>
                                                            <td>
                                                                <span class="frequency-pill">{{ $row['id'] }}</span>
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    wire:model.live="equipmentForm.daily_log_frequency_labels.{{ $row['id'] }}"
                                                                    class="form-control frequency-label-input"
                                                                    placeholder="Type label"
                                                                >
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Logging Monitored By Another Equipment</label>
                                        <div class="d-flex align-items-center" style="height:38px;">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model.live="equipmentForm.daily_log_monitored_by_another_equipment" class="form-check-input" id="equipment_monitored_by_another">
                                                <label class="form-check-label" for="equipment_monitored_by_another">Enable monitored equipment</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($equipmentForm['daily_log_monitored_by_another_equipment']))
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Monitored Equipment (Equipment Number) <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="searchMonitoredEquipments">
                                            <div class="tag-select-input">
                                                @if($selectedMonitoredEquipmentLabel)
                                                    <span class="tag-badge">
                                                        {{ $selectedMonitoredEquipmentLabel }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearMonitoredEquipment"></i>
                                                    </span>
                                                @endif
                                                <input type="text"
                                                       wire:model.live="monitoredEquipmentSearch"
                                                       wire:keyup="searchMonitoredEquipments"
                                                       class="tag-input"
                                                       value=""
                                                       placeholder="{{ $selectedMonitoredEquipmentLabel ? '' : 'Search equipment number...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showMonitoredEquipmentDropdown && count($filteredMonitoredEquipments) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredMonitoredEquipments as $monitoredEquipment)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectMonitoredEquipment('{{ $monitoredEquipment->id }}')">
                                                            {{ $monitoredEquipment->equipment_number }} - {{ $monitoredEquipment->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('equipmentForm.daily_log_monitored_equipment_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.value_type') }} <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_value_type" class="form-control">
                                            <option value="">{{ __('equipment.select_option') }}</option>
                                            <option value="constant">{{ __('equipment.constant') }}</option>
                                            <option value="range">{{ __('equipment.range') }}</option>
                                        </select>
                                        @error('equipmentForm.daily_log_value_type') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">{{ __('equipment.expected_value_constant_or_range') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.nature_of_result') }} <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_nature" class="form-control" {{ $dlType === 'range' ? 'disabled' : '' }}>
                                            <option value="">{{ __('equipment.select_option') }}</option>
                                            <option value="qualitative">{{ __('equipment.qualitative') }}</option>
                                            <option value="quantitative">{{ __('equipment.quantitative') }}</option>
                                        </select>
                                        @error('equipmentForm.daily_log_nature') <span class="text-danger">{{ $message }}</span> @enderror
                                        @if($dlType === 'range')
                                            <small class="form-text text-muted"><i class="mdi mdi-information-outline"></i> {{ __('equipment.range_values_are_always_quantitative') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Constant + Qualitative: text expected value --}}
                            @if($dlType === 'constant' && $dlNature === 'qualitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.expected_value') }} <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.daily_log_expected_value" class="form-control" placeholder="{{ __('equipment.daily_log_expected_text_example') }}">
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
                                        <label class="form-label">{{ __('equipment.expected_value') }} <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_value" class="form-control" step="any" placeholder="{{ __('equipment.daily_log_expected_numeric_example') }}">
                                        @error('equipmentForm.daily_log_expected_value') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Uncertainty Measure (&plusmn;) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_tolerance" class="form-control" min="1" max="100" placeholder="{{ __('equipment.daily_log_tolerance_example') }}">
                                        @error('equipmentForm.daily_log_tolerance') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">{{ __('equipment.acceptable_deviation_from_expected_value') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Range + Quantitative: min, max and tolerance --}}
                            @if($dlType === 'range' && $dlNature === 'quantitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Minimum Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_min" class="form-control" step="any" placeholder="{{ __('equipment.daily_log_min_value_example') }}">
                                        @error('equipmentForm.daily_log_expected_min') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maximum Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_max" class="form-control" step="any" placeholder="{{ __('equipment.daily_log_max_value_example') }}">
                                        @error('equipmentForm.daily_log_expected_max') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Uncertainty Measure (&plusmn;) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_tolerance" class="form-control" min="1" max="100" placeholder="{{ __('equipment.daily_log_tolerance_example') }}">
                                        @error('equipmentForm.daily_log_tolerance') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">{{ __('equipment.acceptable_deviation') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Reporting unit (shown whenever a value type is selected) --}}
                            @if($dlType !== '')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.reporting_unit') }}</label>
                                        <div class="tag-select-container" wire:click="searchReportingUnits">
                                            <div class="tag-select-input">
                                                @if($selectedReportingUnitName)
                                                    <span class="tag-badge">
                                                        {{ $selectedReportingUnitName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearReportingUnit"></i>
                                                    </span>
                                                @endif
                                                <input type="text"
                                                       wire:model.live="reportingUnitSearch"
                                                       wire:keyup="searchReportingUnits"
                                                       class="tag-input"
                                                       value=""
                                                       placeholder="{{ $selectedReportingUnitName ? '' : __('equipment.select_unit') }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showReportingUnitDropdown && count($filteredReportingUnits) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredReportingUnits as $unit)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectReportingUnit('{{ $unit->id }}')">
                                                            {{ $unit->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('equipmentForm.daily_log_reporting_unit') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">{{ __('equipment.unit_of_measurement_for_recorded_value') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                            </div>
                            @endif

                            <!-- STEP 4: Maintenance & Calibration Schedule -->
                            @if($currentStep === 4)
                            <div class="step-content">
                                <div class="eq-section-header">
                                    <i class="mdi mdi-calendar-clock"></i> Maintenance Schedule
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('equipment.maintenance_after_days') }} <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="equipmentForm.maintainance_days" class="form-control" min="0" required>
                                            @error('equipmentForm.maintainance_days') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('equipment.maintenance_notification_days') }} <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="equipmentForm.maintainance_notification_in_days" class="form-control" min="0" required>
                                            @error('equipmentForm.maintainance_notification_in_days') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="eq-section-header mt-4">
                                    <i class="mdi mdi-microscope"></i> Calibration Schedule
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('equipment.calibration_after_days') }} <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="equipmentForm.calibration_days" class="form-control" min="0" required>
                                            @error('equipmentForm.calibration_days') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('equipment.calibration_notification_days') }} <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="equipmentForm.calibration_notification_in_days" class="form-control" min="0" required>
                                            @error('equipmentForm.calibration_notification_in_days') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <button type="button" class="btn btn-secondary" wire:click="closeEquipmentModal">{{ __('equipment.cancel') }}</button>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary ms-2" wire:click="previousStep" style="display: {{ $currentStep === 1 ? 'none' : 'block' }};">
                                    <i class="mdi mdi-chevron-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-outline-primary" wire:click="nextStep" style="display: {{ $currentStep === $totalSteps ? 'none' : 'block' }};">
                                    Next <i class="mdi mdi-chevron-right"></i>
                                </button>
                                <button type="button" class="btn btn-primary" wire:click="saveEquipment" style="display: {{ $currentStep === $totalSteps ? 'block' : 'none' }};">
                                    <i class="mdi mdi-content-save"></i> {{ __('equipment.save') }}
                                </button>
                            </div>
                        </div>
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
                            {{ __('equipment.bulk_import_equipment') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeBulkUploadModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>{{ __('equipment.instructions') }}:</strong>
                            <ol class="mb-0 mt-2">
                                <li>{{ __('equipment.download_excel_template_below') }}</li>
                                <li>{{ __('equipment.fill_required_fields_marked') }}</li>
                                <li>{{ __('equipment.upload_completed_file') }}</li>
                                <li>{{ __('equipment.review_import_results') }}</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <button wire:click="downloadTemplate" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-download"></i> {{ __('equipment.download_excel_template') }}
                            </button>
                        </div>

                        <hr>

                        <form wire:submit.prevent="processBulkUpload">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.upload_excel_file') }} <span class="text-danger">*</span></label>
                                <input type="file" wire:model="bulkFile" class="form-control" accept=".xlsx,.xls,.csv">
                                @error('bulkFile') <span class="text-danger">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="bulkFile" class="mt-2">
                                    <small class="text-muted">
                                        <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.uploading_file') }}
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
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkUploadModal">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-success" wire:click="processBulkUpload" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="processBulkUpload">
                                <i class="mdi mdi-upload"></i> Upload & Import
                            </span>
                            <span wire:loading wire:target="processBulkUpload">
                                <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.processing') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        /* ── Equipment Table Width ────────────────────────────── */
        .table-responsive {
            width: 100%;
        }

        .equipment-table {
            width: 140%;
            /* min-width: 1400px; */
        }

        /* ── Equipment Action Buttons ─────────────────────────── */
        .equipment-actions-cell {
            width: 130px;
        }

        .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        /* EDIT Button - Blue */
        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        /* VIEW Button - Green */
        .rm-act-btn--view {
            border: 1px solid #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .rm-act-btn--view:hover {
            background: #dcfce7;
            border-color: #86efac;
        }

        /* DELETE Button - Red */
        .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .rm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        /* ─────────────────────────────────────────────────────── */

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

        /* ── Uniform form section headers ───────────────────── */
        .eq-section-header {
            background-color: #f4f6fb;
            border-left: 3px solid #001a41;
            padding: 7px 12px;
            margin-bottom: 16px;
            border-radius: 0 4px 4px 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #344767;
        }
        .eq-section-header .mdi {
            font-size: 13px;
            color: #001a41;
        }

        /* ── Uniform label style ─────────────────────────────── */
        .eq-form .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* ── Uniform input / select / textarea height & style ─ */
        .eq-form .form-control,
        .eq-form .tag-select-input {
            height: 38px;
            border-radius: 6px;
            border: 1px solid #d1d7e0;
            font-size: 0.875rem;
            color: #344767;
            background-color: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .eq-form textarea.form-control {
            height: auto;
            min-height: 68px;
            resize: vertical;
        }
        .eq-form .form-control:focus {
            border-color: #001a41;
            box-shadow: 0 0 0 0.15rem rgba(0, 26, 65, 0.15);
            outline: none;
        }

        /* Helper text */
        .eq-form .form-text {
            font-size: 0.75rem;
            color: #8898aa;
            margin-top: 3px;
        }

        /* Checkbox label alignment */
        .eq-form .form-check-label {
            font-size: 0.875rem;
            color: #344767;
        }

        /* Tag select inside eq-form */
        .eq-form .tag-select-input {
            padding: 4px 10px;
            min-height: 38px;
            height: auto;
        }
    
        /* Tag-based Dropdown Styling */
        .tag-select-container {
            position: relative;
            cursor: text;
        }

        .status-filter-container summary {
            list-style: none;
            cursor: pointer;
        }

        .status-filter-container summary::-webkit-details-marker {
            display: none;
        }

        .status-filter-container .status-filter-input {
            pointer-events: none;
            user-select: none;
            background: transparent;
        }

        .status-filter-container .status-filter-badge {
            background-color: #e7f1ff;
            color: #1d4f91;
        }

        .status-filter-container .status-filter-badge i {
            color: #1d4f91;
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

        .eq-form .tag-badge {
            background-color: #e7f1ff;
            color: #1d4f91;
        }

        .eq-form .tag-badge i {
            color: #1d4f91;
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

        .frequency-schedule-card {
            border: 1px solid #d8e2ef;
            border-radius: 10px;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 100%);
            box-shadow: 0 2px 10px rgba(17, 24, 39, 0.04);
            overflow: hidden;
        }

        .frequency-schedule-table thead th {
            background-color: #f2f7ff;
            color: #37517a;
            font-size: 0.77rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border-bottom: 1px solid #d8e2ef;
            padding: 10px 12px;
        }

        .frequency-schedule-table tbody td {
            vertical-align: middle;
            border-top: 1px solid #e8eef7;
            padding: 10px 12px;
            background-color: #ffffff;
        }

        .frequency-schedule-table tbody tr:first-child td {
            border-top: none;
        }

        .frequency-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            border-radius: 999px;
            background-color: #eaf2ff;
            color: #295a9b;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .frequency-label-input {
            height: 36px;
            border: 1px solid #ccd9ea;
            border-radius: 8px;
            background-color: #ffffff;
            font-size: 0.88rem;
            color: #2a3f5f;
        }

        .frequency-label-input:focus {
            border-color: #7ca6df;
            box-shadow: 0 0 0 0.2rem rgba(67, 114, 176, 0.12);
        }
    </style>
    
    @script
    <script>
    // {{ __('equipment.close') }} dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container') && !e.target.closest('.modal')) {
            $wire.set('showEmployeeDropdown', false);
            $wire.set('showDepartmentDropdown', false);
            $wire.set('showAssetTypeDropdown', false);
            $wire.set('showAssetLocationDropdown', false);
        }
    });

    // Make status details dropdown open/close immediately and consistently.
    document.addEventListener('click', function (e) {
        const summary = e.target.closest('.status-filter-container > summary');
        if (!summary) {
            return;
        }

        e.preventDefault();
        const details = summary.parentElement;
        if (!details) {
            return;
        }

        details.open = !details.open;
    });

    document.addEventListener('click', function (e) {
        const option = e.target.closest('.status-filter-option');
        if (!option) {
            return;
        }

        const details = option.closest('.status-filter-container');
        if (details) {
            details.open = false;
        }
    });


    </script>
    @endscript
</div>



