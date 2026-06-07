<div>
    @section('title2')
        <title>{{ __('crm.client_registry') }} | {{ __('crm.module_name') }}</title>
    @endsection

    <main>
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$this->breadcrumbItems"
                :title="__('crm.client_registry')"
                :subtitle="__('crm.client_registry_subtitle')"
                icon="mdi-account-group"
            >
                <x-slot:actions>
                    <button class="btn btn-outline-success btn-sm mr-2 crm-btn-export" wire:click="exportToExcel" wire:loading.attr="disabled">
                        <i class="mdi mdi-file-excel-box mr-1"></i> {{ __('crm.export_to_excel') }}
                    </button>
                    <button class="btn btn-outline-primary btn-sm mr-2 crm-btn-add crm-btn-add-rounded" wire:click="openAddForm">
                        <i class="mdi mdi-plus"></i> {{ __('crm.add_client') }}
                    </button>
                </x-slot:actions>
            </x-crm.page-header>

            <x-crm.filter-bar :title="__('crm.filters')" class="crm-filter-bar-sticky">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="crm-search-wrapper w-100" style="max-width: 100%;">
                            <i class="mdi mdi-magnify crm-search-icon"></i>
                            <input type="text" id="search" class="form-control w-100" placeholder="{{ __('crm.search_accounts_placeholder') }}"
                                wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                    
                    <!-- Filters & Columns on Right -->
                    <div class="col-md-8 d-flex justify-content-md-end align-items-center flex-wrap filter-row">
                        <!-- All Statuses -->
                        <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                            <select class="crm-select custom-select-sm no-select2" style="width: 160px;"
                                wire:model.live="activeFilter" wire:key="active-filter-select">
                                <option value="">All Account Statuses</option>
                                <option value="1">{{ __('crm.active_accounts') }}</option>
                                <option value="0">{{ __('crm.inactive_accounts') }}</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                            <select class="crm-select custom-select-sm no-select2" style="width: 170px;"
                                wire:model.live="accountStatusFilter" wire:key="account-status-filter-select">
                                <option value="">{{ __('crm.all_account_settings') }}</option>
                                @foreach($accounts as $account)
                                    <option value="{{ data_get($account, 'id') }}">{{ data_get($account, 'key') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Columns Selector -->
                        <div class="dropdown mr-3 mb-2 mb-md-0">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 8px 16px; border-radius: 6px;">
                                <i class="mdi mdi-table-column"></i> {{ __('crm.columns') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right p-2" style="min-width: 200px;">
                                @foreach(['postal_address' => __('crm.postal_address'), 'physical_address' => __('crm.physical_address'), 'website' => __('crm.website'), 'fax' => __('crm.fax'), 'phone1' => __('crm.phone_1'), 'phone2' => __('crm.phone_2'), 'country' => __('crm.country')] as $col => $label)
                                    <div class="custom-control custom-checkbox py-1">
                                        <input type="checkbox" class="custom-control-input" id="col_{{ $col }}"
                                            wire:click="toggleColumn('{{ $col }}')"
                                            @if($visibleColumns[$col] ?? true) checked @endif>
                                        <label class="custom-control-label" for="col_{{ $col }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Show Entries -->
                        <div class="d-flex align-items-center mb-2 mb-md-0 mr-3">
                            <label class="mb-0 mr-2 crm-filter-label text-nowrap">{{ __('crm.show') }}</label>
                            <select wire:model.live="perPage" wire:key="per-page-select"
                                class="custom-select custom-select-sm no-select2" style="width: 70px;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <label class="mb-0 ml-2 crm-filter-label text-nowrap">{{ __('crm.entries') }}</label>
                        </div>

                        <!-- Clear Filters -->
                        @if($search || $activeFilter !== '' || $accountStatusFilter !== '' || $startDate || $endDate)
                            <div class="mb-2 mb-md-0 pl-md-2 border-left border-light">
                                <button type="button" class="btn btn-sm btn-clear-filters" wire:click="clearFilters">
                                    <i class="mdi mdi-filter-remove"></i> {{ __('crm.clear_all') }}
                                </button>
                                <span class="d-none filter-badge align-middle">
                                    Filtered
                                </span>
                            </div>
                        @endif
                    </div>
            </x-crm.filter-bar>

            <div wire:offline class="alert offline-warning m-4">
                <i class="mdi mdi-wifi-off"></i> {{ __('crm.offline_warning') }}
            </div>

            <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
                <x-slot:header>
                            <tr>
                                <th style="width: 100px;">{{ __('crm.actions') }}</th>
                                <th wire:click="sortBy('code')" style="cursor: pointer;">
                                    {{ __('crm.code') }}
                                    @if($sortField === 'code')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="mdi mdi-arrow-up text-muted" style="opacity: 0.3;"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('name')" style="cursor: pointer;">
                                    {{ __('crm.name') }}
                                    @if($sortField === 'name')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="mdi mdi-arrow-up text-muted" style="opacity: 0.3;"></i>
                                    @endif
                                </th>
                                @if($visibleColumns['postal_address'] ?? true)<th>{{ __('crm.postal_address') }}</th>@endif
                                @if($visibleColumns['physical_address'] ?? true)<th>{{ __('crm.physical_address') }}</th>@endif
                                @if($visibleColumns['website'] ?? true)<th>{{ __('crm.website') }}</th>@endif
                                @if($visibleColumns['fax'] ?? true)<th>{{ __('crm.fax') }}</th>@endif
                                <th wire:click="sortBy('email')" style="cursor: pointer;">
                                    {{ __('crm.email') }}
                                    @if($sortField === 'email')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="mdi mdi-arrow-up text-muted" style="opacity: 0.3;"></i>
                                    @endif
                                </th>
                                @if($visibleColumns['phone1'] ?? true)<th>{{ __('crm.phone_1') }}</th>@endif
                                @if($visibleColumns['phone2'] ?? true)<th>{{ __('crm.phone_2') }}</th>@endif
                                @if($visibleColumns['country'] ?? true)<th>{{ __('crm.country') }}</th>@endif
                                <th>{{ __('crm.account_status') }}</th>
                            </tr>
                </x-slot:header>
                            @php $shownInactiveDivider = false; @endphp
                            @forelse($customers as $customer)
                                @if(!$shownInactiveDivider && $customer->active == '0' && $activeFilter === '')
                                    <tr>
                                        <td colspan="{{ 7 + count(array_filter($visibleColumns ?? [])) }}" class="text-center py-2" style="background:#f8f9fa;">
                                            <small class="text-muted font-weight-bold text-uppercase" style="letter-spacing: 0.06em;font-size:0.65rem;">
                                                <i class="mdi mdi-minus-circle-outline mr-1"></i>{{ __('crm.inactive_accounts') }}
                                            </small>
                                        </td>
                                    </tr>
                                    @php $shownInactiveDivider = true; @endphp
                                @endif
                                <tr wire:key="customer-{{ $customer->id }}">
                                    <td nowrap>
                                        <x-crm.action-buttons class="justify-content-center">
                                            <button type="button" class="btn crm-btn crm-btn-edit btn-sm" wire:click="openEditForm({{ $customer->id }})" title="Edit">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </button>
                                            <a class="btn crm-btn crm-btn-view btn-sm" href="{{ route('crm.customer.show', $customer->id) }}" title="{{ __('crm.view') }}">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                            <button type="button" class="btn crm-btn crm-btn-delete btn-sm" wire:click="confirmDelete({{ $customer->id }})" title="{{ __('crm.delete') }}">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </x-crm.action-buttons>
                                    </td>
                                    <td>
                                        <a href="{{ route('crm.customer.show', $customer->id) }}"
                                            class="customer-code-link">
                                            {{ $customer->code }}
                                        </a>
                                    </td>
                                    <td>{{ $customer->name }}</td>
                                    @if($visibleColumns['postal_address'] ?? true)<td>{{ $customer->postal_address }}</td>@endif
                                    @if($visibleColumns['physical_address'] ?? true)<td>{{ $customer->physical_address }}</td>@endif
                                    @if($visibleColumns['website'] ?? true)<td>{{ $customer->website }}</td>@endif
                                    @if($visibleColumns['fax'] ?? true)<td>{{ $customer->fax ?? '-' }}</td>@endif
                                    <td>{{ $customer->email }}</td>
                                    @if($visibleColumns['phone1'] ?? true)<td>{{ $customer->telephone1 ?? '-' }}</td>@endif
                                    @if($visibleColumns['phone2'] ?? true)<td>{{ $customer->telephone2 ?? '-' }}</td>@endif
                                    @if($visibleColumns['country'] ?? true)<td>{{ $customer->country->name ?? '-' }}</td>@endif
                                    <td class="text-small">
                                        @php
                                            $accountLabel = $accountLabelMap[(string) $customer->account_status] ?? null;
                                            $accountBadgeClass = strtoupper((string) $accountLabel) === 'POSTPAID'
                                                ? 'crm-badge-success'
                                                : (strtoupper((string) $accountLabel) === 'PREPAID' ? 'crm-badge-info' : 'crm-badge-danger');
                                        @endphp
                                        <span class="crm-badge {{ $accountBadgeClass }}">{{ $accountLabel ?? '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                @php $emptyColspan = 7 + count(array_filter($visibleColumns ?? [])); @endphp
                                <tr>
                                    <td colspan="{{ $emptyColspan }}">
                                        <x-crm.empty-state
                                            icon="mdi-domain-off"
                                            :message="__('crm.no_client_accounts_found')"
                                            :help="__('crm.no_client_accounts_help')"
                                        />
                                    </td>
                                </tr>
                            @endforelse
            </x-crm.data-table>

            <div wire:loading wire:target="search,activeFilter,accountStatusFilter,startDate,endDate,selectedCustomers,sortBy,clearFilters"
                class="crm-loading-indicator">
                <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
            </div>

            <x-crm.pagination :summary="'Showing ' . ($customers->firstItem() ?? 0) . ' to ' . ($customers->lastItem() ?? 0) . ' of ' . $customers->total() . ' results'">
                {{ $customers->links() }}
            </x-crm.pagination>

            @if($showForm)
                @livewire(\App\Livewire\Crm\Customer\CustomerForm::class, [
                    'customer' => $editingCustomer,
                    'countries' => $countries,
                    'accounts' => $accounts,
                    'account_settings' => $account_settings,
                ], 'customer-form-' . ($editingCustomer ? $editingCustomer->id : 'new'))

               @endif
        </div>
    </main>
    <!-- Delete Confirmation Modal -->
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="customerDeleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="customerDeleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="customerDeleteConfirmationModalLabel">{{ __('crm.confirm_deletion') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="mdi mdi-alert-circle-outline text-danger mb-3" style="font-size: 3rem;"></i>
                    <h4>{{ __('crm.are_you_sure') }}</h4>

                                           <p class="text-muted">{{ __('crm.delete_customer_warning') }}</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">{{ __('crm.cancel') }}</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteCustomer" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteCustomer">{{ __('crm.confirm_delete_action') }}</span>
                        <span wire:loading wire:target="deleteCustomer">{{ __('crm.deleting') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>

<style>
    .crm-btn-add-rounded {
        border-radius: 999px;
        padding: 0.4rem 0.95rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .crm-btn-add-rounded:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }
</style>

@section('script2')
    <script>
        window.addEventListener('show-customer-delete-modal', event => {
            $('#customerDeleteConfirmationModal').modal('show');
        });

        window.addEventListener('hide-customer-delete-modal', event => {
            $('#customerDeleteConfirmationModal').modal('hide');
        });
    </script>
    <!-- Select2 initialization now handled by enhanced global initializer in layouts/app.blade.php -->
@endsection