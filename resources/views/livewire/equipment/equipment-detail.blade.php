<div class="container-fluid eq-view-page">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 eq-hero-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div>
                            <div class="eq-kicker mb-1">Equipment Management</div>
                            <h2 class="mb-1 eq-hero-title">
                                <i class="mdi mdi-tools text-primary"></i>
                                {{ $equipment->name ?? 'Equipment' }}
                            </h2>
                            <p class="text-muted mb-0">Equipment Details and Management</p>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <span class="badge badge-light border px-3 py-2">{{ $equipment->equipment_number }}</span>
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
                    <!-- Description -->
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
                    <ul class="nav nav-tabs eq-main-tabs">
                        @if(!$fromDailyLog)
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'maintenance' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('maintenance')" type="button">
                                Maintenance Log
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'calibration' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('calibration')" type="button">
                                Calibration Log
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'verification' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('verification')" type="button">
                                Verification Log
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'operators' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('operators')" type="button">
                                Operators
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'attachments' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('attachments')" type="button">
                                Attachments
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'notifications' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('notifications')" type="button">
                                Notifications
                            </button>
                        </li>
                        @endif
                        @if($equipment->requires_daily_log)
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === 'dailylog' ? 'active' : '' }}" 
                                    wire:click="setActiveTab('dailylog')" type="button">
                                <i class="mdi mdi-notebook-check-outline"></i> Daily Log Config
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body eq-main-body">
                    <!-- Maintenance Log Tab -->
                    @if($activeTab === 'maintenance')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Maintenance Logs</h5>
                            <button wire:click="showCreateMaintenanceModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Maintenance Log
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="maintenanceSearch" class="form-control form-control-sm" placeholder="Search maintenance logs...">
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
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Actions</th>
                                        <th>Type</th>
                                        <th>Service Provider</th>
                                        <th>Date</th>
                                        <th>Certificate</th>
                                        <th>Overseen By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->maintenanceLogs as $log)
                                            <tr>
                                                <td>
                                                    <button wire:click="showEditMaintenanceModal({{ $log->id }})" 
                                                            class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteMaintenanceLog({{ $log->id }})" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $log->maintainance_type == 'in-house' ? 'In House' : 'External' }}</td>
                                                <td>
                                                    {{ $log->maintainance_type != 'in-house' 
                                                        ? (\App\Supplier::find($log->supplier_id)->name ?? '-') 
                                                        : (getUserById($log->employee_id)->name ?? '-') }}
                                                </td>
                                                <td>{{ $log->date }}</td>
                                                <td>
                                                    @if($log->certificate && $log->certificate != 'no-document')
                                                        <a href="{{ $log->certificate }}" target="_blank" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-download"></i> Download
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $log->overseer()->name ?? '-' }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#notesModal{{ $log->id }}">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </button>
                                                    <div class="modal fade" id="notesModal{{ $log->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Maintenance Notes</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>{{ $log->notes }}</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            {{ $this->maintenanceLogs->links() }}
                        </div>
                    @endif

                    <!-- Calibration Log Tab -->
                    @if($activeTab === 'calibration')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Calibration Logs</h5>
                            <button wire:click="showCreateCalibrationModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Calibration Log
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="calibrationSearch" class="form-control form-control-sm" placeholder="Search calibration logs...">
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
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Actions</th>
                                        <th>Type</th>
                                        <th>Service Provider</th>
                                        <th>Date</th>
                                        <th>Certificate</th>
                                        <th>Overseen By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->calibrationLogs as $log)
                                            <tr>
                                                <td>
                                                    <button wire:click="showEditCalibrationModal({{ $log->id }})" 
                                                            class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteCalibrationLog({{ $log->id }})" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $log->maintainance_type == 'in-house' ? 'In House' : 'External' }}</td>
                                                <td>
                                                    {{ $log->maintainance_type != 'in-house' 
                                                        ? (\App\Supplier::find($log->supplier_id)->name ?? '-') 
                                                        : (getUserById($log->employee_id)->name ?? '-') }}
                                                </td>
                                                <td>{{ $log->date }}</td>
                                                <td>
                                                    @if($log->certificate && $log->certificate != 'no-document')
                                                        <a href="{{ $log->certificate }}" target="_blank" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-download"></i> Download
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $log->overseer()->name ?? '-' }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#calNotesModal{{ $log->id }}">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </button>
                                                    <div class="modal fade" id="calNotesModal{{ $log->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Calibration Notes</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>{{ $log->notes }}</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            {{ $this->calibrationLogs->links() }}
                        </div>
                    @endif

                    <!-- Verification Log Tab -->
                    @if($activeTab === 'verification')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Verification Logs</h5>
                            <button wire:click="showCreateVerificationModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Verification Log
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="verificationSearch" class="form-control form-control-sm" placeholder="Search verification logs...">
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
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Actions</th>
                                        <th>Type</th>
                                        <th>Reference Standards</th>
                                        <th>Service Performer</th>
                                        <th>Verification Date</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->verificationLogs as $log)
                                        <tr>
                                            <td>
                                                <button wire:click="showEditVerificationModal({{ $log->id }})" 
                                                        class="btn btn-sm btn-outline-warning">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteVerificationLog({{ $log->id }})" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                            <td>{{ $log->maintainance_type == 'in-house' ? 'In House' : 'External' }}</td>
                                            <td>{{ $log->reference_standard }}</td>
                                            <td>
                                                {{ $log->maintainance_type != 'in-house' 
                                                    ? (\App\Supplier::find($log->supplier_id)->name ?? '-') 
                                                    : (getUserById($log->operator_id)->name ?? '-') }}
                                            </td>
                                            <td>{{ $log->verification_date }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#verificationModal{{ $log->id }}">
                                                    <i class="mdi mdi-eye"></i> View Details
                                                </button>
                                                <div class="modal fade" id="verificationModal{{ $log->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Verification Details</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <h6>Procedure</h6>
                                                                <p>{{ $log->procedure }}</p>
                                                                <h6>Responses</h6>
                                                                <p>{{ $log->response }}</p>
                                                                <h6>Remarks</h6>
                                                                <p>{{ $log->remarks }}</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            {{ $this->verificationLogs->links() }}
                        </div>
                    @endif

                    <!-- Operators Tab -->
                    @if($activeTab === 'operators')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Operators</h5>
                            <button wire:click="showOperatorModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Operator
                            </button>
                        </div>
                        <div class="p-3">
                            @php
                                $operators = $equipment->operators();
                            @endphp
                            @foreach($operators as $operator)
                                <div class="d-inline-block m-2">
                                    <span class="badge badge-primary p-2">
                                        <i class="mdi mdi-account"></i> {{ $operator->operator()->name ?? 'N/A' }}
                                        <button wire:click="removeOperator({{ $operator->id }})" 
                                                class="btn btn-sm btn-link text-white p-0 ms-2"
                                                onclick="return confirm('Remove this operator?')">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </span>
                                </div>
                            @endforeach
                            @if($operators->count() == 0)
                                <div class="alert alert-info">
                                    <i class="mdi mdi-alert"></i> No operators assigned yet.
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Attachments Tab -->
                    @if($activeTab === 'attachments')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Attachments</h5>
                            <button wire:click="showCreateAttachmentModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Attachment
                            </button>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                 <input type="text" wire:model.live="attachmentSearch" class="form-control form-control-sm" placeholder="Search attachments...">
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
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Attachment</th>
                                        <th>Uploaded By</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->attachments as $attachment)
                                        <tr>
                                            <td>{{ $loop->iteration + ($this->attachments->active() ? ($this->attachments->currentPage() - 1) * $this->attachments->perPage() : 0) }}</td>
                                            <td>{{ $attachment->title }}</td>
                                            <td>
                                                <a href="{{ $attachment->attachment }}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="mdi mdi-download"></i> Download
                                                </a>
                                            </td>
                                            <td>{{ getUserById($attachment->upload_by)->name ?? 'N/A' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#attachmentDescModal{{ $attachment->id }}">
                                                    <i class="mdi mdi-eye"></i> View
                                                </button>
                                                <div class="modal fade" id="attachmentDescModal{{ $attachment->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Description</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>{{ $attachment->description ?? 'No description' }}</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <button wire:click="showEditAttachmentModal({{ $attachment->id }})" 
                                                        class="btn btn-sm btn-outline-warning">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteAttachment({{ $attachment->id }})" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
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
                            <h5 class="mb-0">Notification Frequency</h5>
                            <button wire:click="showCreateNotificationModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add Notification
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Actions</th>
                                        <th>Frequency</th>
                                        <th>Notification Type</th>
                                        <th>Notification Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $notifications = \App\Models\Equipments\EquipmentNotifications::where('equipment_id', $equipment->id)->get();
                                    @endphp
                                    @foreach($notifications as $notification)
                                        <tr>
                                            <td>
                                                <button wire:click="showEditNotificationModal({{ $notification->id }})" 
                                                        class="btn btn-sm btn-outline-warning">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteNotification({{ $notification->id }})" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                            <td>{{ $notification->value }} {{ $notification->frequency }}</td>
                                            <td>{{ ucwords($notification->notification_type) }}</td>
                                            <td>{{ $notification->next_date }}</td>
                                            <td>
                                                @if($notification->is_sent)
                                                    <span class="badge badge-success">Sent</span>
                                                @else
                                                    <span class="badge badge-warning">Not Sent</span>
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
                            <h5 class="mb-0"><i class="mdi mdi-notebook-check-outline"></i> Daily Log Configuration</h5>
                            <button wire:click="showEditEquipmentModal" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-pencil"></i> Edit Configuration
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
                                        <th class="text-muted" style="width:50%">Value Type</th>
                                        <td>
                                            <span class="badge badge-info">{{ $dlTypeLabel }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Nature of Results</th>
                                        <td>
                                            <span class="badge badge-secondary">{{ $dlNatureLabel }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Reporting Unit</th>
                                        <td>{{ $equipment->daily_log_reporting_unit ?: '-' }}</td>
                                    </tr>
                                    @php
                                        $freqLabels = [1=>'Once a day',2=>'Twice a day',3=>'Three times a day',4=>'Four times a day',5=>'Five times a day',6=>'Six times a day'];
                                        $equipFreq = $equipment->daily_log_frequency ?? 1;
                                    @endphp
                                    <tr>
                                        <th class="text-muted">Logging Frequency</th>
                                        <td>{{ $freqLabels[$equipFreq] ?? 'Once a day' }}</td>
                                    </tr>
                                    @if($equipFreq >= 2)
                                    <tr>
                                        <th class="text-muted">Time Interval</th>
                                        <td>Every {{ $equipment->daily_log_time_interval }} hours</td>
                                    </tr>
                                    @endif
                                    @if($equipment->daily_log_value_type === 'constant')
                                    <tr>
                                        <th class="text-muted">Expected Value</th>
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
                                        <th class="text-muted">Acceptable Range</th>
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
                                        <p class="mb-0 small">Configuration is incomplete. Click <strong>Edit Configuration</strong> to set up daily log parameters.</p>
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
                                <select wire:model.live="nonConformancePerPage" class="form-control form-control-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
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
                                            <th>Date</th>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Equipment Modal -->
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background-color: #001a41;">
                        <h5 class="modal-title text-white"><i class="mdi mdi-pencil"></i> Edit Equipment</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <form wire:submit.prevent="saveEquipment" class="eq-form">
                            <div class="eq-section-header">
                                <i class="mdi mdi-information-outline"></i> Basic Information
                            </div>
                            <!-- Similar form fields as EquipmentManager but for editing -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.name" class="form-control" required>
                                        @error('equipmentForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Equipment Number <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.equipment_number" class="form-control" required>
                                        @error('equipmentForm.equipment_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea wire:model="equipmentForm.description" class="form-control" rows="2" required></textarea>
                                        @error('equipmentForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Photo</label>
                                        <input type="file" wire:model="photo" class="form-control" accept="image/*">
                                        @if($equipment->picture)
                                            <small class="text-muted">Current: <img src="{{ $equipment->picture }}" style="width: 50px;"></small>
                                        @endif
                                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Add more fields similar to EquipmentManager -->
                            <div class="eq-section-header mt-4">
                                <i class="mdi mdi-cogs"></i> Specifications
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Make <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.make" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Model <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.model" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Serial Number</label>
                                        <input type="text" wire:model="equipmentForm.serial_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Barcode Number</label>
                                        <input type="text" wire:model="equipmentForm.barcode_number" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Manufacturer</label>
                                        <input type="text" wire:model="equipmentForm.manufacturer" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="eq-section-header mt-4">
                                <i class="mdi mdi-map-marker"></i> Assignment &amp; Location
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select wire:model="equipmentForm.status" class="form-control" required>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Condition <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.condition" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Warranty Date <span class="text-danger">*</span></label>
                                        <input type="date" wire:model="equipmentForm.warranty_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Purchased</label>
                                        <input type="date" wire:model="equipmentForm.date_purchased" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <select wire:model="equipmentForm.assigned_department" class="form-control" required>
                                            <option value="">Choose Department...</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Assigned Employee</label>
                                        <select wire:model="equipmentForm.assigned_employee_id" class="form-control">
                                            <option value="">Choose Employee...</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Active</label>
                                        <div class="d-flex align-items-center" style="height:38px;">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model="equipmentForm.active" class="form-check-input" id="equipment_active">
                                                <label class="form-check-label" for="equipment_active">Mark this equipment as active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Requires Daily Log</label>
                                        <div class="d-flex align-items-center" style="height:38px;">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model.live="equipmentForm.requires_daily_log" class="form-check-input" id="equipment_requires_daily_log">
                                                <label class="form-check-label" for="equipment_requires_daily_log">Equipment appears on the Daily Log page</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($equipmentForm['requires_daily_log']))
                            @php $dlType = $equipmentForm['daily_log_value_type'] ?? ''; $dlNature = $equipmentForm['daily_log_nature'] ?? ''; $dlFreq = intval($equipmentForm['daily_log_frequency'] ?? 1); @endphp
                            <div class="eq-section-header mt-4">
                                <i class="mdi mdi-notebook-check-outline"></i> Daily Log Configuration
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Logging Frequency <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_frequency" class="form-control">
                                            <option value="1">Once a day</option>
                                            <option value="2">Twice a day</option>
                                            <option value="3">Three times a day</option>
                                            <option value="4">Four times a day</option>
                                            <option value="5">Five times a day</option>
                                            <option value="6">Six times a day</option>
                                        </select>
                                        @error('equipmentForm.daily_log_frequency') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if($dlFreq >= 2)
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Time Interval (hours) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_time_interval" class="form-control" min="1" placeholder="e.g. 4">
                                        @error('equipmentForm.daily_log_time_interval') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Number of hours between each reading.</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Value Type <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_value_type" class="form-control">
                                            <option value="">-- Select --</option>
                                            <option value="constant">Constant</option>
                                            <option value="range">Range</option>
                                        </select>
                                        @error('equipmentForm.daily_log_value_type') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Whether the expected value is a single constant or a range.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nature of Result <span class="text-danger">*</span></label>
                                        <select wire:model.live="equipmentForm.daily_log_nature" class="form-control" {{ $dlType === 'range' ? 'disabled' : '' }}>
                                            <option value="">-- Select --</option>
                                            <option value="qualitative">Qualitative</option>
                                            <option value="quantitative">Quantitative</option>
                                        </select>
                                        @error('equipmentForm.daily_log_nature') <span class="text-danger">{{ $message }}</span> @enderror
                                        @if($dlType === 'range')
                                            <small class="form-text text-muted"><i class="mdi mdi-information-outline"></i> Range values are always quantitative.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Constant + Qualitative: text expected value --}}
                            @if($dlType === 'constant' && $dlNature === 'qualitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="equipmentForm.daily_log_expected_value" class="form-control" placeholder="e.g. Pass, Clear, Present">
                                        @error('equipmentForm.daily_log_expected_value') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Constant + Quantitative: numeric expected value + tolerance --}}
                            @if($dlType === 'constant' && $dlNature === 'quantitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_value" class="form-control" step="any" placeholder="e.g. 7.0">
                                        @error('equipmentForm.daily_log_expected_value') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance (&plusmn;) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_tolerance" class="form-control" min="1" max="100" placeholder="e.g. 2">
                                        @error('equipmentForm.daily_log_tolerance') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Acceptable deviation from the expected value (e.g. &plusmn;2).</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Range + Quantitative: min, max and tolerance --}}
                            @if($dlType === 'range' && $dlNature === 'quantitative')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Minimum Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_min" class="form-control" step="any" placeholder="e.g. 6.5">
                                        @error('equipmentForm.daily_log_expected_min') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maximum Value <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_expected_max" class="form-control" step="any" placeholder="e.g. 7.5">
                                        @error('equipmentForm.daily_log_expected_max') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance (&plusmn;) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.daily_log_tolerance" class="form-control" min="1" max="100" placeholder="e.g. 2">
                                        @error('equipmentForm.daily_log_tolerance') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Acceptable deviation (&plusmn;).</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- Reporting unit (shown whenever a value type is selected) --}}
                            @if($dlType !== '')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Reporting Unit</label>
                                        <select wire:model="equipmentForm.daily_log_reporting_unit" class="form-control">
                                            <option value="">-- Select Unit --</option>
                                            @foreach($reportingUnits as $unit)
                                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('equipmentForm.daily_log_reporting_unit') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Unit of measurement for the recorded value.</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                            <div class="eq-section-header mt-4">
                                <i class="mdi mdi-calendar-clock"></i> Maintenance &amp; Calibration Schedule
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maintenance After (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.maintainance_days" class="form-control" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Maintenance Notification (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.maintainance_notification_in_days" class="form-control" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Calibration After (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.calibration_days" class="form-control" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Calibration Notification (Days) <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="equipmentForm.calibration_notification_in_days" class="form-control" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveEquipment">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
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
                            {{ $editingLog ? 'Edit' : 'Create' }} Maintenance Log
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showMaintenanceModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveMaintenanceLog">
                            <div class="form-group mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="maintenanceForm.date" class="form-control" required>
                                @error('maintenanceForm.date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
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
                                    <select wire:model="maintenanceForm.employee_id" class="form-select">
                                        <option value="">Choose Employee...</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label class="form-label">Supplier</label>
                                    <select wire:model="maintenanceForm.supplier_id" class="form-select">
                                        <option value="">Choose Supplier...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group mb-3">
                                <label class="form-label">Certificate</label>
                                <input type="file" wire:model="certificate" class="form-control" accept=".pdf,.doc,.docx">
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
                        <button type="button" class="btn btn-secondary" wire:click="$set('showMaintenanceModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveMaintenanceLog">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Calibration Modal (similar structure to Maintenance) -->
    @if($showCalibrationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingLog ? 'pencil' : 'plus' }}"></i>
                            {{ $editingLog ? 'Edit' : 'Create' }} Calibration Log
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showCalibrationModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCalibrationLog">
                            <div class="form-group mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="calibrationForm.date" class="form-control" required>
                                @error('calibrationForm.date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="calibrationForm.description" class="form-control" rows="4" required></textarea>
                                @error('calibrationForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Reference Number <span class="text-danger">*</span></label>
                                <input type="text" wire:model="calibrationForm.reference_number" class="form-control" required>
                                @error('calibrationForm.reference_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Service Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="calibrationForm.maintainance_type" 
                                           value="in_house" id="calib_inhouse">
                                    <label class="form-check-label" for="calib_inhouse">In House</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="calibrationForm.maintainance_type" 
                                           value="external" id="calib_external">
                                    <label class="form-check-label" for="calib_external">External</label>
                                </div>
                            </div>
                            @if($calibrationForm['maintainance_type'] == 'in_house')
                                <div class="form-group mb-3">
                                    <label class="form-label">Employee</label>
                                    <select wire:model="calibrationForm.employee_id" class="form-select">
                                        <option value="">Choose Employee...</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label class="form-label">Supplier</label>
                                    <select wire:model="calibrationForm.supplier_id" class="form-select">
                                        <option value="">Choose Supplier...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group mb-3">
                                <label class="form-label">Certificate</label>
                                <input type="file" wire:model="certificate" class="form-control" accept=".pdf,.doc,.docx">
                                @error('certificate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Notes <span class="text-danger">*</span></label>
                                <textarea wire:model="calibrationForm.notes" class="form-control" rows="4" required></textarea>
                                @error('calibrationForm.notes') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCalibrationModal', false)">Cancel</button>
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
                            {{ $editingLog ? 'Edit' : 'Create' }} Verification Log
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
                                    <select wire:model="verificationForm.operator_id" class="form-select">
                                        <option value="">Choose Operator...</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label class="form-label">Supplier</label>
                                    <select wire:model="verificationForm.supplier_id" class="form-select">
                                        <option value="">Choose Supplier...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
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
                        <button type="button" class="btn btn-secondary" wire:click="$set('showVerificationModal', false)">Cancel</button>
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
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-plus"></i> Add Operators</h5>
                        <button type="button" class="btn-close" wire:click="$set('showOperatorModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveOperators">
                            <div class="form-group mb-3">
                                <label class="form-label">Select Operators <span class="text-danger">*</span></label>
                                <select wire:model="operatorForm.operators" class="form-select" multiple size="10" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                @error('operatorForm.operators') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showOperatorModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveOperators">
                            <i class="mdi mdi-content-save"></i> Save
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
                                <label class="form-label">Description</label>
                                <textarea wire:model="attachmentForm.description" class="form-control" rows="4"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showAttachmentModal', false)">Cancel</button>
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
                                <select wire:model="notificationForm.notification_type" class="form-select" required>
                                    <option value="calibration">Calibration</option>
                                    <option value="maintanance">Maintenance</option>
                                    <option value="verification">Verification</option>
                                </select>
                                @error('notificationForm.notification_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Value (Days) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="notificationForm.value" class="form-control" min="1" required>
                                @error('notificationForm.value') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                <select wire:model="notificationForm.frequency" class="form-select" required>
                                    <option value="days">Days</option>
                                    <option value="weeks">Weeks</option>
                                    <option value="months">Months</option>
                                </select>
                                @error('notificationForm.frequency') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showNotificationModal', false)">Cancel</button>
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
        background: linear-gradient(120deg, #ffffff 0%, #f3f6fb 100%);
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
        padding: 0.55rem 1rem 0;
        background: linear-gradient(120deg, #f7f9fc 0%, #edf2f8 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .eq-main-body {
        background: #ffffff;
    }
    .eq-main-tabs {
        border-bottom: none;
        gap: 6px;
        flex-wrap: wrap;
    }
    .eq-main-tabs .nav-item {
        margin-bottom: 0;
    }
    .eq-main-tabs .nav-link {
        border: 1px solid transparent;
        border-radius: 8px 8px 0 0;
        padding: 0.45rem 0.8rem;
        color: #4f5d6b;
        font-size: 0.82rem;
        font-weight: 600;
        background: transparent;
    }
    .eq-main-tabs .nav-link:hover {
        border-color: #d8e0eb;
        background: #f6f9fd;
        color: #2d3b49;
    }
    .eq-main-tabs .nav-link.active {
        color: #0b4fb3;
        border-color: #c9d8ef;
        background: #ffffff;
        box-shadow: 0 -1px 0 #ffffff;
    }
    @media (max-width: 768px) {
        .eq-hero-title {
            font-size: 1.55rem;
        }
        .eq-main-header {
            padding-top: 0.7rem;
        }
    }
    </style>

    @script
    <script>
    (function () {
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

        if (!window._dlPerfChartBindings) {
            window._dlPerfChartBindings = true;

            document.addEventListener('livewire:initialized', function () {
                scheduleBuild();

                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('morph.updated', function () {
                        scheduleBuild();
                    });
                }
            });

            document.addEventListener('click', function (event) {
                var dailyLogTabButton = event.target.closest("[wire\\:click=\"setActiveTab('dailylog')\"]");
                if (dailyLogTabButton) {
                    scheduleBuild();
                }
            });
        }

        scheduleBuild();
    })();
    </script>
    @endscript
</div>
