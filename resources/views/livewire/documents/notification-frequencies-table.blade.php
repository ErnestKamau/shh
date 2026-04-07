<div>
    <!-- Search and Basic Filters -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="mdi mdi-magnify"></i> Search & Basic Filters</h5>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="toggleAdvancedFilters()" id="advancedFiltersToggleBtn">
                        <i class="mdi mdi-filter-variant" id="advancedFiltersIcon"></i> 
                        <span id="advancedFiltersText">Advanced Filters</span>
                </button>
                    @if(Auth::user()->canAddDocuments())
                        <a href="{{ route('documents.notification-frequencies.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Frequency
                        </a>
                    @endif
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
                               placeholder="Search by frequency name, description...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label me-2 mb-0">Show:</label>
                        <select wire:model.live="perPage" class="form-select form-select-lg" id="perPage" style="width: auto;">
                            <option value="15">15 per page</option>
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
                        <label for="statusFilter" class="form-label fw-bold">Status</label>
                        <select wire:model.live="statusFilter" class="form-select form-select-lg" id="statusFilter">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                        <label for="intervalFilter" class="form-label fw-bold">Interval</label>
                        <select wire:model.live="intervalFilter" class="form-select form-select-lg" id="intervalFilter">
                        @foreach($intervalOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-end h-100">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-lg me-2">
                                <i class="mdi mdi-refresh"></i> Clear Filters
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg" onclick="toggleAdvancedFilters()">
                                <i class="mdi mdi-eye-off"></i> Hide Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Frequencies Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>
                    <i class="mdi mdi-bell-ring"></i> Notification Frequencies List
                </h5>
                <div class="d-flex align-items-center">
                    <span class="me-3">Total: {{ $notificationFrequencies->total() }} frequencies</span>
        </div>
        </div>
    </div>
        <div class="card-body" wire:loading.class="opacity-50">
            <div class="table-responsive">
                <table class="table table-condensed table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('name')" class="text-dark text-decoration-none">
                                    Frequency Name
                                @if($sortField === 'name')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('description')" class="text-dark text-decoration-none">
                                Description
                                @if($sortField === 'description')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('days_interval')" class="text-dark text-decoration-none">
                                Days Interval
                                @if($sortField === 'days_interval')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('is_active')" class="text-dark text-decoration-none">
                                Status
                                @if($sortField === 'is_active')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('documents_count')" class="text-dark text-decoration-none">
                                Documents Using
                                @if($sortField === 'documents_count')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notificationFrequencies as $index => $frequency)
                        <tr>
                            <td>{{ ($notificationFrequencies->currentPage() - 1) * $notificationFrequencies->perPage() + $index + 1 }}</td>
                            <td>
                                <div>
                                                                         <h6 class="font-14 mb-1">
                                         <a href="{{ route('documents.notification-frequencies.show', $frequency->id) }}" 
                                            class="text-body">
                                             {{ $frequency->name }}
                                         </a>
                                     </h6>
                                </div>
                                </td>
                                <td>
                                    @if($frequency->description)
                                    <span class="text-muted">{{ Str::limit($frequency->description, 50) }}</span>
                                    @else
                                        <span class="text-muted">No description</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $frequency->days_interval }} days</span>
                                </td>
                                <td>
                                    @if($frequency->is_active)
                                    <span class="badge bg-success">
                                        <i class="mdi mdi-check"></i> Active
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">
                                        <i class="mdi mdi-close"></i> Inactive
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $frequency->documents_count ?? 0 }} documents</span>
                                </td>
                                <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('documents.notification-frequencies.show', $frequency->id) }}"
                                       class="btn btn-outline-info" title="View">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        @if(Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments())
                                        <a href="{{ route('documents.notification-frequencies.edit', $frequency->id) }}"
                                           class="btn btn-outline-warning" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                        @endif
                                        @if((Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments()) && ($frequency->documents_count ?? 0) == 0)
                                            <button type="button" 
                                                class="btn btn-outline-danger" 
                                                    title="Delete"
                                                    onclick="confirmDelete({{ $frequency->id }}, '{{ $frequency->name }}')">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        @elseif(Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments())
                                        <button class="btn btn-outline-secondary" 
                                                    disabled 
                                                    title="Cannot delete - in use by documents">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                            <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                    <i class="mdi mdi-bell-off" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No notification frequencies found</p>
                                    @if($search || $statusFilter || $intervalFilter)
                                        <p class="small">Try adjusting your search criteria or filters</p>
                                    @endif
                                        @if(Auth::user()->canAddDocuments())
                                        <a href="{{ route('documents.notification-frequencies.create') }}" class="btn btn-primary mt-2">
                                                <i class="mdi mdi-plus"></i> Add First Frequency
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
    </div>

    <!-- Pagination -->
    @if($notificationFrequencies->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $notificationFrequencies->firstItem() }} to {{ $notificationFrequencies->lastItem() }} of {{ $notificationFrequencies->total() }} results
                    </div>
                    <div>
                        {{ $notificationFrequencies->links('livewire::bootstrap') }}
                    </div>
        </div>
    @endif
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        </div>

    <style>
        /* Enhanced Filter Styling */
        .form-select-lg, .form-control-lg {
            font-size: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-select-lg:focus, .form-control-lg:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-label.fw-bold {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        /* Advanced Filters Card */
        .advanced-filters-section .card {
            border: 2px solid #e9ecef;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.3s ease;
        }
        
        .advanced-filters-section .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #e9ecef;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        /* Button Styling */
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
        }
        
        .btn-outline-primary:hover {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .btn-outline-info {
            border-color: #17a2b8;
            color: #17a2b8;
        }
        
        .btn-outline-info:hover {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }
        
        /* Search Bar Enhancement */
        .input-group .form-control-lg {
            border-left: none;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid black;
            color: black;
            font-weight: 500;
        }
        
        /* Advanced Filters Animation */
        .advanced-filters-section {
            transition: all 0.2s ease-in-out;
            overflow: hidden;
        }
        
        .advanced-filters-section.show {
            animation: slideDown 0.2s ease-in-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-5px);
                max-height: 0;
            }
            to {
                opacity: 1;
                transform: translateY(0);
                max-height: 1000px;
            }
        }
        
        /* Smooth button transitions */
        .btn {
            transition: all 0.15s ease-in-out;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-select-lg, .form-control-lg {
                font-size: 0.9rem;
                padding: 0.5rem 0.75rem;
            }
            
            .btn-lg {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        function confirmDelete(id, name) {
            if (confirm(`Are you sure you want to delete the notification frequency "${name}"? This action cannot be undone.`)) {
                @this.deleteFrequency(id);
            }
        }

        // Fast Advanced Filters Toggle
        function toggleAdvancedFilters() {
            const section = document.getElementById('advancedFiltersSection');
            const icon = document.getElementById('advancedFiltersIcon');
            const text = document.getElementById('advancedFiltersText');
            const isVisible = section.style.display !== 'none';
            
            if (isVisible) {
                // Hide filters
                section.style.display = 'none';
                icon.className = 'mdi mdi-filter-variant';
                text.textContent = 'Advanced Filters';
                
                // Update Livewire state without re-rendering
                @this.set('showAdvancedFilters', false);
            } else {
                // Show filters
                section.style.display = 'block';
                icon.className = 'mdi mdi-filter-variant-plus';
                text.textContent = 'Hide Filters';
                
                // Update Livewire state without re-rendering
                @this.set('showAdvancedFilters', true);
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const section = document.getElementById('advancedFiltersSection');
            const icon = document.getElementById('advancedFiltersIcon');
            const text = document.getElementById('advancedFiltersText');
            
            // Set initial state based on Livewire property
            if (@json($showAdvancedFilters)) {
                section.style.display = 'block';
                icon.className = 'mdi mdi-filter-variant-plus';
                text.textContent = 'Hide Filters';
            } else {
                section.style.display = 'none';
                icon.className = 'mdi mdi-filter-variant';
                text.textContent = 'Advanced Filters';
            }
        });
        
        // Add smooth animation for filter changes
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                // Add smooth transition when filters are applied
                const filterInputs = document.querySelectorAll('.form-select-lg, .form-control-lg');
                filterInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        this.style.transition = 'all 0.3s ease';
                        this.style.transform = 'scale(1.02)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 200);
                    });
                });
            });
        });
    </script>
</div>
