<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-calculator text-primary"></i>
                                Formula Management
                            </h2>
                            <p class="text-muted mb-0">Create, edit, and manage formula workflows</p>
                        </div>
                        <button wire:click="showCreateFormulaModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Create Formula
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
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by formula name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulas Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($formulas->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $formulas->firstItem() ?? 0 }} to {{ $formulas->lastItem() ?? 0 }} of {{ $formulas->total() }} entries
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
                            <table class="table table-striped table-hover" id="formulas-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Versions</th>
                                        <th>Created</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formulas as $formula)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $formula->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($formula->description, 50) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $formula->is_active ? 'success' : 'secondary' }}">
                                                    {{ $formula->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $formula->formulaVersions->count() }} version(s)
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $formula->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditFormulaModal(@js($formula->id))" 
                                                            class="btn btn-sm btn-outline-primary mr-2" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    @if($formula->formulaVersions->where('is_active', true)->first())
                                                        <a href="{{ route('formulars.steps', $formula->formulaVersions->where('is_active', true)->first()->id) }}" 
                                                           class="btn btn-sm btn-outline-success mr-2" title="Edit Steps">
                                                            <i class="mdi mdi-cogs"></i>
                                                        </a>
                                                    @endif
                                                    <button wire:click="showVersionModal(@js($formula->id))" 
                                                            class="btn btn-sm btn-outline-info mr-2" title="New Version">
                                                        <i class="mdi mdi-plus-circle"></i>
                                                    </button>
                                                    <button wire:click="toggleFormulaStatus(@js($formula->id))" 
                                                            class="btn btn-sm btn-outline-{{ $formula->is_active ? 'warning' : 'success' }} mr-2" 
                                                            title="{{ $formula->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="mdi mdi-{{ $formula->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteFormula(@js($formula->id))" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this formula?')">
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $formulas->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-calculator fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No formulas found</h5>
                            <p class="text-muted">Create your first formula to get started.</p>
                            <button wire:click="showCreateFormulaModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Create Formula
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- -------------------------------MODALS------------------------------- --}}
    
    <!-- Create Formula Modal -->
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus"></i>
                            Create New Formula
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
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
                        
                        <form wire:submit.prevent="createFormula">
                            <div class="mb-3">
                                <label for="formulaName" class="form-label">Formula Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="formulaName" class="form-control @error('formulaName') is-invalid @enderror" id="formulaName" required>
                                @error('formulaName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="formulaDescription" class="form-label">Description</label>
                                <textarea wire:model="formulaDescription" class="form-control @error('formulaDescription') is-invalid @enderror" id="formulaDescription" rows="3"></textarea>
                                @error('formulaDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="formulaIsActive" class="form-check-input" id="formulaIsActive">
                                    <label class="form-check-label" for="formulaIsActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCreateModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createFormula">
                            <i class="mdi mdi-content-save"></i> Create Formula
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Edit Formula Modal -->
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-pencil"></i>
                            Edit Formula
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
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
                        
                        <form wire:submit.prevent="updateFormula">
                            <div class="mb-3">
                                <label for="editFormulaName" class="form-label">Formula Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="formulaName" class="form-control @error('formulaName') is-invalid @enderror" id="editFormulaName" required>
                                @error('formulaName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="editFormulaDescription" class="form-label">Description</label>
                                <textarea wire:model="formulaDescription" class="form-control @error('formulaDescription') is-invalid @enderror" id="editFormulaDescription" rows="3"></textarea>
                                @error('formulaDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="formulaIsActive" class="form-check-input" id="editFormulaIsActive">
                                    <label class="form-check-label" for="editFormulaIsActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="updateFormula">
                            <i class="mdi mdi-content-save"></i> Update Formula
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- New Version Modal -->
    @if($showVersionModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus-circle"></i>
                            Create New Version
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showVersionModal', false)"></button>
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
                        
                        <form wire:submit.prevent="createNewVersion">
                            <div class="mb-3">
                                <label for="versionDescription" class="form-label">Version Description <span class="text-danger">*</span></label>
                                <textarea wire:model="versionDescription" class="form-control @error('versionDescription') is-invalid @enderror" id="versionDescription" rows="3" 
                                          placeholder="Describe the changes in this version..." required></textarea>
                                @error('versionDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showVersionModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createNewVersion">
                            <i class="mdi mdi-content-save"></i> Create Version
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
    </style>
</div>