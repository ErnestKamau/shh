<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
<style>
    .card {
        border: none;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        margin-bottom: 1rem;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1.25rem;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }
    
    .badge-lg {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .methods-in-validation {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    
    .comparison-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .statistics-card {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .acceptable {
        color: #28a745;
        font-weight: 600;
    }
    
    .not-acceptable {
        color: #dc3545;
        font-weight: 600;
    }
    
    .approval-status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 600;
    }
    
    .approval-status-approved {
        background-color: #d4edda;
        color: #155724;
    }
    
    .approval-status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .approval-status-pending {
        background-color: #fff3cd;
        color: #856404;
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
    
    .action-icon-chart {
        color: #28a745;
        font-size: 1.1rem;
    }
    
    .action-icon-approve {
        color: #007bff;
        font-size: 1.1rem;
    }
    
    .action-icons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
    }
    
    .btn-group .dropdown-toggle::after {
        display: none;
    }
    
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-item i {
        margin-right: 0.5rem;
        width: 1rem;
        text-align: center;
    }
    
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
    }
    
</style>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" wire:model.live="search" class="form-control form-control-sm" 
                               placeholder="Search methods by name, code, or description...">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="mdi mdi-magnify"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-outline-secondary btn-sm" wire:click="clearFilters">
                        <i class="mdi mdi-refresh"></i> Clear
                    </button>
                    <button class="btn btn-outline-primary btn-sm ml-2" wire:click="toggleAdvancedFilters">
                        <i class="mdi mdi-filter"></i> 
                        {{ $showAdvancedFilters ? 'Hide' : 'Show' }} Filters
                    </button>
                    <span class="text-muted ml-2">{{ $methodsInValidation->total() }} methods</span>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        @if($showAdvancedFilters)
        <div class="card-body border-top">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Method Type</label>
                    <x-searchable-select
                        wire:model.live="filters.method_type"
                        :options="collect($methodTypes)->map(fn($name, $id) => ['id' => $id, 'name' => $name])"
                        placeholder="Search method types..."
                        empty-label="All Types"
                    />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Active Status</label>
                    <select wire:model.live="filters.active_status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <x-searchable-select
                        wire:model.live="filters.company"
                        :options="collect($companies)->map(fn($name, $id) => ['id' => $id, 'name' => $name])"
                        placeholder="Search companies..."
                        empty-label="All Companies"
                    />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Per Page</label>
                    <select wire:model.live="perPage" class="form-control form-control-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Created From Date</label>
                    <input type="date" wire:model.live="filters.created_date_from" class="form-control form-control-sm">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Created To Date</label>
                    <input type="date" wire:model.live="filters.created_date_to" class="form-control form-control-sm">
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Methods in Validation Section -->
    @if($methodsInValidation->count() > 0)
    <div class="card methods-in-validation">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-flask text-muted"></i> Methods in Validation 
                <span class="text-muted">({{ $methodsInValidation->count() }})</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th wire:click="sortBy('code')" style="cursor: pointer;">
                                Method Code
                                @if($sortField === 'code')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Method Name
                                @if($sortField === 'name')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>Type</th>
                            <th>Testing Approach</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Reference Method</th>
                            <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                Created Date
                                @if($sortField === 'created_at')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($methodsInValidation as $index => $method)
                        <tr>
                            <td>{{ $methodsInValidation->firstItem() + $index }}</td>
                            <td>
                                {{ $method->code }}
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('method-validation.comparison', ['methodId' => $method->id]) }}" class="text-dark font-weight-bold">
                                        <strong>{{ $method->name }}</strong>
                                    </a>
                                </div>
                                <small class="text-muted">{{ Str::limit($method->description, 50) }}</small>
                            </td>
                            <td>
                                {{ $method->method_type_name }}
                            </td>
                            <td>
                                @if(isset($method->testing_option) && $method->testing_option === 'lab_with_reference_results')
                                    <small class="text-muted">Lab + Ref Results</small>
                                    <small class="text-muted d-block">Pre-provided values</small>
                                @else
                                    <small class="text-muted">Lab + Ref Method</small>
                                    <small class="text-muted d-block">Separate samples</small>
                                @endif
                            </td>
                            <td>
                                @if($method->company_id)
                                    @php
                                        $companyName = $companies[$method->company_id] ?? 'Unknown';
                                    @endphp
                                    <span class="text-muted">{{ $companyName }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $this->getValidationStatusText($method->validation_status) }}
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $method->reference_method_name }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $method->created_at->format('M d, Y') }}<br>
                                    {{ $method->created_at->format('h:i A') }}
                                </small>
                            </td>
                            <td>
                                <div class="action-icons" role="group">
                                    <a href="{{ route('method-validation.comparison', ['methodId' => $method->id]) }}" 
                                       class="action-icon-btn" title="View Method Comparison">
                                        <i class="mdi mdi-chart-line action-icon-chart"></i>
                                    </a>
                                    @if(auth()->user()->checkApproveMethodsRole())
                                    <div class="btn-group" role="group">
                                        <button type="button" class="action-icon-btn dropdown-toggle" 
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                title="Approve/Reject Method">
                                            <i class="mdi mdi-check-decagram action-icon-approve"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" 
                                               wire:click.prevent="openApprovalModal({{ $method->id }}, 'approve')">
                                                <i class="mdi mdi-thumb-up"></i> Approve Method
                                            </a>
                                            <a class="dropdown-item" href="#" 
                                               wire:click.prevent="openApprovalModal({{ $method->id }}, 'reject')">
                                                <i class="mdi mdi-thumb-down"></i> Reject Method
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" 
                                               wire:click.prevent="openReturnSampleModal({{ $method->id }})">
                                                <i class="mdi mdi-arrow-left"></i> Return Sample to Lab
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="mdi mdi-flask-outline" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No methods in validation found.</p>
                                        <small>Methods will appear here when they are sent for verification.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $methodsInValidation->links() }}
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Methods in Validation</h5>
            <p class="text-muted">Methods will appear here when they are sent for verification with status "in_validation".</p>
        </div>
    </div>
    @endif

    <!-- Method Approval Modal -->
<div class="modal fade" id="methodApprovalModal" tabindex="-1" role="dialog" aria-labelledby="methodApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="methodApprovalModalLabel">
                    <i class="mdi mdi-check-decagram text-primary"></i> 
                    <span id="approvalModalTitle">Method Approval</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    <span id="approvalModalMessage">Please confirm your decision for this method validation.</span>
                </div>
                
                <div class="form-group">
                    <label for="approvalAction" class="control-label">Action <span class="text-danger">*</span></label>
                    <select wire:model="approvalAction" id="approvalAction" class="form-control" required>
                        <option value="">Select Action...</option>
                        <option value="approve">Approve Method</option>
                        <option value="reject">Reject Method</option>
                    </select>
                    @error('approvalAction') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label for="approvalReason" class="control-label">Reason/Comments <span class="text-danger">*</span></label>
                    <textarea wire:model="approvalReason" id="approvalReason" class="form-control" rows="4" 
                              placeholder="Please provide reason for approval or rejection..." required></textarea>
                    @error('approvalReason') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="mdi mdi-close"></i> Cancel
                </button>
                <button type="button" wire:click="submitApproval" class="btn btn-primary" 
                        id="submitApprovalBtn" disabled>
                    <i class="mdi mdi-check"></i> Submit Decision
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return Sample to Lab Modal -->
<div class="modal fade" id="returnSampleModal" tabindex="-1" role="dialog" aria-labelledby="returnSampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnSampleModalLabel">
                    <i class="mdi mdi-arrow-left text-warning"></i> 
                    <span id="returnModalTitle">Return Sample to Lab</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert"></i>
                    <span id="returnModalMessage">Please provide a reason for returning this sample to the lab.</span>
                </div>
                
                <div class="form-group">
                    <label for="returnAction" class="control-label">Action <span class="text-danger">*</span></label>
                    <select wire:model="returnAction" id="returnAction" class="form-control" required>
                        <option value="">Select Action...</option>
                        <option value="return_to_lab">Return Sample to Lab</option>
                    </select>
                    @error('returnAction') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label for="returnReason" class="control-label">Reason/Comments <span class="text-danger">*</span></label>
                    <textarea wire:model="returnReason" id="returnReason" class="form-control" rows="4" 
                              placeholder="Please provide reason for returning the sample to the lab..." required></textarea>
                    @error('returnReason') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-check">
                    <input wire:model="sendReturnNotification" type="checkbox" class="form-check-input" id="sendReturnNotification">
                    <label class="form-check-label" for="sendReturnNotification">
                        Send Email Notification
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="mdi mdi-close"></i> Cancel
                </button>
                <button type="button" wire:click="submitReturnSample" class="btn btn-warning" 
                        id="submitReturnBtn" disabled>
                    <i class="mdi mdi-arrow-left"></i> Submit Decision
                </button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Data Review Analysis page loaded');
});

document.addEventListener('livewire:load', function () {
    console.log('Livewire loaded');
});

document.addEventListener('livewire:updated', function () {
    console.log('Livewire updated');
});

// Add debugging for Livewire events
document.addEventListener('livewire:init', function () {
    console.log('Livewire initialized');
    
    // Listen for Livewire events
    Livewire.on('redirect', (url) => {
        console.log('Livewire redirect event:', url);
        window.location.href = url;
    });
    
    // Listen for the redirect-to-comparison event
    Livewire.on('redirect-to-comparison', (event) => {
        console.log('Redirect to comparison event:', event);
        if (event.url) {
            window.location.href = event.url;
        }
    });
});

// Debug function to test if Livewire is working
window.testLivewire = function() {
    console.log('Testing Livewire...');
    Livewire.emit('test-event');
};

// Modal functionality
document.addEventListener('livewire:init', function() {
    // Show modal when Livewire triggers it
    Livewire.on('showApprovalModal', function(data) {
        $('#approvalModalTitle').text(data.title);
        $('#approvalModalMessage').text(data.message);
        $('#methodApprovalModal').modal('show');
    });
    
    // Hide modal when Livewire triggers it
    Livewire.on('hideApprovalModal', function() {
        $('#methodApprovalModal').modal('hide');
    });
    
    // Show return sample modal when Livewire triggers it
    Livewire.on('showReturnSampleModal', function(data) {
        $('#returnModalTitle').text(data.title);
        $('#returnModalMessage').text(data.message);
        $('#returnSampleModal').modal('show');
    });
    
    // Hide return sample modal when Livewire triggers it
    Livewire.on('hideReturnSampleModal', function() {
        $('#returnSampleModal').modal('hide');
    });
    
    
});

document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable submit button based on form completion
    $('#approvalAction, #approvalReason').on('change input', function() {
        var action = $('#approvalAction').val();
        var reason = $('#approvalReason').val().trim();
        
        console.log('Form validation:', { action: action, reason: reason, reasonLength: reason.length });
        
        if (action && reason && reason.length >= 10) {
            $('#submitApprovalBtn').prop('disabled', false);
            console.log('Submit button enabled');
        } else {
            $('#submitApprovalBtn').prop('disabled', true);
            console.log('Submit button disabled');
        }
    });
    
    // Enable/disable return sample submit button based on form completion
    $('#returnAction, #returnReason').on('change input', function() {
        var action = $('#returnAction').val();
        var reason = $('#returnReason').val().trim();
        
        if (action && reason && reason.length >= 10) {
            $('#submitReturnBtn').prop('disabled', false);
        } else {
            $('#submitReturnBtn').prop('disabled', true);
        }
    });
});
</script>
</div>