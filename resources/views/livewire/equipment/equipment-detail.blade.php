<div class="container-fluid eq-view-page">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 eq-hero-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div>
                            <div class="eq-kicker mb-1">{{ __('equipment.equipment_management') }}</div>
                            <h2 class="mb-1 eq-hero-title d-flex align-items-center flex-wrap" style="gap: 10px;">
                                <span class="d-inline-flex align-items-center" style="gap: 8px;">
                                    <i class="mdi mdi-tools text-primary"></i>
                                    {{ $equipment->name ?? 'Equipment' }}
                                </span>
                                <span class="badge badge-light border px-3 py-2 fw-normal" style="font-size: 0.85rem;">{{ $equipment->equipment_number }}</span>
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.equipment_details_management') }}</p>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button wire:click="showEditEquipmentModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-pencil"></i> Edit
                            </button>
                            <a href="{{ route('equipment-home') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
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

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 eq-side-card">
                <!-- Card Header with Gradient -->
                <div class="card-header text-white text-center py-4 eq-side-header">
                    <div class="equipment-image-wrapper mb-3">
                        @if($equipment->picture && $equipment->picture != '/images/placeholder.png' && file_exists(public_path($equipment->picture)))
                            <img src="{{ $equipment->picture }}" 
                                 style="max-width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.2);" 
                                 alt="{{ $equipment->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px; background: rgba(255,255,255,0.2); border-radius: 50%; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.2); margin: 0 auto;">
                                <i class="mdi mdi-tools" style="font-size: 48px; opacity: 0.8;"></i>
                            </div>
                        @endif
                    </div>
                    <h5 class="mb-1 font-weight-bold">{{ $equipment->equipment_number }}</h5>
                    <p class="mb-0 small" style="opacity: 0.9;">{{ $equipment->name }}</p>
                </div>

                <div class="card-body px-4 py-4">
                    <!-- {{ __('equipment.description') }} -->
                    @if($equipment->description)
                        <div class="mb-4 pb-3" style="border-bottom: 1px solid #f0f0f0;">
                            <p class="text-muted mb-0" style="font-size: 14px; line-height: 1.6;">
                                {{ $equipment->description }}
                            </p>
                        </div>
                    @endif

                    @php
                        $calibration = $equipment->calibration_date();
                        $maintenance = $equipment->maintainance_date();
                    @endphp
                    
                    <!-- Alert Badges -->
                    <div class="alerts-section mb-4">
                        @if($calibration['status'] == 'badge-warning')
                            <div class="alert alert-warning mb-2 py-2 px-3 d-flex align-items-center" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                                <i class="mdi mdi-alert-decagram mr-2" style="font-size: 20px;"></i>
                                <span style="font-size: 13px; font-weight: 500;">Schedule Calibration</span>
                            </div>
                        @endif
                        @if($calibration['status'] == 'badge-danger')
                            <div class="alert alert-danger mb-2 py-2 px-3 d-flex align-items-center" style="border-radius: 12px; border-left: 4px solid #dc3545;">
                                <i class="mdi mdi-alert mr-2" style="font-size: 20px;"></i>
                                <span style="font-size: 13px; font-weight: 500;">Calibration Required</span>
                            </div>
                        @endif
                        @if($maintenance['status'] == 'badge-warning')
                            <div class="alert alert-warning mb-2 py-2 px-3 d-flex align-items-center" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                                <i class="mdi mdi-alert-decagram mr-2" style="font-size: 20px;"></i>
                                <span style="font-size: 13px; font-weight: 500;">Schedule Maintenance</span>
                            </div>
                        @endif
                        @if($maintenance['status'] == 'badge-danger')
                            <div class="alert alert-danger mb-2 py-2 px-3 d-flex align-items-center" style="border-radius: 12px; border-left: 4px solid #dc3545;">
                                <i class="mdi mdi-alert mr-2" style="font-size: 20px;"></i>
                                <span style="font-size: 13px; font-weight: 500;">Maintenance Required</span>
                            </div>
                        @endif
                    </div>

                    <!-- Equipment Details -->
                    <div class="equipment-specs">
                        <div class="spec-item mb-3 pb-3" style="border-bottom: 1px dashed #e9ecef;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-information text-primary mr-1"></i>Make
                                </span>
                                <span class="font-weight-600" style="font-size: 14px;">{{ $equipment->make }}</span>
                            </div>
                        </div>
                        <div class="spec-item mb-3 pb-3" style="border-bottom: 1px dashed #e9ecef;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-information-outline text-primary mr-1"></i>Model
                                </span>
                                <span class="font-weight-600" style="font-size: 14px;">{{ $equipment->model }}</span>
                            </div>
                        </div>
                        <div class="spec-item mb-3 pb-3" style="border-bottom: 1px dashed #e9ecef;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-calendar-month text-primary mr-1"></i>Purchased
                                </span>
                                <span class="font-weight-600" style="font-size: 14px;">{{ $equipment->date_purchased }}</span>
                            </div>
                        </div>
                        
                        <!-- Next Calibration -->
                        <div class="spec-item mb-3 p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-ruler-square-compass text-info mr-1"></i>Next Calibration
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size: 15px;">
                                    {{ $calibration['date']->toDateString() }}
                                </span>
                                <span class="badge {{ $calibration['status'] }}" style="padding: 6px 12px; border-radius: 20px; font-size: 11px;">
                                    {{ number_format(intval($calibration['remaining_days'])) }} days
                                </span>
                            </div>
                        </div>
                        
                        <!-- Next Maintenance -->
                        <div class="spec-item p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-pipe-wrench text-warning mr-1"></i>Next Maintenance
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size: 15px;">
                                    {{ $maintenance['date']->toDateString() }}
                                </span>
                                <span class="badge {{ $maintenance['status'] }}" style="padding: 6px 12px; border-radius: 20px; font-size: 11px;">
                                    {{ number_format(intval($maintenance['remaining_days'])) }} days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0 eq-main-card">
                <div class="card-header bg-light border-0 eq-main-header">
                    <div class="eq-main-tabs-wrap">
                        <button type="button" class="eq-tabs-scroll-btn eq-tabs-scroll-btn--prev" aria-label="Scroll tabs left" tabindex="-1">
                            <i class="mdi mdi-chevron-left"></i>
                        </button>
                        <div class="eq-main-tabs-scroll" data-eq-tabs-scroll>
                    <ul class="nav nav-tabs eq-main-tabs flex-nowrap" role="tablist">
                        @if(!$fromEquipmentChecks && !$fromEquipmentMaintenance)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'details' ? 'active' : '' }}"
                                    wire:click="setActiveTab('details')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'details' ? 'true' : 'false' }}">
                                <i class="mdi mdi-clipboard-text-outline"></i>
                                <span>{{ __('equipment.details') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'maintenance' ? 'active' : '' }}"
                                    wire:click="setActiveTab('maintenance')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'maintenance' ? 'true' : 'false' }}">
                                <i class="mdi mdi-wrench"></i>
                                <span>{{ __('equipment.maintenance_log') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'calibration' ? 'active' : '' }}"
                                    wire:click="setActiveTab('calibration')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'calibration' ? 'true' : 'false' }}">
                                <i class="mdi mdi-tune-vertical"></i>
                                <span>{{ __('equipment.calibration_log') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'verification' ? 'active' : '' }}"
                                    wire:click="setActiveTab('verification')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'verification' ? 'true' : 'false' }}">
                                <i class="mdi mdi-check-decagram" aria-hidden="true"></i>
                                <span>{{ __('equipment.verification_log') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'operators' ? 'active' : '' }}"
                                    wire:click="setActiveTab('operators')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'operators' ? 'true' : 'false' }}">
                                <i class="mdi mdi-account-group-outline"></i>
                                <span>{{ __('equipment.operators') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'attachments' ? 'active' : '' }}"
                                    wire:click="setActiveTab('attachments')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'attachments' ? 'true' : 'false' }}">
                                <i class="mdi mdi-paperclip"></i>
                                <span>{{ __('equipment.attachments') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'notifications' ? 'active' : '' }}"
                                    wire:click="setActiveTab('notifications')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'notifications' ? 'true' : 'false' }}">
                                <i class="mdi mdi-bell-outline"></i>
                                <span>{{ __('equipment.notifications') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'accessories' ? 'active' : '' }}"
                                    wire:click="setActiveTab('accessories')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'accessories' ? 'true' : 'false' }}">
                                <i class="mdi mdi-layers-outline"></i>
                                <span>Accessories</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'spareparts' ? 'active' : '' }}"
                                    wire:click="setActiveTab('spareparts')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'spareparts' ? 'true' : 'false' }}">
                                <i class="mdi mdi-cog-outline"></i>
                                <span>Spare Parts</span>
                            </button>
                        </li>
                        @endif
                        @if($fromEquipmentMaintenance)
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'annual-maintenance' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('annual-maintenance')" type="button">
                                <i class="mdi mdi-calendar-clock"></i> Annual (TSU/F/06)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'preventive-maintenance' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('preventive-maintenance')" type="button">
                                <i class="mdi mdi-shield-check"></i> Preventive (TSU/F/05)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'register-maintenance' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('register-maintenance')" type="button">
                                <i class="mdi mdi-book-open"></i> Maintenance Register
                            </button>
                        </li>
                        @endif
                        @if($equipment->requires_daily_log && !$fromEquipmentMaintenance)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'dailylog' ? 'active' : '' }}"
                                    wire:click="setActiveTab('dailylog')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'dailylog' ? 'true' : 'false' }}">
                                <i class="mdi mdi-notebook-check-outline" aria-hidden="true"></i>
                                <span>{{ __('equipment.daily_log_config') }}</span>
                            </button>
                        </li>
                        @endif
                        @if($equipment->has_logbook_tracking && !$fromEquipmentMaintenance)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'logbook' ? 'active' : '' }}"
                                    wire:click="setActiveTab('logbook')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'logbook' ? 'true' : 'false' }}">
                                <i class="mdi mdi-book-open-page-variant" aria-hidden="true"></i>
                                <span>Log Book</span>
                            </button>
                        </li>
                        @endif
                        @can('equipment.components.depreciation.view')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link eq-tab-link {{ $activeTab === 'asset-depreciation' ? 'active' : '' }}"
                                    wire:click="setActiveTab('asset-depreciation')" type="button" role="tab"
                                    aria-selected="{{ $activeTab === 'asset-depreciation' ? 'true' : 'false' }}">
                                <i class="mdi mdi-finance"></i>
                                <span>Asset Depreciation</span>
                            </button>
                        </li>
                        @endcan
                    </ul>
                        </div>
                        <div class="eq-tabs-fade eq-tabs-fade--start" aria-hidden="true"></div>
                        <div class="eq-tabs-fade eq-tabs-fade--end" aria-hidden="true"></div>
                        <button type="button" class="eq-tabs-scroll-btn eq-tabs-scroll-btn--next" aria-label="Scroll tabs right" tabindex="-1">
                            <i class="mdi mdi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body eq-main-body">
                    @if($activeTab === 'details')
                        @include('livewire.equipment.partials.equipment-details-tab')
                    @endif

                    @if($activeTab === 'asset-depreciation')
                        @livewire('equipment.equipment-depreciation-panel', ['equipmentId' => $equipment->id], key('depreciation-'.$equipment->id))
                    @endif

                    @if($activeTab === 'logbook' && $equipment->has_logbook_tracking)
                        @livewire('equipment.equipment-logbook-panel', ['equipmentId' => $equipment->id], key('logbook-'.$equipment->id))
                    @endif

                    <!-- Maintenance Log Tab -->
                    @if($activeTab === 'maintenance')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('equipment.maintenance_log') }}</h5>
                            <button wire:click="showCreateMaintenanceModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_maintenance_log') }}
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="maintenanceSearch" class="form-control form-control-sm" placeholder="{{ __('equipment.search_maintenance_logs') }}">
                             </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <div class="d-flex align-items-center">
                                 <span class="text-muted">
                                     Showing {{ $this->maintenanceLogs->firstItem() ?? 0 }} to {{ $this->maintenanceLogs->lastItem() ?? 0 }} of {{ $this->maintenanceLogs->total() }} entries
                                 </span>
                             </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table" style="width:120%">
                                <thead>
                                    <tr>
                                        <th>{{ __('equipment.actions') }}</th>
                                        <th>{{ __('equipment.type') }}</th>
                                        <th>{{ __('equipment.service_provider') }}</th>
                                        <th>{{ __('equipment.date') }}</th>
                                        <th>{{ __('equipment.certificate') }}</th>
                                        <th>{{ __('equipment.overseen_by') }}</th>
                                        <th>{{ __('equipment.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->maintenanceLogs as $log)
                                            <tr>
                                                <td>
                                                    <button wire:click="showEditMaintenanceModal('{{ $log->id }}')"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--edit equipment-action-btn">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="openDeleteMaintenanceConfirmModal('{{ $log->id }}')"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--delete equipment-action-btn"
                                                            title="{{ __('equipment.delete') }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $log->maintainance_type == 'in-house' ? __('equipment.in_house') : __('equipment.external') }}</td>
                                                <td>
                                                    {{ $log->maintainance_type != 'in-house' 
                                                        ? (\App\Supplier::find($log->supplier_id)->name ?? '-') 
                                                        : (getUserById($log->employee_id)->name ?? '-') }}
                                                </td>
                                                <td>{{ $log->date }}</td>
                                                <td>
                                                    @if($log->certificate && $log->certificate != 'no-document')
                                                        <a href="{{ $log->certificate }}" target="_blank" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-download"></i> {{ __('equipment.download') }}
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $log->overseer()->name ?? '-' }}</td>
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-sm btn-info"
                                                            wire:click="openMaintenanceNotesModal('{{ $log->id }}')"
                                                            title="{{ __('equipment.view') }}">
                                                        <i class="mdi mdi-eye"></i> {{ __('equipment.view') }}
                                                    </button>
                                                </td>
                                            </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $this->maintenanceLogs->links() }}
                        </div>
                    @endif

                    <!-- Calibration Log Tab -->
                    @if($activeTab === 'calibration')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('equipment.calibration_log') }}</h5>
                            <button wire:click="showCreateCalibrationModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_calibration_log') }}
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="calibrationSearch" class="form-control form-control-sm" placeholder="{{ __('equipment.search_calibration_logs') }}">
                             </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <div class="d-flex align-items-center">
                                 <span class="text-muted">
                                     Showing {{ $this->calibrationLogs->firstItem() ?? 0 }} to {{ $this->calibrationLogs->lastItem() ?? 0 }} of {{ $this->calibrationLogs->total() }} entries
                                 </span>
                             </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table" style="width:120%">
                                <thead>
                                    <tr>
                                        <th>{{ __('equipment.actions') }}</th>
                                        <th>{{ __('equipment.type') }}</th>
                                        <th>{{ __('equipment.service_provider') }}</th>
                                        <th>{{ __('equipment.date') }}</th>
                                        <th>{{ __('equipment.correction_factor') }}</th>
                                        <th>{{ __('equipment.uncertainty_of_measure_abbr') }}</th>
                                        <th>{{ __('equipment.certificate') }}</th>
                                        <th>{{ __('equipment.overseen_by') }}</th>
                                        <th>{{ __('equipment.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->calibrationLogs as $log)
                                            <tr>
                                                <td>
                                                    <button wire:click="showEditCalibrationModal('{{ $log->id }}')"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--edit equipment-action-btn">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="openDeleteCalibrationConfirmModal('{{ $log->id }}')"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete equipment-action-btn"
                                                        title="{{ __('equipment.delete') }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $log->maintainance_type == 'in-house' ? __('equipment.in_house') : __('equipment.external') }}</td>
                                                <td>
                                                    {{ $log->maintainance_type != 'in-house' 
                                                        ? (\App\Supplier::find($log->supplier_id)->name ?? '-') 
                                                        : (getUserById($log->employee_id)->name ?? '-') }}
                                                </td>
                                                <td>{{ $log->date }}</td>
                                                <td>{{ $log->correction_factor ? number_format($log->correction_factor, 1) : '-' }}</td>
                                                <td>{{ $log->uncertainty_of_measure ? number_format($log->uncertainty_of_measure, 1) : '-' }}</td>
                                                <td>
                                                    @if($log->certificate && $log->certificate != 'no-document')
                                                        <a href="{{ $log->certificate }}" target="_blank" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-download"></i> {{ __('equipment.download') }}
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $log->overseer()->name ?? '-' }}</td>
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-sm btn-info"
                                                            wire:click="openCalibrationNotesModal('{{ $log->id }}')"
                                                            title="{{ __('equipment.view') }}">
                                                        <i class="mdi mdi-eye"></i> {{ __('equipment.view') }}
                                                    </button>
                                                </td>
                                            </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $this->calibrationLogs->links() }}
                        </div>
                    @endif

                    <!-- Verification Log Tab -->
                    @if($activeTab === 'verification')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('equipment.verification_log') }}</h5>
                            <button wire:click="showCreateVerificationModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_verification_log') }}
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="verificationSearch" class="form-control form-control-sm" placeholder="{{ __('equipment.search_verification_logs') }}">
                             </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <div class="d-flex align-items-center">
                                 <span class="text-muted">
                                     Showing {{ $this->verificationLogs->firstItem() ?? 0 }} to {{ $this->verificationLogs->lastItem() ?? 0 }} of {{ $this->verificationLogs->total() }} entries
                                 </span>
                             </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table" style="width:120%">
                                <thead>
                                    <tr>
                                        <th>{{ __('equipment.actions') }}</th>
                                        <th>{{ __('equipment.type') }}</th>
                                        <th>{{ __('equipment.reference_standards') }}</th>
                                        <th>{{ __('equipment.service_performer') }}</th>
                                        <th>{{ __('equipment.verification_date') }}</th>
                                        <th>{{ __('equipment.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->verificationLogs as $log)
                                        <tr>
                                            <td>
                                                <button wire:click="showEditVerificationModal('{{ $log->id }}')"
                                                    class="btn btn-sm rm-act-btn rm-act-btn--edit equipment-action-btn">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="openDeleteVerificationConfirmModal('{{ $log->id }}')"
                                                    class="btn btn-sm rm-act-btn rm-act-btn--delete equipment-action-btn"
                                                    title="{{ __('equipment.delete') }}">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                            <td>{{ $log->maintainance_type == 'in-house' ? __('equipment.in_house') : __('equipment.external') }}</td>
                                            <td>{{ $log->reference_standard }}</td>
                                            <td>
                                                {{ $log->maintainance_type != 'in-house' 
                                                    ? (\App\Supplier::find($log->supplier_id)->name ?? '-') 
                                                    : (getUserById($log->operator_id)->name ?? '-') }}
                                            </td>
                                            <td>{{ $log->verification_date }}</td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-info"
                                                        wire:click="openVerificationDetailsModal('{{ $log->id }}')"
                                                        title="{{ __('equipment.view_details') }}">
                                                    <i class="mdi mdi-eye"></i> {{ __('equipment.view_details') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $this->verificationLogs->links() }}
                        </div>
                    @endif

                    <!-- Operators Tab -->
                    @if($activeTab === 'operators')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('equipment.operators') }}</h5>
                            <button wire:click="openOperatorModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_operator') }}
                            </button>
                        </div>
                        <div class="p-3">
                            @php
                                $operators = $equipment->operators();
                            @endphp
                            @foreach($operators as $operator)
                                <div class="d-inline-block m-2">
                                    <span class="badge badge-primary p-2">
                                        <i class="mdi mdi-account"></i> {{ $operator->operator()->name ?? __('equipment.not_available') }}
                                        <button wire:click="removeOperator('{{ $operator->id }}')"
                                                class="btn btn-sm btn-link text-white p-0 ms-2"
                                                onclick="return confirm('Remove this operator?')">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </span>
                                </div>
                            @endforeach
                            @if($operators->count() == 0)
                                <div class="alert alert-info">
                                    <i class="mdi mdi-alert"></i> {{ __('equipment.no_operators_assigned_yet') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Attachments Tab -->
                    @if($activeTab === 'attachments')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('equipment.attachments') }}</h5>
                            <button wire:click="showCreateAttachmentModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_attachment') }}
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="attachmentSearch" class="form-control form-control-sm" placeholder="{{ __('equipment.search_attachments') }}">
                             </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <div class="d-flex align-items-center">
                                 <span class="text-muted">
                                     Showing {{ $this->attachments->firstItem() ?? 0 }} to {{ $this->attachments->lastItem() ?? 0 }} of {{ $this->attachments->total() }} entries
                                 </span>
                             </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('equipment.actions') }}</th>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>{{ __('equipment.attachment') }}</th>
                                        <th>{{ __('equipment.uploaded_by') }}</th>
                                        <th>{{ __('equipment.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->attachments as $attachment)
                                        <tr>
                                            <td class="equipment-actions-cell">
                                                <div class="equipment-actions-group">
                                                    <button wire:click="showEditAttachmentModal('{{ $attachment->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit equipment-action-btn"
                                                            title="{{ __('equipment.edit') }}">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="openDeleteAttachmentConfirmModal('{{ $attachment->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete equipment-action-btn"
                                                            title="{{ __('equipment.delete') }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>{{ $loop->iteration + ($this->attachments->currentPage() - 1) * $this->attachments->perPage() }}</td>
                                            <td>{{ $attachment->title }}</td>
                                            <td>
                                                <a href="{{ $attachment->attachment }}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="mdi mdi-download"></i> {{ __('equipment.download') }}
                                                </a>
                                            </td>
                                            <td>{{ getUserById($attachment->upload_by)->name ?? __('equipment.not_available') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#attachmentDescModal{{ $attachment->id }}">
                                                    <i class="mdi mdi-eye"></i> {{ __('equipment.view') }}
                                                </button>
                                                <div class="modal fade" id="attachmentDescModal{{ $attachment->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ __('equipment.description') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>{{ $attachment->description ?? __('equipment.no_description') }}</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('equipment.close') }}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $this->attachments->links() }}
                        </div>
                    @endif

                    <!-- Notifications Tab -->
                    @if($activeTab === 'notifications')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('equipment.notification_frequency') }}</h5>
                            <button wire:click="showCreateNotificationModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_notification') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover equipment-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('equipment.actions') }}</th>
                                        <th>{{ __('equipment.frequency') }}</th>
                                        <th>{{ __('equipment.notification_type') }}</th>
                                        <th>{{ __('equipment.notification_date') }}</th>
                                        <th>{{ __('equipment.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->notifications as $notification)
                                        <tr>
                                            <td>
                                                <button wire:click="showEditNotificationModal('{{ $notification->id }}')"
                                                    class="btn btn-sm rm-act-btn rm-act-btn--edit equipment-action-btn">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="openDeleteNotificationConfirmModal('{{ $notification->id }}')"
                                                    class="btn btn-sm rm-act-btn rm-act-btn--delete equipment-action-btn">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                            <td>{{ $notification->value }} {{ $notification->frequency }}</td>
                                            <td>{{ ucwords($notification->notification_type) }}</td>
                                            <td>{{ $notification->next_date }}</td>
                                            <td>
                                                @if($notification->is_sent)
                                                    <span class="badge badge-success">{{ __('equipment.sent') }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ __('equipment.not_sent') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Daily Log Configuration Tab -->
                    @if($activeTab === 'dailylog' && $equipment->requires_daily_log)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="mdi mdi-notebook-check-outline"></i> {{ __('equipment.daily_log_configuration') }}</h5>
                            <button wire:click="showEditEquipmentModal" class="btn btn-outline-primary btn-sm equipment-add-btn">
                                <i class="mdi mdi-pencil"></i> {{ __('equipment.edit_configuration') }}
                            </button>
                        </div>
                        @php
                            $dlNatureLabel = match($equipment->daily_log_nature) {
                                'qualitative'  => 'Qualitative',
                                'quantitative' => 'Quantitative',
                                default        => '-',
                            };
                            $dlTypeLabel = match($equipment->daily_log_value_type) {
                                'constant' => 'Constant',
                                'range'    => 'Range',
                                default    => '-',
                            };
                        @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th class="text-muted" style="width:50%">{{ __('equipment.value_type') }}</th>
                                        <td>
                                            <span class="badge badge-info">{{ $dlTypeLabel }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">{{ __('equipment.nature_of_results') }}</th>
                                        <td>
                                            <span class="badge badge-secondary">{{ $dlNatureLabel }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">{{ __('equipment.reporting_unit') }}</th>
                                        <td>{{ $equipment->daily_log_reporting_unit ?: '-' }}</td>
                                    </tr>
                                    @php
                                        $freqLabels = [1=>__('equipment.once_a_day'),2=>__('equipment.twice_a_day'),3=>__('equipment.three_times_a_day'),4=>__('equipment.four_times_a_day'),5=>__('equipment.five_times_a_day'),6=>__('equipment.six_times_a_day')];
                                        $equipFreq = $equipment->daily_log_frequency ?? 1;
                                    @endphp
                                    <tr>
                                        <th class="text-muted">{{ __('equipment.logging_frequency') }}</th>
                                        <td>{{ $freqLabels[$equipFreq] ?? __('equipment.once_a_day') }}</td>
                                    </tr>
                                    @if($equipFreq >= 2)
                                    <tr>
                                        <th class="text-muted">{{ __('equipment.time_interval') }}</th>
                                        <td>{{ __('equipment.every_hours', ['hours' => $equipment->daily_log_time_interval]) }}</td>
                                    </tr>
                                    @endif
                                    @if($equipment->daily_log_value_type === 'constant')
                                    <tr>
                                        <th class="text-muted">{{ __('equipment.expected_value') }}</th>
                                        <td>
                                            {{ $equipment->daily_log_expected_value ?: '-' }}
                                            @if($equipment->daily_log_reporting_unit)
                                                <small class="text-muted"> {{ $equipment->daily_log_reporting_unit }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($equipment->daily_log_nature === 'quantitative')
                                    <tr>
                                        <th class="text-muted">Tolerance</th>
                                        <td>
                                            @if($equipment->daily_log_tolerance)
                                                &plusmn;{{ $equipment->daily_log_tolerance }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endif
                                    @if($equipment->daily_log_value_type === 'range')
                                    <tr>
                                        <th class="text-muted">{{ __('equipment.acceptable_range') }}</th>
                                        <td>
                                            {{ $equipment->daily_log_expected_min }} &ndash; {{ $equipment->daily_log_expected_max }}
                                            @if($equipment->daily_log_reporting_unit)
                                                <small class="text-muted"> {{ $equipment->daily_log_reporting_unit }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info" style="border-left: 4px solid #17a2b8;">
                                    <p class="mb-1"><strong><i class="mdi mdi-information-outline"></i> Summary</strong></p>
                                    @if($equipment->daily_log_value_type === 'constant' && $equipment->daily_log_nature === 'qualitative')
                                        <p class="mb-0 small">This equipment requires a <strong>qualitative</strong> daily check. The expected result is <strong>"{{ $equipment->daily_log_expected_value }}"</strong>.</p>
                                    @elseif($equipment->daily_log_value_type === 'constant' && $equipment->daily_log_nature === 'quantitative')
                                        <p class="mb-0 small">This equipment requires a <strong>quantitative</strong> daily reading. Expected value: <strong>{{ $equipment->daily_log_expected_value }}{{ $equipment->daily_log_reporting_unit ? ' ' . $equipment->daily_log_reporting_unit : '' }}</strong>
                                        @if($equipment->daily_log_tolerance), with a tolerance of &plusmn;{{ $equipment->daily_log_tolerance }}%@endif.</p>
                                    @elseif($equipment->daily_log_value_type === 'range')
                                        <p class="mb-0 small">This equipment requires a daily reading within the range <strong>{{ $equipment->daily_log_expected_min }} &ndash; {{ $equipment->daily_log_expected_max }}{{ $equipment->daily_log_reporting_unit ? ' ' . $equipment->daily_log_reporting_unit : '' }}</strong>.</p>
                                    @else
                                        <p class="mb-0 small">{{ __('equipment.configuration_incomplete_click_edit_configuration') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ── Performance Chart ──────────────────────────────────────────── --}}
                        @if($equipment->daily_log_value_type === 'range' || $equipment->daily_log_nature === 'quantitative')
                            <script id="dl-chart-data" type="application/json">@json($this->dailyLogChartData)</script>
                            <hr>
                            <h6 class="mt-3 mb-2"><i class="mdi mdi-chart-line"></i> Performance Over Time <small class="text-muted">(last 60 days)</small></h6>
                            @if(empty(($this->dailyLogChartData)['labels'] ?? []))
                                <div class="alert alert-light py-2 text-muted small">
                                    <i class="mdi mdi-information-outline"></i> No data recorded yet — entries will appear here once readings are saved.
                                </div>
                            @else
                                <div wire:ignore>
                                    <canvas id="dl-perf-chart" height="80"></canvas>
                                </div>
                            @endif
                        @endif

                        {{-- ── Non-Conformance Report ──────────────────────────────────────── --}}
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                            <h6 class="mb-0">
                                <i class="mdi mdi-alert-circle-outline text-danger"></i> Non-Conformance Report
                            </h6>
                            @if(!empty($this->nonConformanceReport))
                                <button wire:click="exportNonConformanceReport" class="btn btn-sm btn-outline-success">
                                    <i class="mdi mdi-file-excel-outline"></i> Export to Excel
                                </button>
                            @endif
                        </div>
                        <div class="row align-items-end mb-2">
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">From Date</label>
                                <input type="date" wire:model.live="nonConformanceFromDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">To Date</label>
                                <input type="date" wire:model.live="nonConformanceToDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Per Page</label>
                                <div class="tag-select-container equipment-tag-select equipment-tag-select--compact" wire:click="$toggle('showNonConformancePerPageDropdown')">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        <span class="tag-badge">{{ $nonConformancePerPage }}</span>
                                    </div>
                                    @if($showNonConformancePerPageDropdown ?? false)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item" wire:click.stop="$set('nonConformancePerPage', 10)">10</div>
                                            <div class="tag-dropdown-item" wire:click.stop="$set('nonConformancePerPage', 25)">25</div>
                                            <div class="tag-dropdown-item" wire:click.stop="$set('nonConformancePerPage', 50)">50</div>
                                            <div class="tag-dropdown-item" wire:click.stop="$set('nonConformancePerPage', 100)">100</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(empty($this->nonConformanceReport))
                            <div class="alert alert-success py-2">
                                <i class="mdi mdi-check-circle-outline"></i> No non-conformances recorded for this equipment.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('equipment.date') }}</th>
                                            <th style="width:70px">Slot #</th>
                                            <th>Recorded Value</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->nonConformanceReportPage as $ncRow)
                                        <tr>
                                            <td>{{ $ncRow['date'] }}</td>
                                            <td class="text-center">{{ $ncRow['slot'] }}</td>
                                            <td><code>{{ $ncRow['recorded'] }}</code></td>
                                            <td class="text-danger small">{{ $ncRow['reason'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    Showing {{ count($this->nonConformanceReportPage) }} of {{ count($this->nonConformanceReport) }} non-conformance entries
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary"
                                            wire:click="previousNonConformancePage"
                                            @disabled($nonConformancePage <= 1)>
                                        <i class="mdi mdi-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" disabled>
                                        Page {{ $nonConformancePage }} of {{ $this->nonConformanceTotalPages }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary"
                                            wire:click="nextNonConformancePage"
                                            @disabled($nonConformancePage >= $this->nonConformanceTotalPages)>
                                        <i class="mdi mdi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                    @endif

                    <!-- Accessories Tab -->
                    @if($activeTab === 'accessories')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="mdi mdi-layers-outline text-primary"></i> Accessories</h5>
                            <button wire:click="showAddAccessoryModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Accessory
                            </button>
                        </div>
                        @if(session()->has('message') && $activeTab === 'accessories')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if($equipment->accessories->isEmpty())
                            <div class="alert alert-info py-3">
                                <i class="mdi mdi-information-outline"></i> No accessories registered for this equipment. Click "Add Accessory" to register one.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Part Number</th>
                                            <th>Serial Number</th>
                                            <th>Description</th>
                                            <th style="width: 180px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipment->accessories as $accessory)
                                            <tr>
                                                <td class="fw-bold">{{ $accessory->name }}</td>
                                                <td><code>{{ $accessory->part_number ?: '—' }}</code></td>
                                                <td><code>{{ $accessory->serial_number ?: '—' }}</code></td>
                                                <td>{{ $accessory->description ?: '—' }}</td>
                                                <td class="text-center">
                                                    <button wire:click="editAccessory('{{ $accessory->id }}')" class="btn btn-xs btn-outline-info mr-1" title="Edit">
                                                        <i class="mdi mdi-pencil-outline"></i> Edit
                                                    </button>
                                                    <button onclick="confirm('Are you sure you want to delete this accessory?') || event.stopImmediatePropagation()" wire:click="deleteAccessory('{{ $accessory->id }}')" class="btn btn-xs btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-trash-can-outline"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    <!-- Spare Parts Tab -->
                    @if($activeTab === 'spareparts')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="mdi mdi-wrench-outline text-primary"></i> Spare Parts</h5>
                            <button wire:click="showAddSparePartModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Spare Part
                            </button>
                        </div>
                        @if(session()->has('message') && $activeTab === 'spareparts')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if($equipment->spareParts->isEmpty())
                            <div class="alert alert-info py-3">
                                <i class="mdi mdi-information-outline"></i> No spare parts registered for this equipment. Click "Add Spare Part" to register one.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Part Number</th>
                                            <th>Serial Number</th>
                                            <th>Description</th>
                                            <th style="width: 180px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipment->spareParts as $sparePart)
                                            <tr>
                                                <td class="fw-bold">{{ $sparePart->name }}</td>
                                                <td><code>{{ $sparePart->part_number ?: '—' }}</code></td>
                                                <td><code>{{ $sparePart->serial_number ?: '—' }}</code></td>
                                                <td>{{ $sparePart->description ?: '—' }}</td>
                                                <td class="text-center">
                                                    <button wire:click="editSparePart('{{ $sparePart->id }}')" class="btn btn-xs btn-outline-info mr-1" title="Edit">
                                                        <i class="mdi mdi-pencil-outline"></i> Edit
                                                    </button>
                                                    <button onclick="confirm('Are you sure you want to delete this spare part?') || event.stopImmediatePropagation()" wire:click="deleteSparePart('{{ $sparePart->id }}')" class="btn btn-xs btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-trash-can-outline"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    <!-- Annual Maintenance (TSU/F/06) Tab -->
                    @if($activeTab === 'annual-maintenance')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="mdi mdi-calendar-clock text-primary"></i> Annual Maintenance Program (TSU/F/06)</h5>
                            <button wire:click="openAnnualModal()" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Annual Record
                            </button>
                        </div>
                        @if(session()->has('message') && $activeTab === 'annual-maintenance')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if($equipment->annualMaintenances->isEmpty())
                            <div class="alert alert-info py-3">
                                <i class="mdi mdi-information-outline"></i> No annual maintenance records registered for this equipment. Click "Add Annual Record" to register one.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Serviced Date</th>
                                            <th>Status</th>
                                            <th>Next Service</th>
                                            <th>Remark</th>
                                            <th style="width: 180px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipment->annualMaintenances as $record)
                                            <tr>
                                                <td class="fw-bold">{{ $record->serviced_date ? $record->serviced_date->format('M d, Y') : '—' }}</td>
                                                <td>
                                                    @if($record->status)
                                                        @php
                                                            $statusClass = 'badge-secondary';
                                                            if (strtolower($record->status) === 'active' || strtolower($record->status) === 'completed') $statusClass = 'badge-success';
                                                            elseif (strtolower($record->status) === 'pending') $statusClass = 'badge-warning';
                                                            elseif (strtolower($record->status) === 'overdue' || strtolower($record->status) === 'critical') $statusClass = 'badge-danger';
                                                        @endphp
                                                        <span class="badge {{ $statusClass }} px-2 py-1">{{ ucfirst($record->status) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-primary fw-bold">{{ $record->next_service ? $record->next_service->format('M d, Y') : '—' }}</td>
                                                <td>{{ $record->remark ?: '—' }}</td>
                                                <td class="text-center">
                                                    <button wire:click="openAnnualModal('{{ $record->id }}')" class="btn btn-xs btn-outline-info mr-1" title="Edit">
                                                        <i class="mdi mdi-pencil-outline"></i> Edit
                                                    </button>
                                                    <button onclick="confirm('Are you sure you want to delete this record?') || event.stopImmediatePropagation()" wire:click="deleteAnnual('{{ $record->id }}')" class="btn btn-xs btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-trash-can-outline"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    <!-- Preventive Maintenance (TSU/F/05) Tab -->
                    @if($activeTab === 'preventive-maintenance')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="mdi mdi-shield-check text-primary"></i> Equipment Preventive Maintenance Program (TSU/F/05)</h5>
                            <button wire:click="openPreventiveModal()" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Preventive Record
                            </button>
                        </div>
                        @if(session()->has('message') && $activeTab === 'preventive-maintenance')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if($equipment->preventiveMaintenances->isEmpty())
                            <div class="alert alert-info py-3">
                                <i class="mdi mdi-information-outline"></i> No preventive maintenance records registered for this equipment. Click "Add Preventive Record" to register one.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Scheduled Month</th>
                                            <th>Status</th>
                                            <th>Serviced Date</th>
                                            <th>Notes</th>
                                            <th style="width: 180px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipment->preventiveMaintenances as $record)
                                            <tr>
                                                <td class="fw-bold">{{ \Carbon\Carbon::create(null, $record->scheduled_month ?: 1, 1)->format('F') }}</td>
                                                <td>
                                                    @if($record->is_serviced)
                                                        <span class="badge badge-success px-2 py-1">Serviced</span>
                                                    @else
                                                        <span class="badge badge-warning px-2 py-1">Scheduled</span>
                                                    @endif
                                                </td>
                                                <td>{{ $record->serviced_date ? $record->serviced_date->format('M d, Y') : '—' }}</td>
                                                <td>{{ $record->notes ?: '—' }}</td>
                                                <td class="text-center align-middle">
                                                    <button wire:click="openPreventiveModal('{{ $record->id }}')" class="btn btn-xs btn-outline-info mr-1" title="Edit">
                                                        <i class="mdi mdi-pencil-outline"></i> Edit
                                                    </button>
                                                    <button onclick="confirm('Are you sure you want to delete this record?') || event.stopImmediatePropagation()" wire:click="deletePreventive('{{ $record->id }}')" class="btn btn-xs btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-trash-can-outline"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    <!-- Maintenance Register Tab -->
                    @if($activeTab === 'register-maintenance')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="mdi mdi-book-open text-primary"></i> DSM - Equipment Maintenance Register</h5>
                            <button wire:click="openRegisterModal()" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Register Entry
                            </button>
                        </div>
                        @if(session()->has('message') && $activeTab === 'register-maintenance')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if($equipment->maintenanceRegisters->isEmpty())
                            <div class="alert alert-info py-3">
                                <i class="mdi mdi-information-outline"></i> No maintenance register entries registered for this equipment. Click "Add Register Entry" to register one.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Year</th>
                                            <th>Service Provider</th>
                                            <th>Type of Service</th>
                                            <th class="text-right">Cost (USD)</th>
                                            <th class="text-right">Cost (TZS)</th>
                                            <th style="width: 180px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipment->maintenanceRegisters as $record)
                                            <tr>
                                                <td class="fw-bold">{{ $record->year ?: '—' }}</td>
                                                <td>{{ $record->service_provider ?: '—' }}</td>
                                                <td>{{ $record->service_type ?: '—' }}</td>
                                                <td class="text-right text-success fw-bold">${{ number_format($record->cost_usd, 2) }}</td>
                                                <td class="text-right text-secondary fw-bold">{{ number_format($record->cost_tzs, 2) }} TZS</td>
                                                <td class="text-center">
                                                    <button wire:click="openRegisterModal('{{ $record->id }}')" class="btn btn-xs btn-outline-info mr-1" title="Edit">
                                                        <i class="mdi mdi-pencil-outline"></i> Edit
                                                    </button>
                                                    <button onclick="confirm('Are you sure you want to delete this record?') || event.stopImmediatePropagation()" wire:click="deleteRegister('{{ $record->id }}')" class="btn btn-xs btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-trash-can-outline"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td colspan="3">Total for this equipment:</td>
                                            <td class="text-right text-success">${{ number_format($equipment->maintenanceRegisters->sum('cost_usd'), 2) }}</td>
                                            <td class="text-right text-secondary">{{ number_format($equipment->maintenanceRegisters->sum('cost_tzs'), 2) }} TZS</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Accessory Modal -->
    @if($showAccessoryModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $accessoryForm['id'] ? 'pencil' : 'plus' }}"></i>
                            {{ $accessoryForm['id'] ? 'Edit' : 'Add' }} Accessory
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showAccessoryModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="saveAccessory">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="accessoryForm.name" class="form-control" placeholder="e.g. Temperature Probe" required>
                                @error('accessoryForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" wire:model="accessoryForm.part_number" class="form-control" placeholder="e.g. ACC-102">
                                @error('accessoryForm.part_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Serial Number</label>
                                <input type="text" wire:model="accessoryForm.serial_number" class="form-control" placeholder="e.g. SN-89231">
                                @error('accessoryForm.serial_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="accessoryForm.description" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                                @error('accessoryForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showAccessoryModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Accessory</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Spare Part Modal -->
    @if($showSparePartModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $sparePartForm['id'] ? 'pencil' : 'plus' }}"></i>
                            {{ $sparePartForm['id'] ? 'Edit' : 'Add' }} Spare Part
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showSparePartModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="saveSparePart">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="sparePartForm.name" class="form-control" placeholder="e.g. Replacement Filter" required>
                                @error('sparePartForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" wire:model="sparePartForm.part_number" class="form-control" placeholder="e.g. FLT-409">
                                @error('sparePartForm.part_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Serial Number</label>
                                <input type="text" wire:model="sparePartForm.serial_number" class="form-control" placeholder="e.g. SN-77291">
                                @error('sparePartForm.serial_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="sparePartForm.description" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                                @error('sparePartForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showSparePartModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Spare Part</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Annual Maintenance Modal -->
    @if($showAnnualModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $annualId ? 'pencil' : 'plus' }}"></i>
                            {{ $annualId ? 'Edit' : 'Add' }} Annual Maintenance Record (TSU/F/06)
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showAnnualModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="saveAnnual">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Serviced Date</label>
                                <input type="date" wire:model="annual_serviced_date" class="form-control">
                                @error('annual_serviced_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Status</label>
                                <select wire:model="annual_status" class="form-control">
                                    <option value="">-- Select Status --</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Overdue">Overdue</option>
                                    <option value="Critical">Critical</option>
                                </select>
                                @error('annual_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Next Service Date</label>
                                <input type="date" wire:model="annual_next_service" class="form-control">
                                @error('annual_next_service') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Remark</label>
                                <textarea wire:model="annual_remark" class="form-control" rows="3" placeholder="Enter remarks..."></textarea>
                                @error('annual_remark') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showAnnualModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Preventive Maintenance Modal -->
    @if($showPreventiveModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $preventiveId ? 'pencil' : 'plus' }}"></i>
                            {{ $preventiveId ? 'Edit' : 'Add' }} Preventive Maintenance Record (TSU/F/05)
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showPreventiveModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="savePreventive">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Beginning of Year <span class="text-danger">*</span></label>
                                <input type="date" wire:model="preventive_year_start" class="form-control" required>
                                @error('preventive_year_start') <span class="text-danger">{{ $message }}</span> @enderror
                                <small class="text-muted">Quarters will be automatically calculated starting from this date.</small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">1st Quarter Status</label>
                                <select wire:model="preventive_q1_status" class="form-control">
                                    <option value="">-- Select Status --</option>
                                    <option value="Done">Done</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Pending">Pending</option>
                                    <option value="N/A">N/A</option>
                                </select>
                                @error('preventive_q1_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">2nd Quarter Status</label>
                                <select wire:model="preventive_q2_status" class="form-control">
                                    <option value="">-- Select Status --</option>
                                    <option value="Done">Done</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Pending">Pending</option>
                                    <option value="N/A">N/A</option>
                                </select>
                                @error('preventive_q2_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">3rd Quarter Status</label>
                                <select wire:model="preventive_q3_status" class="form-control">
                                    <option value="">-- Select Status --</option>
                                    <option value="Done">Done</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Pending">Pending</option>
                                    <option value="N/A">N/A</option>
                                </select>
                                @error('preventive_q3_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">4th Quarter Status</label>
                                <select wire:model="preventive_q4_status" class="form-control">
                                    <option value="">-- Select Status --</option>
                                    <option value="Done">Done</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Pending">Pending</option>
                                    <option value="N/A">N/A</option>
                                </select>
                                @error('preventive_q4_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showPreventiveModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Maintenance Register Modal -->
    @if($showRegisterModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $registerId ? 'pencil' : 'plus' }}"></i>
                            {{ $registerId ? 'Edit' : 'Add' }} Maintenance Register Entry
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showRegisterModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="saveRegister">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Year / Financial Year</label>
                                <input type="text" wire:model="register_year" class="form-control" placeholder="e.g. 2024/2025">
                                @error('register_year') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Service Provider</label>
                                <input type="text" wire:model="register_service_provider" class="form-control" placeholder="e.g. GCLA Tech Services">
                                @error('register_service_provider') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Type of Service</label>
                                <input type="text" wire:model="register_service_type" class="form-control" placeholder="e.g. Annual Calibration">
                                @error('register_service_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Cost (USD)</label>
                                <input type="number" step="0.01" wire:model="register_cost_usd" class="form-control" placeholder="0.00">
                                @error('register_cost_usd') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Cost (TZS)</label>
                                <input type="number" step="0.01" wire:model="register_cost_tzs" class="form-control" placeholder="0.00">
                                @error('register_cost_tzs') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showRegisterModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Equipment Modal -->
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background-color: #001a41;">
                        <h5 class="modal-title text-white"><i class="mdi mdi-pencil"></i> {{ __('equipment.edit') }} Equipment</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditEquipmentModal"></button>
                    </div>
                    <div class="modal-body px-4 py-3" style="max-height: 70vh; overflow-y: auto;">
                        @include('livewire.equipment.partials.equipment-form-wizard', [
                            'wizardPhotoEquipment' => $equipment,
                            'hidePreventiveMaintenance' => true,
                        ])
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <button type="button" class="btn btn-secondary" wire:click="closeEditEquipmentModal">{{ __('equipment.cancel') }}</button>
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


    <!-- Maintenance Modal -->
    @if($showMaintenanceModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingLog ? 'pencil' : 'plus' }}"></i>
                            {{ $editingLog ? __('equipment.edit') : __('equipment.create') }} {{ __('equipment.maintenance_log') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showMaintenanceModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveMaintenanceLog" wire:key="maintenance-log-form-{{ $editingLog?->id ?? 'new' }}">
                            <div class="form-group mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="maintenanceForm.date" class="form-control" required>
                                @error('maintenanceForm.date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.description') }} <span class="text-danger">*</span></label>
                                <textarea wire:model="maintenanceForm.description" class="form-control" rows="4" required></textarea>
                                @error('maintenanceForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" wire:model="maintenanceForm.reference_number" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Service Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="maintenanceForm.maintainance_type" 
                                           value="in_house" id="maint_inhouse">
                                    <label class="form-check-label" for="maint_inhouse">In House</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="maintenanceForm.maintainance_type" 
                                           value="external" id="maint_external">
                                    <label class="form-check-label" for="maint_external">External</label>
                                </div>
                            </div>
                            @if($maintenanceForm['maintainance_type'] == 'in_house')
                                <div class="form-group mb-3">
                                    <label class="form-label">Employee</label>
                                    <div class="tag-select-container equipment-tag-select" wire:click="searchEmployees">
                                        <div class="tag-select-input modern-filter-tag-input">
                                            @if($selectedEmployeeName)
                                                <span class="tag-badge">{{ $selectedEmployeeName }}</span>
                                            @endif
                                            <input type="text" wire:model.live.debounce.200ms="employeeSearch" class="tag-input" placeholder="{{ $selectedEmployeeName ? '' : 'Choose Employee...' }}" autocomplete="off">
                                        </div>
                                        @if($showEmployeeDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($filteredEmployees as $employee)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectEmployee('{{ $employee->id }}', '{{ $employee->name }}')">{{ $employee->name }}</div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted">No employees found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label class="form-label">Supplier</label>
                                    <div class="tag-select-container equipment-tag-select" wire:click="searchSuppliers">
                                        <div class="tag-select-input modern-filter-tag-input">
                                            @if($selectedSupplierName)
                                                <span class="tag-badge">{{ $selectedSupplierName }}</span>
                                            @endif
                                            <input type="text" wire:model.live.debounce.200ms="supplierSearch" class="tag-input" placeholder="{{ $selectedSupplierName ? '' : 'Choose Supplier...' }}" autocomplete="off">
                                        </div>
                                        @if($showSupplierDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($filteredSuppliers as $supplier)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectSupplier('{{ $supplier->id }}', '{{ $supplier->name }}')">{{ $supplier->name }}</div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted">No suppliers found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.certificate') }}</label>
                                @if($editingLog && $editingLog->certificate && $editingLog->certificate !== 'no-document')
                                    @php
                                        $maintCertPath = parse_url($editingLog->certificate, PHP_URL_PATH) ?? $editingLog->certificate;
                                        $maintCertIsPdf = str_ends_with(strtolower((string) $maintCertPath), '.pdf');
                                    @endphp
                                    <div class="card border-0 shadow-sm mb-3 overflow-hidden equipment-cert-preview">
                                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-3 bg-light border-bottom">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <span class="rounded d-flex align-items-center justify-content-center bg-white border text-danger flex-shrink-0" style="width:2.25rem;height:2.25rem;">
                                                    <i class="mdi mdi-file-pdf-box mdi-24px"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="fw-semibold text-body small text-uppercase">{{ __('equipment.current_attachment') }}</div>
                                                    <div class="text-muted text-truncate small" style="max-width: 14rem;" title="{{ basename(parse_url($editingLog->certificate, PHP_URL_PATH) ?: $editingLog->certificate) }}">{{ basename(parse_url($editingLog->certificate, PHP_URL_PATH) ?: $editingLog->certificate) }}</div>
                                                </div>
                                            </div>
                                            <a href="{{ $editingLog->certificate }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary flex-shrink-0">
                                                <i class="mdi mdi-open-in-new"></i> {{ __('equipment.open_in_new_tab') }}
                                            </a>
                                        </div>
                                        @if($maintCertIsPdf)
                                            <div class="border-top bg-secondary bg-opacity-10" style="height: clamp(14rem, 48vh, 26rem);">
                                                <iframe
                                                    src="{{ $editingLog->certificate }}"
                                                    class="d-block w-100 border-0"
                                                    style="height: 100%; min-height: 14rem;"
                                                    title="{{ __('equipment.certificate_preview') }}"
                                                ></iframe>
                                            </div>
                                        @else
                                            <div class="card-body py-3 bg-light">
                                                <p class="mb-0 small text-muted">{{ __('equipment.preview_not_available') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <input type="file" wire:model="certificate" class="form-control" accept=".pdf,.doc,.docx">
                                <small class="form-text text-muted">{{ __('equipment.replace_certificate_hint') }}</small>
                                @error('certificate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Notes <span class="text-danger">*</span></label>
                                <textarea wire:model="maintenanceForm.notes" class="form-control" rows="4" required></textarea>
                                @error('maintenanceForm.notes') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showMaintenanceModal', false)">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveMaintenanceLog">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteMaintenanceConfirmModal)
        @include('livewire.equipment.partials.delete-log-confirm-modal', [
            'closeMethod' => 'closeDeleteMaintenanceConfirmModal',
            'confirmMethod' => 'confirmDeleteMaintenanceLog',
            'confirmTarget' => 'confirmDeleteMaintenanceLog',
            'preview' => $pendingDeleteMaintenancePreview,
            'dateLabel' => __('equipment.maintenance_date'),
            'warningText' => 'Are you sure you want to delete this maintenance log? This cannot be undone.',
        ])
    @endif

    @if($showDeleteCalibrationConfirmModal)
        @include('livewire.equipment.partials.delete-log-confirm-modal', [
            'closeMethod' => 'closeDeleteCalibrationConfirmModal',
            'confirmMethod' => 'confirmDeleteCalibrationLog',
            'confirmTarget' => 'confirmDeleteCalibrationLog',
            'preview' => $pendingDeleteCalibrationPreview,
            'dateLabel' => __('equipment.calibration_date'),
            'wide' => true,
            'scrollable' => true,
            'showCalibrationMetrics' => true,
            'warningText' => 'Are you sure you want to delete this calibration log? This cannot be undone.',
        ])
    @endif

    @if($showDeleteVerificationConfirmModal)
        @include('livewire.equipment.partials.delete-log-confirm-modal', [
            'closeMethod' => 'closeDeleteVerificationConfirmModal',
            'confirmMethod' => 'confirmDeleteVerificationLog',
            'confirmTarget' => 'confirmDeleteVerificationLog',
            'preview' => $pendingDeleteVerificationPreview,
            'dateLabel' => __('equipment.date'),
            'useOperatorLabel' => true,
            'useRemarks' => true,
            'notesLabel' => __('equipment.remarks'),
            'showReferenceStandard' => true,
            'warningText' => 'Are you sure you want to remove this verification log from the list? This cannot be undone.',
        ])
    @endif

    @if($showDeleteAttachmentConfirmModal)
        @include('livewire.equipment.partials.delete-attachment-confirm-modal', [
            'closeMethod' => 'closeDeleteAttachmentConfirmModal',
            'confirmMethod' => 'confirmDeleteAttachment',
            'confirmTarget' => 'confirmDeleteAttachment',
            'preview' => $pendingDeleteAttachmentPreview,
            'warningText' => 'Are you sure you want to delete this attachment? This cannot be undone.',
        ])
    @endif

    @if($showDeleteNotificationConfirmModal)
        @include('livewire.equipment.partials.delete-notification-confirm-modal', [
            'closeMethod' => 'closeDeleteNotificationConfirmModal',
            'confirmMethod' => 'confirmDeleteNotification',
            'confirmTarget' => 'confirmDeleteNotification',
            'preview' => $pendingDeleteNotificationPreview,
            'warningText' => 'Are you sure you want to delete this notification? This cannot be undone.',
        ])
    @endif

    @if($showLogNotesModal)
        @include('livewire.equipment.partials.equipment-log-notes-modal', [
            'kind' => $logNotesModalKind,
            'payload' => $logNotesModalPayload,
        ])
    @endif

    <!-- Calibration Modal (similar structure to Maintenance) -->
    @if($showCalibrationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingLog ? 'pencil' : 'plus' }}"></i>
                            {{ $editingLog ? __('equipment.edit') : __('equipment.create') }} {{ __('equipment.calibration_log') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showCalibrationModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCalibrationLog">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.date') }} <span class="text-danger">*</span></label>
                                <input type="date" wire:model="calibrationForm.date" class="form-control" required>
                                @error('calibrationForm.date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.description') }} <span class="text-danger">*</span></label>
                                <textarea wire:model="calibrationForm.description" class="form-control" rows="4" required></textarea>
                                @error('calibrationForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.reference_number') }} <span class="text-danger">*</span></label>
                                <input type="text" wire:model="calibrationForm.reference_number" class="form-control" required>
                                @error('calibrationForm.reference_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.correction_factor') }} <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="calibrationForm.correction_factor" class="form-control" step="any" placeholder="{{ __('equipment.enter_correction_factor') }}" required>
                                        @error('calibrationForm.correction_factor') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('equipment.uncertainty_of_measure') }} <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="calibrationForm.uncertainty_of_measure" class="form-control" step="any" placeholder="{{ __('equipment.enter_uncertainty_of_measure') }}" required>
                                        @error('calibrationForm.uncertainty_of_measure') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.service_type') }}</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="calibrationForm.maintainance_type" 
                                           value="in_house" id="calib_inhouse">
                                    <label class="form-check-label" for="calib_inhouse">{{ __('equipment.in_house') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="calibrationForm.maintainance_type" 
                                           value="external" id="calib_external">
                                    <label class="form-check-label" for="calib_external">{{ __('equipment.external') }}</label>
                                </div>
                            </div>
                            @if($calibrationForm['maintainance_type'] == 'in_house')
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('equipment.employee') }}</label>
                                    <div class="tag-select-container equipment-tag-select" wire:click="searchEmployees">
                                        <div class="tag-select-input modern-filter-tag-input">
                                            @if($selectedEmployeeName)
                                                <span class="tag-badge">{{ $selectedEmployeeName }}</span>
                                            @endif
                                            <input type="text" wire:model.live.debounce.200ms="employeeSearch" class="tag-input" placeholder="{{ $selectedEmployeeName ? '' : __('equipment.choose_employee') }}" autocomplete="off">
                                        </div>
                                        @if($showEmployeeDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($filteredEmployees as $employee)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectEmployee('{{ $employee->id }}', '{{ $employee->name }}')">{{ $employee->name }}</div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted">No employees found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('equipment.supplier') }}</label>
                                    <div class="tag-select-container equipment-tag-select" wire:click="searchSuppliers">
                                        <div class="tag-select-input modern-filter-tag-input">
                                            @if($selectedSupplierName)
                                                <span class="tag-badge">{{ $selectedSupplierName }}</span>
                                            @endif
                                            <input type="text" wire:model.live.debounce.200ms="supplierSearch" class="tag-input" placeholder="{{ $selectedSupplierName ? '' : __('equipment.choose_supplier') }}" autocomplete="off">
                                        </div>
                                        @if($showSupplierDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($filteredSuppliers as $supplier)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectSupplier('{{ $supplier->id }}', '{{ $supplier->name }}')">{{ $supplier->name }}</div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted">No suppliers found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.certificate') }}</label>
                                <input type="file" wire:model="certificate" class="form-control" accept=".pdf,.doc,.docx">
                                @error('certificate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.notes') }} <span class="text-danger">*</span></label>
                                <textarea wire:model="calibrationForm.notes" class="form-control" rows="4" required></textarea>
                                @error('calibrationForm.notes') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCalibrationModal', false)">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCalibrationLog">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Verification Modal -->
    @if($showVerificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingLog ? 'pencil' : 'plus' }}"></i>
                            {{ $editingLog ? __('equipment.edit') : __('equipment.create') }} {{ __('equipment.verification_log') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showVerificationModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveVerificationLog">
                            <div class="form-group mb-3">
                                <label class="form-label">Verification Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="verificationForm.verification_date" class="form-control" required>
                                @error('verificationForm.verification_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Reference Standard <span class="text-danger">*</span></label>
                                <input type="text" wire:model="verificationForm.reference_standard" class="form-control" required>
                                @error('verificationForm.reference_standard') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Service Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="verificationForm.maintainance_type" 
                                           value="in_house" id="verif_inhouse">
                                    <label class="form-check-label" for="verif_inhouse">In House</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="verificationForm.maintainance_type" 
                                           value="external" id="verif_external">
                                    <label class="form-check-label" for="verif_external">External</label>
                                </div>
                            </div>
                            @if($verificationForm['maintainance_type'] == 'in_house')
                                <div class="form-group mb-3">
                                    <label class="form-label">Operator</label>
                                    <div class="tag-select-container equipment-tag-select" wire:click="searchEmployees">
                                        <div class="tag-select-input modern-filter-tag-input">
                                            @if($selectedEmployeeName)
                                                <span class="tag-badge">{{ $selectedEmployeeName }}</span>
                                            @endif
                                            <input type="text" wire:model.live.debounce.200ms="employeeSearch" class="tag-input" placeholder="{{ $selectedEmployeeName ? '' : 'Choose Operator...' }}" autocomplete="off">
                                        </div>
                                        @if($showEmployeeDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($filteredEmployees as $employee)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectEmployee('{{ $employee->id }}', '{{ $employee->name }}')">{{ $employee->name }}</div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted">No operators found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label class="form-label">Supplier</label>
                                    <div class="tag-select-container equipment-tag-select" wire:click="searchSuppliers">
                                        <div class="tag-select-input modern-filter-tag-input">
                                            @if($selectedSupplierName)
                                                <span class="tag-badge">{{ $selectedSupplierName }}</span>
                                            @endif
                                            <input type="text" wire:model.live.debounce.200ms="supplierSearch" class="tag-input" placeholder="{{ $selectedSupplierName ? '' : 'Choose Supplier...' }}" autocomplete="off">
                                        </div>
                                        @if($showSupplierDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($filteredSuppliers as $supplier)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectSupplier('{{ $supplier->id }}', '{{ $supplier->name }}')">{{ $supplier->name }}</div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted">No suppliers found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group mb-3">
                                <label class="form-label">Procedure <span class="text-danger">*</span></label>
                                <textarea wire:model="verificationForm.procedure" class="form-control" rows="4" required></textarea>
                                @error('verificationForm.procedure') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Responses/Readings <span class="text-danger">*</span></label>
                                <textarea wire:model="verificationForm.response" class="form-control" rows="4" required></textarea>
                                @error('verificationForm.response') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea wire:model="verificationForm.remarks" class="form-control" rows="4" required></textarea>
                                @error('verificationForm.remarks') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showVerificationModal', false)">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveVerificationLog">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Operator Modal -->
    @if($showOperatorModal)
        <div class="modal fade show d-block eq-operator-modal-overlay" tabindex="-1" aria-modal="true" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog eq-operator-modal-dialog">
                <div class="modal-content border-0 shadow-lg rounded-4 eq-operator-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold"><i class="mdi mdi-account-multiple-plus text-primary"></i> {{ __('equipment.add_operators') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeOperatorModal" aria-label="{{ __('equipment.close') }}"></button>
                    </div>
                    <div class="modal-body pt-2 eq-operator-modal-body">
                        <form wire:submit.prevent="saveOperators">
                            <div class="form-group mb-0">
                                <label class="form-label fw-semibold">{{ __('equipment.select_operators') }} <span class="text-danger">*</span></label>
                                <div class="tag-select-container equipment-tag-select equipment-tag-select--multi eq-operator-tag-select" wire:click="$toggle('showOperatorDropdown')">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        @php
                                            $selectedOperatorIds = collect($operatorForm['operators'] ?? [])->map(fn ($id) => (string) $id);
                                        @endphp
                                        @forelse($employees->filter(fn ($employee) => $selectedOperatorIds->contains((string) $employee->id)) as $employee)
                                            <span class="tag-badge me-1 mb-1">{{ $employee->name }}</span>
                                        @empty
                                            <span class="text-muted small">{{ __('equipment.select_operators') }}…</span>
                                        @endforelse
                                    </div>
                                    @if($showOperatorDropdown)
                                        <div class="tag-dropdown tag-dropdown--scrollable">
                                            @forelse($employees as $employee)
                                                <div class="tag-dropdown-item d-flex justify-content-between align-items-center" wire:click.stop="toggleOperatorSelection('{{ $employee->id }}')">
                                                    <span>{{ $employee->name }}</span>
                                                    @if($selectedOperatorIds->contains((string) $employee->id))
                                                        <i class="mdi mdi-check text-primary"></i>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">No employees found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-2">Click to select multiple operators</small>
                                @error('operatorForm.operators') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light border" wire:click="closeOperatorModal">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveOperators" wire:loading.attr="disabled" wire:target="saveOperators">
                            <span wire:loading.remove wire:target="saveOperators"><i class="mdi mdi-content-save"></i> {{ __('equipment.save') }}</span>
                            <span wire:loading wire:target="saveOperators"><i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.save') }}…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Attachment Modal -->
    @if($showAttachmentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingAttachment ? 'pencil' : 'plus' }}"></i>
                            {{ $editingAttachment ? 'Edit' : 'Create' }} Attachment
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showAttachmentModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveAttachment">
                            <div class="form-group mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" wire:model="attachmentForm.title" class="form-control" required>
                                @error('attachmentForm.title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Attachment 
                                    @if(!$editingAttachment) <span class="text-danger">*</span> @endif
                                </label>
                                <input type="file" wire:model="attachmentFile" class="form-control" 
                                       @if(!$editingAttachment) required @endif>
                                @error('attachmentFile') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('equipment.description') }}</label>
                                <textarea wire:model="attachmentForm.description" class="form-control" rows="4"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showAttachmentModal', false)">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveAttachment">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Notification Modal -->
    @if($showNotificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingNotification ? 'pencil' : 'plus' }}"></i>
                            {{ $editingNotification ? 'Edit' : 'Create' }} Notification
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showNotificationModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveNotification">
                            <div class="form-group mb-3">
                                <label class="form-label">Notification Type <span class="text-danger">*</span></label>
                                <div class="tag-select-container equipment-tag-select" wire:click="$toggle('showNotificationTypeDropdown')">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        @if($selectedNotificationTypeLabel)
                                            <span class="tag-badge">{{ $selectedNotificationTypeLabel }}</span>
                                        @endif
                                        <input type="text" class="tag-input" placeholder="{{ $selectedNotificationTypeLabel ? '' : 'Choose Type...' }}" readonly>
                                    </div>
                                    @if($showNotificationTypeDropdown)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item" wire:click.stop="selectNotificationType('calibration', 'Calibration')">Calibration</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectNotificationType('maintanance', 'Maintenance')">Maintenance</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectNotificationType('verification', 'Verification')">Verification</div>
                                        </div>
                                    @endif
                                </div>
                                @error('notificationForm.notification_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Value (Days) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="notificationForm.value" class="form-control" min="1" required>
                                @error('notificationForm.value') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                <div class="tag-select-container equipment-tag-select" wire:click="$toggle('showNotificationFrequencyDropdown')">
                                    <div class="tag-select-input modern-filter-tag-input">
                                        @if($selectedNotificationFrequencyLabel)
                                            <span class="tag-badge">{{ $selectedNotificationFrequencyLabel }}</span>
                                        @endif
                                        <input type="text" class="tag-input" placeholder="{{ $selectedNotificationFrequencyLabel ? '' : 'Choose Frequency...' }}" readonly>
                                    </div>
                                    @if($showNotificationFrequencyDropdown)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item" wire:click.stop="selectNotificationFrequency('days', 'Days')">Days</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectNotificationFrequency('weeks', 'Weeks')">Weeks</div>
                                            <div class="tag-dropdown-item" wire:click.stop="selectNotificationFrequency('months', 'Months')">Months</div>
                                        </div>
                                    @endif
                                </div>
                                @error('notificationForm.frequency') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showNotificationModal', false)">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="saveNotification">
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
    .eq-form .form-control {
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

    /* Equipment view redesign */
    .eq-view-page {
        padding-top: 8px;
        padding-bottom: 18px;
    }
    .eq-hero-card {
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(10, 33, 68, 0.08);
    }
    .eq-kicker {
        display: inline-block;
        font-size: 0.73rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #5f6b7a;
        font-weight: 700;
    }
    .eq-hero-title {
        font-size: 2.1rem;
        line-height: 1.1;
        font-weight: 700;
        color: #212a35;
    }
    .eq-side-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(10, 33, 68, 0.08);
    }
    .eq-side-header {
        background: linear-gradient(135deg, #596574 0%, #3f4a56 100%);
        border: none;
    }
    .eq-main-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(10, 33, 68, 0.08);
    }
    .eq-main-header {
        padding: 0;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .eq-main-tabs-wrap {
        position: relative;
        display: flex;
        align-items: stretch;
    }
    .eq-main-tabs-scroll {
        flex: 1;
        min-width: 0;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }
    .eq-main-tabs-scroll::-webkit-scrollbar {
        display: none;
        height: 0;
        width: 0;
    }
    .eq-tabs-fade {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 56px;
        pointer-events: none;
        z-index: 2;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .eq-tabs-fade--start {
        left: 0;
        background: linear-gradient(to right, #f8fafc 45%, rgba(248, 250, 252, 0));
    }
    .eq-tabs-fade--end {
        right: 0;
        background: linear-gradient(to left, #f8fafc 20%, rgba(248, 250, 252, 0.85) 55%, transparent);
    }
    .eq-main-tabs-wrap.has-overflow-start .eq-tabs-fade--start {
        opacity: 1;
    }
    .eq-main-tabs-wrap.has-overflow-end .eq-tabs-fade--end {
        opacity: 1;
    }
    .eq-tabs-scroll-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        display: none;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid #cbd5e1;
        border-radius: 50%;
        background: #ffffff;
        color: #475569;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .eq-tabs-scroll-btn:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
    }
    .eq-tabs-scroll-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
    }
    .eq-tabs-scroll-btn--prev {
        left: 6px;
    }
    .eq-tabs-scroll-btn--next {
        right: 6px;
    }
    .eq-main-tabs-wrap.has-overflow-start .eq-tabs-scroll-btn--prev {
        display: inline-flex;
    }
    .eq-main-tabs-wrap.has-overflow-end .eq-tabs-scroll-btn--next {
        display: inline-flex;
    }
    .eq-main-body {
        background: #ffffff;
    }
    .equipment-tag-select {
        position: relative;
    }
    .equipment-tag-select .tag-select-input {
        min-height: 38px;
        align-items: center;
    }
    .equipment-tag-select .tag-input {
        border: 0;
        outline: none;
        background: transparent;
        width: 100%;
        min-width: 0;
        padding: 0;
        font-size: 0.875rem;
    }
    .equipment-tag-select .tag-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1080;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d8e0eb;
        border-radius: 14px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
        padding: 6px;
    }
    .equipment-tag-select--compact .tag-select-input {
        min-height: 34px;
    }
    .equipment-tag-select--multi .tag-select-input {
        min-height: 52px;
        flex-wrap: wrap;
        gap: 6px;
        align-items: flex-start;
    }
    .equipment-tag-select.is-disabled {
        opacity: 0.65;
        pointer-events: none;
    }
    .tag-dropdown--scrollable {
        max-height: 300px;
        overflow-y: auto;
    }
    .modal.fade.show.d-block {
        position: fixed;
        inset: 0;
        overflow-y: auto;
    }
    .modal.fade.show.d-block .modal-dialog {
        margin: 1.5rem auto;
    }
    .modal.fade.show.d-block .modal-content {
        max-height: calc(100vh - 3rem);
    }
    .modal.fade.show.d-block .modal-body {
        overflow-y: auto;
    }
    .eq-operator-modal-overlay {
        overscroll-behavior: contain;
        overflow-y: hidden;
    }
    .eq-operator-modal-overlay .eq-operator-modal-dialog {
        margin: 1.5rem auto;
        max-width: 520px;
    }
    .eq-operator-modal-overlay .eq-operator-modal-content {
        min-height: 28rem;
        max-height: min(90vh, 36rem);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .eq-operator-modal-overlay .eq-operator-modal-body {
        flex: 1 1 auto;
        min-height: 20rem;
        overflow: visible;
    }
    .eq-operator-modal-overlay .eq-operator-tag-select .tag-dropdown {
        position: static;
        top: auto;
        left: auto;
        right: auto;
        margin-top: 0.5rem;
        max-height: min(18rem, 40vh);
    }
    body.modal-open {
        overflow: hidden !important;
    }
    .modal-backdrop {
        display: none !important;
    }
    .eq-main-tabs {
        border-bottom: none;
        flex-wrap: nowrap;
        padding: 0 2.5rem 0 1rem;
        margin-bottom: 0;
        min-width: min-content;
        align-items: stretch;
    }
    .eq-main-tabs .nav-item {
        margin-bottom: 0;
        flex-shrink: 0;
        display: flex;
        align-items: stretch;
    }
    .eq-main-tabs .eq-tab-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding: 0 14px;
        min-height: 48px;
        color: #64748b;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.25;
        background: transparent;
        white-space: nowrap;
        vertical-align: middle;
        transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }
    .eq-main-tabs .eq-tab-link i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.125rem;
        height: 1.125rem;
        font-size: 1.125rem;
        line-height: 1;
        flex-shrink: 0;
        opacity: 0.9;
    }
    .eq-main-tabs .eq-tab-link span {
        display: inline-block;
        line-height: 1.25;
        vertical-align: middle;
    }
    .eq-main-tabs .eq-tab-link:hover {
        color: #1d4ed8;
        border-bottom-color: #93c5fd;
        background: rgba(255, 255, 255, 0.6);
    }
    .eq-main-tabs .eq-tab-link.active {
        color: #1d4ed8;
        border-bottom-color: #2563eb;
        background: #ffffff;
        margin-bottom: -1px;
    }
    .eq-main-tabs .eq-tab-link:focus {
        outline: none;
        box-shadow: none;
    }
    @media (max-width: 768px) {
        .eq-hero-title {
            font-size: 1.55rem;
        }
        .eq-main-header {
            padding-top: 0.7rem;
        }
    }

    /* ── Modal form field spacing ─────────────────────────────────
       Bootstrap g-3/gy-4 gutters only apply to .row/.col grids.
       These modal forms stack .form-group divs vertically, so we
       use explicit margin/padding instead.
    ────────────────────────────────────────────────────────────── */
    .eq-view-page .modal-body {
        padding: 1.5rem 1.75rem;
    }
    .eq-view-page .modal-body .form-group {
        margin-bottom: 1.5rem;
    }
    .eq-view-page .modal-body .form-group:last-child {
        margin-bottom: 0;
    }
    .eq-view-page .modal-body .form-label {
        margin-bottom: 0.45rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #4a5568;
    }
    .eq-view-page .modal-body .form-control {
        padding: 0.475rem 0.75rem;
    }
    .eq-view-page .modal-body .row .form-group {
        margin-bottom: 0;
    }
    .eq-view-page .modal-body .row {
        margin-bottom: 1.5rem;
    }

    /* ── Delete log confirmation modals ───────────────────────── */
    .eq-delete-overlay {
        background: rgba(15, 23, 42, 0.52) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        overflow-x: hidden;
    }

    .eq-delete-dialog {
        max-width: min(520px, calc(100vw - 1.5rem));
    }

    .eq-delete-dialog.modal-lg {
        max-width: min(640px, calc(100vw - 1.5rem));
    }

    .eq-delete-shell {
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
        box-shadow:
            0 24px 48px rgba(15, 23, 42, 0.18),
            0 0 0 1px rgba(226, 232, 240, 0.9);
    }

    .eq-delete-body {
        position: relative;
        padding: 1.35rem 1.35rem 1.1rem;
        background: linear-gradient(180deg, #fafbfc 0%, #ffffff 42%);
        overflow-x: hidden;
    }

    .eq-delete-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
        opacity: 0.45;
        padding: 0.5rem;
        border-radius: 10px;
        transition: opacity 0.15s ease, background-color 0.15s ease;
    }

    .eq-delete-close:hover {
        opacity: 0.85;
        background-color: rgba(15, 23, 42, 0.06);
    }

    .eq-delete-frame {
        position: relative;
        padding: 1.25rem 1.35rem 1.15rem;
        border-radius: 14px;
        border: 2px dashed rgba(220, 38, 38, 0.55);
        background:
            linear-gradient(145deg, rgba(254, 242, 242, 0.65) 0%, rgba(255, 255, 255, 0.92) 38%, #ffffff 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        overflow: hidden;
    }

    .eq-delete-frame--scroll {
        max-height: min(52vh, 28rem);
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .eq-delete-frame__glow {
        position: absolute;
        top: -30%;
        right: 0;
        width: 45%;
        height: 70%;
        background: radial-gradient(ellipse at center, rgba(248, 113, 113, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .eq-delete-frame-footer {
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(254, 202, 202, 0.5);
    }

    .eq-delete-intro {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1.15rem;
        padding-right: 1.5rem;
    }

    .eq-delete-intro__icon {
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.35rem;
        color: #b91c1c;
        background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
        border: 1px solid rgba(254, 202, 202, 0.9);
        box-shadow: 0 4px 12px rgba(185, 28, 28, 0.1);
    }

    .eq-delete-intro__eyebrow {
        margin: 0 0 0.2rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #b91c1c;
    }

    .eq-delete-intro__lead {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.45;
        color: #64748b;
    }

    .eq-delete-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem 1.25rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 480px) {
        .eq-delete-details {
            grid-template-columns: 1fr;
        }
    }

    .eq-delete-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .eq-delete-field--span {
        grid-column: 1 / -1;
    }

    .eq-delete-field--full {
        grid-column: 1 / -1;
    }

    .eq-delete-field__label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .eq-delete-field__value {
        font-size: 0.9rem;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.4;
        word-break: break-word;
    }

    .eq-delete-field__value--primary {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .eq-delete-field__value--mono {
        font-variant-numeric: tabular-nums;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.875rem;
    }

    .eq-delete-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    .eq-delete-notes {
        margin-top: 0.15rem;
        padding: 0.65rem 0.85rem;
        border-radius: 10px;
        font-size: 0.84rem;
        line-height: 1.5;
        color: #334155;
        white-space: pre-wrap;
        word-break: break-word;
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid #e8edf3;
        max-height: 8rem;
        overflow-y: auto;
    }

    .eq-delete-warning {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-top: 0.15rem;
        padding: 0.75rem 0.9rem;
        border-radius: 10px;
        background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
        border: 1px solid rgba(254, 202, 202, 0.65);
    }

    .eq-delete-warning__icon {
        flex-shrink: 0;
        font-size: 1.15rem;
        color: #dc2626;
        margin-top: 0.05rem;
    }

    .eq-delete-warning__text {
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #991b1b;
        font-weight: 500;
    }

    .eq-delete-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 0.85rem 1.35rem 1.25rem;
        background: #fafbfc;
        border-top: 1px solid #eef2f6;
    }

    .eq-delete-btn {
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1.15rem;
        transition: transform 0.12s ease, box-shadow 0.12s ease, background-color 0.12s ease;
    }

    .eq-delete-btn--cancel {
        color: #475569;
        background: #ffffff;
        border: 1px solid #d8e0eb;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .eq-delete-btn--cancel:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #334155;
    }

    .eq-delete-btn--confirm {
        color: #ffffff;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border: none;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
    }

    .eq-delete-btn--confirm:hover:not(:disabled) {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
        transform: translateY(-1px);
    }

    .eq-delete-btn--confirm:disabled {
        opacity: 0.72;
    }

    /* Log notes / verification details view modal */
    .eq-notes-view-overlay {
        background: rgba(15, 23, 42, 0.52) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 1060;
        overflow-x: hidden;
    }

    .eq-notes-view-dialog {
        max-width: min(640px, calc(100vw - 1.5rem));
    }

    .eq-notes-view-shell {
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
        box-shadow:
            0 24px 48px rgba(15, 23, 42, 0.18),
            0 0 0 1px rgba(226, 232, 240, 0.9);
    }

    .eq-notes-view-body {
        position: relative;
        padding: 1.35rem;
        background: linear-gradient(180deg, #fafbfc 0%, #ffffff 42%);
        overflow-x: hidden;
    }

    .eq-notes-view-frame {
        position: relative;
        padding: 1.25rem 1.35rem;
        border-radius: 14px;
        border: 2px dashed rgba(37, 99, 235, 0.55);
        background:
            linear-gradient(145deg, rgba(239, 246, 255, 0.75) 0%, rgba(255, 255, 255, 0.95) 38%, #ffffff 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        overflow: hidden;
    }

    .eq-notes-view-frame--scroll {
        max-height: min(58vh, 32rem);
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .eq-notes-view-frame__glow {
        position: absolute;
        top: -30%;
        right: 0;
        width: 45%;
        height: 70%;
        background: radial-gradient(ellipse at center, rgba(96, 165, 250, 0.14) 0%, transparent 70%);
        pointer-events: none;
    }

    .eq-notes-view-section {
        margin-bottom: 1rem;
    }

    .eq-notes-view-section__label {
        margin: 0 0 0.35rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #3b82f6;
    }

    .eq-notes-view-content {
        padding: 0.7rem 0.85rem;
        border-radius: 10px;
        font-size: 0.875rem;
        line-height: 1.55;
        color: #1e293b;
        white-space: pre-wrap;
        word-break: break-word;
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid #dbeafe;
    }

    .eq-notes-view-content--single {
        min-height: 4.5rem;
    }

    .eq-notes-view-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #dbeafe;
    }

    .eq-notes-view-btn {
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        color: #1d4ed8;
        background: #ffffff;
        border: 1px solid #93c5fd;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.08);
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .eq-notes-view-btn:hover {
        background: #eff6ff;
        border-color: #60a5fa;
        color: #1e40af;
    }

    /* Equipment details tab */
    .eq-main-body:has(.eq-details) {
        display: flex;
        flex-direction: column;
    }
    .eq-details {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        padding: 0.15rem 0 0.5rem;
        min-height: 0;
    }
    .eq-details-hero {
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e8edf3;
    }
    .eq-details-hero__row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem 1rem;
    }
    .eq-details-kicker {
        font-size: 0.72rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
    }
    .eq-details-heading {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.65rem;
    }
    .eq-details-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .eq-details-subtitle {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
    }
    .eq-details-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .eq-details-pill--muted {
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .eq-details-pill--outline {
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
    }
    .eq-details-edit-btn {
        flex-shrink: 0;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.14);
    }
    .eq-details-card {
        border-radius: 14px;
        border: 1px solid #e8edf3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .eq-details-card__header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
        background: linear-gradient(120deg, #f8fafc 0%, #eef4fb 100%);
        border-bottom: 1px solid #e8edf3;
    }
    .eq-details-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 10px;
        background: #ffffff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.08);
    }
    .eq-details-card__icon .mdi {
        font-size: 1.1rem;
        line-height: 1;
    }
    .eq-details-card__title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: 0.01em;
    }
    .eq-details-card__body {
        padding: 0.85rem 1rem 1rem;
    }
    .eq-details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem 1rem;
    }
    @media (max-width: 575.98px) {
        .eq-details-grid {
            grid-template-columns: 1fr;
        }
    }
    .eq-details-item--wide {
        grid-column: 1 / -1;
    }
    .eq-details-item__label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.2rem;
    }
    .eq-details-item__value {
        font-size: 0.9rem;
        color: #1e293b;
        line-height: 1.45;
        word-break: break-word;
    }
    .eq-details-item__value--highlight {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .eq-details-item__value--mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.84rem;
    }
    .eq-details-notes {
        padding: 0.65rem 0.8rem;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e8edf3;
        font-size: 0.84rem;
        line-height: 1.5;
        white-space: pre-wrap;
        max-height: 9rem;
        overflow-y: auto;
    }
    .eq-details-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.55rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .eq-details-badge--success {
        color: #166534;
        background: #ecfdf3;
        border-color: #bbf7d0;
    }
    .eq-details-badge--warning {
        color: #92400e;
        background: #fffbeb;
        border-color: #fde68a;
    }
    .eq-details-badge--danger {
        color: #991b1b;
        background: #fef2f2;
        border-color: #fecaca;
    }
    .eq-details-badge--info {
        color: #1d4ed8;
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .eq-details-badge--muted {
        color: #475569;
        background: #f1f5f9;
        border-color: #e2e8f0;
    }
    .eq-details-layout {
        flex: 1 1 auto;
        align-items: stretch;
        min-height: 0;
    }
    @media (min-width: 992px) {
        .eq-details-layout {
            min-height: calc(100vh - 17.5rem);
        }
    }
    .eq-details-panel-col {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    .eq-details-section-nav {
        position: sticky;
        top: 0.75rem;
    }
    .eq-details-section-nav__label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.55rem;
        padding-left: 0.15rem;
    }
    .eq-details-section-nav__list {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }
    @media (max-width: 991.98px) {
        .eq-details-section-nav__list {
            flex-direction: row;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 0.35rem;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }
    }
    .eq-details-section-tab {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        width: 100%;
        text-align: left;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.7rem 0.85rem;
        background: #ffffff;
        color: #334155;
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }
    @media (max-width: 991.98px) {
        .eq-details-section-tab {
            min-width: 11.5rem;
            flex-shrink: 0;
        }
    }
    .eq-details-section-tab:hover {
        border-color: #bfdbfe;
        background: #f8fbff;
    }
    .eq-details-section-tab--active {
        border-color: #93c5fd;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.06);
        color: #1e3a8a;
    }
    .eq-details-section-tab__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 10px;
        background: #f8fafc;
        color: #2563eb;
        border: 1px solid #dbeafe;
        flex-shrink: 0;
    }
    .eq-details-section-tab--active .eq-details-section-tab__icon {
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.06);
    }
    .eq-details-section-tab__icon .mdi {
        font-size: 1.05rem;
        line-height: 1;
    }
    .eq-details-section-tab__text {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
    }
    .eq-details-section-tab__title {
        font-size: 0.84rem;
        font-weight: 700;
        line-height: 1.3;
    }
    .eq-details-section-tab__meta {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
    }
    .eq-details-section-tab--active .eq-details-section-tab__meta {
        color: #3b82f6;
    }
    .eq-details-panel {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        height: 100%;
        min-height: 100%;
        border-radius: 14px;
        border: 1px solid #e8edf3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 2px 8px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }
    .eq-details-panel__header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(120deg, #f8fafc 0%, #eef4fb 100%);
        border-bottom: 1px solid #e8edf3;
    }
    .eq-details-panel__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 12px;
        background: #ffffff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.06);
        flex-shrink: 0;
    }
    .eq-details-panel__icon .mdi {
        font-size: 1.2rem;
        line-height: 1;
    }
    .eq-details-panel__title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    .eq-details-panel__lead {
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 0.15rem;
    }
    .eq-details-panel__body {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        padding: 1rem 1.15rem 1.15rem;
        min-height: 0;
    }
    .eq-details-panel__body .eq-details-grid {
        flex: 1 1 auto;
        align-content: start;
    }
    @media (min-width: 992px) {
        .eq-details-panel__body .eq-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1200px) {
        .eq-details-panel__body .eq-details-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    </style>

    @script
    <script>
    (function () {
        function syncBodyModalState() {
            var isModalOpen = document.querySelector('.modal.fade.show.d-block') !== null;
            document.body.classList.toggle('modal-open', isModalOpen);
        }

        var modalObserver = null;
        if (typeof MutationObserver !== 'undefined') {
            modalObserver = new MutationObserver(function () {
                syncBodyModalState();
            });
            modalObserver.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'style'] });
        }

        function buildDailyLogChart() {
        var chartDataEl = document.getElementById('dl-chart-data');
        var canvas = document.getElementById('dl-perf-chart');
        if (!chartDataEl || !canvas) {
            if (window._dlPerfChart) {
                window._dlPerfChart.destroy();
                window._dlPerfChart = null;
            }
            return;
        }

        var chartData;
        try {
            chartData = JSON.parse(chartDataEl.textContent || 'null');
        } catch (e) {
            return;
        }
        if (!chartData || !chartData.labels || chartData.labels.length === 0) {
            if (window._dlPerfChart) {
                window._dlPerfChart.destroy();
                window._dlPerfChart = null;
            }
            return;
        }

        function buildChart() {
            if (window._dlPerfChart) {
                window._dlPerfChart.destroy();
                window._dlPerfChart = null;
            }

            var unit = chartData.unit || '';
            var n    = chartData.labels.length;
            var datasets = [];

            if (chartData.type === 'range') {
                datasets = [
                    {
                        label: 'Expected Upper Limit',
                        data: Array(n).fill(chartData.expectedMax),
                        borderColor: 'rgba(40,167,69,0.7)',
                        backgroundColor: 'rgba(40,167,69,0.12)',
                        borderDash: [6, 4],
                        borderWidth: 1.5,
                        pointRadius: 0,
                        fill: '+1',
                        order: 1,
                    },
                    {
                        label: 'Expected Lower Limit',
                        data: Array(n).fill(chartData.expectedMin),
                        borderColor: 'rgba(40,167,69,0.7)',
                        backgroundColor: 'transparent',
                        borderDash: [6, 4],
                        borderWidth: 1.5,
                        pointRadius: 0,
                        fill: false,
                        order: 1,
                    },
                    {
                        label: 'Recorded Reading',
                        data: chartData.values,
                        borderColor: 'rgba(0,123,255,0.9)',
                        backgroundColor: 'rgba(0,123,255,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: 'origin',
                        tension: 0.3,
                        order: 0,
                    },
                ];

                if (Array.isArray(chartData.legacyMeans) && chartData.legacyMeans.some(function (v) { return v !== null; })) {
                    datasets.push({
                        label: 'Historic Mean (legacy min-max)',
                        data: chartData.legacyMeans,
                        borderColor: 'rgba(108,117,125,0.8)',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        borderDash: [4, 4],
                        pointRadius: 2,
                        fill: false,
                        tension: 0.25,
                        order: 0,
                    });
                }
            } else {
                // constant + quantitative
                datasets = [
                    {
                        label: 'Expected Target',
                        data: Array(n).fill(parseFloat(chartData.expectedValue) || 0),
                        borderColor: 'rgba(40,167,69,0.8)',
                        backgroundColor: 'transparent',
                        borderDash: [8, 4],
                        borderWidth: 1.5,
                        pointRadius: 0,
                        fill: false,
                        order: 1,
                    },
                    {
                        label: 'Recorded Value',
                        data: chartData.values,
                        borderColor: 'rgba(0,123,255,0.85)',
                        backgroundColor: 'rgba(0,123,255,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: 'origin',
                        tension: 0.3,
                        order: 0,
                    },
                ];
                if (chartData.tolHigh !== undefined) {
                    datasets.unshift({
                        label: 'Tolerance Upper Limit',
                        data: Array(n).fill(chartData.tolHigh),
                        borderColor: 'rgba(255,193,7,0.6)',
                        backgroundColor: 'rgba(255,193,7,0.08)',
                        borderDash: [4, 4],
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: '+1',
                        order: 2,
                    });
                    datasets.splice(datasets.length - 1, 0, {
                        label: 'Tolerance Lower Limit',
                        data: Array(n).fill(chartData.tolLow),
                        borderColor: 'rgba(255,193,7,0.6)',
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        order: 2,
                    });
                }
            }

            var ctx = canvas.getContext('2d');
            window._dlPerfChart = new Chart(ctx, {
                type: 'line',
                data: { labels: chartData.labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    if (ctx.parsed.y === null) return null;
                                    return ctx.dataset.label + ': ' + ctx.parsed.y + (unit ? ' ' + unit : '');
                                },
                                afterBody: function (items) {
                                    if (chartData.type !== 'range' || !Array.isArray(chartData.withinFlags) || !items.length) {
                                        return;
                                    }

                                    var index = items[0].dataIndex;
                                    var within = chartData.withinFlags[index];
                                    if (within === true) return 'Status: Within expected range';
                                    if (within === false) return 'Status: Outside expected range';
                                    return null;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            title: { display: !!unit, text: unit }
                        }
                    }
                }
            });
        }

        if (typeof Chart !== 'undefined') {
            buildChart();
        } else {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js';
            s.onload = buildChart;
            document.head.appendChild(s);
        }

        }

        window.initDailyLogPerformanceChart = buildDailyLogChart;

        function scheduleBuild() {
            setTimeout(function () {
                if (typeof window.initDailyLogPerformanceChart === 'function') {
                    window.initDailyLogPerformanceChart();
                }
            }, 120);
        }

        function initEquipmentTabScroller() {
            document.querySelectorAll('.eq-main-tabs-wrap').forEach(function (wrap) {
                if (wrap.dataset.eqTabsBound === '1') {
                    return;
                }
                wrap.dataset.eqTabsBound = '1';

                var scroller = wrap.querySelector('[data-eq-tabs-scroll]');
                if (!scroller) {
                    return;
                }

                function updateOverflow() {
                    var maxScroll = scroller.scrollWidth - scroller.clientWidth;
                    var scrollLeft = scroller.scrollLeft;
                    wrap.classList.toggle('has-overflow-start', scrollLeft > 4);
                    wrap.classList.toggle('has-overflow-end', maxScroll > 4 && scrollLeft < maxScroll - 4);
                }

                scroller.addEventListener('scroll', updateOverflow, { passive: true });

                scroller.addEventListener('wheel', function (event) {
                    if (scroller.scrollWidth <= scroller.clientWidth) {
                        return;
                    }
                    var delta = Math.abs(event.deltaX) > Math.abs(event.deltaY)
                        ? event.deltaX
                        : event.deltaY;
                    if (delta === 0) {
                        return;
                    }
                    event.preventDefault();
                    // Subtract so trackpad/mouse direction matches finger movement (pan right → content moves right).
                    scroller.scrollLeft -= delta;
                    updateOverflow();
                }, { passive: false });

                var prevBtn = wrap.querySelector('.eq-tabs-scroll-btn--prev');
                var nextBtn = wrap.querySelector('.eq-tabs-scroll-btn--next');

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        scroller.scrollBy({ left: -240, behavior: 'smooth' });
                        setTimeout(updateOverflow, 320);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        scroller.scrollBy({ left: 240, behavior: 'smooth' });
                        setTimeout(updateOverflow, 320);
                    });
                }

                wrap.querySelectorAll('.eq-tab-link').forEach(function (tabBtn) {
                    tabBtn.addEventListener('click', function () {
                        setTimeout(function () {
                            tabBtn.scrollIntoView({ inline: 'nearest', block: 'nearest', behavior: 'smooth' });
                            updateOverflow();
                        }, 80);
                    });
                });

                window.addEventListener('resize', updateOverflow);
                updateOverflow();
                setTimeout(updateOverflow, 150);
                setTimeout(updateOverflow, 500);
            });
        }

        initEquipmentTabScroller();

        if (!window._dlPerfChartBindings) {
            window._dlPerfChartBindings = true;

            document.addEventListener('livewire:initialized', function () {
                scheduleBuild();
                initEquipmentTabScroller();

                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('morph.updated', function () {
                        scheduleBuild();
                        document.querySelectorAll('.eq-main-tabs-wrap').forEach(function (wrap) {
                            delete wrap.dataset.eqTabsBound;
                        });
                        initEquipmentTabScroller();
                    });
                }
            });

            document.addEventListener('click', function (event) {
                var equipmentChecksTabButton = event.target.closest("[wire\\:click=\"setActiveTab('equipment-checks')\"]");
                if (equipmentChecksTabButton) {
                    scheduleBuild();
                }
            });
        }

        scheduleBuild();
        syncBodyModalState();
    })();
    </script>
    @endscript
</div>
