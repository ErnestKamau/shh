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
                                        <th style="width: 250px;">Actions</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Key Columns</th>
                                        <th>Value Column</th>
                                        <th>Labels</th>
                                        <th>Entries</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tables as $table)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('formulars.lookup-table-entries', $table->id) }}" 
                                                       class="btn btn-sm btn-outline-secondary mr-2" title="Manage Entries">
                                                        <i class="mdi mdi-table-edit"></i>
                                                    </a>
                                                    <button wire:click="showEditTableModal({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-primary mr-2" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="openImportModal({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-success mr-2" title="Import Data">
                                                        <i class="mdi mdi-upload"></i>
                                                    </button>
                                                    <button wire:click="exportTable({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-info mr-2" title="Export Data">
                                                        <i class="mdi mdi-download"></i>
                                                    </button>
                                                    <button wire:click="toggleTableStatus({{ $table->id }})" 
                                                            class="btn btn-sm btn-outline-{{ $table->is_active ? 'warning' : 'success' }} mr-2" 
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
                                            <td>
                                                {{ $table->name }}
                                                <div class="d-flex align-items-center">
                                                    <span class="badge p-2 badge-pill mr-2 {{ $table->is_standard ? 'badge-success' : 'badge-secondary' }}">{!! $table->is_standard ? '<i class="mdi mdi-star text-warning"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!} Is Standard</span>
                                                    <span class="badge p-2 badge-pill {{ $table->show_on_report ? 'badge-success' : 'badge-secondary' }}">{!! $table->show_on_report ? '<i class="mdi mdi-file-document text-info"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!} Show on Report</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($table->lookup_type === 'range_based')
                                                    <span class="">
                                                        <i class="mdi mdi-chart-line"></i> Range
                                                    </span>
                                                    @if($table->range_variable_name)
                                                        <br><small class="text-muted">{{ $table->range_variable_name }}</small>
                                                    @endif
                                                @else
                                                    <span class="">Key-Value</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($table->key_columns as $column)
                                                    <span class="badgeme-1">{{ $column }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="">{{ $table->value_column }}</span>
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
                                                <span class="badge badge-info badge-pill p-2">{{ $table->entries_count }} entries</span>
                                            </td>
                                            <td>
                                                <span class="badge p-2 badge-pill badge-{{ $table->is_active ? 'success' : 'secondary' }}">
                                                    {{ $table->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                           
                                            <td>
                                                <small class="text-muted">{{ $table->created_at->format('M d, Y') }}</small>
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

    <!-- Create Table Modal -->
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                            <label class="form-label fw-bold">
                                <i class="mdi mdi-format-list-bulleted-type"></i> Lookup Type *
                            </label>
                            <select wire:model.live="lookupType" class="form-select lookup-type-select shadow-sm">
                                <option value="key_value_comparison">🔑 Key-Value Comparison</option>
                                <option value="range_based">📊 Range-Based</option>
                            </select>
                            <div class="mt-2 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
                                <small class="text-muted">
                                    <i class="mdi mdi-information-outline"></i>
                                    <strong>Key-Value:</strong> Exact match lookups | 
                                    <strong>Range-Based:</strong> Numeric range lookups
                                </small>
                            </div>
                        </div>

                        @if($lookupType === 'range_based')
                            <div class="alert alert-info mb-3">
                                <i class="mdi mdi-information"></i>
                                <strong>Range-Based Lookups:</strong> Check if an input value falls within defined ranges.
                                Entries will have 'low' and 'high' values. Set 'high' to null for open-ended ranges (e.g., 100+).
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-variable"></i> Range Variable Name *
                                </label>
                                <div class="tag-select-container" wire:click="$set('showRangeVariableDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($rangeVariableName && $rangeVariableName !== 'custom')
                                            <span class="tag-badge">
                                                {{ $rangeVariableName }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('rangeVariableName', null)"></i>
                                            </span>
                                        @endif
                                        
                                        <input type="text" 
                                               wire:model.live="rangeVariableSearch" 
                                               class="tag-input" 
                                               placeholder="{{ $rangeVariableName && $rangeVariableName !== 'custom' ? '' : 'Search variables...' }}"
                                               autocomplete="off">
                                    </div>
                                    
                                    @if($showRangeVariableDropdown ?? false)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-header">Lab Measurements</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('bacterial_count')">bacterial_count</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('ph_value')">ph_value</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('temperature')">temperature</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('pressure')">pressure</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('concentration')">concentration</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('density')">density</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('humidity')">humidity</div>
                                            
                                            <div class="tag-dropdown-divider"></div>
                                            <div class="tag-dropdown-header">General Values</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('score')">score</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('count')">count</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('percentage')">percentage</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('measurement')">measurement</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('weight')">weight</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('volume')">volume</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('time')">time</div>
                                            
                                            <div class="tag-dropdown-divider"></div>
                                            <div class="tag-dropdown-item tag-dropdown-create" wire:click.stop="selectRangeVariable('custom')">
                                                <i class="mdi mdi-pencil text-primary"></i>
                                                <strong>✏️ Enter Custom Name</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">Variable to check against ranges (e.g., bacterial_count, temperature)</small>
                                @error('rangeVariableName') <span class="text-danger d-block">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($rangeVariableName === 'custom')
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Custom Variable Name *</label>
                                    <input type="text" wire:model="customRangeVariableName" class="form-control" 
                                           placeholder="e.g., bacterial_colony_count, dissolved_oxygen">
                                    <small class="text-muted">Enter a custom variable name (lowercase, use underscores)</small>
                                    @error('customRangeVariableName') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-text-box"></i> Value Interpretation Column
                                </label>
                                <div class="tag-select-container" wire:click="$set('showValueInterpretationDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($valueInterpretationColumn)
                                            <span class="tag-badge">
                                                {{ $valueInterpretationColumn }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('valueInterpretationColumn', null)"></i>
                                            </span>
                                        @endif
                                        
                                        <input type="text" 
                                               wire:model.live="valueInterpretationSearch" 
                                               class="tag-input" 
                                               placeholder="{{ $valueInterpretationColumn ? '' : 'Search columns (optional)...' }}"
                                               autocomplete="off">
                                    </div>
                                    
                                    @if($showValueInterpretationDropdown ?? false)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-header">Common Interpretations</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('interpretation')">interpretation</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('description')">description</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('grade')">grade</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('level')">level</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('category')">category</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('status')">status</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('rating')">rating</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('classification')">classification</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('risk_level')">risk_level</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('quality')">quality</div>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">Optional text description for range values (e.g., "Excellent", "Poor")</small>
                                @error('valueInterpretationColumn') <span class="text-danger d-block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">📋 Preview</h6>
                                    <p class="mb-1">
                                        <strong>Input:</strong> <code>{{ $rangeVariableName === 'custom' ? ($customRangeVariableName ?: 'your_variable') : ($rangeVariableName ?: 'variable') }}</code>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Output:</strong> 
                                        <code>value</code>
                                        @if($valueInterpretationColumn)
                                            + <code>{{ $valueInterpretationColumn }}</code>
                                        @endif
                                    </p>
                                    <small class="text-muted">
                                        Example: Input 85 → Returns value from matching range
                                    </small>
                                </div>
                            </div>
                        @endif

                        @if($lookupType === 'key_value_comparison')
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
                        @endif

                        <div class="mb-3">
                            <label for="valueColumn" class="form-label">Value Column *</label>
                            <input type="text" wire:model="valueColumn" class="form-control" id="valueColumn" required>
                            @error('valueColumn') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        @if($lookupType === 'key_value_comparison')
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
                        @endif
                        
                        <!-- Table Settings Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-cog"></i> Table Settings
                                </h6>
                                <small class="text-muted">Configure table status and display options</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" wire:model="isActive" class="form-check-input" id="isActive" role="switch">
                                            <label class="form-check-label" for="isActive">
                                                Active <i class="mdi mdi-check-circle text-success"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" wire:model="isStandard" class="form-check-input" id="isStandard" role="switch">
                                            <label class="form-check-label" for="isStandard">
                                                Is Standard Table <i class="mdi mdi-star text-warning"></i> 
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" wire:model="showOnReport" class="form-check-input" id="showOnReport" role="switch">
                                            <label class="form-check-label" for="showOnReport">
                                                Show on Report <i class="mdi mdi-file-document text-info"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                            <label class="form-label fw-bold">
                                <i class="mdi mdi-format-list-bulleted-type"></i> Lookup Type *
                            </label>
                            <select wire:model.live="lookupType" class="form-select lookup-type-select shadow-sm">
                                <option value="key_value_comparison">🔑 Key-Value Comparison</option>
                                <option value="range_based">📊 Range-Based</option>
                            </select>
                            <div class="mt-2 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
                                <small class="text-muted">
                                    <i class="mdi mdi-information-outline"></i>
                                    <strong>Key-Value:</strong> Exact match lookups | 
                                    <strong>Range-Based:</strong> Numeric range lookups
                                </small>
                            </div>
                        </div>

                        @if($lookupType === 'range_based')
                            <div class="alert alert-info mb-3">
                                <i class="mdi mdi-information"></i>
                                <strong>Range-Based Lookups:</strong> Check if an input value falls within defined ranges.
                                Entries will have 'low' and 'high' values. Set 'high' to null for open-ended ranges (e.g., 100+).
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-variable"></i> Range Variable Name *
                                </label>
                                <div class="tag-select-container" wire:click="$set('showRangeVariableDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($rangeVariableName && $rangeVariableName !== 'custom')
                                            <span class="tag-badge">
                                                {{ $rangeVariableName }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('rangeVariableName', null)"></i>
                                            </span>
                                        @endif
                                        
                                        <input type="text" 
                                               wire:model.live="rangeVariableSearch" 
                                               class="tag-input" 
                                               placeholder="{{ $rangeVariableName && $rangeVariableName !== 'custom' ? '' : 'Search variables...' }}"
                                               autocomplete="off">
                                    </div>
                                    
                                    @if($showRangeVariableDropdown ?? false)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-header">Lab Measurements</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('bacterial_count')">bacterial_count</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('ph_value')">ph_value</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('temperature')">temperature</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('pressure')">pressure</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('concentration')">concentration</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('density')">density</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('humidity')">humidity</div>
                                            
                                            <div class="tag-dropdown-divider"></div>
                                            <div class="tag-dropdown-header">General Values</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('score')">score</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('count')">count</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('percentage')">percentage</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('measurement')">measurement</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('weight')">weight</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('volume')">volume</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectRangeVariable('time')">time</div>
                                            
                                            <div class="tag-dropdown-divider"></div>
                                            <div class="tag-dropdown-item tag-dropdown-create" wire:click.stop="selectRangeVariable('custom')">
                                                <i class="mdi mdi-pencil text-primary"></i>
                                                <strong>✏️ Enter Custom Name</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">Variable to check against ranges (e.g., bacterial_count, temperature)</small>
                                @error('rangeVariableName') <span class="text-danger d-block">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($rangeVariableName === 'custom')
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Custom Variable Name *</label>
                                    <input type="text" wire:model="customRangeVariableName" class="form-control" 
                                           placeholder="e.g., bacterial_colony_count, dissolved_oxygen">
                                    <small class="text-muted">Enter a custom variable name (lowercase, use underscores)</small>
                                    @error('customRangeVariableName') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="mdi mdi-text-box"></i> Value Interpretation Column
                                </label>
                                <div class="tag-select-container" wire:click="$set('showValueInterpretationDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($valueInterpretationColumn)
                                            <span class="tag-badge">
                                                {{ $valueInterpretationColumn }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('valueInterpretationColumn', null)"></i>
                                            </span>
                                        @endif
                                        
                                        <input type="text" 
                                               wire:model.live="valueInterpretationSearch" 
                                               class="tag-input" 
                                               placeholder="{{ $valueInterpretationColumn ? '' : 'Search columns (optional)...' }}"
                                               autocomplete="off">
                                    </div>
                                    
                                    @if($showValueInterpretationDropdown ?? false)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-header">Common Interpretations</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('interpretation')">interpretation</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('description')">description</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('grade')">grade</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('level')">level</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('category')">category</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('status')">status</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('rating')">rating</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('classification')">classification</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('risk_level')">risk_level</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectValueInterpretation('quality')">quality</div>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">Optional text description for range values (e.g., "Excellent", "Poor")</small>
                                @error('valueInterpretationColumn') <span class="text-danger d-block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">📋 Preview</h6>
                                    <p class="mb-1">
                                        <strong>Input:</strong> <code>{{ $rangeVariableName === 'custom' ? ($customRangeVariableName ?: 'your_variable') : ($rangeVariableName ?: 'variable') }}</code>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Output:</strong> 
                                        <code>value</code>
                                        @if($valueInterpretationColumn)
                                            + <code>{{ $valueInterpretationColumn }}</code>
                                        @endif
                                    </p>
                                    <small class="text-muted">
                                        Example: Input 85 → Returns value from matching range
                                    </small>
                                </div>
                            </div>
                        @endif

                        @if($lookupType === 'key_value_comparison')
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
                        @endif

                        <div class="mb-3">
                            <label for="editValueColumn" class="form-label">Value Column *</label>
                            <input type="text" wire:model="valueColumn" class="form-control" id="editValueColumn" required>
                            @error('valueColumn') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        @if($lookupType === 'key_value_comparison')
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
                        @endif
                        
                        <!-- Table Settings Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-cog"></i> Table Settings
                                </h6>
                                <small class="text-muted">Configure table status and display options</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" wire:model="isActive" class="form-check-input" id="editIsActive" role="switch">
                                            <label class="form-check-label" for="editIsActive">
                                                <i class="mdi mdi-check-circle text-success"></i> Active
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" wire:model="isStandard" class="form-check-input" id="editIsStandard" role="switch">
                                            <label class="form-check-label" for="editIsStandard">
                                                <i class="mdi mdi-star text-warning"></i> Standard Table
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" wire:model="showOnReport" class="form-check-input" id="editShowOnReport" role="switch">
                                            <label class="form-check-label" for="editShowOnReport">
                                                <i class="mdi mdi-file-document text-info"></i> Show on Report
                                            </label>
                                        </div>
                                    </div>
                                </div>
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
                    <!-- Template Download Helper -->
                    <div class="alert alert-info mb-4" style="border-left: 4px solid #6f42c1;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="mdi mdi-file-excel-outline me-3" style="font-size: 2.5rem; color: #6f42c1;"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><strong>📥 Need a template to get started?</strong></h6>
                                <p class="mb-0 small text-muted">Download the Excel template with pre-configured columns for this table.</p>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" wire:click="downloadTemplate({{ $editingTable->id ?? 0 }})" class="btn btn-purple" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="downloadTemplate">
                                    <i class="mdi mdi-download"></i> Download Excel Template
                                </span>
                                <span wire:loading wire:target="downloadTemplate">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Generating Template...
                                </span>
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="mdi mdi-information"></i>
                                The template includes: <strong>{{ implode(', ', $editingTable->key_columns ?? []) }}</strong>
                                @if($editingTable && $editingTable->value_column)
                                    and <strong>{{ $editingTable->value_column }}</strong>
                                @endif
                            </small>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h6 class="mb-3">Or upload your existing file</h6>

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
    
    /* Lookup Type Select Styling */
    .lookup-type-select {
        padding: 0.75rem 1rem;
        height: 3rem;
        font-size: 0.9rem;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    .lookup-type-select:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        border-color: #17a2b8;
    }
    
    .lookup-type-select:hover {
        border-color: #17a2b8;
    }
    
    /* Variable Select Styling */
    .variable-select {
        padding: 0.75rem 1rem !important;
        height: 3rem !important;
        font-size: 0.9rem !important;
        border-radius: 10px !important;
        border: 2px solid #e0e0e0 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        background-color: #fff !important;
    }
    
    .variable-select:focus {
        outline: none !important;
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25) !important;
        /* border-color: #6f42c1 !important; */
    }
    
    .variable-select:hover {
        border-color: #a598f0 !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Purple button styling for template download */
    .btn-purple {
        color: #fff;
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
    
    .btn-purple:hover {
        color: #fff;
        background-color: #5a32a3;
        border-color: #5a32a3;
    }
    
    .btn-purple:focus,
    .btn-purple.focus {
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.5);
    }
    
    /* Make modal body scrollable */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Ensure modal dialog is properly sized */
    .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
    }
    
    /* Modal content styling */
    .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    /* Keep header and footer fixed, body scrollable */
    .modal-header {
        flex-shrink: 0;
    }
    
    .modal-footer {
        flex-shrink: 0;
    }
    
    /* Custom scrollbar for better UX */
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
    .form-text{
        font-size: 0.6rem;
        color: #6c757d;
        margin-top: 0.25rem;
        margin-bottom: 0;
        font-weight: 400;
        line-height: 1.5;
        text-align: left;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }
    
    /* Single-Select Searchable Dropdown Styling */
    .searchable-input-single {
        border: none;
        outline: none;
        box-shadow: none !important;
        padding: 4px 0;
        width: 100%;
    }
    
    .searchable-input-single:focus {
        border: none !important;
        box-shadow: none !important;
    }
    
    .single-select-container {
        position: relative;
        min-height: 45px;
        border: 1px solid #ced4da;
        border-radius: 12px;
        padding: 8px 40px 8px 12px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .single-select-container:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    }
    
    .single-select-container:has(.searchable-input-single:focus) {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .options-list {
        padding: 8px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .option-item:hover {
        background: #f8f9fa;
    }
    
    .option-item.selected {
        background: rgba(0, 123, 255, 0.08);
        font-weight: 500;
    }
    
    .option-item i {
        font-size: 18px;
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
    
    .tag-dropdown-create {
        background-color: #f8f9fa;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .tag-dropdown-create:hover {
        background-color: #e9ecef;
    }
    
    .tag-dropdown-divider {
        height: 1px;
        background-color: #dee2e6;
        margin: 4px 0;
    }
    
    .tag-dropdown-header {
        padding: 8px 16px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    </style>
    
    @script
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container')) {
            $wire.set('showRangeVariableDropdown', false);
            $wire.set('showValueInterpretationDropdown', false);
        }
    });
    </script>
    @endscript
</div>