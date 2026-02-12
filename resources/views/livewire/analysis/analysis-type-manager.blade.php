<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-test-tube text-primary"></i>
                                Analysis Types Management
                            </h2>
                            <p class="text-muted mb-0">Manage analysis types and their elements</p>
                        </div>
                        <button wire:click="showCreateAnalysisTypeModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Analysis Type
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
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search analysis types...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Lab</label>
                                <select wire:model.live="labFilter" class="form-select modern-select">
                                    <option value="">All Labs</option>
                                    @foreach($labs as $lab)
                                        <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
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

    <!-- Analysis Types Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Analysis Types</h5>
                </div>
                <div class="card-body">
                    @if($this->analysisTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Actions</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Procedure</th>
                                        <th>Lab Section</th>
                                        <th>Elements</th>
                                        <th>Level</th>
                                        <th>Calculations</th>
                                        <th>No Result</th>
                                        <th>Reporting Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->analysisTypes as $analysisType)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('livewire.elements', ['analysisTypeId' => $analysisType->id]) }}" 
                                                       class="btn btn-sm btn-outline-primary mr-1" 
                                                       title="View Elements">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="showEditAnalysisTypeModal({{ $analysisType->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteAnalysisType({{ $analysisType->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this analysis type? This will also delete all associated elements.')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="">{{ $analysisType->code }}</span>
                                                
                                            </td>
                                            <td>
                                                <strong>{{ $analysisType->name }}</strong>
                                                @if($analysisType->description)
                                                    <br><small class="text-muted">{{ $analysisType->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($analysisType->procedureWorksheet)
                                                    <span class="badge bg-light text-dark p-2 border">{{ $analysisType->procedureWorksheet->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $analysisType->labsectionname ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info p-2" style="color: white;">{{ $analysisType->analysis_elements->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary p-2" style="color: white;">{{ $analysisType->level }}</span>
                                            </td>
                                            <td>
                                                @if($analysisType->include_hygiene_score)
                                                    <span class="badge bg-info p-2" style="color: white;">Hygiene Score</span>
                                                @endif
                                                @if($analysisType->include_sanitizer_efficiency)
                                                    <span class="badge bg-info p-2" style="color: white;">Sanitizer Efficiency</span>
                                                @endif
                                                @if(!$analysisType->include_hygiene_score && !$analysisType->include_sanitizer_efficiency)
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($analysisType->has_no_result)
                                                    <span class="badge bg-warning p-2" style="color: black;">Yes</span>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($analysisType->reporting_time)
                                                    <span class="badge bg-info p-2" style="color: white;">{{ $analysisType->reporting_time }}d</span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge p-2 bg-{{ $analysisType->active ? 'success' : 'danger' }}">
                                                    {{ $analysisType->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->analysisTypes->firstItem() ?? 0 }} to {{ $this->analysisTypes->lastItem() ?? 0 }} of {{ $this->analysisTypes->total() }} entries
                                </span>
                                <div class="d-flex align-items-center">
                                    <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                    <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                        @foreach($perPageOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                {{ $this->analysisTypes->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-test-tube text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No analysis types found</h5>
                            <p class="text-muted">Create your first analysis type to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Type Modal -->
    @if($showAnalysisTypeModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingAnalysisType ? 'pencil' : 'plus' }}"></i>
                            {{ $editingAnalysisType ? 'Edit' : 'Create' }} Analysis Type
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeAnalysisTypeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveAnalysisType">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="analysisTypeForm.name" class="form-control @error('analysisTypeForm.name') is-invalid @enderror">
                                        @error('analysisTypeForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="analysisTypeForm.code" class="form-control @error('analysisTypeForm.code') is-invalid @enderror">
                                        @error('analysisTypeForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="analysisTypeForm.description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-layers text-primary"></i> Lab Section <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showLabSectionDropdown', true)" wire:click.outside="$set('showLabSectionDropdown', false)">
                                            <div class="tag-select-input">
                                                <!-- Display selected lab section or allow searching -->
                                                @if($this->selectedLabSection)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedLabSection->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('analysisTypeForm.lab_section_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Search Input -->
                                                <input type="text" 
                                                       wire:model.live="labSectionSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedLabSection ? '' : 'Search lab sections...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showLabSectionDropdown && count($this->filteredLabSections) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredLabSections as $labSection)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLabSection({{ $labSection->id }})">
                                                            {{ $labSection->code }} - {{ $labSection->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('analysisTypeForm.lab_section_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-flask text-primary"></i> Lab (Auto-populated)</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $this->selectedLab ? $this->selectedLab->name : '' }}" 
                                               readonly 
                                               style="background-color: #f8f9fa; cursor: not-allowed;"
                                               placeholder="Select lab section first">
                                        @error('analysisTypeForm.lab_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Level</label>
                                        <input type="number" wire:model="analysisTypeForm.level" class="form-control" min="1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Reporting Time (days)</label>
                                        <input type="number" wire:model="analysisTypeForm.reporting_time" class="form-control" min="0" placeholder="e.g., 7">
                                        <small class="form-text text-muted">Expected time to complete analysis in days</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoicable Item Selection -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-currency-usd text-success"></i> Invoicable Item
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showInvoicableItemDropdown', true)" wire:click.outside="$set('showInvoicableItemDropdown', false)">
                                            <div class="tag-select-input">
                                                @if($this->selectedInvoicableItem)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedInvoicableItem->item_code }} - {{ $this->selectedInvoicableItem->item_name }} ({{ number_format($this->selectedInvoicableItem->unit_price, 2) }})
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('analysisTypeForm.invoicable_item_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <input type="text" 
                                                       wire:model.live="invoicableItemSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedInvoicableItem ? '' : 'Search invoicable items...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            @if($showInvoicableItemDropdown && count($this->filteredInvoicableItems) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredInvoicableItems as $item)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectInvoicableItem({{ $item->id }})">
                                                            <strong>{{ $item->item_code }}</strong> - {{ $item->item_name }}
                                                            <span class="badge bg-success float-end">{{ number_format($item->unit_price, 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">Select the invoicable item for billing this analysis type</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Analysis Options Section -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-cog"></i> Analysis Options
                                    </h6>
                                    <small class="text-muted">Configure analysis type settings and features</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="analysisTypeForm.active" class="form-check-input" id="analysis_active" role="switch">
                                                <label class="form-check-label" for="analysis_active">
                                                    <i class="mdi mdi-check-circle text-success"></i> Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check form-switch">
                                    <input type="checkbox" wire:model.live="analysisTypeForm.has_no_result" class="form-check-input" id="has_no_result" role="switch">
                                                <label class="form-check-label" for="has_no_result">
                                                    <i class="mdi mdi-flask-empty-off-outline text-warning"></i> Has No Result Captured
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <!-- Conditional Procedure Worksheet Dropdown -->
                                        @if($analysisTypeForm['has_no_result'] ?? false)
                                            <div class="col-md-12 mb-2">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">
                                                        <i class="mdi mdi-file-document-outline text-primary"></i> Procedure Worksheet <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="tag-select-container" wire:click="$set('showProcedureWorksheetDropdown', true)" wire:click.outside="$set('showProcedureWorksheetDropdown', false)">
                                                        <div class="tag-select-input">
                                                            @if($this->selectedProcedureWorksheet)
                                                                <span class="tag-badge">
                                                                    {{ $this->selectedProcedureWorksheet->name }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('analysisTypeForm.procedure_worksheet_id', null)"></i>
                                                                </span>
                                                            @endif
                                                            
                                                            <input type="text" 
                                                                   wire:model.live="procedureWorksheetSearch" 
                                                                   class="tag-input" 
                                                                   placeholder="{{ $this->selectedProcedureWorksheet ? '' : 'Search procedure worksheets...' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        
                                                        @if($showProcedureWorksheetDropdown && count($this->filteredProcedureWorksheets) > 0)
                                                            <div class="tag-dropdown">
                                                                @foreach($this->filteredProcedureWorksheets as $worksheet)
                                                                    <div class="tag-dropdown-item" wire:click.stop="selectProcedureWorksheet({{ $worksheet->id }})">
                                                                        <strong>{{ $worksheet->name }}</strong>
                                                                        @if($worksheet->description)
                                                                            <br><small class="text-muted">{{ Str::limit($worksheet->description, 50) }}</small>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <small class="form-text text-muted">Select the procedure worksheet for this analysis type</small>
                                                    @error('analysisTypeForm.procedure_worksheet_id') <span class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="analysisTypeForm.include_hygiene_score" class="form-check-input" id="include_hygiene_score" role="switch">
                                                <label class="form-check-label" for="include_hygiene_score">
                                                    <i class="mdi mdi-bacteria text-info"></i> Include Hygiene Score
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="analysisTypeForm.include_sanitizer_efficiency" class="form-check-input" id="include_sanitizer_efficiency" role="switch">
                                                <label class="form-check-label" for="include_sanitizer_efficiency">
                                                    <i class="mdi mdi-spray text-info"></i> Include Sanitizer Efficiency
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAnalysisTypeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveAnalysisType">
                            <i class="mdi mdi-content-save"></i> Save
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
    .modern-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background-color: #ffffff;
        outline: none;
    }
    
    .modern-select:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
    }
    
    .modern-select option {
        padding: 10px 16px;
        font-weight: 500;
        color: #495057;
    }
    
    .modern-select option:hover {
        background-color: #f8f9fa;
    }
    
    /* Custom dropdown arrow */
    .modern-select {
        background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: left center, right 12px center;
        background-repeat: no-repeat, no-repeat;
        background-size: 100% 100%, 16px 16px;
        padding-right: 40px;
    }
    
    /* Invalid state styling */
    .modern-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .modern-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
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
    
    /* Tag-based Select Styling */
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
    </style>
    
</div>
