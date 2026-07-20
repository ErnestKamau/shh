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
                        <button wire:click="showCreateAnalysisTypeModal" class="btn btn-outline-primary analysis-add-btn">
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
                                <x-searchable-select
                                    wire:model.live="labFilter"
                                    :options="collect($labs)->map(fn($lab) => ['id' => $lab->id, 'name' => $lab->name])"
                                    placeholder="Search labs..."
                                    empty-label="All Labs"
                                />
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
                                        <th>Labs</th>
                                        <th>Lab Section</th>
                                        <th>Elements</th>
                                        <th>Level</th>
                                        <th>Has Attachable Result</th>
                                        <th>Reporting Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->analysisTypes as $analysisType)
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('livewire.elements', ['analysisTypeId' => $analysisType->id]) }}" 
                                                       class="rm-act-btn rm-act-btn--view" 
                                                       title="View Elements">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="showEditAnalysisTypeModal('{{ $analysisType->id }}')" 
                                                            class="rm-act-btn rm-act-btn--edit" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteAnalysisType('{{ $analysisType->id }}')" 
                                                            class="rm-act-btn rm-act-btn--delete" 
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
                                                @php
                                                    $labs = $analysisType->labs;
                                                @endphp
                                                @if($labs->count() > 0)
                                                    @foreach($labs as $lab)
                                                        <span class="badge bg-light text-dark p-2 border mr-1">{{ $lab->name }}</span>
                                                    @endforeach
                                                @elseif($analysisType->lab)
                                                    <span class="badge bg-light text-dark p-2 border">{{ $analysisType->lab->name }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($analysisType->labsectionname)
                                                    <span class="badge bg-light text-dark p-2 border">{{ $analysisType->labsectionname }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info p-2" style="color: white;">{{ $analysisType->analysis_elements->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary p-2" style="color: white;">{{ $analysisType->level }}</span>
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
                                {{ $this->analysisTypes->links() }}
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
                                        <label class="form-label"><i class="mdi mdi-flask text-primary"></i> Lab <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showLabDropdown', true)" wire:click.outside="$set('showLabDropdown', false)">
                                            <div class="tag-select-input">
                                                @foreach($this->selectedLabs as $selectedLab)
                                                    <span class="tag-badge">
                                                        {{ $selectedLab->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="selectLab('{{ $selectedLab->id }}')"></i>
                                                    </span>
                                                @endforeach

                                                <input type="text" 
                                                       wire:model.live="labSearch" 
                                                       class="tag-input" 
                                                       placeholder="Search labs..."
                                                       autocomplete="off">
                                            </div>

                                            @if($showLabDropdown && count($this->filteredLabs) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredLabs as $lab)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLab('{{ $lab->id }}')">
                                                            {{ $lab->code }} - {{ $lab->name }}
                                                            @if(in_array((string) $lab->id, (array) ($analysisTypeForm['lab_ids'] ?? []), true))
                                                                <span class="badge bg-success float-end">Selected</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('analysisTypeForm.lab_ids') <span class="text-danger">{{ $message }}</span> @enderror
                                        @error('analysisTypeForm.lab_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-view-grid-outline text-primary"></i> Lab Section <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showLabSectionDropdown', true)" wire:click.outside="$set('showLabSectionDropdown', false)">
                                            <div class="tag-select-input">
                                                @if($this->selectedLabSection)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedLabSection->name }}{{ $this->selectedLabSection->code ? ' — '.$this->selectedLabSection->code : '' }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearLabSectionSelection"></i>
                                                    </span>
                                                @endif

                                                <input type="text"
                                                       wire:model.live="labSectionSearch"
                                                       class="tag-input"
                                                       placeholder="Search lab sections..."
                                                       autocomplete="off"
                                                       @if($this->selectedLabSection) style="min-width: 8rem;" @endif>
                                            </div>

                                            @if($showLabSectionDropdown && count($this->filteredLabSections) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($this->filteredLabSections as $section)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLabSection('{{ $section->id }}')">
                                                            {{ $section->code ? $section->code.' — ' : '' }}{{ $section->name }}
                                                            @if((string) ($analysisTypeForm['lab_section_id'] ?? '') === (string) $section->id)
                                                                <span class="badge bg-success float-end">Selected</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">All tests under this type belong to this section (e.g. MICRO / CHEM).</small>
                                        @error('analysisTypeForm.lab_section_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Level</label>
                                        <input type="number" wire:model="analysisTypeForm.level" class="form-control" min="1">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Reporting Time (days)</label>
                                        <input type="number" wire:model="analysisTypeForm.reporting_time" class="form-control" min="0" placeholder="e.g., 7">
                                        <small class="form-text text-muted">Days to complete</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Analysis Options Section -->
                            <div class="at-options-panel mb-3">
                                <div class="at-options-head">
                                    <span class="at-options-head-icon" aria-hidden="true">
                                        <i class="mdi mdi-tune-variant"></i>
                                    </span>
                                    <div>
                                        <h6 class="at-options-heading">Analysis Options</h6>
                                        <p class="at-options-subheading">Configure analysis type settings and worksheet behaviour</p>
                                    </div>
                                </div>

                                <div class="at-options-body">
                                    <ul class="at-option-list">
                                        <li>
                                            <label class="at-option-card" for="analysis_active">
                                                <span class="at-option-card-body">
                                                    <span class="at-option-icon at-option-icon--success">
                                                        <i class="mdi mdi-check-circle-outline"></i>
                                                    </span>
                                                    <span class="at-option-text">
                                                        <span class="at-option-title">Active</span>
                                                        <span class="at-option-desc">Make this analysis type available for sample assignment</span>
                                                    </span>
                                                </span>
                                                <span class="at-option-toggle">
                                                    <input type="checkbox" wire:model="analysisTypeForm.active" class="at-switch-input" id="analysis_active">
                                                    <span class="at-switch-track"></span>
                                                </span>
                                            </label>
                                        </li>
                                        <li>
                                            <label class="at-option-card" for="has_no_result">
                                                <span class="at-option-card-body">
                                                    <span class="at-option-icon at-option-icon--amber">
                                                        <i class="mdi mdi-flask-empty-outline"></i>
                                                    </span>
                                                    <span class="at-option-text">
                                                        <span class="at-option-title">Has attachable result</span>
                                                        <span class="at-option-desc">Results are captured via a procedure worksheet rather than analyte elements</span>
                                                    </span>
                                                </span>
                                                <span class="at-option-toggle">
                                                    <input type="checkbox" wire:model.live="analysisTypeForm.has_no_result" class="at-switch-input" id="has_no_result">
                                                    <span class="at-switch-track"></span>
                                                </span>
                                            </label>
                                        </li>
                                        <li class="at-option-stack {{ ($analysisTypeForm['uses_grouped_procedures'] ?? false) ? 'is-expanded' : '' }}">
                                            <label class="at-option-card {{ ($analysisTypeForm['uses_grouped_procedures'] ?? false) ? 'is-expanded' : '' }}" for="uses_grouped_procedures">
                                                <span class="at-option-card-body">
                                                    <span class="at-option-icon at-option-icon--teal">
                                                        <i class="mdi mdi-file-tree-outline"></i>
                                                    </span>
                                                    <span class="at-option-text">
                                                        <span class="at-option-title">Analysis done by grouped procedures</span>
                                                        <span class="at-option-desc">Use a multi-stage grouped pipeline or a hybrid worksheet instead of a single procedure worksheet</span>
                                                    </span>
                                                </span>
                                                <span class="at-option-toggle">
                                                    <input type="checkbox" wire:model.live="analysisTypeForm.uses_grouped_procedures" class="at-switch-input" id="uses_grouped_procedures">
                                                    <span class="at-switch-track"></span>
                                                </span>
                                            </label>

                                            @if($analysisTypeForm['uses_grouped_procedures'] ?? false)
                                                <div class="at-options-nested">
                                                    <label class="at-nested-label">
                                                        Grouped or hybrid worksheet <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="tag-select-container at-nested-select" wire:click="$set('showPipelineWorksheetDropdown', true)" wire:click.outside="$set('showPipelineWorksheetDropdown', false)">
                                                        <div class="tag-select-input">
                                                            @if($this->selectedPipelineWorksheet)
                                                                <span class="tag-badge">
                                                                    {{ $this->selectedPipelineWorksheet['name'] }}
                                                                    <small class="ms-1 opacity-75">({{ $this->selectedPipelineWorksheet['subtitle'] }})</small>
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearPipelineWorksheetSelection"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text"
                                                                   wire:model.live="pipelineWorksheetSearch"
                                                                   class="tag-input"
                                                                   placeholder="{{ $this->selectedPipelineWorksheet ? '' : 'Search grouped pipelines or hybrid worksheets...' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        @if($showPipelineWorksheetDropdown && count($this->filteredPipelineWorksheets) > 0)
                                                            <div class="tag-dropdown">
                                                                @foreach($this->filteredPipelineWorksheets as $option)
                                                                    <div class="tag-dropdown-item" wire:click.stop="selectPipelineWorksheet('{{ $option['type'] }}', '{{ $option['id'] }}')">
                                                                        <strong>{{ $option['name'] }}</strong>
                                                                        <span class="at-pipeline-badge">{{ $option['subtitle'] }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('analysisTypeForm.grouped_worksheet_holder_id')
                                                        <span class="at-field-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            @endif
                                        </li>
                                    </ul>

                                    @if(($analysisTypeForm['has_no_result'] ?? false) && !($analysisTypeForm['uses_grouped_procedures'] ?? false))
                                        <div class="at-options-nested at-options-nested--standalone">
                                            <label class="at-nested-label">
                                                <i class="mdi mdi-file-document-outline"></i>
                                                Procedure worksheet <span class="text-danger">*</span>
                                            </label>
                                            <div class="tag-select-container at-nested-select" wire:click="$set('showProcedureWorksheetDropdown', true)" wire:click.outside="$set('showProcedureWorksheetDropdown', false)">
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
                                                            <div class="tag-dropdown-item" wire:click.stop="selectProcedureWorksheet('{{ $worksheet->id }}')">
                                                                <strong>{{ $worksheet->name }}</strong>
                                                                @if($worksheet->description)
                                                                    <br><small class="text-muted">{{ Str::limit($worksheet->description, 50) }}</small>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <p class="at-nested-hint">Select the procedure worksheet used when capturing results for this analysis type.</p>
                                            @error('analysisTypeForm.procedure_worksheet_id')
                                                <span class="at-field-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif
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

    /* Analysis Options panel */
    .at-options-panel {
        --at-slate-50: #f8fafc;
        --at-slate-100: #f1f5f9;
        --at-slate-200: #e2e8f0;
        --at-slate-500: #64748b;
        --at-slate-700: #334155;
        --at-slate-900: #0f172a;
        --at-blue-500: #3b82f6;
        --at-blue-600: #2563eb;
        background: linear-gradient(165deg, var(--at-slate-50) 0%, #ffffff 52%, var(--at-slate-100) 100%);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 0.875rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.85);
        overflow: visible;
    }

    .at-options-head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.15rem 0.9rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.75);
        background: rgba(255, 255, 255, 0.55);
    }

    .at-options-head-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.6rem;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: var(--at-blue-600);
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .at-options-heading {
        margin: 0 0 0.2rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--at-slate-900);
        letter-spacing: -0.02em;
    }

    .at-options-subheading {
        margin: 0;
        font-size: 0.8rem;
        color: var(--at-slate-500);
        line-height: 1.45;
    }

    .at-options-body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .at-option-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .at-option-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        margin: 0;
        padding: 0.85rem 0.95rem;
        background: #fff;
        border: 1px solid var(--at-slate-200);
        border-radius: 0.65rem;
        cursor: pointer;
        user-select: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .at-option-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.08);
    }

    .at-option-card:has(.at-switch-input:checked),
    .at-option-card.is-expanded {
        border-color: rgba(59, 130, 246, 0.32);
        background: linear-gradient(90deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 2px 10px rgba(59, 130, 246, 0.07);
    }

    .at-option-stack.is-expanded .at-option-card.is-expanded {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-color: rgba(226, 232, 240, 0.6);
    }

    .at-option-card-body {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        min-width: 0;
        flex: 1;
    }

    .at-option-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 0.5rem;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .at-option-icon--success {
        background: #ecfdf5;
        color: #059669;
    }

    .at-option-icon--amber {
        background: #fffbeb;
        color: #d97706;
    }

    .at-option-icon--teal {
        background: #f0fdfa;
        color: #0d9488;
    }

    .at-option-text {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 0;
    }

    .at-option-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--at-slate-900);
        line-height: 1.3;
    }

    .at-option-desc {
        font-size: 0.78rem;
        color: var(--at-slate-500);
        line-height: 1.4;
    }

    .at-option-toggle {
        position: relative;
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
    }

    .at-switch-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        margin: 0;
        pointer-events: none;
    }

    .at-switch-track {
        position: relative;
        display: block;
        width: 2.75rem;
        height: 1.5rem;
        background: #cbd5e1;
        border-radius: 999px;
        transition: background-color 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s ease;
    }

    .at-switch-track::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 1.125rem;
        height: 1.125rem;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .at-switch-input:checked + .at-switch-track {
        background: linear-gradient(135deg, var(--at-blue-500) 0%, var(--at-blue-600) 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    .at-switch-input:checked + .at-switch-track::after {
        transform: translateX(1.25rem);
    }

    .at-switch-input:focus-visible + .at-switch-track {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.28);
    }

    .at-option-stack {
        position: relative;
        z-index: 1;
    }

    .at-option-stack.is-expanded {
        z-index: 2;
    }

    .at-options-nested {
        margin: 0;
        padding: 0.95rem 1rem 1rem;
        background: #fff;
        border: 1px solid rgba(59, 130, 246, 0.22);
        border-top: none;
        border-radius: 0 0 0.65rem 0.65rem;
        box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.02);
        overflow: visible;
    }

    .at-options-nested--standalone {
        margin-top: 0.65rem;
        border-top: 1px solid var(--at-slate-200);
        border-radius: 0.65rem;
    }

    .at-nested-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--at-slate-700);
        letter-spacing: 0.01em;
    }

    .at-nested-label .mdi {
        font-size: 1rem;
        vertical-align: -2px;
        margin-right: 0.15rem;
        color: var(--at-blue-600);
    }

    .at-nested-hint {
        margin: 0.45rem 0 0;
        font-size: 0.76rem;
        color: var(--at-slate-500);
        line-height: 1.4;
    }

    .at-nested-select .tag-select-input {
        border-color: var(--at-slate-200);
        border-radius: 0.55rem;
        background: var(--at-slate-50);
    }

    .at-nested-select .tag-select-input:focus-within {
        border-color: var(--at-blue-500);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .at-nested-select .tag-dropdown {
        z-index: 2100;
    }

    .at-pipeline-badge {
        float: right;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 0.2rem 0.45rem;
        border-radius: 0.35rem;
        background: #eff6ff;
        color: var(--at-blue-600);
    }

    .at-field-error {
        display: block;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        color: #dc3545;
    }

    .analysis-add-btn {
        border-radius: 8px;
        padding: 0.48rem 1rem;
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
