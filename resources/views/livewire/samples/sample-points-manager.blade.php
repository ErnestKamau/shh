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
                                {{ $customer->sample_point_configurable_name ?: 'Sample Points' }} Management
                            </h2>
                            <p class="text-muted mb-0">Manage {{ strtolower($customer->sample_point_configurable_name ?: 'sample points') }} for: <strong>{{ $customer->name }}</strong></p>
                        </div>
                        <button wire:click="showCreateSamplePointModal" class="btn btn-primary">
                <i class="mdi mdi-plus"></i> Add {{ $customer->sample_point_configurable_name ?: 'Sample Point' }}
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
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by master {{ strtolower($customer->sample_point_configurable_name ?: 'sample point') }} name or code...">
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

    <!-- Sample Points Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->samplePoints->flatten()->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->samplePoints as $areaId => $points)
                                        @if($areaId)
                                            @php
                                                $area = $points->first()->area;
                                            @endphp
                                            <!-- Area Header Row -->
                                            <tr>
                                                <td colspan="3" class="bg-light fw-bold" style="padding: 12px 15px;">
                                                    <i class="mdi mdi-map-marker text-primary"></i>
                                                    <strong>Area:</strong> {{ $area->crmArea->name ?? 'N/A' }} ({{ $area->crmArea->code ?? 'N/A' }})
                                                    <span class="ms-3">
                                                        <i class="mdi mdi-file-tree text-warning"></i>
                                                        <strong>Sub Unit:</strong> {{ $area->subUnit->name ?? 'N/A' }}
                                                    </span>
                                                    <span class="ms-3">
                                                        <i class="mdi mdi-office-building text-info"></i>
                                                        <strong>Unit:</strong> {{ $area->companyUnit->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                        
                                        <!-- Sample Points under this Area -->
                                        @foreach($points as $samplePoint)
                                            <tr>
                                                <td>
                                                    @if($samplePoint->crmSamplePoint)
                                                        {{ $samplePoint->crmSamplePoint->name }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($samplePoint->crmSamplePoint)
                                                        {{ $samplePoint->crmSamplePoint->code }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditSamplePointModal({{ $samplePoint->id }})" 
                                                                class="btn btn-sm btn-outline-warning me-1" 
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="deleteSamplePoint({{ $samplePoint->id }})" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this sample point?')">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-map-marker text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No {{ strtolower($customer->sample_point_configurable_name ?: 'sample points') }} found</h5>
                            <p class="text-muted">Start by adding your first {{ strtolower($customer->sample_point_configurable_name ?: 'sample point') }}.</p>
                        </div>
                    @endif
                </div>
            </div>
    </div>
</div>

<!-- Sample Point Modal -->
    @if($showSamplePointModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingSamplePoint ? 'pencil' : 'plus' }}"></i>
                            {{ $editingSamplePoint ? 'Edit' : 'Create' }} {{ $customer->sample_point_configurable_name ?: 'Sample Point' }}
                    </h5>
                        <button type="button" class="btn-close" wire:click="closeSamplePointModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveSamplePoint">
                            <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-office-building text-primary"></i> Company Unit <span class="text-danger">*</span></label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('samplePointForm.unit_id').live,
                                            units: {{ json_encode($units->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()) }},
                                            get filteredUnits() {
                                                if (!this.search) return this.units.slice(0, 50);
                                                return this.units.filter(unit => 
                                                    unit.name.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            selectUnit(unitId) {
                                                this.selected = unitId;
                                                this.open = false;
                                                this.search = '';
                                            },
                                            getSelectedName() {
                                                const unit = this.units.find(u => u.id == this.selected);
                                                return unit ? unit.name : '';
                                            }
                                        }" class="searchable-dropdown-wrapper">
                                            <div class="single-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected ? getSelectedName() : 'Search company units...'"
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
                                                <template x-if="filteredUnits.length > 0">
                                                    <div class="options-list">
                                                        <template x-for="unit in filteredUnits" :key="unit.id">
                                                            <div @click="selectUnit(unit.id)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': selected == unit.id }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="selected == unit.id"></i>
                                                                <span x-text="unit.name"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="filteredUnits.length === 0">
                                                    <div class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>No units found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('samplePointForm.unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-map-marker text-info"></i> Area</label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('samplePointForm.area_id').live,
                                            areas: {{ json_encode($areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'code' => $a->code])->values()) }},
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
                                                    :placeholder="selected ? getSelectedLabel() : 'Select Area (Optional)'"
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
                                                        <span>No areas found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('samplePointForm.area_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                            </div>
                        </div>

                        <!-- Company Sub Unit -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><i class="mdi mdi-file-tree text-warning"></i> Company Sub Unit</label>
                                    <div x-data="{
                                        open: false,
                                        search: '',
                                        selected: @entangle('samplePointForm.sub_unit_id').live,
                                        subUnits: {{ json_encode($subUnits->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()) }},
                                        get filteredSubUnits() {
                                            if (!this.search) return this.subUnits.slice(0, 50);
                                            return this.subUnits.filter(subUnit => 
                                                subUnit.name.toLowerCase().includes(this.search.toLowerCase())
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
                                        getSelectedName() {
                                            if (!this.selected) return '';
                                            const subUnit = this.subUnits.find(s => s.id == this.selected);
                                            return subUnit ? subUnit.name : '';
                                        }
                                    }" class="searchable-dropdown-wrapper">
                                        <div class="single-select-container" @click="open = !open">
                                            <input 
                                                type="text" 
                                                x-model="search"
                                                :placeholder="selected ? getSelectedName() : 'Select Sub Unit (Optional)'"
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
                                                            <span x-text="subUnit.name"></span>
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
                                    @error('samplePointForm.sub_unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><i class="mdi mdi-database text-success"></i> Master Sample Point <span class="text-danger">*</span></label>
                                    <div x-data="{
                                        open: false,
                                        search: '',
                                        selected: @entangle('samplePointForm.crm_sample_point_id').live,
                                        points: {{ json_encode($masterSamplePoints->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->code])->values()) }},
                                        get filteredPoints() {
                                            if (!this.search) return this.points.slice(0, 50);
                                            return this.points.filter(point => 
                                                point.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                                point.code.toLowerCase().includes(this.search.toLowerCase())
                                            );
                                        },
                                        selectPoint(pointId) {
                                            this.selected = pointId;
                                            this.open = false;
                                            this.search = '';
                                        },
                                        clearSelection() {
                                            this.selected = null;
                                            this.search = '';
                                        },
                                        getSelectedLabel() {
                                            if (!this.selected) return '';
                                            const point = this.points.find(p => p.id == this.selected);
                                            return point ? `${point.name} (${point.code})` : '';
                                        }
                                    }" class="searchable-dropdown-wrapper">
                                        <div class="single-select-container" @click="open = !open">
                                            <input 
                                                type="text" 
                                                x-model="search"
                                                :placeholder="selected ? getSelectedLabel() : 'Select Master Sample Point (Optional)'"
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
                                            <template x-if="filteredPoints.length > 0">
                                                <div class="options-list">
                                                    <template x-for="point in filteredPoints" :key="point.id">
                                                        <div @click="selectPoint(point.id)" 
                                                             class="option-item"
                                                             :class="{ 'selected': selected == point.id }">
                                                            <i class="mdi mdi-check-circle text-primary" x-show="selected == point.id"></i>
                                                            <span x-text="`${point.name} (${point.code})`"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="filteredPoints.length === 0">
                                                <div class="no-results">
                                                    <i class="mdi mdi-alert-circle-outline"></i>
                                                    <span>No master sample points found</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    @error('samplePointForm.crm_sample_point_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Global Area -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label"><i class="mdi mdi-map text-danger"></i> Global Area</label>
                                    <div x-data="{
                                        open: false,
                                        search: '',
                                        selected: @entangle('samplePointForm.crm_area_id').live,
                                        areas: {{ json_encode($globalAreas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'code' => $a->code])->values()) }},
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
                                                :placeholder="selected ? getSelectedLabel() : 'Select Global Area (Optional)'"
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
                                                    <span>No global areas found</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    @error('samplePointForm.crm_area_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                            <div class="row">
                                <div class="col-md-12">
                        <div class="form-group mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="samplePointForm.active" class="form-check-input" id="sample_point_active">
                                            <label class="form-check-label" for="sample_point_active">Active</label>
                                        </div>
                                    </div>
                        </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeSamplePointModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveSamplePoint">
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