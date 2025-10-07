<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-table text-info"></i>
                                Lookup Tables Management
                            </h2>
                            <p class="text-muted mb-0">Create, edit, and manage lookup tables for formulas</p>
                        </div>
                        <button wire:click="showCreateTableModal" class="btn btn-info">
                            <i class="mdi mdi-plus"></i> Create Table
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name or description...">
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

    <!-- Tables Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($tables->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $tables->firstItem() ?? 0 }} to {{ $tables->lastItem() ?? 0 }} of {{ $tables->total() }} entries
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
                            <table class="table table-striped table-hover" id="tables-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Key Columns</th>
                                        <th>Value Column</th>
                                        <th>Labels</th>
                                        <th>Entries</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th style="width: 250px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tables as $table)
                                        <tr>
                                            <td>{{ $table->id }}</td>
                                            <td>
                                                <strong>{{ $table->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($table->description, 50) }}</span>
                                            </td>
                                            <td>
                                                @foreach($table->key_columns as $column)
                                                    <span class="badge badge-secondary me-1">{{ $column }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $table->value_column }}</span>
                                            </td>
                                            <td>
                                                @if($table->key_label || $table->value_label)
                                                    <div class="small">
                                                        @if($table->key_label)
                                                            <div><strong>Key:</strong> {{ $table->key_label }}</div>
                                                        @endif
                                                        @if($table->value_label)
                                                            <div><strong>Value:</strong> {{ $table->value_label }}</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">No labels defined</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $table->entries_count }} entries</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $table->is_active ? 'success' : 'secondary' }}">
                                                    {{ $table->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $table->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditTableModal({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="showImportModal({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-success" title="Import Data">
                                                        <i class="mdi mdi-upload"></i>
                                                    </button>
                                                    <button wire:click="exportTable({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-info" title="Export Data">
                                                        <i class="mdi mdi-download"></i>
                                                    </button>
                                                    <button wire:click="toggleTableStatus({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-{{ $table->is_active ? 'warning' : 'success' }}" 
                                                            title="{{ $table->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="mdi mdi-{{ $table->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteTable({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this table?')">
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
                            {{ $tables->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-table fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No lookup tables found</h5>
                            <p class="text-muted">Create your first lookup table to get started.</p>
                            <button wire:click="showCreateTableModal" class="btn btn-info">
                                <i class="mdi mdi-plus"></i> Create Table
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Table Modal -->
@if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus"></i>
                        Create New Lookup Table
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="createTable">
                        <div class="mb-3">
                            <label for="tableName" class="form-label">Table Name *</label>
                            <input type="text" wire:model="tableName" class="form-control" id="tableName" required>
                            @error('tableName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="tableDescription" class="form-label">Description</label>
                            <textarea wire:model="tableDescription" class="form-control" id="tableDescription" rows="3"></textarea>
                            @error('tableDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Key Columns *</label>
                            @foreach($keyColumns as $index => $column)
                                <div class="input-group mb-2">
                                    <input type="text" wire:model="keyColumns.{{ $index }}" class="form-control" placeholder="Column name">
                                    @if(count($keyColumns) > 1)
                                        <button type="button" wire:click="removeKeyColumn({{ $index }})" class="btn btn-outline-danger">
                                            <i class="mdi mdi-minus"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                            <button type="button" wire:click="addKeyColumn" class="btn btn-outline-success btn-sm">
                                <i class="mdi mdi-plus"></i> Add Key Column
                            </button>
                            @error('keyColumns') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="valueColumn" class="form-label">Value Column *</label>
                            <input type="text" wire:model="valueColumn" class="form-control" id="valueColumn" required>
                            @error('valueColumn') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Label Definitions Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-label"></i> Label Definitions
                                </h6>
                                <small class="text-muted">Define user-friendly labels for key and value fields</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="keyLabel" class="form-label">Key Definition Label</label>
                                            <input type="text" wire:model="keyLabel" class="form-control" id="keyLabel" placeholder="e.g., Sample ID, Temperature">
                                            @error('keyLabel') <span class="text-danger">{{ $message }}</span> @enderror
                                            <div class="form-text">This label will be shown in formula step editor</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="valueLabel" class="form-label">Value Definition Label</label>
                                            <input type="text" wire:model="valueLabel" class="form-control" id="valueLabel" placeholder="e.g., Result Code, Description">
                                            @error('valueLabel') <span class="text-danger">{{ $message }}</span> @enderror
                                            <div class="form-text">This label will be shown in formula step editor</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                    <button type="button" class="btn btn-info" wire:click="createTable">Create Table</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Edit Table Modal -->
@if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i>
                        Edit Lookup Table
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="updateTable">
                        <div class="mb-3">
                            <label for="editTableName" class="form-label">Table Name *</label>
                            <input type="text" wire:model="tableName" class="form-control" id="editTableName" required>
                            @error('tableName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editTableDescription" class="form-label">Description</label>
                            <textarea wire:model="tableDescription" class="form-control" id="editTableDescription" rows="3"></textarea>
                            @error('tableDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Key Columns *</label>
                            @foreach($keyColumns as $index => $column)
                                <div class="input-group mb-2">
                                    <input type="text" wire:model="keyColumns.{{ $index }}" class="form-control" placeholder="Column name">
                                    @if(count($keyColumns) > 1)
                                        <button type="button" wire:click="removeKeyColumn({{ $index }})" class="btn btn-outline-danger">
                                            <i class="mdi mdi-minus"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                            <button type="button" wire:click="addKeyColumn" class="btn btn-outline-success btn-sm">
                                <i class="mdi mdi-plus"></i> Add Key Column
                            </button>
                            @error('keyColumns') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editValueColumn" class="form-label">Value Column *</label>
                            <input type="text" wire:model="valueColumn" class="form-control" id="editValueColumn" required>
                            @error('valueColumn') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Label Definitions Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-label"></i> Label Definitions
                                </h6>
                                <small class="text-muted">Define user-friendly labels for key and value fields</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editKeyLabel" class="form-label">Key Definition Label</label>
                                            <input type="text" wire:model="keyLabel" class="form-control" id="editKeyLabel" placeholder="e.g., Sample ID, Temperature">
                                            @error('keyLabel') <span class="text-danger">{{ $message }}</span> @enderror
                                            <div class="form-text">This label will be shown in formula step editor</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editValueLabel" class="form-label">Value Definition Label</label>
                                            <input type="text" wire:model="valueLabel" class="form-control" id="editValueLabel" placeholder="e.g., Result Code, Description">
                                            @error('valueLabel') <span class="text-danger">{{ $message }}</span> @enderror
                                            <div class="form-text">This label will be shown in formula step editor</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                    <button type="button" class="btn btn-info" wire:click="updateTable">Update Table</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Import Data Modal -->
@if($showImportModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-upload"></i>
                        Import Data to {{ $editingTable->name ?? 'Table' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showImportModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select File *</label>
                        <input type="file" wire:model="importFile" class="form-control" id="importFile" accept=".xlsx,.xls,.csv">
                        @error('importFile') <span class="text-danger">{{ $message }}</span> @enderror
                        <div class="form-text">Supported formats: Excel (.xlsx, .xls) and CSV files</div>
                    </div>

                    @if($importFile)
                        <div class="mb-3">
                            <button type="button" wire:click="previewImport" class="btn btn-outline-primary">
                                <i class="mdi mdi-eye"></i> Preview Import
                            </button>
                        </div>
                    @endif

                    @if(!empty($importPreview))
                        <div class="mb-3">
                            <h6>Preview (First 10 rows):</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            @foreach(array_keys($importPreview[0] ?? []) as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($importPreview as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if(!empty($importErrors))
                            <div class="alert alert-danger">
                                <h6>Validation Errors:</h6>
                                <ul class="mb-0">
                                    @foreach($importErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showImportModal', false)">Cancel</button>
                    @if($importFile && empty($importErrors))
                        <button type="button" wire:click="importData" class="btn btn-success">
                            <i class="mdi mdi-upload"></i> Import Data
                        </button>
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
    </style>
</div>