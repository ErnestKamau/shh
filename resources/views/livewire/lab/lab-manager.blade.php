<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-flask text-primary"></i>
                                Labs Management
                            </h2>
                            <p class="text-muted mb-0">Manage laboratory facilities and their configurations</p>
                        </div>
                        <button wire:click="showCreateLabModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Lab
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if(!empty($message))
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search labs by name, code, or email...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Zone</label>
                                <div class="tag-select-container" wire:click="$set('showZoneFilterDropdown', true)" wire:click.outside="$set('showZoneFilterDropdown', false)">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        @if($this->selectedZoneFilter)
                                            <span class="tag-badge">
                                                {{ $this->selectedZoneFilter->key }}{{ $this->selectedZoneFilter->value ? ' - ' . $this->selectedZoneFilter->value : '' }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearZoneFilter"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                               wire:model.live.debounce.200ms="zoneFilterSearch"
                                               class="tag-input"
                                               placeholder="{{ $this->selectedZoneFilter ? '' : 'All Zones / Search...' }}"
                                               autocomplete="off">
                                    </div>

                                    @if($showZoneFilterDropdown)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item" wire:click.stop="clearZoneFilter">
                                                All Zones
                                            </div>

                                            @forelse($this->filteredZones as $zone)
                                                <div class="tag-dropdown-item" wire:click.stop="selectZoneFilter('{{ $zone->id }}')">
                                                    {{ $zone->key }}{{ $zone->value ? ' - ' . $zone->value : '' }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">
                                                    No zones found
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Directorate</label>
                                <div class="tag-select-container" wire:click="$set('showDirectorateFilterDropdown', true)" wire:click.outside="$set('showDirectorateFilterDropdown', false)">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        @if($this->selectedDirectorateFilter)
                                            <span class="tag-badge">
                                                {{ $this->selectedDirectorateFilter->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearDirectorateFilter"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                               wire:model.live.debounce.200ms="directorateFilterSearch"
                                               class="tag-input"
                                               placeholder="{{ $this->selectedDirectorateFilter ? '' : 'All Directorates / Search...' }}"
                                               autocomplete="off">
                                    </div>

                                    @if($showDirectorateFilterDropdown)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item" wire:click.stop="clearDirectorateFilter">
                                                All Directorates
                                            </div>

                                            @forelse($this->filteredDirectorates as $directorate)
                                                <div class="tag-dropdown-item" wire:click.stop="selectDirectorateFilter('{{ $directorate->id }}')">
                                                    {{ $directorate->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">
                                                    No directorates found
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
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

    <!-- Labs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Labs</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @forelse($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @empty
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->labs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover lab-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Actions</th>
                                        <th>Sections</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Zone</th>
                                        <th>Directorate</th>
                                        <th>Start Sample</th>
                                        <th>Email</th>
                                        <th>Phone 1</th>
                                        <th>Internal Lab</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->labs as $lab)
                                        <tr wire:key="lab-row-{{ $lab->id }}">
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button wire:click="toggleLabRow('{{ $lab->id }}')"
                                                            class="rm-act-btn rm-act-btn--expand"
                                                            title="{{ in_array($lab->id, $expandedLabs, true) ? 'Collapse' : 'Expand' }}">
                                                        <i class="mdi mdi-chevron-{{ in_array($lab->id, $expandedLabs, true) ? 'up' : 'down' }}"></i>
                                                    </button>
                                                    <button wire:click="showEditLabModal('{{ $lab->id }}')" 
                                                            class="rm-act-btn rm-act-btn--edit" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="confirmDeleteLab('{{ $lab->id }}')" 
                                                            class="rm-act-btn rm-act-btn--delete" 
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="lab-badge lab-badge--info">
                                                    {{ $lab->labSections->count() }} Section{{ $lab->labSections->count() === 1 ? '' : 's' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="">{{ $lab->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $lab->name }}</strong>
                                                @if($lab->location)
                                                    <br><small class="text-muted"><i class="mdi mdi-map-marker"></i> {{ $lab->location }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lab->zone ? (($lab->zone->key ?? '') . (($lab->zone->value ?? '') ? ' - ' . $lab->zone->value : '')) : '-' }}
                                            </td>
                                            <td>{{ $lab->directorate->name ?? '-' }}</td>
                                            <td>{{ $lab->start_sample_no ?? '-' }}</td>
                                            <td>{{ $lab->email }}</td>
                                            <td>{{ $lab->phone1 ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($lab->is_external == 0)
                                                    <span class="lab-badge lab-badge--internal">
                                                        <i class="mdi mdi-check-circle"></i> Internal
                                                    </span>
                                                @else
                                                    <span class="lab-badge lab-badge--external">
                                                        <i class="mdi mdi-earth"></i> External
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="lab-badge {{ $lab->active ? 'lab-badge--active' : 'lab-badge--inactive' }}">
                                                    {{ $lab->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>

                                        @if(in_array($lab->id, $expandedLabs, true))
                                            <tr class="lab-expand-row" wire:key="lab-expand-row-{{ $lab->id }}">
                                                <td colspan="11">
                                                    <div class="section-shell">
                                                        <div class="section-shell__header">
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <i class="mdi mdi-layers-triple"></i>
                                                                    Lab Sections for {{ $lab->name }}
                                                                </h6>
                                                                <p class="text-muted mb-0">Track environmental analysis setup, expected values, and reporting units.</p>
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm section-add-btn" wire:click="showCreateLabSectionModal('{{ $lab->id }}')">
                                                                <i class="mdi mdi-plus"></i> Add Lab Section
                                                            </button>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            @forelse($lab->labSections as $section)
                                                                <div class="col-md-6 col-xl-4">
                                                                    <div class="section-card">
                                                                        <div class="section-card__top">
                                                                            <div>
                                                                                <p class="section-code">{{ $section->code }}</p>
                                                                                <h6 class="mb-1">{{ $section->name }}</h6>
                                                                            </div>
                                                                            <div class="section-card__actions">
                                                                                <span class="lab-badge {{ $section->active ? 'lab-badge--active' : 'lab-badge--inactive' }}">
                                                                                    {{ $section->active ? 'Active' : 'Inactive' }}
                                                                                </span>
                                                                                <div class="section-card__action-buttons">
                                                                                    <button type="button" class="rm-act-btn rm-act-btn--edit" wire:click="showEditLabSectionModal('{{ $section->id }}')" title="Edit Section">
                                                                                        <i class="mdi mdi-pencil"></i>
                                                                                    </button>
                                                                                    <button type="button" class="rm-act-btn rm-act-btn--delete" wire:click="confirmDeleteLabSection('{{ $section->id }}')" title="Delete Section">
                                                                                        <i class="mdi mdi-delete"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <ul class="section-meta">
                                                                            <li><span>Environmental Analysis</span><strong>{{ $section->does_environmental_analysis ? 'Yes' : 'No' }}</strong></li>
                                                                            <li><span>Equipment</span><strong>{{ $section->equipment->name ?? '-' }}</strong></li>
                                                                            <li><span>Expected Value</span><strong>
                                                                                @if($section->expected_value_type === 'constant')
                                                                                    Constant: {{ $section->expected_value ?? '-' }}
                                                                                @elseif($section->expected_value_type === 'range')
                                                                                    Range: {{ $section->expected_min ?? '-' }} - {{ $section->expected_max ?? '-' }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </strong></li>
                                                                            <li><span>Optimum Level</span><strong>{{ $section->optimum_level ?? '-' }}</strong></li>
                                                                            <li><span>Result Nature</span><strong>{{ $section->result_nature ?? '-' }}</strong></li>
                                                                            <li><span>Reporting Unit</span><strong>{{ $section->reportingUnit->name ?? $section->reporting_unit ?? '-' }}</strong></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="col-12">
                                                                    <div class="empty-section-state">
                                                                        <i class="mdi mdi-flask-empty"></i>
                                                                        <p class="mb-0">No sections created for this lab yet.</p>
                                                                    </div>
                                                                </div>
                                                            @endforelse
                                                        </div>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->labs->firstItem() ?? 0 }} to {{ $this->labs->lastItem() ?? 0 }} of {{ $this->labs->total() }} entries
                                </span>
                            </div>
                            <div>
                                {{ $this->labs->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No labs found</h5>
                            <p class="text-muted">Create your first lab to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lab Modal -->
    @if($showLabModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingLab ? 'pencil' : 'plus' }}"></i>
                            {{ $editingLab ? 'Edit' : 'Create' }} Lab
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeLabModal"></button>
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
                        
                        <form wire:submit.prevent="saveLab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.name" class="form-control @error('labForm.name') is-invalid @enderror" placeholder="Lab Name...">
                                        @error('labForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.code" class="form-control @error('labForm.code') is-invalid @enderror" placeholder="Lab Code...">
                                        @error('labForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Start Sample No <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.start_sample_no" class="form-control @error('labForm.start_sample_no') is-invalid @enderror" placeholder="Start Sample No...">
                                        @error('labForm.start_sample_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Postal Address <span class="text-danger">*</span></label>
                                        <textarea wire:model="labForm.address" class="form-control @error('labForm.address') is-invalid @enderror" rows="3" placeholder="Lab Postal Address..."></textarea>
                                        @error('labForm.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Location <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.location" class="form-control @error('labForm.location') is-invalid @enderror" placeholder="Lab Location...">
                                        @error('labForm.location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Website</label>
                                        <input type="text" wire:model="labForm.website" class="form-control" placeholder="Lab Website...">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Fax</label>
                                        <input type="text" wire:model="labForm.fax" class="form-control" placeholder="Lab Fax...">
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Email <span class="text-danger">*</span></label>
                                        <input type="email" wire:model="labForm.email" class="form-control @error('labForm.email') is-invalid @enderror" placeholder="Lab Email...">
                                        @error('labForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Phone 1 <span class="text-danger">*</span></label>
                                        <input type="tel" wire:model="labForm.phone1" class="form-control @error('labForm.phone1') is-invalid @enderror" placeholder="Lab Phone 1...">
                                        @error('labForm.phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Phone 2</label>
                                        <input type="tel" wire:model="labForm.phone2" class="form-control" placeholder="Lab Phone 2...">
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Phone 3</label>
                                        <input type="tel" wire:model="labForm.phone3" class="form-control" placeholder="Lab Phone 3...">
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="labForm.is_external" class="form-check-input" id="is_external">
                                            <label class="form-check-label" for="is_external">Is an External Lab?</label>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="labForm.active" class="form-check-input" id="active">
                                            <label class="form-check-label" for="active">Is Active?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLabModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveLab">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showLabSectionModal && $activeLabSectionLabId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content lab-section-modal">
                    <div class="modal-header lab-section-modal__header">
                        <div>
                            <div class="lab-section-modal__eyebrow">Lab Configuration</div>
                            <h5 class="modal-title lab-section-modal__title">
                                <i class="mdi mdi-{{ $editingLabSection ? 'pencil-circle-outline' : 'plus-circle-outline' }}"></i>
                                {{ $editingLabSection ? 'Edit' : 'Create' }} Lab Section
                            </h5>
                            <p class="lab-section-modal__subtitle mb-0">Define monitoring logic, expected values, and reporting rules for this lab section.</p>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeLabSectionModal"></button>
                    </div>
                    <div class="modal-body lab-section-modal__body">
                        <div class="lab-section-form-grid">
                            <section class="lab-section-panel">
                                <div class="lab-section-panel__head">
                                    <div>
                                        <p class="lab-section-panel__kicker mb-1">Identity</p>
                                        <h6 class="mb-0">Section Definition</h6>
                                    </div>
                                    <label class="lab-switch mb-0" for="modal_env_analysis">
                                        <input type="checkbox" wire:model.live="labSectionForms.{{ $activeLabSectionLabId }}.does_environmental_analysis" class="form-check-input d-none" id="modal_env_analysis">
                                        <span class="lab-switch__track"></span>
                                        <span class="lab-switch__label">Environmental Analysis</span>
                                    </label>
                                </div>

                                <div class="row">
                                    <div class="col-md-7">
                                        <label class="form-label form-label--modern">Section Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.name" class="form-control form-control--modern" placeholder="e.g. Air Quality Monitoring">
                                        @error('labSectionForms.' . $activeLabSectionLabId . '.name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label form-label--modern">Section Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.code" class="form-control form-control--modern" placeholder="AQM">
                                        @error('labSectionForms.' . $activeLabSectionLabId . '.code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label form-label--modern">Description</label>
                                        <textarea wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.description" class="form-control form-control--modern form-control--modern-textarea" rows="4" placeholder="Short scope, monitoring context, and what this section is responsible for."></textarea>
                                        @error('labSectionForms.' . $activeLabSectionLabId . '.description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </section>

                            @if(data_get($labSectionForms, $activeLabSectionLabId . '.does_environmental_analysis', false))
                                <section class="lab-section-panel lab-section-panel--accent">
                                    <div class="lab-section-panel__head">
                                        <div>
                                            <p class="lab-section-panel__kicker mb-1">Monitoring</p>
                                            <h6 class="mb-0">Environmental Analysis Setup</h6>
                                        </div>
                                        <span class="lab-section-chip">Required</span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Monitoring Equipment <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionEquipmentDropdown', true)" wire:click.outside="$set('showLabSectionEquipmentDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if($this->selectedLabSectionEquipment)
                                                <span class="tag-badge">
                                                    {{ $this->selectedLabSectionEquipment->name }}{{ $this->selectedLabSectionEquipment->equipment_number ? ' (' . $this->selectedLabSectionEquipment->equipment_number . ')' : '' }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearLabSectionEquipment"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionEquipmentSearch"
                                                   class="tag-input"
                                                   placeholder="{{ $this->selectedLabSectionEquipment ? '' : 'Search equipment...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionEquipmentDropdown)
                                                    <div class="tag-dropdown">
                                                        @forelse($this->filteredLabSectionEquipments as $equipment)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectLabSectionEquipment('{{ $equipment->id }}')">
                                                                {{ $equipment->name }}{{ $equipment->equipment_number ? ' (' . $equipment->equipment_number . ')' : '' }}
                                                            </div>
                                                        @empty
                                                            <div class="tag-dropdown-item text-muted">No equipment found</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.equipment_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Expected Value Type <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionExpectedValueTypeDropdown', true)" wire:click.outside="$set('showLabSectionExpectedValueTypeDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if($this->selectedLabSectionExpectedValueType)
                                                <span class="tag-badge">
                                                    {{ $this->selectedLabSectionExpectedValueType['label'] }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearLabSectionExpectedValueType"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionExpectedValueTypeSearch"
                                                   class="tag-input"
                                                   placeholder="{{ $this->selectedLabSectionExpectedValueType ? '' : 'Search type...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionExpectedValueTypeDropdown)
                                                    <div class="tag-dropdown">
                                                        @forelse($this->filteredLabSectionExpectedValueTypes as $option)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectLabSectionExpectedValueType('{{ $option['id'] }}')">
                                                                {{ $option['label'] }}
                                                            </div>
                                                        @empty
                                                            <div class="tag-dropdown-item text-muted">No value types found</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.expected_value_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>

                                        @if(data_get($labSectionForms, $activeLabSectionLabId . '.expected_value_type') === 'constant')
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Expected Constant <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.expected_value" class="form-control form-control--modern" placeholder="e.g. 7.0000">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.expected_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        @endif

                                        @if(data_get($labSectionForms, $activeLabSectionLabId . '.expected_value_type') === 'range')
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Expected Min <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.expected_min" class="form-control form-control--modern" placeholder="e.g. 6.5000">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.expected_min') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Expected Max <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.expected_max" class="form-control form-control--modern" placeholder="e.g. 8.5000">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.expected_max') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        @endif

                                        @if(data_get($labSectionForms, $activeLabSectionLabId . '.expected_value_type') !== 'range')
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Optimum Level <span class="text-danger">*</span></label>
                                                <input type="text" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.optimum_level" class="form-control form-control--modern" placeholder="e.g. WHO Preferred Band">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.optimum_level') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        @endif
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Result Nature <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionResultNatureDropdown', true)" wire:click.outside="$set('showLabSectionResultNatureDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if(data_get($labSectionForms, $activeLabSectionLabId . '.result_nature'))
                                                <span class="tag-badge">
                                                    {{ data_get($labSectionForms, $activeLabSectionLabId . '.result_nature') }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.result_nature', null)"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionResultNatureSearch"
                                                   class="tag-input"
                                                   placeholder="{{ data_get($labSectionForms, $activeLabSectionLabId . '.result_nature') ? '' : 'Select nature...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionResultNatureDropdown)
                                                    <div class="tag-dropdown">
                                                        @foreach(['Qualitative', 'Quantitative'] as $nature)
                                                            <div class="tag-dropdown-item" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.result_nature', '{{ $nature }}')">
                                                                {{ $nature }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.result_nature') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Reporting Unit <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionReportingUnitDropdown', true)" wire:click.outside="$set('showLabSectionReportingUnitDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if($this->selectedLabSectionReportingUnit)
                                                <span class="tag-badge">
                                                    {{ $this->selectedLabSectionReportingUnit->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.reporting_unit', '')"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionReportingUnitSearch"
                                                   class="tag-input"
                                                   placeholder="{{ $this->selectedLabSectionReportingUnit ? '' : 'Search reporting unit...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionReportingUnitDropdown)
                                                    <div class="tag-dropdown">
                                                        @forelse($this->filteredLabSectionReportingUnits as $unit)
                                                            <div class="tag-dropdown-item" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.reporting_unit', '{{ $unit->id }}')">
                                                                {{ $unit->name }}
                                                            </div>
                                                        @empty
                                                            <div class="tag-dropdown-item text-muted">No active reporting units found</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.reporting_unit') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </section>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer lab-section-modal__footer">
                        <button type="button" class="btn btn-outline-secondary btn-modal-soft" wire:click="resetLabSectionForm('{{ $activeLabSectionLabId }}')">Reset</button>
                        <button type="button" class="btn btn-secondary" wire:click="closeLabSectionModal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-modal-primary" wire:click="saveLabSection('{{ $activeLabSectionLabId }}')">
                            <i class="mdi mdi-content-save"></i> Save Section
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- DELETE CONFIRMATION MODAL -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-alert-circle"></i>
                            Confirm Delete
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i> 
                            <strong>Warning:</strong> This action cannot be undone. The {{ $deleteType === 'lab_section' ? 'lab section' : 'lab' }} will be permanently deleted from the database.
                        </div>

                        <p class="mb-3">Are you sure you want to delete the following <strong>{{ $deleteType === 'lab_section' ? 'Lab Section' : 'Lab' }}</strong>?</p>
                        
                        <div class="card">
                            <div class="card-body bg-light">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th style="width: 35%;">Name:</th>
                                        <td><strong>{{ $deleteDetails['name'] ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Code:</th>
                                        <td>{{ $deleteDetails['code'] ?? '-' }}</td>
                                    </tr>
                                    @if($deleteType === 'lab_section')
                                        <tr>
                                            <th>Description:</th>
                                            <td>{{ $deleteDetails['description'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Environmental Analysis:</th>
                                            <td>{{ $deleteDetails['environmental_analysis'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Equipment:</th>
                                            <td>{{ $deleteDetails['equipment'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Expected Value:</th>
                                            <td>{{ $deleteDetails['expected_value'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Result Nature:</th>
                                            <td>{{ $deleteDetails['result_nature'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reporting Unit:</th>
                                            <td>{{ $deleteDetails['reporting_unit'] ?? '-' }}</td>
                                        </tr>
                                    @else
                                    <tr>
                                        <th>Start Sample No:</th>
                                        <td>{{ $deleteDetails['start_sample_no'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $deleteDetails['address'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $deleteDetails['email'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone 1:</th>
                                        <td>{{ $deleteDetails['phone1'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Lab Type:</th>
                                        <td>{{ $deleteDetails['is_external'] ?? '-' }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Status:</th>
                                        <td>{{ $deleteDetails['active'] ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteLab">
                            <i class="mdi mdi-delete"></i> Yes, Delete Permanently
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

    .tag-select-container {
        position: relative;
        cursor: text;
    }

    .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .tag-select-input:hover {
        border-color: #93c5fd;
    }

    .tag-select-input:focus-within {
        border-color: #93c5fd;
        box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.15);
    }

    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .tag-badge i {
        cursor: pointer;
        font-size: 14px;
    }

    .tag-input {
        flex: 1;
        min-width: 120px;
        border: none;
        outline: none;
        padding: 4px;
        font-size: 14px;
    }

    .tag-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #93c5fd;
        border-top: none;
        border-radius: 0 0 10px 10px;
        max-height: 220px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
        margin-top: -1px;
    }

    .tag-dropdown-item {
        padding: 9px 12px;
        cursor: pointer;
        transition: background-color 0.15s ease;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .tag-dropdown-item:hover {
        background-color: #eff6ff;
    }

    .tag-dropdown-item:last-child {
        border-bottom: none;
    }

    .modern-filter-tag-input {
        min-height: 44px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .modern-filter-tag-input:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
    }

    .modern-filter-tag-input:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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

    /* Action Button Styling */
    .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .rm-act-btn:last-child {
        margin-right: 0;
    }

    .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #991b1b;
        background: #fee2e2;
    }

    .rm-act-btn--delete:hover {
        background: #fecaca;
        border-color: #fca5a5;
    }

    .rm-act-btn--expand {
        border: 1px solid #d1fae5;
        color: #047857;
        background: #ecfdf5;
    }

    .rm-act-btn--expand:hover {
        background: #d1fae5;
        border-color: #6ee7b7;
    }

    .lab-table {
        min-width: 1300px;
    }

    .lab-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid transparent;
        line-height: 1;
        white-space: nowrap;
    }

    .lab-badge i {
        font-size: 14px;
    }

    .lab-badge--internal {
        color: #166534;
        background: #dcfce7;
        border-color: #86efac;
    }

    .lab-badge--external {
        color: #0f766e;
        background: #ccfbf1;
        border-color: #99f6e4;
    }

    .lab-badge--active {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }

    .lab-badge--inactive {
        color: #991b1b;
        background: #fee2e2;
        border-color: #fca5a5;
    }

    .lab-badge--info {
        color: #1e3a8a;
        background: #dbeafe;
        border-color: #93c5fd;
    }

    .lab-expand-row {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .section-shell {
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        padding: 16px;
    }

    .section-shell__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
        gap: 16px;
    }

    .section-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        height: 100%;
    }

    .section-card__top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .section-card__actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .section-card__action-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .section-code {
        margin-bottom: 4px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #475569;
        font-weight: 700;
    }

    .section-meta {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .section-meta li {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 12px;
        border-bottom: 1px dashed #e2e8f0;
        padding: 6px 0;
    }

    .section-meta li:last-child {
        border-bottom: none;
    }

    .section-meta li span {
        color: #64748b;
    }

    .section-meta li strong {
        color: #0f172a;
        text-align: right;
        max-width: 58%;
        word-break: break-word;
    }

    .empty-section-state {
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 18px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
    }

    .empty-section-state i {
        font-size: 24px;
        display: block;
        margin-bottom: 6px;
    }

    .section-add-btn {
        white-space: nowrap;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
    }

    .lab-section-modal {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 32px 70px rgba(15, 23, 42, 0.22);
    }

    .lab-section-modal__header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 34%),
            linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .lab-section-modal__eyebrow {
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #2563eb;
    }

    .lab-section-modal__title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
    }

    .lab-section-modal__title i {
        color: #2563eb;
        font-size: 1.3rem;
    }

    .lab-section-modal__subtitle {
        max-width: 640px;
        color: #475569;
        font-size: 0.93rem;
    }

    .lab-section-modal__body {
        padding: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .lab-section-form-grid {
        display: grid;
        gap: 18px;
    }

    .lab-section-panel {
        padding: 24px 24px 28px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .lab-section-panel--accent {
        border-color: #bfdbfe;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .lab-section-panel .row > [class*="col-"] {
        margin-bottom: 1.4rem;
    }

    .lab-section-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
    }

    .lab-section-panel__head h6 {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
    }

    .lab-section-panel__kicker {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #64748b;
    }

    .lab-section-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .form-label--modern {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #334155;
    }

    .form-control--modern {
        min-height: 48px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        padding: 12px 14px;
        color: #0f172a;
        background: #ffffff;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .form-control--modern:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    .form-control--modern::placeholder {
        color: #94a3b8;
    }

    .form-control--modern-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .lab-tag-select-input {
        min-height: 48px;
        padding: 8px 12px;
        border-radius: 14px;
        background: #ffffff;
    }

    .lab-switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }

    .lab-switch__track {
        position: relative;
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background-color 0.2s ease;
    }

    .lab-switch__track::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
        transition: transform 0.2s ease;
    }

    .lab-switch input:checked + .lab-switch__track {
        background: #2563eb;
    }

    .lab-switch input:checked + .lab-switch__track::after {
        transform: translateX(20px);
    }

    .lab-switch__label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #334155;
    }

    .lab-section-modal__footer {
        padding: 18px 24px 24px;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .btn-modal-soft {
        border-radius: 12px;
        padding-inline: 16px;
    }

    .btn-modal-primary {
        border-radius: 12px;
        padding-inline: 18px;
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.2);
    }

    @media (max-width: 768px) {
        .section-shell__header {
            flex-direction: column;
            align-items: flex-start;
        }

        .lab-section-panel__head {
            flex-direction: column;
            align-items: flex-start;
        }

        .lab-table {
            min-width: 1100px;
        }
    }
    </style>
</div>
