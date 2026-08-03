<div class="lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Error Messages -->
    @if($errors->has('filters.created_date_from'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle"></i>
            <strong>Date Range Too Large:</strong> Please select a date range of 365 days or less to prevent performance issues.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Search and Basic Filters -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="mdi mdi-calculator"></i> Search & Basic Filters</h5>
            <div>
                    <button type="button" class="btn btn-outline-primary me-2" wire:click="toggleAdvancedFilters" id="advancedFiltersToggleBtn">
                        <i class="mdi {{ $showAdvancedFilters ? 'mdi-filter-variant-plus' : 'mdi-filter-variant' }}" id="advancedFiltersIcon"></i> 
                        <span id="advancedFiltersText">Advanced Filters</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar and Per Page -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="mdi mdi-magnify text-info"></i>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-lg border-start-0" 
                               placeholder="Search by analyte name, code, method name, etc...">
                    </div>
                        </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label me-2 mb-0">Show:</label>
                        <select wire:model.live="perPage" class="form-select form-select-lg" id="perPage" style="width: auto;">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
                </div>
            </div>

    <!-- Advanced Filters (Collapsible) -->
    <div class="advanced-filters-section" id="advancedFiltersSection" style="display: {{ $showAdvancedFilters ? 'block' : 'none' }}; transition: all 0.3s ease;">
                <div class="card mb-3">
                    <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-filter-variant"></i> Advanced Filters</h6>
                    </div>
                    <div class="card-body">
                <!-- Filters Row 1 -->
                <div class="row mb-3">
                            <div class="col-md-3">
                        <label for="analyte_name" class="form-label fw-bold">Analyte Name</label>
                        <select wire:model.live="filters.analyte_name" class="form-select form-select-lg" id="analyte_name">
                            <option value="">All Analytes</option>
                                        @foreach($availableAnalyteNames as $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-3">
                        <label for="analyte_code" class="form-label fw-bold">Report Display</label>
                        <select wire:model.live="filters.analyte_code" class="form-select form-select-lg" id="analyte_code">
                            <option value="">All Codes</option>
                                        @foreach($availableAnalyteCodes as $code)
                                            <option value="{{ $code }}">{{ $code }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-3">
                        <label for="method_name" class="form-label fw-bold">Method Name</label>
                        <select wire:model.live="filters.method_name" class="form-select form-select-lg" id="method_name">
                            <option value="">All Methods</option>
                                        @foreach($availableMethodNames as $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-3">
                        <label for="coverage_factor_k" class="form-label fw-bold">Coverage Factor (k)</label>
                        <select wire:model.live="filters.coverage_factor_k" class="form-select form-select-lg" id="coverage_factor_k">
                            <option value="">All Factors</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                            </div>

                <!-- Filters Row 2 -->
                <div class="row mb-3">
                            <div class="col-md-3">
                        <label for="created_date_year" class="form-label fw-bold">Created Year</label>
                        <select wire:model.live="filters.created_date_year" class="form-select form-select-lg" id="created_date_year">
                            <option value="">All Years</option>
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-3">
                        <label for="created_date_month" class="form-label fw-bold">Created Month</label>
                        <select wire:model.live="filters.created_date_month" class="form-select form-select-lg" id="created_date_month">
                            <option value="">All Months</option>
                                        @foreach($availableMonths as $month)
                                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-3">
                        <label for="active" class="form-label fw-bold">Status</label>
                        <select wire:model.live="filters.active" class="form-select form-select-lg" id="active">
                            <option value="">All Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                    <div class="col-md-3">
                        <label for="created_date_from" class="form-label fw-bold">Created Date From</label>
                        <input wire:model.live="filters.created_date_from" type="date" class="form-control form-control-lg" id="created_date_from">
                            </div>
                        </div>

                <!-- Filters Row 3 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="created_date_to" class="form-label fw-bold">Created Date To</label>
                        <input wire:model.live="filters.created_date_to" type="date" class="form-control form-control-lg" id="created_date_to">
                    </div>
                    <div class="col-md-9">
                        <!-- Empty space for better alignment -->
                    </div>
                </div>

                <!-- Action Buttons Row -->
                <div class="row">
                    <div class="col-md-12 d-flex align-items-end">
                        <div class="d-flex" style="gap: 1rem;">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-lg">
                                <i class="mdi mdi-refresh"></i> Clear Filters
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg" wire:click="toggleAdvancedFilters">
                                <i class="mdi {{ $showAdvancedFilters ? 'mdi-eye-off' : 'mdi-eye' }}"></i> 
                                {{ $showAdvancedFilters ? 'Hide Filters' : 'Show Filters' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Uncertainty Budgets Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="mdi mdi-calculator"></i> Uncertainty Budgets</h5>
                <div class="d-flex align-items-center">
                    <span class="me-3">Total: {{ $uncertaintyBudgets->total() }} budgets</span>
                    <button type="button" class="btn btn-outline-success btn-sm mr-2" wire:click="exportToExcel">
                        <i class="mdi mdi-file-excel"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUncertaintyBudgetModal">
                        <i class="mdi mdi-plus"></i> Add Budget
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">#</th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('analytes.name')" class="text-dark text-decoration-none">
                                Analyte Name
                                @if($sortField === 'analytes.name')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('analytes.code')" class="text-dark text-decoration-none">
                                    Report Display
                                @if($sortField === 'analytes.code')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('analysis_methods.name')" class="text-dark text-decoration-none">
                                Methods
                                @if($sortField === 'analysis_methods.name')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('coverage_factor_k')" class="text-dark text-decoration-none">
                                    Coverage Factor (k)
                                    @if($sortField === 'coverage_factor_k')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('confidence_level')" class="text-dark text-decoration-none">
                                    Confidence Level
                                    @if($sortField === 'confidence_level')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('combined_standard_uncertainty')" class="text-dark text-decoration-none">
                                    Combined Uncertainty
                                @if($sortField === 'combined_standard_uncertainty')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('expanded_uncertainty')" class="text-dark text-decoration-none">
                                Expanded Uncertainty
                                @if($sortField === 'expanded_uncertainty')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('created_at')" class="text-dark text-decoration-none">
                                    Created Date
                                    @if($sortField === 'created_at')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uncertaintyBudgets as $index => $budget)
                            <tr>
                                <td class="text-center">
                                    {{ ($uncertaintyBudgets->currentPage() - 1) * $uncertaintyBudgets->perPage() + $index + 1 }}
                                </td>
                                <td>
                                    <strong class="text-primary">{{ $budget->analyte_name ?? '-' }}</strong>
                                </td>
                                <td>
                                    <code class="text-info">{{ $budget->analyte_code ?? '-' }}</code>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $budget->method_name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ $budget->coverage_factor_k ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ $budget->confidence_level ?? '-' }}%</span>
                                </td>
                                <td>
                                    @if($budget->combined_standard_uncertainty)
                                        <span class="badge badge-info">{{ $budget->formatted_combined_uncertainty }}</span>
                                    @else
                                        <span class="text-muted">Not calculated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($budget->expanded_uncertainty)
                                        <span class="badge badge-success">{{ $budget->formatted_expanded_uncertainty }}</span>
                                    @else
                                        <span class="text-muted">Not calculated</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $budget->created_at ? \Carbon\Carbon::parse($budget->created_at)->format('Y-m-d H:i:s') : '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($budget->active)
                                        <span class="badge badge-success"><i class="mdi mdi-check"></i> Active</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="mdi mdi-close"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="uncertainty-actions d-inline-flex align-items-center justify-content-center">
                                        <a href="{{ route('uncertainty-budgets.show', $budget->id) }}"
                                           class="btn btn-sm btn-outline-primary uncertainty-action-btn" title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        <a href="{{ route('uncertainty-budgets.edit', $budget->id) }}"
                                           class="btn btn-sm btn-outline-info uncertainty-action-btn" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                wire:click="delete('{{ $budget->id }}')"
                                                class="btn btn-sm btn-outline-danger uncertainty-action-btn" title="Deactivate"
                                                onclick="return confirm('Are you sure you want to deactivate this budget?')">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="mdi mdi-calculator text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No uncertainty budgets found.</p>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUncertaintyBudgetModal">
                                        <i class="mdi mdi-plus"></i> Create First Budget
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($uncertaintyBudgets->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                    <div>
                            Showing {{ $uncertaintyBudgets->firstItem() }} to {{ $uncertaintyBudgets->lastItem() }} of {{ $uncertaintyBudgets->total() }} results
                    </div>
                    <div>
                            {{ $uncertaintyBudgets->links('livewire::bootstrap') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>


    <!-- Add Uncertainty Budget Modal -->
    <div class="modal fade" id="addUncertaintyBudgetModal" tabindex="-1" role="dialog" aria-labelledby="addUncertaintyBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUncertaintyBudgetModalLabel">
                        <i class="mdi mdi-plus"></i> Add Uncertainty Budget
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addUncertaintyBudgetForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_analyte_id" class="form-label">Analyte <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('analyte_id') is-invalid @enderror" id="modal_analyte_id" name="analyte_id" required>
                                        <option value="">Select Analyte</option>
                                        @foreach(\App\Analyte::where('active', true)->where('company_id', getUserCompany())->orderBy('name')->get() as $analyte)
                                            <option value="{{ $analyte->id }}">
                                                {{ $analyte->name }} ({{ $analyte->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('analyte_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_method_ids" class="form-label">Methods <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('method_ids') is-invalid @enderror" id="modal_method_ids" name="method_ids[]" multiple required>
                                        @foreach(\App\AnalysisMethod::where('active', true)->where('company_id', getUserCompany())->orderBy('name')->get() as $method)
                                            <option value="{{ $method->id }}">
                                                {{ $method->name }} ({{ $method->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select one or more methods for this analyte. You can create multiple budgets for the same analyte with different methods.</small>
                                    @error('method_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_coverage_factor_k" class="form-label">Coverage Factor (k) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('coverage_factor_k') is-invalid @enderror" 
                                           id="modal_coverage_factor_k" name="coverage_factor_k" 
                                           value="2.00" step="0.01" min="1" max="10" required>
                                    <small class="form-text text-muted">Usually 2 for 95% confidence level</small>
                                    @error('coverage_factor_k')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_confidence_level" class="form-label">Confidence Level (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('confidence_level') is-invalid @enderror" 
                                           id="modal_confidence_level" name="confidence_level" 
                                           value="95.00" step="0.01" min="50" max="99.99" required>
                                    <small class="form-text text-muted">Usually 95% for most applications</small>
                                    @error('confidence_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Create Uncertainty Budget
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .advanced-filters-section {
            transition: all 0.3s ease;
        }
        
        .form-select-lg, .form-control-lg {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        
        .btn-lg {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        
        @media (max-width: 768px) {
            .form-select-lg, .form-control-lg {
                font-size: 0.875rem;
            }
        }
        
        
        /* Badge styling */
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.5rem;
        }
        
        .badge-light {
            background-color: #f8f9fa;
            color: #495057;
        }
        
        /* Table styling */
        .table-condensed th,
        .table-condensed td {
            padding: 0.5rem;
            vertical-align: middle;
        }
        
        .table-condensed th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .uncertainty-actions {
            gap: 0.4rem;
        }

        .uncertainty-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            border-radius: 0.35rem;
            line-height: 1;
        }

        .uncertainty-action-btn .mdi {
            font-size: 1rem;
            line-height: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .uncertainty-actions {
                flex-direction: column;
                gap: 0.3rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 for modal dropdowns
            $('#addUncertaintyBudgetModal').on('shown.bs.modal', function() {
                $('#modal_analyte_id, #modal_method_ids').select2({
                    placeholder: 'Select options...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#addUncertaintyBudgetModal')
                });
            });

            // Check for existing budgets when analyte is selected
            $('#modal_analyte_id').on('change', function() {
                const analyteId = $(this).val();
                const methodSelect = $('#modal_method_ids');
                
                if (analyteId) {
                    // Fetch existing budgets for this analyte
                    fetch(`/lab-uncertainty/api/existing-budgets/${analyteId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Update method options to show which ones already have budgets
                            methodSelect.find('option').each(function() {
                                const option = $(this);
                                const methodId = option.val();
                                
                                if (methodId && data.existing_methods.includes(parseInt(methodId))) {
                                    option.text(option.text() + ' (Already has budget)');
                                    option.addClass('text-warning');
                                } else {
                                    option.text(option.text().replace(' (Already has budget)', ''));
                                    option.removeClass('text-warning');
                                }
                            });
                            
                            methodSelect.trigger('change');
                        })
                        .catch(error => {
                            console.error('Error checking existing budgets:', error);
                        });
                } else {
                    // Reset method options
                    methodSelect.find('option').each(function() {
                        const option = $(this);
                        option.text(option.text().replace(' (Already has budget)', ''));
                        option.removeClass('text-warning');
                    });
                    methodSelect.trigger('change');
                }
            });

            // Reset form when modal is hidden
            $('#addUncertaintyBudgetModal').on('hidden.bs.modal', function() {
                $('#addUncertaintyBudgetForm')[0].reset();
                $('#modal_analyte_id, #modal_method_ids').val(null).trigger('change');
            });

            // Handle form submission
            $('#addUncertaintyBudgetForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const selectedMethods = Array.from(document.getElementById('modal_method_ids').selectedOptions);
                
                if (selectedMethods.length === 0) {
                    alert('Please select at least one method for this analyte.');
                    return false;
                }
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Creating...');
                
                console.log('Submitting form to:', '{{ route("uncertainty-budgets.store") }}');
                console.log('Form data entries:', Array.from(formData.entries()));
                
                // Log each form field individually
                console.log('analyte_id:', formData.get('analyte_id'));
                console.log('method_ids:', formData.getAll('method_ids'));
                console.log('coverage_factor_k:', formData.get('coverage_factor_k'));
                console.log('confidence_level:', formData.get('confidence_level'));
                console.log('_token:', formData.get('_token'));
                
                fetch('{{ route("uncertainty-budgets.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    return response.text().then(text => {
                        console.log('Raw response:', text.substring(0, 500) + '...');
                        try {
                            const data = JSON.parse(text);
                            if (!response.ok) {
                                // Handle validation errors (422) or other errors
                                if (response.status === 422) {
                                    let errorMessage = 'Validation failed:';
                                    if (data.errors) {
                                        Object.keys(data.errors).forEach(field => {
                                            errorMessage += '\n' + field + ': ' + data.errors[field].join(', ');
                                        });
                                    } else if (data.message) {
                                        errorMessage = data.message;
                                    }
                                    throw new Error(errorMessage);
                                } else {
                                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                                }
                            }
                            return data;
                        } catch (e) {
                            if (e instanceof SyntaxError) {
                                console.error('Failed to parse JSON:', e);
                                console.error('Response was:', text);
                                throw new Error('Invalid JSON response - server returned HTML instead of JSON');
                            }
                            throw e;
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Close modal and refresh page
                        $('#addUncertaintyBudgetModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error creating uncertainty budget: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating uncertainty budget: ' + error.message);
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalText);
                });
            });
        });
    </script>
</div>