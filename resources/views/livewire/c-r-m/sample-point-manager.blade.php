<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-map-marker text-primary"></i>
                                Sample Points Management
                            </h2>
                            <p class="text-muted mb-0">Manage sample points</p>
                        </div>
                        
                        <div>
                            <button wire:click="openBulkUploadModal" class="btn btn-success me-2" type="button" style="border-radius: 8px;">
                                <i class="mdi mdi-file-excel"></i> Bulk Create
                            </button>
                            <button wire:click="showCreateSamplePointModal" class="btn btn-primary" type="button" style="border-radius: 8px;" wire:loading.attr="disabled" wire:target="showCreateSamplePointModal">
                                <span wire:loading.remove wire:target="showCreateSamplePointModal">
                                    <i class="mdi mdi-plus"></i> Add Sample Point
                                </span>
                                <span wire:loading wire:target="showCreateSamplePointModal">
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
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search sample points...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Sample Type</label>
                                <select wire:model.live="sampleTypeFilter" class="form-select">
                                    <option value="">All Sample Types</option>
                                    @foreach($this->sampleTypes as $sampleType)
                                        <option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
                                    @endforeach
                                </select>
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

    <!-- Sample Points Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->samplePoints->count() > 0)
                        <!-- Bulk Actions -->
                        
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-3" wire:key="bulk-actions-{{ count($selectedSamplePoints) }}">
                            <span>
                                <i class="mdi mdi-information"></i>
                                {{ count($selectedSamplePoints) }} sample point(s) selected
                            </span>
                            <div class="btn-group" role="group">
                                <button wire:click="showAssignSampleTypeModalInitiator" 
                                        class="btn btn-success btn-sm mr-2" 
                                        type="button"
                                        style="border-radius: 8px;"
                                        wire:loading.attr="disabled" 
                                        wire:target="showAssignSampleTypeModalInitiator">
                                    <span wire:loading.remove wire:target="showAssignSampleTypeModalInitiator">
                                        <i class="mdi mdi-tag-multiple"></i> Assign Sample Type
                                    </span>
                                    <span wire:loading wire:target="showAssignSampleTypeModalInitiator">
                                        <span class="spinner-border spinner-border-sm" role="status"></span> Opening...
                                    </span>
                                </button>
                                <button wire:click="bulkDelete" class="btn btn-danger btn-sm" type="button"
                                        style="border-radius: 8px;"
                                        onclick="return confirm('Are you sure you want to delete selected sample points?')">
                                    <i class="mdi mdi-delete"></i> Delete
                                </button>
                            </div>
                        </div>
                        

                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->samplePoints->firstItem() ?? 0 }} to {{ $this->samplePoints->lastItem() ?? 0 }} of {{ $this->samplePoints->total() }} entries
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
                                        <th style="width: 50px; text-align: center; vertical-align: middle;">
                                            <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                                        </th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Sample Types</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->samplePoints as $samplePoint)
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <input type="checkbox" wire:model.live="selectedSamplePoints" value="{{ $samplePoint->id }}" class="form-check-input">
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $samplePoint->code }}</span>
                                            </td>
                                            <td>{{ $samplePoint->name }}</td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $this->getSampleTypesForSamplePoint($samplePoint->id) ?: '—' }}
                                                </span>
                                            </td>
                                            <td>{{ $samplePoint->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $samplePoint->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditSamplePointModal({{ $samplePoint->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            style="border-radius: 8px;"
                                                            title="Edit"
                                                            wire:loading.attr="disabled"
                                                            wire:target="showEditSamplePointModal({{ $samplePoint->id }})">
                                                        <span wire:loading.remove wire:target="showEditSamplePointModal({{ $samplePoint->id }})">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </span>
                                                        <span wire:loading wire:target="showEditSamplePointModal({{ $samplePoint->id }})">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                                        </span>
                                                    </button>
                                                    <button wire:click="deleteSamplePoint({{ $samplePoint->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            style="border-radius: 8px;"
                                                            title="Delete"
                                                            wire:loading.attr="disabled"
                                                            wire:target="deleteSamplePoint({{ $samplePoint->id }})"
                                                            onclick="return confirm('Are you sure you want to delete this sample point?')">
                                                        <span wire:loading.remove wire:target="deleteSamplePoint({{ $samplePoint->id }})">
                                                            <i class="mdi mdi-delete"></i>
                                                        </span>
                                                        <span wire:loading wire:target="deleteSamplePoint({{ $samplePoint->id }})">
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
                            {{ $this->samplePoints->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-map-marker text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No sample points found</h5>
                            <p class="text-muted">Start by adding your first sample point.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Point Modal -->
    @if($showSamplePointModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingSamplePoint ? 'pencil' : 'plus' }}"></i>
                            {{ $editingSamplePoint ? 'Edit' : 'Create' }} Sample Point
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSamplePointModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveSamplePoint">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="samplePointForm.code" class="form-control" placeholder="Sample point code...">
                                @error('samplePointForm.code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="samplePointForm.name" class="form-control" placeholder="Sample point name...">
                                @error('samplePointForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeSamplePointModal" wire:loading.attr="disabled" wire:target="saveSamplePoint">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveSamplePoint" wire:loading.attr="disabled" wire:target="saveSamplePoint">
                            <span wire:loading.remove wire:target="saveSamplePoint">
                                <i class="mdi mdi-content-save"></i> {{ $editingSamplePoint ? 'Update' : 'Create' }} Sample Point
                            </span>
                            <span wire:loading wire:target="saveSamplePoint">
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
                            Bulk Create Sample Points
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

    <!-- Assign Sample Type Modal -->
    @if($showAssignSampleTypeModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-tag-multiple"></i>
                            Assign Sample Types
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeAssignSampleTypeModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 85vh; overflow-y: auto;">
                        <!-- Selected Sample Points -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="mdi mdi-map-marker text-primary"></i> Selected Sample Points ({{ count($selectedSamplePoints) }})
                            </h6>
                            <div class="card">
                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                    @foreach($selectedSamplePoints as $samplePointId)
                                        @php
                                            $samplePoint = \App\Models\SamplePoint::find($samplePointId);
                                        @endphp
                                        @if($samplePoint)
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                                <span><strong>{{ $samplePoint->code }}</strong> - {{ $samplePoint->name }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Sample Types Multi-Select -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="mdi mdi-tag-multiple text-success"></i> Select Sample Types <span class="text-danger">*</span>
                            </label>
                            <div class="searchable-dropdown-wrapper" wire:key="sample-type-dropdown">
                                <div class="multi-select-container" wire:click="toggleSampleTypeDropdown">
                                    <input 
                                        type="text" 
                                        wire:model.live="sampleTypeSearch"
                                        placeholder="@if(count($selectedSampleTypes) > 0){{ $this->getSelectedSampleTypeNames() }}@else Select sample types...@endif"
                                        class="form-control searchable-input-single"
                                        autocomplete="off"
                                        wire:click.stop
                                    >
                                    @if(count($selectedSampleTypes) > 0)
                                        <span class="selected-count">{{ count($selectedSampleTypes) }}</span>
                                    @endif
                                    <i class="mdi mdi-chevron-down dropdown-arrow @if($sampleTypeDropdownOpen) rotated @endif"></i>
                                </div>

                                @if($sampleTypeDropdownOpen)
                                    <div class="dropdown-list">
                                        @if($this->filteredSampleTypes->count() > 0)
                                            <div class="options-list">
                                                @foreach($this->filteredSampleTypes as $st)
                                                    <div wire:click="toggleSampleType({{ $st->id }})" 
                                                         class="option-item @if($this->isSampleTypeSelected($st->id)) selected @endif"
                                                         style="cursor: pointer;">
                                                        @if($this->isSampleTypeSelected($st->id))
                                                            <i class="mdi mdi-check-circle text-primary"></i>
                                                        @endif
                                                        <div class="d-flex flex-column flex-grow-1">
                                                            <span class="fw-bold">{{ $st->name }}</span>
                                                            <small class="text-muted">Code: {{ $st->code }}</small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="no-results">
                                                <i class="mdi mdi-alert-circle-outline"></i>
                                                <span>No sample types found</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <small class="form-text text-muted mt-2 d-block">
                                <i class="mdi mdi-information-outline"></i> Select one or more sample types to assign to the selected sample points
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAssignSampleTypeModal" wire:loading.attr="disabled" wire:target="assignSampleTypes">
                            <i class="mdi mdi-close"></i> Close
                        </button>
                        <button type="button" class="btn btn-success" wire:click="assignSampleTypes" wire:loading.attr="disabled" wire:target="assignSampleTypes">
                            <span wire:loading.remove wire:target="assignSampleTypes">
                                <i class="mdi mdi-check"></i> Yes, Assign
                            </span>
                            <span wire:loading wire:target="assignSampleTypes">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Assigning...
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
    
    /* Ensure modal is properly positioned */
    .modal.fade.show {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1050 !important;
        overflow-y: auto !important;
    }
    
    /* Make modal body scrollable */
    .modal-body {
        max-height: 85vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Specific styling for Assign Sample Type Modal */
    .modal-dialog.modal-lg .modal-body {
        max-height: 85vh;
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
    
    /* Searchable Dropdown Styling */
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
    
    .single-select-container, .multi-select-container {
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
    
    .single-select-container:hover, .multi-select-container:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    }
    
    .single-select-container:has(.searchable-input-single:focus), 
    .multi-select-container:has(.searchable-input-single:focus) {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .options-list {
        padding: 8px;
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .options-list::-webkit-scrollbar {
        display: none;
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
    
    .dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 12px;
        margin-top: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 350px;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .dropdown-list::-webkit-scrollbar {
        display: none;
    }
    
    .searchable-dropdown-wrapper {
        position: relative;
    }
    
    .no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }
    
    .no-results i {
        font-size: 24px;
        display: block;
        margin-bottom: 8px;
    }
    
    .dropdown-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        transition: transform 0.3s ease;
        pointer-events: none;
        font-size: 20px;
        color: #6c757d;
    }
    
    .dropdown-arrow.rotated {
        transform: translateY(-50%) rotate(180deg);
    }
    
    .selected-count {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        background: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            console.log('Livewire initialized for Sample Points Manager');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.searchable-dropdown-wrapper')) {
                Livewire.all().forEach(component => {
                    if (component.get && typeof component.get('sampleTypeDropdownOpen') !== 'undefined') {
                        component.set('sampleTypeDropdownOpen', false);
                    }
                });
            }
        });
    </script>
</div>
