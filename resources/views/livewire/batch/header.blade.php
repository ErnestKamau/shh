<div>
<style>
    .batch-header-bar {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 14px 20px 0 20px;
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
        color: #1e293b;
        letter-spacing: 0.01em;
    }
    .batch-stage-pill {
        background: #f0f4ff;
        color: #3b5fc0;
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid #c7d7fc;
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
                    {{ $batch->tracking_stage()->name }}
                </span>
            @endif
        </div>

        {{-- Action Buttons --}}
        @if(isset($batch->id))
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">

            <a href="{{ route('batch-worksheets', ['batch' => $batch->id]) }}"
               class="btn btn-sm btn-outline-info btn-action-sm">
                <i class="mdi mdi-clipboard-text"></i> Worksheets
            </a>

            @if(!$defaultClient)
            <div class="btn-group">
                <button type="button"
                        class="btn btn-sm btn-outline-secondary btn-action-sm dropdown-toggle"
                        id="moveWorkflowDropdown"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <i class="mdi mdi-swap-vertical"></i> Move Workflow
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="moveWorkflowDropdown">
                    @foreach($workflows as $item)
                        <form class="dropdown-item p-0" method="POST"
                              action="{{ route('move-to-workflow', ['status' => $item, 'batch_id' => $batch->id]) }}">
                            @csrf
                                                        <input type="hidden" name="is_approval" value="1">
                                                        <button type="submit"
                                    class="btn btn-link btn-sm text-left w-100"
                                    style="text-decoration: none; color: inherit;">
                                <small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small>
                                {{ $item }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm dropdown-toggle"
                        id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-horizontal"></i> Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
            
                    @if(isset($batch->id))
                        @if($batch->status == 'Finished Sample')
                        <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                        <li>
                            <a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download COA</a>
                        </li>
                        @endif
                        @if(!in_array($batch->status,array("Completed")))
                            @if($batch->hasSubmissionForm() && $batch->samples()->exists())
                                @if($batch->hasSubmissionFormAttachment())
                                <li>
                                    <form action="{{ route('regenerate-submission-form', ['batch' => $batch->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm dropdown-item"><i class="mdi mdi-refresh mr-2"></i> Regenerate Submission Form</button>
                                    </form>
                                </li>
                                @else
                                <li>
                                    <form action="{{ route('regenerate-submission-form', ['batch' => $batch->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm dropdown-item"><i class="mdi mdi-file-pdf-box mr-2"></i> Generate Submission Form</button>
                                    </form>
                                </li>
                                @endif
                            @endif
                            @if($batch->schedule_analysis_sent == '')
                            <li>
                                <span class="btn btn-sm dropdown-item" wire:click="$set('showSendScheduleModal', true)" style="cursor: pointer;">
                                    <i class="mdi mdi-email-send mr-2"></i> Send Schedule of Analysis
                                </span>
                            </li>
                            @endif
                            <li>
                                <span class="btn btn-sm dropdown-item" wire:click="$set('showPaymentReminderModal', true)" style="cursor: pointer;">
                                    <i class="mdi mdi-email-send mr-2"></i> Send Payment Reminder
                                </span>
                            </li>
                        @endif

                        @if(isset($batch->status) && $batch->status=="Samples In Lab" && Auth::user()->is_client == 0 && $status == 'Samples In Lab')
                        <li><span class="btn btn-sm dropdown-item" wire:click="$set('showBulkUpdateModal', true)" style="cursor: pointer;"><i class="mdi mdi-database-edit mr-2"></i> Update Sample Data</span></li>
                        <li><span class="btn btn-sm dropdown-item" wire:click="$set('showVerificationModal', true)" style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Verification</span></li>
                        <li><span class="btn btn-sm dropdown-item" data-target="#view-coa-report" data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span></li>
                        @endif
                        
                        @if(isset($batch->status) && Auth::user()->is_client == 0 && $batch->prelim_report_status != 0 && $status == 'Sample Verification')
                        @if(auth()->user()->checkVerifyLabSampleRole() || in_array(auth()->id(), $this->approversUserIds))
                        <li><span class="btn btn-sm dropdown-item" wire:click="$set('showApprovalModal', true)" style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval</span></li>
                        @endif
                        <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span></li>
                        @if($batch->invoice_number == '')
                        <li><span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice" data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span></li>
                        @endif
                        @endif

                        @if(isset($batch->status) && in_array($batch->status, ["Samples In Lab","Sample Verification","Sample Approval"]) && Auth::user()->is_client == 0 && $batch->prelim_report_status != 0)
                            @if(auth()->user()->checkVerifyLabSampleRole() && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 2 && $status == 'Sample Verification')
                            <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span></li>
                            @endif
                            @if((auth()->user()->checkVerifyLabSampleRole() || in_array(auth()->id(), $this->approversUserIds)) && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 1 && $status == 'Sample Verification')
                            <li><span class="btn btn-sm dropdown-item" wire:click="$set('showApprovalModal', true)" style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval</span></li>
                            @endif
                            @if($batch->batch_report_url)
                            <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                            <li><a class="btn btn-sm dropdown-item" target="_blank" href="{{ $reportpath }}"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</a></li>
                            @endif
                        @endif
                        @if(isset($batch->status) && $batch->status == 'Samples In Lab' && ($batch->invoice_id == 0 || $batch->invoice_id == null))
                        <li>
                            <a href="{{ route('billing.sales-order.create', ['batches' => [$batch->batch_code]]) }}" class="dropdown-item">
                                <i class="mdi mdi-check-decagram mr-2 text-success"></i> Generate Draft Invoice
                            </a>
                        </li>
                        @endif
                        @if(isset($batch->status) && $batch->status == 'Samples In Lab' && $batch->prelim_report_status == 2 && $batch->invoice_number == '')
                            <li><span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice" data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span></li>
                        @endif
                        @if(isset($batch->status) && in_array($batch->status, ["Sample Verification","Sample Approval","Reports for Collection","Reports In Payment"]) && Auth::user()->is_client == 0)
                            @if($batch->status == "Sample Verification")
                                @if($notCaptured->count() == 0)
                                    @if(auth()->user()->checkVerifyLabSampleRole() || in_array(auth()->id(), $this->approversUserIds))
                                    <li><span class="dropdown-item"><hr/></span></li>
                                    <li><span class="btn btn-sm dropdown-item" wire:click="$set('showApprovalModal', true)" style="cursor: pointer;"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval</span></li>
                                    @else
                                    <li><span class="dropdown-item text-muted small"><i class="mdi mdi-lock"></i> Send for Approval (Requires Verify Role)</span></li>
                                    @endif
                                @else
                                    <li><span class="dropdown-item text-danger small"><i class="mdi mdi-alert"></i> Send for Approval (Pending Data Capture)</span></li>
                                @endif
                            @endif
                            @if(in_array($batch->status,["Sample Approval","Reports for Collection","Reports In Payment"]) && $batch->batch_report_url != '')
                                @if($batch->invoice_number == '')
                                <li><span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice" data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span></li>
                                @endif
                            @endif
                            @if($batch->status == "Sample Approval")
                                @if($batch->batch_report_url)
                                <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                                <li><a class="btn btn-sm dropdown-item" target="_blank" href="{{ $reportpath }}"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</a></li>
                                @endif
                                @if(in_array($batch->status,["Sample Approval","Reports for Collection","Reports In Payment"]))
                                <li><span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span></li>
                                @endif
                                @if($batch->batch_report_url != '')
                                    @if($batch->is_qc_batch == 0)
                                    <li><span class="dropdown-item"><hr/></span></li>
                                    <li><span class="btn btn-sm dropdown-item" data-target="#send-to-payments-modal" data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Payment</span></li>
                                    <li><span class="btn btn-sm dropdown-item" data-target="#send-to-email-modal" data-toggle="modal"><i class="mdi mdi-email mr-2"></i> Send for Collection</span></li>
                                    @else
                                    <li><span class="dropdown-item"><hr/></span></li>
                                    <li><span class="btn btn-sm dropdown-item" data-target="#mark-complete" data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Mark as Complete</span></li>
                                    @endif
                                @endif
                            @endif
                            @if($batch->status == 'Reports In Payment')
                                <li><span class="btn btn-sm dropdown-item" data-target="#send-to-email-modal" data-toggle="modal"><i class="mdi mdi-email mr-2"></i> Send for Collection</span></li>
                            @endif
                            @if($batch->status == 'Reports In Payment' || $batch->status == 'Reports for Collection')
                                @if($batch->batch_report_url)
                                <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                                <li><a class="btn btn-sm dropdown-item" target="_blank" href="{{ $reportpath }}"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</a></li>
                                @endif
                            @endif
                        @endif
                    @endif
                </div>
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
                <small class="text-muted" style="font-size:0.7rem; font-weight:600; text-transform:uppercase;">Related:</small>
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
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .modal-header-modern {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
        padding: 15px 20px;
    }
    .modal-title-modern {
        color: #343a40;
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
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }
    .btn-primary-modern:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    .modal-label-small {
        font-size: 0.75rem !important; /* Smaller label font size */
    }
</style>

{{-- Send Schedule of Analysis --}}
@if($showSendScheduleModal)
<div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
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
                            <option value="{{ $contact->id }}">{{ $contact->first_name }} {{ $contact->last_name }} ({{ $contact->email }})</option>
                        @endforeach
                    </select>
                    @error('selectedContact') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="modal-footer modal-footer-modern">
                <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showSendScheduleModal', false)">Close</button>
                <button type="button" class="btn btn-primary-modern btn-sm" wire:click="sendScheduleAnalysis">Send Schedule</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Send Payment Reminder --}}
@if($showPaymentReminderModal)
<div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
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
                            <option value="{{ $contact->id }}">{{ $contact->first_name }} {{ $contact->last_name }} ({{ $contact->email }})</option>
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
                        <textarea id="paymentReminderBody" class="form-control form-control-modern" rows="10" wire:model="emailBody"></textarea>
                    </div>
                    @error('emailBody') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="modal-footer modal-footer-modern">
                <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showPaymentReminderModal', false)">Close</button>
                <button type="button" class="btn btn-primary-modern btn-sm" wire:click="sendPaymentReminder">Send Reminder</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bulk Update Samples --}}
@if($showBulkUpdateModal)
<div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
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
                        <h6 class="text-muted font-weight-bold small mb-3 modal-label-small">Select Samples to Update</h6>
                        <div style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 6px;">
                            @foreach($allSamples as $sample)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" wire:model="selectedSamples" value="{{ $sample->id }}" id="sample_{{ $sample->id }}">
                                    <label class="custom-control-label text-dark modal-label-small" for="sample_{{ $sample->id }}">
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
                                <select class="form-control form-control-modern" wire:model="bulkData.main_standard">
                                    <option value="">-- No Change --</option>
                                    @foreach($standards as $standard)
                                        <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="text-muted small modal-label-small">Secondary Standard</label>
                                <select class="form-control form-control-modern" wire:model="bulkData.secondary_standard">
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
                                <select class="form-control form-control-modern" wire:model.live="bulkData.store_id">
                                    <option value="">-- No Change --</option>
                                    @foreach($labStores as $storeName => $storeData)
                                        <option value="{{ $storeData['id'] }}">{{ $storeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="text-muted small modal-label-small">Storage Slot</label>
                                <select class="form-control form-control-modern" wire:model="bulkData.store_slot_id" @if(empty($storeSlots)) disabled @endif>
                                    <option value="">-- Select Slot --</option>
                                    @foreach($storeSlots as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="text-muted small modal-label-small">Disposal Date</label>
                            <input type="date" class="form-control form-control-modern" wire:model="bulkData.disposal_date">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-modern">
                <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showBulkUpdateModal', false)">Close</button>
                <button type="button" class="btn btn-primary-modern btn-sm" wire:click="bulkUpdateSampleData">Update Samples</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Send/Move for Verification --}}
@if($showVerificationModal)
<div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-modern">
            <div class="modal-header modal-header-modern pb-0" style="border-bottom: none;">
                <h5 class="modal-title modal-title-modern w-100">
                    Move Batch to Verification
                    <button type="button" class="close float-right" wire:click="$set('showVerificationModal', false)">
                        <span>&times;</span>
                    </button>
                    
                    <ul class="nav nav-tabs mt-3" style="border-bottom: 1px solid #dee2e6; margin-bottom: -1px;">
                        <li class="nav-item">
                            <a class="nav-link @if($verificationActiveTab == 'case_file_review') active font-weight-bold @endif" 
                               wire:click="$set('verificationActiveTab', 'case_file_review')" 
                               style="cursor: pointer; color: @if($verificationActiveTab == 'case_file_review') #495057 @else #6c757d @endif;">
                               Step 1: Case File Review
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($verificationActiveTab == 'assign_approvers') active font-weight-bold @endif" 
                               wire:click="$set('verificationActiveTab', 'assign_approvers')"
                               style="cursor: pointer; color: @if($verificationActiveTab == 'assign_approvers') #495057 @else #6c757d @endif;">
                               Step 2: Assign Approvers
                            </a>
                        </li>
                    </ul>
                </h5>
            </div>
            <div class="modal-body modal-body-modern pt-3">
                
                <div class="tab-content">
                    <div class="tab-pane fade @if($verificationActiveTab == 'case_file_review') show active @endif">
                        <div class="case-file-review-form text-left">
                            <h6 class="font-weight-bold text-center mb-4">FORENSIC BIOLOGY AND DNA LABORATORY<br>CASE FILE REVIEW FORM (DNA/F/12)</h6>

                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>SAMPLE INFORMATION</strong></div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="modal-label-small text-muted mb-0">Lab No:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.lab_no">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="modal-label-small text-muted mb-0">File No:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.file_no">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="modal-label-small text-muted mb-0">Date in:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.date_in">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="modal-label-small text-muted mb-0">Client:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.client">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="modal-label-small text-muted mb-0">No. of samples:</label>
                                            <input type="number" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.no_of_samples">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="modal-label-small text-muted mb-0">Name of Analyst:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.name_of_analyst">
                                        </div>
                                    </div>
                                    <div class="row align-items-center mt-2">
                                        <div class="col-md-3">
                                            <label class="modal-label-small text-muted mb-0">Sample condition:</label>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.sample_condition_sealed" id="cond_sealed">
                                                <label class="custom-control-label modal-label-small" for="cond_sealed">Sealed</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.sample_condition_labelled" id="cond_labelled">
                                                <label class="custom-control-label modal-label-small" for="cond_labelled">Labelled</label>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.sample_condition_remark" placeholder="Remark">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <label class="modal-label-small text-muted mb-0">Signature:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.analyst_signature">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SAMPLE SCREENING -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>SAMPLE SCREENING</strong></div>
                                <div class="card-body py-2">
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.screening_date">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="modal-label-small text-muted mb-0">Method:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.screening_method">
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-md-3">
                                            <label class="modal-label-small text-muted mb-0">Sample type:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.screening_sample_type_blood" id="ss_blood">
                                                <label class="custom-control-label modal-label-small" for="ss_blood">Blood</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.screening_sample_type_object_with_blood" id="ss_obj_blood">
                                                <label class="custom-control-label modal-label-small" for="ss_obj_blood">Object with blood</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.screening_sample_type_semen" id="ss_semen">
                                                <label class="custom-control-label modal-label-small" for="ss_semen">Semen</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.screening_sample_type_object_with_semen" id="ss_obj_semen">
                                                <label class="custom-control-label modal-label-small" for="ss_obj_semen">Object with semen</label>
                                            </div>
                                            <div class="form-inline mt-1">
                                                <label class="modal-label-small text-muted mr-2">Others</label>
                                                <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.screening_sample_type_others">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <label class="modal-label-small text-muted mb-0">Results:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <select class="form-control form-control-sm form-control-modern w-auto d-inline-block mr-2" wire:model="caseFormData.screening_results_1">
                                                <option value="">-- Result 1 --</option>
                                                <option value="Positive">Positive</option>
                                                <option value="Negative">Negative</option>
                                                <option value="N/A">N/A</option>
                                            </select>
                                            <select class="form-control form-control-sm form-control-modern w-auto d-inline-block" wire:model="caseFormData.screening_results_2">
                                                <option value="">-- Result 2 --</option>
                                                <option value="Positive">Positive</option>
                                                <option value="Negative">Negative</option>
                                                <option value="N/A">N/A</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SAMPLE EXTRACTION -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>SAMPLE EXTRACTION</strong></div>
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Extraction Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.extraction_date">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="modal-label-small text-muted mr-3 mb-0">Method:</label>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.extraction_method_chelex" id="se_chelex">
                                                <label class="custom-control-label modal-label-small" for="se_chelex">Chelex</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.extraction_method_prepfiler" id="se_prepfiler">
                                                <label class="custom-control-label modal-label-small" for="se_prepfiler">Prepfiler</label>
                                            </div>
                                            <div class="form-inline mt-1 d-inline-block">
                                                <label class="modal-label-small text-muted mr-2">Other</label>
                                                <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.extraction_method_other">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QUANTIFICATION -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>QUANTIFICATION</strong></div>
                                <div class="card-body py-2">
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Quantification Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.quantification_date">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="modal-label-small text-muted mr-2 mb-0">No. of cycles:</label>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.quantification_no_of_cycles_40" id="q_cycles_40">
                                                <label class="custom-control-label modal-label-small" for="q_cycles_40">40</label>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="modal-label-small text-muted mb-0">Remarks:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.quantification_remarks">
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <label class="modal-label-small text-muted mr-3 mb-0">Kit used:</label>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.quantification_kit_used_quant_trio" id="q_kit_quant">
                                                <label class="custom-control-label modal-label-small" for="q_kit_quant">Quant trio</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- POLYMERASE CHAIN REACTION (PCR) -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>POLYMERASE CHAIN REACTION (PCR)</strong></div>
                                <div class="card-body py-2">
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Amplification Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.pcr_amplification_date">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mr-2 mb-0">No. of cycles:</label>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_no_of_cycles_28" id="pcr_28">
                                                <label class="custom-control-label modal-label-small" for="pcr_28">28</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_no_of_cycles_29" id="pcr_29">
                                                <label class="custom-control-label modal-label-small" for="pcr_29">29</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_no_of_cycles_30" id="pcr_30">
                                                <label class="custom-control-label modal-label-small" for="pcr_30">30</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_no_of_cycles_32" id="pcr_32">
                                                <label class="custom-control-label modal-label-small" for="pcr_32">32</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Remarks:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.pcr_remarks">
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <label class="modal-label-small text-muted mr-3 mb-0">Kit used:</label>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_kit_used_identifiler_plus" id="pcr_identifiler">
                                                <label class="custom-control-label modal-label-small" for="pcr_identifiler">Identifiler plus</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_kit_used_globalfiler" id="pcr_globalfiler">
                                                <label class="custom-control-label modal-label-small" for="pcr_globalfiler">Globalfiler</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.pcr_kit_used_yfiler_plus" id="pcr_yfiler">
                                                <label class="custom-control-label modal-label-small" for="pcr_yfiler">Yfiler plus</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- INJECTION & INTERPRETATION -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>INJECTION & INTERPRETATION</strong></div>
                                <div class="card-body py-2">
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Injection Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.injection_date">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Run ID:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.injection_run_id">
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <label class="modal-label-small text-muted mr-2 mb-0">Instrument:</label>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.injection_instrument_3500" id="inj_3500">
                                                <label class="custom-control-label modal-label-small" for="inj_3500">3500</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-2"><label class="modal-label-small text-muted font-weight-bold">Results</label></div>
                                        <div class="col-md-10">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="modal-label-small text-muted mb-0 d-block">Positive</label>
                                                    <select class="form-control form-control-sm form-control-modern" wire:model="caseFormData.injection_result_positive">
                                                        <option value="">-</option><option value="Pass">Pass</option><option value="Fail">Fail</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="modal-label-small text-muted mb-0 d-block">Negative</label>
                                                    <select class="form-control form-control-sm form-control-modern" wire:model="caseFormData.injection_result_negative">
                                                        <option value="">-</option><option value="Pass">Pass</option><option value="Fail">Fail</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="modal-label-small text-muted mb-0 d-block">Ladder</label>
                                                    <select class="form-control form-control-sm form-control-modern" wire:model="caseFormData.injection_result_ladder">
                                                        <option value="">-</option><option value="Pass">Pass</option><option value="Fail">Fail</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="modal-label-small text-muted mb-0 d-block">Blank</label>
                                                    <select class="form-control form-control-sm form-control-modern" wire:model="caseFormData.injection_result_blank">
                                                        <option value="">-</option><option value="Pass">Pass</option><option value="Fail">Fail</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3 align-items-center">
                                        <div class="col-md-2">
                                            <label class="modal-label-small text-muted font-weight-bold mb-0">Run:</label>
                                        </div>
                                        <div class="col-md-10">
                                            <select class="form-control form-control-sm form-control-modern w-auto" wire:model="caseFormData.injection_run">
                                                <option value="">- Select -</option><option value="Pass">Pass</option><option value="Fail">Fail</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- REPORTING -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>REPORTING</strong></div>
                                <div class="card-body py-2">
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Draft report Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.reporting_draft_report_date">
                                        </div>
                                        <div class="col-md-8 mt-4">
                                            <div class="custom-control custom-checkbox custom-control-inline mr-4">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.reporting_reviewed" id="rep_reviewed">
                                                <label class="custom-control-label modal-label-small" for="rep_reviewed">Reviewed</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.reporting_corrected" id="rep_corrected">
                                                <label class="custom-control-label modal-label-small" for="rep_corrected">Corrected</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">
                                            <label class="modal-label-small text-muted mb-0">Report attachments:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.reporting_attachment_real_time_data" id="rep_att_realtime">
                                                <label class="custom-control-label modal-label-small" for="rep_att_realtime">Real-time Data</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.reporting_attachment_converge" id="rep_att_converge">
                                                <label class="custom-control-label modal-label-small" for="rep_att_converge">Converge</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.reporting_attachment_statistical_analysis" id="rep_att_statistical">
                                                <label class="custom-control-label modal-label-small" for="rep_att_statistical">Statistical analysis</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="modal-label-small text-muted mb-0">Remarks:</label>
                                            <textarea class="form-control form-control-modern" rows="2" wire:model="caseFormData.reporting_remarks"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- AUTHENTICATION -->
                            <div class="card mb-3">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>AUTHENTICATION</strong></div>
                                <div class="card-body py-2">
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <label class="modal-label-small text-muted mb-0">Reviewer's comments on the report:</label>
                                            <textarea class="form-control form-control-modern" rows="2" wire:model="caseFormData.reviewer_comments"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Name:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.reviewer_name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Signature:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.reviewer_signature">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Date:</label>
                                            <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.reviewer_date">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MANAGER'S REVIEW & VERIFICATION -->
                            <div class="card mb-2">
                                <div class="card-header py-2" style="background-color: #f8f9fa;"><strong>MANAGER'S REVIEW & VERIFICATION</strong></div>
                                <div class="card-body py-2">
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Manager report review:</label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.manager_review_technical" id="mgr_rev_tech">
                                                <label class="custom-control-label modal-label-small" for="mgr_rev_tech">Technical review</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.manager_review_administrative" id="mgr_rev_admin">
                                                <label class="custom-control-label modal-label-small" for="mgr_rev_admin">Administrative review</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-md-4">
                                            <label class="modal-label-small text-muted mb-0">Manager's comments on the report:</label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.manager_comments_verified" id="mgr_com_ver">
                                                <label class="custom-control-label modal-label-small" for="mgr_com_ver">Verified</label>
                                            </div>
                                            <div class="custom-control custom-checkbox custom-control-inline mr-4">
                                                <input class="custom-control-input" type="checkbox" wire:model="caseFormData.manager_comments_not_verified" id="mgr_com_not_ver">
                                                <label class="custom-control-label modal-label-small" for="mgr_com_not_ver">Not Verified</label>
                                            </div>
                                            <div class="form-inline d-inline-block">
                                                <label class="modal-label-small text-muted mr-2">Date:</label>
                                                <input type="date" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.manager_date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="modal-label-small text-muted mb-0">Manager's Name:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.manager_name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="modal-label-small text-muted mb-0">Signature:</label>
                                            <input type="text" class="form-control form-control-sm form-control-modern" wire:model="caseFormData.manager_signature">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary-modern btn-sm" wire:click="$set('verificationActiveTab', 'assign_approvers')">Next: Assign Approvers <i class="mdi mdi-arrow-right"></i></button>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade @if($verificationActiveTab == 'assign_approvers') show active @endif">
                <div class="alert alert-secondary" style="background-color: #e2e6ea; border-color: #d6d8db; color: #383d41;">
                   <small><i class="mdi mdi-information-outline"></i> Select the approvers for each laboratory below.</small>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert" style="font-size: 0.875rem; font-weight: 400;">
                        <i class="mdi mdi-alert-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-muted small font-weight-bold modal-label-small">Laboratory</th>
                                <th class="text-muted small font-weight-bold modal-label-small">Assign Approver</th>
                                <th class="text-muted small font-weight-bold modal-label-small">Title</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background-color: #f8f9fa;">
                                <td class="align-middle"><strong>Technical Reviewer</strong> <br><small class="text-muted">(Overall Signatory)</small></td>
                                <td>
                                    <select class="form-control form-control-sm form-control-modern" wire:model="verificationData.technical_reviewer_id">
                                        <option value="">Select Technical Reviewer</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm form-control-modern" value="Technical Signatory" readonly>
                                </td>
                            </tr>
                            @foreach($this->getBatchLabs() as $lab)
                                <tr>
                                    <td class="align-middle">{{ $lab->name }}</td>
                                    <td>
                                        <select class="form-control form-control-sm form-control-modern" wire:model="verificationData.approver_user.{{ $lab->id }}">
                                            <option value="">Select Approver</option>
                                            @foreach($this->getLabManagersForLab($lab->id) as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm form-control-modern" wire:model="verificationData.title.{{ $lab->id }}" placeholder="Verification Title">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="form-group mt-3">
                    <label class="text-muted font-weight-bold small modal-label-small">Report Status Level</label>
                    <select class="form-control form-control-modern" wire:model="verificationData.level">
                        <option value="0">Final Report</option>
                        <option value="1">Preliminary Report</option>
                        <option value="2">Draft Report</option>
                    </select>
                </div>

                <div class="form-group mt-3">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="has_method_deviation_livewire"
                            wire:model="verificationData.has_method_deviation"
                            onchange="document.getElementById('method_deviation_reason_livewire_group').style.display = this.checked ? 'block' : 'none';"
                        >
                        <label class="form-check-label text-muted small" for="has_method_deviation_livewire" style="font-size: 0.85rem;">
                            Deviations from method
                        </label>
                    </div>
                </div>

                <div class="form-group" id="method_deviation_reason_livewire_group" style="display:none;">
                    <label class="text-muted font-weight-bold small modal-label-small">Reason for Deviation</label>
                    <textarea
                        class="form-control form-control-modern"
                        wire:model="verificationData.method_deviation_reason"
                        placeholder="Describe the deviation from method..."></textarea>
                </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-modern">
                @if($verificationActiveTab == 'assign_approvers')
                <button type="button" class="btn btn-secondary-modern btn-sm mr-auto" wire:click="$set('verificationActiveTab', 'case_file_review')"><i class="mdi mdi-arrow-left"></i> Back</button>
                @endif
                <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showVerificationModal', false)">Close</button>
                @if($verificationActiveTab == 'assign_approvers')
                <button type="button" class="btn btn-primary-modern btn-sm" wire:click="moveToVerification">Submit</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Send for Approval Modal --}}
@if($showApprovalModal)
<div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
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
                    <span class="p-2">Confirm all approvers have approved before sending the report for approval</span>
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
                    <span class="p-2">Confirm you want to send this {{ $batch->batch_code }} batch for approval</span>
                </div>

                <div class="form-group">
                    <label class="text-muted font-weight-bold small modal-label-small">Title</label>
                    <input type="text" 
                           class="form-control form-control-modern" 
                           wire:model="approvalData.title" 
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
                    <textarea class="form-control form-control-modern" 
                              wire:model="approvalData.comments" 
                              placeholder="Comments..." 
                              rows="3"></textarea>
                </div>

                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           wire:model="approvalData.notification" 
                           id="approval-notification">
                    <label class="form-check-label" for="approval-notification">
                        Send Email Notification
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" 
                           type="checkbox" 
                           wire:model="approvalData.send_message" 
                           id="approval-sms">
                    <label class="form-check-label" for="approval-sms">
                        Send SMS
                    </label>
                </div>
            </div>
            <div class="modal-footer modal-footer-modern">
                @if($this->verificationApprovalStatus == 0)
                <button type="button" 
                        class="btn btn-primary-modern btn-sm" 
                        wire:click="sendForApproval">
                    <i class="mdi mdi-thumb-up"></i> Yes Proceed
                </button>
                @endif
                <button type="button" 
                        class="btn btn-secondary-modern btn-sm" 
                        wire:click="$set('showApprovalModal', false)">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
{{-- [modals end] --}}
</div>
