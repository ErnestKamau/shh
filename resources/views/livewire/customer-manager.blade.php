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
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Country</label>
                                <select wire:model.live="countryFilter" class="form-select">
                                    <option value="">All Countries</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
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
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-filter-remove"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-table"></i> Customers List
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Country</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $customer->code }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $customer->name }}</div>
                                                <small class="text-muted">{{ $customer->physical_address }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->telephone1 }}</td>
                                        <td>{{ $customer->country->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($customer->active == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button wire:click="viewCustomer({{ $customer->id }})" 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        title="View Profile">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                <button wire:click="showEditCustomerModal({{ $customer->id }})" 
                                                        class="btn btn-outline-warning btn-sm" 
                                                        title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteCustomer({{ $customer->id }})" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this customer?')">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-information-outline fs-1"></i>
                                                <p class="mt-2">No customers found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-0" style="border-radius: 0 0 15px 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} customers
                        </div>
                        <div>
                            {{ $customers->links() }}
                        </div>
                    </div>
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
</div>

