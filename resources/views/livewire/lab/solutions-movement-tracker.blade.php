<div class="solutions-movement-tracker container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card scd-hero border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start gap-3">
                        <div class="flex-grow-1">
                            <p class="scd-eyebrow mb-1">Stock monitoring · Movement tracker</p>
                            <h2 class="scd-title mb-0">
                                <i class="mdi mdi-finance text-primary"></i>
                                {{ $subCategory->name }}
                            </h2>
                            <p class="scd-subtitle mb-0 mt-1">Track and manage stock movements</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3 pt-3 border-top border-light">
                        <button type="button"
                                wire:click="showAddMovementModal"
                                class="btn btn-primary smt-action-btn">
                            <i class="mdi mdi-swap-vertical"></i> Stock In / Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show smt-alert" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} me-1"></i>
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Two Panel Layout -->
    <div class="row">
        <!-- Left Panel: Sub-Category Details (Read-Only) -->
        <div class="col-xl-4 col-sm-12 mb-4">
            <div class="card scd-panel border-0 shadow-sm">
                <div class="scd-panel__head">
                    <span class="scd-panel__icon"><i class="mdi mdi-information-outline"></i></span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Details</h6>
                        <small class="text-muted">Sub-category information</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="smt-label">Name</label>
                        <input type="text" class="form-control smt-input" value="{{ $subCategory->name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="smt-label">Description</label>
                        <textarea class="form-control smt-input" rows="3" readonly>{{ $subCategory->description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="smt-label">Category</label>
                        <input type="text" class="form-control smt-input" value="{{ $subCategory->category->name ?? 'N/A' }}" readonly>
                    </div>

                    @if($subCategory->image)
                    <div class="mb-3">
                        <label class="smt-label">Image</label>
                        <div>
                            <img src="{{ $subCategory->image }}" class="smt-thumb" alt="{{ $subCategory->name }}">
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="smt-label">Rate</label>
                        <input type="text" class="form-control smt-input" value="{{ $subCategory->rate }}" readonly>
                    </div>

                    <div class="mb-0">
                        <label class="smt-label">Unit of measure</label>
                        <input type="text" class="form-control smt-input" value="{{ $subCategory->reportingUnit->name ?? 'N/A' }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Stock Movement Tracking -->
        <div class="col-xl-8 col-sm-12">
            <!-- Current Stock -->
            <div class="mb-3">
                @if($subCategory->stock > 0)
                    <span class="smt-stock-badge smt-stock-badge--available">
                        <i class="mdi mdi-package-variant-closed"></i>
                        <span><strong>Available:</strong> {{ number_format($subCategory->stock ?? 0, 2) }} {{ $subCategory->reportingUnit->name ?? '' }}</span>
                    </span>
                @else
                    <span class="smt-stock-badge smt-stock-badge--empty">
                        <i class="mdi mdi-package-variant"></i>
                        <span><strong>Available:</strong> {{ number_format($subCategory->stock ?? 0, 2) }} {{ $subCategory->reportingUnit->name ?? '' }}</span>
                    </span>
                @endif
            </div>

            <!-- Stock Movement History -->
            <div class="card scd-panel border-0 shadow-sm">
                <div class="scd-panel__head scd-panel__head--split">
                    <div class="d-flex align-items-center gap-3">
                        <span class="scd-panel__icon"><i class="mdi mdi-history"></i></span>
                        <div>
                            <h6 class="mb-0 fw-semibold">Stock movement history</h6>
                            <small class="text-muted">All in/out transactions</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    @if($this->stockMovements->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <span class="text-muted small">
                                Showing {{ $this->stockMovements->firstItem() ?? 0 }} to {{ $this->stockMovements->lastItem() ?? 0 }} of {{ $this->stockMovements->total() }} entries
                            </span>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="smt-label mb-0 me-2">Show</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm smt-per-page">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0 smt-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Stock in</th>
                                        <th>Stock out</th>
                                        <th>UOM</th>
                                        <th>Created by</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->stockMovements as $movement)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($subCategory->image)
                                                        <img src="{{ $subCategory->image }}" class="smt-table-thumb" alt="">
                                                    @endif
                                                    <span>{{ $subCategory->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($movement->stock_in > 0)
                                                    <span class="text-success fw-semibold">{{ number_format($movement->stock_in, 2) }}</span>
                                                @else
                                                    {{ number_format($movement->stock_in, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($movement->stock_out > 0)
                                                    <span class="text-danger fw-semibold">{{ number_format($movement->stock_out, 2) }}</span>
                                                @else
                                                    {{ number_format($movement->stock_out, 2) }}
                                                @endif
                                            </td>
                                            <td>{{ $movement->uom->name ?? 'N/A' }}</td>
                                            <td>{{ $movement->creator->name ?? 'N/A' }}</td>
                                            <td class="text-nowrap">{{ $movement->created_at->format('M j, Y g:i A') }}</td>
                                            <td>{{ $movement->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->stockMovements->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="smt-empty-icon mx-auto mb-3">
                                <i class="mdi mdi-history"></i>
                            </div>
                            <h6 class="text-muted mb-1">No stock movements yet</h6>
                            <p class="text-muted small mb-0">Use <strong>Stock In / Out</strong> above to record your first movement.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Modal -->
    @if($showMovementModal)
        <div class="modal fade show d-block smt-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content smt-modal-content border-0 shadow">
                    <div class="modal-header smt-modal-header border-0">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="mdi mdi-swap-vertical text-primary"></i>
                                Record stock movement
                            </h5>
                            <small class="text-muted">Add stock in or stock out for {{ $subCategory->name }}</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeMovementModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body smt-modal-body pt-0">
                        <div class="smt-stock-summary mb-4 {{ $subCategory->stock > 0 ? 'smt-stock-summary--ok' : 'smt-stock-summary--low' }}">
                            <i class="mdi mdi-{{ $subCategory->stock > 0 ? 'package-variant-closed' : 'package-variant' }}"></i>
                            <div>
                                <span class="smt-stock-summary__label">Current available stock</span>
                                <strong>{{ number_format($subCategory->stock ?? 0, 2) }} {{ $subCategory->reportingUnit->name ?? '' }}</strong>
                            </div>
                        </div>

                        <form wire:submit.prevent="saveMovement" id="smt-movement-form">
                            <section class="smt-form-section">
                                <h6 class="smt-form-section__title">Movement type</h6>
                                <div class="smt-type-options">
                                    <label class="smt-type-option {{ ($movementForm['stock_type'] ?? '') === 'stock_in' ? 'smt-type-option--active smt-type-option--in' : '' }}">
                                        <input type="radio"
                                               wire:model.live="movementForm.stock_type"
                                               value="stock_in"
                                               class="smt-type-option__input">
                                        <i class="mdi mdi-arrow-down-bold-circle-outline"></i>
                                        <span>Stock in</span>
                                    </label>
                                    <label class="smt-type-option {{ ($movementForm['stock_type'] ?? '') === 'stock_out' ? 'smt-type-option--active smt-type-option--out' : '' }}">
                                        <input type="radio"
                                               wire:model.live="movementForm.stock_type"
                                               value="stock_out"
                                               class="smt-type-option__input">
                                        <i class="mdi mdi-arrow-up-bold-circle-outline"></i>
                                        <span>Stock out</span>
                                    </label>
                                </div>
                                @error('movementForm.stock_type')
                                    <div class="smt-field-error">{{ $message }}</div>
                                @enderror
                            </section>

                            <section class="smt-form-section">
                                <h6 class="smt-form-section__title">Quantity</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="smt-label">Amount <span class="text-danger">*</span></label>
                                        <input type="number"
                                               wire:model="movementForm.amount"
                                               class="form-control smt-input @error('movementForm.amount') is-invalid @enderror"
                                               step="0.01"
                                               min="0.01"
                                               placeholder="0.00">
                                        @error('movementForm.amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="smt-label">Unit of measure <span class="text-danger">*</span></label>
                                        <x-searchable-select
                                            wire:model="movementForm.uom_id"
                                            :options="collect($reportingUnits)->map(fn($unit) => ['id' => $unit->id, 'name' => $unit->name])"
                                            placeholder="Search units..."
                                            empty-label="Select unit"
                                            class="@error('movementForm.uom_id') is-invalid @enderror"
                                        />
                                        @error('movementForm.uom_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="smt-form-section">
                                <h6 class="smt-form-section__title">Notes</h6>
                                <label class="smt-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="movementForm.description"
                                          class="form-control smt-input @error('movementForm.description') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Reason or reference for this movement..."></textarea>
                                @error('movementForm.description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </section>
                        </form>
                    </div>
                    <div class="modal-footer smt-modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeMovementModal">Cancel</button>
                        <button type="submit"
                                form="smt-movement-form"
                                class="btn btn-primary smt-save-btn"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveMovement">
                                <i class="mdi mdi-content-save-outline"></i> Save movement
                            </span>
                            <span wire:loading wire:target="saveMovement">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.lab.partials.scd-styles')

    <style>
    .solutions-movement-tracker {
        --smt-primary: #2563eb;
        --smt-primary-soft: #eff6ff;
        --smt-slate-50: #f8fafc;
        --smt-slate-200: #e2e8f0;
        --smt-slate-500: #64748b;
    }

    .smt-action-btn,
    .smt-save-btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
    }

    .smt-alert { border-radius: 12px; border: none; }

    .smt-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.35rem;
    }

    .smt-input {
        border-radius: 10px;
        border-color: var(--smt-slate-200);
        font-size: 0.9rem;
    }

    .smt-input:focus,
    .smt-per-page:focus {
        border-color: var(--smt-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .smt-input[readonly] {
        background: var(--smt-slate-50);
        color: #334155;
    }

    .smt-thumb {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--smt-slate-200);
    }

    .smt-stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .smt-stock-badge--available {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .smt-stock-badge--empty {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .smt-stock-badge i { font-size: 1.25rem; }

    .smt-table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--smt-slate-500);
        font-weight: 600;
        background: var(--smt-slate-50);
        border-bottom: 1px solid var(--smt-slate-200);
    }

    .smt-table tbody td {
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .smt-table-thumb {
        width: 30px;
        height: 30px;
        object-fit: cover;
        border-radius: 6px;
    }

    .smt-per-page {
        width: auto;
        border-radius: 8px;
        min-width: 4.5rem;
    }

    .smt-empty-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: var(--smt-primary-soft);
        color: var(--smt-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .smt-modal-backdrop {
        background-color: rgba(15, 23, 42, 0.45);
        overflow-y: auto;
    }

    .smt-modal-content { border-radius: 16px; max-width: 100%; }

    .smt-modal-header,
    .smt-modal-footer { padding: 1.25rem 1.5rem; }

    .smt-modal-body { padding: 0 1.5rem 1rem; }

    .smt-stock-summary {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--smt-slate-200);
    }

    .smt-stock-summary i { font-size: 1.5rem; }

    .smt-stock-summary--ok {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .smt-stock-summary--low {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .smt-stock-summary__label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.85;
        margin-bottom: 0.15rem;
    }

    .smt-form-section {
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--smt-slate-200);
    }

    .smt-form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .smt-form-section__title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--smt-slate-500);
        margin-bottom: 0.85rem;
    }

    .smt-type-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .smt-type-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        padding: 1rem;
        border: 2px solid var(--smt-slate-200);
        border-radius: 12px;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        margin: 0;
        font-weight: 600;
        font-size: 0.9rem;
        color: #475569;
        background: #fff;
    }

    .smt-type-option i { font-size: 1.5rem; }

    .smt-type-option__input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .smt-type-option:hover {
        border-color: #93c5fd;
        background: var(--smt-primary-soft);
    }

    .smt-type-option--active.smt-type-option--in {
        border-color: #10b981;
        background: #ecfdf5;
        color: #047857;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .smt-type-option--active.smt-type-option--out {
        border-color: #f59e0b;
        background: #fffbeb;
        color: #b45309;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
    }

    .smt-field-error {
        display: block;
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 0.35rem;
    }

    .modal.show { display: block !important; }

    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 220px);
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('movement-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });

        Livewire.on('movement-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
