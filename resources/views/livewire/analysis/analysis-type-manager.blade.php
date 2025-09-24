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
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Lab</th>
                                        <th>Elements</th>
                                        <th>Level</th>
                                        <th>Reporting Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->analysisTypes as $analysisType)
                                        <tr>
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
                                                {{ $analysisType->lab->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info p-2">{{ $analysisType->analysis_elements->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary p-2">{{ $analysisType->level }}</span>
                                            </td>
                                            <td>
                                                @if($analysisType->reporting_time)
                                                    <span class="badge bg-info p-2">{{ $analysisType->reporting_time }}d</span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge p-2 bg-{{ $analysisType->active ? 'success' : 'danger' }}">
                                                    {{ $analysisType->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
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
                                    <thead class="table-light">
                                        <tr>
                                            <th>Analyte</th>
                                            <th>Method</th>
                                            <th>Equipment</th>
                                            <th>Operator</th>
                                            <th>Reporting Unit</th>
                                            <th>LOD</th>
                                            <th>HOD</th>
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
                                                    @if($element->recommend_remedies && $element->remedyHeader)
                                                        <br><small class="text-success"><i class="mdi mdi-medical-bag"></i> {{ $element->remedyHeader->name }}</small>
                                                    @endif
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
                                                    {{ $element->lod }}
                                                </td>
                                                <td>
                                                    {{ $element->hod }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $element->level }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $element->active ? 'success' : 'danger' }}">
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
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab <span class="text-danger">*</span></label>
                                        <select wire:model="analysisTypeForm.lab_id" class="form-select modern-select @error('analysisTypeForm.lab_id') is-invalid @enderror">
                                            <option value="">Select Lab</option>
                                            @foreach($labs as $lab)
                                                <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('analysisTypeForm.lab_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <select wire:model="elementForm.analyte_id" class="form-select modern-select @error('elementForm.analyte_id') is-invalid @enderror">
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
                                        <select wire:model="elementForm.method" class="form-select modern-select">
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
                                        <select wire:model="elementForm.equipment_id" class="form-select modern-select">
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
                                        <select wire:model="elementForm.operator_id" class="form-select modern-select">
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
                            
                            <!-- Remedy Recommendation Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">
                                                <i class="mdi mdi-medical-bag"></i> Remedy Recommendations
                                            </h6>
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           wire:model="elementForm.recommend_remedies" 
                                                           class="form-check-input" 
                                                           id="recommend_remedies"
                                                           wire:change="updatedElementFormRecommendRemedies">
                                                    <label class="form-check-label" for="recommend_remedies">
                                                        Recommend Remedies if Test Fails
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            @if($elementForm['recommend_remedies'])
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Remedy System</label>
                                                    <select wire:model="elementForm.remedy_header_id" 
                                                            class="form-select modern-select @error('elementForm.remedy_header_id') is-invalid @enderror">
                                                        <option value="">Select Remedy System</option>
                                                        @foreach($remedyHeaders as $remedyHeader)
                                                            <option value="{{ $remedyHeader->id }}">{{ $remedyHeader->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('elementForm.remedy_header_id') 
                                                        <div class="invalid-feedback">{{ $message }}</div> 
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Select the remedy system to recommend when this test fails
                                                    </small>
                                                </div>
                                            @endif
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
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
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
    </style>
</div>
