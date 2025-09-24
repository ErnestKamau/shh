<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-test-tube text-primary"></i>
                                Standard Analytes Management
                            </h2>
                            <p class="text-muted mb-0">Manage standard analytes for: <strong>{{ $standard->name }}</strong></p>
                        </div>
                        <div class="btn-group">
                           
                            <button wire:click="showCreateStandardAnalyteModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Standard Analyte
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by analyte name...">
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

    <!-- Standard Analytes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->standardAnalytes->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->standardAnalytes->firstItem() ?? 0 }} to {{ $this->standardAnalytes->lastItem() ?? 0 }} of {{ $this->standardAnalytes->total() }} entries
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
                                        <th>Analyte</th>
                                        <th>Standard Value</th>
                                        <th>Type</th>
                                        <th>Range/Value</th>
                                        <th>Expected Value</th>
                                        <th style="display: none;">Mean Value</th>
                                        <th>Comments</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->standardAnalytes as $standardAnalyte)
                                        <tr>
                                            <td>
                                                <strong>{{ $standardAnalyte->analyte->name ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $standardAnalyte->analyte->code ?? '' }}</small>
                                            </td>
                                            <td>
                                                {{ $standardAnalyte->standardValue->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                @if($standardAnalyte->standard_value_type === 'is_range')
                                                    <span class="badge bg-info p-2" style="color: white;">Range</span>
                                                @else
                                                    <span class="badge bg-primary p-2" style="color: white;">Value</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($standardAnalyte->standard_value_type === 'is_range')
                                                    <strong>{{ $standardAnalyte->low }} - {{ $standardAnalyte->high }}</strong>
                                                @else
                                                    <strong>{{ $standardAnalyte->standard_is_value }}</strong>
                                                    @if($standardAnalyte->value_type)
                                                        <br><small class="text-muted">{{ $standardAnalyte->value_type }}</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                {{ $standardAnalyte->expected_value ?? 'N/A' }}
                                            </td>
                                            <td style="display: none;">
                                                {{ $standardAnalyte->mean_value ?? 'N/A' }}
                                            </td>
                                            <td>
                                                {{ Str::limit($standardAnalyte->comments, 30) }}
                                            </td>
                                            <td>
                                                @if($standardAnalyte->is_active)
                                                    <span class="badge bg-success p-2" style="color: white;">Active</span>
                                                @else
                                                    <span class="badge bg-danger p-2" style="color: white;">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditStandardAnalyteModal({{ $standardAnalyte->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteStandardAnalyte({{ $standardAnalyte->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this standard analyte?')">
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
                            {{ $this->standardAnalytes->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-test-tube text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No standard analytes found</h5>
                            <p class="text-muted">Start by adding your first standard analyte.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Standard Analyte Modal -->
    @if($showStandardAnalyteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStandardAnalyte ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStandardAnalyte ? 'Edit' : 'Create' }} Standard Analyte
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStandardAnalyteModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveStandardAnalyte">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Analyte <span class="text-danger">*</span></label>
                                        <select wire:model="standardAnalyteForm.analyte_id" class="form-select modern-select @error('standardAnalyteForm.analyte_id') is-invalid @enderror">
                                            <option value="">Select Analyte</option>
                                            @foreach($analytes as $analyte)
                                                <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('standardAnalyteForm.analyte_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Standard Value</label>
                                        <select wire:model="standardAnalyteForm.standard_value_id" class="form-select modern-select">
                                            <option value="">Select Standard Value</option>
                                            @foreach($standardValues as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }} ({{ $value->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Value Type <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" wire:model.live="standardAnalyteForm.value_type" value="range" class="form-check-input" id="range_type">
                                            <label class="form-check-label" for="range_type">
                                                <i class="mdi mdi-range"></i> Range
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" wire:model.live="standardAnalyteForm.value_type" value="use_value" class="form-check-input" id="use_value_type">
                                            <label class="form-check-label" for="use_value_type">
                                                <i class="mdi mdi-numeric"></i> Use Value
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('standardAnalyteForm.value_type') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Range Type Fields --}}
                            @if($standardAnalyteForm['value_type'] === 'range')
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="mdi mdi-range"></i> Range Configuration</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Low Value <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="standardAnalyteForm.low" class="form-control @error('standardAnalyteForm.low') is-invalid @enderror" placeholder="Enter low value">
                                                    @error('standardAnalyteForm.low') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">High Value <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="standardAnalyteForm.high" class="form-control @error('standardAnalyteForm.high') is-invalid @enderror" placeholder="Enter high value">
                                                    @error('standardAnalyteForm.high') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Use Value Type Fields --}}
                            @if($standardAnalyteForm['value_type'] === 'use_value')
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="mdi mdi-numeric"></i> Standard Value Configuration</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Standard Value <span class="text-danger">*</span></label>
                                                    <select wire:model="standardAnalyteForm.standard_value_id" class="form-select modern-select @error('standardAnalyteForm.standard_value_id') is-invalid @enderror">
                                                        <option value="">Select Standard Value</option>
                                                        @foreach($standardValues as $value)
                                                            <option value="{{ $value->id }}">{{ $value->name }} ({{ $value->code }})</option>
                                                        @endforeach
                                                    </select>
                                                    @error('standardAnalyteForm.standard_value_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Matrix Operator and Value Fields (shown when Standard Value is selected) --}}
                                        @if($standardAnalyteForm['standard_value_id'])
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i> Additional configuration for the selected standard value.
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Matrix Operator <span class="text-danger">*</span></label>
                                                        <select wire:model="standardAnalyteForm.matrix_operator" class="form-select modern-select @error('standardAnalyteForm.matrix_operator') is-invalid @enderror">
                                                            <option value="">Select Operator</option>
                                                            <option value="max">Max</option>
                                                            <option value="min">Min</option>
                                                            <option value="greater_than">> (Greater Than)</option>
                                                            <option value="less_than">< (Less Than)</option>
                                                        </select>
                                                        @error('standardAnalyteForm.matrix_operator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Actual Value <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="standardAnalyteForm.matrix_value" class="form-control @error('standardAnalyteForm.matrix_value') is-invalid @enderror" placeholder="Enter actual value">
                                                        @error('standardAnalyteForm.matrix_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Hidden tolerance, mean and standard deviation fields --}}
                            <div class="row" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Expected Value</label>
                                        <input type="text" wire:model="standardAnalyteForm.expected_value" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Mean Value</label>
                                        <input type="text" wire:model="standardAnalyteForm.mean_value" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Relative Standard Deviation</label>
                                        <input type="text" wire:model="standardAnalyteForm.rel_std_dev" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance 1</label>
                                        <input type="text" wire:model="standardAnalyteForm.tolerance_1" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance 2</label>
                                        <input type="text" wire:model="standardAnalyteForm.tolerance_2" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Comments</label>
                                <textarea wire:model="standardAnalyteForm.comments" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Recommendations</label>
                                <textarea wire:model="standardAnalyteForm.recommendations" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="standardAnalyteForm.is_active" class="form-check-input" id="standard_analyte_active">
                                            <label class="form-check-label" for="standard_analyte_active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline" style="display: none;">
                                            <input type="checkbox" wire:model="standardAnalyteForm.absolute_tolerance" class="form-check-input" id="absolute_tolerance">
                                            <label class="form-check-label" for="absolute_tolerance">Absolute Tolerance</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStandardAnalyteModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStandardAnalyte">
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

    /* Modal Enhancement Styles */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal.fade .modal-dialog {
        transform: translate(0, -50px);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: none;
    }

    .form-control:focus, .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .modal-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-bottom: none;
    }

    .modal-title {
        font-weight: 600;
    }

    .btn-close {
        filter: invert(1);
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        // Listen for modal opened events
        Livewire.on('modal-opened', (event) => {
            const data = event[0];
            console.log('Modal opened:', data);
            
            // Add loading state to modal
            const modal = document.getElementById('standardAnalyteModal');
            if (modal) {
                modal.classList.add('loading');
            }
            
            // If it's an edit modal, we can add additional JavaScript enhancements here
            if (data.type === 'edit') {
                console.log('Editing standard analyte with ID:', data.id);
                
                // Focus on the first input field after a short delay
                setTimeout(() => {
                    const firstInput = modal.querySelector('.form-control, .form-select');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 300);
            }
            
            // Remove loading state
            setTimeout(() => {
                if (modal) {
                    modal.classList.remove('loading');
                }
            }, 500);
        });

        // Add form validation enhancement
        Livewire.on('validation-error', (errors) => {
            console.log('Validation errors:', errors);
            
            // Scroll to first error field
            const firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
        });

        // Add success feedback
        Livewire.on('standard-analyte-saved', (data) => {
            console.log('Standard analyte saved successfully:', data);
            
            // Show success animation
            const modal = document.getElementById('standardAnalyteModal');
            if (modal) {
                modal.classList.add('success');
                setTimeout(() => {
                    modal.classList.remove('success');
                }, 2000);
            }
        });
    });

    // Add click animation to edit buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-standard-analyte-btn')) {
            const btn = e.target.closest('.edit-standard-analyte-btn');
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 150);
        }
    });

    // Add form field enhancement
    document.addEventListener('DOMContentLoaded', function() {
        // Add floating labels effect
        const formInputs = document.querySelectorAll('.form-control, .form-select');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
            
            // Check if input has value on load
            if (input.value) {
                input.parentElement.classList.add('focused');
            }
        });
    });

    // Modal form enhancement
    function enhanceModalForm() {
        const modal = document.getElementById('standardAnalyteModal');
        if (!modal) return;

        // Add form validation on blur
        const inputs = modal.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required') || field.classList.contains('required');
        
        if (isRequired && !value) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else if (value) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        } else {
            field.classList.remove('is-invalid', 'is-valid');
        }
    }

    // Initialize when modal is shown
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target.id === 'standardAnalyteModal') {
            enhanceModalForm();
        }
    });

    // Enhanced form behavior for value type changes
    document.addEventListener('livewire:init', () => {
        // Listen for value type changes
        Livewire.on('value-type-changed', (event) => {
            const valueType = event[0];
            console.log('Value type changed to:', valueType);
            
            // Add smooth transition effects
            const rangeCard = document.querySelector('.card.border-primary');
            const useValueCard = document.querySelector('.card.border-info');
            
            if (valueType === 'range') {
                if (rangeCard) {
                    rangeCard.style.opacity = '0';
                    rangeCard.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        rangeCard.style.transition = 'all 0.3s ease';
                        rangeCard.style.opacity = '1';
                        rangeCard.style.transform = 'translateY(0)';
                    }, 100);
                }
            } else if (valueType === 'use_value') {
                if (useValueCard) {
                    useValueCard.style.opacity = '0';
                    useValueCard.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        useValueCard.style.transition = 'all 0.3s ease';
                        useValueCard.style.opacity = '1';
                        useValueCard.style.transform = 'translateY(0)';
                    }, 100);
                }
            }
        });

        // Add real-time validation feedback
        Livewire.on('field-validated', (event) => {
            const data = event[0];
            const field = document.querySelector(`[wire\\:model="${data.field}"]`);
            if (field) {
                if (data.valid) {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                } else {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                }
            }
        });
    });

    // Add smooth animations for conditional fields
    function addSmoothTransitions() {
        const style = document.createElement('style');
        style.textContent = `
            .card.border-primary, .card.border-info {
                transition: all 0.3s ease;
                transform: translateY(0);
                opacity: 1;
            }
            
            .form-group {
                transition: all 0.2s ease;
            }
            
            .alert {
                transition: all 0.3s ease;
            }
            
            .form-check-input:checked + .form-check-label {
                color: #007bff;
                font-weight: 600;
            }
            
            .form-check-input:checked {
                background-color: #007bff;
                border-color: #007bff;
            }
        `;
        document.head.appendChild(style);
    }

    // Initialize smooth transitions
    addSmoothTransitions();
    </script>
</div>
