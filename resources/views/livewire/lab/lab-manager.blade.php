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
                        @can('laboratory.components.labs.edit')
                            <button wire:click="showCreateLabModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Lab
                            </button>
                        @endcan
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
                                                    <a href="{{ route('livewire.labs.show', $lab) }}"
                                                       class="rm-act-btn rm-act-btn--view"
                                                       title="View lab profile">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="toggleLabRow('{{ $lab->id }}')"
                                                            class="rm-act-btn rm-act-btn--expand"
                                                            title="{{ in_array($lab->id, $expandedLabs, true) ? 'Collapse' : 'Expand' }}">
                                                        <i class="mdi mdi-chevron-{{ in_array($lab->id, $expandedLabs, true) ? 'up' : 'down' }}"></i>
                                                    </button>
                                                    @can('laboratory.components.labs.edit')
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
                                                    @endcan
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
                                                    @include('livewire.lab.partials.lab-section-cards', ['lab' => $lab])
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


    @include('livewire.lab.partials.lab-section-modal')

    @include('livewire.lab.partials.lab-delete-modal', ['confirmMethod' => 'deleteLab'])

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
    </style>

    @include('livewire.lab.partials.lab-config-styles')
</div>
