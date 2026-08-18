<div>
    <style>
        .batch-attachments-panel .workflow-board-panel-body.flush-top {
            padding: 0.85rem 1.25rem 1.25rem !important;
        }

        .batch-attachments-panel .workflow-doc-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-left: 0.25rem;
        }

        .batch-attachments-panel .workflow-doc-card-wrap {
            flex: 1 1 220px;
            max-width: 280px;
            min-width: 0;
        }

        .batch-attachments-panel .workflow-doc-card {
            overflow: hidden;
            height: 100%;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #e0e6ed !important;
        }

        .batch-attachments-panel .workflow-doc-card .card-body {
            min-width: 0;
            gap: 0.35rem;
            overflow: hidden;
        }

        .batch-attachments-panel .workflow-doc-card .workflow-doc-meta {
            min-width: 0;
            overflow: hidden;
        }

        .batch-attachments-panel .workflow-doc-card .workflow-doc-actions {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
    </style>
    <div class="workflow-board-panel batch-attachments-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-paperclip"></i> Attachments</h5>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;" wire:ignore>
                <button type="button" class="btn btn-outline-primary btn-sm btn-action-sm" id="merge-attachments-btn"
                    disabled>
                    <i class="mdi mdi-file-document-box-multiple"></i> Merge selected
                    <span class="badge badge-primary ml-1" id="merge-count">0</span>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm btn-action-sm" id="open-add-attachment-modal" data-target="#add-attachment-batch">
                    <i class="mdi mdi-plus"></i> Add
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif
            @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                    placeholder="Search attachments by filename, type, or uploader...">
            </div>

            <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-4" role="tablist" style="border-radius: 10px; background: #f8f9fa; padding: 5px;">
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $activeAttachmentPane === 'request-attachments' ? 'active' : '' }}"
                       href="#request-attachments" role="tab"
                       wire:click.prevent="setActiveAttachmentPane('request-attachments')"
                       style="border-radius: 8px;">
                        <i class="mdi mdi-file-document-box text-primary mr-1"></i> Request Attachments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $activeAttachmentPane === 'sample-attachments' ? 'active' : '' }}"
                       href="#sample-attachments" role="tab"
                       wire:click.prevent="setActiveAttachmentPane('sample-attachments')"
                       style="border-radius: 8px;">
                        <i class="mdi mdi-test-tube text-info mr-1"></i> Sample Attachments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $activeAttachmentPane === 'reports' ? 'active' : '' }}"
                       href="#reports" role="tab"
                       wire:click.prevent="setActiveAttachmentPane('reports')"
                       style="border-radius: 8px;">
                        <i class="mdi mdi-file-chart text-success mr-1"></i> Reports
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="attachments-container">
                <!-- ============================================== -->
                <!-- 1. REQUEST ATTACHMENTS TAB -->
                <!-- ============================================== -->
                <div class="tab-pane {{ $activeAttachmentPane === 'request-attachments' ? 'active show' : '' }}" id="request-attachments" role="tabpanel">
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3 small" style="letter-spacing: 0.5px;">Workflow Documents</h6>
                        <div class="workflow-doc-cards">
                            <!-- Rejection Form -->
                            @if($rejectionForm)
                            <div class="workflow-doc-card-wrap mb-2">
                                <div class="card border-0 shadow-sm workflow-doc-card">
                                    <div class="card-body py-2 px-2 d-flex align-items-center">
                                        <div class="mr-2 text-danger bg-danger-light rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="mdi mdi-close-octagon mdi-18px"></i>
                                        </div>
                                        <div class="flex-grow-1 workflow-doc-meta">
                                            <h6 class="mb-0 font-weight-bold text-truncate" style="font-size: 12px;">Rejection Form</h6>
                                            <small class="text-muted text-truncate d-block" style="font-size: 10px;">{{ $rejectionForm->submitted_at ? $rejectionForm->submitted_at->format('Y-m-d H:i') : 'Completed' }}</small>
                                        </div>
                                        <div class="workflow-doc-actions">
                                            <a href="{{ $rejectionForm->attachment_url }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-none border" title="View PDF">
                                                <i class="mdi mdi-eye text-dark" style="font-size: 14px;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Quotation -->
                            @if($quotationDocument)
                            <div class="workflow-doc-card-wrap mb-2">
                                <div class="card border-0 shadow-sm workflow-doc-card">
                                    <div class="card-body py-2 px-2 d-flex align-items-center">
                                        <div class="mr-2 text-warning bg-warning-light rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="mdi mdi-file-document-outline mdi-18px"></i>
                                        </div>
                                        <div class="flex-grow-1 workflow-doc-meta">
                                            <h6 class="mb-0 font-weight-bold text-truncate" style="font-size: 12px;">Quotation</h6>
                                            <small class="text-muted text-truncate d-block" style="font-size: 10px;">{{ $quotationDocument->quote_number ?? ($quotationDocument->submitted_at ? $quotationDocument->submitted_at->format('Y-m-d H:i') : 'Linked') }}</small>
                                        </div>
                                        <div class="workflow-doc-actions">
                                            <button wire:click="syncWorkflowDocuments" wire:loading.attr="disabled" class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-none border" title="Refresh linked quotation attachment">
                                                <i wire:loading.remove wire:target="syncWorkflowDocuments" class="mdi mdi-refresh text-primary" style="font-size: 14px;"></i>
                                                <span wire:loading wire:target="syncWorkflowDocuments" class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                                            </button>
                                            <a href="{{ $quotationDocument->attachment_url }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-none border" title="View Quotation">
                                                <i class="mdi mdi-eye text-dark" style="font-size: 14px;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Test Request Form -->
                            @if($testRequestFormDocument)
                            <div class="workflow-doc-card-wrap mb-2">
                                <div class="card border-0 shadow-sm workflow-doc-card">
                                    <div class="card-body py-2 px-2 d-flex align-items-center">
                                        <div class="mr-2 text-secondary bg-light rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="mdi mdi-clipboard-text-outline mdi-18px"></i>
                                        </div>
                                        <div class="flex-grow-1 workflow-doc-meta">
                                            <h6 class="mb-0 font-weight-bold text-truncate" style="font-size: 12px;">Test Request Form</h6>
                                            <small class="text-muted text-truncate d-block" style="font-size: 10px;">{{ $testRequestFormDocument->form_number ?? ($testRequestFormDocument->submitted_at ? $testRequestFormDocument->submitted_at->format('Y-m-d H:i') : 'Linked') }}</small>
                                        </div>
                                        <div class="workflow-doc-actions">
                                            <button wire:click="syncWorkflowDocuments" wire:loading.attr="disabled" class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-none border" title="Refresh linked test request form attachment">
                                                <i wire:loading.remove wire:target="syncWorkflowDocuments" class="mdi mdi-refresh text-info" style="font-size: 14px;"></i>
                                                <span wire:loading wire:target="syncWorkflowDocuments" class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>
                                            </button>
                                            <a href="{{ $testRequestFormDocument->attachment_url }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-none border" title="View Test Request Form">
                                                <i class="mdi mdi-eye text-dark" style="font-size: 14px;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Invoice -->
                            @if($batch->invoice_id)
                            <div class="workflow-doc-card-wrap mb-2">
                                <div class="card border-0 shadow-sm workflow-doc-card">
                                    <div class="card-body py-2 px-2 d-flex align-items-center">
                                        <div class="mr-2 text-primary bg-primary-light rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="mdi mdi-file-document mdi-18px"></i>
                                        </div>
                                        <div class="flex-grow-1 workflow-doc-meta">
                                            <h6 class="mb-0 font-weight-bold text-truncate" style="font-size: 12px;">Invoice</h6>
                                            <small class="text-muted text-truncate d-block" style="font-size: 10px;">ID: {{ $batch->invoice_id }}</small>
                                        </div>
                                        <div class="workflow-doc-actions">
                                            <a href="{{ route('print-invoice', $batch->invoice_id) }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-none border" title="View Invoice">
                                                <i class="mdi mdi-eye text-dark" style="font-size: 14px;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(!$rejectionForm && !$batch->invoice_id && !$quotationDocument && !$testRequestFormDocument)
                        <div class="alert alert-light border text-center py-4" style="border-radius: 10px;">
                            <i class="mdi mdi-file-hidden text-muted" style="font-size: 24px;"></i>
                            <p class="mb-0 mt-2 text-muted small">No workflow documents generated yet.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 2. SAMPLE ATTACHMENTS TAB -->
                <!-- ============================================== -->
                <div class="tab-pane {{ $activeAttachmentPane === 'sample-attachments' ? 'active show' : '' }}" id="sample-attachments" role="tabpanel">
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
                                                @if(Auth::user()->is_client == 0)
                                                <button type="button" wire:click="editAttachment('{{ $a->id }}')" class="btn btn-sm btn-light border text-primary" data-toggle="tooltip" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                @endif
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
                <div class="tab-pane {{ $activeAttachmentPane === 'reports' ? 'active show' : '' }}" id="reports" role="tabpanel">
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
    <div wire:ignore>
    <div class="modal fade" id="add-attachment-batch" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
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
                                id="open-add-attachment-type-btn"
                                title="Add New Attachment Type">
                                <i class="mdi mdi-plus" style="font-size: 13px;"></i> ADD NEW
                            </span>
                        </div>
                        <select name="attachment_type" id="attachment_type_select" class="form-control form-control-lg no-select2"
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
                            <input type="file" class="custom-file-input" id="customFile" name="attachment" accept=".pdf,application/pdf" required>
                            <label class="custom-file-label" for="customFile" style="border-radius: 10px; background-color: #f9fafb; border: 1.5px solid #e0e6ed; color: #6c757d;">
                                Choose file...
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1" id="coa-upload-hint" style="display:none;">
                            Use Browse to add PDFs. Each selection adds to the list (or pick several at once). Drag to reorder before saving.
                        </small>
                    </div>

                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="internalUse" name="is_internal" value="1">
                            <label class="custom-control-label font-weight-bold text-muted small" for="internalUse">
                                For Internal Use Only
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeInCoa" name="show_on_coa" value="1">
                            <label class="custom-control-label font-weight-bold text-muted small" for="includeInCoa">
                                Include in Test Report (merge report first, then your PDF file(s))
                            </label>
                        </div>
                    </div>

                    <div id="coa-merge-section" class="form-group mb-3" style="display:none;">
                        <label class="control-label font-weight-bold text-muted text-uppercase small mb-2">
                            Merge Order
                        </label>
                        <p class="text-muted small mb-2">
                            The <strong>Test Report</strong> is always placed first. Drag the uploaded files below to set their order.
                        </p>
                        <ul id="coa-merge-sortable" class="list-group mb-2">
                            <li class="list-group-item py-2 d-flex align-items-center" data-locked="1">
                                <i class="mdi mdi-lock text-muted mr-2"></i>
                                <span class="font-weight-bold">Test Report</span>
                                <span class="badge badge-light border ml-auto">First</span>
                            </li>
                        </ul>
                        <input type="hidden" name="coa_file_order" id="coa-file-order" value="">
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
    </div>

    @if($showEditAttachmentModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1.5px solid #e7eaf0; background: #f3f4f7; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title font-weight-bold">Edit Attachment</h5>
                    <button type="button" class="close" wire:click="closeEditAttachmentModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="updateAttachment">
                    <div class="modal-body px-4 py-4">
                        <div class="form-group mb-3">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Title</label>
                            <input type="text" class="form-control" wire:model="editAttachmentTitle" placeholder="Attachment title">
                            @error('editAttachmentTitle') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Attachment Type</label>
                            <select class="form-control no-select2" wire:model="editAttachmentTypeId">
                                <option value="">Choose Attachment Type ...</option>
                                @foreach($attachmentTypes as $aType)
                                <option value="{{ $aType->id }}">{{ $aType->value }}</option>
                                @endforeach
                            </select>
                            @error('editAttachmentTypeId') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Replace File</label>
                            <input type="file" class="form-control-file" wire:model="editAttachmentFile" accept="image/*,.pdf,application/pdf">
                            <small class="text-muted d-block mt-1">Leave empty to keep the current file. Images and PDFs are allowed.</small>
                            @error('editAttachmentFile') <small class="text-danger">{{ $message }}</small> @enderror
                            <div wire:loading wire:target="editAttachmentFile" class="text-muted small mt-1">
                                <i class="mdi mdi-loading mdi-spin"></i> Uploading...
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="editAttachmentInternal" wire:model="editAttachmentIsInternal">
                            <label class="custom-control-label font-weight-bold text-muted small" for="editAttachmentInternal">
                                For Internal Use Only
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1.5px solid #e7eaf0; background: #f3f4f7; border-radius: 0 0 16px 16px;">
                        <button type="button" class="btn btn-light btn-sm" wire:click="closeEditAttachmentModal">Close</button>
                        <button type="submit" class="btn btn-success btn-sm" wire:loading.attr="disabled" wire:target="updateAttachment,editAttachmentFile">
                            <span wire:loading.remove wire:target="updateAttachment"><i class="mdi mdi-content-save"></i> Save</span>
                            <span wire:loading wire:target="updateAttachment"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Attachment Type Modal -->
    <div wire:ignore>
    <div class="modal fade" id="add-attachment-type-modal" role="dialog" style="z-index: 1060;" data-livewire-id="{{ $this->getId() }}">
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
                            value="{{ $newAttachmentTypeName }}">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3"
                        data-dismiss="modal">Cancel</button>
                    <button type="button"
                        class="btn btn-primary btn-sm rounded-pill px-4"
                        id="save-attachment-type-btn">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>


    <style>
        #add-attachment-batch .modal-dialog {
            max-height: calc(100vh - 2rem);
        }

        #add-attachment-batch .modal-content {
            max-height: calc(100vh - 2rem);
        }

        #add-attachment-batch .modal-body {
            overflow-y: auto;
        }

        #add-attachment-batch #attachment_type_select {
            position: relative;
            z-index: 1;
        }

        #add-attachment-type-modal {
            z-index: 2060 !important;
        }

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

    <!-- Case File Review Form Modal -->
    @if($showCaseFileModal && $batch->hasDnaLab())
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document" style="max-width: 1000px; width: 95vw;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: #f8fafc;">
                    
                    <style>
                        .cf-modal-header {
                            background: #ffffff;
                            border-bottom: 1px solid #e2e8f0;
                            padding: 16px 24px;
                        }
                        .cf-card {
                            background: #ffffff;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
                            margin-bottom: 20px;
                            overflow: hidden;
                        }
                        .cf-card-header {
                            background: #f8fafc;
                            padding: 14px 20px;
                            border-bottom: 1px solid #e2e8f0;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                        .cf-card-title {
                            font-size: 0.85rem;
                            font-weight: 700;
                            color: #0f172a;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                            margin: 0;
                            display: flex;
                            align-items: center;
                            gap: 6px;
                        }
                        .cf-card-body {
                            padding: 20px;
                        }
                        .cf-input-label {
                            font-size: 0.72rem;
                            font-weight: 700;
                            color: #475569;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                            margin-bottom: 6px;
                            display: block;
                        }
                        .cf-form-control {
                            height: 38px;
                            border-radius: 8px;
                            border: 1px solid #cbd5e1;
                            padding: 8px 12px;
                            font-size: 0.88rem;
                            color: #0f172a;
                            background-color: #ffffff;
                            transition: all 0.2s ease-in-out;
                            width: 100%;
                        }
                        .cf-form-control:focus {
                            border-color: #2563eb;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
                            outline: none;
                        }
                        textarea.cf-form-control {
                            height: auto;
                            min-height: 80px;
                        }
                        .custom-checkbox-modern {
                            display: flex;
                            align-items: center;
                            position: relative;
                            padding-left: 0;
                            margin-bottom: 0;
                        }
                        .custom-checkbox-modern .custom-control-input {
                            position: absolute;
                            opacity: 0;
                            cursor: pointer;
                            height: 0;
                            width: 0;
                        }
                        .custom-checkbox-modern .custom-control-label {
                            position: relative;
                            padding-left: 28px;
                            cursor: pointer;
                            font-size: 0.85rem;
                            font-weight: 600;
                            color: #334155;
                            user-select: none;
                            line-height: 20px;
                            margin-bottom: 0;
                        }
                        .custom-checkbox-modern .custom-control-label::before {
                            content: '';
                            position: absolute;
                            left: 0;
                            top: 0;
                            width: 20px;
                            height: 20px;
                            border: 1px solid #cbd5e1;
                            border-radius: 6px;
                            background-color: #ffffff;
                            transition: all 0.15s ease-in-out;
                        }
                        .custom-checkbox-modern .custom-control-input:checked ~ .custom-control-label::before {
                            background-color: #2563eb;
                            border-color: #2563eb;
                        }
                        .custom-checkbox-modern .custom-control-label::after {
                            content: '';
                            position: absolute;
                            left: 7px;
                            top: 3px;
                            width: 6px;
                            height: 11px;
                            border: solid white;
                            border-width: 0 2px 2px 0;
                            transform: rotate(45deg);
                            opacity: 0;
                            transition: all 0.15s ease-in-out;
                        }
                        .custom-checkbox-modern .custom-control-input:checked ~ .custom-control-label::after {
                            opacity: 1;
                        }
                        .cf-section-subtitle {
                            font-size: 0.78rem;
                            font-weight: 700;
                            color: #64748b;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                            margin-bottom: 12px;
                            border-bottom: 1px dashed #e2e8f0;
                            padding-bottom: 6px;
                        }
                    </style>

                    @php
                        $activeCompany = getActiveCompany();
                    @endphp
                    <div class="cf-modal-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            @if($activeCompany && $activeCompany->logo)
                                <img src="{{ $activeCompany->logo }}" alt="Logo" class="mr-3" style="max-height: 42px; max-width: 140px; object-fit: contain;">
                            @else
                                <div class="mr-3 bg-light d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; border: 1px solid #e2e8f0;">
                                    <i class="mdi mdi-flask-outline text-primary" style="font-size: 20px;"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1.1rem; letter-spacing: -0.01em;">Case File Review Form</h5>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">Batch Reference: {{ $batch->batch_code }} &bull; DNA/F/12</span>
                            </div>
                        </div>
                        <button type="button" class="close shadow-none" wire:click="$set('showCaseFileModal', false)" aria-label="Close" style="font-size: 24px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <p class="text-muted small mb-0">Empty fields can be filled from saved grouped worksheet stages.</p>
                            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold"
                                wire:click="refreshCaseFileFromWorksheets"
                                wire:loading.attr="disabled"
                                wire:target="refreshCaseFileFromWorksheets">
                                <span wire:loading.remove wire:target="refreshCaseFileFromWorksheets">
                                    <i class="mdi mdi-file-import-outline"></i> Fill from worksheets
                                </span>
                                <span wire:loading wire:target="refreshCaseFileFromWorksheets">Loading…</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="saveCaseFile">
                            
                            <!-- 1. SAMPLE INFORMATION -->
                            <div class="cf-card">
                                <div class="cf-card-header" style="border-left: 4px solid #3b82f6;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-information-outline text-primary" style="font-size: 18px;"></i>
                                        1. Sample Information
                                    </h6>
                                </div>
                                <div class="cf-card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">Lab No</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.lab_no" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">File No</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.file_no">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">Date In</label>
                                            <input type="date" class="cf-form-control" wire:model="caseFileForm.date_in">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">Client</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.client">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">No of Samples</label>
                                            <input type="number" class="cf-form-control" wire:model="caseFileForm.no_of_samples">
                                        </div>
                                        <div class="col-md-9 mb-3">
                                            <label class="cf-input-label">Sample Condition</label>
                                            <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                                                <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                    <input type="checkbox" class="custom-control-input" id="cf_sealed_att" wire:model="caseFileForm.sample_condition_sealed">
                                                    <label class="custom-control-label" for="cf_sealed_att">Sealed</label>
                                                </div>
                                                <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                    <input type="checkbox" class="custom-control-input" id="cf_labelled_att" wire:model="caseFileForm.sample_condition_labelled">
                                                    <label class="custom-control-label" for="cf_labelled_att">Labelled</label>
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 250px;">
                                                    <input type="text" class="cf-form-control" placeholder="Condition Remarks..." wire:model="caseFileForm.sample_condition_remark">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. SAMPLE SCREENING -->
                            <div class="cf-card">
                                <div class="cf-card-header" style="border-left: 4px solid #6366f1;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-magnify text-indigo" style="font-size: 18px;"></i>
                                        2. Sample Screening
                                    </h6>
                                </div>
                                <div class="cf-card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="cf-input-label">Screening Date</label>
                                            <input type="date" class="cf-form-control" wire:model="caseFileForm.screening_date">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="cf-input-label">Screening Method</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.screening_method">
                                        </div>
                                    </div>
                                    
                                    <div class="cf-section-subtitle">Sample Types</div>
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                <input type="checkbox" class="custom-control-input" id="sc_blood_att" wire:model="caseFileForm.screening_sample_type_blood">
                                                <label class="custom-control-label" for="sc_blood_att">Blood</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                <input type="checkbox" class="custom-control-input" id="sc_obj_blood_att" wire:model="caseFileForm.screening_sample_type_object_with_blood">
                                                <label class="custom-control-label" for="sc_obj_blood_att">Object w/ Blood</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                <input type="checkbox" class="custom-control-input" id="sc_semen_att" wire:model="caseFileForm.screening_sample_type_semen">
                                                <label class="custom-control-label" for="sc_semen_att">Semen</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                <input type="checkbox" class="custom-control-input" id="sc_obj_semen_att" wire:model="caseFileForm.screening_sample_type_object_with_semen">
                                                <label class="custom-control-label" for="sc_obj_semen_att">Object w/ Semen</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="cf-input-label">Others</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.screening_sample_type_others">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">Result 1</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.screening_results_1" placeholder="Positive/Negative">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cf-input-label">Result 2</label>
                                            <input type="text" class="cf-form-control" wire:model="caseFileForm.screening_results_2" placeholder="Positive/Negative">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. EXTRACTION & QUANTIFICATION -->
                            <div class="cf-card">
                                <div class="cf-card-header" style="border-left: 4px solid #06b6d4;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-test-tube text-cyan" style="font-size: 18px;"></i>
                                        3. Extraction & Quantification
                                    </h6>
                                </div>
                                <div class="cf-card-body">
                                    <div class="row">
                                        <!-- Extraction -->
                                        <div class="col-md-6 mb-3 pr-md-4" style="border-right: 1px solid #e2e8f0;">
                                            <div class="cf-section-subtitle">Extraction</div>
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Extraction Date</label>
                                                <input type="date" class="cf-form-control" wire:model="caseFileForm.extraction_date">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="cf-input-label">Extraction Method</label>
                                                <div class="d-flex flex-column gap-2" style="gap: 8px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="ext_chelex_att" wire:model="caseFileForm.extraction_method_chelex">
                                                        <label class="custom-control-label" for="ext_chelex_att">Chelex Method</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="ext_prepfiler_att" wire:model="caseFileForm.extraction_method_prepfiler">
                                                        <label class="custom-control-label" for="ext_prepfiler_att">Prepfiler Method</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label class="cf-input-label">Other Method</label>
                                                <input type="text" class="cf-form-control" wire:model="caseFileForm.extraction_method_other">
                                            </div>
                                        </div>

                                        <!-- Quantification -->
                                        <div class="col-md-6 mb-3 pl-md-4">
                                            <div class="cf-section-subtitle">Quantification</div>
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Quantification Date</label>
                                                <input type="date" class="cf-form-control" wire:model="caseFileForm.quantification_date">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="cf-input-label">Kit & Cycles</label>
                                                <div class="d-flex flex-column gap-2" style="gap: 8px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="quant_cycles_att" wire:model="caseFileForm.quantification_no_of_cycles_40">
                                                        <label class="custom-control-label" for="quant_cycles_att">No. of Cycles (40)</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="quant_kit_att" wire:model="caseFileForm.quantification_kit_used_quant_trio">
                                                        <label class="custom-control-label" for="quant_kit_att">Kit: Quant Trio</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label class="cf-input-label">Remarks</label>
                                                <input type="text" class="cf-form-control" wire:model="caseFileForm.quantification_remarks">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. PCR AMPLIFICATION & INJECTION -->
                            <div class="cf-card">
                                <div class="cf-card-header" style="border-left: 4px solid #8b5cf6;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-dna text-purple" style="font-size: 18px;"></i>
                                        4. PCR Amplification & Injection
                                    </h6>
                                </div>
                                <div class="cf-card-body">
                                    <div class="row">
                                        <!-- PCR -->
                                        <div class="col-md-6 mb-3 pr-md-4" style="border-right: 1px solid #e2e8f0;">
                                            <div class="cf-section-subtitle">PCR Amplification</div>
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">PCR Amplification Date</label>
                                                <input type="date" class="cf-form-control" wire:model="caseFileForm.pcr_amplification_date">
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">No. of Cycles</label>
                                                <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 15px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="pcr_28_att" wire:model="caseFileForm.pcr_no_of_cycles_28">
                                                        <label class="custom-control-label" for="pcr_28_att">28</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="pcr_29_att" wire:model="caseFileForm.pcr_no_of_cycles_29">
                                                        <label class="custom-control-label" for="pcr_29_att">29</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="pcr_30_att" wire:model="caseFileForm.pcr_no_of_cycles_30">
                                                        <label class="custom-control-label" for="pcr_30_att">30</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="pcr_32_att" wire:model="caseFileForm.pcr_no_of_cycles_32">
                                                        <label class="custom-control-label" for="pcr_32_att">32</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Kit Used</label>
                                                <div class="d-flex flex-column gap-2 mt-2" style="gap: 8px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="kit_identifiler_att" wire:model="caseFileForm.pcr_kit_used_identifiler_plus">
                                                        <label class="custom-control-label" for="kit_identifiler_att">Identifiler Plus</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="kit_globalfiler_att" wire:model="caseFileForm.pcr_kit_used_globalfiler">
                                                        <label class="custom-control-label" for="kit_globalfiler_att">Globalfiler</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="kit_yfiler_att" wire:model="caseFileForm.pcr_kit_used_yfiler_plus">
                                                        <label class="custom-control-label" for="kit_yfiler_att">Yfiler Plus</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label class="cf-input-label">PCR Remarks</label>
                                                <input type="text" class="cf-form-control" wire:model="caseFileForm.pcr_remarks">
                                            </div>
                                        </div>

                                        <!-- Injection -->
                                        <div class="col-md-6 mb-3 pl-md-4">
                                            <div class="cf-section-subtitle">Injection & Interpretation</div>
                                            <div class="row">
                                                <div class="col-6 form-group mb-3">
                                                    <label class="cf-input-label">Injection Date</label>
                                                    <input type="date" class="cf-form-control" wire:model="caseFileForm.injection_date">
                                                </div>
                                                <div class="col-6 form-group mb-3">
                                                    <label class="cf-input-label">Run ID</label>
                                                    <input type="text" class="cf-form-control" wire:model="caseFileForm.injection_run_id">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                    <input type="checkbox" class="custom-control-input" id="inj_inst_3500_att" wire:model="caseFileForm.injection_instrument_3500">
                                                    <label class="custom-control-label font-weight-bold" for="inj_inst_3500_att">Instrument: 3500 Genetic Analyzer</label>
                                                </div>
                                            </div>

                                            <label class="cf-input-label mt-3">Control Results (Conforming / Non-Conforming)</label>
                                            <div class="row mb-3">
                                                <div class="col-6 mb-2">
                                                    <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">+ve Ctrl</label>
                                                    <input type="text" class="cf-form-control form-control-sm" wire:model="caseFileForm.injection_result_positive">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">-ve Ctrl</label>
                                                    <input type="text" class="cf-form-control form-control-sm" wire:model="caseFileForm.injection_result_negative">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">Ladder</label>
                                                    <input type="text" class="cf-form-control form-control-sm" wire:model="caseFileForm.injection_result_ladder">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">Blank</label>
                                                    <input type="text" class="cf-form-control form-control-sm" wire:model="caseFileForm.injection_result_blank">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="cf-input-label">Run Result</label>
                                                <input type="text" class="cf-form-control" wire:model="caseFileForm.injection_run">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. REPORTING & REVIEW -->
                            <div class="cf-card">
                                <div class="cf-card-header" style="border-left: 4px solid #10b981;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-checkbox-marked-circle-outline text-emerald" style="font-size: 18px;"></i>
                                        5. Reporting & Review
                                    </h6>
                                </div>
                                <div class="cf-card-body">
                                    <div class="row">
                                        <!-- Reporting -->
                                        <div class="col-md-6 mb-3 pr-md-4" style="border-right: 1px solid #e2e8f0;">
                                            <div class="cf-section-subtitle">Reporting Details</div>
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Draft Report Date</label>
                                                <input type="date" class="cf-form-control" wire:model="caseFileForm.reporting_draft_report_date">
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Review Status</label>
                                                <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="rep_reviewed_att" wire:model="caseFileForm.reporting_reviewed">
                                                        <label class="custom-control-label" for="rep_reviewed_att">Reviewed</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="rep_corrected_att" wire:model="caseFileForm.reporting_corrected">
                                                        <label class="custom-control-label" for="rep_corrected_att">Corrected</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Attachments</label>
                                                <div class="d-flex flex-column gap-2 mt-2" style="gap: 8px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="att_real_time_att" wire:model="caseFileForm.reporting_attachment_real_time_data">
                                                        <label class="custom-control-label" for="att_real_time_att">Real Time Data</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="att_converge_att" wire:model="caseFileForm.reporting_attachment_converge">
                                                        <label class="custom-control-label" for="att_converge_att">Converge</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="att_stat_att" wire:model="caseFileForm.reporting_attachment_statistical_analysis">
                                                        <label class="custom-control-label" for="att_stat_att">Statistical Analysis</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="cf-input-label">Reporting Remarks</label>
                                                <textarea class="cf-form-control" wire:model="caseFileForm.reporting_remarks" rows="2"></textarea>
                                            </div>
                                        </div>

                                        <!-- Manager -->
                                        <div class="col-md-6 mb-3 pl-md-4">
                                            <div class="cf-section-subtitle">Manager's Review</div>
                                            
                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Manager Review Type</label>
                                                <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="mgr_tech_att" wire:model="caseFileForm.manager_review_technical">
                                                        <label class="custom-control-label" for="mgr_tech_att">Technical</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="mgr_admin_att" wire:model="caseFileForm.manager_review_administrative">
                                                        <label class="custom-control-label" for="mgr_admin_att">Administrative</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Manager's Verification</label>
                                                <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="mgr_verified_att" wire:model="caseFileForm.manager_comments_verified">
                                                        <label class="custom-control-label" for="mgr_verified_att">Verified</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                                                        <input type="checkbox" class="custom-control-input" id="mgr_not_verified_att" wire:model="caseFileForm.manager_comments_not_verified">
                                                        <label class="custom-control-label" for="mgr_not_verified_att">Not Verified</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 form-group mb-3">
                                                    <label class="cf-input-label">Manager Date</label>
                                                    <input type="date" class="cf-form-control" wire:model="caseFileForm.manager_date">
                                                </div>
                                                <div class="col-6 form-group mb-3">
                                                    <label class="cf-input-label">Manager Name</label>
                                                    <input type="text" class="cf-form-control" wire:model="caseFileForm.manager_name">
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="cf-input-label">Signature (Type to sign)</label>
                                                <input type="text" class="cf-form-control" wire:model="caseFileForm.manager_signature" placeholder="Manager Signature...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                    </div>

                    <div class="modal-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold"
                            wire:click="refreshCaseFileFromWorksheets"
                            wire:loading.attr="disabled"
                            wire:target="refreshCaseFileFromWorksheets">
                            <i class="mdi mdi-file-import-outline"></i> Fill from worksheets
                        </button>
                        <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary font-weight-bold px-4 rounded-pill border" 
                            wire:click="$set('showCaseFileModal', false)" style="height: 38px; font-size: 0.85rem; transition: all 0.15s ease-in-out;">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-dark font-weight-bold px-4 rounded-pill shadow-sm ml-2" 
                            wire:click="saveCaseFile" style="height: 38px; font-size: 0.85rem; background: #0f172a; border-color: #0f172a; transition: all 0.15s ease-in-out;">
                            <i class="mdi mdi-content-save mr-1" style="font-size: 14px;"></i> Save & Generate PDF
                        </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Handler for the "+ ADD NEW" button inside #add-attachment-batch.
        // Uses document delegation so it works after the modal has been appended to body.
        (function() {
            function ensureNativeAttachmentTypeSelect() {
                if (!window.jQuery) {
                    return;
                }

                window.jQuery('#attachment_type_select').each(function() {
                    var $select = window.jQuery(this);
                    $select.addClass('no-select2');

                    if ($select.hasClass('select2-hidden-accessible') && window.jQuery.fn.select2) {
                        $select.select2('destroy');
                    }
                });
            }

            function restoreParentAttachmentModal() {
                if (!window.jQuery) {
                    return;
                }

                var $parentModal = window.jQuery('#add-attachment-batch').first();
                if (!$parentModal.hasClass('show')) {
                    return;
                }

                window.jQuery('body').addClass('modal-open');

                var backdrops = window.jQuery('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops.not(':first').remove();
                }
            }

            function updateAttachmentTypeSelect(id, value) {
                document.querySelectorAll('#attachment_type_select').forEach(function(select) {
                    if (id === undefined || id === null || value === undefined || value === null) {
                        return;
                    }

                    var exists = Array.from(select.options).some(function(option) {
                        return String(option.value) === String(id);
                    });

                    if (!exists) {
                        var option = document.createElement('option');
                        option.value = id;
                        option.textContent = value;
                        select.appendChild(option);
                    }

                    select.value = String(id);

                    if (typeof handleAttachmentTypeChange === 'function') {
                        handleAttachmentTypeChange(select);
                    }
                });

                ensureNativeAttachmentTypeSelect();
            }

            function closeAttachmentTypeModal() {
                if (!window.jQuery || !window.jQuery.fn.modal) {
                    return;
                }

                var $subModal = window.jQuery('#add-attachment-type-modal').last();
                $subModal.modal('hide');

                setTimeout(function() {
                    restoreParentAttachmentModal();
                    ensureNativeAttachmentTypeSelect();
                }, 200);
            }

            function saveAttachmentTypeFromModal() {
                if (!window.jQuery) {
                    return;
                }

                var input = document.getElementById('new_attachment_type_name');
                var name = input ? input.value.trim() : '';

                if (name === '') {
                    window.alert('Please enter a name for the attachment type.');
                    return;
                }

                window.jQuery.ajax({
                    url: @json(route('store-attachment-type')),
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        value: name
                    },
                    success: function(response) {
                        if (!response || !response.success) {
                            window.alert((response && response.message) ? response.message : 'Error adding attachment type.');
                            return;
                        }

                        updateAttachmentTypeSelect(response.id, response.value);

                        if (input) {
                            input.value = '';
                        }

                        closeAttachmentTypeModal();
                    },
                    error: function() {
                        window.alert('Error adding attachment type. Please try again.');
                    }
                });
            }

            window.saveAttachmentTypeFromModal = saveAttachmentTypeFromModal;
            window.ensureNativeAttachmentTypeSelect = ensureNativeAttachmentTypeSelect;

            function openAddTypeModal(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!window.jQuery || !window.jQuery.fn.modal) return;

                var $ = window.jQuery;
                var $subModal = $('#add-attachment-type-modal').last();
                if (!$subModal.length) return;

                $subModal.appendTo('body');
                $subModal.modal('show');
            }

            document.addEventListener('click', function(e) {
                var saveBtn = e.target.closest('#save-attachment-type-btn');
                if (saveBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    saveAttachmentTypeFromModal();
                    return;
                }

                var openBtn = e.target.closest('#open-add-attachment-type-btn');
                if (openBtn) {
                    openAddTypeModal(e);
                }
            }, true);

            document.addEventListener('shown.bs.modal', function(e) {
                if (!e.target || e.target.id !== 'add-attachment-batch') {
                    return;
                }

                ensureNativeAttachmentTypeSelect();
            });

            document.addEventListener('hidden.bs.modal', function(e) {
                if (!e.target || e.target.id !== 'add-attachment-type-modal') {
                    return;
                }

                restoreParentAttachmentModal();
                ensureNativeAttachmentTypeSelect();
            });

            ensureNativeAttachmentTypeSelect();
        })();

        if (!window.hasOpenNewTabListener) {
            window.hasOpenNewTabListener = true;
            window.addEventListener('open-new-tab', function(event) {
                var url = event.detail.url || event.detail;
                if (url) {
                    window.open(url, '_blank');
                }
            });
        }

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

            var coaCheckbox = document.getElementById('includeInCoa');
            if (coaCheckbox) {
                coaCheckbox.checked = isResultReport;
                coaCheckbox.dispatchEvent(new Event('change'));
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
                    var internalUse = document.getElementById('internalUse');
                    if (internalUse) internalUse.checked = false;
                    var includeInCoa = document.getElementById('includeInCoa');
                    if (includeInCoa) {
                        includeInCoa.checked = false;
                        includeInCoa.dispatchEvent(new Event('change'));
                    }
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

            function handleAddAttachmentClick(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.jQuery && window.jQuery.fn.modal) {
                    var $modal = window.jQuery('#add-attachment-batch').first();
                    if ($modal.length) {
                        $modal.appendTo('body').modal('show');
                        if (typeof window.ensureNativeAttachmentTypeSelect === 'function') {
                            window.ensureNativeAttachmentTypeSelect();
                        }
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

                var addBtn = document.getElementById('open-add-attachment-modal');
                if (addBtn) {
                    addBtn.removeEventListener('click', handleAddAttachmentClick);
                    addBtn.addEventListener('click', handleAddAttachmentClick);
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
                    setTimeout(function() {
                        initializeHandlers();
                        if (typeof window.ensureNativeAttachmentTypeSelect === 'function') {
                            window.ensureNativeAttachmentTypeSelect();
                        }
                    }, 50);
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

            var includeInCoa = document.getElementById('includeInCoa');
            var customFile = document.getElementById('customFile');

            if (includeInCoa && includeInCoa.checked) {
                if (window.__coaAttachmentUpload) {
                    window.__coaAttachmentUpload.syncBeforeSubmit();
                }

                if (!customFile || !customFile.files || customFile.files.length === 0) {
                    e.preventDefault();
                    window.alert('Please select at least one PDF file to merge with the Test Report.');
                    return;
                }

                var hasPdf = Array.from(customFile.files).some(function(file) {
                    return file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                });

                if (!hasPdf) {
                    e.preventDefault();
                    window.alert('Test Report merge only supports PDF files.');
                    return;
                }
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

        (function() {
            var includeInCoa = document.getElementById('includeInCoa');
            var coaSection = document.getElementById('coa-merge-section');
            var coaHint = document.getElementById('coa-upload-hint');
            var customFile = document.getElementById('customFile');
            var sortableList = document.getElementById('coa-merge-sortable');
            var orderInput = document.getElementById('coa-file-order');
            var coaSortableInstance = null;
            var coaSelectedFiles = [];

            function isPdfFile(file) {
                return file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            }

            function destroyCoaSortable() {
                if (coaSortableInstance) {
                    coaSortableInstance.destroy();
                    coaSortableInstance = null;
                }
            }

            function resetCoaSortable() {
                if (!sortableList) {
                    return;
                }

                while (sortableList.children.length > 1) {
                    sortableList.removeChild(sortableList.lastElementChild);
                }

                if (orderInput) {
                    orderInput.value = '';
                }

                destroyCoaSortable();
            }

            function updateCoaFileOrder() {
                if (!sortableList || !orderInput) {
                    return;
                }

                var order = [];
                sortableList.querySelectorAll('li[data-file-index]').forEach(function(li) {
                    order.push(li.getAttribute('data-file-index'));
                });
                orderInput.value = order.join(',');
            }

            function updateCoaFileLabel() {
                if (!customFile) {
                    return;
                }

                var label = customFile.nextElementSibling;
                if (!label || !label.classList.contains('custom-file-label')) {
                    return;
                }

                if (includeInCoa && includeInCoa.checked) {
                    label.textContent = coaSelectedFiles.length
                        ? coaSelectedFiles.length + ' PDF file(s) added — click to add more'
                        : 'Add PDF file(s)...';
                }
            }

            function initCoaSortable() {
                if (!window.Sortable || !sortableList || coaSortableInstance) {
                    return;
                }

                coaSortableInstance = window.Sortable.create(sortableList, {
                    animation: 150,
                    filter: '[data-locked]',
                    preventOnFilter: true,
                    onEnd: updateCoaFileOrder,
                });
            }

            function renderCoaFileList() {
                if (!sortableList) {
                    return;
                }

                destroyCoaSortable();

                while (sortableList.children.length > 1) {
                    sortableList.removeChild(sortableList.lastElementChild);
                }

                coaSelectedFiles.forEach(function(file, idx) {
                    var li = document.createElement('li');
                    li.className = 'list-group-item py-2 d-flex align-items-center';
                    li.setAttribute('data-file-index', String(idx));
                    li.innerHTML = '<i class="mdi mdi-drag-vertical text-muted mr-2"></i>'
                        + '<span class="flex-grow-1 text-truncate">' + file.name + '</span>'
                        + '<button type="button" class="btn btn-sm btn-link text-danger p-0 ml-2 coa-remove-file" data-index="'
                        + idx + '" title="Remove"><i class="mdi mdi-close"></i></button>';
                    sortableList.appendChild(li);
                });

                updateCoaFileOrder();
                initCoaSortable();
                updateCoaFileLabel();
            }

            function appendCoaFiles(fileList) {
                var added = false;

                Array.from(fileList).forEach(function(file) {
                    if (!isPdfFile(file)) {
                        return;
                    }

                    var duplicate = coaSelectedFiles.some(function(existing) {
                        return existing.name === file.name
                            && existing.size === file.size
                            && existing.lastModified === file.lastModified;
                    });

                    if (!duplicate) {
                        coaSelectedFiles.push(file);
                        added = true;
                    }
                });

                if (added) {
                    renderCoaFileList();
                }
            }

            function getOrderedCoaFiles() {
                var ordered = [];

                if (!sortableList) {
                    return coaSelectedFiles.slice();
                }

                sortableList.querySelectorAll('li[data-file-index]').forEach(function(li) {
                    var idx = parseInt(li.getAttribute('data-file-index'), 10);
                    if (!Number.isNaN(idx) && coaSelectedFiles[idx]) {
                        ordered.push(coaSelectedFiles[idx]);
                    }
                });

                return ordered;
            }

            function syncCoaFilesToInput() {
                if (!customFile) {
                    return;
                }

                var ordered = getOrderedCoaFiles();
                var dataTransfer = new DataTransfer();

                ordered.forEach(function(file) {
                    dataTransfer.items.add(file);
                });

                customFile.files = dataTransfer.files;

                if (orderInput) {
                    orderInput.value = ordered.map(function(_, index) {
                        return String(index);
                    }).join(',');
                }
            }

            function captureExistingFiles() {
                if (coaSelectedFiles.length > 0) {
                    return coaSelectedFiles.slice();
                }

                if (customFile && customFile.files && customFile.files.length > 0) {
                    return Array.from(customFile.files);
                }

                return [];
            }

            function setFileInputFiles(files) {
                if (!customFile) {
                    return;
                }

                var dataTransfer = new DataTransfer();
                files.forEach(function(file) {
                    dataTransfer.items.add(file);
                });
                customFile.files = dataTransfer.files;
            }

            function setCoaMode(enabled) {
                if (!customFile) {
                    return;
                }

                var existingFiles = captureExistingFiles();

                if (coaSection) {
                    coaSection.style.display = enabled ? '' : 'none';
                }

                if (coaHint) {
                    coaHint.style.display = enabled ? '' : 'none';
                }

                customFile.multiple = enabled;
                customFile.accept = enabled ? '.pdf,application/pdf' : '';
                customFile.name = enabled ? 'coa_attachments[]' : 'attachment';
                customFile.required = !enabled;

                var label = customFile.nextElementSibling;

                if (enabled) {
                    coaSelectedFiles = existingFiles.filter(isPdfFile);
                    renderCoaFileList();
                    syncCoaFilesToInput();

                    if (label && label.classList.contains('custom-file-label') && coaSelectedFiles.length === 0) {
                        label.textContent = 'Add PDF file(s)...';
                    }
                } else {
                    coaSelectedFiles = [];
                    resetCoaSortable();

                    if (existingFiles.length > 0) {
                        setFileInputFiles([existingFiles[0]]);
                        if (label && label.classList.contains('custom-file-label')) {
                            label.textContent = existingFiles[0].name;
                        }
                    } else {
                        customFile.value = '';
                        if (label && label.classList.contains('custom-file-label')) {
                            label.textContent = 'Choose file...';
                        }
                    }
                }
            }

            window.__coaAttachmentUpload = {
                syncBeforeSubmit: syncCoaFilesToInput,
            };

            if (includeInCoa) {
                includeInCoa.addEventListener('change', function() {
                    setCoaMode(includeInCoa.checked);
                });
            }

            if (sortableList) {
                sortableList.addEventListener('click', function(e) {
                    var removeButton = e.target.closest('.coa-remove-file');
                    if (!removeButton) {
                        return;
                    }

                    e.preventDefault();
                    var index = parseInt(removeButton.getAttribute('data-index'), 10);
                    if (Number.isNaN(index)) {
                        return;
                    }

                    coaSelectedFiles.splice(index, 1);
                    renderCoaFileList();
                });
            }

            if (customFile) {
                customFile.addEventListener('change', function(e) {
                    if (includeInCoa && includeInCoa.checked && e.target.files && e.target.files.length > 0) {
                        appendCoaFiles(e.target.files);
                        e.target.value = '';
                    }
                });
            }
        })();
    </script>
</div>