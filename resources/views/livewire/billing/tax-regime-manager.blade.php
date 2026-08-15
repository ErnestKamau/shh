<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-money-bill-alt text-primary"></i>
                                Tax Regime Management
                            </h2>
                            <p class="text-muted mb-0">Manage tax rates for invoicing and quotations</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="showCreateTaxModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Tax Rate
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by tax value...">
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

    <!-- Tax Regimes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tax Regimes</h5>
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
                                    <th width="8%">No</th>
                                    <th width="20%">Created At</th>
                                    <th width="20%">Registered By</th>
                                    <th width="15%">Tax Value</th>
                                    <th width="12%">Status</th>
                                    <th width="15%">End Date</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($taxRegimes->count() > 0)
                                    @foreach($taxRegimes as $tax)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <small>{{ $tax->created_at->format('Y-m-d H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($tax->registeredBy)
                                                    <strong>{{ $tax->registeredBy->name }}</strong>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary p-2 ">
                                                    {{ $tax->value }}%
                                                </span>
                                            </td>
                                            <td>
                                                @if($tax->active)
                                                    <span class="badge bg-success p-2 badge-pill">
                                                        <i class="mdi mdi-check-circle"></i> Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary p-2 badge-pill">
                                                        <i class="mdi mdi-close-circle"></i> Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tax->end_date)
                                                    <small>{{ $tax->end_date->format('Y-m-d H:i:s') }}</small>
                                                @else
                                                    <span class="badge bg-info text-white p-2 badge-pill">To Date</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" wire:click="showEditTaxModal('{{ $tax->id }}')" class="btn btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button" wire:click="toggleStatus('{{ $tax->id }}')"
                                                            class="btn btn-outline-{{ $tax->active ? 'warning' : 'success' }}"
                                                            title="{{ $tax->active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="mdi mdi-{{ $tax->active ? 'close-circle' : 'check-circle' }}"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div style="padding: 3rem 1rem;">
                                                <i class="fas fa-money-bill-alt text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                                <h5 class="text-muted mt-3 mb-2">No Tax Regimes Found</h5>
                                                <p class="text-muted mb-3">Get started by adding a new tax rate</p>
                                                <button wire:click="showCreateTaxModal" class="btn btn-primary">
                                                    <i class="mdi mdi-plus"></i> Add Tax Rate
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if($taxRegimes->count() > 0)
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $taxRegimes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Modal -->
    @if($showTaxModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title">
                            <i class="fas fa-money-bill-alt"></i>
                            {{ $editingTax ? 'Edit Tax Regime' : 'Add New Tax Regime' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="saveTax">
                            <div class="mb-3">
                                <label for="value" class="form-label fw-bold">
                                    Tax Value (%) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       wire:model="taxForm.value" 
                                       id="value" 
                                       class="form-control @error('taxForm.value') is-invalid @enderror" 
                                       placeholder="e.g., 16.00">
                                @error('taxForm.value') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                                <small class="form-text text-muted">Enter the tax percentage (0-100)</small>
                            </div>

                            <div class="form-check p-3 bg-light rounded">
                                <input class="form-check-input"
                                       type="checkbox"
                                       wire:model.live="taxForm.active"
                                       id="active">
                                <label class="form-check-label fw-bold" for="active">
                                    <i class="mdi mdi-check-circle text-success"></i> Set as Active Tax Rate
                                </label>
                                <br>
                                <small class="text-muted">
                                    <i class="mdi mdi-information"></i> 
                                    Activating this will deactivate all other tax rates
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveTax">
                            <i class="mdi mdi-content-save"></i> {{ $editingTax ? 'Update' : 'Save' }} Tax Regime
                        </button>
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
    
        /* Active tax row highlight */
        .table-success {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }
    
        /* Empty state styling */
        tbody tr td[colspan] {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
    </style>
</div>

