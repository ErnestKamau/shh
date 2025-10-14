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
                                <select wire:model.live="statusFilter" wire:change="$refresh" class="form-select">
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
                                                            class="btn btn-sm mr-2 btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="toggleVariableStatus({{ $variable->id }})" 
                                                            class="btn btn-sm mr-2 btn-outline-{{ $variable->is_active ? 'warning' : 'success' }}" 
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
                    <button type="button" class="btn-close" wire:click="closeCreateModal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="closeCreateModal"></span>
                        <span wire:loading wire:target="closeCreateModal" class="spinner-border spinner-border-sm" role="status"></span>
                    </button>
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
                    <button type="button" class="btn btn-secondary" wire:click="closeCreateModal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="closeCreateModal">Cancel</span>
                        <span wire:loading wire:target="closeCreateModal">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Closing...
                        </span>
                    </button>
                    <button type="button" class="btn btn-success" wire:click="createVariable" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createVariable">Create Variable</span>
                        <span wire:loading wire:target="createVariable">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Creating...
                        </span>
                    </button>
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
                    <button type="button" class="btn-close" wire:click="closeEditModal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="closeEditModal"></span>
                        <span wire:loading wire:target="closeEditModal" class="spinner-border spinner-border-sm" role="status"></span>
                    </button>
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
                    <button type="button" class="btn btn-secondary" wire:click="closeEditModal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="closeEditModal">Cancel</span>
                        <span wire:loading wire:target="closeEditModal">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Closing...
                        </span>
                    </button>
                    <button type="button" class="btn btn-success" wire:click="updateVariable" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="updateVariable">Update Variable</span>
                        <span wire:loading wire:target="updateVariable">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Updating...
                        </span>
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
    
    /* Modern Select Styling */
    .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 0.6rem 2.5rem 0.6rem 1rem;
        font-size: 0.95rem;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    .form-select:hover {
        border-color: #4e73df;
        box-shadow: 0 4px 8px rgba(78, 115, 223, 0.1);
    }
    
    .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: 0;
    }
    
    .form-select-sm {
        padding: 0.4rem 2rem 0.4rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }
    
    /* Modern Input Styling */
    .form-control {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    
    .form-control:hover {
        border-color: #4e73df;
        box-shadow: 0 4px 8px rgba(78, 115, 223, 0.1);
    }
    
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: 0;
    }
    
    /* Textarea specific */
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    /* Button enhancements */
    .btn {
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }
    
    /* Badge styling */
    .badge {
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    /* Form labels */
    .form-label {
        font-weight: 500;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }
    
    /* Checkbox styling */
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #e3e6f0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .form-check-input:checked {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: 0;
    }
    
    .form-check-label {
        cursor: pointer;
        margin-left: 0.5rem;
    }
    
    /* Make modal body scrollable */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
    }
    
    .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    .modal-header,
    .modal-footer {
        flex-shrink: 0;
    }
    
    /* Custom scrollbar */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    </style>
</div>