<div class="container-fluid">
    @if(!$disposal)
        <div class="alert alert-danger">
            Disposal request not found.
        </div>
    @else
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0">
                                    <i class="mdi mdi-delete-sweep text-primary"></i>
                                    Disposal Request #{{ $disposal->id }}
                                </h2>
                                <p class="text-muted mb-0">{{ $disposal->equipment->name }} ({{ $disposal->equipment->equipment_number }})</p>
                            </div>
                            <div>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'executed' => 'info',
                                        'closed' => 'dark'
                                    ];
                                    $statusColor = $statusColors[$disposal->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $statusColor }} p-2" style="font-size: 1rem;">
                                    {{ strtoupper($disposal->status) }}
                                </span>
                                <a href="{{ route('equipment-disposal-home') }}" class="btn btn-secondary btn-sm ms-2">
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

        <!-- Action Buttons -->
        @if($disposal->status === 'pending' && $canApprove())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Action Required:</strong> You have a pending approval for this disposal request.
                        <button wire:click="openApprovalModal" class="btn btn-primary btn-sm ms-3">
                            <i class="mdi mdi-check-circle"></i> Review & Approve
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($disposal->status === 'approved' && $canExecute())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success">
                        <i class="mdi mdi-check-circle"></i>
                        <strong>Ready for Execution:</strong> This disposal request has been approved. You can now execute the disposal.
                        <button wire:click="openExecutionModal" class="btn btn-success btn-sm ms-3">
                            <i class="mdi mdi-play-circle"></i> Execute Disposal
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content with Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" 
                                        wire:click="switchTab('details')" type="button">
                                    <i class="mdi mdi-information"></i> Details
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link {{ $activeTab === 'approvals' ? 'active' : '' }}" 
                                        wire:click="switchTab('approvals')" type="button">
                                    <i class="mdi mdi-check-all"></i> Approvals
                                    @if($disposal->approvals->count() > 0)
                                        <span class="badge badge-primary">{{ $disposal->approvals->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link {{ $activeTab === 'audit' ? 'active' : '' }}" 
                                        wire:click="switchTab('audit')" type="button">
                                    <i class="mdi mdi-history"></i> Audit Trail
                                </button>
                            </li>
                            @if($disposal->status === 'approved' || $disposal->status === 'executed' || $disposal->status === 'closed')
                                <li class="nav-item">
                                    <button class="nav-link {{ $activeTab === 'execution' ? 'active' : '' }}" 
                                            wire:click="switchTab('execution')" type="button">
                                        <i class="mdi mdi-play-circle"></i> Execution
                                    </button>
                                </li>
                            @endif
                            @if($disposal->status === 'executed' || $disposal->status === 'closed')
                                <li class="nav-item">
                                    <button class="nav-link {{ $activeTab === 'report' ? 'active' : '' }}" 
                                            wire:click="switchTab('report')" type="button">
                                        <i class="mdi mdi-file-pdf-box"></i> PDF Report
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="card-body">
                        <!-- Details Tab -->
                        @if($activeTab === 'details')
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-4">Equipment Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Equipment Name:</th>
                                            <td>{{ $disposal->equipment->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Equipment Number:</th>
                                            <td>{{ $disposal->equipment->equipment_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number:</th>
                                            <td>{{ $disposal->equipment->serial_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Make:</th>
                                            <td>{{ $disposal->equipment->make }}</td>
                                        </tr>
                                        <tr>
                                            <th>Model:</th>
                                            <td>{{ $disposal->equipment->model }}</td>
                                        </tr>
                                        <tr>
                                            <th>Department:</th>
                                            <td>{{ $disposal->equipment->assigned_department ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Condition:</th>
                                            <td>{{ $disposal->equipment->condition ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-4">Disposal Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Requested By:</th>
                                            <td>{{ $disposal->requester->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Requested Date:</th>
                                            <td>{{ $disposal->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Proposed Method:</th>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($disposal->proposed_method ?? '-') }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Risk Level:</th>
                                            <td>
                                                @php
                                                    $riskColors = [
                                                        'Low' => 'success',
                                                        'Medium' => 'warning',
                                                        'High' => 'danger',
                                                        'Critical' => 'danger'
                                                    ];
                                                    $riskColor = $riskColors[$disposal->risk_level] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $riskColor }}">{{ $disposal->risk_level ?? '-' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Regulatory Category:</th>
                                            <td>{{ $disposal->regulatory_category ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="badge badge-{{ $statusColor }}">{{ ucfirst($disposal->status) }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3">Justification</h5>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p style="white-space: pre-wrap;">{{ $disposal->justification }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3">Evidence Files</h5>
                                    @if($disposal->files->count() > 0)
                                        <div class="row">
                                            @foreach($disposal->files as $file)
                                                <div class="col-md-3 mb-3">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            @if($file->file_type === 'photo')
                                                                <img src="{{ $file->file_path }}" class="img-fluid" style="max-height: 150px; border-radius: 8px;" alt="{{ $file->file_name }}">
                                                            @else
                                                                <i class="mdi mdi-file-document" style="font-size: 3rem;"></i>
                                                            @endif
                                                            <p class="mt-2 mb-0"><small>{{ Str::limit($file->file_name, 20) }}</small></p>
                                                            <a href="{{ $file->file_path }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                                                <i class="mdi mdi-download"></i> View
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No evidence files uploaded.</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Approvals Tab -->
                        @if($activeTab === 'approvals')
                            <h5 class="mb-4">Approval Workflow</h5>
                            @if($disposal->approvals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Step</th>
                                                <th>Approver</th>
                                                <th>Role</th>
                                                <th>Decision</th>
                                                <th>Date</th>
                                                <th>Remarks</th>
                                                <th>Signature</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($disposal->approvals as $approval)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-primary">Step {{ $approval->step }}</span>
                                                    </td>
                                                    <td>{{ $approval->approver->name ?? '-' }}</td>
                                                    <td>-</td>
                                                    <td>
                                                        @if($approval->decision === 'approve')
                                                            <span class="badge badge-success">Approved</span>
                                                        @elseif($approval->decision === 'reject')
                                                            <span class="badge badge-danger">Rejected</span>
                                                        @else
                                                            <span class="badge badge-warning">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($approval->decided_at)
                                                            {{ $approval->decided_at->format('Y-m-d H:i:s') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($approval->remarks)
                                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#remarksModal{{ $approval->id }}">
                                                                View
                                                            </button>
                                                            <div class="modal fade" id="remarksModal{{ $approval->id }}" tabindex="-1">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Remarks - Step {{ $approval->step }}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <p>{{ $approval->remarks }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($approval->signature_path)
                                                            <img src="{{ $approval->signature_path }}" style="max-width: 100px; max-height: 50px;" alt="Signature">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No approvals configured yet.</p>
                            @endif
                        @endif

                        <!-- Audit Trail Tab -->
                        @if($activeTab === 'audit')
                            <h5 class="mb-4">Audit Trail (ISO 19011 Compliant)</h5>
                            @if($disposal->auditLogs->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Action</th>
                                                <th>User</th>
                                                <th>IP Address</th>
                                                <th>Description</th>
                                                <th>Changes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($disposal->auditLogs as $log)
                                                <tr>
                                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                                    <td>
                                                        <span class="badge badge-info">{{ ucfirst($log->action) }}</span>
                                                    </td>
                                                    <td>{{ $log->user->name ?? 'System' }}</td>
                                                    <td>{{ $log->ip_address ?? '-' }}</td>
                                                    <td>{{ $log->description ?? '-' }}</td>
                                                    <td>
                                                        @if($log->old_values || $log->new_values)
                                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#auditModal{{ $log->id }}">
                                                                View Changes
                                                            </button>
                                                            <div class="modal fade" id="auditModal{{ $log->id }}" tabindex="-1">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Audit Details - {{ ucfirst($log->action) }}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            @if($log->old_values)
                                                                                <h6>Before:</h6>
                                                                                <pre class="bg-light p-3">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                                            @endif
                                                                            @if($log->new_values)
                                                                                <h6>After:</h6>
                                                                                <pre class="bg-light p-3">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No audit log entries found.</p>
                            @endif
                        @endif

                        <!-- Execution Tab -->
                        @if($activeTab === 'execution')
                            @if($disposal->status === 'executed' || $disposal->status === 'closed')
                                <h5 class="mb-4">Disposal Execution Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Final Disposal Method:</th>
                                                <td>{{ $disposal->final_disposal_method ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Disposal Date:</th>
                                                <td>{{ $disposal->disposal_date ? $disposal->disposal_date->format('Y-m-d') : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Executed By:</th>
                                                <td>{{ $disposal->executor->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Witness:</th>
                                                <td>{{ $disposal->witness->name ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Compliance Checklist</h6>
                                        @if($disposal->compliance_checklist_json)
                                            <ul class="list-group">
                                                @foreach($disposal->compliance_checklist_json as $item => $checked)
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>{{ ucfirst(str_replace('_', ' ', $item)) }}</span>
                                                        @if($checked)
                                                            <span class="badge badge-success"><i class="mdi mdi-check"></i></span>
                                                        @else
                                                            <span class="badge badge-danger"><i class="mdi mdi-close"></i></span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6>Execution Photos & Documents</h6>
                                        @php
                                            $executionFiles = $disposal->files->where('file_type', 'photo')->where('description', 'execution');
                                        @endphp
                                        @if($executionFiles->count() > 0)
                                            <div class="row">
                                                @foreach($executionFiles as $file)
                                                    <div class="col-md-3 mb-3">
                                                        <img src="{{ $file->file_path }}" class="img-fluid" style="border-radius: 8px;" alt="{{ $file->file_name }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i>
                                    Execution details will appear here after disposal is executed.
                                </div>
                            @endif
                        @endif

                        <!-- PDF Report Tab -->
                        @if($activeTab === 'report')
                            <h5 class="mb-4">Disposal Report</h5>
                            @if($disposal->pdf_report_path)
                                <div class="text-center">
                                    <i class="mdi mdi-file-pdf-box" style="font-size: 5rem; color: #dc3545;"></i>
                                    <p class="mt-3">Disposal Report Generated</p>
                                    <div class="mt-4">
                                        <a href="{{ route('equipment-disposal-download-report', ['disposalId' => $disposal->id]) }}" 
                                           class="btn btn-primary me-2">
                                            <i class="mdi mdi-download"></i> Download PDF
                                        </a>
                                        <button wire:click="viewReport" class="btn btn-secondary">
                                            <i class="mdi mdi-eye"></i> View in Browser
                                        </button>
                                    </div>
                                    @if($disposal->sha_hash)
                                        <div class="mt-4">
                                            <small class="text-muted">
                                                <strong>SHA-256 Hash:</strong> {{ $disposal->sha_hash }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert"></i>
                                    Report has not been generated yet. Report will be available after disposal execution.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Modal -->
        @livewire('equipment.disposal-approval-modal', ['disposalId' => $disposal->id], key('approval-modal-'.$disposal->id))

        <!-- Execution Modal -->
        @if($showExecutionModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto; z-index: 1050;">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="mdi mdi-play-circle"></i> Execute Disposal
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeExecutionModal"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="executeDisposal">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Final Disposal Method <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model="executionForm.final_disposal_method" class="form-select" required>
                                                <option value="">Select Method</option>
                                                <option value="scrap">Scrap</option>
                                                <option value="donation">Donation</option>
                                                <option value="auction">Auction</option>
                                                <option value="recycling">Recycling</option>
                                                <option value="destruction">Destruction</option>
                                            </select>
                                            @error('executionForm.final_disposal_method') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Disposal Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" wire:model="executionForm.disposal_date" class="form-control" required>
                                            @error('executionForm.disposal_date') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Executed By <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model="executionForm.executed_by" class="form-select" required>
                                                <option value="">Select User</option>
                                                @foreach(getUsers() as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('executionForm.executed_by') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Witness <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model="executionForm.witness_id" class="form-select" required>
                                                <option value="">Select Witness</option>
                                                @foreach(getUsers() as $user)
                                                    @if($user->id != $executionForm['executed_by'])
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('executionForm.witness_id') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            <small class="text-muted">Witness must be different from executor</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Execution Photos
                                            </label>
                                            <input type="file" wire:model="executionPhotos" class="form-control" multiple accept="image/*">
                                            @error('executionPhotos.*') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                External Vendor Documents
                                            </label>
                                            <input type="file" wire:model="executionDocuments" class="form-control" multiple>
                                            @error('executionDocuments.*') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="mb-3">Compliance Checklist <span class="text-danger">*</span></h6>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                @php
                                                    $checklist = [
                                                        'equipment_removed_from_service' => 'Equipment removed from service',
                                                        'equipment_labeled' => 'Equipment labeled (ISO 17025 Clause 6.4.13)',
                                                        'regulatory_requirements_met' => 'Regulatory requirements met',
                                                        'witness_present' => 'Independent witness present',
                                                        'documentation_complete' => 'Documentation complete'
                                                    ];
                                                @endphp
                                                @foreach($checklist as $key => $label)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" 
                                                               wire:model="executionForm.compliance_checklist.{{ $key }}"
                                                               id="checklist_{{ $key }}">
                                                        <label class="form-check-label" for="checklist_{{ $key }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('executionForm.compliance_checklist') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeExecutionModal">
                                <i class="mdi mdi-close"></i> Cancel
                            </button>
                            <button type="button" wire:click="executeDisposal" class="btn btn-success" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="executeDisposal">
                                    <i class="mdi mdi-check-circle"></i> Execute Disposal
                                </span>
                                <span wire:loading wire:target="executeDisposal">
                                    <i class="mdi mdi-loading mdi-spin"></i> Executing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>










