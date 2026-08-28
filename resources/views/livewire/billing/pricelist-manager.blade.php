<div class="container-fluid {{ ($showImportModal || $showPricelistModal || $showDeletePricelistConfirmModal) ? 'modal-active' : '' }}">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-bulleted-type text-primary"></i>
                                Pricelists Management
                            </h2>
                            <p class="text-muted mb-0">Manage pricelists and access list details</p>
                        </div>
                        <button wire:click="showCreatePricelistModal" class="btn btn-outline-primary pricelist-action-btn">
                            <i class="mdi mdi-plus"></i> Add Pricelist
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($message)
        @php
            $alertClass = match ($messageType) {
                'success' => 'success',
                'warning' => 'warning',
                'info' => 'info',
                default => 'danger',
            };
        @endphp
        <div class="alert alert-{{ $alertClass }} alert-dismissible fade show" role="alert" style="white-space: pre-line;">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

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
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search pricelists...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Currency</label>
                                <div class="tag-select-container" wire:click="$set('showCurrencyFilterDropdown', true)" wire:click.outside="$set('showCurrencyFilterDropdown', false)">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        @if($selectedCurrencyFilter)
                                            <span class="tag-badge">
                                                {{ $selectedCurrencyFilter->code }}{{ $selectedCurrencyFilter->description ? ' - ' . $selectedCurrencyFilter->description : '' }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearCurrencyFilter"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                               wire:model.live.debounce.200ms="currencyFilterSearch"
                                               class="tag-input"
                                               placeholder="{{ $selectedCurrencyFilter ? '' : 'All Currencies / Search...' }}"
                                               autocomplete="off">
                                    </div>

                                    @if($showCurrencyFilterDropdown)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item" wire:click.stop="clearCurrencyFilter">
                                                All Currencies
                                            </div>

                                            @forelse($filteredCurrencyFilterOptions as $currency)
                                                <div class="tag-dropdown-item" wire:click.stop="selectCurrencyFilter(@js($currency->id))">
                                                    {{ $currency->code }}{{ $currency->description ? ' - ' . $currency->description : '' }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">
                                                    No currencies found
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary pricelist-action-btn w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pricelists</h5>
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
                    @if($pricelists->count() > 0)
                        <div class="pricelist-status-legend mb-3">
                            <span class="legend-label">Row border status:</span>
                            <span class="legend-item">
                                <span class="legend-swatch legend-swatch--valid"></span>
                                Valid pricelist
                            </span>
                            <span class="legend-item">
                                <span class="legend-swatch legend-swatch--expired"></span>
                                Expired pricelist
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Actions</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Currency</th>
                                        <th>Revision</th>
                                        <th>Master</th>
                                        <th>Status</th>
                                        <th>Valid Till</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pricelists as $pricelist)
                                        @php
                                            $isExpired = !empty($pricelist->valid_till)
                                                && \Illuminate\Support\Carbon::parse($pricelist->valid_till)->endOfDay()->isPast();
                                        @endphp
                                        <tr class="{{ $isExpired ? 'pricelist-row--expired' : 'pricelist-row--valid' }}">
                                            <td nowrap style="width: 200px;">
                                                <div class="d-flex">
                                                    <button wire:click="openPricelist(@js($pricelist->id))" type="button" class="btn btn-sm rm-act-btn rm-act-btn--view" title="View details">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </button>
                                                    <button wire:click="showEditPricelistModal(@js($pricelist->id))" type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="Edit pricelist">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button
                                                        wire:click="openImportModal(@js($pricelist->id))"
                                                        type="button"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--import"
                                                        title="Import package prices from Excel or an Amspec quotation-preparation PDF into this pricelist">
                                                        <i class="mdi mdi-file-upload-outline"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                        title="Delete pricelist"
                                                        wire:click="openDeletePricelistConfirmModal('{{ $pricelist->id }}')"
                                                    >
                                                        <i class="mdi mdi-delete-outline"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td><strong>{{ $pricelist->code }}</strong></td>
                                            <td>{{ $pricelist->description }}</td>
                                            <td>{{ $pricelist->currency_code ?? 'N/A' }}</td>
                                            <td>{{ $pricelist->revision_number ?? '1' }}</td>
                                            <td>
                                                @if($pricelist->is_master)
                                                    <span class="badge pricelist-badge pricelist-badge--master">Master</span>
                                                @else
                                                    <span class="badge pricelist-badge pricelist-badge--standard">Standard</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge pricelist-badge {{ $pricelist->active ? 'pricelist-badge--active' : 'pricelist-badge--inactive' }}">{{ $pricelist->active ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td>{{ $pricelist->valid_till ?: 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">
                                Showing {{ $pricelists->firstItem() ?? 0 }} to {{ $pricelists->lastItem() ?? 0 }} of {{ $pricelists->total() }} entries
                            </span>
                            <div>
                                {{ $pricelists->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-format-list-bulleted-type text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No pricelists found</h5>
                            <p class="text-muted">Create your first pricelist to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('layouts.lab.partials.billing.pricelist-form-modal-styles')

    @teleport('body')
    {{-- Teleported so backdrop is not clipped by #main-container-body overflow. --}}
    <div
        class="modal fade ls-pricelist-form-modal ls-ui-kit {{ $showPricelistModal ? 'show d-block' : '' }}"
        tabindex="-1"
        role="dialog"
        wire:key="ls-pricelist-form-modal"
        @if($showPricelistModal)
            style="background-color: rgba(15, 23, 42, 0.55);"
            aria-modal="true"
            aria-hidden="false"
            wire:click.self="closePricelistModal"
        @else
            style="display: none;"
            aria-hidden="true"
        @endif
    >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg ls-pricelist-form-dialog">
            <div class="modal-content item-modal-content" wire:click.stop>
                <div class="modal-header item-modal-header border-0 ls-pricelist-form-header">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="mdi mdi-{{ $editingPricelist ? 'pencil' : 'plus' }}"></i>
                            {{ $editingPricelist ? 'Edit' : 'Create' }} Pricelist
                        </h5>
                        <p class="mb-0 ls-pricelist-form-subtitle">
                            Set core pricelist details before adding items and assigning customers.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white ls-pricelist-form-close"
                        wire:click="closePricelistModal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body item-modal-body">
                    <div class="item-modal-section mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label class="form-label item-modal-label">Description <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="pricelistForm.description" class="form-control modal-input @error('pricelistForm.description') is-invalid @enderror" placeholder="Example: 2026 Corporate Pricelist">
                                    @error('pricelistForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label item-modal-label">Currency <span class="text-danger">*</span></label>
                                    <div class="tag-select-container" wire:click="$set('showCurrencyDropdown', true)" wire:click.outside="$set('showCurrencyDropdown', false)">
                                        <div class="tag-select-input modal-input @error('pricelistForm.currency_id') is-invalid @enderror">
                                            @if($selectedCurrency)
                                                <span class="tag-badge">
                                                    {{ $selectedCurrency->code }}{{ $selectedCurrency->description ? ' - ' . $selectedCurrency->description : '' }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('pricelistForm.currency_id', null)"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live="currencySearch"
                                                   class="tag-input"
                                                   placeholder="{{ $selectedCurrency ? '' : 'Search & select currency...' }}"
                                                   autocomplete="off">
                                        </div>

                                        @if($showCurrencyDropdown && (count($filteredCurrencies) > 0 || $showAddCurrencyAction))
                                            <div class="tag-dropdown">
                                                @foreach($filteredCurrencies as $currency)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectCurrency(@js($currency->id))">
                                                        {{ $currency->code }}{{ $currency->description ? ' - ' . $currency->description : '' }}
                                                    </div>
                                                @endforeach
                                                @if($showAddCurrencyAction)
                                                <button type="button" class="tag-dropdown-item tag-dropdown-item--add" wire:click.stop="addCurrencyFromSearch">
                                                    <i class="mdi mdi-plus-circle-outline me-1"></i>
                                                    Add currency "{{ trim($currencySearch) }}"
                                                </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @error('pricelistForm.currency_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="item-modal-section mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label item-modal-label">Valid Till</label>
                                    <input type="date" wire:model="pricelistForm.valid_till" class="form-control modal-input @error('pricelistForm.valid_till') is-invalid @enderror">
                                    @error('pricelistForm.valid_till') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label item-modal-label">Status Tag</label>
                                    <select wire:model="pricelistForm.status" class="form-select modal-input">
                                        <option value="no-changes">no-changes</option>
                                        <option value="has-changes">has-changes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label item-modal-label">Billing mode</label>
                                    <select wire:model="pricelistForm.billing_mode" class="form-select modal-input @error('pricelistForm.billing_mode') is-invalid @enderror">
                                        <option value="package">Per package</option>
                                        <option value="per_test">Per test</option>
                                    </select>
                                    @error('pricelistForm.billing_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label item-modal-label d-block">Flags</label>
                                <div class="flag-card">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" wire:model="pricelistForm.is_master" class="form-check-input" role="switch">
                                        <label class="form-check-label">Master</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="pricelistForm.active" class="form-check-input" role="switch">
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-note">
                        <i class="mdi mdi-information-outline"></i>
                        New pricelists start as a profile record. You can add items and assign customers from the details screen after saving.
                    </div>
                </div>
                <div class="modal-footer item-modal-footer border-0">
                    <button type="button" class="btn btn-light item-modal-cancel-btn" wire:click="closePricelistModal">Cancel</button>
                    <button type="button" class="btn btn-primary item-modal-save-btn" wire:click="savePricelist">
                        <i class="mdi mdi-content-save"></i> Save Pricelist
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport

    @include('layouts.lab.partials.billing.amspec-import-modal', [
        'context' => 'pricelist',
        'driver' => 'livewire',
        'isOpen' => $showImportModal,
        'importFormat' => $importFormat,
        'importPricingMode' => $importPricingMode,
        'closeMethod' => 'closeImportModal',
        'submitMethod' => 'submitImport',
        'wireModel' => 'importFile',
        'errorBag' => 'importFile',
        'inputId' => 'ls-pricelist-list-import-file',
    ])

    @include('livewire.billing.partials.delete-pricelist-confirm-modal', [
        'isOpen' => $showDeletePricelistConfirmModal,
    ])

    <style>
        .modal.show {
            display: block !important;
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

        .rm-act-btn--import {
            border: 1px solid #fde68a;
            color: #b45309;
            background: #fffbeb;
        }
        .rm-act-btn--import:hover {
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .rm-act-btn--delete {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }
        .rm-act-btn--delete:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .pricelist-action-btn {
            border-radius: 10px;
            min-height: 42px;
            font-weight: 600;
            padding-left: 16px;
            padding-right: 16px;
        }

        .pricelist-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 84px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .pricelist-badge--master {
            color: #0f766e;
            background: #ccfbf1;
            border-color: #99f6e4;
        }

        .pricelist-badge--standard {
            color: #475569;
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .pricelist-badge--active {
            color: #166534;
            background: #dcfce7;
            border-color: #86efac;
        }

        .pricelist-badge--inactive {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .modern-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: left center, right 12px center;
            background-repeat: no-repeat, no-repeat;
            background-size: 100% 100%, 16px 16px;
            padding-right: 40px;
        }

        .modern-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background-color: #ffffff;
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

        .modern-filter-tag-input {
            min-height: 44px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .modern-filter-tag-input:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        }

        .modern-filter-tag-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .pricelist-status-legend {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            font-size: 13px;
            color: #334155;
        }

        .legend-label {
            font-weight: 700;
            color: #475569;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .legend-swatch {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }

        .legend-swatch--valid {
            background: #16a34a;
        }

        .legend-swatch--expired {
            background: #dc2626;
        }

        .pricelist-row--valid td:first-child {
            border-left: 4px solid #16a34a;
        }

        .pricelist-row--expired td:first-child {
            border-left: 4px solid #dc2626;
        }
    </style>
</div>
