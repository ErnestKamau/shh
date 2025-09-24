<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-scale text-primary"></i>
                                Standards Management
                            </h2>
                            <p class="text-muted mb-0">Manage standards and standard values</p>
                        </div>
                        <div class="btn-group">
                            @if($activeTab === 'standards')
                                <button wire:click="showCreateStandardModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Standard
                                </button>
                            @else
                                <button wire:click="showCreateStandardValueModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Standard Value
                                </button>
                            @endif
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

    <!-- Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-0">
                    <div class="modern-tabs-container">
                        <div class="modern-tabs">
                            <button class="modern-tab {{ $activeTab === 'standards' ? 'active' : '' }}" 
                                    wire:click="switchTab('standards')" 
                                    type="button">
                                <div class="tab-icon">
                                    <i class="mdi mdi-scale"></i>
                                </div>
                                <div class="tab-content">
                                    <span class="tab-title">Standards</span>
                                    <span class="tab-subtitle">Manage standards</span>
                                </div>
                            </button>
                            <button class="modern-tab {{ $activeTab === 'standard-values' ? 'active' : '' }}" 
                                    wire:click="switchTab('standard-values')" 
                                    type="button">
                                <div class="tab-icon">
                                    <i class="mdi mdi-numeric"></i>
                                </div>
                                <div class="tab-content">
                                    <span class="tab-title">Standard Values</span>
                                    <span class="tab-subtitle">Manage standard values</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search {{ $activeTab === 'standards' ? 'standards' : 'standard values' }}...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
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

    <!-- Content based on active tab -->
    @if($activeTab === 'standards')
        <!-- Standards Table -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        @if($this->standards->count() > 0)
                            <!-- Show Entries -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $this->standards->firstItem() ?? 0 }} to {{ $this->standards->lastItem() ?? 0 }} of {{ $this->standards->total() }} entries
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
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Standard Analytes</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->standards as $standard)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary p-2">{{ $standard->code }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $standard->name }}</strong>
                                                    @if($standard->main_standard)
                                                        <br><span class="badge bg-warning p-1 mt-1">Main</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($standard->is_qc_standard)
                                                        <span class="badge bg-info p-2">QC Standard</span>
                                                    @else
                                                        <span class="badge bg-primary p-2">Regular</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info p-2">{{ $standard->standardAnalytes->count() }}</span>
                                                </td>
                                                <td>
                                                    @if($standard->status)
                                                        <span class="badge bg-success p-2">Active</span>
                                                    @else
                                                        <span class="badge bg-danger p-2">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('livewire.standard-analytes', ['standardId' => $standard->id]) }}" 
                                                           class="btn btn-sm btn-outline-primary mr-1" 
                                                           title="View Standard Analytes">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                        <button wire:click="showEditStandardModal({{ $standard->id }})" 
                                                                class="btn btn-sm btn-outline-warning mr-1" 
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="deleteStandard({{ $standard->id }})" 
                                                                class="btn btn-sm btn-outline-danger mr-1" 
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this standard? This will also delete all associated standard analytes.')">
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
                            <div class="d-flex justify-content-center mt-3">
                                {{ $this->standards->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-scale text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No standards found</h5>
                                <p class="text-muted">Start by adding your first standard.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Standard Values Table -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        @if($this->standardValues->count() > 0)
                            <!-- Show Entries -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $this->standardValues->firstItem() ?? 0 }} to {{ $this->standardValues->lastItem() ?? 0 }} of {{ $this->standardValues->total() }} entries
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
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->standardValues as $standardValue)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary p-2">{{ $standardValue->code }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $standardValue->name }}</strong>
                                                </td>
                                                <td>
                                                    @if($standardValue->status)
                                                        <span class="badge bg-success p-2">Active</span>
                                                    @else
                                                        <span class="badge bg-danger p-2">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditStandardValueModal({{ $standardValue->id }})" 
                                                                class="btn btn-sm btn-outline-warning mr-1" 
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="deleteStandardValue({{ $standardValue->id }})" 
                                                                class="btn btn-sm btn-outline-danger mr-1" 
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this standard value?')">
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
                            <div class="d-flex justify-content-center mt-3">
                                {{ $this->standardValues->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-numeric text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No standard values found</h5>
                                <p class="text-muted">Start by adding your first standard value.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Standard Modal -->
    @if($showStandardModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStandard ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStandard ? 'Edit' : 'Create' }} Standard
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStandardModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveStandard">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardForm.name" class="form-control @error('standardForm.name') is-invalid @enderror">
                                        @error('standardForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardForm.code" class="form-control @error('standardForm.code') is-invalid @enderror">
                                        @error('standardForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="standardForm.main_standard" class="form-check-input" id="main_standard">
                                            <label class="form-check-label" for="main_standard">Main Standard</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="standardForm.is_qc_standard" class="form-check-input" id="is_qc_standard">
                                            <label class="form-check-label" for="is_qc_standard">QC Standard</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="standardForm.status" class="form-check-input" id="standard_status">
                                            <label class="form-check-label" for="standard_status">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStandardModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStandard">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Standard Value Modal -->
    @if($showStandardValueModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStandardValue ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStandardValue ? 'Edit' : 'Create' }} Standard Value
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStandardValueModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveStandardValue">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardValueForm.name" class="form-control @error('standardValueForm.name') is-invalid @enderror">
                                        @error('standardValueForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardValueForm.code" class="form-control @error('standardValueForm.code') is-invalid @enderror">
                                        @error('standardValueForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="standardValueForm.status" class="form-check-input" id="standard_value_status">
                                    <label class="form-check-label" for="standard_value_status">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStandardValueModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStandardValue">
                            <i class="mdi mdi-content-save"></i> Save
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

    /* Prevent body scroll when modal is open */
    body.modal-open {
        overflow: hidden;
    }

    /* Ensure modal is properly positioned and scrollable */
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }

    /* Smooth scrolling for modal content */
    .modal-body {
        scroll-behavior: smooth;
    }

    /* Ensure modal backdrop doesn't interfere with scrolling */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.5);
    }

    /* Modern Select Styling */
    .modern-select {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background: #ffffff;
        outline: none;
    }

    .modern-select:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
    }

    .modern-select option {
        padding: 10px 16px;
        font-weight: 500;
        color: #495057;
    }

    .modern-select option:hover {
        background-color: #f8f9fa;
    }

    /* Custom dropdown arrow */
    .modern-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 40px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Invalid state styling */
    .modern-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .modern-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Modern Tabs Styling */
    .modern-tabs-container {
        padding: 20px;
    }

    .modern-tabs {
        display: flex;
        gap: 12px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px;
        padding: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .modern-tab {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 20px 24px;
        border: none;
        background: transparent;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        min-height: 80px;
    }

    .modern-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 10px;
    }

    .modern-tab:hover::before {
        opacity: 0.05;
    }

    .modern-tab.active::before {
        opacity: 1;
    }

    .modern-tab .tab-icon {
        position: relative;
        z-index: 2;
        margin-right: 16px;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(0, 123, 255, 0.1);
        transition: all 0.3s ease;
    }

    .modern-tab.active .tab-icon {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.1);
    }

    .modern-tab .tab-icon i {
        font-size: 24px;
        color: #007bff;
        transition: all 0.3s ease;
    }

    .modern-tab.active .tab-icon i {
        color: #ffffff;
        transform: scale(1.1);
    }

    .modern-tab .tab-content {
        position: relative;
        z-index: 2;
        text-align: left;
        flex: 1;
    }

    .modern-tab .tab-title {
        display: block;
        font-size: 16px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 4px;
        transition: all 0.3s ease;
        line-height: 1.2;
    }

    .modern-tab.active .tab-title {
        color: #ffffff;
        font-weight: 700;
    }

    .modern-tab .tab-subtitle {
        display: block;
        font-size: 13px;
        color: #6c757d;
        font-weight: 400;
        transition: all 0.3s ease;
        line-height: 1.3;
    }

    .modern-tab.active .tab-subtitle {
        color: rgba(255, 255, 255, 0.9);
    }

    .modern-tab:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
    }

    .modern-tab.active {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
    }

    .modern-tab:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modern-tabs {
            flex-direction: column;
            gap: 8px;
        }
        
        .modern-tab {
            min-height: 70px;
            padding: 16px 20px;
        }
        
        .modern-tab .tab-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
        }
        
        .modern-tab .tab-icon i {
            font-size: 20px;
        }
        
        .modern-tab .tab-title {
            font-size: 15px;
        }
        
        .modern-tab .tab-subtitle {
            font-size: 12px;
        }
    }

    /* Animation for tab switching */
    .modern-tab {
        animation: fadeInUp 0.4s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loading state */
    .modern-tab.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .modern-tab.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #007bff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 3;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        // Add loading state to tabs when switching
        Livewire.on('tab-switching', () => {
            const tabs = document.querySelectorAll('.modern-tab');
            tabs.forEach(tab => tab.classList.add('loading'));
        });

        // Remove loading state when tab switch is complete
        Livewire.on('tab-switched', () => {
            const tabs = document.querySelectorAll('.modern-tab');
            tabs.forEach(tab => tab.classList.remove('loading'));
        });
    });

    // Add click animation to tabs
    document.addEventListener('click', function(e) {
        if (e.target.closest('.modern-tab')) {
            const tab = e.target.closest('.modern-tab');
            tab.style.transform = 'scale(0.98)';
            setTimeout(() => {
                tab.style.transform = '';
            }, 150);
        }
    });
    </script>
</div>
