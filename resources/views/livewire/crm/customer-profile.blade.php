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
                                <button wire:click="openLabelModal" class="btn btn-sm btn-outline-info me-2" title="Configure Tab Names">
                                    <i class="mdi mdi-label-outline"></i> Edit Tab Names
                                </button>
                                <button wire:click="startEditing" class="btn btn-sm btn-primary">
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
            <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="modern-tabs">
                        <ul class="nav nav-pills nav-fill" id="customerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'details' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('details')" 
                                        type="button">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <span class="fw-semibold">Details</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'units' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('units')" 
                                        type="button">
                                    <i class="mdi mdi-sitemap me-2"></i>
                                    <span class="fw-semibold">{{ trim($customer->unit_configurable_name ?? '') !== '' ? $customer->unit_configurable_name : 'Company Units' }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'sub-units' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('sub-units')" 
                                        type="button">
                                    <i class="mdi mdi-file-tree me-2"></i>
                                    <span class="fw-semibold">{{ trim($customer->sub_unit_configurable_name ?? '') !== '' ? $customer->sub_unit_configurable_name : 'Company Sub-Units' }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'areas' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('areas')" 
                                        type="button">
                                    <i class="mdi mdi-map-marker-multiple me-2"></i>
                                    <span class="fw-semibold">{{ trim($customer->area_configurable_name ?? '') !== '' ? $customer->area_configurable_name : 'Areas' }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'sample-points' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('sample-points')" 
                                        type="button">
                                    <i class="mdi mdi-map-marker me-2"></i>
                                    <span class="fw-semibold">{{ trim($customer->sample_point_configurable_name ?? '') !== '' ? $customer->sample_point_configurable_name : 'Sample Points' }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'contacts' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('contacts')" 
                                        type="button">
                                    <i class="mdi mdi-account-box-outline me-2"></i>
                                    <span class="fw-semibold">Contacts</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'reports' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('reports')" 
                                        type="button">
                                    <i class="mdi mdi-file-document-outline me-2"></i>
                                    <span class="fw-semibold">Reports</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'amendments' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('amendments')" 
                                        type="button">
                                    <i class="mdi mdi-file-document-edit me-2"></i>
                                    <span class="fw-semibold">Amendments</span>
                                </button>
                            </li>
                        </ul>
                    </div>
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
                                            <label class="form-label fw-bold">Account Settings</label>
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
                            <div class="c">
                                <!-- Contact Information Section -->
                                <div class="info-section mb-4">
                                    <h6 class="section-title">
                                        <i class="mdi mdi-information-outline text-primary"></i>
                                        Basic Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-account text-primary"></i> Name
                                                </div>
                                                <div class="info-value">{{ $customer->name }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-email text-info"></i> Email
                                                </div>
                                                <div class="info-value">{{ $customer->email }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-phone text-success"></i> Primary Phone
                                                </div>
                                                <div class="info-value">{{ $customer->telephone1 }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-phone-outline text-success"></i> Secondary Phone
                                                </div>
                                                <div class="info-value text-muted">{{ $customer->telephone2 ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-earth text-warning"></i> Country
                                                </div>
                                                <div class="info-value">{{ $customer->country->name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-check-circle text-success"></i> Status
                                                </div>
                                                <div class="info-value">
                                                    @if($customer->active == 1)
                                                        <span class="badge badge-success-modern">
                                                            <i class="mdi mdi-check-circle"></i> Active
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary-modern">
                                                            <i class="mdi mdi-pause-circle"></i> Inactive
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Information Section -->
                                <div class="info-section mb-4">
                                    <h6 class="section-title">
                                        <i class="mdi mdi-map-marker text-danger"></i>
                                        Address Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-mailbox text-primary"></i> Postal Address
                                                </div>
                                                <div class="info-value">{{ $customer->postal_address }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-12">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-home-map-marker text-danger"></i> Physical Address
                                                </div>
                                                <div class="info-value">{{ $customer->physical_address }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information Section -->
                                <div class="info-section">
                                    <h6 class="section-title">
                                        <i class="mdi mdi-cog text-info"></i>
                                        Additional Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-web text-info"></i> Website
                                                </div>
                                                <div class="info-value">
                                                    @if($customer->website)
                                                        <a href="{{ $customer->website }}" target="_blank" class="text-primary">
                                                            {{ $customer->website }} <i class="mdi mdi-open-in-new"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-fax text-secondary"></i> Fax
                                                </div>
                                                <div class="info-value text-muted">{{ $customer->fax ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-receipt text-warning"></i> VAT Number
                                                </div>
                                                <div class="info-value text-muted">{{ $customer->vat_no ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-calendar-clock text-success"></i> Credit Days
                                                </div>
                                                <div class="info-value">
                                                    @if($customer->credit_days)
                                                        <span class="badge badge-info-modern">{{ $customer->credit_days }} days</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Company Units Tab -->
            @if($activeTab === 'units')
                @livewire(\App\Livewire\CRM\CompanyUnitsManager::class, ['customerId' => $customerId])
            @endif

            <!-- Company Sub Units Tab -->
            @if($activeTab === 'sub-units')
                @livewire(\App\Livewire\CRM\CompanySubUnitsManager::class, ['customerId' => $customerId])
            @endif

            <!-- Areas Tab -->
            @if($activeTab === 'areas')
                @livewire(\App\Livewire\CRM\AreasManager::class, ['customerId' => $customerId])
            @endif

            <!-- Sample Points Tab -->
            @if($activeTab === 'sample-points')
                @livewire(\App\Livewire\Samples\SamplePointsManager::class, ['customerId' => $customerId])
            @endif

            <!-- Contacts Tab -->
            @if($activeTab === 'contacts')
                @livewire(\App\Livewire\CRM\ContactsManager::class, ['customerId' => $customerId])
            @endif

            <!-- Reports Tab -->
            @if($activeTab === 'reports')
                @livewire(\App\Livewire\Reports\ReportsViewer::class, ['customerId' => $customerId])
            @endif

            <!-- Amendments Tab -->
            @if($activeTab === 'amendments')
                @livewire(\App\Livewire\Amendments\AmendmentViewer::class, ['customerId' => $customerId])
            @endif
        </div>
    </div>
    
    <!-- Modern Tab Styling -->
    <style>
        /* Modern Tab Styling */
        .modern-tabs {
            background: #ffffff;
            border-radius: 20px;
            padding: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

    .modern-tab-link {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        color: #6c757d !important;
        padding: 12px 20px !important;
        margin: 0 4px !important;
        border-radius: 16px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 48px !important;
    }

    .modern-tab-link:hover {
        background: rgba(0, 123, 255, 0.08) !important;
        color: #007bff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15) !important;
        border: none !important;
        outline: none !important;
    }

    .modern-tab-link:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .modern-tab-link:focus-visible {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .modern-tab-link.active {
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%) !important;
        color: #4a90e2 !important;
        transform: translateY(-1px) !important;
        border: none !important;
    }


    .modern-tab-link i {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .modern-tab-link:hover i {
        transform: scale(1.1);
    }

    .modern-tab-link.active i {
        transform: scale(1.1);
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modern-tab-link {
            padding: 10px 16px !important;
            font-size: 13px !important;
            margin: 0 2px !important;
        }
        
        .modern-tab-link span {
            display: none;
        }
        
        .modern-tab-link i {
            margin-right: 0 !important;
        }
    }

    @media (max-width: 576px) {
        .modern-tabs {
            padding: 4px;
        }
        
        .modern-tab-link {
            padding: 8px 12px !important;
            min-height: 40px !important;
        }
    }

    /* Smooth transitions for tab content */
    .tab-content {
        transition: all 0.3s ease;
    }

    /* Card hover effects for tab content */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    /* Modern Customer Details Styling */
    .info-section {
        padding: 0;
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        font-size: 18px;
    }

    .info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 16px 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #dee2e6;
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-label i {
        font-size: 16px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: #212529;
        word-break: break-word;
    }

    .info-value a {
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .info-value a:hover {
        text-decoration: underline;
        opacity: 0.8;
    }

    /* Modern Badge Styling */
    .badge-success-modern {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .badge-secondary-modern {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }

    .badge-info-modern {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
    }

    /* Responsive adjustments for info cards */
    @media (max-width: 768px) {
        .info-card {
            padding: 12px 16px;
        }
        
        .section-title {
            font-size: 13px;
        }
        
        .info-value {
            font-size: 14px;
        }
    }
    </style>

    <!-- Label Configuration Modal -->
    @if($showLabelModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-label-outline"></i>
                            Configure Tab Names
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeLabelModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>Customize tab names for this customer.</strong>
                            <p class="mb-0 mt-2">Leave blank to use default names. These names will be used throughout the system for this customer.</p>
                        </div>

                        <form wire:submit.prevent="saveLabels">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-sitemap text-primary"></i> 
                                    Company Units Tab Name
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.unit_configurable_name" 
                                       class="form-control" 
                                       placeholder="e.g., Sections, Departments, Branches (default: Company Units)">
                                <small class="text-muted">
                                    Current: <strong>{{ trim($customer->unit_configurable_name ?? '') !== '' ? $customer->unit_configurable_name : 'Company Units' }}</strong>
                                </small>
                                @error('labelForm.unit_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-file-tree text-info"></i> 
                                    Company Sub-Units Tab Name
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.sub_unit_configurable_name" 
                                       class="form-control" 
                                       placeholder="e.g., Sub Sections, Sub Departments (default: Company Sub-Units)">
                                <small class="text-muted">
                                    Current: <strong>{{ trim($customer->sub_unit_configurable_name ?? '') !== '' ? $customer->sub_unit_configurable_name : 'Company Sub-Units' }}</strong>
                                </small>
                                @error('labelForm.sub_unit_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-map-marker-multiple text-warning"></i> 
                                    Areas Tab Name
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.area_configurable_name" 
                                       class="form-control" 
                                       placeholder="e.g., Zones, Regions, Locations (default: Areas)">
                                <small class="text-muted">
                                    Current: <strong>{{ trim($customer->area_configurable_name ?? '') !== '' ? $customer->area_configurable_name : 'Areas' }}</strong>
                                </small>
                                @error('labelForm.area_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-map-marker text-success"></i> 
                                    Sample Points Tab Name
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.sample_point_configurable_name" 
                                       class="form-control" 
                                       placeholder="e.g., Locations, Sites, Testing Points (default: Sample Points)">
                                <small class="text-muted">
                                    Current: <strong>{{ trim($customer->sample_point_configurable_name ?? '') !== '' ? $customer->sample_point_configurable_name : 'Sample Points' }}</strong>
                                </small>
                                @error('labelForm.sample_point_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-package-variant text-warning"></i> 
                                    Product Tab Name
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.product_configurable_name" 
                                       class="form-control" 
                                       placeholder="e.g., Items, Materials, Services (default: Products)">
                                <small class="text-muted">
                                    Current: <strong>{{ $customer->product_configurable_name ?: 'Products' }}</strong>
                                </small>
                                @error('labelForm.product_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="mdi mdi-alert"></i>
                                <small><strong>Note:</strong> Changing these names will update how they appear in all forms, reports, and interfaces for this customer.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLabelModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveLabels">
                            <i class="mdi mdi-content-save"></i> Save Tab Names
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

