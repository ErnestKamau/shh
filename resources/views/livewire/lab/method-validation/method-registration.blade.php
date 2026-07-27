<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
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
            color: #495057;
        }
        
        /* Status icons */
        .status-icon-active {
            color: #28a745;
            font-size: 1.25rem;
            background: none;
            border: none;
        }
        
        .status-icon-inactive {
            color: #6c757d;
            font-size: 1.25rem;
            background: none;
            border: none;
        }
        
        /* Action icons */
        .action-icon-btn {
            background: none;
            border: none;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .action-icon-btn:hover {
            opacity: 0.7;
        }
        
        .action-icon-view {
            color: #17a2b8;
            font-size: 1.1rem;
        }
        
        .action-icons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }
        
        /* Button group styling */
        .btn-group-sm > .btn,
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                border-radius: 0.25rem !important;
                margin-bottom: 0.25rem;
            }
        }
    </style>

    <!-- Search and Basic Filters -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="mdi mdi-magnify"></i> Search & Basic Filters</h5>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" wire:click="toggleAdvancedFilters" id="advancedFiltersToggleBtn" onclick="fallbackToggle()">
                        <i class="mdi {{ $showAdvancedFilters ? 'mdi-filter-variant-plus' : 'mdi-filter-variant' }}" id="advancedFiltersIcon"></i> 
                        <span id="advancedFiltersText">{{ $showAdvancedFilters ? 'Hide' : 'Show' }} Advanced Filters</span>
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
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-lg border-start-0" 
                               placeholder="Search by method name, code, description, etc...">
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
            
            <!-- Quick Stats -->
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-end align-items-center">
                        <div>
                            <span class="text-muted">{{ $methods->total() }} methods found</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters (Collapsible) -->
    <div class="advanced-filters-section" id="advancedFiltersSection" style="display: {{ $showAdvancedFilters ? 'block' : 'none' }};">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-filter-variant"></i> Advanced Filters</h6>
            </div>
            <div class="card-body">
                <!-- Filters Row 1 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="method_type" class="form-label fw-bold">Method Type</label>
                        <x-searchable-select
                            wire:model.live="filters.method_type"
                            :options="collect($methodTypes)->map(fn($type, $id) => ['id' => $id, 'name' => $type])"
                            placeholder="Search method types..."
                            empty-label="All Types"
                        />
                    </div>
                    <div class="col-md-3">
                        <label for="active_status" class="form-label fw-bold">Status</label>
                        <select wire:model.live="filters.active_status" class="form-select form-select-lg" id="active_status">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="is_ltm" class="form-label fw-bold">Method Category</label>
                        <select wire:model.live="filters.is_ltm" class="form-select form-select-lg" id="is_ltm">
                            <option value="">All Categories</option>
                            <option value="1">Laboratory Test Method</option>
                            <option value="0">Reference Method</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="company" class="form-label fw-bold">Company</label>
                        <x-searchable-select
                            wire:model.live="filters.company"
                            :options="collect($companies)->map(fn($name, $id) => ['id' => $id, 'name' => $name])"
                            placeholder="Search companies..."
                            empty-label="All Companies"
                        />
                    </div>
                </div>

                <!-- Filters Row 2 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="created_date_from" class="form-label fw-bold">Created Date From</label>
                        <input wire:model.live="filters.created_date_from" type="date" class="form-control form-control-lg" id="created_date_from">
                    </div>
                    <div class="col-md-3">
                        <label for="created_date_to" class="form-label fw-bold">Created Date To</label>
                        <input wire:model.live="filters.created_date_to" type="date" class="form-control form-control-lg" id="created_date_to">
                    </div>
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
                            @foreach($availableMonths as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filters Row 3 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="updated_date_from" class="form-label fw-bold">Updated Date From</label>
                        <input wire:model.live="filters.updated_date_from" type="date" class="form-control form-control-lg" id="updated_date_from">
                    </div>
                    <div class="col-md-3">
                        <label for="updated_date_to" class="form-label fw-bold">Updated Date To</label>
                        <input wire:model.live="filters.updated_date_to" type="date" class="form-control form-control-lg" id="updated_date_to">
                    </div>
                    <div class="col-md-3">
                        <label for="updated_date_year" class="form-label fw-bold">Updated Year</label>
                        <select wire:model.live="filters.updated_date_year" class="form-select form-select-lg" id="updated_date_year">
                            <option value="">All Years</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="updated_date_month" class="form-label fw-bold">Updated Month</label>
                        <select wire:model.live="filters.updated_date_month" class="form-select form-select-lg" id="updated_date_month">
                            <option value="">All Months</option>
                            @foreach($availableMonths as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
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

    <!-- Methods Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="mdi mdi-file-document-multiple"></i> Methods Sent for Validation</h5>
                <div class="d-flex align-items-center">
                    <span class="me-3">Total: {{ $methods->total() }} methods</span>
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
                                <a href="#" wire:click.prevent="sortBy('name')" class="text-dark text-decoration-none">
                                    Method Name
                                    @if($sortField === 'name')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('code')" class="text-dark text-decoration-none">
                                    Code
                                    @if($sortField === 'code')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Type</th>
                            <th>Company</th>
                            <th class="text-center">Active</th>
                            <th>Reference Method</th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('created_at')" class="text-dark text-decoration-none">
                                    Created Date
                                    @if($sortField === 'created_at')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($methods as $index => $method)
                        <tr>
                            <td class="text-center">
                                {{ ($methods->currentPage() - 1) * $methods->perPage() + $index + 1 }}
                            </td>
                            <td>
                                <strong>{{ $method->name }}</strong>
                            </td>
                            <td>
                                {{ $method->code ?? '-' }}
                            </td>
                            <td>
                                {{ $method->method_type_name ?? '-' }}
                            </td>
                            <td>
                                @if($method->company_id)
                                    @php
                                        $companyName = $companies[$method->company_id] ?? 'Unknown';
                                    @endphp
                                    <span class="text-muted">{{ $companyName }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($method->active == 1)
                                    <i class="mdi mdi-check-circle status-icon-active" title="Active"></i>
                                @else
                                    <i class="mdi mdi-close-circle status-icon-inactive" title="Inactive"></i>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $method->reference_method_name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $method->created_at ? \Carbon\Carbon::parse($method->created_at)->format('Y-m-d H:i:s') : '-' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="action-icons" role="group">
                                    <button class="action-icon-btn" 
                                            wire:click="viewDetails({{ $method->id }})"
                                            title="View Method Details">
                                        <i class="mdi mdi-eye action-icon-view"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="mdi mdi-file-document-multiple text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No methods sent for validation found.</p>
                                    <button wire:click="clearFilters" class="btn btn-outline-primary">
                                        <i class="mdi mdi-refresh"></i> Clear Filters
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($methods->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Showing {{ $methods->firstItem() }} to {{ $methods->lastItem() }} of {{ $methods->total() }} results
                        </div>
                        <div>
                            {{ $methods->links('livewire::bootstrap') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Fallback toggle function
        function fallbackToggle() {
            const filtersSection = document.getElementById('advancedFiltersSection');
            if (filtersSection) {
                const currentDisplay = window.getComputedStyle(filtersSection).display;
                const isVisible = currentDisplay === 'block';
                
                filtersSection.style.display = isVisible ? 'none' : 'block';
                
                // Update button text and icon
                const buttonText = document.getElementById('advancedFiltersText');
                const buttonIcon = document.getElementById('advancedFiltersIcon');
                if (buttonText) {
                    buttonText.textContent = isVisible ? 'Show Advanced Filters' : 'Hide Advanced Filters';
                }
                if (buttonIcon) {
                    buttonIcon.className = isVisible ? 'mdi mdi-filter-variant' : 'mdi mdi-filter-variant-plus';
                }
                
                // Try to sync with Livewire if available
                if (typeof Livewire !== 'undefined') {
                    try {
                        const livewireElement = document.querySelector('[wire\\:id]');
                        if (livewireElement) {
                            const componentId = livewireElement.getAttribute('wire:id');
                            if (Livewire.find && Livewire.find(componentId)) {
                                Livewire.find(componentId).set('showAdvancedFilters', !isVisible);
                            }
                        }
                    } catch (e) {
                        // Silent fail
                    }
                }
            }
        }
        
        document.addEventListener('livewire:load', function () {
            // Handle modal backdrop clicks
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop')) {
                    Livewire.dispatch('closeAllModals');
                }
            });

            // Handle escape key to close modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('closeAllModals');
                }
            });
        });
    </script>

    <!-- Toastr CSS and JS for notifications -->
    @if(!isset($toastrIncluded))
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
        </script>
        @php $toastrIncluded = true; @endphp
    @endif
</div>