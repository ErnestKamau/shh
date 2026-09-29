<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
<div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-molecule text-primary"></i>
                                Analytes Management
                            </h2>
                            <p class="text-muted mb-0">Manage analytes, methods, and equipment</p>
                        </div>
                        <div class="d-flex align-items-center" style="gap: .5rem;">
                            <button type="button" wire:click="openBulkImportModal" class="btn btn-outline-secondary px-3" style="border-radius: 8px;">
                                <i class="mdi mdi-file-excel"></i> Import
                            </button>
                            <button wire:click="showCreateModal" class="btn btn-outline-primary px-3" style="border-radius: 8px;">
                                <i class="mdi mdi-plus"></i> Add Analyte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        @php
            $analyteAlertClass = match ($messageType) {
                'success' => 'success',
                'warning' => 'warning',
                default => 'danger',
            };
        @endphp
        <div class="alert alert-{{ $analyteAlertClass }} alert-dismissible fade show" role="alert">
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
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, report display, or common name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Status</label>
                                <div class="tag-select-container"
                                     wire:click="$set('showStatusDropdown', true)"
                                     wire:click.outside="$set('showStatusDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($statusFilter !== '')
                                            <span class="tag-badge {{ $statusFilter == '1' ? 'tag-badge--success' : 'tag-badge--neutral' }}">
                                                <i class="mdi mdi-{{ $statusFilter == '1' ? 'check-circle' : 'close-circle' }} me-1"></i>
                                                {{ $statusFilter == '1' ? 'Active' : 'Inactive' }}
                                                <i class="mdi mdi-close ms-1" wire:click.stop="selectStatus('')" style="cursor:pointer;"></i>
                                            </span>
                                        @else
                                            <span class="tag-input text-muted" style="cursor:pointer; line-height:28px;">All Status</span>
                                        @endif
                                    </div>
                                    @if($showStatusDropdown)
                                    <div class="tag-dropdown">
                                        <div class="tag-dropdown-item" wire:click.stop="selectStatus('')">
                                            <i class="mdi mdi-all-inclusive me-2 text-muted"></i> All Status
                                        </div>
                                        <div class="tag-dropdown-item" wire:click.stop="selectStatus('1')">
                                            <i class="mdi mdi-check-circle me-2 text-success"></i> Active
                                        </div>
                                        <div class="tag-dropdown-item" wire:click.stop="selectStatus('0')">
                                            <i class="mdi mdi-close-circle me-2 text-danger"></i> Inactive
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
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

    <!-- Analytes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($analytes->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $analytes->firstItem() ?? 0 }} to {{ $analytes->lastItem() ?? 0 }} of {{ $analytes->total() }} entries
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
                                        <th style="width: 120px;">Actions</th>
                                        <th>Name</th>
                                        <th>Report Display</th>
                                        <th>Common Name</th>
                                        <th style="width: 220px;">Methods</th>
                                        <th style="width: 200px;">Equipment</th>
                                        <th>Decimal Places</th>
                                        <th>Equivalent Weight</th>
                                        <th>Reporting Symbol</th>
                                        <th>Reporting Unit</th>
                                        <th>Font Italic</th>
                                        <th>Scientific</th>
                                        <th>Non Detectable</th>
                                        <th>Non Accredited</th>
                                        <th>Show on Report</th>
                                        <th>Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytes as $analyte)
                                        <tr wire:key="analyte-row-{{ $analyte->id }}">
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button wire:click="showEditModal(@js($analyte->id))"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="showDeleteModal(@js($analyte->id))"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td><strong>{{ $analyte->name }}</strong></td>
                                            <td><strong>{{ $analyte->plainReportDisplay() }}</strong></td>
                                            <td>{{ $analyte->common_name }}</td>
                                            <td style="max-width:200px;white-space:normal;"><small>{{ $analyte->analysisMethods->pluck('name')->join(', ') ?: '-' }}</small></td>
                                            <td style="max-width:180px;white-space:normal;"><small>{{ $analyte->equipmentItems->pluck('name')->join(', ') ?: '-' }}</small></td>
                                            <td>{{ $analyte->decimal_places }}</td>
                                            <td>{{ $analyte->equivalent_weight ? number_format($analyte->equivalent_weight, $analyte->decimal_places) : '-' }}</td>
                                            <td>{{ $analyte->reporting_symbol }}</td>
                                            <td>{{ $analyte->reporting_unit }}</td>
                                            <td class="text-center">
                                                {!! $analyte->is_italic ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->report_scientific_notation ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->non_detectable ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->non_accredited ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->show_on_report ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-center">
                                                {!! $analyte->active ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $analytes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-molecule fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No analytes found</h5>
                            <p class="text-muted">Create your first analyte to get started.</p>
                            <button wire:click="showCreateModal" class="btn btn-outline-primary rounded-pill px-3">
                                <i class="mdi mdi-plus"></i> Add Analyte
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analyte Create/Edit Modal -->
    @if($showModal)
    <div wire:key="analyte-form-modal"
         class="modal fade show d-block analyte-modal-overlay"
         tabindex="-1"
         style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(3px);">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                {{-- Brand mono gradient header (modal-styles) --}}
                <div class="modal-header border-0 py-3 px-4">
                    <div class="d-flex align-items-center analyte-modal-title-wrap flex-grow-1">
                        <div class="analyte-modal-icon-wrap">
                            <i class="mdi mdi-{{ $editingAnalyteId ? 'pencil-outline' : 'plus' }} fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold">
                                {{ $editingAnalyteId ? 'Edit Analyte' : 'Add New Analyte' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingAnalyteId ? 'Update analyte information and assignments' : 'Fill in the details to create a new analyte' }}
                            </small>
                        </div>
                    </div>
                    <button type="button" class="ls-modal-close" wire:click.stop="closeModal" aria-label="Close">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <div class="modal-body p-0">

                    {{-- ===== Section: Basic Information ===== --}}
                    <div class="section-divider px-4 py-2 d-flex align-items-center analyte-section-head"
                         style="background:#f8f9fa;border-bottom:1px solid #e9ecef;">
                        <span class="badge rounded-pill section-number-badge">01</span>
                        <small class="text-uppercase fw-bold text-muted d-flex align-items-center" style="letter-spacing:.6px;">
                            <i class="mdi mdi-information-outline me-2"></i>Basic Information
                        </small>
                    </div>
                    <div class="px-4 py-3">
                        <div class="row analyte-form-row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Analyte Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="analyteForm.name"
                                       class="form-control @error('analyteForm.name') is-invalid @enderror"
                                       placeholder="e.g. Nitrate">
                                @error('analyteForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Report Display <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="analyteForm.code"
                                       class="form-control @error('analyteForm.code') is-invalid @enderror"
                                       placeholder="e.g. NO3">
                                @error('analyteForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Common Name</label>
                                <input type="text" wire:model="analyteForm.common_name" class="form-control" placeholder="e.g. Nitrate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Decimal Places <span class="text-danger">*</span></label>
                                <input type="number" min="0" step="1"
                                       wire:model="analyteForm.decimal_places"
                                       class="form-control @error('analyteForm.decimal_places') is-invalid @enderror"
                                       placeholder="0">
                                @error('analyteForm.decimal_places') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Equivalent Weight</label>
                                <input type="number" step="0.0001" wire:model="analyteForm.equivalent_weight" class="form-control" placeholder="0.0000">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Reporting Symbol</label>
                                <input type="text" wire:model="analyteForm.reporting_symbol" class="form-control" placeholder="e.g. NO₃⁻">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Reporting Unit</label>
                                <div class="tag-select-container"
                                     wire:click="openReportingUnitDropdown"
                                     wire:click.outside="closeReportingUnitDropdown">
                                    <div class="tag-select-input">
                                        @if($analyteForm['reporting_unit'])
                                            <span class="tag-badge tag-badge--neutral">
                                                {{ $analyteForm['reporting_unit'] }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('analyteForm.reporting_unit', '')"></i>
                                            </span>
                                        @endif
                                        <input type="text"
                                               wire:model.live.debounce.200ms="reportingUnitSearch"
                                               class="tag-input"
                                               placeholder="{{ $analyteForm['reporting_unit'] ? '' : 'Search units...' }}"
                                               autocomplete="off">
                                    </div>
                                    @if($showReportingUnitDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredReportingUnits as $unit)
                                                <div class="tag-dropdown-item" wire:key="reporting-unit-{{ $unit->id }}" wire:click.stop="selectReportingUnit(@js($unit->name))">
                                                    {{ $unit->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">
                                                    <i class="mdi mdi-information-outline me-2"></i>No reporting units found
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Section: Assignments ===== --}}
                    <div class="section-divider px-4 py-2 d-flex align-items-center analyte-section-head"
                         style="background:#f8f9fa;border-top:1px solid #e9ecef;border-bottom:1px solid #e9ecef;">
                        <span class="badge rounded-pill section-number-badge">02</span>
                        <small class="text-uppercase fw-bold text-muted d-flex align-items-center" style="letter-spacing:.6px;">
                            <i class="mdi mdi-link-variant me-2"></i>Assignments
                        </small>
                    </div>
                    <div class="px-4 py-3">
                        <div class="row analyte-form-row">
                            {{-- Method Multi-Select --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    <i class="mdi mdi-test-tube text-success me-1"></i>Analysis Methods
                                </label>
                                <div class="tag-select-container"
                                     wire:click="openMethodDropdown"
                                     wire:click.outside="closeMethodDropdown">
                                    <div class="tag-select-input">
                                        @foreach($this->selectedMethods as $method)
                                            <span class="tag-badge tag-badge--success" wire:key="selected-method-{{ $method->id }}">
                                                {{ $method->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="removeMethod(@js($method->id))"></i>
                                            </span>
                                        @endforeach
                                        <input type="text"
                                               wire:model.live.debounce.200ms="methodSearch"
                                               class="tag-input"
                                               placeholder="{{ count($this->selectedMethods) > 0 ? '' : 'Search or select methods...' }}"
                                               autocomplete="off">
                                    </div>
                                    @if($showMethodDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredMethods as $method)
                                                <div class="tag-dropdown-item" wire:click.stop="addMethod(@js($method->id))">
                                                    {{ $method->name }}
                                                    @if($method->code)
                                                        <small class="text-muted ms-1">({{ $method->code }})</small>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">
                                                    <i class="mdi mdi-information-outline me-2"></i>No matching methods found
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Equipment Multi-Select --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    <i class="mdi mdi-cog text-warning me-1"></i>Equipment
                                </label>
                                <div class="tag-select-container"
                                     wire:click="openEquipmentDropdown"
                                     wire:click.outside="closeEquipmentDropdown">
                                    <div class="tag-select-input">
                                        @foreach($this->selectedEquipment as $equip)
                                            <span class="tag-badge tag-badge--warning" wire:key="selected-equipment-{{ $equip->id }}">
                                                {{ $equip->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="removeEquipment(@js($equip->id))"></i>
                                            </span>
                                        @endforeach
                                        <input type="text"
                                               wire:model.live.debounce.200ms="equipmentSearch"
                                               class="tag-input"
                                               placeholder="{{ count($this->selectedEquipment) > 0 ? '' : 'Search or select equipment...' }}"
                                               autocomplete="off">
                                    </div>
                                    @if($showEquipmentDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredEquipment as $equip)
                                                <div class="tag-dropdown-item" wire:click.stop="addEquipment(@js($equip->id))">
                                                    {{ $equip->name }}
                                                    @if($equip->equipment_number)
                                                        <small class="text-muted ms-1">({{ $equip->equipment_number }})</small>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">
                                                    <i class="mdi mdi-information-outline me-2"></i>No matching equipment found
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Section: Properties ===== --}}
                    <div class="section-divider px-4 py-2 d-flex align-items-center analyte-section-head"
                         style="background:#f8f9fa;border-top:1px solid #e9ecef;border-bottom:1px solid #e9ecef;">
                        <span class="badge rounded-pill section-number-badge">03</span>
                        <small class="text-uppercase fw-bold text-muted d-flex align-items-center" style="letter-spacing:.6px;">
                            <i class="mdi mdi-tune-variant me-2"></i>Properties
                        </small>
                    </div>
                    <div class="px-4 py-3">
                        <div class="row analyte-form-row">
                            @php
                            $toggleOptions = [
                                ['key' => 'is_italic',      'icon' => 'format-italic',      'color' => 'text-primary',  'label' => 'Report Font Italic',  'desc' => 'Display analyte name in italic on reports'],
                                ['key' => 'report_scientific_notation', 'icon' => 'format-superscript', 'color' => 'text-primary', 'label' => 'Scientific notation', 'desc' => 'Show 490000 on the report as 4,9 × 10⁵'],
                                ['key' => 'non_detectable', 'icon' => 'eye-off-outline',    'color' => 'text-warning',  'label' => 'Non-Detectable',      'desc' => 'Mark as below detection limit'],
                                ['key' => 'non_accredited', 'icon' => 'certificate-outline','color' => 'text-warning',  'label' => 'Non-Accredited',      'desc' => 'This analyte is not accredited'],
                                ['key' => 'show_on_report', 'icon' => 'file-document-outline','color' => 'text-success', 'label' => 'Show on Report',      'desc' => 'Include in generated reports'],
                                ['key' => 'active',         'icon' => 'check-circle-outline','color' => 'text-success',  'label' => 'Active',             'desc' => 'Analyte is currently active'],
                            ];
                            @endphp
                            @foreach($toggleOptions as $opt)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                                     style="background:#f8fafc;border:1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="mdi mdi-{{ $opt['icon'] }} fs-5 {{ $opt['color'] }}"></i>
                                        <div>
                                            <div class="fw-medium small">{{ $opt['label'] }}</div>
                                            <div class="text-muted" style="font-size:.75rem;">{{ $opt['desc'] }}</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0 ms-3">
                                        <input type="checkbox"
                                               wire:model="analyteForm.{{ $opt['key'] }}"
                                               class="form-check-input"
                                               role="switch"
                                               style="width:2.5rem;height:1.25rem;cursor:pointer;">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>{{-- end modal-body --}}

                {{-- ===== Footer ===== --}}
                <div class="modal-footer border-0 px-4 py-3 flex-column align-items-stretch gap-2" style="background:#f8f9fa;">
                    @if($message && $messageType === 'danger')
                        <div class="alert alert-danger mb-0 py-2 px-3 small" role="alert">
                            <i class="mdi mdi-alert-circle-outline me-1"></i>{{ $message }}
                        </div>
                    @endif
                    <div class="d-flex justify-content-end w-100 analyte-modal-footer-actions">
                    <button type="button" class="btn btn-light px-4" wire:click.stop="closeModal">
                        <i class="mdi mdi-close me-1"></i> Cancel
                    </button>
                        <button type="button" class="btn btn-primary px-4 fw-semibold"
                            wire:click.stop="saveAnalyte"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAnalyte">
                            <i class="mdi mdi-content-save me-1"></i>
                            {{ $editingAnalyteId ? 'Update Analyte' : 'Save Analyte' }}
                        </span>
                        <span wire:loading wire:target="saveAnalyte">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                        </span>
                    </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($deleteModalVisible && $this->analyteToDelete)
    <div wire:key="analyte-delete-modal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="mdi mdi-delete-alert text-danger" style="font-size: 64px;"></i>
                    </div>
                    <h5 class="text-center mb-3">Delete Analyte: <strong>{{ $this->analyteToDelete->name }}</strong>?</h5>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-information"></i> <strong>Note:</strong>
                        <ul class="mb-0 mt-2">
                            <li>If this analyte <strong>has samples or results</strong> tied to it, it will be <strong>soft deleted</strong> (marked as inactive and hidden).</li>
                            <li>If this analyte <strong>has no samples or results</strong>, it will be <strong>permanently deleted</strong> along with its analysis elements.</li>
                        </ul>
                    </div>
                    <p class="text-muted text-center mb-0">This action cannot be undone for permanent deletions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDelete">
                        <i class="mdi mdi-delete"></i> Confirm Delete
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

    .analyte-modal-overlay {
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .analyte-modal-overlay .modal-dialog {
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .analyte-modal-title-wrap {
        gap: 14px;
    }

    .analyte-modal-icon-wrap {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
    }

    .analyte-modal-icon-wrap .mdi {
        color: #fff !important;
    }

    .analyte-section-head {
        gap: 12px;
    }

    /* Bootstrap 4 has no gap utilities — space footer actions */
    .analyte-modal-footer-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.65rem;
    }

    .section-number-badge {
        background: #f4f6f9;
        color: #6c757d;
        border: 1px solid #dee2e6;
        font-size: .72rem;
        padding: 4px 10px;
        font-weight: 700;
    }

    /* Explicit spacing for form fields (Bootstrap-version independent) */
    .analyte-form-row {
        margin-left: -10px;
        margin-right: -10px;
    }

    .analyte-form-row > [class*="col-"] {
        padding-left: 10px;
        padding-right: 10px;
        margin-bottom: 14px;
    }
    
    /* Tag-based Multi-Select — density matches Process Enquiry acc-param-tags */
    .tag-select-container {
        position: relative;
        cursor: text;
    }
    
    .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        min-height: var(--ls-control-h, var(--control-h, 34px));
        padding: 0.35rem 0.45rem;
        background: #fff;
        border: 1px solid var(--ls-color-border, var(--color-border, #e2e8f0));
        border-radius: var(--ls-radius-sm, 6px);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    
    .tag-select-input:hover {
        border-color: var(--ls-color-primary, var(--color-primary));
    }
    
    .tag-select-input:focus-within {
        border-color: var(--ls-color-primary, var(--color-primary));
        box-shadow: 0 0 0 3px var(--ls-color-primary-focus, var(--color-primary-focus));
        outline: none;
    }
    
    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.35rem 0.15rem 0.45rem;
        background-color: var(--ls-color-primary-soft, var(--color-primary-soft));
        color: var(--ls-color-primary, var(--color-primary));
        border: 1px solid var(--ls-color-primary-border, var(--color-primary-border-soft));
        border-radius: 999px;
        font-size: var(--ls-text-sm, 0.75rem);
        font-weight: 600;
        line-height: 1.25;
        white-space: nowrap;
    }

    .tag-badge--neutral {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }

    .tag-badge--success {
        background: var(--ls-color-success-soft, #dcfce7);
        color: var(--ls-color-success-text, #166534);
        border-color: #bbf7d0;
    }

    .tag-badge--warning {
        background: var(--ls-color-warning-soft, #fef3c7);
        color: var(--ls-color-warning-text, #92400e);
        border-color: #fde68a;
    }
    
    .tag-badge i {
        cursor: pointer;
        font-size: 0.95rem;
        opacity: 0.75;
        transition: opacity 0.15s;
        color: inherit;
    }
    
    .tag-badge i:hover {
        opacity: 1;
    }
    
    .tag-input {
        flex: 1;
        min-width: 120px;
        border: none;
        outline: none;
        padding: 0.15rem;
        font-size: var(--ls-text-base, 0.8125rem);
        background: transparent;
    }
    
    .tag-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid var(--ls-color-border, var(--color-border));
        border-radius: var(--ls-radius-sm, 6px);
        max-height: 240px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
        margin-top: 0;
    }
    
    .tag-dropdown-item {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: background-color 0.15s;
        border-bottom: 1px solid #f1f5f9;
        font-size: var(--ls-text-base, 0.8125rem);
    }
    
    .tag-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .tag-dropdown-item:last-child {
        border-bottom: none;
    }
    
    /* Use overlay/body scroll for the modal instead of inner modal-body scrolling */
    .modal-body {
        max-height: none;
        overflow: visible;
    }
    </style>

    @include('livewire.partials.lab-taxonomy-bulk-import-modal', ['entityLabel' => 'Analyte'])
</div>
