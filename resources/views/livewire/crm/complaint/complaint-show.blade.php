@push('styles')
    <style>
        /* ========================================
                                                                                                                                       Complaint Show Section - Modern Styling
                                                                                                                                       Matching customer-show.blade.php design
                                                                                                                                       ======================================== */

        .complaint-show-section {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Page Title */
        .complaint-show-section .page-title {
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -0.025em;
            color: #1a202c;
            margin-bottom: 0;
        }

        .complaint-show-section .page-title i {
            color: #e53e3e;
            margin-right: 8px;
        }

        .complaint-show-section .page-title .text-muted {
            font-size: 1.25rem;
            font-weight: 400;
            color: #718096;
        }

        /* Tab Card Styling */
        .complaint-show-section .tab-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            overflow: visible;
        }

        .complaint-show-section .tab-card-header {
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            padding: 0;
        }


        /* Tab Content */
        .complaint-show-section .tab-content {
            background: #ffffff;
            min-height: 400px;
        }

        .complaint-show-section .tab-pane {
            padding: 24px !important;
        }

        /* Typography */
        .complaint-show-section h3,
        .complaint-show-section h4,
        .complaint-show-section h5 {
            color: #1a202c;
            font-weight: 600;
        }

        .complaint-show-section h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .complaint-show-section h4 {
            font-size: 1.125rem;
            margin-bottom: 16px;
        }

        .complaint-show-section h5 {
            font-size: 1rem;
            margin-bottom: 12px;
        }

        .complaint-show-section h5.card-title i {
            color: #e53e3e;
            margin-right: 6px;
        }

        /* Table Styling */
        .complaint-show-section .table-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow: visible;
            margin-bottom: 20px;
        }

        .complaint-show-section .table {
            margin-bottom: 0;
        }

        .complaint-show-section .table thead th {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4a5568;
            border-top: none;
        }

        .complaint-show-section .table tbody td {
            padding: 12px 16px;
            font-size: 0.875rem;
            color: #2d3748;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .complaint-show-section .table tbody tr {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .complaint-show-section .table tbody tr:hover {
            background: #f7fafc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .complaint-show-section .table tbody tr:last-child td {
            border-bottom: none;
        }

        .complaint-show-section .table-striped tbody tr:nth-of-type(odd) {
            background-color: transparent;
        }

        /* Action Buttons */
        .complaint-show-section .action-buttons {
            display: flex;
            gap: 4px;
            flex-wrap: nowrap;
        }

        .complaint-show-section .action-buttons .btn {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            border: none;
            transition: all 0.15s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .complaint-show-section .action-buttons .btn i {
            font-size: 14px;
        }

        /* Edit Button */
        .complaint-show-section .action-buttons .btn-edit,
        .complaint-show-section .btn-edit {
            background: #edf2f7;
            color: #4299e1;
        }

        .complaint-show-section .action-buttons .btn-edit:hover,
        .complaint-show-section .btn-edit:hover {
            background: #4299e1;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(66, 153, 225, 0.3);
            transform: translateY(-1px);
        }

        /* View Button */
        .complaint-show-section .action-buttons .btn-view,
        .complaint-show-section .btn-view {
            background: #f0fff4;
            color: #38a169;
        }

        .complaint-show-section .action-buttons .btn-view:hover,
        .complaint-show-section .btn-view:hover {
            background: #38a169;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(56, 161, 105, 0.3);
            transform: translateY(-1px);
        }

        /* Delete Button */
        .complaint-show-section .action-buttons .btn-delete,
        .complaint-show-section .btn-delete {
            background: #fff5f5;
            color: #e53e3e;
        }

        .complaint-show-section .action-buttons .btn-delete:hover,
        .complaint-show-section .btn-delete:hover {
            background: #e53e3e;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(229, 62, 62, 0.3);
            transform: translateY(-1px);
        }


        /* Modal footer button spacing */
        .complaint-show-section .modal-footer .btn+.btn {
            margin-left: 8px;
        }

        /* Table styling for tab sections */
        .complaint-show-section .table-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow: visible;
            margin-bottom: 20px;
        }

        .complaint-show-section .tab-table thead th {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4a5568;
            border-top: none;
        }

        .complaint-show-section .tab-table tbody td {
            padding: 12px 16px;
            font-size: 0.875rem;
            color: #2d3748;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .complaint-show-section .tab-table tbody tr:hover {
            background: #f7fafc;
        }

        .complaint-show-section .tab-table tbody tr:last-child td {
            border-bottom: none;
        }

        .complaint-show-section .tab-table-striped tbody tr:nth-of-type(odd) {
            background-color: transparent;
        }

        /* Pagination */
        .complaint-show-section .pagination {
            margin-bottom: 0;
        }

        .complaint-show-section .pagination .page-link {
            color: #4a5568;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            margin: 0 2px;
            border-radius: 6px;
            transition: all 0.2s ease;
            min-width: 40px;
            text-align: center;
        }

        .complaint-show-section .pagination .page-link:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
            color: #2d3748;
        }

        .complaint-show-section .pagination .page-item.active .page-link {
            background: #4299e1;
            border-color: #4299e1;
            color: #ffffff;
            font-weight: 600;
        }

        .complaint-show-section .pagination .page-item.disabled .page-link {
            background: #f7fafc;
            border-color: #e2e8f0;
            color: #cbd5e0;
        }

        /* Workflow Action Buttons */
        .complaint-show-section .workflow-actions {
            display: flex;
            gap: 12px;
            /* Increased from 8px */
            flex-wrap: wrap;
            pointer-events: auto !important;
            position: relative;
            z-index: 100 !important;
        }

        .complaint-show-section .workflow-actions .btn {
            font-size: 0.875rem;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            pointer-events: auto !important;
            position: relative;
            z-index: 101 !important;
        }

        /* Info Cards/Details */
        .complaint-show-section .info-card {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
        }

        .complaint-show-section .info-card .label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #718096;
            margin-bottom: 4px;
        }

        .complaint-show-section .info-card .value {
            font-size: 1rem;
            color: #1a202c;
            font-weight: 500;
        }

        /* Empty States */
        .complaint-show-section .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .complaint-show-section .empty-state i {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 16px;
        }

        .complaint-show-section .empty-state .alert {
            border: none;
            background: #f7fafc;
            color: #4a5568;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .complaint-show-section .page-title {
                font-size: 1.5rem;
            }

            .complaint-show-section .nav-tabs .nav-link {
                padding: 12px 14px;
                font-size: 0.8125rem;
            }

            .complaint-show-section .tab-pane {
                padding: 16px !important;
            }

            .complaint-show-section .workflow-actions {
                flex-direction: column;
            }

            .complaint-show-section .workflow-actions .btn {
                width: 100%;
            }
        }

        /* Table Scrollbar */
        .complaint-show-section .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }

        .complaint-show-section .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .complaint-show-section .table-responsive::-webkit-scrollbar-track {
            background: #f7fafc;
        }

        .complaint-show-section .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .complaint-show-section .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
@endpush


@push('scripts')
    {{-- Global Modal Event Listeners --}}
    <script>
        document.addEventListener('livewire:load', function () {
            console.log('Complaint modals initialized');
            // Previous custom logic removed in favor of native Livewire/Alpine components
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Modal Z-Index Management */
        .modal {
            z-index: 9999 !important;
        }

        .modal-dialog {
            z-index: 10000 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }
    </style>
@endpush


<div class="complaint-show-section">
    <main>
        <x-bread-crumb :items="$this->breadcrumbItems"></x-bread-crumb>

        {{-- Incident Identity Card --}}
        <div class="px-4 pb-4 pt-3"> {{-- Increased padding for better vertical spacing --}}
            <div class="d-flex align-items-start justify-content-between flex-wrap">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center rounded"
                        style="width:48px;height:48px;background:#fff3f3;flex-shrink:0;border:2px solid #f8d6d6;">
                        <i class="mdi mdi-comment-alert text-danger" style="font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="font-weight-bold text-dark mr-2"
                                style="font-size:1.15rem;font-family:monospace;letter-spacing:.03em;">{{ $complaint->complaint_id }}</span>
                            @php
                                $priColor = match ($complaint->priority) {
                                    'high' => 'badge-danger',
                                    'medium' => 'badge-warning',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $priColor }} mr-1"
                                style="font-size:0.68rem;padding:3px 8px;">{{ ucfirst($complaint->priority ?? 'Low') }}
                                Priority</span>
                            <span class="badge badge-light border"
                                style="font-size:0.68rem;padding:3px 8px;">{{ $workflowStage }}</span>
                        </div>
                        <small class="text-muted" style="font-size:0.72rem;">
                            <i
                                class="mdi mdi-account-outline mr-1"></i>{{ $complaint->client->name ?? $complaint->received_from }}
                            <span class="mx-2">&middot;</span>
                            <i class="mdi mdi-calendar-outline mr-1"></i>Logged
                            {{ \Carbon\Carbon::parse($complaint->date)->format('d M Y') }}
                        </small>
                    </div>
                </div>
                <div class="workflow-actions mt-2 mt-md-0">
                    @if($complaint->complaint_workflow == 1)
                        <button class="btn btn-success btn-sm"
                            wire:click="$dispatch('initiate-workflow-action', 'approveNext')">
                            <i class="mdi mdi-check-all"></i> Approve & Proceed
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"
                            wire:click="$dispatch('initiate-workflow-action', 'logForRecordOnly')">
                            <i class="mdi mdi-archive-outline"></i> Log for Record Only
                        </button>
                    @endif
                    @if($complaint->complaint_workflow >= 5 && $activeTab !== 'capa')
                        <button class="btn btn-outline-danger btn-sm"
                            wire:click="generateReport({{ $complaint->id }})">
                            <i class="mdi mdi-file-pdf-box"></i> Export to PDF
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-sm-12 p-2">
                <div class="card tab-card">
                    <div class="card-header tab-card-header">
                        <ul class="crm-tab-nav" id="complaint-tabs" role="tablist">
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'details' ? 'active' : '' }}"
                                    wire:click="switchTab('details')" href="#complaint-details" role="tab">
                                    <i class="mdi mdi-information-outline"></i> Complaint Details
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'attachments' ? 'active' : '' }}"
                                    wire:click="switchTab('attachments')" href="#complaint-attachments" role="tab">
                                    <i class="mdi mdi-paperclip"></i> Attachments
                                    <span class="crm-tab-badge">{{ $attachmentsCount }}</span>
                                </a>
                            </li>
                            @if($complaint->complaint_workflow >= 2 && !$this->hasCarIssued)
                                <li class="crm-tab-item">
                                    <a class="crm-tab-link {{ $activeTab == 'investigation' ? 'active' : '' }}"
                                        wire:click="switchTab('investigation')" href="#complaint-investigation" role="tab">
                                        <i class="mdi mdi-briefcase-search-outline"></i> Investigation
                                    </a>
                                </li>
                            @endif
                            @if($this->hasCarIssued && $complaint->complaint_workflow >= 3)
                                <li class="crm-tab-item">
                                    <a class="crm-tab-link {{ $activeTab == 'capa' ? 'active' : '' }}"
                                        wire:click="switchTab('capa')" href="#complaint-capa" role="tab">
                                        <i class="mdi mdi-microscope"></i> CAPA & RCA
                                    </a>
                                </li>
                            @endif
                            @if($complaint->complaint_workflow >= 4)
                                <li class="crm-tab-item">
                                    <a class="crm-tab-link {{ $activeTab == 'closure' ? 'active' : '' }}"
                                        wire:click="switchTab('closure')" href="#complaint-closure" role="tab">
                                        <i class="mdi mdi-email-fast-outline"></i> Client Comms
                                    </a>
                                </li>
                            @endif
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'workflow' ? 'active' : '' }}"
                                    wire:click="switchTab('workflow')" href="#complaint-workflow" role="tab">
                                    <i class="mdi mdi-ray-start-arrow"></i> Chain of Custody
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="complaint-tabs-content">
                        {{-- Tab content from tabs section only. Workflow modal handler rendered below. --}}
                        @if($activeTab == 'details')
                            <div class="tab-pane fade show active p-3" id="complaint-details" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintDetailsTab::class, ['complaint' => $complaint], 'details-' . $complaint->id)
                            </div>
                        @elseif($activeTab == 'attachments')
                            <div class="tab-pane fade show active p-3" id="complaint-attachments" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintAttachmentsTab::class, ['complaintId' => $complaint->id], 'attachments-' . $complaint->id)
                            </div>
                        @elseif($activeTab == 'investigation')
                            <div class="tab-pane fade show active p-3" id="complaint-investigation" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintInvestigationTab::class, ['complaintId' => $complaint->id], 'investigation-' . $complaint->id)
                            </div>
                        @elseif($activeTab == 'capa')
                            <div class="tab-pane fade show active p-3" id="complaint-capa" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintCapaTab::class, ['complaintId' => $complaint->id], 'capa-' . $complaint->id)
                            </div>
                        @elseif($activeTab == 'closure')
                            <div class="tab-pane fade show active p-3" id="complaint-closure" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintClosureTab::class, ['complaintId' => $complaint->id], 'closure-' . $complaint->id)
                            </div>
                        @elseif($activeTab == 'workflow')
                            @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintWorkflowTab::class, ['complaintId' => $complaint->id, 'viewMode' => 'tab'], 'workflow-tab-' . $complaint->id)
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Always load workflow component for event handling, outside tab structure --}}
        @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintWorkflowTab::class, ['complaintId' => $complaint->id, 'viewMode' => 'modal'], 'workflow-handler-' . $complaint->id)
    </main>
</div>