<div>
    @section('title2')
        <title>Client Registry | CRM</title>
    @endsection

    @php
        $breadcrumbItems = [
            ['link' => route('customers-list'), 'name' => 'CRM', 'icon' => null],
            ['link' => route('customers-list'), 'name' => 'Client Registry', 'icon' => null],
        ];
    @endphp
    <main>
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$breadcrumbItems"
                title="Client Registry"
                subtitle="All laboratory service accounts and their engagement status"
                icon="mdi-account-group"
            >
                <x-slot:actions>
                    <button class="btn btn-outline-success btn-sm mr-2 crm-btn-export" wire:click="exportToExcel" wire:loading.attr="disabled">
                        <i class="mdi mdi-file-excel-box mr-1"></i> Export to Excel
                    </button>
                    @if(isset($account_settings->id))
                        <button class="btn btn-add btn-sm crm-btn-add" wire:click="openAddForm">
                            <i class="mdi mdi-plus"></i> Add Client
                        </button>
                    @else
                        <a href="{{ route('add-config-customer') }}" class="btn btn-add btn-sm crm-btn-add">
                            <i class="mdi mdi-plus"></i> Add Client
                        </a>
                    @endif
                </x-slot:actions>
            </x-crm.page-header>

            <x-crm.filter-bar title="Filters" class="crm-filter-bar-sticky">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="crm-search-wrapper w-100" style="max-width: 100%;">
                            <i class="mdi mdi-magnify crm-search-icon"></i>
                            <input type="text" id="search" class="form-control w-100" placeholder="Search by account name, email, or reference..."
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
                                <option value="1">Active Accounts</option>
                                <option value="0">Inactive Accounts</option>
                            </select>
                        </div>

                        <!-- Columns Selector -->
                        <div class="dropdown mr-3 mb-2 mb-md-0">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 8px 16px; border-radius: 6px;">
                                <i class="mdi mdi-table-column"></i> Columns
                            </button>
                            <div class="dropdown-menu dropdown-menu-right p-2" style="min-width: 200px;">
                                @foreach(['postal_address' => 'Postal Address', 'physical_address' => 'Physical Address', 'website' => 'Website', 'fax' => 'Fax', 'phone1' => 'Phone 1', 'phone2' => 'Phone 2', 'country' => 'Country'] as $col => $label)
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
                            <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                            <select wire:model.live="perPage" wire:key="per-page-select"
                                class="custom-select custom-select-sm no-select2" style="width: 70px;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
                        </div>

                        <!-- Clear Filters -->
                        @if($search || $activeFilter !== '' || $startDate || $endDate)
                            <div class="mb-2 mb-md-0 pl-md-2 border-left border-light">
                                <button type="button" class="btn btn-sm btn-clear-filters" wire:click="clearFilters">
                                    <i class="mdi mdi-filter-remove"></i> Clear All
                                </button>
                                <span class="d-none filter-badge align-middle">
                                    Filtered
                                </span>
                            </div>
                        @endif
                    </div>
            </x-crm.filter-bar>

            <div wire:offline class="alert offline-warning m-4">
                <i class="mdi mdi-wifi-off"></i> You are currently offline. Some features may not work.
            </div>

            <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
                <x-slot:header>
                            <tr>
                                <th style="width: 100px;">Actions</th>
                                <th wire:click="sortBy('code')" style="cursor: pointer;">
                                    Code
                                    @if($sortField === 'code')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="mdi mdi-arrow-up text-muted" style="opacity: 0.3;"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('name')" style="cursor: pointer;">
                                    Name
                                    @if($sortField === 'name')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="mdi mdi-arrow-up text-muted" style="opacity: 0.3;"></i>
                                    @endif
                                </th>
                                @if($visibleColumns['postal_address'] ?? true)<th>Postal Address</th>@endif
                                @if($visibleColumns['physical_address'] ?? true)<th>Physical Address</th>@endif
                                @if($visibleColumns['website'] ?? true)<th>Website</th>@endif
                                @if($visibleColumns['fax'] ?? true)<th>Fax</th>@endif
                                <th wire:click="sortBy('email')" style="cursor: pointer;">
                                    Email
                                    @if($sortField === 'email')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="mdi mdi-arrow-up text-muted" style="opacity: 0.3;"></i>
                                    @endif
                                </th>
                                @if($visibleColumns['phone1'] ?? true)<th>Phone 1</th>@endif
                                @if($visibleColumns['phone2'] ?? true)<th>Phone 2</th>@endif
                                @if($visibleColumns['country'] ?? true)<th>Country</th>@endif
                                <th>Account Status</th>
                            </tr>
                </x-slot:header>
                            @php $shownInactiveDivider = false; @endphp
                            @forelse($customers as $customer)
                                @if(!$shownInactiveDivider && $customer->active == '0' && $activeFilter === '')
                                    <tr>
                                        <td colspan="{{ 7 + count(array_filter($visibleColumns ?? [])) }}" class="text-center py-2" style="background:#f8f9fa;">
                                            <small class="text-muted font-weight-bold text-uppercase" style="letter-spacing: 0.06em;font-size:0.65rem;">
                                                <i class="mdi mdi-minus-circle-outline mr-1"></i>Inactive Accounts
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
                                            <a class="btn crm-btn crm-btn-view btn-sm" href="{{ route('crm.customer.show', $customer->id) }}" title="View">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                            <button type="button" class="btn crm-btn crm-btn-delete btn-sm" wire:click="confirmDelete({{ $customer->id }})" title="Delete">
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
                                        @if($customer->active == '1')
                                            <span class="crm-badge crm-badge-success">Active</span>
                                        @else
                                            <span class="crm-badge crm-badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                @php $emptyColspan = 7 + count(array_filter($visibleColumns ?? [])); @endphp
                                <tr>
                                    <td colspan="{{ $emptyColspan }}">
                                        <x-crm.empty-state
                                            icon="mdi-domain-off"
                                            message="No client accounts found"
                                            help="Try adjusting your search or filters, or add your first laboratory client account."
                                        />
                                    </td>
                                </tr>
                            @endforelse
            </x-crm.data-table>

            <div wire:loading wire:target="search,activeFilter,startDate,endDate,selectedCustomers,sortBy,clearFilters"
                class="crm-loading-indicator">
                <i class="mdi mdi-loading mdi-spin"></i> Loading...
            </div>

            <x-crm.pagination :summary="'Showing ' . ($customers->firstItem() ?? 0) . ' to ' . ($customers->lastItem() ?? 0) . ' of ' . $customers->total() . ' results'">
                {{ $customers->links() }}
            </x-crm.pagination>

            @if($showForm)
                @livewire(\App\Livewire\Crm\Customer\CustomerForm::class, [
                    'customer' => $editingCustomer,
                    'countries' => $countries,
                    'accounts' => $accounts,
                    'account_settings' => $account_settings
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
                    <h5 class="modal-title" id="customerDeleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="mdi mdi-alert-circle-outline text-danger mb-3" style="font-size: 3rem;"></i>
                    <h4>Are you sure?</h4>

                                           <p class="text-muted">You are about to delete this customer. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteCustomer" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteCustomer">Yes, Delete it</span>
                        <span wire:loading wire:target="deleteCustomer">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>

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