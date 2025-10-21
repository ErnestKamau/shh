<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
<div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-molecule text-primary"></i>
                                Analytes Management
                            </h2>
                            <p class="text-muted mb-0">Manage analytes, methods, and equipment</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Analyte
                        </button>
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, code, or common name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
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

    <!-- Analytes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($analytes->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $analytes->firstItem() ?? 0 }} to {{ $analytes->lastItem() ?? 0 }} of {{ $analytes->total() }} entries
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
                                        <th style="width: 60px;">#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Common Name</th>
                                        <th>Decimal Places</th>
                                        <th>Equivalent Weight</th>
                                        <th>Reporting Symbol</th>
                                        <th>Reporting Unit</th>
                                        <th>Method</th>
                                        <th>Equipment</th>
                                        <th>Font Italic</th>
                                        <th>Non Detectable</th>
                                        <th>Non Accredited</th>
                                        <th>Show on Report</th>
                                        <th>Active</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytes as $analyte)
                                        @php
                                            $methods = $analyte->methods();
                                            $equipments = $analyte->equipments();
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $analyte->code }}</strong></td>
                                            <td>
                                                <strong>{{ $analyte->name }}</strong>
                                            </td>
                                            <td>{{ $analyte->common_name }}</td>
                                            <td>{{ $analyte->decimal_places }}</td>
                                            <td>{{ $analyte->equivalent_weight ? number_format($analyte->equivalent_weight, $analyte->decimal_places) : '-' }}</td>
                                            <td>{{ $analyte->reporting_symbol }}</td>
                                            <td>{{ $analyte->reporting_unit }}</td>
                                            <td><small>{{ implode(", ", array_keys($methods)) ?: '-' }}</small></td>
                                            <td><small>{{ implode(", ", array_keys($equipments)) ?: '-' }}</small></td>
                                            <td class="text-center">
                                                {!! $analyte->is_italic ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->non_detectable ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->non_accredited ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->show_on_report ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->active ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditModal({{ $analyte->id }})" 
                                                            class="btn btn-sm btn-outline-primary mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="showDeleteModal({{ $analyte->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $analytes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-molecule fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No analytes found</h5>
                            <p class="text-muted">Create your first analyte to get started.</p>
                            <button wire:click="showCreateModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Analyte
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analyte Modal -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingAnalyte ? 'pencil' : 'plus' }}"></i>
                        {{ $editingAnalyte ? 'Edit' : 'Add' }} Analyte
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="saveAnalyte">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Analyte Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="analyteForm.name" class="form-control @error('analyteForm.name') is-invalid @enderror" id="name" placeholder="Analyte Name...">
                                    @error('analyteForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="code" class="form-label fw-bold">Analyte Code <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="analyteForm.code" class="form-control @error('analyteForm.code') is-invalid @enderror" id="code" placeholder="Analyte Code...">
                                    @error('analyteForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="common_name" class="form-label fw-bold">Common Name</label>
                                    <input type="text" wire:model="analyteForm.common_name" class="form-control" id="common_name" placeholder="Common Name...">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="equivalent_weight" class="form-label fw-bold">Equivalent Weight</label>
                                    <input type="number" step="0.0001" wire:model="analyteForm.equivalent_weight" class="form-control" id="equivalent_weight" placeholder="Equivalent Weight...">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="decimal_places" class="form-label fw-bold">Analyte Decimal Places <span class="text-danger">*</span></label>
                                    <input type="number" min="0" step="1" wire:model="analyteForm.decimal_places" class="form-control @error('analyteForm.decimal_places') is-invalid @enderror" id="decimal_places" placeholder="Analyte Decimal Places...">
                                    @error('analyteForm.decimal_places') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reporting_symbol" class="form-label fw-bold">Analyte Reporting Symbol</label>
                                    <input type="text" wire:model="analyteForm.reporting_symbol" class="form-control" id="reporting_symbol" placeholder="Analyte Reporting Symbol...">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="mdi mdi-scale-balance text-primary"></i> Reporting Unit
                                    </label>
                                    <div class="tag-select-container" wire:click="$set('showReportingUnitDropdown', true)">
                                        <div class="tag-select-input">
                                            <!-- Display selected unit or allow searching -->
                                            @if($analyteForm['reporting_unit'])
                                                <span class="tag-badge">
                                                    {{ $analyteForm['reporting_unit'] }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('analyteForm.reporting_unit', '')"></i>
                                                </span>
                                            @endif
                                            
                                            <!-- Search Input -->
                                            <input type="text" 
                                                   wire:model.live="reportingUnitSearch" 
                                                   class="tag-input" 
                                                   placeholder="{{ $analyteForm['reporting_unit'] ? '' : 'Search reporting units...' }}"
                                                   autocomplete="off">
                                        </div>
                                        
                                        <!-- Dropdown -->
                                        @if($showReportingUnitDropdown && count($this->filteredReportingUnits) > 0)
                                            <div class="tag-dropdown">
                                                @foreach($this->filteredReportingUnits as $unit)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectReportingUnit('{{ $unit->name }}')">
                                                        {{ $unit->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Method Multi-Select -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="mdi mdi-test-tube text-success"></i> Method
                                    </label>
                                    <div class="tag-select-container" wire:click="$set('showMethodDropdown', true)">
                                        <div class="tag-select-input">
                                            <!-- Selected Method Tags -->
                                            @foreach($this->selectedMethods as $method)
                                                <span class="tag-badge">
                                                    {{ $method->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="removeMethod({{ $method->id }})"></i>
                                                </span>
                                            @endforeach
                                            
                                            <!-- Search Input -->
                                            <input type="text" 
                                                   wire:model.live="methodSearch" 
                                                   class="tag-input" 
                                                   placeholder="{{ count($this->selectedMethods) > 0 ? '' : 'Select Method...' }}"
                                                   autocomplete="off">
                                        </div>
                                        
                                        <!-- Dropdown -->
                                        @if($showMethodDropdown && count($this->filteredMethods) > 0)
                                            <div class="tag-dropdown">
                                                @foreach($this->filteredMethods as $method)
                                                    @if(!in_array($method->id, $analyteForm['method']))
                                                        <div class="tag-dropdown-item" wire:click.stop="addMethod({{ $method->id }})">
                                                            {{ $method->name }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Equipment Multi-Select -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="mdi mdi-cog text-warning"></i> Equipment
                                    </label>
                                    <div class="tag-select-container" wire:click="$set('showEquipmentDropdown', true)">
                                        <div class="tag-select-input">
                                            <!-- Selected Equipment Tags -->
                                            @foreach($this->selectedEquipment as $equip)
                                                <span class="tag-badge">
                                                    {{ $equip->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="removeEquipment({{ $equip->id }})"></i>
                                                </span>
                                            @endforeach
                                            
                                            <!-- Search Input -->
                                            <input type="text" 
                                                   wire:model.live="equipmentSearch" 
                                                   class="tag-input" 
                                                   placeholder="{{ count($this->selectedEquipment) > 0 ? '' : 'Select Equipment...' }}"
                                                   autocomplete="off">
                                        </div>
                                        
                                        <!-- Dropdown -->
                                        @if($showEquipmentDropdown && count($this->filteredEquipment) > 0)
                                            <div class="tag-dropdown">
                                                @foreach($this->filteredEquipment as $equip)
                                                    @if(!in_array($equip->id, $analyteForm['equipment_id']))
                                                        <div class="tag-dropdown-item" wire:click.stop="addEquipment({{ $equip->id }})">
                                                            {{ $equip->name }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Options Section -->
                                <div class="card bg-light mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0 text-muted">
                                            <i class="mdi mdi-cog"></i> Analyte Options
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model.defer="analyteForm.is_italic" class="form-check-input" id="is_italic">
                                                    <label class="form-check-label" for="is_italic">
                                                        <i class="mdi mdi-format-italic text-primary"></i> Report Font Italic
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model.defer="analyteForm.non_detectable" class="form-check-input" id="non_detectable">
                                                    <label class="form-check-label" for="non_detectable">
                                                        <i class="mdi mdi-eye-off text-warning"></i> Non-Detectable
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" wire:model.defer="analyteForm.non_accredited" class="form-check-input" id="non_accredited">
                                                    <label class="form-check-label" for="non_accredited">
                                                        <i class="mdi mdi-certificate-outline text-warning"></i> Non-Accredited
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model.defer="analyteForm.show_on_report" class="form-check-input" id="show_on_report">
                                                    <label class="form-check-label" for="show_on_report">
                                                        <i class="mdi mdi-file-document text-primary"></i> Show on Report
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" wire:model.defer="analyteForm.active" class="form-check-input" id="active">
                                                    <label class="form-check-label" for="active">
                                                        <i class="mdi mdi-check-circle text-success"></i> Active
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveAnalyte">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($deleteModalVisible && $analyteToDelete)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="mdi mdi-delete-alert text-danger" style="font-size: 64px;"></i>
                    </div>
                    <h5 class="text-center mb-3">Delete Analyte: <strong>{{ $analyteToDelete->name }}</strong>?</h5>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-information"></i> <strong>Note:</strong>
                        <ul class="mb-0 mt-2">
                            <li>If this analyte <strong>has samples or results</strong> tied to it, it will be <strong>soft deleted</strong> (marked as inactive and hidden).</li>
                            <li>If this analyte <strong>has no samples or results</strong>, it will be <strong>permanently deleted</strong> along with its analysis elements.</li>
                        </ul>
                    </div>
                    <p class="text-muted text-center mb-0">This action cannot be undone for permanent deletions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDelete">
                        <i class="mdi mdi-delete"></i> Confirm Delete
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
    
    /* Tag-based Multi-Select Styling */
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
    
    /* Modal Scrolling */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    /* Custom scrollbar */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    </style>
    
    @script
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container')) {
            $wire.set('showMethodDropdown', false);
            $wire.set('showEquipmentDropdown', false);
            $wire.set('showReportingUnitDropdown', false);
        }
    });
    </script>
    @endscript
</div>
