<div class="container-fluid" wire:id="area-manager">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-map text-primary"></i>
                                Areas Management
                            </h2>
                            <p class="text-muted mb-0">Manage areas</p>
                        </div>
                        <div>
                            <button wire:click="openBulkUploadModal" class="btn btn-success me-2" type="button">
                                <i class="mdi mdi-file-excel"></i> Bulk Create
                            </button>
                            <button wire:click="showCreateAreaModal" class="btn btn-primary" type="button" wire:loading.attr="disabled" wire:target="showCreateAreaModal">
                                <span wire:loading.remove wire:target="showCreateAreaModal">
                                    <i class="mdi mdi-plus"></i> Add Area
                                </span>
                                <span wire:loading wire:target="showCreateAreaModal">
                                    <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                </span>
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search areas...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">From Date</label>
                                <input type="date" wire:model.live="dateFrom" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">To Date</label>
                                <input type="date" wire:model.live="dateTo" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-refresh"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Areas Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->areas->count() > 0)
                        <!-- Bulk Actions -->
                        @if(count($selectedAreas) > 0)
                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                                <span>
                                    <i class="mdi mdi-information"></i>
                                    {{ count($selectedAreas) }} area(s) selected
                                </span>
                                <div class="btn-group">
                                    <button wire:click="bulkDelete" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to delete selected areas?')">
                                        <i class="mdi mdi-delete"></i> Delete
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->areas->firstItem() ?? 0 }} to {{ $this->areas->lastItem() ?? 0 }} of {{ $this->areas->total() }} entries
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
                            <table class="table table-striped table-hover livewire-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>  
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->areas as $area)
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $area->code }}</span>
                                            </td>
                                            <td>{{ $area->name }}</td>
                                            <td>{{ $area->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $area->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditAreaModal({{ $area->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit"
                                                            wire:loading.attr="disabled"
                                                            wire:target="showEditAreaModal({{ $area->id }})">
                                                        <span wire:loading.remove wire:target="showEditAreaModal({{ $area->id }})">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </span>
                                                        <span wire:loading wire:target="showEditAreaModal({{ $area->id }})">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                                        </span>
                                                    </button>
                                                    <button wire:click="deleteArea({{ $area->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            title="Delete"
                                                            wire:loading.attr="disabled"
                                                            wire:target="deleteArea({{ $area->id }})"
                                                            onclick="return confirm('Are you sure you want to delete this area?')">
                                                        <span wire:loading.remove wire:target="deleteArea({{ $area->id }})">
                                                            <i class="mdi mdi-delete"></i>
                                                        </span>
                                                        <span wire:loading wire:target="deleteArea({{ $area->id }})">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                                        </span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->areas->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-map text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No areas found</h5>
                            <p class="text-muted">Start by adding your first area.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Area Modal -->
    @if($showAreaModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingArea ? 'pencil' : 'plus' }}"></i>
                            {{ $editingArea ? 'Edit' : 'Create' }} Area
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeAreaModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveArea">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="areaForm.code" class="form-control" placeholder="Area code...">
                                @error('areaForm.code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="areaForm.name" class="form-control" placeholder="Area name...">
                                @error('areaForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAreaModal" wire:loading.attr="disabled" wire:target="saveArea">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveArea" wire:loading.attr="disabled" wire:target="saveArea">
                            <span wire:loading.remove wire:target="saveArea">
                                <i class="mdi mdi-content-save"></i> {{ $editingArea ? 'Update' : 'Create' }} Area
                            </span>
                            <span wire:loading wire:target="saveArea">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Saving data...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Upload Modal -->
    @if($showBulkUploadModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-excel"></i>
                            Bulk Create Areas
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeBulkUploadModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>Instructions:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Download the Excel template below</li>
                                <li>Fill in the Code and Name columns</li>
                                <li>Upload the completed file</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <button wire:click="downloadTemplate" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-download"></i> Download Excel Template
                            </button>
                        </div>

                        <hr>

                        <form wire:submit.prevent="processBulkUpload">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Upload Excel File <span class="text-danger">*</span></label>
                                <input type="file" wire:model="bulkFile" class="form-control" accept=".xlsx,.xls,.csv">
                                @error('bulkFile') <span class="text-danger">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="bulkFile" class="mt-2">
                                    <small class="text-muted">
                                        <i class="mdi mdi-loading mdi-spin"></i> Uploading file...
                                    </small>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <small>
                                    <strong>Note:</strong> Duplicate codes will be skipped. Maximum file size: 2MB. Supported formats: .xlsx, .xls, .csv
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkUploadModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="processBulkUpload" 
                                wire:loading.attr="disabled" wire:target="processBulkUpload">
                            <span wire:loading.remove wire:target="processBulkUpload">
                                <i class="mdi mdi-upload"></i> Upload & Process
                            </span>
                            <span wire:loading wire:target="processBulkUpload">
                                <i class="mdi mdi-loading mdi-spin"></i> Processing...
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
    
    /* Make modal body scrollable */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
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
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            console.log('Livewire initialized for Areas Manager');
            
            Livewire.on('bulk-upload-modal-opened', () => {
                console.log('Bulk upload modal event received!');
            });
        });
    </script>
</div>
