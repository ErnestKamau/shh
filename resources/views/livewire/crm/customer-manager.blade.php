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
                                <i class="mdi mdi-account-group text-primary"></i>
                                {{ __('crm.customer_management') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('crm.customer_management_subtitle') }}</p>
                        </div>
                        <button wire:click="showCreateCustomerModal" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <span wire:loading.remove wire:target="showCreateCustomerModal">
                                <i class="mdi mdi-plus"></i> {{ __('crm.add_customer') }}
                            </span>
                            <span wire:loading wire:target="showCreateCustomerModal">
                                <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
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
                        <i class="mdi mdi-filter-variant"></i> {{ __('crm.filter_options') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('crm.search') }}</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('crm.search_customers') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('crm.account_settings') }}</label>
                                <select wire:model.live="accountSettingsFilter" class="form-select modern-select">
                                    <option value="">{{ __('crm.all_account_settings') }}</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ is_object($account) ? ($account->id ?? '') : ($account['id'] ?? '') }}">
                                            {{ is_object($account) ? ($account->key ?? '') : ($account['key'] ?? '') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('crm.status') }}</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">{{ __('crm.all_status') }}</option>
                                    <option value="1">{{ __('crm.active') }}</option>
                                    <option value="0">{{ __('crm.inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('crm.from_date') }}</label>
                                <input type="date" wire:model.live="dateFrom" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('crm.to_date') }}</label>
                                <input type="date" wire:model.live="dateTo" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-refresh"></i> {{ __('crm.clear_filters') }}
                            </button>
                        </div>
                        <div class="col-md-6 text-end hidden">
                            <div class="btn-group">
                                <button wire:click="exportCustomers" class="btn btn-success btn-sm">
                                    <i class="mdi mdi-download"></i> {{ __('crm.export_csv') }}
                                </button>
                                <select wire:model="exportFormat" class="form-select form-select-sm" style="width: auto;">
                                    <option value="csv">{{ __('crm.csv') }}</option>
                                    <option value="excel">{{ __('crm.excel') }}</option>
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
                                    {{ __('crm.customers_selected_count', ['count' => count($selectedCustomers)]) }}
                                </span>
                                <div class="btn-group">
                                    <button wire:click="bulkStatusUpdate(1)" class="btn btn-success btn-sm">
                                        <i class="mdi mdi-check"></i> {{ __('crm.activate') }}
                                    </button>
                                    <button wire:click="bulkStatusUpdate(0)" class="btn btn-warning btn-sm">
                                        <i class="mdi mdi-pause"></i> {{ __('crm.deactivate') }}
                                    </button>
                                    <button wire:click="bulkDelete" class="btn btn-danger btn-sm" 
                                            onclick="return confirm(@js(__('crm.delete_selected_customers_confirm')))">
                                        <i class="mdi mdi-delete"></i> {{ __('crm.delete') }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    {{ __('crm.showing_to_of_results', ['from' => ($this->customers->firstItem() ?? 0), 'to' => ($this->customers->lastItem() ?? 0), 'total' => $this->customers->total()]) }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">{{ __('crm.show') }}:</label>
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
                                    <th>{{ __('crm.code') }}</th>
                                    <th>{{ __('crm.name') }}</th>
                                    <th>{{ __('crm.dynamics_mapping') }}</th>
                                    <th>{{ __('crm.email') }}</th>
                                    <th>{{ __('crm.phone_1') }}</th>
                                    <th>{{ __('crm.country') }}</th>
                                    <th>{{ __('crm.status') }}</th>
                                        <th>{{ __('crm.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($this->customers as $customer)
                                        <tr>
                                           
                                        <td>
                                            <a href="{{ route('crm.customer.show', $customer->id) }}" class="btn btn-sm rm-act-btn rm-act-btn--view">{{ $customer->code }}</a>
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
                                                        <span class="badge bg-info" style="font-size: 10px;">{{ __('crm.plus_more_count', ['count' => $linkedZohoCustomers->count() - 1]) }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->telephone1 }}</td>
                                        <td>{{ $customer->country->name ?? __('crm.not_available') }}</td>
                                        <td>
                                            @if($customer->active == 1)
                                                    <span class="badge bg-success p-2" style="color: white;">{{ __('crm.active') }}</span>
                                            @else
                                                    <span class="badge bg-danger p-2" style="color: white;">{{ __('crm.inactive') }}</span>
                                            @endif
                                        </td>
                                            <td nowrap style="width: 150px;">
                                            <div class="d-flex">
                                                <a href="{{ route('crm.customer.show', $customer->id) }}"
                                                   class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                   title="{{ __('crm.view_profile') }}">
                                                    <i class="mdi mdi-eye-outline"></i>
                                                </a>
                                                <button wire:click="showEditCustomerModal(@js($customer->id))"
                                                        type="button"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                        title="{{ __('crm.edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="showCloneModal(@js($customer->id))"
                                                        type="button"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--clone"
                                                        title="{{ __('crm.clone_customer') }}">
                                                    <i class="mdi mdi-content-copy"></i>
                                                </button>
                                                <button wire:click="deleteCustomer(@js($customer->id))"
                                                        type="button"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                        title="{{ __('crm.delete') }}"
                                                        onclick="return confirm(@js(__('crm.delete_customer_confirm')))" >
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
                            <h5 class="text-muted mt-3">{{ __('crm.no_customers_found') }}</h5>
                            <p class="text-muted">{{ __('crm.add_first_customer_hint') }}</p>
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
                            {{ $editingCustomer ? __('crm.edit') : __('crm.create') }} {{ __('crm.customer') }}
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
                                        <label class="form-label fw-bold">{{ __('crm.name') }} <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="customerForm.name" class="form-control" placeholder="{{ __('crm.customer_name_placeholder') }}">
                                        @error('customerForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.email') }} <span class="text-danger">*</span></label>
                                        <input type="email" wire:model="customerForm.email" class="form-control" placeholder="{{ __('crm.email_address_placeholder') }}">
                                        @error('customerForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.phone_1') }} <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="customerForm.telephone1" class="form-control" placeholder="{{ __('crm.primary_phone_placeholder') }}">
                                        @error('customerForm.telephone1') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.phone_2') }}</label>
                                        <input type="text" wire:model="customerForm.telephone2" class="form-control" placeholder="{{ __('crm.secondary_phone_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold"><i class="mdi mdi-earth text-primary"></i> {{ __('crm.country') }} <span class="text-danger">*</span></label>
                                        <div class="tag-select-container"
                                            wire:click="$set('showCountryDropdown', true)"
                                            wire:click.outside="$set('showCountryDropdown', false)">
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
                                        <label class="form-label fw-bold"><i class="mdi mdi-cog text-info"></i> {{ __('crm.account_settings') }} <span class="text-danger">*</span></label>
                                        <div class="tag-select-container"
                                            wire:click="$set('showAccountDropdown', true)"
                                            wire:click.outside="$set('showAccountDropdown', false)">
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
                                                    autocomplete="off"
                                                >
                                            </div>
                                            @if($showAccountDropdown && count($this->filteredAccounts) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredAccounts as $account)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectAccount('{{ data_get($account, 'id') }}')">
                                                            {{ data_get($account, 'key') }}
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
                                <label class="form-label fw-bold">{{ __('crm.postal_address') }} <span class="text-danger">*</span></label>
                                <textarea wire:model="customerForm.postal_address" class="form-control" rows="3" placeholder="{{ __('crm.postal_address_placeholder') }}"></textarea>
                                @error('customerForm.postal_address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('crm.physical_address') }} <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customerForm.physical_address" class="form-control" placeholder="{{ __('crm.physical_address_placeholder') }}">
                                @error('customerForm.physical_address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.website') }}</label>
                                        <input type="text" wire:model="customerForm.website" class="form-control" placeholder="{{ __('crm.website_url_placeholder') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.fax') }}</label>
                                        <input type="text" wire:model="customerForm.fax" class="form-control" placeholder="{{ __('crm.fax_number_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.vat_number') }}</label>
                                        <input type="text" wire:model="customerForm.vat_no" class="form-control" placeholder="{{ __('crm.vat_number_placeholder') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('crm.credit_days') }}</label>
                                        <input type="number" wire:model="customerForm.credit_days" class="form-control" placeholder="{{ __('crm.credit_days_placeholder') }}">
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
                        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal" wire:loading.attr="disabled" wire:target="saveCustomer">{{ __('crm.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCustomer" wire:loading.attr="disabled" wire:target="saveCustomer">
                            <span wire:loading.remove wire:target="saveCustomer">
                                <i class="mdi mdi-content-save"></i> {{ $editingCustomer ? __('crm.update') : __('crm.create') }} {{ __('crm.customer') }}
                            </span>
                            <span wire:loading wire:target="saveCustomer">
                                <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.saving') }}...
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
                            {{ __('crm.clone_customer') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCloneModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Confirmation Message -->
                        <div class="alert alert-info mb-4">
                            <i class="mdi mdi-information"></i>
                            {{ __('crm.confirm_cloning_message', ['customer' => $customerToClone->name]) }}
                        </div>

                        <!-- Summary Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-chart-box text-primary"></i>
                                    {{ __('crm.profile_summary') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-office-building text-primary me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">{{ __('crm.company_units') }}</div>
                                                <div class="h4 mb-0 text-primary">{{ $this->cloneSummary['company_units'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-domain text-success me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">{{ __('crm.company_sub_units') }}</div>
                                                <div class="h4 mb-0 text-success">{{ $this->cloneSummary['company_sub_units'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-map text-warning me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">{{ __('crm.sample_areas') }}</div>
                                                <div class="h4 mb-0 text-warning">{{ $this->cloneSummary['sample_areas'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-map-marker text-danger me-2" style="font-size: 24px;"></i>
                                            <div>
                                                <div class="fw-bold text-muted" style="font-size: 12px;">{{ __('crm.sample_points') }}</div>
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
                                <i class="mdi mdi-account text-primary"></i> {{ __('crm.new_customer_name') }} <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                wire:model="cloneCustomerName" 
                                class="form-control" 
                                placeholder="{{ __('crm.new_customer_name_placeholder') }}"
                                autofocus
                            >
                            @error('cloneCustomerName') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                            <small class="form-text text-muted mt-2 d-block">
                                <i class="mdi mdi-information-outline"></i> {{ __('crm.unique_customer_code_hint') }}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCloneModal" wire:loading.attr="disabled" wire:target="cloneCustomer">
                            <i class="mdi mdi-close"></i> {{ __('crm.close') }}
                        </button>
                        <button type="button" class="btn btn-info" wire:click="cloneCustomer" wire:loading.attr="disabled" wire:target="cloneCustomer">
                            <span wire:loading.remove wire:target="cloneCustomer">
                                <i class="mdi mdi-content-copy"></i> {{ __('crm.yes_clone') }}
                            </span>
                            <span wire:loading wire:target="cloneCustomer">
                                <span class="spinner-border spinner-border-sm" role="status"></span> {{ __('crm.cloning') }}...
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

    .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
    }

    .rm-act-btn:last-child {
        margin-right: 0;
    }

    .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    .rm-act-btn--view {
        border: 1px solid #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
    }

    .rm-act-btn--clone {
        border: 1px solid #c7d2fe;
        color: #4338ca;
        background: #eef2ff;
    }

    .rm-act-btn--clone:hover {
        background: #e0e7ff;
        border-color: #a5b4fc;
    }

    .rm-act-btn--delete {
        border: 1px solid #fecdd3;
        color: #e11d48;
        background: #fff5f7;
    }

    .rm-act-btn--delete:hover {
        background: #ffe4e6;
        border-color: #fda4af;
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

