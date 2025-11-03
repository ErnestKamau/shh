<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-package-variant text-primary"></i>
                                Invoicable Items Management
                            </h2>
                            <p class="text-muted mb-0">Manage invoicable items for billing and quotations</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="showSyncConfirmationModal" class="btn btn-info">
                                <i class="mdi mdi-cloud-download"></i> Pull Invoicable Items
                            </button>
                            {{-- <button wire:click="showCreateItemModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Invoicable Item
                            </button> --}}
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by code, name, or description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Item Type</label>
                                <select wire:model.live="itemTypeFilter" class="form-select modern-select">
                                    <option value="">All Types</option>
                                    @foreach($itemTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Currency</label>
                                <select wire:model.live="currencyFilter" class="form-select modern-select">
                                    <option value="">All Currencies</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Invoicable Items</h5>
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
                    @if($this->invoicableItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Type</th>
                                        <th>Unit Price</th>
                                        <th>Unit Cost</th>
                                        <th>Currency</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->invoicableItems as $item)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $item->item_code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $item->item_name }}</strong>
                                                @if($item->description)
                                                    <br><small class="text-muted">{{ \Str::limit($item->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->item_type)
                                                    <span class="badge bg-info" style="color: white;">{{ $item->item_type }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ number_format($item->unit_price, 2) }}</strong>
                                            </td>
                                            <td>
                                                {{ number_format($item->unit_cost, 2) }}
                                            </td>
                                            <td>
                                                @if($item->currency)
                                                    {{ $item->currency->name }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->blocked)
                                                    <span class="badge p-2 bg-danger" style="color: white;">Blocked</span>
                                                @elseif($item->active)
                                                    <span class="badge p-2 bg-success" style="color: white;">Active</span>
                                                @else
                                                    <span class="badge p-2 bg-secondary" style="color: white;">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button wire:click="showEditItemModal({{ $item->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="cloneItem({{ $item->id }})" 
                                                            class="btn btn-sm btn-outline-info mr-1" 
                                                            title="Clone"
                                                            onclick="return confirm('Are you sure you want to clone this item?')">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </button>
                                                    <button wire:click="deleteItem({{ $item->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this item?')">
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
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->invoicableItems->firstItem() ?? 0 }} to {{ $this->invoicableItems->lastItem() ?? 0 }} of {{ $this->invoicableItems->total() }} entries
                                </span>
                            </div>
                            <div>
                                {{ $this->invoicableItems->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-package-variant text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No invoicable items found</h5>
                            <p class="text-muted">Create your first invoicable item to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Item Modal -->
    @if($showItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingItem ? 'pencil' : 'plus' }}"></i>
                            {{ $editingItem ? 'Edit' : 'Create' }} Invoicable Item
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeItemModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Display -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="saveItem">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Item Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="itemForm.item_code" class="form-control @error('itemForm.item_code') is-invalid @enderror">
                                        @error('itemForm.item_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="itemForm.item_name" class="form-control @error('itemForm.item_name') is-invalid @enderror">
                                        @error('itemForm.item_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="itemForm.description" class="form-control @error('itemForm.description') is-invalid @enderror" rows="3"></textarea>
                                @error('itemForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Item Type</label>
                                        <input type="text" wire:model="itemForm.item_type" class="form-control" placeholder="e.g., Service, Inventory">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Category Code</label>
                                        <input type="text" wire:model="itemForm.item_category_code" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" wire:model="itemForm.unit_price" class="form-control @error('itemForm.unit_price') is-invalid @enderror">
                                        @error('itemForm.unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" wire:model="itemForm.unit_cost" class="form-control @error('itemForm.unit_cost') is-invalid @enderror">
                                        @error('itemForm.unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-currency-usd text-success"></i> Currency <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showCurrencyDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($this->selectedCurrency)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedCurrency->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('itemForm.currency_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <input type="text" 
                                                       wire:model.live="currencySearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedCurrency ? '' : 'Search currencies...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            @if($showCurrencyDropdown && count($this->filteredCurrencies) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredCurrencies as $currency)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectCurrency({{ $currency->id }})">
                                                            {{ $currency->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('itemForm.currency_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tax Group Code</label>
                                        <input type="text" wire:model="itemForm.tax_group_code" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Unit of Measure</label>
                                        <input type="text" wire:model="itemForm.base_unit_of_measure" class="form-control" placeholder="e.g., PCS, KG, L">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">GTIN/Barcode</label>
                                        <input type="text" wire:model="itemForm.gtin" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" wire:model="itemForm.price_includes_tax" class="form-check-input" id="price_includes_tax">
                                            <label class="form-check-label" for="price_includes_tax">Price Includes Tax</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" wire:model="itemForm.blocked" class="form-check-input" id="blocked">
                                            <label class="form-check-label" for="blocked">Blocked</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" wire:model="itemForm.active" class="form-check-input" id="active">
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeItemModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveItem">
                            <i class="mdi mdi-content-save"></i> Save
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
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-cloud-download"></i>
                            Pull Items from Dynamics 365
                        </h5>
                        @if(!$isSyncing)
                            <button type="button" class="btn-close btn-close-white" wire:click="closeSyncModal"></button>
                        @endif
                    </div>
                    <div class="modal-body">
                        @if(!$isSyncing && !$syncResult)
                            <!-- Confirmation Message -->
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <strong>Important:</strong> This action will:
                                <ul class="mb-0 mt-2">
                                    <li>Fetch all items from Dynamics 365 Business Central</li>
                                    <li>Create new invoicable items that don't exist locally</li>
                                    <li><strong>Update existing items</strong> with data from Dynamics</li>
                                </ul>
                            </div>
                            <p class="mb-0">Are you sure you want to proceed?</p>
                        @elseif($isSyncing)
                            <!-- Progress Display -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-info mb-3" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="text-info">{{ $syncProgress }}</h5>
                                <p class="text-muted">Please wait while we sync items from Dynamics...</p>
                            </div>
                        @elseif($syncResult)
                            <!-- Result Display -->
                            <div class="alert alert-{{ $syncResult['success'] ? 'success' : 'danger' }}">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-{{ $syncResult['success'] ? 'check-circle' : 'alert-circle' }}"></i>
                                    {{ $syncResult['success'] ? 'Sync Completed' : 'Sync Failed' }}
                                </h6>
                                <p class="mb-0">{{ $syncResult['message'] }}</p>
                            </div>

                            @if($syncResult['success'])
                                <div class="row text-center mt-3">
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-primary mb-0">{{ $syncResult['total_fetched'] }}</h3>
                                                <small class="text-muted">Total Fetched</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-success mb-0">{{ $syncResult['new_count'] }}</h3>
                                                <small class="text-muted">New Items</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h3 class="text-warning mb-0">{{ $syncResult['updated_count'] }}</h3>
                                                <small class="text-muted">Updated Items</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if(!$isSyncing)
                            @if(!$syncResult)
                                <button type="button" class="btn btn-secondary" wire:click="closeSyncModal">Cancel</button>
                                <button type="button" class="btn btn-info" wire:click="pullItemsFromDynamics">
                                    <i class="mdi mdi-check"></i> Yes, Proceed
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" wire:click="closeSyncModal">
                                    <i class="mdi mdi-close"></i> Close
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .modal.show {
        display: block !important;
    }
    
    /* Modern Select Styling */
    .modern-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background-color: #ffffff;
        outline: none;
    }
    
    /* Tag-based Dropdown Styling */
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
    
    @script
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container')) {
            $wire.set('showCurrencyDropdown', false);
        }
    });
    </script>
    @endscript
</div>
