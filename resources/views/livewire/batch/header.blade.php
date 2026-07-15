<div>
    <style>
        .batch-header-bar {
            margin-bottom: 0;
        }

        .batch-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            padding-bottom: 12px;
        }

        .batch-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .batch-code-label {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .batch-stage-pill {
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .batch-priority-pill {
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .batch-meta-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            border-top: 1px solid #f1f5f9;
            padding: 8px 0;
            margin-top: 2px;
        }

        .batch-date-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.77rem;
            color: #495057;
        }

        .batch-date-pill .pill-label {
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.67rem;
            letter-spacing: 0.04em;
        }

        .batch-date-pill .pill-val {
            font-weight: 600;
            color: #334155;
        }

        .batch-related-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.77rem;
            color: #166534;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }

        .batch-related-pill:hover {
            background: #dcfce7;
            color: #14532d;
            text-decoration: none;
        }

        .meta-divider {
            color: #cbd5e1;
            font-size: 0.8rem;
            margin: 0 2px;
        }

        .btn-action-sm {
            height: 32px;
            padding: 0 14px;
            font-size: 0.82rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .batch-header-actions .batch-actions-dropdown {
            position: relative;
            flex-shrink: 0;
        }

        .batch-header-actions .batch-actions-dropdown > .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
            float: none;
            margin-top: 0.35rem;
            min-width: 15.5rem;
            max-width: 20rem;
            max-height: min(70vh, 520px);
            overflow-y: auto;
            padding: 0.35rem 0;
            border: 1px solid #dbe5f0;
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            z-index: 1050;
        }

        .batch-header-actions .batch-actions-dropdown .dropdown-menu > li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .batch-header-actions .batch-actions-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.5rem 0.95rem;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.35;
            color: #334155;
            border: none;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }
    </style>

    <div class="batch-header-bar">
        <div class="batch-header-top">
            {{-- Title & badges --}}
            <div class="batch-title-group">
                <i class="mdi mdi-layers-triple" style="font-size:1.2rem; color:#64748b;"></i>

                @if(isset($batch->id) && $batch->prelim_report_status == 1)
                    <span class="badge badge-info batch-priority-pill">Prelim</span>
                @elseif(isset($batch->id) && $batch->prelim_report_status == 2)
                    <span class="badge badge-secondary batch-priority-pill">Draft</span>
                @else
                    @if(isset($batch->priority) && $batch->priority != "Normal")
                        <span class="batch-priority-pill" style="background:#fff5f5; color:#dc2626; border:1px solid #fecaca;">
                            <i class="mdi mdi-star" style="font-size:0.75rem;"></i> {{ $batch->priority }}
                        </span>
                    @endif
                @endif

                <span class="batch-code-label">
                    {{ isset($batch->batch_code) ? $batch->batch_code : 'New Batch' }}
                </span>

                @if(isset($batch->batch_code))
                    <span class="batch-stage-pill">
                        <i class="mdi mdi-sitemap" style="font-size:0.75rem;"></i>
                        {{ $batch->status ?? 'N/A' }}
                    </span>
                @endif
            </div>

            {{-- Action Buttons --}}
            @if(isset($batch->id))
                <div class="d-flex align-items-center flex-wrap batch-header-actions" style="gap: 6px;">

                    <a href="{{ route('batch-worksheets', ['batch' => $batch->id]) }}"
                        class="btn btn-sm btn-outline-info btn-action-sm">
                        <i class="mdi mdi-clipboard-text"></i> Worksheets
                    </a>

                    @if(!$defaultClient)
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm dropdown-toggle"
                                id="moveWorkflowDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-swap-vertical"></i> Move Workflow
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="moveWorkflowDropdown">
                                @foreach($workflows as $item)
                                    <form class="dropdown-item p-0" method="POST"
                                        action="{{ route('move-to-workflow', ['status' => $item, 'batch_id' => $batch->id]) }}">
                                        @csrf
                                        <input type="hidden" name="is_approval" value="1">
                                        <button type="submit" class="btn btn-link btn-sm text-left w-100"
                                            style="text-decoration: none; color: inherit;">
                                            <small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small>
                                            {{ $item }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="btn-group batch-actions-dropdown"
                         x-data="{ open: false }"
                         @click.outside="open = false">
                        <button type="button"
                            class="btn btn-sm btn-outline-secondary btn-action-sm dropdown-toggle"
                            id="batchActionsDropdownToggle"
                            @click.stop="open = !open"
                            :aria-expanded="open"
                            aria-haspopup="true">
                            <i class="mdi mdi-dots-horizontal"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right"
                            :class="{ 'show': open }"
                            aria-labelledby="batchActionsDropdownToggle"
                            @click="if ($event.target.closest('.dropdown-item, [data-toggle=\'modal\'], form')) { open = false; }">

                            @if(isset($batch->id))
                                @if($batch->status == 'Finished Sample')
                                    <?php            $reportpath = '/storage' . $batch->batch_report_url; ?>
                                    <li>
                                        <a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i
                                                class="mdi mdi-download mr-2"></i> Download COA</a>
                                    </li>
                                @endif
                                @php
                                    $dceaAttachment = \App\BatchAttachment::where('batch_id', $batch->id)
                                        ->where('title', 'like', 'DCEA 009 Form%')
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                                @endphp
                                @if($dceaAttachment && $batch->hasForensicChemistryLab())
                                    <li>
                                        <a target="_blank" href="{{ '/storage' . $dceaAttachment->attachment_url }}" class="dropdown-item">
                                            <i class="mdi mdi-download mr-2 text-primary"></i> Download DCEA 009
                                        </a>
                                    </li>
                                @endif
                                @if(!in_array($batch->status, array("Completed")))
                                    @if($batch->hasSubmissionForm() && $batch->samples()->exists() && $batch->status === 'Sample Approval')
                                        <li>
                                            <span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal">
                                                <i class="mdi mdi-file-pdf-box mr-2"></i> Generate Submission Form
                                            </span>
                                        </li>
                                    @endif
                                    @if($batch->schedule_analysis_sent == '')
                                        <li>
                                            <span class="btn btn-sm dropdown-item" wire:click="$set('showSendScheduleModal', true)"
                                                style="cursor: pointer;">
                                                <i class="mdi mdi-email-send mr-2"></i> Send Schedule of Analysis
                                            </span>
                                        </li>
                                    @endif
                                    <li>
                                        <span class="btn btn-sm dropdown-item" wire:click="$set('showPaymentReminderModal', true)"
                                            style="cursor: pointer;">
                                            <i class="mdi mdi-email-send mr-2"></i> Send Payment Reminder
                                        </span>
                                    </li>
                                @endif

                                @if(isset($batch->status) && $batch->status == "Samples In Lab" && Auth::user()->is_client == 0 && $status == 'Samples In Lab')
                                    @can('laboratory.components.lab-reports.view')
                                    <li>
                                        <a href="{{ route('generateTestRequestReport', ['batch_id' => $batch->id, 'mode' => 'preview', 'lang' => 'en']) }}"
                                           class="dropdown-item">
                                            <i class="mdi mdi-eye-outline mr-2"></i> Preview Test Report
                                        </a>
                                    </li>
                                    @endcan
                                    <li><span class="btn btn-sm dropdown-item" wire:click="$set('showBulkUpdateModal', true)"
                                            style="cursor: pointer;"><i class="mdi mdi-database-edit mr-2"></i> Update Sample
                                            Data</span></li>
                                    <li><span class="btn btn-sm dropdown-item" wire:click="openVerificationModal"
                                            style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for
                                            Verification</span></li>
                                @endif

                                @if(isset($batch->status) && Auth::user()->is_client == 0 && $status == 'Sample Verification')
                                    @can('laboratory.components.lab-reports.view')
                                    <li>
                                        <a href="{{ route('generateTestRequestReport', ['batch_id' => $batch->id, 'mode' => 'preview', 'lang' => 'en']) }}"
                                           class="dropdown-item">
                                            <i class="mdi mdi-eye-outline mr-2"></i> Preview Test Report
                                        </a>
                                    </li>
                                    @endcan
                                @endif

                                @if(isset($batch->status) && Auth::user()->is_client == 0 && $batch->prelim_report_status != 0 && $status == 'Sample Verification')
                                    @if(auth()->user()->checkVerifyLabSampleRole() || in_array(auth()->id(), $this->approversUserIds))
                                        <li><span class="btn btn-sm dropdown-item" wire:click="openApprovalModal"
                                                style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for
                                                Approval</span></li>
                                    @endif
                                    {{-- <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal"
                                            data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process
                                            Results</span></li> --}}
                                    @if($batch->invoice_number == '')
                                        <li><span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice"
                                                data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span>
                                        </li>
                                    @endif
                                @endif

                                @if(isset($batch->status) && in_array($batch->status, ["Samples In Lab", "Sample Verification", "Sample Approval"]) && Auth::user()->is_client == 0 && $batch->prelim_report_status != 0)
                                    @if(auth()->user()->checkVerifyLabSampleRole() && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 2 && $status == 'Sample Verification')
                                        {{-- <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal"
                                                data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process
                                                Results</span></li> --}}
                                    @endif
                                    @if((auth()->user()->checkVerifyLabSampleRole() || in_array(auth()->id(), $this->approversUserIds)) && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 1 && $status == 'Sample Verification')
                                        <li><span class="btn btn-sm dropdown-item" wire:click="openApprovalModal"
                                                style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for
                                                Approval</span></li>
                                    @endif
                                @endif
                                @if(isset($batch->status) && $batch->status == 'Samples In Lab' && ($batch->invoice_id == 0 || $batch->invoice_id == null))
                                    <li>
                                        <a href="{{ route('billing.sales-order.create', ['batches' => [$batch->batch_code]]) }}"
                                            class="dropdown-item">
                                            <i class="mdi mdi-check-decagram mr-2 text-success"></i> Generate Draft Invoice
                                        </a>
                                    </li>
                                @endif
                                @if(isset($batch->status) && $batch->status == 'Samples In Lab' && $batch->prelim_report_status == 2 && $batch->invoice_number == '')
                                    <li><span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice"
                                            data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span>
                                    </li>
                                @endif
                                @if(isset($batch->status) && in_array($batch->status, ["Sample Verification", "Sample Approval", "Reports for Collection", "Reports In Payment"]) && Auth::user()->is_client == 0)
                                    @if($batch->status == "Sample Verification")
                                        @if($notCaptured->count() == 0)
                                            @if(auth()->user()->checkVerifyLabSampleRole() || in_array(auth()->id(), $this->approversUserIds))
                                                <li><span class="dropdown-item">
                                                        <hr />
                                                    </span></li>
                                                <li><span class="btn btn-sm dropdown-item" wire:click="openApprovalModal"
                                                        style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for
                                                        Approval</span></li>
                                            @else
                                                <li><span class="dropdown-item text-muted small"><i class="mdi mdi-lock"></i> Send for Approval
                                                        (Requires Verify Role)</span></li>
                                            @endif
                                        @else
                                            <li><span class="dropdown-item text-danger small"><i class="mdi mdi-alert"></i> Send for
                                                    Approval (Pending Data Capture)</span></li>
                                        @endif
                                    @endif
                                    @if(in_array($batch->status, ["Sample Approval", "Reports for Collection", "Reports In Payment"]) && $batch->batch_report_url != '')
                                        @if($batch->invoice_number == '')
                                            <li><span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice"
                                                    data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span>
                                            </li>
                                        @endif
                                    @endif
                                    @if($batch->status == "Sample Approval")
                                        <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal" data-next-modal="#process-test-request-report-modal"><i class="mdi mdi-file-document-edit-outline mr-2"></i> Generate Test Request Report</span></li>
                                        @if(in_array($batch->status, ["Sample Approval", "Reports for Collection", "Reports In Payment"]))
                                            {{-- <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal"
                                                    data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process
                                                    Results</span></li> --}}
                                        @endif
                                        @if($batch->batch_report_url != '')
                                            @if($batch->is_qc_batch == 0)
                                                <li><span class="dropdown-item">
                                                        <hr />
                                                    </span></li>
                                                <li><span class="btn btn-sm dropdown-item" data-target="#send-to-payments-modal"
                                                        data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for
                                                        Payment</span></li>
                                                <li><span class="btn btn-sm dropdown-item" data-target="#send-to-email-modal"
                                                        data-toggle="modal"><i class="mdi mdi-email mr-2"></i> Send for Collection</span></li>
                                            @else
                                                <li><span class="dropdown-item">
                                                        <hr />
                                                    </span></li>
                                                <li><span class="btn btn-sm dropdown-item" data-target="#mark-complete" data-toggle="modal"><i
                                                            class="mdi mdi-subdirectory-arrow-right mr-2"></i> Mark as Complete</span></li>
                                            @endif
                                        @endif
                                    @endif
                                    @if($batch->status == 'Reports In Payment')
                                        <li><span class="btn btn-sm dropdown-item" data-target="#send-to-email-modal"
                                                data-toggle="modal"><i class="mdi mdi-email mr-2"></i> Send for Collection</span></li>
                                    @endif
                                @endif
                            @endif
                        </ul>
                    </div>
                </div> {{-- end action buttons --}}
            @endif
        </div> {{-- end batch-header-top --}}

        {{-- META BAR: Dates + Related Batches --}}
        @if(isset($batch->id))
            <div class="batch-meta-bar">
                {{-- Key Dates --}}
                @foreach (getSampleDateTypes() as $date)
                    @if($date == 'Login Date' || $date == 'Target Date' || $date == 'Processing Date')
                        @php $dateVal = $batch->get_date($date); @endphp
                        <span class="batch-date-pill">
                            <span class="pill-label">{{ $date }}</span>
                            <span class="pill-val">{{ $dateVal ? date('d M Y', strtotime($dateVal['date'])) : '—' }}</span>
                        </span>
                    @endif
                @endforeach

                {{-- Related Batches --}}
                @if($batch->hasSubmissionForm())
                    @php
                        $relatedBatches = $batch->submissionFormInstance
                            ? $batch->submissionFormInstance->batches->where('id', '!=', $batch->id)
                            : collect();
                    @endphp
                    @if($relatedBatches->count() > 0)
                        <span class="meta-divider">|</span>
                        <small class="text-muted"
                            style="font-size:0.7rem; font-weight:600; text-transform:uppercase;">Related:</small>
                        @foreach($relatedBatches as $rb)
                            <a href="{{ route('view-batch-details', ['batch' => $rb->id, 'client' => 0, 'portal' => 0, 'status' => $rb->status]) }}"
                                class="batch-related-pill">
                                <i class="mdi mdi-flask-outline" style="font-size:0.75rem;"></i>
                                {{ $rb->batch_code }}
                            </a>
                        @endforeach
                    @endif
                @endif
            </div>
        @endif

    </div>
    {{-- MODALS --}}

    <style>
        /* Modern Gray Theme for Modals */
        .modal-content-modern {
            border: none;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header-modern {
            border-radius: 8px 8px 0 0;
            padding: 15px 20px;
        }

        .modal-title-modern {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .modal-body-modern {
            padding: 20px;
            background-color: #fff;
        }

        .modal-footer-modern {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 8px 8px;
            padding: 15px 20px;
        }

        .form-control-modern {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #495057;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .form-control-modern:focus {
            background-color: #fff;
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
        }

        .btn-secondary-modern {
            background-color: #e2e6ea;
            border-color: #dae0e5;
            color: #212529;
        }

        .btn-secondary-modern:hover {
            background-color: #dbe0e5;
            border-color: #d3d9df;
            color: #212529;
        }

        .btn-primary-modern {
            border-radius: 6px;
        }

        .btn-primary-modern:hover {
            opacity: 0.95;
        }

        .modal-label-small {
            font-size: 0.75rem !important;
            /* Smaller label font size */
        }
    </style>

    {{-- Send Schedule of Analysis --}}
    @if($showSendScheduleModal)
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content modal-content-modern">
                    <div class="modal-header modal-header-modern">
                        <h5 class="modal-title modal-title-modern">Send Schedule of Analysis</h5>
                        <button type="button" class="close" wire:click="$set('showSendScheduleModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-body-modern">
                        <div class="form-group">
                            <label class="text-muted font-weight-bold small modal-label-small">Select Contact</label>
                            <select class="form-control form-control-modern" wire:model.live="selectedContact">
                                <option value="">Select Contact</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}">{{ $contact->first_name }} {{ $contact->last_name }}
                                        ({{ $contact->email }})</option>
                                @endforeach
                            </select>
                            @error('selectedContact') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-secondary-modern btn-sm"
                            wire:click="$set('showSendScheduleModal', false)">Close</button>
                        <button type="button" class="btn btn-primary-modern btn-sm" wire:click="sendScheduleAnalysis">Send
                            Schedule</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Send Payment Reminder --}}
    @if($showPaymentReminderModal)
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-modern">
                    <div class="modal-header modal-header-modern">
                        <h5 class="modal-title modal-title-modern">Send Payment Reminder</h5>
                        <button type="button" class="close" wire:click="$set('showPaymentReminderModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-body-modern">
                        <div class="form-group">
                            <label class="text-muted font-weight-bold small modal-label-small">Select Contact</label>
                            <select class="form-control form-control-modern" wire:model.live="selectedContact">
                                <option value="">Select Contact</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}">{{ $contact->first_name }} {{ $contact->last_name }}
                                        ({{ $contact->email }})</option>
                                @endforeach
                            </select>
                            @error('selectedContact') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group" wire:ignore>
                            <label class="text-muted font-weight-bold small modal-label-small">Email Body</label>
                            <div x-data="{
                            initTinyMCE() {
                                if (typeof tinymce === 'undefined') {
                                    const script = document.createElement('script');
                                    script.src = '/tinymce/tinymce.min.js';
                                    script.onload = () => this.setupEditor();
                                    document.head.appendChild(script);
                                } else {
                                    this.setupEditor();
                                }
                            },
                            setupEditor() {
                                if (tinymce.get('paymentReminderBody')) {
                                    tinymce.get('paymentReminderBody').remove();
                                }
                                tinymce.init({
                                    selector: '#paymentReminderBody',
                                    height: 300,
                                    menubar: false,
                                    plugins: [
                                        'advlist autolink lists link image charmap print preview anchor',
                                        'searchreplace visualblocks code fullscreen',
                                        'insertdatetime media table paste code help wordcount'
                                    ],
                                    toolbar: 'undo redo | formatselect | ' +
                                    'bold italic backcolor | alignleft aligncenter ' +
                                    'alignright alignjustify | bullist numlist outdent indent | ' +
                                    'removeformat | help',
                                    setup: (editor) => {
                                        editor.on('change', () => {
                                            @this.set('emailBody', editor.getContent());
                                        });
                                    }
                                });
                            }
                        }" x-init="initTinyMCE()">
                                <textarea id="paymentReminderBody" class="form-control form-control-modern" rows="10"
                                    wire:model="emailBody"></textarea>
                            </div>
                            @error('emailBody') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-secondary-modern btn-sm"
                            wire:click="$set('showPaymentReminderModal', false)">Close</button>
                        <button type="button" class="btn btn-primary-modern btn-sm" wire:click="sendPaymentReminder">Send
                            Reminder</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Update Samples --}}
    @if($showBulkUpdateModal)
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-modern">
                    <div class="modal-header modal-header-modern">
                        <h5 class="modal-title modal-title-modern">Bulk Update Sample Data</h5>
                        <button type="button" class="close" wire:click="$set('showBulkUpdateModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-body-modern">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <h6 class="text-muted font-weight-bold small mb-3 modal-label-small">Select Samples to
                                    Update</h6>
                                <div
                                    style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 6px;">
                                    @foreach($allSamples as $sample)
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" wire:model="selectedSamples"
                                                value="{{ $sample->id }}" id="sample_{{ $sample->id }}">
                                            <label class="custom-control-label text-dark modal-label-small"
                                                for="sample_{{ $sample->id }}">
                                                {{ $sample->sample_code }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedSamples') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted font-weight-bold small mb-3 modal-label-small">Update Fields</h6>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="text-muted small modal-label-small">Main Standard</label>
                                        <select class="form-control form-control-modern"
                                            wire:model="bulkData.main_standard">
                                            <option value="">-- No Change --</option>
                                            @foreach($standards as $standard)
                                                <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="text-muted small modal-label-small">Secondary Standard</label>
                                        <select class="form-control form-control-modern"
                                            wire:model="bulkData.secondary_standard">
                                            <option value="">-- No Change --</option>
                                            @foreach($standards as $standard)
                                                <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="text-muted small modal-label-small">Storage</label>
                                        <select class="form-control form-control-modern"
                                            wire:model.live="bulkData.store_id">
                                            <option value="">-- No Change --</option>
                                            @foreach($labStores as $storeName => $storeData)
                                                <option value="{{ $storeData['id'] }}">{{ $storeName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="text-muted small modal-label-small">Storage Slot</label>
                                        <select class="form-control form-control-modern" wire:model="bulkData.store_slot_id"
                                            @if(empty($storeSlots)) disabled @endif>
                                            <option value="">-- Select Slot --</option>
                                            @foreach($storeSlots as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="text-muted small modal-label-small">Disposal Date</label>
                                    <input type="date" class="form-control form-control-modern"
                                        wire:model="bulkData.disposal_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-secondary-modern btn-sm"
                            wire:click="$set('showBulkUpdateModal', false)">Close</button>
                        <button type="button" class="btn btn-primary-modern btn-sm" wire:click="bulkUpdateSampleData">Update
                            Samples</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Send/Move for Verification --}}
    @if($showVerificationModal)
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document"
                style="max-width: 1040px; width: 96vw;">
                <div class="modal-content vw-modal-shell shadow-lg">
                    @include('livewire.batch.partials.case-file-review-styles')

                    <div class="vw-modal-header">
                        <button type="button" class="close" wire:click="$set('showVerificationModal', false)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="d-flex align-items-start">
                            <div class="vw-icon-wrap">
                                <i class="mdi mdi-clipboard-check-outline"></i>
                            </div>
                            <div>
                                <h5 class="vw-title">Move Batch to Verification</h5>
                                <p class="vw-subtitle">
                                    Assign approvers for <strong>{{ $batch->batch_code }}</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="vw-modal-body">
                        @if($verificationActiveTab === 'assign_approvers' || ! $batch->hasDnaLab())
                            <div class="vw-info-banner">
                                <i class="mdi mdi-account-multiple-check-outline"></i>
                                <span>Select the technical signatory for each lab section before submitting to verification.</span>
                            </div>

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small rounded mb-3" role="alert">
                                    <i class="mdi mdi-alert-circle mr-1"></i>
                                    <span>{{ session('error') }}</span>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            @endif

                            <div class="cf-card vw-approvers-card mb-3">
                                <div class="cf-card-header" style="border-left: 4px solid #2563eb;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-account-group-outline text-primary" style="font-size: 18px;"></i>
                                        Section technical signatories
                                    </h6>
                                </div>
                                <div class="cf-card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Lab Section</th>
                                                    <th style="min-width: 220px;">Assign Signatory</th>
                                                    <th style="min-width: 180px;">Title</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($verificationSections as $section)
                                                    <tr>
                                                        <td class="align-middle font-weight-bold">
                                                            {{ $section->name }}
                                                            @if(!empty($section->code))
                                                                <br><small class="text-muted">{{ $section->code }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <select class="cf-form-control" wire:model="verificationData.approver_user.{{ $section->id }}">
                                                                <option value="">Select signatory</option>
                                                                @foreach($this->getSectionVerifierUsers($section->id) as $user)
                                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="cf-form-control"
                                                                wire:model="verificationData.title.{{ $section->id }}"
                                                                placeholder="Verification title">
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-muted small p-3">
                                                            No lab sections on this batch. Ensure analysis types have a lab section assigned, then recreate or refresh sample setup.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="cf-card mb-0">
                                <div class="cf-card-header" style="border-left: 4px solid #64748b;">
                                    <h6 class="cf-card-title">
                                        <i class="mdi mdi-file-document-outline text-secondary" style="font-size: 18px;"></i>
                                        Report options
                                    </h6>
                                </div>
                                <div class="cf-card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="cf-input-label">Report Status Level</label>
                                            <select class="cf-form-control" wire:model="verificationData.level">
                                                <option value="0">Final Report</option>
                                                <option value="1">Preliminary Report</option>
                                                <option value="2">Draft Report</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="custom-control custom-checkbox custom-checkbox-modern mb-3">
                                        <input class="custom-control-input" type="checkbox" id="has_method_deviation_ver"
                                            wire:model.live="verificationData.has_method_deviation">
                                        <label class="custom-control-label" for="has_method_deviation_ver">
                                            Deviations from method
                                        </label>
                                    </div>

                                    @if(!empty($verificationData['has_method_deviation']))
                                        <div class="form-group mb-0">
                                            <label class="cf-input-label">Reason for deviation</label>
                                            <textarea class="cf-form-control" rows="3"
                                                wire:model="verificationData.method_deviation_reason"
                                                placeholder="Describe the deviation from method…"></textarea>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="vw-modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            wire:click="$set('showVerificationModal', false)">Close</button>

                        @if($verificationActiveTab === 'assign_approvers' || ! $batch->hasDnaLab())
                            <button type="button" class="btn btn-vw-primary btn-sm"
                                wire:click="moveToVerification">
                                <i class="mdi mdi-send-check-outline mr-1"></i> Submit to Verification
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- Send for Approval Modal --}}
    @if($showApprovalModal)
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
                <div class="modal-content modal-content-modern">
                    <div class="modal-header modal-header-modern">
                        <h5 class="modal-title modal-title-modern">
                            <i class="mdi mdi-check-decagram"></i> Send for Approval
                        </h5>
                        <button type="button" class="close" wire:click="$set('showApprovalModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-body-modern">
                        @if($this->verificationApprovalStatus > 0)
                            <div class="alert alert-danger p-2 d-flex mt-1">
                                <i class="mdi mdi-decagram" style="font-size: 30px"></i>
                                <span class="p-2">Confirm all approvers have approved before sending the report for
                                    approval</span>
                            </div>
                        @endif

                        @if(session()->has('approval_error'))
                            <div class="alert alert-danger p-2 d-flex mt-1">
                                <i class="mdi mdi-alert" style="font-size: 30px"></i>
                                <span class="p-2">{{ session('approval_error') }}</span>
                            </div>
                        @endif

                        @if(session()->has('error'))
                            <div class="alert alert-danger p-2 d-flex mt-1">
                                <i class="mdi mdi-alert" style="font-size: 30px"></i>
                                <span class="p-2">{!! session('error') !!}</span>
                            </div>
                        @endif

                        <div class="alert alert-primary p-2 d-flex">
                            <i class="mdi mdi-decagram" style="font-size: 30px"></i>
                            <span class="p-2">Confirm you want to send this {{ $batch->batch_code }} batch for
                                approval</span>
                        </div>

                        <div class="form-group">
                            <label class="text-muted font-weight-bold small modal-label-small">Title</label>
                            <input type="text" class="form-control form-control-modern" wire:model="approvalData.title"
                                placeholder="Approver Title">
                            @error('approvalData.title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="text-muted font-weight-bold small modal-label-small">Approver</label>
                            <select class="form-control form-control-modern" wire:model="approvalData.user_id">
                                <option value="">Select Approver</option>
                                @foreach($this->availableApprovalUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('approvalData.user_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="text-muted font-weight-bold small modal-label-small">Remarks</label>
                            <textarea class="form-control form-control-modern" wire:model="approvalData.comments"
                                placeholder="Comments..." rows="3"></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="approvalData.notification"
                                id="approval-notification">
                            <label class="form-check-label" for="approval-notification">
                                Send Email Notification
                            </label>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" wire:model="approvalData.send_message"
                                id="approval-sms">
                            <label class="form-check-label" for="approval-sms">
                                Send SMS
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        @if($this->verificationApprovalStatus == 0)
                            <button type="button" class="btn btn-primary-modern btn-sm" wire:click="sendForApproval">
                                <i class="mdi mdi-thumb-up"></i> Yes Proceed
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary-modern btn-sm"
                            wire:click="$set('showApprovalModal', false)">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- [modals end] --}}

    {{-- Checklist Required Modal --}}
    @if($showChecklistRequiredModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 860px; width: 92vw;">
            <div class="modal-content modal-content-modern" style="max-height: calc(100vh - 3.5rem); overflow: hidden;">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern">
                        <i class="mdi mdi-clipboard-alert-outline"></i> Complete Checklist First
                    </h5>
                    <button type="button" class="close" wire:click="closeChecklistRequiredModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-modern" style="overflow-y: auto; max-height: calc(100vh - 12rem);">
                    <p class="mb-3">{{ $checklistRequiredMessage }}</p>

                    @if($checklistStageName)
                        <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #fff;">
                            @livewire(
                                'sampleworkflow.approval-checklist',
                                ['sampleId' => (string) $batch->id, 'stageName' => $checklistStageName],
                                key('header-checklist-' . $batch->id . '-' . $checklistStageName)
                            )
                        </div>
                    @endif
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="closeChecklistRequiredModal">Done</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Standalone Case File Review Form Modal -->
    @if($showCaseFileModal && $batch->hasDnaLab())
        <div class="modal fade show" tabindex="-1" role="dialog"
            style="display: block; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document" style="max-width: 1000px; width: 95vw;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: #f8fafc;">
                    @include('livewire.batch.partials.case-file-review-styles')

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
                        <div class="cf-worksheet-bar">
                            <p><i class="mdi mdi-information-outline mr-1"></i> Empty fields can be filled from saved grouped worksheet stages.</p>
                            <button type="button" class="btn btn-cf-outline"
                                wire:click="refreshCaseFileFromWorksheets"
                                wire:loading.attr="disabled"
                                wire:target="refreshCaseFileFromWorksheets">
                                <span wire:loading.remove wire:target="refreshCaseFileFromWorksheets">
                                    <i class="mdi mdi-file-import-outline"></i> Fill from worksheets
                                </span>
                                <span wire:loading wire:target="refreshCaseFileFromWorksheets">Loading…</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="saveStandaloneCaseFile">
                            @include('livewire.batch.partials.case-file-review-form', [
                                'wireModel' => 'caseFormData',
                                'idSuffix' => 'sa',
                            ])
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
                            wire:click="saveStandaloneCaseFile" style="height: 38px; font-size: 0.85rem; background: #0f172a; border-color: #0f172a; transition: all 0.15s ease-in-out;">
                            <i class="mdi mdi-content-save mr-1" style="font-size: 14px;"></i> Save & Generate PDF
                        </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @include('layouts.lab.sample-workflow.modals.process-test-request-report-modal', ['batch' => $batch])
</div>

<script>
    if (!window.hasOpenNewTabListener) {
        window.hasOpenNewTabListener = true;
        window.addEventListener('open-new-tab', function(event) {
            var url = event.detail.url || event.detail;
            if (url) {
                window.open(url, '_blank');
            }
        });
    }
</script>