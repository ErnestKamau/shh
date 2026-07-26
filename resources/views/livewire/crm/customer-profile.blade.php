<div class="container-fluid">
    @php
        $breadcrumbItems = [
            [
                'link' => route('crm.dashboard'),
                'name' => __('crm.module_name'),
                'icon' => null,
            ],
            [
                'link' => route('customers-list'),
                'name' => __('crm.client_registry'),
                'icon' => null,
            ],
            [
                'link' => '#',
                'name' => $customer->name,
                'icon' => null,
            ],
        ];
    @endphp
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

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
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                @php
                                    $engagementLabels = [
                                        'contract' => 'Contract',
                                        'portal' => 'Portal',
                                        'walk_in' => 'Walk-in',
                                    ];
                                    $engagementType = $customer->engagement_type ?? null;
                                @endphp
                                @if($engagementType && isset($engagementLabels[$engagementType]))
                                    <span class="badge badge-info-modern">{{ $engagementLabels[$engagementType] }}</span>
                                @endif
                                @if($customer->requires_sampling)
                                    <span class="badge bg-warning text-dark">Requires Sampling</span>
                                @endif
                                @if($customer->is_one_time)
                                    <span class="badge bg-secondary">One-time</span>
                                @endif
                                @if($engagementType === 'portal')
                                    <span class="badge bg-light text-dark border">Portal access via contacts</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <button wire:click="openLabelModal" class="btn btn-sm btn-outline-info pricelist-action-btn" title="{{ __('crm.edit_tab_names') }}">
                                <i class="mdi mdi-label-outline"></i> {{ __('crm.edit_tab_names') }}
                            </button>
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

    @php
        $unitLabel = trim($customer->unit_configurable_name ?? '') !== '' ? trim($customer->unit_configurable_name) : __('crm.company_units');
        $samplePointLabel = trim($customer->sample_point_configurable_name ?? '') !== '' ? trim($customer->sample_point_configurable_name) : __('crm.sample_points');
    @endphp

    <!-- Tabs Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="modern-tabs">
                        <ul class="nav nav-pills flex-nowrap" id="customerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'details' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('details')" 
                                        type="button">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <span class="fw-semibold">{{ __('crm.details') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'units' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('units')" 
                                        type="button">
                                    <i class="mdi mdi-sitemap me-2"></i>
                                    <span class="fw-semibold">{{ $unitLabel }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'sample-points' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('sample-points')" 
                                        type="button">
                                    <i class="mdi mdi-map-marker me-2"></i>
                                    <span class="fw-semibold">{{ $samplePointLabel }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'contacts' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('contacts')" 
                                        type="button">
                                    <i class="mdi mdi-account-box-outline me-2"></i>
                                    <span class="fw-semibold">{{ __('crm.contacts') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'reports' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('reports')" 
                                        type="button">
                                    <i class="mdi mdi-file-document-outline me-2"></i>
                                    <span class="fw-semibold">{{ __('crm.reports') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'amendments' ? 'active' : '' }}" 
                                        wire:click="setActiveTab('amendments')" 
                                        type="button">
                                    <i class="mdi mdi-file-document-edit me-2"></i>
                                    <span class="fw-semibold">{{ __('crm.amendments') }}</span>
                                </button>
                            </li>
                            {{-- Certifications tab disabled (use Documents tab instead)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'certifications' ? 'active' : '' }}"
                                        wire:click="setActiveTab('certifications')"
                                        type="button">
                                    <i class="mdi mdi-file-certificate me-2"></i>
                                    <span class="fw-semibold">{{ __('crm.certifications') }}</span>
                                </button>
                            </li>
                            --}}
                            <li class="nav-item" role="presentation">
                                <button class="nav-link modern-tab-link {{ $activeTab === 'documents' ? 'active' : '' }}"
                                        wire:click="setActiveTab('documents')"
                                        type="button">
                                    <i class="mdi mdi-paperclip me-2"></i>
                                    <span class="fw-semibold">{{ __('crm.documents') }}</span>
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
                    <div class="card-header bg-white border-0" style="border-radius: 15px 15px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                                    style="width:28px;height:28px;background:#eef2ff;">
                                    <i class="mdi mdi-information-outline text-primary" style="font-size:1rem;"></i>
                                </span>
                                <div>
                                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.client_account_profile') }}</small>
                                    <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.client_account_profile_subtitle') }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                @if($editingCustomer)
                                    <button wire:click="cancelEditing" class="btn btn-outline-secondary btn-sm mr-2">
                                        <i class="mdi mdi-close"></i> {{ __('crm.cancel') }}
                                    </button>
                                    <button wire:click="saveCustomer" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-content-save"></i> {{ __('crm.save_changes') }}
                                    </button>
                                @else
                                    <button wire:click="startEditing" class="btn btn-sm btn-outline-primary pricelist-action-btn">
                                        <i class="mdi mdi-pencil"></i> {{ __('crm.edit_customer') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if($editingCustomer)
                            <form wire:submit.prevent="saveCustomer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.name') }} <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="customerForm.name" class="form-control">
                                            @error('customerForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.email') }} <span class="text-danger">*</span></label>
                                            <input type="email" wire:model="customerForm.email" class="form-control">
                                            @error('customerForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.phone_1') }} <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="customerForm.telephone1" class="form-control">
                                            @error('customerForm.telephone1') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.phone_2') }}</label>
                                            <input type="text" wire:model="customerForm.telephone2" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.country') }} <span class="text-danger">*</span></label>
                                            <div class="tag-select-container @error('customerForm.country_id') is-invalid @enderror"
                                                wire:click="$set('showCountryDropdown', true)"
                                                wire:click.outside="$set('showCountryDropdown', false)">
                                                <div class="tag-select-input">
                                                    @if($this->selectedCountry)
                                                        <span class="tag-badge">
                                                            {{ data_get($this->selectedCountry, 'name') }}
                                                            <i class="mdi mdi-close-circle" wire:click.stop="clearCountry"></i>
                                                        </span>
                                                    @endif

                                                    <input type="text"
                                                        wire:model.live="countrySearch"
                                                        class="tag-input"
                                                        placeholder="{{ $this->selectedCountry ? '' : __('crm.select_country') }}"
                                                        autocomplete="off">
                                                </div>

                                                @if($showCountryDropdown)
                                                    <div class="tag-dropdown">
                                                        @if(count($this->filteredCountries) > 0)
                                                            @foreach($this->filteredCountries as $country)
                                                                <div class="tag-dropdown-item" wire:click.stop="selectCountry('{{ data_get($country, 'id') }}')">
                                                                    {{ data_get($country, 'name') }}
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="tag-dropdown-item text-muted">No countries found</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            @error('customerForm.country_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.account_settings') }} <span class="text-danger">*</span></label>
                                            <div class="tag-select-container @error('customerForm.account_status') is-invalid @enderror"
                                                wire:click="$set('showAccountDropdown', true)"
                                                wire:click.outside="$set('showAccountDropdown', false)">
                                                <div class="tag-select-input">
                                                    @if($this->selectedAccount)
                                                        <span class="tag-badge">
                                                            {{ data_get($this->selectedAccount, 'key') }}
                                                            <i class="mdi mdi-close-circle" wire:click.stop="clearAccountStatus"></i>
                                                        </span>
                                                    @endif

                                                    <input type="text"
                                                        wire:model.live="accountSearch"
                                                        class="tag-input"
                                                        placeholder="{{ $this->selectedAccount ? '' : __('crm.select_account_settings') }}"
                                                        autocomplete="off">
                                                </div>

                                                @if($showAccountDropdown)
                                                    <div class="tag-dropdown">
                                                        @if(count($this->filteredAccounts) > 0)
                                                            @foreach($this->filteredAccounts as $account)
                                                                <div class="tag-dropdown-item" wire:click.stop="selectAccountStatus('{{ data_get($account, 'id') }}')">
                                                                    {{ data_get($account, 'key') }}
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="tag-dropdown-item text-muted">No account settings found</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            @error('customerForm.account_status') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('crm.postal_address') }} <span class="text-danger">*</span></label>
                                    <textarea wire:model="customerForm.postal_address" class="form-control" rows="3"></textarea>
                                    @error('customerForm.postal_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('crm.physical_address') }} <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="customerForm.physical_address" class="form-control">
                                    @error('customerForm.physical_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.website') }}</label>
                                            <input type="text" wire:model="customerForm.website" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.fax') }}</label>
                                            <input type="text" wire:model="customerForm.fax" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.vat_number') }}</label>
                                            <input type="text" wire:model="customerForm.vat_no" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('crm.credit_days') }}</label>
                                            <input type="number" wire:model="customerForm.credit_days" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Contract Validity From</label>
                                            <input type="date" wire:model="customerForm.contract_valid_from" class="form-control">
                                            @error('customerForm.contract_valid_from') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Contract Validity To</label>
                                            <input type="date" wire:model="customerForm.contract_valid_to" class="form-control">
                                            @error('customerForm.contract_valid_to') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="customerForm.active" class="form-check-input" id="active">
                                            <label class="form-check-label" for="active">
                                                {{ __('crm.is_active') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="customerForm.lpos_required" class="form-check-input" id="lpos">
                                            <label class="form-check-label" for="lpos">
                                                {{ __('crm.lpo_required') }}
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

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-calendar text-primary"></i> Contract Validity From
                                                </div>
                                                <div class="info-value">
                                                    {{ $customer->contract_valid_from ? \Carbon\Carbon::parse($customer->contract_valid_from)->format('d-M-Y') : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-calendar text-primary"></i> Contract Validity To
                                                </div>
                                                <div class="info-value">
                                                    {{ $customer->contract_valid_to ? \Carbon\Carbon::parse($customer->contract_valid_to)->format('d-M-Y') : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <div class="col-12">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-file-document-outline text-primary"></i> Contract Documents
                                                </div>
                                                <div class="info-value">
                                                    @php
                                                        $contractDocs = $customer->contractAttachments ?? collect();
                                                    @endphp
                                                    @if($contractDocs->isEmpty())
                                                        <span class="text-muted">No contract documents uploaded</span>
                                                    @else
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($contractDocs as $attachment)
                                                                <li class="d-flex justify-content-between align-items-center py-1">
                                                                    <span>
                                                                        {{ $attachment->title ?: 'Contract' }}
                                                                        @if($attachment->created_at)
                                                                            <small class="text-muted">({{ $attachment->created_at->format('d-M-Y') }})</small>
                                                                        @endif
                                                                    </span>
                                                                    <a href="{{ route('crm.customer.attachment.download', [$customer->id, $attachment->id]) }}"
                                                                       class="btn btn-sm btn-outline-primary">
                                                                        <i class="mdi mdi-download"></i> Download
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">
                                                    <i class="mdi mdi-timer-sand text-warning"></i> Quotation Acceptance TAT (avg)
                                                </div>
                                                <div class="info-value">
                                                    @php
                                                        $tatMinutes = is_numeric($customer->quotation_acceptance_tat_minutes)
                                                            ? max((int) $customer->quotation_acceptance_tat_minutes, 0)
                                                            : null;
                                                    @endphp
                                                    @if($tatMinutes !== null)
                                                        <span class="badge badge-info-modern">{{ number_format($tatMinutes / 60, 2) }} hours</span>
                                                        <small class="text-muted d-block mt-1">{{ number_format($tatMinutes) }} minutes</small>
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

            {{-- Certifications tab disabled (use Documents tab instead)
            @if($activeTab === 'certifications')
                @livewire(\App\Livewire\Crm\Customer\Tabs\AttachmentsManager::class, ['customer' => $customer], key('tab-certifications-' . $customer->id))
            @endif
            --}}

            <!-- Documents Tab -->
            @if($activeTab === 'documents')
                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerAttachmentsTab::class, ['customer' => $customer], key('tab-documents-' . $customer->id))
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
            overflow-x: auto;
            scrollbar-width: thin;
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
        background: var(--color-primary-soft) !important;
        color: var(--color-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px var(--color-primary-highlight) !important;
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
        background: linear-gradient(135deg, var(--color-primary-soft) 0%, var(--color-primary-soft-light) 100%) !important;
        color: var(--color-primary) !important;
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

    .pricelist-action-btn {
        border-radius: 10px;
        min-height: 42px;
        font-weight: 600;
        padding-left: 16px;
        padding-right: 16px;
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

    /* Tag Select Dropdown Styling */
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
        border-color: var(--color-primary);
    }

    .tag-select-input:focus-within {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem var(--color-primary-focus);
        outline: none;
    }

    .tag-select-container.is-invalid .tag-select-input {
        border-color: #dc3545;
    }

    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background-color: var(--color-primary);
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
        background: transparent;
    }

    .tag-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid var(--color-primary);
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
        font-size: 0.9rem;
    }

    .tag-dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .tag-dropdown-item:last-child {
        border-bottom: none;
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
                            {{ __('crm.edit_tab_names') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeLabelModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>{{ __('crm.edit_tab_names') }}</strong>
                            <p class="mb-0 mt-2">{{ __('crm.configure_tab_names_help') }}</p>
                        </div>

                        <form wire:submit.prevent="saveLabels">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-sitemap text-primary"></i> 
                                    {{ __('crm.company_units') }}
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.unit_configurable_name" 
                                       class="form-control" 
                                       placeholder="{{ __('crm.company_units') }}">
                                <small class="text-muted">
                                    {{ __('crm.current') }}: <strong>{{ trim($customer->unit_configurable_name ?? '') !== '' ? $customer->unit_configurable_name : __('crm.company_units') }}</strong>
                                </small>
                                @error('labelForm.unit_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-map-marker text-success"></i> 
                                    {{ __('crm.sample_points') }}
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.sample_point_configurable_name" 
                                       class="form-control" 
                                       placeholder="{{ __('crm.sample_points') }}">
                                <small class="text-muted">
                                    {{ __('crm.current') }}: <strong>{{ trim($customer->sample_point_configurable_name ?? '') !== '' ? $customer->sample_point_configurable_name : __('crm.sample_points') }}</strong>
                                </small>
                                @error('labelForm.sample_point_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-package-variant text-warning"></i> 
                                    {{ __('crm.product_tab_name') }}
                                </label>
                                <input type="text" 
                                       wire:model="labelForm.product_configurable_name" 
                                       class="form-control" 
                                       placeholder="{{ __('crm.products') }}">
                                <small class="text-muted">
                                    {{ __('crm.current') }}: <strong>{{ $customer->product_configurable_name ?: __('crm.products') }}</strong>
                                </small>
                                @error('labelForm.product_configurable_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="mdi mdi-alert"></i>
                                <small><strong>{{ __('crm.note') }}:</strong> {{ __('crm.tab_name_note') }}</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLabelModal">{{ __('crm.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveLabels">
                            <i class="mdi mdi-content-save"></i> {{ __('crm.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

