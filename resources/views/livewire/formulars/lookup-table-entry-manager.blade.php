<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center-">
                        <div>
                            <h4 class="mb-0">
                                <i class="mdi mdi-table-edit text-info"></i>
                                Manage Entries: {{ $lookupTable->name }}
                            </h4>
                            <p class="text-muted mb-0">{{ $lookupTable->description }}</p>
                            <div class="mt-2">
                                @if($lookupTable->lookup_type === 'range_based')
                                    <span class="badge badge-warning me-1">
                                        <i class="mdi mdi-chart-line"></i> Range-Based
                                    </span>
                                    <span class="badge badge-secondary me-1">Variable: {{ $lookupTable->range_variable_name }}</span>
                                @else
                                    <span class="badge badge-primary me-1">
                                        <i class="mdi mdi-key"></i> Key-Value
                                    </span>
                                <span class="badge badge-secondary me-1">Keys: {{ implode(', ', $lookupTable->key_columns) }}</span>
                                @endif
                                <span class="badge badge-primary">Value: {{ $lookupTable->value_column }}</span>
                                @if($lookupTable->value_interpretation_column)
                                    <span class="badge badge-info">Interpretation: {{ $lookupTable->value_interpretation_column }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('formulars.lookup-tables') }}" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                            <button wire:click="openImportModal" class="btn btn-sm btn-info me-2">
                                <i class="mdi mdi-upload"></i> Bulk Import
                            </button>
                            <button wire:click="showCreateEntryModal" class="btn btn-sm btn-success">
                                <i class="mdi mdi-plus"></i> Add Entry
                            </button>
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

    <!-- Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Search entries...">
                        </div>
                        <div class="col-md-2">
                            <button wire:click="clearSearch" class="btn btn-outline-secondary w-100">
                                <i class="mdi mdi-refresh"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Entries Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($entries->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
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
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        @if($lookupTable->lookup_type === 'range_based')
                                            <th>Range (Low - High)</th>
                                            <th>{{ ucfirst($lookupTable->value_column) }}</th>
                                            @if($lookupTable->value_interpretation_column)
                                                <th>{{ ucfirst($lookupTable->value_interpretation_column) }}</th>
                                            @endif
                                        @else
                                        @foreach($lookupTable->key_columns as $keyColumn)
                                            <th>{{ ucfirst($keyColumn) }}</th>
                                        @endforeach
                                        <th>{{ ucfirst($lookupTable->value_column) }}</th>
                                        @endif
                                        <th>Created</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entries as $entry)
                                        @php
                                            $keys = is_array($entry->keys) ? $entry->keys : json_decode($entry->keys, true);
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            @if($lookupTable->lookup_type === 'range_based')
                                                <td>
                                                    <span class="">
                                                        {{ $keys['low'] ?? 'N/A' }} - {{ $keys['high'] ?? '∞' }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $entry->value }}</strong></td>
                                                @if($lookupTable->value_interpretation_column)
                                                    <td>
                                                        <span class="badge p-2 badge-pill badge-info">{{ $keys['value_interpretation'] ?? '-' }}</span>
                                                    </td>
                                                @endif
                                            @else
                                            @foreach($lookupTable->key_columns as $keyColumn)
                                                <td><code>{{ $keys[$keyColumn] ?? 'N/A' }}</code></td>
                                            @endforeach
                                            <td><strong>{{ $entry->value }}</strong></td>
                                            @endif
                                            <td><small class="text-muted">{{ $entry->created_at->format('M d, Y') }}</small></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditEntryModal(@js($entry->id))"
                                                            class="btn btn-sm btn-outline-primary mr-2" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteEntry(@js($entry->id))"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this entry?')">
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
                            {{ $entries->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-table-edit fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No entries found</h5>
                            <p class="text-muted">Add your first entry to get started.</p>
                            <button wire:click="showCreateEntryModal" class="btn btn-success">
                                <i class="mdi mdi-plus"></i> Add Entry
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Entry Modal -->
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus"></i>
                        Add New Entry
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="createEntry">
                        @if($lookupTable->lookup_type === 'range_based')
                            <!-- Range-based entry form -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rangeLow" class="form-label">Low (Minimum) *</label>
                                        <input type="number" 
                                               step="any"
                                               wire:model="rangeLow" 
                                               class="form-control" 
                                               id="rangeLow" 
                                               required>
                                        @error('rangeLow') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rangeHigh" class="form-label">
                                            High (Maximum) 
                                            @if($isOpenEnded)
                                                <span class="badge badge-warning">Open-ended</span>
                                            @else
                                                *
                                            @endif
                                        </label>
                                        <input type="number" 
                                               step="any"
                                               wire:model="rangeHigh" 
                                               class="form-control" 
                                               id="rangeHigh" 
                                               :disabled="$isOpenEnded"
                                               :required="!$isOpenEnded">
                                        @error('rangeHigh') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           wire:model.live="isOpenEnded" 
                                           class="form-check-input" 
                                           id="isOpenEnded">
                                    <label class="form-check-label" for="isOpenEnded">
                                        Open-ended range (all values from Low and above)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="entryValue" class="form-label">{{ ucfirst($lookupTable->value_column) }} *</label>
                                <input type="text" wire:model="entryValue" class="form-control" id="entryValue" required>
                                @error('entryValue') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($lookupTable->value_interpretation_column)
                                <div class="mb-3">
                                    <label for="valueInterpretation" class="form-label">{{ ucfirst($lookupTable->value_interpretation_column) }}</label>
                                    <input type="text" 
                                           wire:model="valueInterpretation" 
                                           class="form-control" 
                                           id="valueInterpretation"
                                           placeholder="e.g., Excellent, High Risk, Pass">
                                    @error('valueInterpretation') <span class="text-danger">{{ $message }}</span> @enderror
                                    <small class="text-muted">Optional text interpretation of the value</small>
                                </div>
                            @endif
                            
                            <!-- Preview -->
                            <div class="alert alert-info">
                                <strong>Preview:</strong> 
                                @if($isOpenEnded)
                                    Values {{ $rangeLow }}+ → Returns <code>{{ $entryValue ?: 'value' }}</code>
                                @else
                                    Values {{ $rangeLow }} to {{ $rangeHigh }} → Returns <code>{{ $entryValue ?: 'value' }}</code>
                                @endif
                                @if($valueInterpretation)
                                    ({{ $valueInterpretation }})
                                @endif
                            </div>
                        @else
                            <!-- Key-value entry form -->
                            @foreach($lookupTable->key_columns as $keyColumn)
                            <div class="mb-3">
                                <label for="key_{{ $keyColumn }}" class="form-label">{{ ucfirst($keyColumn) }} *</label>
                                <input type="text" 
                                       wire:model="entryKeys.{{ $keyColumn }}" 
                                       class="form-control" 
                                       id="key_{{ $keyColumn }}" 
                                       required>
                                @error('entryKeys.' . $keyColumn) <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                        <div class="mb-3">
                            <label for="entryValue" class="form-label">{{ ucfirst($lookupTable->value_column) }} *</label>
                            <input type="text" wire:model="entryValue" class="form-control" id="entryValue" required>
                            @error('entryValue') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCreateModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="createEntry">Add Entry</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Entry Modal -->
    @if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i>
                        Edit Entry
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="updateEntry">
                        @if($lookupTable->lookup_type === 'range_based')
                            <!-- Range-based entry form -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editRangeLow" class="form-label">Low (Minimum) *</label>
                                        <input type="number" 
                                               step="any"
                                               wire:model="rangeLow" 
                                               class="form-control" 
                                               id="editRangeLow" 
                                               required>
                                        @error('rangeLow') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editRangeHigh" class="form-label">
                                            High (Maximum) 
                                            @if($isOpenEnded)
                                                <span class="badge badge-warning">Open-ended</span>
                                            @else
                                                *
                                            @endif
                                        </label>
                                        <input type="number" 
                                               step="any"
                                               wire:model="rangeHigh" 
                                               class="form-control" 
                                               id="editRangeHigh" 
                                               :disabled="$isOpenEnded"
                                               :required="!$isOpenEnded">
                                        @error('rangeHigh') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           wire:model.live="isOpenEnded" 
                                           class="form-check-input" 
                                           id="editIsOpenEnded">
                                    <label class="form-check-label" for="editIsOpenEnded">
                                        Open-ended range (all values from Low and above)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editEntryValue" class="form-label">{{ ucfirst($lookupTable->value_column) }} *</label>
                                <input type="text" wire:model="entryValue" class="form-control" id="editEntryValue" required>
                                @error('entryValue') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($lookupTable->value_interpretation_column)
                                <div class="mb-3">
                                    <label for="editValueInterpretation" class="form-label">{{ ucfirst($lookupTable->value_interpretation_column) }}</label>
                                    <input type="text" 
                                           wire:model="valueInterpretation" 
                                           class="form-control" 
                                           id="editValueInterpretation"
                                           placeholder="e.g., Excellent, High Risk, Pass">
                                    @error('valueInterpretation') <span class="text-danger">{{ $message }}</span> @enderror
                                    <small class="text-muted">Optional text interpretation of the value</small>
                                </div>
                            @endif
                            
                            <!-- Preview -->
                            <div class="alert alert-info">
                                <strong>Preview:</strong> 
                                @if($isOpenEnded)
                                    Values {{ $rangeLow }}+ → Returns <code>{{ $entryValue ?: 'value' }}</code>
                                @else
                                    Values {{ $rangeLow }} to {{ $rangeHigh }} → Returns <code>{{ $entryValue ?: 'value' }}</code>
                                @endif
                                @if($valueInterpretation)
                                    ({{ $valueInterpretation }})
                                @endif
                            </div>
                        @else
                            <!-- Key-value entry form -->
                            @foreach($lookupTable->key_columns as $keyColumn)
                            <div class="mb-3">
                                <label for="edit_key_{{ $keyColumn }}" class="form-label">{{ ucfirst($keyColumn) }} *</label>
                                <input type="text" 
                                       wire:model="entryKeys.{{ $keyColumn }}" 
                                       class="form-control" 
                                       id="edit_key_{{ $keyColumn }}" 
                                       required>
                                @error('entryKeys.' . $keyColumn) <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                        <div class="mb-3">
                            <label for="editEntryValue" class="form-label">{{ ucfirst($lookupTable->value_column) }} *</label>
                            <input type="text" wire:model="entryValue" class="form-control" id="editEntryValue" required>
                            @error('entryValue') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="updateEntry">Update Entry</button>
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
                        Bulk Import Entries to {{ $lookupTable->name }}
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
                            <button type="button" wire:click="downloadTemplate" class="btn btn-purple" wire:loading.attr="disabled">
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
                                The template includes: <strong>{{ implode(', ', $lookupTable->key_columns) }}</strong> and <strong>{{ $lookupTable->value_column }}</strong>
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
                        <button type="button" wire:click="importData" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="importData">
                                <i class="mdi mdi-upload"></i> Import Data
                            </span>
                            <span wire:loading wire:target="importData">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Importing...
                            </span>
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

