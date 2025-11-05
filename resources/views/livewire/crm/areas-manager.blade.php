<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-map-marker-multiple text-primary"></i>
                                {{ $customer->area_configurable_name ?: 'Areas' }} Management
                            </h2>
                            <p class="text-muted mb-0">Manage {{ strtolower($customer->area_configurable_name ?: 'areas') }} for: <strong>{{ $customer->name }}</strong></p>
                        </div>
                        <div class="float-right">
                            <button wire:click="showCreateAreaModal" class="btn btn-sm btn-primary" wire:loading.attr="disabled" wire:target="showCreateAreaModal">
                                <span wire:loading.remove wire:target="showCreateAreaModal">
                                    <i class="mdi mdi-plus"></i> Add {{ $customer->area_configurable_name ?: 'Area' }}
                                </span>
                                <span wire:loading wire:target="showCreateAreaModal">
                                    <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                </span>
                            </button>
                            <button wire:click="showCloneModalMethod" class="btn btn-sm btn-success ms-2" wire:loading.attr="disabled" wire:target="showCloneModalMethod">
                                <span wire:loading.remove wire:target="showCloneModalMethod">
                                    <i class="mdi mdi-content-copy"></i> Clone {{ $customer->area_configurable_name ?: 'Areas' }}
                                </span>
                                <span wire:loading wire:target="showCloneModalMethod">
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by area name, code, or description...">
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
                        <div class="col-md-3">
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

    <!-- Areas Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->areas->count() > 0)
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
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>CRM Area</th>
                                        <th>Sub Unit</th>
                                        <th>Description</th>
                                        <th>Sample Points</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->areas as $area)
                                        <tr>
                                            <td>
                                                <strong>{{ $area->crmArea->name ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $area->crmArea->code ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info p-2" style="color: white;">{{ $area->subUnit->name ?? 'N/A' }} - {{ $area->companyUnit->name ?? '' }}</span>
                                            </td>
                                            <td>
                                                {{ Str::limit($area->description, 50) ?: 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary" style="color: white;">{{ $area->samplePoints()->count() }}</span>
                                            </td>
                                            <td>
                                                @if($area->active)
                                                    <span class="badge bg-success p-2" style="color: white;">Active</span>
                                                @else
                                                    <span class="badge bg-danger p-2" style="color: white;">Inactive</span>
                                                @endif
                                            </td>
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
                                                    <button wire:click="showDeleteConfirmation({{ $area->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            title="Delete"
                                                            wire:loading.attr="disabled"
                                                            wire:target="showDeleteConfirmation({{ $area->id }})">
                                                        <span wire:loading.remove wire:target="showDeleteConfirmation({{ $area->id }})">
                                                            <i class="mdi mdi-delete"></i>
                                                        </span>
                                                        <span wire:loading wire:target="showDeleteConfirmation({{ $area->id }})">
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
                            <i class="mdi mdi-map-marker-multiple text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No {{ strtolower($customer->area_configurable_name ?: 'areas') }} found</h5>
                            <p class="text-muted">Start by adding your first {{ strtolower($customer->area_configurable_name ?: 'area') }}.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Area Modal -->
    @if($showAreaModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingArea ? 'pencil' : 'plus' }}"></i>
                            {{ $editingArea ? 'Edit' : 'Create' }} {{ $customer->area_configurable_name ?: 'Area' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeAreaModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveArea">
                            <!-- Customer Areas Selection -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-map text-primary"></i> Customer Areas <span class="text-danger">*</span></label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('areaForm.crm_area_id').live,
                                            areas: {{ json_encode($customerAreas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'code' => $a->code])->values()) }},
                                            get filteredAreas() {
                                                if (!this.search) return this.areas.slice(0, 50);
                                                return this.areas.filter(area => 
                                                    area.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                                    area.code.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            selectArea(areaId) {
                                                this.selected = areaId;
                                                this.open = false;
                                                this.search = '';
                                            },
                                            clearSelection() {
                                                this.selected = null;
                                                this.search = '';
                                            },
                                            getSelectedLabel() {
                                                if (!this.selected) return '';
                                                const area = this.areas.find(a => a.id == this.selected);
                                                return area ? `${area.name} (${area.code})` : '';
                                            }
                                        }" class="searchable-dropdown-wrapper">
                                            <div class="single-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected ? getSelectedLabel() : 'Select Customer Area'"
                                                    @focus="open = true"
                                                    class="form-control searchable-input-single"
                                                    autocomplete="off"
                                                >
                                                <button type="button" @click.stop="clearSelection()" x-show="selected" class="btn btn-sm btn-link position-absolute" style="right: 35px; top: 50%; transform: translateY(-50%); padding: 0; color: #dc3545;">
                                                    <i class="mdi mdi-close-circle"></i>
                                                </button>
                                                <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                            </div>

                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 x-transition
                                                 class="dropdown-list">
                                                <template x-if="filteredAreas.length > 0">
                                                    <div class="options-list">
                                                        <template x-for="area in filteredAreas" :key="area.id">
                                                            <div @click="selectArea(area.id)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': selected == area.id }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="selected == area.id"></i>
                                                                <span x-text="`${area.name} (${area.code})`"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="filteredAreas.length === 0">
                                                    <div class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>No customer areas found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('areaForm.crm_area_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Company Sub Unit Selection -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-office-building text-success"></i> Company Sub Unit <span class="text-danger">*</span></label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('areaForm.crm_company_sub_unit_id').live,
                                            subUnits: {{ json_encode($companySubUnits->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code])->values()) }},
                                            get filteredSubUnits() {
                                                if (!this.search) return this.subUnits.slice(0, 50);
                                                return this.subUnits.filter(subUnit => 
                                                    subUnit.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                                    subUnit.code.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            selectSubUnit(subUnitId) {
                                                this.selected = subUnitId;
                                                this.open = false;
                                                this.search = '';
                                            },
                                            clearSelection() {
                                                this.selected = null;
                                                this.search = '';
                                            },
                                            getSelectedLabel() {
                                                if (!this.selected) return '';
                                                const subUnit = this.subUnits.find(s => s.id == this.selected);
                                                return subUnit ? `${subUnit.name} (${subUnit.code})` : '';
                                            }
                                        }" class="searchable-dropdown-wrapper">
                                            <div class="single-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected ? getSelectedLabel() : 'Select Company Sub Unit'"
                                                    @focus="open = true"
                                                    class="form-control searchable-input-single"
                                                    autocomplete="off"
                                                >
                                                <button type="button" @click.stop="clearSelection()" x-show="selected" class="btn btn-sm btn-link position-absolute" style="right: 35px; top: 50%; transform: translateY(-50%); padding: 0; color: #dc3545;">
                                                    <i class="mdi mdi-close-circle"></i>
                                                </button>
                                                <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                            </div>

                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 x-transition
                                                 class="dropdown-list">
                                                <template x-if="filteredSubUnits.length > 0">
                                                    <div class="options-list">
                                                        <template x-for="subUnit in filteredSubUnits" :key="subUnit.id">
                                                            <div @click="selectSubUnit(subUnit.id)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': selected == subUnit.id }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="selected == subUnit.id"></i>
                                                                <span x-text="`${subUnit.name} (${subUnit.code})`"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="filteredSubUnits.length === 0">
                                                    <div class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>No sub units found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('areaForm.crm_company_sub_unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea wire:model="areaForm.description" class="form-control" rows="3" placeholder="Enter area description"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Points Multi-Select -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-map-marker text-success"></i> Sample Points</label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('areaForm.selectedSamplePoints').live,
                                            points: {{ json_encode($masterSamplePoints->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->code])->values()) }},
                                            get filteredPoints() {
                                                if (!this.search) return this.points;
                                                return this.points.filter(point => 
                                                    point.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                                    point.code.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            togglePoint(pointId) {
                                                const index = this.selected.indexOf(pointId);
                                                if (index > -1) {
                                                    this.selected.splice(index, 1);
                                                } else {
                                                    this.selected.push(pointId);
                                                }
                                            },
                                            isSelected(pointId) {
                                                return this.selected.includes(pointId);
                                            },
                                            getSelectedNames() {
                                                if (this.selected.length === 0) return '';
                                                const names = this.selected.map(id => {
                                                    const point = this.points.find(p => p.id == id);
                                                    return point ? point.name : '';
                                                }).filter(n => n);
                                                return names.length > 3 ? `${names.slice(0, 3).join(', ')} +${names.length - 3} more` : names.join(', ');
                                            }
                                        }" class="searchable-dropdown-wrapper">
                                            <div class="multi-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected.length > 0 ? getSelectedNames() : 'Select sample points...'"
                                                    @focus="open = true"
                                                    class="form-control searchable-input-single"
                                                    autocomplete="off"
                                                >
                                                <span class="selected-count" x-show="selected.length > 0" x-text="selected.length"></span>
                                                <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                            </div>

                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 x-transition
                                                 class="dropdown-list">
                                                <template x-if="filteredPoints.length > 0">
                                                    <div class="options-list">
                                                        <template x-for="point in filteredPoints" :key="point.id">
                                                            <div @click="togglePoint(point.id)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': isSelected(point.id) }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="isSelected(point.id)"></i>
                                                                <div class="d-flex flex-column flex-grow-1">
                                                                    <span x-text="point.name" class="fw-bold"></span>
                                                                    <small class="text-muted" x-text="`Code: ${point.code}`"></small>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="filteredPoints.length === 0">
                                                    <div class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>No sample points found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="mdi mdi-information-outline"></i> Select sample points to associate with this area
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="areaForm.active" class="form-check-input" id="area_active">
                                            <label class="form-check-label" for="area_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAreaModal" wire:loading.attr="disabled" wire:target="saveArea">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveArea" wire:loading.attr="disabled" wire:target="saveArea">
                            <span wire:loading.remove wire:target="saveArea">
                                <i class="mdi mdi-content-save"></i> Save
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

    <!-- Clone Areas Modal -->
    @if($showCloneModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-content-copy"></i>
                            Clone {{ $customer->area_configurable_name ?: 'Areas' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCloneModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="cloneAreas">
                            <!-- Clone From/To Sub Units -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold"><i class="mdi mdi-source-branch text-primary"></i> Clone From {{ $customer->sub_unit_configurable_name ?: 'Sub Unit' }} <span class="text-danger">*</span></label>
                                        <select wire:model.live="cloneFromSubUnitId" class="form-select modern-select">
                                            <option value="">Select {{ strtolower($customer->sub_unit_configurable_name ?: 'sub unit') }} to clone from</option>
                                            @foreach($companySubUnits as $subUnit)
                                                <option value="{{ $subUnit->id }}">{{ $subUnit->name }} - {{ $subUnit->companyUnit->name ?? '' }}</option>
                                            @endforeach
                                        </select>
                                        @error('cloneFromSubUnitId') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold"><i class="mdi mdi-target text-success"></i> Clone To {{ $customer->sub_unit_configurable_name ?: 'Sub Unit' }} <span class="text-danger">*</span></label>
                                        <select wire:model="cloneToSubUnitId" class="form-select modern-select">
                                            <option value="">Select {{ strtolower($customer->sub_unit_configurable_name ?: 'sub unit') }} to clone to</option>
                                            @foreach($companySubUnits as $subUnit)
                                                @if($subUnit->id != $cloneFromSubUnitId)
                                                    <option value="{{ $subUnit->id }}">{{ $subUnit->name }} - {{ $subUnit->companyUnit->name ?? '' }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('cloneToSubUnitId') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Areas List with Checkboxes -->
                            @if(count($availableAreasToClone) > 0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-checkbox-multiple-marked text-info"></i> 
                                                Select {{ $customer->area_configurable_name ?: 'Areas' }} to Clone 
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="card">
                                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                                    @foreach($availableAreasToClone as $area)
                                                        <div class="form-check mb-2">
                                                            <input 
                                                                type="checkbox" 
                                                                wire:model="selectedAreasToClone" 
                                                                value="{{ $area['id'] }}" 
                                                                class="form-check-input" 
                                                                id="area_{{ $area['id'] }}">
                                                            <label class="form-check-label" for="area_{{ $area['id'] }}">
                                                                <strong>{{ $area['area_name'] }}</strong>
                                                                <span class="badge bg-info ms-2">{{ $area['area_code'] }}</span>
                                                                @if($area['description'] && $area['description'] != 'N/A')
                                                                    <br><small class="text-muted">{{ Str::limit($area['description'], 60) }}</small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @error('selectedAreasToClone') <span class="text-danger">{{ $message }}</span> @enderror
                                            <small class="form-text text-muted mt-2 d-block">
                                                <i class="mdi mdi-information-outline"></i> {{ count($selectedAreasToClone) }} of {{ count($availableAreasToClone) }} area(s) selected
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Clone Options -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card bg-light">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="mdi mdi-tune text-warning"></i> Clone Options</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model="includeSamplePoints" 
                                                        class="form-check-input" 
                                                        id="includeSamplePoints">
                                                    <label class="form-check-label" for="includeSamplePoints">
                                                        <i class="mdi mdi-map-marker text-success"></i>
                                                        <strong>Include Sample Points</strong>
                                                        <small class="text-muted d-block">Clone all sample points from selected areas</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($cloneFromSubUnitId)
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information-outline"></i>
                                    No areas found for the selected {{ strtolower($customer->sub_unit_configurable_name ?: 'sub unit') }}.
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert-outline"></i>
                                    Please select a {{ strtolower($customer->sub_unit_configurable_name ?: 'sub unit') }} to clone from to see available areas.
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCloneModal" wire:loading.attr="disabled" wire:target="cloneAreas">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-success" 
                            wire:click="cloneAreas" 
                            wire:loading.attr="disabled" 
                            wire:target="cloneAreas"
                            @if(count($selectedAreasToClone) == 0 || !$cloneFromSubUnitId || !$cloneToSubUnitId) disabled @endif>
                            <span wire:loading.remove wire:target="cloneAreas">
                                <i class="mdi mdi-content-copy"></i> Clone Selected
                            </span>
                            <span wire:loading wire:target="cloneAreas">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Cloning...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmModal && $areaToDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-alert"></i>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteConfirmModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-outline"></i>
                            <strong>Warning!</strong> This action cannot be undone.
                        </div>
                        
                        <p class="mb-3">
                            You are about to delete the area: 
                            <strong>{{ $areaToDelete->crmArea->name ?? 'N/A' }}</strong>
                        </p>
                        
                        @if($samplePointsCount > 0)
                            <div class="alert alert-danger">
                                <i class="mdi mdi-map-marker-alert"></i>
                                <strong>This will also delete {{ $samplePointsCount }} sample point(s)</strong> associated with this area.
                            </div>
                        @else
                            <p class="text-muted">This area has no associated sample points.</p>
                        @endif
                        
                        <p class="mb-0">Are you sure you want to proceed?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteConfirmModal" wire:loading.attr="disabled" wire:target="confirmDeleteArea">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="confirmDeleteArea" wire:loading.attr="disabled" wire:target="confirmDeleteArea">
                            <span wire:loading.remove wire:target="confirmDeleteArea">
                                <i class="mdi mdi-delete"></i> Yes, Delete
                            </span>
                            <span wire:loading wire:target="confirmDeleteArea">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Deleting...
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

    /* Prevent body scroll when modal is open */
    body.modal-open {
        overflow: hidden;
    }

    /* Ensure modal is properly positioned and scrollable */
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }

    /* Smooth scrolling for modal content */
    .modal-body {
        scroll-behavior: smooth;
    }

    /* Ensure modal backdrop doesn't interfere with scrolling */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.5);
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
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .options-list::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
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
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .dropdown-list::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
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
</div>
