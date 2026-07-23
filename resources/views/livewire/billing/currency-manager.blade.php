<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-currency-usd text-primary"></i>
                                Currency Management
                            </h2>
                            <p class="text-muted mb-0">Manage currencies for billing and quotations</p>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            <button type="button" wire:click="showCreateCurrencyModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Currency
                            </button>
                            <button type="button" wire:click="showSyncConfirmationModal" class="btn btn-info">
                                <i class="mdi mdi-cloud-download"></i> Pull Currencies
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="clearMessage"></button>
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by code, description, or ISO code...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select" style="border: 2px solid #e0e0e0; border-radius: 10px; padding: 0.6rem 0.75rem; background: white; transition: all 0.3s ease;">
                                    <option value="">All Status</option>
                                    <option value="1" selected>✓ Active</option>
                                    <option value="0">✗ Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currencies Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Currencies</h5>
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
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>ISO Code</th>
                                    <th>Exchange Rate</th>
                                    <th>Currency Factor</th>
                                    <th>Last Modified</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($currencies->count() > 0)
                                    @foreach($currencies as $currency)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $currency->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $currency->description }}</strong>
                                            </td>
                                            <td>
                                                @if($currency->iso_code)
                                                    <span class="badge bg-info text-white">{{ $currency->iso_code }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ number_format($currency->exchange_rate_amt, 6) }}
                                            </td>
                                            <td>
                                                {{ number_format($currency->currency_factor, 6) }}
                                            </td>
                                            <td>
                                                @if($currency->last_date_modified)
                                                    {{ $currency->last_date_modified->format('Y-m-d') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($currency->active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button wire:click="showEditCurrencyModal('{{ $currency->id }}')" class="btn btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="toggleStatus('{{ $currency->id }}')" class="btn btn-outline-{{ $currency->active ? 'warning' : 'success' }}" title="{{ $currency->active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="mdi mdi-{{ $currency->active ? 'close-circle' : 'check-circle' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteCurrency('{{ $currency->id }}')" onclick="return confirm('Are you sure you want to delete this currency?')" class="btn btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div style="padding: 3rem 1rem;">
                                                <i class="mdi mdi-cloud-download text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                                <h5 class="text-muted mt-3 mb-2">No Currencies Found</h5>
                                                <p class="text-muted mb-3">Add a currency manually or pull currencies from Dynamics 365 Business Central</p>
                                                <button type="button" wire:click="showCreateCurrencyModal" class="btn btn-primary mr-2">
                                                    <i class="mdi mdi-plus"></i> Add Currency
                                                </button>
                                                <button type="button" wire:click="showSyncConfirmationModal" class="btn btn-info">
                                                    <i class="mdi mdi-cloud-download"></i> Pull Currencies from Dynamics
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if($currencies->count() > 0)
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $currencies->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Modal -->
    @if($showCurrencyModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title">
                            <i class="mdi mdi-currency-usd"></i>
                            {{ $editingCurrency ? 'Edit Currency' : 'Add New Currency' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="saveCurrency">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label fw-bold">Currency Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="currencyForm.code" id="code" class="form-control @error('currencyForm.code') is-invalid @enderror" placeholder="e.g., USD, EUR, KES">
                                        @error('currencyForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <input type="text" wire:model="currencyForm.description" id="description" class="form-control @error('currencyForm.description') is-invalid @enderror" placeholder="e.g., US Dollar">
                                        @error('currencyForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="iso_code" class="form-label fw-bold">ISO Code</label>
                                        <input type="text" wire:model="currencyForm.iso_code" id="iso_code" class="form-control @error('currencyForm.iso_code') is-invalid @enderror" placeholder="e.g., USD">
                                        @error('currencyForm.iso_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="iso_numeric_code" class="form-label fw-bold">ISO Numeric Code</label>
                                        <input type="text" wire:model="currencyForm.iso_numeric_code" id="iso_numeric_code" class="form-control @error('currencyForm.iso_numeric_code') is-invalid @enderror" placeholder="e.g., 840">
                                        @error('currencyForm.iso_numeric_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="exchange_rate_amt" class="form-label fw-bold">Exchange Rate Amount <span class="text-danger">*</span></label>
                                        <input type="number" step="0.0000000001" wire:model="currencyForm.exchange_rate_amt" id="exchange_rate_amt" class="form-control @error('currencyForm.exchange_rate_amt') is-invalid @enderror" placeholder="0.00">
                                        @error('currencyForm.exchange_rate_amt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="currency_factor" class="form-label fw-bold">Currency Factor <span class="text-danger">*</span></label>
                                        <input type="number" step="0.0000000001" wire:model="currencyForm.currency_factor" id="currency_factor" class="form-control @error('currencyForm.currency_factor') is-invalid @enderror" placeholder="1.00">
                                        @error('currencyForm.currency_factor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model="currencyForm.active" id="active">
                                        <label class="form-check-label fw-bold" for="active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveCurrency">
                            <i class="mdi mdi-content-save"></i> {{ $editingCurrency ? 'Update' : 'Save' }} Currency
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Dynamics Sync Modal -->
    @if($showSyncModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title">
                            <i class="mdi mdi-cloud-sync"></i>
                            Sync Currencies from Dynamics
                        </h5>
                        @if(!$isSyncing && !$syncResult)
                            <button type="button" class="btn-close btn-close-white" wire:click="closeSyncModal"></button>
                        @endif
                    </div>
                    <div class="modal-body p-4">
                        @if(!$isSyncing && !$syncResult)
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <strong>Confirm Sync</strong>
                                <p class="mb-0 mt-2">This will pull all currencies from Dynamics 365 Business Central and update existing records in the database.</p>
                            </div>
                            <p class="text-muted">
                                <i class="mdi mdi-information"></i>
                                Are you sure you want to proceed?
                            </p>
                        @endif

                        @if($isSyncing)
                            <div class="text-center">
                                <div class="spinner-border text-info mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted">{{ $syncProgress }}</p>
                            </div>
                        @endif

                        @if($syncResult)
                            @if($syncResult['success'])
                                <div class="alert alert-success">
                                    <i class="mdi mdi-check-circle"></i>
                                    <strong>Sync Completed Successfully!</strong>
                                </div>
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3">
                                            <h3 class="text-primary mb-0">{{ $syncResult['total_fetched'] }}</h3>
                                            <small class="text-muted">Total Fetched</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3">
                                            <h3 class="text-success mb-0">{{ $syncResult['new_count'] }}</h3>
                                            <small class="text-muted">New Currencies</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3">
                                            <h3 class="text-warning mb-0">{{ $syncResult['updated_count'] }}</h3>
                                            <small class="text-muted">Updated</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle"></i>
                                    <strong>Sync Failed</strong>
                                    <p class="mb-0 mt-2">{{ $syncResult['message'] }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if(!$isSyncing && !$syncResult)
                            <button type="button" class="btn btn-secondary" wire:click="closeSyncModal">
                                <i class="mdi mdi-close"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-info" wire:click="pullCurrenciesFromDynamics">
                                <i class="mdi mdi-cloud-download"></i> Yes, Sync Now
                            </button>
                        @else
                            <button type="button" class="btn btn-primary" wire:click="closeSyncModal" @if($isSyncing) disabled @endif>
                                <i class="mdi mdi-close"></i> Close
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        /* Modern Select Dropdown Styling */
        .modern-select:hover {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }
    
        .modern-select:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
    
        /* Option styling */
        .modern-select option {
            padding: 0.5rem;
        }
    
        /* Table hover effects */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            transition: background-color 0.3s ease;
        }
    
        /* Empty state styling */
        tbody tr td[colspan] {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
    </style>
</div>

