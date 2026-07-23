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
                                    <div class="step-indicator {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}" wire:click="goToStep(4)" style="cursor: pointer;">
                                        <span class="step-number">4</span>
                                    </div>
                                    <small class="d-block mt-2 fw-bold">Maintenance</small>
                                </div>
                                <div class="step-line {{ $currentStep > 4 ? 'completed' : '' }}"></div>
                                <div class="text-center flex-grow-1">
                                    <div class="step-indicator {{ $currentStep >= 5 ? 'active' : '' }}">
                                        <span class="step-number">5</span>
                                    </div>
                                    <small class="d-block mt-2 fw-bold">Depreciation</small>
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
                                            Equipment ID <span class="text-danger">*</span>
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
                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" wire:model.live="equipmentForm.has_logbook_tracking" class="form-check-input" id="equipment_has_logbook_tracking">
                                        <label class="form-check-label" for="equipment_has_logbook_tracking">
                                            Equipment has logbook tracking
                                        </label>
                                        <small class="form-text text-muted d-block">When enabled, a Log Book tab appears on the equipment profile to configure columns and capture logs.</small>
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
                                        @if(isset($wizardPhotoEquipment) && $wizardPhotoEquipment && $wizardPhotoEquipment->picture)
                                            <small class="text-muted">Current: <img src="{{ $wizardPhotoEquipment->picture }}" style="width: 50px; height: 50px; object-fit: cover;"></small>
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

                             <div class="eq-section-header mt-4">
                                 <i class="mdi mdi-cash-multiple"></i> Procurement & Technical Lifecycle
                             </div>
                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Purchase Price</label>
                                         <input type="number" step="0.01" wire:model="equipmentForm.purchase_price" class="form-control" placeholder="e.g. 5000.00">
                                         @error('equipmentForm.purchase_price') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Supplier Name</label>
                                         <input type="text" wire:model="equipmentForm.supplier_name" class="form-control" placeholder="e.g. ACME Corp">
                                         @error('equipmentForm.supplier_name') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                             </div>
                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Installation Date</label>
                                         <input type="date" wire:model="equipmentForm.installation_date" class="form-control">
                                         @error('equipmentForm.installation_date') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Commissioning Date</label>
                                         <input type="date" wire:model="equipmentForm.commissioning_date" class="form-control">
                                         @error('equipmentForm.commissioning_date') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                             </div>
                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Detection Limit</label>
                                         <input type="text" wire:model="equipmentForm.detection_limit" class="form-control" placeholder="e.g. 0.01 ppm">
                                         @error('equipmentForm.detection_limit') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Tolerance Limit</label>
                                         <input type="text" wire:model="equipmentForm.tolerance_limit" class="form-control" placeholder="e.g. ± 0.05">
                                         @error('equipmentForm.tolerance_limit') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                             </div>
                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Warranty Duration / Info</label>
                                         <input type="text" wire:model="equipmentForm.warranty" class="form-control" placeholder="e.g. 24 Months">
                                         @error('equipmentForm.warranty') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">Operating Environment</label>
                                         <input type="text" wire:model="equipmentForm.environment" class="form-control" placeholder="e.g. 20-25°C, Cleanroom">
                                         @error('equipmentForm.environment') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                             </div>
                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">End of Life</label>
                                         <input type="date" wire:model="equipmentForm.end_of_life" class="form-control">
                                         @error('equipmentForm.end_of_life') <span class="text-danger">{{ $message }}</span> @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-group mb-3">
                                         <label class="form-label">End of Service</label>
                                         <input type="date" wire:model="equipmentForm.end_of_service" class="form-control">
                                         @error('equipmentForm.end_of_service') <span class="text-danger">{{ $message }}</span> @enderror
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
                                        <label class="form-label">
                                            Lab
                                            @if(!empty($equipmentForm['requires_daily_log']))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <div class="tag-select-container" wire:click="searchLabs">
                                            <div class="tag-select-input">
                                                @if($selectedLabName)
                                                    <span class="tag-badge">
                                                        {{ $selectedLabName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearLab"></i>
                                                    </span>
                                                @endif
                                                <input type="text"
                                                       wire:model.live="labSearch"
                                                       wire:keyup="searchLabs"
                                                       class="tag-input"
                                                       placeholder="{{ $selectedLabName ? '' : 'Search labs...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showLabDropdown && count($filteredLabs) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredLabs as $lab)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLab('{{ $lab->id }}', @js($lab->name))">
                                                            {{ $lab->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">Required for monitoring templates so the unit appears under the selected labs.</small>
                                        @error('equipmentForm.lab_id') <span class="text-danger">{{ $message }}</span> @enderror
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
                            @error('equipmentForm.lab_id')
                                <div class="alert alert-danger py-2">
                                    <i class="mdi mdi-alert-circle-outline"></i> {{ $message }}
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" wire:click="goToStep(2)">Go to Assignment</button>
                                </div>
                            @enderror
                            @php $dlFreq = intval($equipmentForm['daily_log_frequency'] ?? 1); @endphp
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
                                        <label class="form-label">Monitored Equipment (Equipment ID) <span class="text-danger">*</span></label>
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
                                                       placeholder="{{ $selectedMonitoredEquipmentLabel ? '' : 'Search equipment ID...' }}"
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

                            <div class="eq-section-header mt-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="mdi mdi-chart-line"></i> Value Types <span class="text-danger">*</span>
                                </div>
                                <button type="button" wire:click="addValueType" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Value Type
                                </button>
                            </div>

                            @error('equipmentForm.daily_log_value_types')
                                <div class="alert alert-danger py-2 mt-2">
                                    <i class="mdi mdi-alert-circle-outline"></i> {{ $message }}
                                </div>
                            @enderror

                            @if(empty($equipmentForm['daily_log_value_types']))
                                <div class="alert alert-warning mt-2">
                                    <i class="mdi mdi-alert-outline"></i>
                                    At least one value type is required before you can continue. Click <strong>Add Value Type</strong>, then set the expected value or range.
                                </div>
                            @else
                                @foreach($equipmentForm['daily_log_value_types'] as $index => $valueType)
                                    <div class="card mb-3 value-type-card" wire:key="vt-{{ $valueType['id'] }}-{{ $valueType['value_type'] ?? '' }}-{{ $valueType['nature'] ?? '' }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Value Type #{{ $index + 1 }}</h6>
                                                <button type="button" wire:click="removeValueType('{{ $valueType['id'] }}')" class="btn btn-sm btn-outline-danger">
                                                    <i class="mdi mdi-delete"></i> Remove
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Value Type <span class="text-danger">*</span></label>
                                                        <select wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.value_type" wire:change="$set('equipmentForm.daily_log_value_types.{{ $index }}.nature', $event.target.value === 'range' ? 'quantitative' : '')" class="form-control">
                                                            <option value="">Select option</option>
                                                            <option value="constant">Constant</option>
                                                            <option value="range">Range</option>
                                                        </select>
                                                        @error("equipmentForm.daily_log_value_types.{$index}.value_type") <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Nature of Result <span class="text-danger">*</span></label>
                                                        <select wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.nature" class="form-control" {{ ($valueType['value_type'] ?? '') === 'range' ? 'disabled' : '' }}>
                                                            <option value="">Select option</option>
                                                            <option value="qualitative">Qualitative</option>
                                                            <option value="quantitative">Quantitative</option>
                                                        </select>
                                                        @error("equipmentForm.daily_log_value_types.{$index}.nature") <span class="text-danger">{{ $message }}</span> @enderror
                                                        @if(($valueType['value_type'] ?? '') === 'range')
                                                            <small class="form-text text-muted"><i class="mdi mdi-information-outline"></i> Range values are always quantitative</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Constant + Qualitative: text expected value --}}
                                            @if(($valueType['value_type'] ?? '') === 'constant' && ($valueType['nature'] ?? '') === 'qualitative')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.expected_value" class="form-control" placeholder="e.g. Good, Fair, Poor">
                                                        @error("equipmentForm.daily_log_value_types.{$index}.expected_value") <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            {{-- Constant + Quantitative: numeric expected value --}}
                                            @if(($valueType['value_type'] ?? '') === 'constant' && ($valueType['nature'] ?? '') === 'quantitative')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                                                        <input type="number" wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.expected_value" class="form-control" step="any" placeholder="e.g. 25.5">
                                                        @error("equipmentForm.daily_log_value_types.{$index}.expected_value") <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            {{-- Range + Quantitative: min and max --}}
                                            @if(($valueType['value_type'] ?? '') === 'range' && ($valueType['nature'] ?? '') === 'quantitative')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Minimum Value <span class="text-danger">*</span></label>
                                                        <input type="number" wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.expected_min" class="form-control" step="any" placeholder="e.g. 0">
                                                        @error("equipmentForm.daily_log_value_types.{$index}.expected_min") <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Maximum Value <span class="text-danger">*</span></label>
                                                        <input type="number" wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.expected_max" class="form-control" step="any" placeholder="e.g. 4">
                                                        @error("equipmentForm.daily_log_value_types.{$index}.expected_max") <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            {{-- Reporting unit (shown whenever a value type is selected) --}}
                                            @if(($valueType['value_type'] ?? '') !== '')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Reporting Unit</label>
                                                        <select wire:model.live="equipmentForm.daily_log_value_types.{{ $index }}.reporting_unit" class="form-control">
                                                            <option value="">Select unit</option>
                                                            @foreach($this->reportingUnits as $unit)
                                                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error("equipmentForm.daily_log_value_types.{$index}.reporting_unit") <span class="text-danger">{{ $message }}</span> @enderror
                                                        <small class="form-text text-muted">Unit of measurement for recorded value</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
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

                                @if (empty($hidePreventiveMaintenance ?? false))
                                <div class="eq-section-header mt-4">
                                    <i class="mdi mdi-wrench-clock-outline"></i> {{ __('equipment.preventive_maintenance_schedule') ?? 'Preventive maintenance' }}
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('equipment.preventive_maintenance_period') ?? 'Preventive maintenance period (days)' }} <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="equipmentForm.preventive_maintainance_period" class="form-control" min="0" required>
                                            @error('equipmentForm.preventive_maintainance_period') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('equipment.preventive_maintenance_notification_days') ?? 'Preventive maintenance notification (days)' }} <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="equipmentForm.preventive_maintainance_notification_days" class="form-control" min="0" required>
                                            @error('equipmentForm.preventive_maintainance_notification_days') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                @endif

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

                            @if($currentStep === 5)
                                @include('livewire.equipment.partials.equipment-depreciation-wizard-step')
                            @endif
                        </form>

                        <style>
                            .value-type-card {
                                border: 1px solid #e5e7eb;
                                border-radius: 8px;
                                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                                transition: box-shadow 0.2s ease;
                            }

                            .value-type-card:hover {
                                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                            }

                            .value-type-card .card-body {
                                padding: 1.25rem;
                            }
                        </style>
