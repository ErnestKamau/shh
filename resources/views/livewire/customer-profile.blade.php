<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account text-primary"></i>
                                {{ $customer->name }}
                            </h2>
                            <p class="text-muted mb-0">{{ $customer->code }} | {{ $customer->email }}</p>
                        </div>
                        <div>
                            @if($editingCustomer)
                                <button wire:click="cancelEditing" class="btn btn-outline-secondary me-2">
                                    <i class="mdi mdi-close"></i> Cancel
                                </button>
                                <button wire:click="saveCustomer" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Changes
                                </button>
                            @else
                                <button wire:click="startEditing" class="btn btn-primary">
                                    <i class="mdi mdi-pencil"></i> Edit Customer
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

    <!-- Tabs Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-fill" id="customerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('details')" 
                                    type="button">
                                <i class="mdi mdi-information-outline"></i> Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'units' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('units')" 
                                    type="button">
                                <i class="mdi mdi-sitemap"></i> Company Units
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'sample-points' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('sample-points')" 
                                    type="button">
                                <i class="mdi mdi-map-marker"></i> Sample Points
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'contacts' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('contacts')" 
                                    type="button">
                                <i class="mdi mdi-account-box-outline"></i> Contacts
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'reports' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('reports')" 
                                    type="button">
                                <i class="mdi mdi-file-document-outline"></i> Reports
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'amendments' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('amendments')" 
                                    type="button">
                                <i class="mdi mdi-file-document-edit"></i> Amendments
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="row">
        <div class="col-12">
            <!-- Details Tab -->
            @if($activeTab === 'details')
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0 text-muted">
                            <i class="mdi mdi-information-outline"></i> Customer Details
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        @if($editingCustomer)
                            <form wire:submit.prevent="saveCustomer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="customerForm.name" class="form-control">
                                            @error('customerForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                            <input type="email" wire:model="customerForm.email" class="form-control">
                                            @error('customerForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Phone 1 <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="customerForm.telephone1" class="form-control">
                                            @error('customerForm.telephone1') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Phone 2</label>
                                            <input type="text" wire:model="customerForm.telephone2" class="form-control">
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
                                    <textarea wire:model="customerForm.postal_address" class="form-control" rows="3"></textarea>
                                    @error('customerForm.postal_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Physical Address <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="customerForm.physical_address" class="form-control">
                                    @error('customerForm.physical_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Website</label>
                                            <input type="text" wire:model="customerForm.website" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Fax</label>
                                            <input type="text" wire:model="customerForm.fax" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">VAT Number</label>
                                            <input type="text" wire:model="customerForm.vat_no" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Credit Days</label>
                                            <input type="number" wire:model="customerForm.credit_days" class="form-control">
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
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Name</label>
                                        <p class="mb-0">{{ $customer->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Email</label>
                                        <p class="mb-0">{{ $customer->email }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Phone 1</label>
                                        <p class="mb-0">{{ $customer->telephone1 }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Phone 2</label>
                                        <p class="mb-0">{{ $customer->telephone2 ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Country</label>
                                        <p class="mb-0">{{ $customer->country->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Status</label>
                                        <p class="mb-0">
                                            @if($customer->active == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Postal Address</label>
                                <p class="mb-0">{{ $customer->postal_address }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Physical Address</label>
                                <p class="mb-0">{{ $customer->physical_address }}</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Website</label>
                                        <p class="mb-0">{{ $customer->website ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Fax</label>
                                        <p class="mb-0">{{ $customer->fax ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">VAT Number</label>
                                        <p class="mb-0">{{ $customer->vat_no ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">Credit Days</label>
                                        <p class="mb-0">{{ $customer->credit_days ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Company Units Tab -->
            @if($activeTab === 'units')
                @livewire('company-units-manager', ['customerId' => $customerId])
            @endif

            <!-- Sample Points Tab -->
            @if($activeTab === 'sample-points')
                @livewire('sample-points-manager', ['customerId' => $customerId])
            @endif

            <!-- Contacts Tab -->
            @if($activeTab === 'contacts')
                @livewire('contacts-manager', ['customerId' => $customerId])
            @endif

            <!-- Reports Tab -->
            @if($activeTab === 'reports')
                @livewire('reports-viewer', ['customerId' => $customerId])
            @endif

            <!-- Amendments Tab -->
            @if($activeTab === 'amendments')
                @livewire('amendment-viewer', ['customerId' => $customerId])
            @endif
        </div>
    </div>
</div>

