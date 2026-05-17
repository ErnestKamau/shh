<div class="container-fluid">
    @if(!$embedded)
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-tools text-primary"></i>
                                {{ __('equipment.equipment_management') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.equipment_management_subtitle') }}</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button wire:click="openBulkUploadModal" class="btn btn-outline-success" style="border-radius: 8px;margin-right: 10px !important;">
                                <i class="mdi mdi-file-excel"></i> {{ __('equipment.import_equipment') }}
                            </button>
                            <button wire:click="showCreateEquipmentModal" class="btn btn-outline-primary" style="border-radius: 8px;">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_equipment') }}
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
                        <i class="mdi mdi-filter-variant"></i> {{ __('equipment.filter_options') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.search') }}</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('equipment.search_by_name_number_make_model') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.status') }}</label>
                                <details class="tag-select-container status-filter-container">
                                    <summary class="tag-select-input">
                                        @if($statusFilter)
                                            <span class="tag-badge status-filter-badge">
                                                {{ $statusFilter }}
                                                <i class="mdi mdi-close" wire:click.prevent="$set('statusFilter', '')"></i>
                                            </span>
                                        @endif
                                        <input
                                            type="text"
                                            class="tag-input status-filter-input"
                                            readonly
                                            value=""
                                            placeholder="{{ $statusFilter ? '' : __('equipment.all_status') }}"
                                        >
                                    </summary>
                                    <div class="tag-dropdown">
                                        <button type="button" class="tag-dropdown-item status-filter-option w-100 text-start" wire:click="$set('statusFilter', '')">
                                            {{ __('equipment.all_status') }}
                                        </button>
                                        @foreach($statuses as $status)
                                            <button type="button" class="tag-dropdown-item status-filter-option w-100 text-start" wire:click='$set("statusFilter", @js($status))'>
                                                {{ $status }}
                                            </button>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> {{ __('equipment.clear') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">


                    @if($this->equipment->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->equipment->firstItem() ?? 0 }} to {{ $this->equipment->lastItem() ?? 0 }} of {{ $this->equipment->total() }} entries
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">{{ __('equipment.show') }}:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 100px;">{{ __('equipment.actions') }}</th>
                                        <th>{{ __('equipment.photo') }}</th>
                                        <th>{{ __('equipment.name') }}</th>
                                        <th>{{ __('equipment.equipment_number') }}</th>
                                        <th>{{ __('equipment.make') }}</th>
                                        <th>{{ __('equipment.model') }}</th>
                                        <th>{{ __('equipment.serial_number') }}</th>
                                        <th>{{ __('equipment.manufacturer') }}</th>
                                        <th>{{ __('equipment.department') }}</th>
                                        <th>{{ __('equipment.employee') }}</th>
                                        <th>{{ __('equipment.purchased_on') }}</th>
                                        <th>{{ __('equipment.calibration_date') }}</th>
                                        <th>{{ __('equipment.maintenance_date') }}</th>
                                        <th>{{ __('equipment.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->equipment as $item)
                                        <tr>
                                            <td class="equipment-actions-cell">
                                                <div class="d-flex">
                                                    <a href="{{ route('view-equipment', ['equipmentId' => $item->id]) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="{{ __('equipment.view') }}">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </a>
                                                    <button wire:click="showEditEquipmentModal('{{ $item->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="{{ __('equipment.edit') }}">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <a href="{{ route('equipment-disposal-home', ['equipment_id' => $item->id]) }}"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                        title="{{ __('equipment.request_disposal') }}">
                                                        <i class="mdi mdi-delete-sweep"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <img src="{{ $item->picture }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" alt="{{ $item->name }}">
                                            </td>
                                            <td>
                                                <a href="{{ route('view-equipment', ['equipmentId' => $item->id]) }}">
                                                    {{ $item->name }}
                                                </a>
                                            </td>
                                            <td>{{ $item->equipment_number }}</td>
                                            <td>{{ $item->make }}</td>
                                            <td>{{ $item->model }}</td>
                                            <td>{{ $item->serial_number ?? '-' }}</td>
                                            <td>{{ $item->manufacturer ?? '-' }}</td>
                                            <td>{{ getInventoryDepartmentByid($item->assigned_department)->name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $employee = \App\User::find($item->assigned_employee_id);
                                                @endphp
                                                {{ $employee->name ?? '-' }}
                                            </td>
                                            <td>{{ $item->date_purchased }}</td>
                                            <td>
                                                @php
                                                    $calibration = $item->calibration_date();
                                                @endphp
                                                {{ $calibration['date']->toDateString() }}
                                                <br>
                                                <small class="badge {{ $calibration['status'] }}">
                                                    {{ number_format(intval($calibration['remaining_days'])) }} days
                                                </small>
                                                @if($calibration['status'] == 'badge-warning')
                                                    <br><small class="text-warning">
                                                        <i class="mdi mdi-alert-decagram"></i> Schedule Calibration
                                                    </small>
                                                @endif
                                                @if($calibration['status'] == 'badge-danger')
                                                    <br><small class="text-danger">
                                                        <i class="mdi mdi-alert"></i> Calibration Required
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $maintenance = $item->maintainance_date();
                                                @endphp
                                                {{ $maintenance['date']->toDateString() }}
                                                <br>
                                                <small class="badge {{ $maintenance['status'] }}">
                                                    {{ number_format(intval($maintenance['remaining_days'])) }} days
                                                </small>
                                                @if($maintenance['status'] == 'badge-warning')
                                                    <br><small class="text-warning">
                                                        <i class="mdi mdi-alert-decagram"></i> Schedule Maintenance
                                                    </small>
                                                @endif
                                                @if($maintenance['status'] == 'badge-danger')
                                                    <br><small class="text-danger">
                                                        <i class="mdi mdi-alert"></i> Maintenance Required
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->active == 1)
                                                    <span class="badge badge-success p-2">{{ __('equipment.active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary p-2">{{ __('equipment.inactive') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->equipment->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-tools text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">{{ __('equipment.no_equipment_found') }}</h5>
                            <p class="text-muted">{{ __('equipment.add_first_equipment_hint') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @endif

    <!-- Equipment Modal -->
    @if($showEquipmentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingEquipment ? 'pencil' : 'plus' }}"></i>
                            {{ $editingEquipment ? 'Edit' : 'Create' }} Equipment
                        </h5>
                        <button type="button" class="btn-close" wire:click.prevent.stop="closeEquipmentModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3" style="max-height: 70vh; overflow-y: auto;">
                        @include('livewire.equipment.partials.equipment-form-wizard', [
                            'wizardPhotoEquipment' => $editingEquipment,
                            'hidePreventiveMaintenance' => (bool) $editingEquipment,
                        ])
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <button type="button" class="btn btn-secondary" wire:click="closeEquipmentModal">{{ __('equipment.cancel') }}</button>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary ms-2" wire:click="previousStep" style="display: {{ $currentStep === 1 ? 'none' : 'block' }};">
                                    <i class="mdi mdi-chevron-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-outline-primary" wire:click="nextStep" style="display: {{ $currentStep === $totalSteps ? 'none' : 'block' }};">
                                    Next <i class="mdi mdi-chevron-right"></i>
                                </button>
                                <button type="button" class="btn btn-primary" wire:click="saveEquipment" style="display: {{ $currentStep === $totalSteps ? 'block' : 'none' }};">
                                    <i class="mdi mdi-content-save"></i> {{ __('equipment.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif



    <!-- Bulk Upload Modal -->
    @if($showBulkUploadModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-excel text-success"></i>
                            {{ __('equipment.bulk_import_equipment') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeBulkUploadModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>{{ __('equipment.instructions') }}:</strong>
                            <ol class="mb-0 mt-2">
                                <li>{{ __('equipment.download_excel_template_below') }}</li>
                                <li>{{ __('equipment.fill_required_fields_marked') }}</li>
                                <li>{{ __('equipment.upload_completed_file') }}</li>
                                <li>{{ __('equipment.review_import_results') }}</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <button wire:click="downloadTemplate" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-download"></i> {{ __('equipment.download_excel_template') }}
                            </button>
                        </div>

                        <hr>

                        <form wire:submit.prevent="processBulkUpload">
                            <div class="form-group mb-4 text-start">
                                <label class="form-label fw-bold">Associate with Zone (Optional)</label>
                                
                                <!-- Premium Custom Zone Selector -->
                                <div class="custom-zone-selector-wrapper" 
                                     x-data="{ 
                                        open: false, 
                                        search: '',
                                        zones: @js($zones),
                                        selectedId: @entangle('selectedZoneId'),
                                        get selectedZone() {
                                            return this.zones.find(z => z.id == this.selectedId) || null;
                                        },
                                        get filteredZones() {
                                            if (!this.search.trim()) return this.zones;
                                            return this.zones.filter(z => 
                                                (z.value && z.value.toLowerCase().includes(this.search.toLowerCase())) ||
                                                (z.key && z.key.toLowerCase().includes(this.search.toLowerCase()))
                                            );
                                        },
                                        selectZone(id) {
                                            this.selectedId = id;
                                            this.open = false;
                                            this.search = '';
                                        },
                                        clearSelection() {
                                            this.selectedId = null;
                                            this.open = false;
                                            this.search = '';
                                        }
                                     }"
                                     @click.outside="open = false"
                                >
                                    <!-- Trigger Button -->
                                    <div class="zone-select-trigger" 
                                         :class="{ 'active': open, 'has-value': selectedZone }" 
                                         @click="open = !open"
                                    >
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="zone-trigger-icon-circle">
                                                    <i class="mdi mdi-map-marker"></i>
                                                </div>
                                                <div class="text-start">
                                                    <div class="zone-trigger-label" x-text="selectedZone ? selectedZone.value : 'No Zone Associated'"></div>
                                                    <div class="zone-trigger-subtext" x-text="selectedZone ? 'Code: ' + selectedZone.key : 'All imported equipment will be general/global'"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <template x-if="selectedZone">
                                                    <button type="button" class="btn-clear-zone" @click.stop="clearSelection()">
                                                        <i class="mdi mdi-close-circle"></i>
                                                    </button>
                                                </template>
                                                <i class="mdi mdi-chevron-down zone-chevron" :class="{ 'rotated': open }"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dropdown Panel -->
                                    <div class="zone-dropdown-panel" 
                                         x-show="open" 
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 transform scale-100"
                                         x-transition:leave-end="opacity-0 transform scale-95"
                                         style="display: none;"
                                    >
                                        <!-- Search Box -->
                                        <div class="zone-search-wrapper">
                                            <i class="mdi mdi-magnify zone-search-icon"></i>
                                            <input type="text" 
                                                   class="form-control zone-search-input" 
                                                   placeholder="Search zones by name or code..." 
                                                   x-model="search"
                                                   @click.prevent.stop
                                            >
                                            <template x-if="search">
                                                <button type="button" class="zone-search-clear" @click.stop="search = ''">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Zones List -->
                                        <div class="zone-options-list">
                                            <!-- Global / No Zone option -->
                                            <div class="zone-option-item" 
                                                 :class="{ 'selected': !selectedId }" 
                                                 @click="clearSelection()"
                                            >
                                                <div class="d-flex align-items-center justify-content-between w-100">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="zone-option-icon-circle bg-light-gray">
                                                            <i class="mdi mdi-earth text-muted"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <span class="zone-option-name fw-bold">No Zone Association</span>
                                                            <span class="zone-option-description d-block text-muted small">Import as global records (general)</span>
                                                        </div>
                                                    </div>
                                                    <template x-if="!selectedId">
                                                        <i class="mdi mdi-check text-primary"></i>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Loop options -->
                                            <template x-for="zone in filteredZones" :key="zone.id">
                                                <div class="zone-option-item" 
                                                     :class="{ 'selected': selectedId == zone.id }" 
                                                     @click="selectZone(zone.id)"
                                                >
                                                    <div class="d-flex align-items-center justify-content-between w-100">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="zone-option-icon-circle">
                                                                <i class="mdi mdi-map-marker-radius text-primary"></i>
                                                            </div>
                                                            <div class="text-start">
                                                                <span class="zone-option-name fw-bold" x-text="zone.value"></span>
                                                                <span class="zone-option-code-pill" x-text="zone.key"></span>
                                                            </div>
                                                        </div>
                                                        <template x-if="selectedId == zone.id">
                                                            <i class="mdi mdi-check text-primary"></i>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- No results -->
                                            <template x-if="filteredZones.length === 0">
                                                <div class="zone-no-results text-center py-3 text-muted">
                                                    <i class="mdi mdi-alert-circle-outline mb-2 d-block" style="font-size: 1.25rem;"></i>
                                                    No zones found matching "<span x-text="search"></span>"
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    If selected, all imported equipment will be tied to this Zone.
                                </div>
                                @error('selectedZoneId') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3 text-start">
                                <label class="form-label fw-bold">{{ __('equipment.upload_excel_file') }} <span class="text-danger">*</span></label>
                                <input type="file" wire:model="bulkFile" class="form-control" accept=".xlsx,.xls,.csv">
                                @error('bulkFile') <span class="text-danger">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="bulkFile" class="mt-2">
                                    <small class="text-muted">
                                        <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.uploading_file') }}
                                    </small>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <small>
                                    <strong>Note:</strong> Duplicate equipment numbers will be skipped. Maximum file size: 5MB. Supported formats: .xlsx, .xls, .csv
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkUploadModal">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-success" wire:click="processBulkUpload" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="processBulkUpload">
                                <i class="mdi mdi-upload"></i> Upload & Import
                            </span>
                            <span wire:loading wire:target="processBulkUpload">
                                <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.processing') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        /* ── Equipment Table Width ────────────────────────────── */
        .table-responsive {
            width: 100%;
        }

        .equipment-table {
            width: 140%;
            /* min-width: 1400px; */
        }

        /* ── Equipment Action Buttons ─────────────────────────── */
        .equipment-actions-cell {
            width: 130px;
        }

        .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        /* EDIT Button - Blue */
        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        /* VIEW Button - Green */
        .rm-act-btn--view {
            border: 1px solid #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .rm-act-btn--view:hover {
            background: #dcfce7;
            border-color: #86efac;
        }

        /* DELETE Button - Red */
        .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .rm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        /* ─────────────────────────────────────────────────────── */

        .modal.show {
            display: block !important;
        }
    
        body.modal-open {
            overflow: hidden;
        }
    
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        /* ── Uniform form section headers ───────────────────── */
        .eq-section-header {
            background-color: #f4f6fb;
            border-left: 3px solid #001a41;
            padding: 7px 12px;
            margin-bottom: 16px;
            border-radius: 0 4px 4px 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #344767;
        }
        .eq-section-header .mdi {
            font-size: 13px;
            color: #001a41;
        }

        /* ── Uniform label style ─────────────────────────────── */
        .eq-form .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* ── Uniform input / select / textarea height & style ─ */
        .eq-form .form-control,
        .eq-form .tag-select-input {
            height: 38px;
            border-radius: 6px;
            border: 1px solid #d1d7e0;
            font-size: 0.875rem;
            color: #344767;
            background-color: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .eq-form textarea.form-control {
            height: auto;
            min-height: 68px;
            resize: vertical;
        }
        .eq-form .form-control:focus {
            border-color: #001a41;
            box-shadow: 0 0 0 0.15rem rgba(0, 26, 65, 0.15);
            outline: none;
        }

        /* Helper text */
        .eq-form .form-text {
            font-size: 0.75rem;
            color: #8898aa;
            margin-top: 3px;
        }

        /* Checkbox label alignment */
        .eq-form .form-check-label {
            font-size: 0.875rem;
            color: #344767;
        }

        /* Tag select inside eq-form */
        .eq-form .tag-select-input {
            padding: 4px 10px;
            min-height: 38px;
            height: auto;
        }
    
        /* Tag-based Dropdown Styling */
        .tag-select-container {
            position: relative;
            cursor: text;
        }

        .status-filter-container summary {
            list-style: none;
            cursor: pointer;
        }

        .status-filter-container summary::-webkit-details-marker {
            display: none;
        }

        .status-filter-container .status-filter-input {
            pointer-events: none;
            user-select: none;
            background: transparent;
        }

        .status-filter-container .status-filter-badge {
            background-color: #e7f1ff;
            color: #1d4f91;
        }

        .status-filter-container .status-filter-badge i {
            color: #1d4f91;
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

        .eq-form .tag-badge {
            background-color: #e7f1ff;
            color: #1d4f91;
        }

        .eq-form .tag-badge i {
            color: #1d4f91;
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

        .frequency-schedule-card {
            border: 1px solid #d8e2ef;
            border-radius: 10px;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 100%);
            box-shadow: 0 2px 10px rgba(17, 24, 39, 0.04);
            overflow: hidden;
        }

        .frequency-schedule-table thead th {
            background-color: #f2f7ff;
            color: #37517a;
            font-size: 0.77rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border-bottom: 1px solid #d8e2ef;
            padding: 10px 12px;
        }

        .frequency-schedule-table tbody td {
            vertical-align: middle;
            border-top: 1px solid #e8eef7;
            padding: 10px 12px;
            background-color: #ffffff;
        }

        .frequency-schedule-table tbody tr:first-child td {
            border-top: none;
        }

        .frequency-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            border-radius: 999px;
            background-color: #eaf2ff;
            color: #295a9b;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .frequency-label-input {
            height: 36px;
            border: 1px solid #ccd9ea;
            border-radius: 8px;
            background-color: #ffffff;
            font-size: 0.88rem;
            color: #2a3f5f;
        }

        .frequency-label-input:focus {
            border-color: #7ca6df;
            box-shadow: 0 0 0 0.2rem rgba(67, 114, 176, 0.12);
        }

        /* ── Premium Custom Zone Selector ────────────────────────── */
        .custom-zone-selector-wrapper {
            position: relative;
            width: 100%;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .zone-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px 14px;
            background: #ffffff;
            border: 1.5px solid #ccd9ea;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .zone-select-trigger:hover {
            border-color: #4372b0;
            background: #f8fafc;
        }

        .zone-select-trigger.active {
            border-color: #4372b0;
            background: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(67, 114, 176, 0.15);
        }

        .zone-select-trigger.has-value {
            border-color: #28a745;
            background: #f4fbf7;
        }

        .zone-select-trigger.has-value:hover {
            background: #eafcf1;
            border-color: #218838;
        }

        .zone-trigger-icon-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 6px;
            font-size: 1.1rem;
            transition: all 0.2s ease;
        }

        .zone-select-trigger.has-value .zone-trigger-icon-circle {
            background: #d4edda;
            color: #28a745;
        }

        .zone-trigger-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #2a3f5f;
            line-height: 1.2;
        }

        .zone-trigger-subtext {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 1px;
            line-height: 1.2;
        }

        .zone-select-trigger.has-value .zone-trigger-subtext {
            color: #28a745;
            font-weight: 500;
        }

        .btn-clear-zone {
            background: transparent;
            border: none;
            color: #6c757d;
            padding: 2px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color 0.15s, transform 0.15s;
        }

        .btn-clear-zone:hover {
            color: #dc3545;
            transform: scale(1.15);
        }

        .zone-chevron {
            color: #6c757d;
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

        .zone-chevron.rotated {
            transform: rotate(180deg);
            color: #4372b0;
        }

        /* Dropdown Panel */
        .zone-dropdown-panel {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1.5px solid #ccd9ea;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            z-index: 1050;
            overflow: hidden;
            padding: 6px;
        }

        /* Search Wrapper */
        .zone-search-wrapper {
            position: relative;
            margin-bottom: 6px;
        }

        .zone-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
        }

        .zone-search-input {
            width: 100% !important;
            height: 36px !important;
            padding: 8px 30px 8px 32px !important;
            border: 1px solid #ccd9ea !important;
            border-radius: 6px !important;
            font-size: 0.85rem !important;
            background: #f8fafc !important;
            color: #2a3f5f !important;
            transition: all 0.15s ease !important;
        }

        .zone-search-input:focus {
            border-color: #7ca6df !important;
            background: #ffffff !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .zone-search-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .zone-search-clear:hover {
            color: #2a3f5f;
        }

        /* Options List */
        .zone-options-list {
            max-height: 200px;
            overflow-y: auto;
            padding-right: 2px;
        }

        .zone-options-list::-webkit-scrollbar {
            width: 5px;
        }
        .zone-options-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .zone-options-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }
        .zone-options-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .zone-option-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 2px;
            transition: all 0.15s ease;
        }

        .zone-option-item:last-child {
            margin-bottom: 0;
        }

        .zone-option-item:hover {
            background: #f1f5f9;
        }

        .zone-option-item.selected {
            background: #e7f1ff;
        }

        .zone-option-icon-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #e7f1ff;
            border-radius: 6px;
            font-size: 1.1rem;
        }

        .zone-option-icon-circle.bg-light-gray {
            background: #f1f5f9;
        }

        .zone-option-name {
            font-size: 0.85rem;
            color: #2a3f5f;
        }

        .zone-option-code-pill {
            display: inline-block;
            padding: 1px 6px;
            background: #e2e8f0;
            color: #475569;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 6px;
            text-transform: uppercase;
        }

        .zone-option-item.selected .zone-option-code-pill {
            background: #cbd5e1;
            color: #1e293b;
        }

        .zone-no-results {
            padding: 15px 10px;
            font-size: 0.8rem;
        }
    </style>
    
    @script
    <script>
    // {{ __('equipment.close') }} dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container') && !e.target.closest('.modal')) {
            $wire.set('showEmployeeDropdown', false);
            $wire.set('showDepartmentDropdown', false);
            $wire.set('showAssetTypeDropdown', false);
            $wire.set('showAssetLocationDropdown', false);
        }
    });

    // Make status details dropdown open/close immediately and consistently.
    document.addEventListener('click', function (e) {
        const summary = e.target.closest('.status-filter-container > summary');
        if (!summary) {
            return;
        }

        e.preventDefault();
        const details = summary.parentElement;
        if (!details) {
            return;
        }

        details.open = !details.open;
    });

    document.addEventListener('click', function (e) {
        const option = e.target.closest('.status-filter-option');
        if (!option) {
            return;
        }

        const details = option.closest('.status-filter-container');
        if (details) {
            details.open = false;
        }
    });


    </script>
    @endscript
</div>



