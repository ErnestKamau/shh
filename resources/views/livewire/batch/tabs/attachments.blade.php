<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-paperclip"></i> Attachments</h5>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;" wire:ignore>
                <button type="button" class="btn btn-outline-primary btn-sm btn-action-sm" id="merge-attachments-btn"
                    disabled>
                    <i class="mdi mdi-file-document-box-multiple"></i> Merge selected
                    <span class="badge badge-primary ml-1" id="merge-count">0</span>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm btn-action-sm" data-target="#add-attachment-batch" data-toggle="modal">
                    <i class="mdi mdi-plus"></i> Add
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                    placeholder="Search attachments by filename, type, or uploader...">
            </div>

            <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-4" role="tablist" style="border-radius: 10px; background: #f8f9fa; padding: 5px;">
                <li class="nav-item">
                    <a class="nav-link active font-weight-bold" data-toggle="tab" href="#request-attachments" role="tab" style="border-radius: 8px;">
                        <i class="mdi mdi-file-document-box text-primary mr-1"></i> Request Attachments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" data-toggle="tab" href="#sample-attachments" role="tab" style="border-radius: 8px;">
                        <i class="mdi mdi-test-tube text-info mr-1"></i> Sample Attachments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" data-toggle="tab" href="#reports" role="tab" style="border-radius: 8px;">
                        <i class="mdi mdi-file-chart text-success mr-1"></i> Reports
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="attachments-container">
                <!-- ============================================== -->
                <!-- 1. REQUEST ATTACHMENTS TAB -->
                <!-- ============================================== -->
                <div class="tab-pane active" id="request-attachments" role="tabpanel">
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3 small" style="letter-spacing: 0.5px;">Workflow Documents</h6>
                        <div class="row">
                            <!-- Acceptance Form -->
                            @if($acceptanceForm)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff; border: 1px solid #e0e6ed !important;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="mr-3 text-success bg-success-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="mdi mdi-check-decagram mdi-24px"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 font-weight-bold" style="font-size: 14px;">Acceptance Form</h6>
                                            <small class="text-muted">{{ $acceptanceForm->submitted_at ? $acceptanceForm->submitted_at->format('Y-m-d H:i') : 'Completed' }}</small>
                                        </div>
                                        <div>
                                            <a href="{{ $acceptanceForm->attachment_url }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-none border" title="View PDF">
                                                <i class="mdi mdi-eye text-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Rejection Form -->
                            @if($rejectionForm)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff; border: 1px solid #e0e6ed !important;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="mr-3 text-danger bg-danger-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="mdi mdi-close-octagon mdi-24px"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 font-weight-bold" style="font-size: 14px;">Rejection Form</h6>
                                            <small class="text-muted">{{ $rejectionForm->submitted_at ? $rejectionForm->submitted_at->format('Y-m-d H:i') : 'Completed' }}</small>
                                        </div>
                                        <div>
                                            <a href="{{ $rejectionForm->attachment_url }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-none border" title="View PDF">
                                                <i class="mdi mdi-eye text-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Receipt Notification -->
                            @if($receiptNotification)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff; border: 1px solid #e0e6ed !important;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="mr-3 text-info bg-info-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="mdi mdi-receipt mdi-24px"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 font-weight-bold" style="font-size: 14px;">Receipt Notification</h6>
                                            <small class="text-muted">{{ $receiptNotification->created_at->format('Y-m-d H:i') }}</small>
                                        </div>
                                        <div>
                                            <a href="{{ $receiptNotification->attachment_url }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-none border" title="View Document">
                                                <i class="mdi mdi-eye text-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Invoice -->
                            @if($batch->invoice_id)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff; border: 1px solid #e0e6ed !important;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="mr-3 text-primary bg-primary-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="mdi mdi-file-document mdi-24px"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 font-weight-bold" style="font-size: 14px;">Invoice</h6>
                                            <small class="text-muted">ID: {{ $batch->invoice_id }}</small>
                                        </div>
                                        <div>
                                            <a href="{{ route('print-invoice', $batch->invoice_id) }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-none border" title="View Invoice">
                                                <i class="mdi mdi-eye text-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(!$acceptanceForm && !$rejectionForm && !$receiptNotification && !$batch->invoice_id)
                        <div class="alert alert-light border text-center py-4" style="border-radius: 10px;">
                            <i class="mdi mdi-file-hidden text-muted" style="font-size: 24px;"></i>
                            <p class="mb-0 mt-2 text-muted small">No workflow documents generated yet.</p>
                        </div>
                        @endif
                    </div>

                    <div>
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3 small" style="letter-spacing: 0.5px;">Customer & SRO Attachments (Portal)</h6>
                        <div class="table-responsive" style="border-radius: 10px; border: 1px solid #e0e6ed;">
                            <table class="table table-hover workflow-table mb-0">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th>File Name</th>
                                        <th>Uploaded Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($customerAttachments && $customerAttachments->count() > 0)
                                        @foreach($customerAttachments as $ca)
                                        <tr>
                                            <td class="align-middle font-weight-bold">{{ $ca->file_name ?? 'Attachment' }}</td>
                                            <td class="align-middle text-muted">{{ $ca->created_at ? $ca->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                            <td class="text-center align-middle">
                                                <a href="{{ $ca->file_url ?? '#' }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                                                    <i class="mdi mdi-download text-primary mr-1"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center py-4">
                                                <i class="mdi mdi-folder-open text-muted" style="font-size: 32px;"></i>
                                                <p class="mt-2 mb-0 text-muted small">No customer attachments found from the portal.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 2. SAMPLE ATTACHMENTS TAB -->
                <!-- ============================================== -->
                <div class="tab-pane" id="sample-attachments" role="tabpanel">
                    <div class="table-responsive" style="border-radius: 10px; border: 1px solid #e0e6ed;">
                        <table class="table table-hover workflow-table mb-0" id="attachments-table">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="check-all-attachments">
                                    </th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Upload Date</th>
                                    <th>Uploaded By</th>
                                    <th class="text-center">File</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($sampleAttachments->count() > 0)
                                    @foreach($sampleAttachments as $a)
                                        @if(Auth::user()->is_client == 0 || $a->is_internal == 0)
                                        <tr>
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="attachment-checkbox" value="{{$a->id}}" data-title="{{$a->title ?? 'N/a'}}" data-type="{{$a->attachtypename ?? 'General'}}">
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-light border">{{ $a->attachtypename}}</span>
                                            </td>
                                            <td class="align-middle font-weight-bold">{{$a->title ?? 'N/a'}}</td>
                                            <td class="align-middle text-muted">{{date('Y-m-d', strtotime($a->created_at))}}</td>
                                            <td class="align-middle">{{$a->uploaduser}}</td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group">
                                                    <a href="{{$a->attachment_url}}" target="_blank" data-toggle="tooltip" title="View Attachment" class="btn btn-sm btn-light border">
                                                        <i class="mdi mdi-eye text-dark"></i>
                                                    </a>
                                                    @if(Auth::user()->is_client == 0)
                                                    <a href="{{ route('download-attachment', $a->id) }}" data-toggle="tooltip" title="Download" class="btn btn-sm btn-light border">
                                                        <i class="mdi mdi-download text-primary"></i>
                                                    </a>
                                                    @endif
                                                    @if(strtolower($a->file_type) == 'pdf')
                                                    <a href="{{ route('show-pdf-annotation-page', $a->id) }}" class="btn btn-sm btn-light border" data-toggle="tooltip" title="Comment on PDF">
                                                        <i class="mdi mdi-comment-text text-info"></i>
                                                        @if($a->annotations && $a->annotations->count() > 0)
                                                        <span class="badge badge-primary ml-1">{{ $a->annotations->count() }}</span>
                                                        @endif
                                                    </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" wire:click="deleteAttachment({{$a->id}})" wire:confirm="Are you sure you want to delete attachment: {{$a->title}}?" class="btn btn-sm btn-light border text-danger" data-toggle="tooltip" title="Delete">
                                                    <i class="mdi mdi-delete-empty"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="mdi mdi-camera-burst text-muted" style="font-size: 48px;"></i>
                                        <h6 class="mt-3 text-muted">No Sample Attachments</h6>
                                        <p class="text-muted mb-0"><small>Sample photos and other generic attachments will appear here.</small></p>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 3. REPORTS TAB -->
                <!-- ============================================== -->
                <div class="tab-pane" id="reports" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted font-weight-bold mb-3 small" style="letter-spacing: 0.5px;">Generate Reports</h6>
                            <div class="d-flex gap-2" style="gap: 10px;">
                                <button wire:click="openGclaLanguageModal" class="btn btn-outline-success border-2 shadow-sm rounded-pill px-4 font-weight-bold">
                                    <i class="mdi mdi-certificate mr-1"></i> Generate GCLA 02 Form
                                </button>
                                <a href="#" onclick="alert('Analysis Report generation route to be implemented')" class="btn btn-outline-primary border-2 shadow-sm rounded-pill px-4 font-weight-bold">
                                    <i class="mdi mdi-chart-box mr-1"></i> Generate Analysis Report
                                </a>
                                <button wire:click="openCaseFileModal" class="btn btn-outline-dark border-2 shadow-sm rounded-pill px-4 font-weight-bold">
                                    <i class="mdi mdi-folder-account mr-1"></i> Generate Case File
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3 small" style="letter-spacing: 0.5px;">Uploaded Reports</h6>
                        <div class="table-responsive" style="border-radius: 10px; border: 1px solid #e0e6ed;">
                            <table class="table table-hover workflow-table mb-0">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th>Report Title</th>
                                        <th>Type</th>
                                        <th>Upload Date</th>
                                        <th>Uploaded By</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($reportAttachments->count() > 0)
                                        @foreach($reportAttachments as $ra)
                                        <tr>
                                            <td class="align-middle font-weight-bold text-success">{{ $ra->title }}</td>
                                            <td class="align-middle"><span class="badge badge-light border">{{ $ra->attachtypename }}</span></td>
                                            <td class="align-middle text-muted">{{ $ra->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="align-middle">{{ $ra->uploaduser }}</td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group">
                                                    @if(isset($ra->is_mock) && $ra->is_mock)
                                                    <a href="{{ $ra->attachment_url }}" target="_blank" class="btn btn-sm btn-light shadow-sm" title="View PDF">
                                                        <i class="mdi mdi-eye text-primary"></i> View
                                                    </a>
                                                    @else
                                                    <a href="{{ url($ra->attachment_url) }}" target="_blank" class="btn btn-sm btn-light shadow-sm" title="View PDF">
                                                        <i class="mdi mdi-eye text-primary"></i> View
                                                    </a>
                                                    @endif
                                                    <button type="button" wire:click="deleteAttachment({{$ra->id}})" wire:confirm="Are you sure you want to delete this report?" class="btn btn-sm btn-light border text-danger" title="Delete">
                                                        <i class="mdi mdi-delete-empty"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="mdi mdi-file-document-box-multiple-outline text-muted" style="font-size: 40px;"></i>
                                                <p class="mt-2 mb-0 text-muted small">No saved reports found.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge Attachments Modal -->
    <div class="modal fade" id="merge-attachments-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('merge-attachments')}}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Merge Attachments</h5>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info p-2 mb-3">
                            <i class="mdi mdi-information"></i> Drag and drop items to reorder the report sections.
                        </div>

                        <div class="form-group mb-3">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" required
                                placeholder="Merged Report Title" style="border-radius: 8px;">
                        </div>

                        <div class="form-group mb-4">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Attachment
                                Type</label>
                            <select name="attachment_type" class="form-control form-control-lg" required
                                style="border-radius: 8px;">
                                <option value="">Choose Attachment Type ...</option>
                                @foreach($attachmentTypes as $aType)
                                <option value="{{$aType->id}}">{{$aType->value}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Selected Files
                                (Order Matters)</label>
                            <ul id="sortable-attachments" class="list-group">
                                <!-- Populated by JS -->
                            </ul>
                        </div>
                        <input type="hidden" name="attachment_ids" id="ordered-attachment-ids">
                        <input type="hidden" name="batch_id" value="{{$batch->id}}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-check"></i> Merge &
                            Save</button>
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Attachment Modal -->
    <div class="modal fade" id="add-attachment-batch" role="dialog">
        <div class="modal-dialog modal-lg">
            <form id="add-attachment-form" action="{{ route('add_batch_attachment') }}" method="post" enctype="multipart/form-data" class="modal-content shadow-sm" style="border-radius: 20px; border: none; background: #fafbfc;">
                @csrf
                <div class="modal-header" style="border-bottom: 1.5px solid #e7eaf0; background: #f3f4f7; border-radius: 20px 20px 0 0;">
                    <div class="w-100 d-flex flex-column justify-content-center align-items-start py-2">
                        <span class="font-weight-bold" style="font-size: 1.55rem; color: #23272b;">Add Attachment</span>
                        <span class="badge badge-secondary mt-1 px-2 py-1" style="font-size: 1em; background: #e5e9ef; color: #343a40; border-radius: 6px; letter-spacing: 0.04em;">
                            {{$batch->batch_code}}
                        </span>
                    </div>
                </div>
                <div class="modal-body px-5 py-4" style="background:#fff; border-radius: 0 0 0 0;">
                    <div class="form-group mb-4">
                        <label class="control-label font-weight-bold text-muted text-uppercase small" for="attachTitle">Title</label>
                        <input type="text" name="title" id="attachTitle" class="form-control form-control-lg"
                            placeholder="e.g. Lab Report, Invoice..." required
                            style="border-radius: 10px; background-color: #f4f7fa; border: 1.5px solid #e0e6ed; box-shadow: none;">
                    </div>
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="control-label font-weight-bold text-muted text-uppercase small mb-2" for="attachment_type_select">
                                Attachment Type
                            </label>
                            <span class="btn btn-xs btn-info mb-2 d-flex align-items-center gap-1"
                                style="cursor: pointer; padding: 3px 10px; font-size: 11px; border-radius: 5px; box-shadow: none;"
                                data-toggle="modal" data-target="#add-attachment-type-modal"
                                title="Add New Attachment Type">
                                <i class="mdi mdi-plus" style="font-size: 13px;"></i> ADD NEW
                            </span>
                        </div>
                        <select name="attachment_type" id="attachment_type_select" class="form-control form-control-lg"
                            style="border-radius: 10px;"
                            onchange="handleAttachmentTypeChange(this)">
                            <option value="">Choose Attachment Type ...</option>
                            @foreach($attachmentTypes as $aType)
                            <option value="{{ $aType->id }}">{{ $aType->value }}</option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Shown client-side when selected type is "Result Report" --}}
                    <div class="form-group mb-4" id="samples-with-results-section" style="display:none;">
                        <label class="control-label font-weight-bold text-muted text-uppercase small mb-2">
                            Link Captured Results
                            <span class="text-muted font-weight-normal text-lowercase" style="font-size:11px;">
                                — select the analyte results this report covers
                            </span>
                        </label>

                        @if($samplesWithResults->isEmpty())
                        <div class="alert alert-info py-2 px-3 mb-0" style="border-radius:8px; font-size:13px;">
                            <i class="mdi mdi-information-outline mr-1"></i>
                            No captured results found for this batch yet.
                        </div>
                        @else
                        <div class="accordion" id="samplesResultsAccordion"
                            style="border-radius:8px; overflow:hidden; border:1.5px solid #e0e6ed;">
                            @foreach($samplesWithResults as $sIdx => $sample)
                            @php $collapseId = 'sampleCollapse' . $sIdx; @endphp
                            <div class="card mb-0" style="border:none; border-bottom:1px solid #e0e6ed;">
                                {{-- Sample header row --}}
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                                    style="background:#f8f9fb; cursor:pointer;"
                                    data-toggle="collapse"
                                    data-target="#{{ $collapseId }}"
                                    aria-expanded="{{ $sIdx === 0 ? 'true' : 'false' }}"
                                    aria-controls="{{ $collapseId }}">
                                    <span class="font-weight-bold" style="font-size:13px;">
                                        <i class="mdi mdi-test-tube mr-1 text-primary"></i>
                                        {{ $sample['sample_code'] }}
                                    </span>
                                    <small class="text-muted">
                                        {{ count($sample['results_by_analyte']) }} analyte(s)
                                    </small>
                                </div>

                                <div id="{{ $collapseId }}"
                                    class="collapse {{ $sIdx === 0 ? 'show' : '' }}"
                                    data-parent="#samplesResultsAccordion">
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0"
                                            style="font-size:12.5px;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width:36px;">
                                                        {{-- select-all for this sample --}}
                                                        <input type="checkbox"
                                                            class="sample-select-all"
                                                            data-sample="{{ $sample['id'] }}"
                                                            title="Select all analytes for this sample">
                                                    </th>
                                                    <th>Analyte</th>
                                                    <th>Results</th>
                                                    <th>Linked?</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sample['results_by_analyte'] as $analyte)
                                                @php
                                                $allLinked = collect($analyte['results'])
                                                ->every(fn($r) => !empty($r['batch_attachment_id']));
                                                $someLinked = collect($analyte['results'])
                                                ->contains(fn($r) => !empty($r['batch_attachment_id']));
                                                @endphp
                                                <tr>
                                                    <td class="align-middle">
                                                        <input type="checkbox"
                                                            class="captured-result-checkbox"
                                                            data-sample="{{ $sample['id'] }}"
                                                            data-ids="{{ implode(',', $analyte['captured_result_ids']) }}"
                                                            data-has-linked="{{ $someLinked ? 1 : 0 }}"
                                                            onchange="syncCapturedResultIds()"
                                                            title="{{ $someLinked ? 'Selecting this will replace existing linked attachment(s).' : 'Select analyte results to link.' }}">
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="font-weight-bold">{{ $analyte['analyte_code'] }}</span>
                                                        <span class="text-muted ml-1">{{ $analyte['analyte_name'] }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        @foreach($analyte['results'] as $r)
                                                        @php
                                                            $isNoAttachment = in_array(
                                                                strtolower((string) $r['result']),
                                                                ['no attachment', 'has attachment']
                                                            );
                                                            $isAttached = strtolower((string) $r['result']) === 'as attached'
                                                                || !empty($r['batch_attachment_id']);
                                                        @endphp
                                                        <span
                                                            class="badge {{ $isAttached ? 'badge-success' : ($isNoAttachment ? 'badge-danger' : 'badge-light border') }}"
                                                            style="font-size:11px;"
                                                        >
                                                            {{ $r['result'] }}
                                                        </span>
                                                        @endforeach
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($allLinked)
                                                        <span class="badge badge-warning" title="All results already linked to another attachment">Linked</span>
                                                        @elseif($someLinked)
                                                        <span class="badge badge-secondary" title="Some results linked">Partial</span>
                                                        @else
                                                        <span class="text-muted" style="font-size:11px;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Hidden input carrying the comma-separated captured_result IDs to the controller --}}
                        <input type="hidden" name="selected_captured_result_ids" id="selected_captured_result_ids" value="">
                    </div>


                    <div class="form-group mb-4">
                        <label class="control-label font-weight-bold text-muted text-uppercase small" for="customFile">Upload File</label>
                        <div class="custom-file" style="border-radius: 10px;">
                            <input type="file" class="custom-file-input" id="customFile" name="attachment" required>
                            <label class="custom-file-label" for="customFile" style="border-radius: 10px; background-color: #f9fafb; border: 1.5px solid #e0e6ed; color: #6c757d;">
                                Choose file...
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox pl-1">
                            <input type="checkbox" class="custom-control-input" id="internalUse" name="is_internal">
                            <label class="custom-control-label font-weight-bold text-muted small" for="internalUse" style="margin-left: 3px;">
                                For Internal Use Only
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox pl-1">
                            <input type="checkbox" class="custom-control-input" id="includeInCoa" name="show_on_coa">
                            <label class="custom-control-label font-weight-bold text-muted small" for="includeInCoa" style="margin-left: 3px;">
                                Include in COA (append this file after the report)
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="batch_id" value="{{$batch->id}}">
                </div>
                <div class="modal-footer" style="border-top: 1.5px solid #e7eaf0; background: #f3f4f7; border-radius: 0 0 20px 20px;">
                    <button type="submit" class="btn btn-success btn-sm px-4 shadow-none" style="border-radius: 6px;">
                        <i class="mdi mdi-thumb-up"></i> Save
                    </button>
                    <button type="button" class="btn btn-light btn-sm px-4 shadow-none" data-dismiss="modal" style="border-radius: 6px; border: 1.5px solid #e0e6ed;">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Attachment Type Modal -->
    <div class="modal fade" id="add-attachment-type-modal" role="dialog" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold">New Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text"
                            class="form-control"
                            id="new_attachment_type_name"
                            placeholder="Type Name..."
                            style="border-radius: 8px; background-color: #f8f9fa; border: 1px solid #e9ecef;"
                            wire:model.defer="newAttachmentTypeName">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3"
                        data-dismiss="modal">Cancel</button>
                    <button type="button"
                        class="btn btn-primary btn-sm rounded-pill px-4"
                        wire:click.prevent="saveAttachmentType">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>


    <style>
        #sortable-attachments {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        #sortable-attachments li {
            cursor: move;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
        }

        #sortable-attachments li:hover {
            background-color: #f8f9fa;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        /**
         * Called on every change of the attachment type select inside the Add Attachment modal.
         * Shows/hides the "Link Captured Results" section purely client-side
         * by checking if the selected option's text is exactly "Result Report".
         */
        function handleAttachmentTypeChange(selectEl) {
            var section = document.getElementById('samples-with-results-section');
            if (!section) return;

            var selectedText = selectEl.options[selectEl.selectedIndex] ?
                selectEl.options[selectEl.selectedIndex].text.trim() :
                '';

            var isResultReport = selectedText.toLowerCase() === 'result report';
            section.style.display = isResultReport ? '' : 'none';

            // Auto-check "Include in COA" when type is Result Report
            var coaCheckbox = document.getElementById('includeInCoa');
            if (coaCheckbox) {
                coaCheckbox.checked = isResultReport;
            }

            // Clear selections when switching away
            if (!isResultReport) {
                resetCapturedResultSelections();
            }
        }

        /**
         * Gathers all data-ids from checked .captured-result-checkbox rows
         * and writes a deduplicated comma-separated list into the hidden input.
         */
        function syncCapturedResultIds() {
            var allIds = [];
            document.querySelectorAll('.captured-result-checkbox:checked').forEach(function(cb) {
                var ids = cb.getAttribute('data-ids');
                if (ids) {
                    ids.split(',').forEach(function(id) {
                        id = id.trim();
                        if (id && allIds.indexOf(id) === -1) allIds.push(id);
                    });
                }
            });
            var hidden = document.getElementById('selected_captured_result_ids');
            if (hidden) hidden.value = allIds.join(',');
        }

        function resetCapturedResultSelections() {
            document.querySelectorAll('.captured-result-checkbox, .sample-select-all').forEach(function(cb) {
                cb.checked = false;
            });
            var hidden = document.getElementById('selected_captured_result_ids');
            if (hidden) hidden.value = '';
        }

        // "Select all analytes" checkbox per sample row
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('sample-select-all')) {
                var sampleId = e.target.getAttribute('data-sample');
                document.querySelectorAll('.captured-result-checkbox[data-sample="' + sampleId + '"]')
                    .forEach(function(cb) {
                        cb.checked = e.target.checked;
                    });
                syncCapturedResultIds();
            }
        });

        // Reset modal on close
        (function() {
            var modal = document.getElementById('add-attachment-batch');
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function() {
                    var section = document.getElementById('samples-with-results-section');
                    if (section) section.style.display = 'none';
                    var sel = document.getElementById('attachment_type_select');
                    if (sel) sel.value = '';
                    resetCapturedResultSelections();
                });
            }
        })();

        (function() {
            'use strict';

            function updateMergeButtonState() {
                var checkboxes = document.querySelectorAll('.attachment-checkbox:checked');
                var count = checkboxes.length;
                var mergeBtn = document.getElementById('merge-attachments-btn');
                var mergeCount = document.getElementById('merge-count');

                if (mergeCount) {
                    mergeCount.textContent = count;
                }

                if (mergeBtn) {
                    if (count >= 2) {
                        mergeBtn.disabled = false;
                        mergeBtn.classList.remove('disabled');
                    } else {
                        mergeBtn.disabled = true;
                        mergeBtn.classList.add('disabled');
                    }
                }
            }

            function initializeHandlers() {
                // Use event delegation on the container (which persists through Livewire updates)
                var container = document.getElementById('attachments-container');
                if (!container) {
                    // Fallback to document if container not found
                    container = document;
                }

                // Remove old listener and add new one
                container.removeEventListener('change', handleCheckboxChange);
                container.addEventListener('change', handleCheckboxChange);

                // Check all handler
                var checkAll = document.getElementById('check-all-attachments');
                if (checkAll) {
                    checkAll.removeEventListener('change', handleCheckAllChange);
                    checkAll.addEventListener('change', handleCheckAllChange);
                }

                // Merge button handler
                var mergeBtn = document.getElementById('merge-attachments-btn');
                if (mergeBtn) {
                    mergeBtn.removeEventListener('click', handleMergeClick);
                    mergeBtn.addEventListener('click', handleMergeClick);
                }

                // Initial state
                updateMergeButtonState();
            }

            function handleCheckboxChange(e) {
                if (e.target.classList.contains('attachment-checkbox')) {
                    updateMergeButtonState();

                    // Update check-all state
                    var table = document.getElementById('attachments-table');
                    if (table) {
                        var allCheckboxes = table.querySelectorAll('.attachment-checkbox');
                        var checkedCheckboxes = table.querySelectorAll('.attachment-checkbox:checked');
                        var checkAll = document.getElementById('check-all-attachments');
                        if (checkAll && allCheckboxes.length > 0) {
                            checkAll.checked = checkedCheckboxes.length === allCheckboxes.length;
                        }
                    }
                }
            }

            function handleCheckAllChange(e) {
                var table = document.getElementById('attachments-table');
                if (table) {
                    var isChecked = e.target.checked;
                    table.querySelectorAll('.attachment-checkbox').forEach(function(cb) {
                        cb.checked = isChecked;
                    });
                    updateMergeButtonState();
                }
            }

            function handleMergeClick(e) {
                e.preventDefault();

                var table = document.getElementById('attachments-table');
                if (!table) return;

                var checkedBoxes = table.querySelectorAll('.attachment-checkbox:checked');
                if (checkedBoxes.length < 2) {
                    alert('Please select at least 2 attachments to merge.');
                    return;
                }

                // Populate modal
                var sortableList = document.getElementById('sortable-attachments');
                if (sortableList) {
                    sortableList.innerHTML = '';

                    checkedBoxes.forEach(function(cb) {
                        var id = cb.value;
                        var title = cb.getAttribute('data-title') || 'N/a';
                        var type = cb.getAttribute('data-type') || 'General';

                        var li = document.createElement('li');
                        li.className = 'list-group-item p-2 mb-1 d-flex justify-content-between align-items-center';
                        li.setAttribute('data-id', id);
                        li.innerHTML = '<span><i class="mdi mdi-drag-vertical mr-2 text-muted"></i> ' + title + ' <small class="text-muted">(' + type + ')</small></span>';
                        sortableList.appendChild(li);
                    });

                    // Show modal (using jQuery/bootstrap)
                    if (window.jQuery && window.jQuery.fn.modal) {
                        window.jQuery('#merge-attachments-modal').modal('show');
                    }

                    // Initialize SortableJS
                    if (window.Sortable) {
                        // Destroy existing instance
                        if (sortableList.sortableInstance) {
                            sortableList.sortableInstance.destroy();
                        }

                        sortableList.sortableInstance = window.Sortable.create(sortableList, {
                            animation: 150,
                            onEnd: function() {
                                var ids = [];
                                sortableList.querySelectorAll('li').forEach(function(li) {
                                    ids.push(li.getAttribute('data-id'));
                                });
                                var hiddenInput = document.getElementById('ordered-attachment-ids');
                                if (hiddenInput) {
                                    hiddenInput.value = ids.join(',');
                                }
                            }
                        });

                        // Initial update
                        var ids = [];
                        sortableList.querySelectorAll('li').forEach(function(li) {
                            ids.push(li.getAttribute('data-id'));
                        });
                        var hiddenInput = document.getElementById('ordered-attachment-ids');
                        if (hiddenInput) {
                            hiddenInput.value = ids.join(',');
                        }
                    }
                }
            }


            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeHandlers);
            } else {
                initializeHandlers();
            }

            // Re-initialize after Livewire updates
            if (typeof Livewire !== 'undefined') {
                document.addEventListener('livewire:load', initializeHandlers);

                Livewire.hook('message.processed', function() {
                    setTimeout(initializeHandlers, 50);
                });
            }

            // Also listen for Livewire component updates
            document.addEventListener('livewire:update', function() {
                setTimeout(initializeHandlers, 50);
            });
        })();

        // Form Submit handler and file input handler (jQuery)
        (function($) {
            // Vanilla JS fallback for file input labels
            document.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('custom-file-input')) {
                    var fileName = e.target.value.split("\\").pop();
                    var label = e.target.nextElementSibling;
                    if (label && label.classList.contains('custom-file-label')) {
                        label.classList.add("selected");
                        label.innerHTML = fileName;
                    }
                }
            });

            if ($) {
                $(document).on('submit', '#merge-attachments-modal form', function() {
                    var ids = [];
                    $('#sortable-attachments li').each(function() {
                        ids.push($(this).data('id'));
                    });
                    $('#ordered-attachment-ids').val(ids.join(','));
                });

                $(document).on('change', '.custom-file-input', function() {
                    var fileName = $(this).val().split("\\").pop();
                    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
                });
            }
        })(window.jQuery);

        // Confirm when replacing already linked captured results
        document.addEventListener('submit', function(e) {
            if (!e.target || e.target.id !== 'add-attachment-form') {
                return;
            }

            var linkedSelectedCount = 0;
            document.querySelectorAll('.captured-result-checkbox:checked').forEach(function(cb) {
                if (cb.getAttribute('data-has-linked') === '1') {
                    linkedSelectedCount++;
                }
            });

            if (linkedSelectedCount > 0) {
                var ok = window.confirm(
                    'Some selected results are already linked to another attachment. Continue and replace those links?'
                );
                if (!ok) {
                    e.preventDefault();
                }
            }
        });
    </script>

    <!-- ========================================== -->
    <!-- GCLA 02 Form Language Selection Modal -->
    <!-- ========================================== -->
    <div class="modal fade {{ $showGclaLanguageModal ? 'show' : '' }}" tabindex="-1" role="dialog" style="display: {{ $showGclaLanguageModal ? 'block' : 'none' }}; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title font-weight-bold">
                        <i class="mdi mdi-translate mr-2"></i> Choose Report Language
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeGclaLanguageModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="mb-4 text-muted">Select the language for the GCLA 02 Form (Certificate of Analysis).</p>
                    <div class="d-flex justify-content-center gap-3" style="gap: 15px;">
                        <button type="button" wire:click="generateGCLA02Form('en')" class="btn btn-outline-primary px-4 py-2 font-weight-bold" style="border-radius: 8px;">
                            <i class="mdi mdi-earth"></i> English
                        </button>
                        <button type="button" wire:click="generateGCLA02Form('sw')" class="btn btn-outline-success px-4 py-2 font-weight-bold" style="border-radius: 8px;">
                            <i class="mdi mdi-earth"></i> Kiswahili
                        </button>
                    </div>
                    <div wire:loading wire:target="generateGCLA02Form" class="mt-3 text-success">
                        <i class="mdi mdi-loading mdi-spin mr-1"></i> Generating PDF...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>