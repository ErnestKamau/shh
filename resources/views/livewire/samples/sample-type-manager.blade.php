<div class="container-fluid lab-surface-theme ls-admin-page ls-admin-page--size-only" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                Sample Types Management
                            </h2>
                            <p class="text-muted mb-0">Manage sample types, analysis types, and analysis elements</p>
                        </div>
                        <button wire:click="showCreateSampleTypeModal" class="btn btn-outline-primary sampletype-action-btn">
                            <i class="mdi mdi-plus"></i> Add Sample Type
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
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search sample types...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Types Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Sample Types</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->sampleTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover sampletype-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Actions</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Analysis Types</th>
                                        <th>Has Attachable Result</th>
                                        <th>Exhibit Returned On Reception</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->sampleTypes as $sampleType)
                                        <tr>
                                            <td>
                                                <div class="d-flex sampletype-actions-cell">
                                                    <a href="{{ route('livewire.analysis-types', ['sampleTypeId' => $sampleType->id]) }}" 
                                                       class="rm-act-btn rm-act-btn--view" 
                                                       title="View Analysis Types">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                            wire:click="showEditSampleTypeModal(@js($sampleType->id))" 
                                                            class="rm-act-btn rm-act-btn--edit" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            wire:click="cloneSampleType(@js($sampleType->id))" 
                                                            class="rm-act-btn rm-act-btn--clone" 
                                                            title="Clone"
                                                            onclick="return confirm('Are you sure you want to clone this sample type?')">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </button>
                                                    <button type="button"
                                                            wire:click="deleteSampleType(@js($sampleType->id))" 
                                                            class="rm-act-btn rm-act-btn--delete" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this sample type? This will also delete all associated analysis types and elements.')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="sampletype-code">{{ $sampleType->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $sampleType->name }}</strong>
                                                @if($sampleType->description)
                                                    <br><small class="text-muted">{{ $sampleType->description }}</small>
                                                @endif
                                                @if($sampleType->ratingHeader)
                                                    <br><small class="text-primary"><i class="mdi mdi-star"></i> {{ $sampleType->ratingHeader->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info" style="color: white;">{{ $sampleType->analysis_types->count() }}</span>
                                            </td>
                                            <td>
                                                @if($sampleType->is_results_attachable)
                                                    <span class="badge bg-warning p-2" style="color: black;">Yes</span>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sampleType->exhibit_returned_on_reception)
                                                    <span class="badge bg-info p-2" style="color: white;">Yes</span>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge p-2 bg-{{ $sampleType->active ? 'success' : 'danger' }}" style="color: white;">
                                                    {{ $sampleType->active ? 'Active' : 'Inactive' }}
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
                                    Showing {{ $this->sampleTypes->firstItem() ?? 0 }} to {{ $this->sampleTypes->lastItem() ?? 0 }} of {{ $this->sampleTypes->total() }} entries
                                </span>
                            </div>
                            <div>
                                {{ $this->sampleTypes->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No sample types found</h5>
                            <p class="text-muted">Create your first sample type to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Type Modal -->
    @if($showSampleTypeModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingSampleType ? 'pencil' : 'plus' }}"></i>
                            {{ $editingSampleType ? 'Edit' : 'Create' }} Sample Type
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSampleTypeModal"></button>
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
                        
                        <form wire:submit.prevent="saveSampleType">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="sampleTypeForm.name" class="form-control @error('sampleTypeForm.name') is-invalid @enderror">
                                        @error('sampleTypeForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="sampleTypeForm.code" class="form-control @error('sampleTypeForm.code') is-invalid @enderror">
                                        @error('sampleTypeForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="sampleTypeForm.description" class="form-control @error('sampleTypeForm.description') is-invalid @enderror" rows="3"></textarea>
                                @error('sampleTypeForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-delete-clock text-warning"></i> Retention Days
                                        </label>
                                        <input type="number" 
                                               wire:model="sampleTypeForm.disposal_count" 
                                               class="form-control" 
                                               placeholder="Enter number of days"
                                               min="0">
                                        <small class="form-text text-muted">Number of days to retain the sample</small>
                                        @error('sampleTypeForm.disposal_count') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Sample Type Options Section -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-cog"></i> Sample Type Options
                                    </h6>
                                    <small class="text-muted">Configure sample type settings and features</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="sampleTypeForm.active" class="form-check-input" id="active" role="switch">
                                                <label class="form-check-label" for="active">
                                                    <i class="mdi mdi-check-circle text-success"></i> Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="sampleTypeForm.is_results_attachable" class="form-check-input" id="is_results_attachable" role="switch">
                                                <label class="form-check-label" for="is_results_attachable">
                                                    <i class="mdi mdi-flask-empty-off-outline text-warning"></i> Results Attachable
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="sampleTypeForm.exhibit_returned_on_reception" class="form-check-input" id="exhibit_returned_on_reception" role="switch">
                                                <label class="form-check-label" for="exhibit_returned_on_reception">
                                                    <i class="mdi mdi-package-variant-closed text-info"></i> Exhibit Returned On Reception
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary sampletype-action-btn" wire:click="closeSampleTypeModal">Cancel</button>
                        <button type="button" class="btn btn-outline-primary sampletype-action-btn" wire:click="saveSampleType">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-flask text-primary"></i> Lab <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showLabDropdown', true)" wire:click.outside="$set('showLabDropdown', false)">
                                            <div class="tag-select-input">
                                                <!-- Display selected lab -->
                                                @if($this->selectedLab)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedLab->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('analysisTypeForm.lab_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Search Input -->
                                                <input type="text" 
                                                       wire:model.live="labSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedLab ? '' : 'Search labs...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showLabDropdown && count($this->filteredLabs) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredLabs as $lab)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLab(@js($lab->id))">
                                                            {{ $lab->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('analysisTypeForm.lab_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Level</label>
                                        <input type="number" wire:model="analysisTypeForm.level" class="form-control" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="analysisTypeForm.active" class="form-check-input" id="analysis_active">
                                    <label class="form-check-label" for="analysis_active">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary sampletype-action-btn" wire:click="closeAnalysisTypeModal">Cancel</button>
                        <button type="button" class="btn btn-outline-primary sampletype-action-btn" wire:click="saveAnalysisType">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Category Modal -->
    @if($showCategoryModal ?? false)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus-circle text-success"></i>
                            Create New Category
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCategoryModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCategory">
                            <div class="form-group mb-3">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       wire:model="categoryForm.sample_type_category" 
                                       class="form-control @error('categoryForm.sample_type_category') is-invalid @enderror"
                                       placeholder="Enter category name"
                                       autofocus>
                                @error('categoryForm.sample_type_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="categoryForm.active" class="form-check-input" id="category_active" checked>
                                    <label class="form-check-label" for="category_active">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary sampletype-action-btn" wire:click="closeCategoryModal">Cancel</button>
                        <button type="button" class="btn btn-outline-success sampletype-action-btn" wire:click="saveCategory">
                            <i class="mdi mdi-content-save"></i> Create Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Product Modal -->
    @if($showProductModal ?? false)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus-circle text-success"></i>
                            Create New Product
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeProductModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveProduct">
                            <div class="form-group mb-3">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       wire:model="productForm.name" 
                                       class="form-control @error('productForm.name') is-invalid @enderror"
                                       placeholder="Enter product name"
                                       autofocus>
                                @error('productForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="productForm.active" class="form-check-input" id="product_active" checked>
                                    <label class="form-check-label" for="product_active">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary sampletype-action-btn" wire:click="closeProductModal">Cancel</button>
                        <button type="button" class="btn btn-outline-success sampletype-action-btn" wire:click="saveProduct">
                            <i class="mdi mdi-content-save"></i> Create Product
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
    
    /* Tag-select density from layouts.partials.tag-select-styles */
    
    .tag-dropdown-create {
        background-color: #f8fafc;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .tag-dropdown-create:hover {
        background-color: #f1f5f9;
    }
    
    .tag-dropdown-divider {
        height: 1px;
        background-color: #e2e8f0;
        margin: 4px 0;
    }

    .sampletype-action-btn {
        border-radius: var(--ls-radius-sm, 6px);
        min-height: var(--ls-btn-h, 34px);
        font-weight: 600;
        padding-left: 16px;
        padding-right: 16px;
    }

    .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
        border: 1px solid transparent;
        background: #fff;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .rm-act-btn:last-child {
        margin-right: 0;
    }

    .rm-act-btn--view {
        border-color: #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    .rm-act-btn--edit {
        border-color: #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
        color: #1e40af;
    }

    .rm-act-btn--clone {
        border-color: #c4b5fd;
        color: #6d28d9;
        background: #f5f3ff;
    }

    .rm-act-btn--clone:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #5b21b6;
    }

    .rm-act-btn--delete {
        border-color: #fecaca;
        color: #991b1b;
        background: #fee2e2;
    }

    .rm-act-btn--delete:hover {
        background: #fecaca;
        border-color: #fca5a5;
        color: #7f1d1d;
    }

    .sampletype-table {
        width: 100%;
        min-width: 1120px;
    }

    .sampletype-table th,
    .sampletype-table td {
        vertical-align: middle;
    }

    .sampletype-table th {
        white-space: normal;
        line-height: 1.25;
        font-size: 0.92rem;
    }

    .sampletype-table th:first-child,
    .sampletype-table td:first-child {
        min-width: 170px;
    }

    .sampletype-table th:nth-child(2),
    .sampletype-table td:nth-child(2) {
        min-width: 120px;
        white-space: nowrap;
    }

    .sampletype-table th:nth-child(3),
    .sampletype-table td:nth-child(3) {
        min-width: 260px;
    }

    .sampletype-table th:nth-child(4),
    .sampletype-table td:nth-child(4),
    .sampletype-table th:nth-child(7),
    .sampletype-table td:nth-child(7) {
        min-width: 120px;
        white-space: nowrap;
    }

    .sampletype-table th:nth-child(5),
    .sampletype-table td:nth-child(5),
    .sampletype-table th:nth-child(6),
    .sampletype-table td:nth-child(6) {
        min-width: 180px;
    }

    .sampletype-actions-cell {
        gap: 0.45rem;
        flex-wrap: nowrap;
    }

    .sampletype-code {
        display: inline-block;
        font-weight: 600;
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {
        .sampletype-table {
            min-width: 1120px;
        }
    }
    </style>
    
</div>