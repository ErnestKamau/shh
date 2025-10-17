<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h2 class="mb-0">
                        <i class="mdi mdi-finance text-primary"></i>
                        {{ $subCategory->name }}
                    </h2>
                    <p class="text-muted mb-0">Track and manage stock movements</p>
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

    <!-- Two Panel Layout -->
    <div class="row">
        <!-- Left Panel: Sub-Category Details (Read-Only) -->
        <div class="col-xl-4 col-sm-12 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-information"></i> Details
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" value="{{ $subCategory->name }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" readonly>{{ $subCategory->description }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" value="{{ $subCategory->category->name ?? 'N/A' }}" readonly>
                    </div>

                    @if($subCategory->image)
                    <div class="form-group mb-3">
                        <label class="form-label">Image</label>
                        <div>
                            <img src="{{ $subCategory->image }}" class="img-thumbnail" style="width: 100px; height: 100px;" alt="{{ $subCategory->name }}">
                        </div>
                    </div>
                    @endif

                    <div class="form-group mb-3">
                        <label class="form-label">Rate</label>
                        <input type="text" class="form-control" value="{{ $subCategory->rate }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Unit of Measure</label>
                        <input type="text" class="form-control" value="{{ $subCategory->reportingUnit->name ?? 'N/A' }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Stock Movement Tracking -->
        <div class="col-xl-8 col-sm-12">
            <!-- Current Stock Badge -->
            <div class="mb-3">
                @if($subCategory->stock > 0)
                    <span class="badge p-3" style="background-color: white; border: 1px solid #28a745;">
                        <i class="mdi mdi-package-variant-closed text-success" style="font-size: 18px;"></i>
                        <strong>Available:</strong> {{ number_format($subCategory->stock ?? 0, 2) }} {{ $subCategory->reportingUnit->name ?? '' }}
                    </span>
                @else
                    <span class="badge bg-danger p-3">
                        <i class="mdi mdi-package-variant" style="font-size: 18px;"></i>
                        <strong>Available:</strong> {{ number_format($subCategory->stock ?? 0, 2) }} {{ $subCategory->reportingUnit->name ?? '' }}
                    </span>
                @endif
                
                <button wire:click="showAddMovementModal" class="btn btn-primary float-end">
                    <i class="mdi mdi-plus"></i> Stock In/Out
                </button>
            </div>

            <!-- Stock Movement History -->
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-history"></i> Stock Movement History
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($this->stockMovements->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->stockMovements->firstItem() ?? 0 }} to {{ $this->stockMovements->lastItem() ?? 0 }} of {{ $this->stockMovements->total() }} entries
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Stock In</th>
                                        <th>Stock Out</th>
                                        <th>UOM</th>
                                        <th>Created By</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->stockMovements as $movement)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <img src="{{ $subCategory->image }}" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;" alt="">
                                                {{ $subCategory->name }}
                                            </td>
                                            <td>
                                                @if($movement->stock_in > 0)
                                                    <span class="text-success">{{ number_format($movement->stock_in, 2) }}</span>
                                                @else
                                                    {{ number_format($movement->stock_in, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($movement->stock_out > 0)
                                                    <span class="text-danger">{{ number_format($movement->stock_out, 2) }}</span>
                                                @else
                                                    {{ number_format($movement->stock_out, 2) }}
                                                @endif
                                            </td>
                                            <td>{{ $movement->uom->name ?? 'N/A' }}</td>
                                            <td>{{ $movement->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $movement->created_at->format('Y-m-d h:i:s A') }}</td>
                                            <td>{{ $movement->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->stockMovements->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-history text-muted" style="font-size: 3rem;"></i>
                            <h6 class="text-muted mt-3">No stock movements recorded yet</h6>
                            <p class="text-muted">Click "Stock In/Out" to add your first movement.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Modal -->
    @if($showMovementModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-package-variant text-success"></i>
                            Add Stock In / Out
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeMovementModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Stock Status Alert -->
                        @if($subCategory->stock > 0)
                            <div class="alert alert-success">
                                <i class="mdi mdi-package-variant-closed"></i> 
                                {{ $subCategory->name }} available stock: {{ number_format($subCategory->stock ?? 0, 2) }} {{ $subCategory->reportingUnit->name ?? '' }}
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="mdi mdi-package-variant"></i> 
                                {{ $subCategory->name }} available stock is {{ number_format($subCategory->stock ?? 0, 2) }}
                            </div>
                        @endif

                        <form wire:submit.prevent="saveMovement">
                            <div class="form-group mb-3">
                                <label class="form-label">Stock Movement Type <span class="text-danger">*</span></label>
                                <select wire:model="movementForm.stock_type" 
                                        class="form-select @error('movementForm.stock_type') is-invalid @enderror">
                                    <option value="">Select either Stock In / Stock Out</option>
                                    <option value="stock_in">Stock In</option>
                                    <option value="stock_out">Stock Out</option>
                                </select>
                                @error('movementForm.stock_type') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       wire:model="movementForm.amount" 
                                       class="form-control @error('movementForm.amount') is-invalid @enderror"
                                       step="0.01"
                                       placeholder="Enter amount">
                                @error('movementForm.amount') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                <select wire:model="movementForm.uom_id" 
                                        class="form-select @error('movementForm.uom_id') is-invalid @enderror">
                                    <option value="">Select UOM</option>
                                    @foreach($reportingUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('movementForm.uom_id') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="movementForm.description" 
                                          class="form-control @error('movementForm.description') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Enter description..."></textarea>
                                @error('movementForm.description') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeMovementModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="saveMovement">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .modal.show {
        display: block !important;
    }

    body.modal-open {
        overflow: hidden;
    }

    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .btn-close {
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        opacity: .5;
    }

    .btn-close:hover {
        opacity: .75;
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
