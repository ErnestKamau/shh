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
                        <button wire:click="showCreateCustomerModal" wire:loading.attr="disabled" class="btn btn-sm btn-primary">
                            <span wire:loading.remove wire:target="showCreateCustomerModal">
                                <i class="mdi mdi-plus"></i> Add Customer
                            </span>
                            <span wire:loading wire:target="showCreateCustomerModal">
                                <i class="mdi mdi-loading mdi-spin"></i> Loading...
                            </span>
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
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>  
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Dynamics Mapping</th>
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
                                            <a wire:click="viewCustomer('{{ $customer->id }}')" class="btn btn-sm fw-bold text-primary">{{ $customer->code }}</a>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $customer->name }}</div>
                                                    <small class="text-muted">{{ Str::limit($customer->physical_address, 30) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $linkedZohoCustomers = $customer->zohoCustomers();
                                            @endphp
                                            @if($linkedZohoCustomers->count() > 0)
                                                <div>
                                                    <div class="fw-bold">{{ $linkedZohoCustomers->first()->name }}</div>
                                                    <small class="text-muted">{{ $linkedZohoCustomers->first()->customer_no }}</small>
                                                    @if($linkedZohoCustomers->count() > 1)
                                                        <span class="badge bg-info" style="font-size: 10px;">+{{ $linkedZohoCustomers->count() - 1 }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->telephone1 }}</td>
                                        <td>{{ $customer->country->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($customer->active == 1)
                                                    <span class="badge bg-success p-2" style="color: white;">Active</span>
                                            @else
                                                    <span class="badge bg-danger p-2" style="color: white;">Inactive</span>
                                            @endif
                                        </td>
                                            <td>
                                            <div class="btn-group" role="group">
                                                <button wire:click="viewCustomer('{{ $customer->id }}')" 
                                                            class="btn btn-sm btn-outline-primary mr-1" 
                                                        title="View Profile">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                <button wire:click="showEditCustomerModal('{{ $customer->id }}')" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                        title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="showCloneModal('{{ $customer->id }}')" 
                                                            class="btn btn-sm btn-outline-info mr-1" 
                                                        title="Clone Customer">
                                                    <i class="mdi mdi-content-copy"></i>
                                                </button>
                                                <button wire:click="deleteCustomer('{{ $customer->id }}')" 
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
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:loading.class="modal-loading">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="position: relative; z-index: 1055;">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingCustomer ? 'pencil' : 'plus' }}"></i>
                            {{ $editingCustomer ? 'Edit' : 'Create' }} Customer
                            <span wire:loading wire:target="showCreateCustomerModal,showEditCustomerModal">
                                <span class="spinner-border spinner-border-sm ms-2" role="status"></span>
                            </span>
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCustomerModal"></button>
                    </div>
                    <div class="modal-body" wire:loading.class="opacity-50" wire:target="saveCustomer">
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
                                        <label class="form-label fw-bold"><i class="mdi mdi-earth text-primary"></i> Country <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showCountryDropdown', true); $set('showAccountDropdown', false); $set('showZohoCustomerDropdown', false)">
                                            <div class="tag-select-input">
                                                @if($this->selectedCountryName)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedCountryName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="selectCountry(null)"></i>
                                                    </span>
                                                @endif
                                                <input
                                                    type="text"
                                                    wire:model.live.debounce.300ms="countrySearch"
                                                    class="tag-input"
                                                    placeholder="{{ $this->selectedCountryName ? '' : 'Search countries...' }}"
                                                    wire:focus="$set('showCountryDropdown', true)"
                                                    autocomplete="off"
                                                >
                                            </div>
                                            @if($showCountryDropdown && $this->filteredCountries->count() > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredCountries as $country)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectCountry('{{ $country->id }}')">
                                                            {{ $country->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('customerForm.country_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold"><i class="mdi mdi-cog text-info"></i> Account Settings</label>
                                        <div class="tag-select-container" wire:click="$set('showAccountDropdown', true); $set('showCountryDropdown', false); $set('showZohoCustomerDropdown', false)">
                                            <div class="tag-select-input">
                                                @if($this->selectedAccountName)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedAccountName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="selectAccount(null)"></i>
                                                    </span>
                                                @endif
                                                <input
                                                    type="text"
                                                    wire:model.live.debounce.300ms="accountSearch"
                                                    class="tag-input"
                                                    placeholder="{{ $this->selectedAccountName ? '' : 'Search account settings...' }}"
                                                    wire:focus="$set('showAccountDropdown', true)"
                                                    autocomplete="off"
                                                >
                                            </div>
                                            @if($showAccountDropdown && $this->filteredAccounts->count() > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredAccounts as $account)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectAccount('{{ is_object($account) ? $account->id : ($account['id'] ?? '') }}')">
                                                            {{ is_object($account) ? ($account->key ?? '') : ($account['key'] ?? '') }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
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
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold"><i class="mdi mdi-link-variant text-success"></i> Dynamics Customer Mapping <small class="text-muted">(Optional)</small></label>
                                        <div class="tag-select-container" wire:click="openZohoCustomerDropdown">
                                            <div class="tag-select-input">
                                                @if($this->selectedZohoCustomerName)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedZohoCustomerName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearZohoCustomer"></i>
                                                    </span>
                                                @endif
                                                <input
                                                    type="text"
                                                    wire:model.live.debounce.300ms="zohoCustomerSearch"
                                                    class="tag-input"
                                                    placeholder="{{ $this->selectedZohoCustomerName ? '' : 'Search Dynamics customers...' }}"
                                                    wire:focus="openZohoCustomerDropdown"
                                                    autocomplete="off"
                                                >
                                            </div>
                                            @if($showZohoCustomerDropdown)
                                                <div class="tag-dropdown" wire:click.stop>
                                                    <div wire:loading wire:target="toggleZohoCustomerDropdown" class="text-center py-3">
                                                        <i class="mdi mdi-loading mdi-spin"></i> Loading Dynamics customers...
                                                    </div>
                                                    <div wire:loading.remove wire:target="toggleZohoCustomerDropdown">
                                                        @if($this->filteredZohoCustomers->count() > 0)
                                                            @foreach($this->filteredZohoCustomers as $zc)
                                                                <div class="tag-dropdown-item" wire:click.stop="selectZohoCustomer({{ $zc->id }})">
                                                                    {{ $zc->name }} ({{ $zc->customer_no }})
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="tag-dropdown-item text-muted">No Dynamics customers found</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="mdi mdi-information-outline"></i> Link this customer to a Dynamics 365 customer for billing integration.
                                        </small>
                                        @error('customerForm.zoho_customer_id') <span class="text-danger">{{ $message }}</span> @enderror
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
                        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal" wire:loading.attr="disabled" wire:target="saveCustomer">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCustomer" wire:loading.attr="disabled" wire:target="saveCustomer">
                            <span wire:loading.remove wire:target="saveCustomer">
                                <i class="mdi mdi-content-save"></i> {{ $editingCustomer ? 'Update' : 'Create' }} Customer
                            </span>
                            <span wire:loading wire:target="saveCustomer">
                                <i class="mdi mdi-loading mdi-spin"></i> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Clone Customer Modal -->
    @if($showCloneModal && $customerToClone)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-content-copy"></i>
                            Clone Customer
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCloneModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Confirmation Message -->
                        <div class="alert alert-info mb-4">
                            <i class="mdi mdi-information"></i>
                            <strong>Confirm Cloning:</strong> You are about to clone <strong>{{ $customerToClone->name }}</strong> and all its profile information. Please review the summary below and enter a new customer name.
                        </div>

                        <!-- Summary Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-chart-box text-primary"></i>
                                    Profile Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-office-building text-primary me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">Company Units</div>
                                                <div class="h4 mb-0 text-primary">{{ $this->cloneSummary['company_units'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-domain text-success me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">Company Sub Units</div>
                                                <div class="h4 mb-0 text-success">{{ $this->cloneSummary['company_sub_units'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-map text-warning me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">Sample Areas</div>
                                                <div class="h4 mb-0 text-warning">{{ $this->cloneSummary['sample_areas'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-map-marker text-danger me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">Sample Points</div>
                                                <div class="h4 mb-0 text-danger">{{ $this->cloneSummary['sample_points'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Customer Name Input -->
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">
                                <i class="mdi mdi-account text-primary"></i> New Customer Name <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                wire:model="cloneCustomerName" 
                                class="form-control" 
                                placeholder="Enter new customer name..."
                                autofocus
                            >
                            @error('cloneCustomerName') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                            <small class="form-text text-muted mt-2 d-block">
                                <i class="mdi mdi-information-outline"></i> A unique customer code will be automatically generated from this name.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCloneModal" wire:loading.attr="disabled" wire:target="cloneCustomer">
                            <i class="mdi mdi-close"></i> Close
                        </button>
                        <button type="button" class="btn btn-info" wire:click="cloneCustomer" wire:loading.attr="disabled" wire:target="cloneCustomer">
                            <span wire:loading.remove wire:target="cloneCustomer">
                                <i class="mdi mdi-content-copy"></i> Yes, Clone
                            </span>
                            <span wire:loading wire:target="cloneCustomer">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Cloning...
                            </span>
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
    
    /* Make modal body scrollable */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Custom scrollbar for better UX */
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
    
    .modal.show {
        display: block !important;
    }
    
    /* Equipment-style Tag Select Styling */
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
    </style>

    <script>
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            // Check if click is outside any dropdown wrapper
            if (!event.target.closest('.tag-select-container')) {
                @this.resetDropdownStates();
            }
        });

        // Prevent modal backdrop from closing dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.querySelector('.modal.show');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal && !event.target.closest('.modal-content')) {
                        // Click on backdrop - but don't close dropdowns, let Livewire handle it
                        return;
                    }
                });
            }
        });
    </script>
</div>

