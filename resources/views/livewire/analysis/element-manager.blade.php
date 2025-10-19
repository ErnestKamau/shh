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
                                Analysis Elements Management
                            </h2>
                            <p class="text-muted mb-0">Manage analysis elements for this analysis type</p>
                        </div>
                        <button wire:click="showCreateElementModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Element
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by analyte name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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

    <!-- Elements Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->elements->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->elements->firstItem() ?? 0 }} to {{ $this->elements->lastItem() ?? 0 }} of {{ $this->elements->total() }} entries
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
                            <table class="table table-striped table-hover" id="elements-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">
                                            <i class="mdi mdi-drag text-muted"></i>
                                        </th>
                                        <th>Level</th>
                                        <th>Analyte</th>
                                        <th>Method</th>
                                        <th>Equipment</th>
                                        <th>Operator</th>
                                        <th>Reporting Unit</th>
                                        <th>LOD</th>
                                        <th>HOD</th>
                                        <th>Remedy</th>
                                        <th>Calculated</th>
                                        <th>Method Sequence</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-elements">
                                    @foreach($this->elements as $element)
                                        <tr class="sortable-row" data-element-id="{{ $element->id }}">
                                            <td class="drag-handle text-center">
                                                <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                            </td>
                                            <td>
                                                <span class="badge badge-info p-2">{{ $element->level ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $element->analyte->name ?? 'N/A' }}</td>
                                            <td>{{ $element->mmethod->name ?? $element->ltmethod->name ?? 'N/A' }}</td>
                                            <td>{{ $element->equipment->name ?? 'N/A' }}</td>
                                            <td>{{ $element->operator->name ?? 'N/A' }}</td>
                                            <td>{{ $element->reporting_unit }}</td>
                                            <td>{{ $element->lod ?? 'N/A' }}</td>
                                            <td>{{ $element->hod ?? 'N/A' }}</td>
                                            <td>
                                                @if($element->recommend_remedies)
                                                    <span class="badge badge-warning p-2" title="Remedy Recommended">
                                                        <i class="mdi mdi-medical-bag"></i> Yes
                                                    </span>
                                                    @if($element->remedyHeader)
                                                        <br><small class="text-muted">{{ $element->remedyHeader->name }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary p-2">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($element->result_is_calculated)
                                                    <span class="badge badge-info p-2" title="Result is Calculated">
                                                        <i class="mdi mdi-calculator"></i> Yes
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary p-2">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($element->has_method_sequence)
                                                    <span class="badge badge-info p-2" title="Has Method Sequence">
                                                        <i class="mdi mdi-timeline-check"></i> Yes
                                                    </span>
                                                    @if($element->methodSequence)
                                                        <br><small class="text-muted">
                                                            {{ $element->methodSequence->name }}
                                                            @php
                                                                $activeVersion = $element->methodSequence->activeVersion->first();
                                                                $latestVersion = $element->methodSequence->latestVersion->first();
                                                            @endphp
                                                            @if($activeVersion)
                                                                <br><span class="badge badge-success badge-sm">v{{ $activeVersion->version_number }} - Active</span>
                                                            @elseif($latestVersion)
                                                                <br><span class="badge badge-warning badge-sm">v{{ $latestVersion->version_number }} - Latest</span>
                                                            @endif
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary p-2">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($element->active)
                                                    <span class="badge badge-success p-2 ">Active</span>
                                                @else
                                                    <span class="badge badge-secondary p-2">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditElementModal({{ $element->id }})" 
                                                            class="btn btn-sm mr-2 btn-outline-warning" 
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
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->elements->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No elements found</h5>
                            <p class="text-muted">Start by adding your first analysis element.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Element Modal -->
    @if($showElementModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingElement ? 'pencil' : 'plus' }}"></i>
                            {{ $editingElement ? 'Edit' : 'Create' }} Analysis Element
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeElementModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveElement">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-flask-outline text-primary"></i> Analyte <span class="text-danger">*</span>
                                        </label>
                                        <div class="searchable-select-container">
                                            <input type="text" 
                                                   wire:model.live="analyteSearch" 
                                                   wire:keyup="searchAnalytes"
                                                   class="form-control @error('elementForm.analyte_id') is-invalid @enderror" 
                                                   placeholder="Type to search analytes..."
                                                   autocomplete="off"
                                                   style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                            <input type="hidden" wire:model="elementForm.analyte_id">
                                            
                                            @if($showAnalyteDropdown && count($filteredAnalytes) > 0)
                                                <div class="searchable-dropdown">
                                                    @foreach($filteredAnalytes as $analyte)
                                                        <div class="dropdown-item" 
                                                             wire:click="selectAnalyte({{ $analyte->id }}, '{{ $analyte->name }}')"
                                                             style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                            {{ $analyte->name }} ({{ $analyte->code }})
                                                        </div>
                                            @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($selectedAnalyteName)
                                                <div class="selected-item mt-2">
                                                    <span class="badge">
                                                        {{ $selectedAnalyteName }}
                                                        <i class="mdi mdi-close-circle ms-1" wire:click="clearAnalyte" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.analyte_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-test-tube text-success"></i> Method
                                        </label>
                                        <div class="searchable-select-container">
                                            <input type="text" 
                                                   wire:model.live="methodSearch" 
                                                   wire:keyup="searchMethods"
                                                   class="form-control @error('elementForm.method') is-invalid @enderror" 
                                                   placeholder="Type to search methods..."
                                                   autocomplete="off"
                                                   style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                            <input type="hidden" wire:model="elementForm.method">
                                            
                                            @if($showMethodDropdown && count($filteredMethods) > 0)
                                                <div class="searchable-dropdown">
                                                    @foreach($filteredMethods as $method)
                                                        <div class="dropdown-item" 
                                                             wire:click="selectMethod({{ $method->id }}, '{{ $method->name }}')"
                                                             style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                            {{ $method->name }}
                                                        </div>
                                            @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($selectedMethodName)
                                                <div class="selected-item mt-2">
                                                    <span class="badge">
                                                        {{ $selectedMethodName }}
                                                        <i class="mdi mdi-close-circle ms-1" wire:click="clearMethod" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.method') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-cog text-warning"></i> Equipment
                                        </label>
                                        <div class="searchable-select-container">
                                            <input type="text" 
                                                   wire:model.live="equipmentSearch" 
                                                   wire:keyup="searchEquipment"
                                                   class="form-control @error('elementForm.equipment_id') is-invalid @enderror" 
                                                   placeholder="Type to search equipment..."
                                                   autocomplete="off"
                                                   style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                            <input type="hidden" wire:model="elementForm.equipment_id">
                                            
                                            @if($showEquipmentDropdown && count($filteredEquipment) > 0)
                                                <div class="searchable-dropdown">
                                                    @foreach($filteredEquipment as $equipment)
                                                        <div class="dropdown-item" 
                                                             wire:click="selectEquipment({{ $equipment->id }}, '{{ $equipment->name }}')"
                                                             style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                            {{ $equipment->name }}
                                                        </div>
                                            @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($selectedEquipmentName)
                                                <div class="selected-item mt-2">
                                                    <span class="badge">
                                                        {{ $selectedEquipmentName }}
                                                        <i class="mdi mdi-close-circle ms-1" wire:click="clearEquipment" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.equipment_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-account text-info"></i> Operator
                                        </label>
                                        <div class="searchable-select-container">
                                            <input type="text" 
                                                   wire:model.live="operatorSearch" 
                                                   wire:keyup="searchOperators"
                                                   class="form-control @error('elementForm.operator_id') is-invalid @enderror" 
                                                   placeholder="Type to search operators..."
                                                   autocomplete="off"
                                                   style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                            <input type="hidden" wire:model="elementForm.operator_id">
                                            
                                            @if($showOperatorDropdown && count($filteredOperators) > 0)
                                                <div class="searchable-dropdown">
                                                    @foreach($filteredOperators as $operator)
                                                        <div class="dropdown-item" 
                                                             wire:click="selectOperator({{ $operator->id }}, '{{ $operator->name }}')"
                                                             style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                            {{ $operator->name }}
                                                        </div>
                                            @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($selectedOperatorName)
                                                <div class="selected-item mt-2">
                                                    <span class="badge">
                                                        {{ $selectedOperatorName }}
                                                        <i class="mdi mdi-close-circle ms-1" wire:click="clearOperator" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.operator_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-scale-balance text-primary"></i> Reporting Unit <span class="text-danger">*</span></label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('elementForm.reporting_unit').live,
                                            units: {{ json_encode($reportingUnits->pluck('name')->values()) }},
                                            get filteredUnits() {
                                                if (!this.search) return this.units.slice(0, 50);
                                                return this.units.filter(unit => 
                                                    unit.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            selectUnit(unit) {
                                                this.selected = unit;
                                                this.open = false;
                                                this.search = '';
                                            }
                                        }" class="searchable-dropdown-wrapper" wire:key="reporting-unit-dropdown-{{ $editingElement?->id ?? 'new' }}">
                                            <div class="single-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected ? selected : 'Search reporting units...'"
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
                                                        <template x-for="unit in filteredUnits" :key="unit">
                                                            <div @click="selectUnit(unit)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': selected == unit }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="selected == unit"></i>
                                                                <span x-text="unit"></span>
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
                                        @error('elementForm.reporting_unit') <span class="text-danger">{{ $message }}</span> @enderror
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
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.remark_is_manual" class="form-check-input" id="remark_is_manual">
                                            <label class="form-check-label" for="remark_is_manual">Remark is Manual</label>
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
                                                           wire:model.live="elementForm.recommend_remedies" 
                                                           class="form-check-input" 
                                                           id="recommend_remedies">
                                                    <label class="form-check-label" for="recommend_remedies">
                                                        Recommend Remedies if Test Fails
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            @if($elementForm['recommend_remedies'] ?? false)
                                                <div class="form-group mb-3">
                                                    <label class="form-label fw-bold">
                                                        <i class="mdi mdi-medical-bag text-danger"></i> Remedy System
                                                    </label>
                                                    <div class="searchable-select-container">
                                                        <input type="text" 
                                                               wire:model.live="remedyHeaderSearch" 
                                                               wire:keyup="searchRemedyHeaders"
                                                               class="form-control @error('elementForm.remedy_header_id') is-invalid @enderror" 
                                                               placeholder="Type to search remedy systems..."
                                                               autocomplete="off"
                                                               style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                                        <input type="hidden" wire:model="elementForm.remedy_header_id">
                                                        
                                                        @if($showRemedyHeaderDropdown && count($filteredRemedyHeaders) > 0)
                                                            <div class="searchable-dropdown">
                                                                @foreach($filteredRemedyHeaders as $remedyHeader)
                                                                    <div class="dropdown-item" 
                                                                         wire:click="selectRemedyHeader({{ $remedyHeader->id }}, '{{ $remedyHeader->name }}')"
                                                                         style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                                        {{ $remedyHeader->name }}
                                                                    </div>
                                                        @endforeach
                                                            </div>
                                                        @endif
                                                        
                                                        @if($selectedRemedyHeaderName)
                                                            <div class="selected-item mt-2">
                                                                <span class="badge">
                                                                    {{ $selectedRemedyHeaderName }}
                                                                    <i class="mdi mdi-close-circle ms-1" wire:click="clearRemedyHeader" style="cursor: pointer;"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('elementForm.remedy_header_id') 
                                                        <div class="invalid-feedback d-block">{{ $message }}</div> 
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
                            
                            <!-- Worksheet Calculation Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">
                                                <i class="mdi mdi-calculator"></i> Worksheet Calculation
                                            </h6>
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           wire:model.live="elementForm.result_is_calculated" 
                                                           class="form-check-input" 
                                                           id="result_is_calculated">
                                                    <label class="form-check-label" for="result_is_calculated">
                                                        Result is Calculated
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            @if($elementForm['result_is_calculated'] ?? false)
                                                <div class="form-group mb-3">
                                                    <label class="form-label fw-bold">
                                                        <i class="mdi mdi-calculator text-primary"></i> Formulars
                                                    </label>
                                                    <div class="searchable-select-container">
                                                        <input type="text" 
                                                               wire:model.live="formularSearch" 
                                                               wire:keyup="searchFormulars"
                                                               class="form-control @error('elementForm.formular_id') is-invalid @enderror" 
                                                               placeholder="Type to search formulars..."
                                                               autocomplete="off"
                                                               style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                                        <input type="hidden" wire:model="elementForm.formular_id">
                                                        
                                                        @if($showFormularDropdown && count($filteredFormulars) > 0)
                                                            <div class="searchable-dropdown">
                                                                @foreach($filteredFormulars as $formular)
                                                                    <div class="dropdown-item" 
                                                                         wire:click="selectFormular({{ $formular->id }}, '{{ $formular->name }}')"
                                                                         style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                                        {{ $formular->name }}
                                                                    </div>
                                                        @endforeach
                                                            </div>
                                                        @endif
                                                        
                                                        @if($selectedFormularName)
                                                            <div class="selected-item mt-2">
                                                                <span class="badge">
                                                                    {{ $selectedFormularName }}
                                                                    <i class="mdi mdi-close-circle ms-1" wire:click="clearFormular" style="cursor: pointer;"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('elementForm.formular_id') 
                                                        <div class="invalid-feedback d-block">{{ $message }}</div> 
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Select the formular to use for calculating this result
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Method Sequence Stages Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">
                                                <i class="mdi mdi-timeline-check"></i> Method Sequence Stages
                                            </h6>
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           wire:model.live="elementForm.has_method_sequence" 
                                                           class="form-check-input" 
                                                           id="has_method_sequence">
                                                    <label class="form-check-label" for="has_method_sequence">
                                                        Has Method Sequence
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            @if($elementForm['has_method_sequence'] ?? false)
                                                <div class="form-group mb-3">
                                                    <label class="form-label fw-bold">
                                                        <i class="mdi mdi-timeline-check text-success"></i> Method Sequence
                                                    </label>
                                                    <div class="searchable-select-container">
                                                        <input type="text" 
                                                               wire:model.live="methodSequenceSearch" 
                                                               wire:keyup="searchMethodSequences"
                                                               class="form-control @error('elementForm.method_sequence_id') is-invalid @enderror" 
                                                               placeholder="Type to search method sequences..."
                                                               autocomplete="off"
                                                               style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                                        <input type="hidden" wire:model="elementForm.method_sequence_id">
                                                        
                                                        @if($showMethodSequenceDropdown && count($filteredMethodSequences) > 0)
                                                            <div class="searchable-dropdown">
                                                                @foreach($filteredMethodSequences as $methodSequence)
                                                            @php
                                                                $activeVer = $methodSequence->activeVersion->first();
                                                                $latestVer = $methodSequence->latestVersion->first();
                                                                $versionInfo = '';
                                                                if ($activeVer) {
                                                                    $versionInfo = ' (v' . $activeVer->version_number . ' - Active)';
                                                                } elseif ($latestVer) {
                                                                    $versionInfo = ' (v' . $latestVer->version_number . ' - Latest)';
                                                                }
                                                            @endphp
                                                                    <div class="dropdown-item" 
                                                                         wire:click="selectMethodSequence({{ $methodSequence->id }}, '{{ $methodSequence->name }}')"
                                                                         style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                                        {{ $methodSequence->name }}{{ $versionInfo }}
                                                                    </div>
                                                        @endforeach
                                                            </div>
                                                        @endif
                                                        
                                                        @if($selectedMethodSequenceName)
                                                            <div class="selected-item mt-2">
                                                                <span class="badge">
                                                                    {{ $selectedMethodSequenceName }}
                                                                    <i class="mdi mdi-close-circle ms-1" wire:click="clearMethodSequence" style="cursor: pointer;"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('elementForm.method_sequence_id') 
                                                        <div class="invalid-feedback d-block">{{ $message }}</div> 
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        @if($elementForm['analyte_id'])
                                                            Select the method sequence for this element. Only active sequences for the selected analyte are shown. The active/latest version will be automatically used.
                                                        @else
                                                            Please select an analyte first to see available method sequences.
                                                        @endif
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
</style>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('livewire:init', () => {
    // Prevent body scroll when modal opens
    Livewire.on('element-modal-opened', () => {
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    });
    
    // Restore body scroll when modal closes
    Livewire.on('element-modal-closed', () => {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });
    
    // Initialize drag and drop functionality
    initializeSortable();
});

// Initialize sortable when Livewire updates the DOM
document.addEventListener('livewire:updated', () => {
    initializeSortable();
});

function initializeSortable() {
    const sortableElement = document.getElementById('sortable-elements');
    console.log('Initializing sortable on element:', sortableElement);
    
    if (sortableElement && typeof Sortable !== 'undefined') {
        console.log('Sortable library loaded, creating sortable instance');
        
        // Destroy existing sortable instance if it exists
        if (sortableElement.sortableInstance) {
            sortableElement.sortableInstance.destroy();
        }
        
        sortableElement.sortableInstance = Sortable.create(sortableElement, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                console.log('Drag ended, new index:', evt.newIndex, 'old index:', evt.oldIndex);
                
                const elementIds = Array.from(sortableElement.children).map(row => {
                    return parseInt(row.getAttribute('data-element-id'));
                });
                
                console.log('New element order:', elementIds);
                
                // Send the new order to Livewire
                @this.call('updateElementOrder', elementIds);
            }
        });
        
        console.log('Sortable instance created successfully');
    } else {
        console.error('Failed to initialize sortable:', {
            sortableElement: sortableElement,
            sortableLibrary: typeof Sortable
        });
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.searchable-select-container')) {
        @this.set('showAnalyteDropdown', false);
        @this.set('showMethodDropdown', false);
        @this.set('showEquipmentDropdown', false);
        @this.set('showOperatorDropdown', false);
        @this.set('showRemedyHeaderDropdown', false);
        @this.set('showFormularDropdown', false);
        @this.set('showMethodSequenceDropdown', false);
    }
});
</script>

<style>
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

/* Drag and Drop Styling */
.sortable-row {
    transition: all 0.2s ease;
}

.sortable-row:hover {
    background-color: #f8f9fa !important;
}

.drag-handle:hover {
    background-color: #e9ecef;
    border-radius: 4px;
}

.drag-handle:hover i {
    color: #007bff !important;
}

/* SortableJS visual feedback */
.sortable-ghost {
    opacity: 0.4;
    background-color: #c8e6c9 !important;
    border: 2px dashed #4caf50 !important;
}

.sortable-chosen {
    background-color: #e3f2fd !important;
    border: 2px solid #2196f3 !important;
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.sortable-drag {
    background-color: #fff3e0 !important;
    border: 2px solid #ff9800 !important;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Searchable Select Styles */
.searchable-select-container {
    position: relative;
}

.searchable-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #007bff;
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.searchable-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.selected-item .badge {
    font-size: 0.9rem;
    padding: 8px 12px;
    background-color: #f8f9fa !important;
    color: #495057 !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    font-weight: 500;
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