<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-table-edit text-info"></i>
                                Manage Entries: {{ $lookupTable->name }}
                            </h2>
                            <p class="text-muted mb-0">{{ $lookupTable->description }}</p>
                            <div class="mt-2">
                                <span class="badge badge-secondary me-1">Keys: {{ implode(', ', $lookupTable->key_columns) }}</span>
                                <span class="badge badge-primary">Value: {{ $lookupTable->value_column }}</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('formulars.lookup-tables') }}" class="btn btn-outline-secondary me-2">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                            <button wire:click="showCreateEntryModal" class="btn btn-success">
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
                                        @foreach($lookupTable->key_columns as $keyColumn)
                                            <th>{{ ucfirst($keyColumn) }}</th>
                                        @endforeach
                                        <th>{{ ucfirst($lookupTable->value_column) }}</th>
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
                                            <td>{{ $entry->id }}</td>
                                            @foreach($lookupTable->key_columns as $keyColumn)
                                                <td><code>{{ $keys[$keyColumn] ?? 'N/A' }}</code></td>
                                            @endforeach
                                            <td><strong>{{ $entry->value }}</strong></td>
                                            <td><small class="text-muted">{{ $entry->created_at->format('M d, Y') }}</small></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditEntryModal({{ $entry->id }})" 
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteEntry({{ $entry->id }})" 
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

<style>
.modal.show {
    display: block !important;
}
</style>

