<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account-multiple text-primary"></i>
                                Dynamics Customers Management
                            </h2>
                            <p class="text-muted mb-0">Manage Zoho CRM customer integrations</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="showSyncConfirmationModal" class="btn btn-info">
                                <i class="mdi mdi-cloud-download"></i> Pull Customers
                            </button>
                            <button wire:click="showSyncToImaraModal" class="btn btn-success">
                                <i class="mdi mdi-sync"></i> Sync to Imara
                            </button>
                            {{-- <button wire:click="showCreateCustomerModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Customer
                            </button> --}}
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, email, Zoho ID, or currency...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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

    <!-- Customers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Dynamics Customers</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->customers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Customer Code</th>
                                        <th>Currency</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->customers as $customer)
                                        <tr>
                                            <td>
                                                <strong>{{ $customer->name }}</strong>
                                            </td>
                                            <td>
                                                {{ trim($customer->phone_no) != '' ? trim($customer->phone_no) : '-' }}
                                               
                                            </td>
                                            <td>
                                                <span class="badge bg-info p-2" style="color: white;">{{ $customer->customer_no }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $customer->currency_code }}</strong> 
                                            </td>
                                            <td>
                                                <span class="badge p-2 bg-{{ $customer->status == 'Active' ? 'success' : 'danger' }}" style="color: white;">
                                                    {{ ucfirst($customer->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button wire:click="showEditCustomerModal({{ $customer->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteCustomer({{ $customer->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this customer?')">
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
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->customers->firstItem() ?? 0 }} to {{ $this->customers->lastItem() ?? 0 }} of {{ $this->customers->total() }} entries
                                </span>
                            </div>
                            <div>
                                {{ $this->customers->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-account-off text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No customers found</h5>
                            <p class="text-muted">Create your first dynamics customer to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Customer Modal -->
    @if($showCustomerModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingCustomer ? 'pencil' : 'plus' }}"></i>
                            {{ $editingCustomer ? 'Edit' : 'Create' }} Dynamics Customer
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCustomerModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Display -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="saveCustomer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="customerForm.name" 
                                               class="form-control @error('customerForm.name') is-invalid @enderror" 
                                               placeholder="Enter customer name">
                                        @error('customerForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" 
                                               wire:model="customerForm.email" 
                                               class="form-control @error('customerForm.email') is-invalid @enderror" 
                                               placeholder="customer@example.com">
                                        @error('customerForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Zoho Contact ID <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="customerForm.zoho_contact_id" 
                                               class="form-control @error('customerForm.zoho_contact_id') is-invalid @enderror" 
                                               placeholder="Enter Zoho Contact ID">
                                        @error('customerForm.zoho_contact_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select wire:model="customerForm.status" class="form-select modern-select @error('customerForm.status') is-invalid @enderror">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        @error('customerForm.status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Currency ID <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="customerForm.currency_id" 
                                               class="form-control @error('customerForm.currency_id') is-invalid @enderror" 
                                               placeholder="Enter currency ID">
                                        @error('customerForm.currency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Currency Code <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="customerForm.currency_code" 
                                               class="form-control @error('customerForm.currency_code') is-invalid @enderror" 
                                               placeholder="e.g., USD, EUR, KES"
                                               maxlength="10">
                                        @error('customerForm.currency_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCustomer">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Sync to Imara Modal -->
    @if($showSyncToImaraModalFlag)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @if($isSyncingToImara) wire:poll.1s="processSyncToImaraBatch" @endif>
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-sync"></i>
                            Sync Dynamics Customers to Imara
                        </h5>
                        @if(!$isSyncingToImara)
                            <button type="button" class="btn-close btn-close-white" wire:click="closeSyncToImaraModal"></button>
                        @endif
                    </div>
                    <div class="modal-body">
                        @if(!$isSyncingToImara && !$syncToImaraResult)
                            <!-- Confirmation Message -->
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i>
                                <strong>Sync Process:</strong> This action will:
                                <ul class="mb-0 mt-2">
                                    <li>Find all Dynamics customers not yet linked to Imara customers</li>
                                    <li>Check if an Imara customer with the same name exists</li>
                                    <li>If not, create a new Imara customer with Dynamics data</li>
                                    <li>Link the Dynamics customer to the Imara customer</li>
                                    <li>Support multiple Dynamics customers per Imara customer</li>
                                </ul>
                            </div>
                            <p class="mb-0">Are you sure you want to sync unlinked Dynamics customers to Imara?</p>
                        @elseif($isSyncingToImara)
                            <!-- Progress Display -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="text-success">{{ $syncToImaraProgress }}</h5>
                                
                                <!-- Progress Bar -->
                                @if($syncToImaraTotalCustomers > 0)
                                    <div class="progress mt-3 mb-2" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                             role="progressbar" 
                                             style="width: {{ ($syncToImaraProcessedCount / $syncToImaraTotalCustomers) * 100 }}%"
                                             aria-valuenow="{{ $syncToImaraProcessedCount }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $syncToImaraTotalCustomers }}">
                                            {{ number_format($syncToImaraProcessedCount) }} of {{ number_format($syncToImaraTotalCustomers) }}
                                        </div>
                                    </div>
                                    
                                    <!-- Stats Cards -->
                                    <div class="row mt-4">
                                        <div class="col-6">
                                            <div class="card bg-light">
                                                <div class="card-body py-2">
                                                    <h4 class="text-success mb-0">{{ number_format($syncToImaraCreatedCount) }}</h4>
                                                    <small class="text-muted">Created</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="card bg-light">
                                                <div class="card-body py-2">
                                                    <h4 class="text-warning mb-0">{{ number_format($syncToImaraLinkedCount) }}</h4>
                                                    <small class="text-muted">Linked</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <p class="text-muted mt-3">Please wait while we sync customers to Imara...</p>
                                <small class="text-muted">Processing in batches of 500 customers</small>
                            </div>
                        @elseif($syncToImaraResult)
                            <!-- Result Display -->
                            <div class="alert alert-{{ $syncToImaraResult['success'] ? 'success' : 'danger' }}">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-{{ $syncToImaraResult['success'] ? 'check-circle' : 'alert-circle' }}"></i>
                                    {{ $syncToImaraResult['success'] ? 'Sync Completed' : 'Sync Failed' }}
                                </h6>
                                <p class="mb-0">{{ $syncToImaraResult['message'] }}</p>
                            </div>

                            @if($syncToImaraResult['success'])
                                <div class="row text-center mt-3">
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-primary mb-0">{{ $syncToImaraResult['total_processed'] }}</h3>
                                                <small class="text-muted">Total Processed</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-success mb-0">{{ $syncToImaraResult['created_count'] }}</h3>
                                                <small class="text-muted">New Customers</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-warning mb-0">{{ $syncToImaraResult['linked_count'] }}</h3>
                                                <small class="text-muted">Linked Existing</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if(!$isSyncingToImara)
                            @if(!$syncToImaraResult)
                                <button type="button" class="btn btn-secondary" wire:click="closeSyncToImaraModal">Cancel</button>
                                <button type="button" class="btn btn-success" wire:click="syncDynamicsToImara" wire:loading.attr="disabled" wire:loading.class="disabled">
                                    <span wire:loading.remove wire:target="syncDynamicsToImara">
                                        <i class="mdi mdi-check"></i> Yes, Sync Now
                                    </span>
                                    <span wire:loading wire:target="syncDynamicsToImara">
                                        <i class="mdi mdi-loading mdi-spin"></i> Processing...
                                    </span>
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" wire:click="closeSyncToImaraModal">
                                    <i class="mdi mdi-close"></i> Close
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Dynamics Sync Modal -->
    @if($showSyncModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-cloud-download"></i>
                            Pull Customers from Dynamics 365
                        </h5>
                        @if(!$isSyncing)
                            <button type="button" class="btn-close btn-close-white" wire:click="closeSyncModal"></button>
                        @endif
                    </div>
                    <div class="modal-body">
                        @if(!$isSyncing && !$syncResult)
                            <!-- Confirmation Message -->
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <strong>Important:</strong> This action will:
                                <ul class="mb-0 mt-2">
                                    <li>Fetch all customers from Dynamics 365 Business Central</li>
                                    <li>Create new customer records that don't exist locally</li>
                                    <li><strong>Update existing customers</strong> with data from Dynamics</li>
                                    <li>Process using bulk inserts for optimal performance</li>
                                </ul>
                            </div>
                            <p class="mb-0">Are you sure you want to proceed?</p>
                        @elseif($isSyncing)
                            <!-- Progress Display -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-info mb-3" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="text-info">{{ $syncProgress }}</h5>
                                <p class="text-muted">Please wait while we sync customers from Dynamics...</p>
                                <small class="text-muted">This may take a while for large datasets.</small>
                            </div>
                        @elseif($syncResult)
                            <!-- Result Display -->
                            <div class="alert alert-{{ $syncResult['success'] ? 'success' : 'danger' }}">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-{{ $syncResult['success'] ? 'check-circle' : 'alert-circle' }}"></i>
                                    {{ $syncResult['success'] ? 'Sync Completed' : 'Sync Failed' }}
                                </h6>
                                <p class="mb-0">{{ $syncResult['message'] }}</p>
                            </div>

                            @if($syncResult['success'])
                                <div class="row text-center mt-3">
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-primary mb-0">{{ $syncResult['total_fetched'] }}</h3>
                                                <small class="text-muted">Total Fetched</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-success mb-0">{{ $syncResult['new_count'] }}</h3>
                                                <small class="text-muted">New Customers</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-warning mb-0">{{ $syncResult['updated_count'] }}</h3>
                                                <small class="text-muted">Updated Customers</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if(!$isSyncing)
                            @if(!$syncResult)
                                <button type="button" class="btn btn-secondary" wire:click="closeSyncModal">Cancel</button>
                                <button type="button" class="btn btn-info" wire:click="pullCustomersFromDynamics">
                                    <i class="mdi mdi-check"></i> Yes, Proceed
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" wire:click="closeSyncModal">
                                    <i class="mdi mdi-close"></i> Close
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .modal.show {
        display: block !important;
    }
    
    /* Modern Select Styling */
    .modern-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background-color: #ffffff;
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
    
    /* Custom dropdown arrow */
    .modern-select {
        background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: left center, right 12px center;
        background-repeat: no-repeat, no-repeat;
        background-size: 100% 100%, 16px 16px;
        padding-right: 40px;
    }
    </style>
    
    <script>
        // Debug sync to Imara button clicks
        document.addEventListener('livewire:init', () => {
            Livewire.on('sync-to-imara-started', () => {
                console.log('Sync to Imara started event received');
            });
        });
        
        // Add click handler debugging
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dynamics Customer Manager page loaded');
        });
    </script>
</div>
