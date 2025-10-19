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

    <!-- Analysis Types Section -->
    @if($showAnalysisTypes && $selectedSampleType)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Analysis Types</h5>
                        <button wire:click="showCreateAnalysisTypeModal" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus"></i> Add Analysis Type
                        </button>
                    </div>
                    <div class="card-body">
                        @if($analysisTypes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Lab</th>
                                            <th>Elements</th>
                                            <th>Level</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analysisTypes as $analysisType)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary" style="color: white;">{{ $analysisType->code }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $analysisType->name }}</strong>
                                                    @if($analysisType->description)
                                                        <br><small class="text-muted">{{ $analysisType->description }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $lab = $labs->firstWhere('id', $analysisType->lab_id);
                                                    @endphp
                                                    {{ $lab ? $lab->name : 'N/A' }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-info" style="color: white;">{{ $analysisType->analysis_elements->count() }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary" style="color: white;">{{ $analysisType->level }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $analysisType->active ? 'success' : 'danger' }}" style="color: white;">
                                                        {{ $analysisType->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="selectAnalysisType({{ $analysisType->id }})" 
                                                                class="btn btn-sm btn-outline-primary" 
                                                                title="View Elements">
                                                            <i class="mdi mdi-eye"></i>
                                                        </button>
                                                        <button wire:click="showEditAnalysisTypeModal({{ $analysisType->id }})" 
                                                                class="btn btn-sm btn-outline-warning" 
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="deleteAnalysisType({{ $analysisType->id }})" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this analysis type? This will also delete all associated elements.')">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-test-tube text-muted" style="font-size: 2rem;"></i>
                                <h6 class="text-muted mt-2">No analysis types found</h6>
                                <p class="text-muted">Add analysis types to this sample type.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Elements Section -->
    @if($showElements && $selectedAnalysisType)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Analysis Elements</h5>
                        <button wire:click="showCreateElementModal" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus"></i> Add Element
                        </button>
                    </div>
                    <div class="card-body">
                        @if($elements->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th>Analyte</th>
                                            <th>Method</th>
                                            <th>Equipment</th>
                                            <th>Operator</th>
                                            <th>Reporting Unit</th>
                                            <th>Level</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($elements as $element)
                                            <tr>
                                                <td>
                                                    <strong>{{ $element->analyte->name ?? 'N/A' }}</strong>
                                                    <br><small class="text-muted">{{ $element->analyte->code ?? '' }}</small>
                                                </td>
                                                <td>
                                                    {{ $element->mmethod->name ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    {{ $element->equipment->name ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    {{ $element->operator->name ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    {{ $element->reporting_unit }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary" style="color: white;">{{ $element->level }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $element->active ? 'success' : 'danger' }}" style="color: white;">
                                                        {{ $element->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditElementModal({{ $element->id }})" 
                                                                class="btn btn-sm btn-outline-warning" 
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="deleteElement({{ $element->id }})" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this element?')">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-atom text-muted" style="font-size: 2rem;"></i>
                                <h6 class="text-muted mt-2">No elements found</h6>
                                <p class="text-muted">Add analysis elements to this analysis type.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Sample Type Modal -->
    @if($showSampleTypeModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
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
                                        <label class="form-label"><i class="mdi mdi-shape text-primary"></i> Category <span class="text-danger">*</span></label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('sampleTypeForm.category_id').live,
                                            categories: {{ json_encode($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->sample_type_category])->values()) }},
                                            get filteredCategories() {
                                                if (!this.search) return this.categories;
                                                return this.categories.filter(cat => 
                                                    cat.name.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            selectCategory(catId) {
                                                this.selected = catId;
                                                this.open = false;
                                                this.search = '';
                                            },
                                            getSelectedName() {
                                                const cat = this.categories.find(c => c.id == this.selected);
                                                return cat ? cat.name : '';
                                            }
                                        }" class="searchable-dropdown-wrapper">
                                            <div class="single-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected ? getSelectedName() : 'Search categories...'"
                                                    @focus="open = true"
                                                    class="form-control searchable-input-single"
                                                    autocomplete="off"
                                                >
                                                <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                            </div>

                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 x-transition
                                                 class="dropdown-list">
                                                <template x-if="filteredCategories.length > 0">
                                                    <div class="options-list">
                                                        <template x-for="cat in filteredCategories" :key="cat.id">
                                                            <div @click="selectCategory(cat.id)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': selected == cat.id }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="selected == cat.id"></i>
                                                                <span x-text="cat.name"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="filteredCategories.length === 0">
                                                    <div class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>No categories found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('sampleTypeForm.category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Rating System</label>
                                        <select wire:model="sampleTypeForm.rating_header_id" class="form-select modern-select @error('sampleTypeForm.rating_header_id') is-invalid @enderror">
                                            <option value="">No Rating System</option>
                                            @foreach($ratingHeaders as $ratingHeader)
                                                <option value="{{ $ratingHeader->id }}">{{ $ratingHeader->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('sampleTypeForm.rating_header_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="form-text text-muted">Optional: Select a rating system for this sample type</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Report Format</label>
                                        <select wire:model="sampleTypeForm.report_format_id" class="form-select modern-select @error('sampleTypeForm.report_format_id') is-invalid @enderror">
                                            <option value="">No Report Format</option>
                                            @foreach($reportFormats as $reportFormat)
                                                <option value="{{ $reportFormat->id }}">{{ $reportFormat->report_name }} ({{ $reportFormat->report_code }})</option>
                                            @endforeach
                                        </select>
                                        @error('sampleTypeForm.report_format_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="form-text text-muted">Optional: Select a report format for this sample type</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
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
            <div class="modal-dialog modal-lg">
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
                                        <label class="form-label">Lab <span class="text-danger">*</span></label>
                                        <select wire:model="analysisTypeForm.lab_id" class="form-select @error('analysisTypeForm.lab_id') is-invalid @enderror">
                                            <option value="">Select Lab</option>
                                            @foreach($labs as $lab)
                                                <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('analysisTypeForm.lab_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

    <!-- Element Modal -->
    @if($showElementModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingElement ? 'pencil' : 'plus' }}"></i>
                            {{ $editingElement ? 'Edit' : 'Create' }} Analysis Element
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeElementModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveElement">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Analyte <span class="text-danger">*</span></label>
                                        <select wire:model="elementForm.analyte_id" class="form-select @error('elementForm.analyte_id') is-invalid @enderror">
                                            <option value="">Select Analyte</option>
                                            @foreach($analytes as $analyte)
                                                <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('elementForm.analyte_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Method</label>
                                        <select wire:model="elementForm.method" class="form-select">
                                            <option value="">Select Method</option>
                                            @foreach($methods as $method)
                                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Equipment</label>
                                        <select wire:model="elementForm.equipment_id" class="form-select">
                                            <option value="">Select Equipment</option>
                                            @foreach($equipment as $eq)
                                                <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Operator</label>
                                        <select wire:model="elementForm.operator_id" class="form-select">
                                            <option value="">Select Operator</option>
                                            @foreach($operators as $operator)
                                                <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Reporting Unit</label>
                                        <input type="text" wire:model="elementForm.reporting_unit" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Decimal Places</label>
                                        <input type="number" wire:model="elementForm.decimal_places" class="form-control" min="0" max="10">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Level</label>
                                        <input type="number" wire:model="elementForm.level" class="form-control" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">LOD (Limit of Detection)</label>
                                        <input type="number" wire:model="elementForm.lod" class="form-control" step="0.0001">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">HOD (Limit of Quantification)</label>
                                        <input type="number" wire:model="elementForm.hod" class="form-control" step="0.0001">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Significant Figures</label>
                                        <input type="number" wire:model="elementForm.significant_figures" class="form-control" min="1" max="10">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.active" class="form-check-input" id="element_active">
                                            <label class="form-check-label" for="element_active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.non_detectable" class="form-check-input" id="non_detectable">
                                            <label class="form-check-label" for="non_detectable">Non-detectable</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.non_accredited" class="form-check-input" id="non_accredited">
                                            <label class="form-check-label" for="non_accredited">Non-accredited</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.show_on_report" class="form-check-input" id="show_on_report">
                                            <label class="form-check-label" for="show_on_report">Show on Report</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeElementModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveElement">
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
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
    }
    
    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background: #ffffff;
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
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
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
    </style>

</div>