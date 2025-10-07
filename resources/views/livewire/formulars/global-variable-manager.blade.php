<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-variable text-success"></i>
                                Global Variables Management
                            </h2>
                            <p class="text-muted mb-0">Create, edit, and manage global variables for formulas</p>
                        </div>
                        <button wire:click="showCreateVariableModal" class="btn btn-success">
                            <i class="mdi mdi-plus"></i> Create Variable
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, value, or description...">
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

    <!-- Variables Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($variables->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $variables->firstItem() ?? 0 }} to {{ $variables->lastItem() ?? 0 }} of {{ $variables->total() }} entries
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
                            <table class="table table-striped table-hover" id="variables-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>Value</th>
                                        <th>Data Type</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variables as $variable)
                                        <tr>
                                            <td>{{ $variable->id }}</td>
                                            <td>
                                                <strong>{{ $variable->name }}</strong>
                                            </td>
                                            <td>
                                                <code class="text-primary">{{ $variable->value }}</code>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst($variable->data_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($variable->description, 50) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $variable->is_active ? 'success' : 'secondary' }}">
                                                    {{ $variable->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $variable->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditVariableModal({{ $variable->id }})" 
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="toggleVariableStatus({{ $variable->id }})" 
                                                            class="btn btn-sm btn-outline-{{ $variable->is_active ? 'warning' : 'success' }}" 
                                                            title="{{ $variable->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="mdi mdi-{{ $variable->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteVariable({{ $variable->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this variable?')">
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
                            {{ $variables->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-variable fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No variables found</h5>
                            <p class="text-muted">Create your first global variable to get started.</p>
                            <button wire:click="showCreateVariableModal" class="btn btn-success">
                                <i class="mdi mdi-plus"></i> Create Variable
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Variable Modal -->
@if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus"></i>
                        Create New Global Variable
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="createVariable">
                        <div class="mb-3">
                            <label for="variableName" class="form-label">Variable Name *</label>
                            <input type="text" wire:model="variableName" class="form-control" id="variableName" required>
                            @error('variableName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="dataType" class="form-label">Data Type *</label>
                            <select wire:model="dataType" class="form-select" id="dataType" required>
                                <option value="string">String</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                            </select>
                            @error('dataType') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="variableValue" class="form-label">Value *</label>
                            <input type="text" wire:model="variableValue" class="form-control" id="variableValue" required>
                            @error('variableValue') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="variableDescription" class="form-label">Description</label>
                            <textarea wire:model="variableDescription" class="form-control" id="variableDescription" rows="3"></textarea>
                            @error('variableDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" wire:model="isActive" class="form-check-input" id="isActive">
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCreateModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="createVariable">Create Variable</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Edit Variable Modal -->
@if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i>
                        Edit Global Variable
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="updateVariable">
                        <div class="mb-3">
                            <label for="editVariableName" class="form-label">Variable Name *</label>
                            <input type="text" wire:model="variableName" class="form-control" id="editVariableName" required>
                            @error('variableName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editDataType" class="form-label">Data Type *</label>
                            <select wire:model="dataType" class="form-select" id="editDataType" required>
                                <option value="string">String</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                            </select>
                            @error('dataType') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editVariableValue" class="form-label">Value *</label>
                            <input type="text" wire:model="variableValue" class="form-control" id="editVariableValue" required>
                            @error('variableValue') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editVariableDescription" class="form-label">Description</label>
                            <textarea wire:model="variableDescription" class="form-control" id="editVariableDescription" rows="3"></textarea>
                            @error('variableDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" wire:model="isActive" class="form-check-input" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="updateVariable">Update Variable</button>
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