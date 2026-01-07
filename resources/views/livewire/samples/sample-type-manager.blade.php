<div class="container-fluid">
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
                        <button wire:click="showCreateSampleTypeModal" class="btn btn-primary">
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
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search sample types...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select wire:model.live="categoryFilter" class="form-select modern-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->sample_type_category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="1" selected>Active</option>
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
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Report Format</th>
                                        <th>Lab Sections</th>
                                        <th>Analysis Types</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->sampleTypes as $sampleType)
                                        <tr>
                                            <td>
                                                <span class="">{{ $sampleType->code }}</span>
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
                                                @php
                                                    $category = $categories->firstWhere('id', $sampleType->sample_type_category);
                                                @endphp
                                                {{ $category ? $category->sample_type_category : 'N/A' }}
                                            </td>
                                            <td>
                                                @if($sampleType->reportFormat)
                                                    <span class="badge bg-primary p-2" style="color: white;">{{ $sampleType->reportFormat->report_name }}</span>
                                                @else
                                                    <span class="text-muted">No Format</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($sampleType->sampleAnalysisStages as $stage)
                                                    <span class="badge bg-secondary mb-1" style="color: white;">{{ $stage->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="badge bg-info" style="color: white;">{{ $sampleType->analysis_types->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge p-2 bg-{{ $sampleType->active ? 'success' : 'danger' }}" style="color: white;">
                                                    {{ $sampleType->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('livewire.analysis-types', ['sampleTypeId' => $sampleType->id]) }}" 
                                                       class="btn btn-sm btn-outline-primary mr-1" 
                                                       title="View Analysis Types">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="showEditSampleTypeModal({{ $sampleType->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="cloneSampleType({{ $sampleType->id }})" 
                                                            class="btn btn-sm btn-outline-info mr-1" 
                                                            title="Clone"
                                                            onclick="return confirm('Are you sure you want to clone this sample type?')">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </button>
                                                    <button wire:click="deleteSampleType({{ $sampleType->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this sample type? This will also delete all associated analysis types and elements.')">
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
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->sampleTypes->firstItem() ?? 0 }} to {{ $this->sampleTypes->lastItem() ?? 0 }} of {{ $this->sampleTypes->total() }} entries
                                </span>
                            </div>
                            <div>
                                {{ $this->sampleTypes->links('pagination::bootstrap-4') }}
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
                                            <i class="mdi mdi-shape text-primary"></i> Category <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showCategoryDropdown', true)">
                                            <div class="tag-select-input">
                                                <!-- Display selected category -->
                                                @if($this->selectedCategory)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedCategory->sample_type_category }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('sampleTypeForm.category_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Search Input -->
                                                <input type="text" 
                                                       wire:model.live="categorySearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedCategory ? '' : 'Search categories...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showCategoryDropdown)
                                                <div class="tag-dropdown">
                                                    <!-- Create New Category Option -->
                                                    <div class="tag-dropdown-item tag-dropdown-create" wire:click.stop="showCreateCategoryModal">
                                                        <i class="mdi mdi-plus-circle text-success"></i>
                                                        <strong class="text-success">Create New Category</strong>
                                                    </div>
                                                    @if(count($this->filteredCategories) > 0)
                                                        <div class="tag-dropdown-divider"></div>
                                                        @foreach($this->filteredCategories as $category)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectCategory({{ $category->id }})">
                                                                {{ $category->sample_type_category }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        @error('sampleTypeForm.category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-flask-outline text-info"></i> Lab Sections
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showLabSectionDropdown', true)">
                                            <div class="tag-select-input">
                                                @foreach($this->selectedLabSections as $section)
                                                    <span class="tag-badge">
                                                        {{ $section->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="toggleLabSection({{ $section->id }})"></i>
                                                    </span>
                                                @endforeach
                                                
                                                <input type="text" 
                                                       wire:model.live="labSectionSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ count($this->selectedLabSections) > 0 ? '' : 'Search lab sections...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showLabSectionDropdown)
                                                <div class="tag-dropdown">
                                                    @if(count($this->filteredLabSections) > 0)
                                                        @foreach($this->filteredLabSections as $section)
                                                            <div class="tag-dropdown-item" wire:click.stop="toggleLabSection({{ $section->id }})">
                                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                                    <span>{{ $section->name }}</span>
                                                                    @if(in_array($section->id, $sampleTypeForm['sample_analysis_stage_ids']))
                                                                        <i class="mdi mdi-check text-success"></i>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="p-3 text-center text-muted">No sections found</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">Select lab sections required for this sample type</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-star text-warning"></i> Rating System
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showRatingHeaderDropdown', true)">
                                            <div class="tag-select-input">
                                                <!-- Display selected rating header -->
                                                @if($this->selectedRatingHeader)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedRatingHeader->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('sampleTypeForm.rating_header_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Search Input -->
                                                <input type="text" 
                                                       wire:model.live="ratingHeaderSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedRatingHeader ? '' : 'Search rating systems...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showRatingHeaderDropdown && count($this->filteredRatingHeaders) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredRatingHeaders as $ratingHeader)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectRatingHeader({{ $ratingHeader->id }})">
                                                            {{ $ratingHeader->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('sampleTypeForm.rating_header_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Optional: Select a rating system for this sample type</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-file-document text-info"></i> Report Format
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showReportFormatDropdown', true)">
                                            <div class="tag-select-input">
                                                <!-- Display selected report format -->
                                                @if($this->selectedReportFormat)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedReportFormat->report_name }} ({{ $this->selectedReportFormat->report_code }})
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('sampleTypeForm.report_format_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Search Input -->
                                                <input type="text" 
                                                       wire:model.live="reportFormatSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedReportFormat ? '' : 'Search report formats...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showReportFormatDropdown && count($this->filteredReportFormats) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredReportFormats as $reportFormat)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectReportFormat({{ $reportFormat->id }})">
                                                            {{ $reportFormat->report_name }} ({{ $reportFormat->report_code }})
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('sampleTypeForm.report_format_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Optional: Select a report format for this sample type</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-package-variant text-success"></i> Default Product
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showCompanyProductDropdown', true)">
                                            <div class="tag-select-input">
                                                <!-- Display selected company product -->
                                                @if($this->selectedCompanyProduct)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedCompanyProduct->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('sampleTypeForm.default_product_id', null)"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Search Input -->
                                                <input type="text" 
                                                       wire:model.live="companyProductSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedCompanyProduct ? '' : 'Search products...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showCompanyProductDropdown)
                                                <div class="tag-dropdown">
                                                    <!-- Create New Product Option -->
                                                    <div class="tag-dropdown-item tag-dropdown-create" wire:click.stop="showCreateProductModal">
                                                        <i class="mdi mdi-plus-circle text-success"></i>
                                                        <strong class="text-success">Create New Product</strong>
                                                    </div>
                                                    @if(count($this->filteredCompanyProducts) > 0)
                                                        <div class="tag-dropdown-divider"></div>
                                                        @foreach($this->filteredCompanyProducts as $companyProduct)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectCompanyProduct({{ $companyProduct->id }})">
                                                                {{ $companyProduct->name }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        @error('sampleTypeForm.default_product_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Optional: Select a default product for this sample type</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-delete-clock text-warning"></i> Disposal Count (Days)
                                        </label>
                                        <input type="number" 
                                               wire:model="sampleTypeForm.disposal_count" 
                                               class="form-control" 
                                               placeholder="Enter number of days"
                                               min="0">
                                        <small class="form-text text-muted">Number of days before sample disposal</small>
                                        @error('sampleTypeForm.disposal_count') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" wire:model="sampleTypeForm.active" class="form-check-input" id="active">
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeSampleTypeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveSampleType">
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
                                        <div class="tag-select-container" wire:click="$set('showLabDropdown', true)">
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
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLab({{ $lab->id }})">
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
                        <button type="button" class="btn btn-secondary" wire:click="closeAnalysisTypeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveAnalysisType">
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
                        <button type="button" class="btn btn-secondary" wire:click="closeCategoryModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="saveCategory">
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
                        <button type="button" class="btn btn-secondary" wire:click="closeProductModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="saveProduct">
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
    </style>
    
    @script
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container') && !e.target.closest('.modal')) {
            $wire.set('showCategoryDropdown', false);
            $wire.set('showRatingHeaderDropdown', false);
            $wire.set('showReportFormatDropdown', false);
            $wire.set('showCompanyProductDropdown', false);
            $wire.set('showLabDropdown', false);
        }
    });
    </script>
    @endscript
</div>