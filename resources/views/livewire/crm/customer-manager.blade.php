<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account-group text-primary"></i>
                                Customer Management
                            </h2>
                            <p class="text-muted mb-0">Manage customers, company units, sample points, and contacts</p>
                        </div>
                        <button wire:click="showCreateCustomerModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Customer
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
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search customers...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Country</label>
                                <select wire:model.live="countryFilter" class="form-select modern-select">
                                    <option value="">All Countries</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">From Date</label>
                                <input type="date" wire:model.live="dateFrom" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">To Date</label>
                                <input type="date" wire:model.live="dateTo" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-refresh"></i> Clear Filters
                            </button>
                        </div>
                        <div class="col-md-6 text-end hidden">
                            <div class="btn-group">
                                <button wire:click="exportCustomers" class="btn btn-success btn-sm">
                                    <i class="mdi mdi-download"></i> Export CSV
                                </button>
                                <select wire:model="exportFormat" class="form-select form-select-sm" style="width: auto;">
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel</option>
                                </select>
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
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->customers->count() > 0)
                        <!-- Bulk Actions -->
                        @if(count($selectedCustomers) > 0)
                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                                <span>
                                    <i class="mdi mdi-information"></i>
                                    {{ count($selectedCustomers) }} customer(s) selected
                                </span>
                                <div class="btn-group">
                                    <button wire:click="bulkStatusUpdate(1)" class="btn btn-success btn-sm">
                                        <i class="mdi mdi-check"></i> Activate
                                    </button>
                                    <button wire:click="bulkStatusUpdate(0)" class="btn btn-warning btn-sm">
                                        <i class="mdi mdi-pause"></i> Deactivate
                                    </button>
                                    <button wire:click="bulkDelete" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to delete selected customers?')">
                                        <i class="mdi mdi-delete"></i> Delete
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->customers->firstItem() ?? 0 }} to {{ $this->customers->lastItem() ?? 0 }} of {{ $this->customers->total() }} entries
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
                                        <th style="width: 50px;">
                                            <input type="checkbox" wire:model="selectAll" class="form-check-input">
                                        </th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Country</th>
                                    <th>Status</th>
                                        <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($this->customers as $customer)
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       wire:model="selectedCustomers" 
                                                       value="{{ $customer->id }}" 
                                                       class="form-check-input">
                                            </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $customer->code }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $customer->name }}</div>
                                                    <small class="text-muted">{{ Str::limit($customer->physical_address, 30) }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->telephone1 }}</td>
                                        <td>{{ $customer->country->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($customer->active == 1)
                                                    <span class="badge bg-success p-2">Active</span>
                                            @else
                                                    <span class="badge bg-danger p-2">Inactive</span>
                                            @endif
                                        </td>
                                            <td>
                                            <div class="btn-group" role="group">
                                                <button wire:click="viewCustomer({{ $customer->id }})" 
                                                            class="btn btn-sm btn-outline-primary mr-1" 
                                                        title="View Profile">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                <button wire:click="showEditCustomerModal({{ $customer->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                        title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteCustomer({{ $customer->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
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
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->customers->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-account-group text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No customers found</h5>
                            <p class="text-muted">Start by adding your first customer.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    @if($showCustomerModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingCustomer ? 'pencil' : 'plus' }}"></i>
                            {{ $editingCustomer ? 'Edit' : 'Create' }} Customer
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCustomerModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCustomer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="customerForm.name" class="form-control" placeholder="Customer name...">
                                        @error('customerForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                        <input type="email" wire:model="customerForm.email" class="form-control" placeholder="Email address...">
                                        @error('customerForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Phone 1 <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="customerForm.telephone1" class="form-control" placeholder="Primary phone...">
                                        @error('customerForm.telephone1') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Phone 2</label>
                                        <input type="text" wire:model="customerForm.telephone2" class="form-control" placeholder="Secondary phone...">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Country <span class="text-danger">*</span></label>
                                        <select wire:model="customerForm.country_id" class="form-select">
                                            <option value="">Select Country</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customerForm.country_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Account Settings <span class="text-danger">*</span></label>
                                        <select wire:model="customerForm.account_status" class="form-select">
                                            <option value="">Select Account Settings</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->key }}</option>
                                            @endforeach
                                        </select>
                                        @error('customerForm.account_status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Postal Address <span class="text-danger">*</span></label>
                                <textarea wire:model="customerForm.postal_address" class="form-control" rows="3" placeholder="Postal address..."></textarea>
                                @error('customerForm.postal_address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Physical Address <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customerForm.physical_address" class="form-control" placeholder="Physical address...">
                                @error('customerForm.physical_address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Website</label>
                                        <input type="text" wire:model="customerForm.website" class="form-control" placeholder="Website URL...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Fax</label>
                                        <input type="text" wire:model="customerForm.fax" class="form-control" placeholder="Fax number...">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">VAT Number</label>
                                        <input type="text" wire:model="customerForm.vat_no" class="form-control" placeholder="VAT number...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Credit Days</label>
                                        <input type="number" wire:model="customerForm.credit_days" class="form-control" placeholder="Credit days...">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="customerForm.active" class="form-check-input" id="active">
                                        <label class="form-check-label" for="active">
                                            Is Active?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="customerForm.lpos_required" class="form-check-input" id="lpos">
                                        <label class="form-check-label" for="lpos">
                                            LPO Required?
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCustomer">
                            <i class="mdi mdi-content-save"></i> {{ $editingCustomer ? 'Update' : 'Create' }} Customer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
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
    </style>
</div>

