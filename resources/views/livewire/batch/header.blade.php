<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body">
        <h4 class="p-2 mb-0">
            <span class="float-left">
                <i class="mdi mdi-layers-triple"></i>
                @if(isset($batch->id) && $batch->prelim_report_status == 1)
                    <span class="badge badge-info p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">Prelim</span>
                @elseif(isset($batch->id) && $batch->prelim_report_status == 2)
                    <span class="badge badge-info p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">Draft</span>
                @else
                    <span class="badge badge-pill bg-white pt-2 pb-2 pr-3 pl-3" style="font-weight: 400!important">{!! isset($batch->priority) && $batch->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $batch->priority ?? '' }}</span>
                @endif
               {{ isset($batch->batch_code) ? $batch->batch_code.' Batch Info' : 'New Batch' }} 
                <small class="text-muted">{!! isset($batch->batch_code) ? '<i class="mdi mdi-sitemap"></i> '.$batch->tracking_stage()->name : '' !!}</small>
            </span>
            
            @if(isset($batch->id))
            <a href="{{ route('batch-worksheets', ['batch' => $batch->id]) }}" 
               class="btn btn-sm ml-2 btn-info float-right mr-2" 
               style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;">
                <i class="mdi mdi-clipboard-text"></i> Worksheets
            </a>
            @endif
            
            <div class="btn-group float-right">
                <button type="button" class="btn btn-sm btn-white dropdown-toggle" 
                        style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;" 
                        type="button" id="dropdownMenuButton" 
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @if(isset($batch->id))
                        @if($batch->status == 'Finished Sample')
                        <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                        
                        <li>
                            <a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download
                                COA</a>
                        </li>
                        
                        @endif
                        @if(!in_array($batch->status,array("Completed")))
                            <li>
                                <a target="_blank" href="{{route('generateCustomerFocusIndex',['batch_id'=>$batch->id])}}" class="btn btn-sm dropdown-item"><i class="mdi mdi-eye mr-2"></i> View Sample Submission Form</a>
                            </li>
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
                        @endif
                    
                    @if(isset($batch->status) && $batch->status=="Samples In Lab" && Auth::user()->is_client == 0 && $status == 'Samples In Lab')
                    <li>
                        <span class="btn btn-sm dropdown-item" wire:click="$set('showBulkUpdateModal', true)" style="cursor: pointer;">
                            <i class="mdi mdi-database-edit mr-2"></i> Update Sample Data
                        </span>
                    </li>
                    <li>
                        <span class="btn btn-sm dropdown-item" wire:click="$set('showVerificationModal', true)" style="cursor: pointer;">
                        <i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Verification
                        </span>
                    </li>
                        <li class="">
                        <span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
                        
                        </li>
                        
                        @if( $batch->prelim_report_status != 0 && $status == 'Sample Verification')
                        <li>
                            <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-for-approval-modal">
                                <i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval
                            </span>
                        </li>
                        <li>
                            <span class="btn btn-sm dropdown-item"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
                        </li>
                        <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                            @if($batch->invoice_number != '')
                            <li>
                                <a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download
                                    COA</a>
                            </li>
                            @else
                            <li>
                                <span class="dropdown-item btn btn-sm" data-target="#download-coa-invoice-exception" data-toggle="modal"><i
                                        class="mdi mdi-download mr-2"></i> Download COA</span>
                            </li>
                            @endif
                            @if($batch->invoice_number == '' )
                                <li>
                                    <span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice" data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span>
                                </li>
                            @endif
                        @endif                        
                        @endif
                        
                        @if(isset($batch->status) && in_array($batch->status, array("Samples In Lab","Sample Verification","Sample Approval")) && Auth::user()->is_client == 0 && $batch->prelim_report_status != 0)
                            
                            @if(auth()->user()->checkVerifyLabSampleRole() && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 2 && $status == 'Sample Verification')
                            <li>
                                <span class="btn btn-sm dropdown-item"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
                            </li>
                            @endif
                            @if(auth()->user()->checkVerifyLabSampleRole() && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 1 && $status == 'Sample Verification')
                            <li>
                                <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-for-approval-modal">
                                    <i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval 
                                </span>
                            </li>
                            @endif
                            <li>
                                <span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
                                
                            </li>

                        @endif
                        @if(isset($batch->status) && $batch->status == 'Samples In Lab' && $batch->prelim_report_status == 2)
                        <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                            @if($batch->invoice_number != '')
                            <li>
                                <a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download
                                    COA</a>
                            </li>
                            @else
                            <li>
                                <span class="dropdown-item btn btn-sm" data-target="#download-coa-invoice-exception" data-toggle="modal"><i
                                        class="mdi mdi-download mr-2"></i> Download COA</span>
                            </li>
                            @endif
                            @if($batch->invoice_number == '' )
                                <li>
                                    <span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice" data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span>
                                </li>
                            @endif
                        @endif
                        @if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")) && Auth::user()->is_client == 0)
                        
                            @if($batch->status == "Sample Verification")
                                @if($notCaptured->count() == 0)
                                    @if(auth()->user()->checkVerifyLabSampleRole())
                                    <li>
                                        <span class="dropdown-item"><hr/></span>
                                    </li>
                                    <li>
                                        <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-for-approval-modal">
                                            <i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval
                                        </span>
                                    </li>
                                    @endif
                                    <li class="hidden">
                                        <span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
                                        
                                    </li>
                                @else
                                    <li class="hidden">
                                        <span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report {{$notCaptured->count()}}</span>
                                    </li>
                                @endif
                                @if(in_array($batch->status,["Sample Approval","Reports for Collection","Reports In Payment"]))
                                <li>
                                    <span class="btn btn-sm dropdown-item"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
                                </li>
                                
                                @endif
                            @endif
                            
                            @if(in_array($batch->status,["Sample Approval","Reports for Collection","Reports In Payment"]) && $batch->batch_report_url != '')
                                <?php $reportpath = '/storage'.$batch->batch_report_url; ?>
                                @if($batch->invoice_number != '')
                                <li>
                                    <a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download
                                        COA</a>
                                </li>
                                @else
                                <li>
                                    <span class="dropdown-item btn btn-sm" data-target="#download-coa-invoice-exception" data-toggle="modal"><i
                                            class="mdi mdi-download mr-2"></i> Download COA</span>
                                </li>
                                @endif
                                @if($batch->invoice_number == '' )
                                <li>
                                    <span class="dropdown-item btn btn-sm" data-target="#add-batch-invoice" data-toggle="modal"><i class="mdi mdi-cash-plus mr-2"></i> Add Invoice Details</span>
                                </li>
                                @endif
                            
                            @endif 
                            @if($batch->status == "Sample Approval")
                                <li>
                                    <span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
                                </li>
                                
                                @if(in_array($batch->status,["Sample Approval","Reports for Collection","Reports In Payment"]))
                                <li>
                                    <span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
                                </li>
                                @endif

                                @if ($batch->batch_report_url != '' )
                                    @if($batch->is_qc_batch == 0)
                                        <li>
                                            <span class="dropdown-item"><hr/></span>
                                        </li>
                                        <li>
                                            <span class="btn btn-sm dropdown-item" data-target="#send-to-payments-modal" data-toggle="modal" title="Send for  Payment"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Payment</span>
                                        </li>
                                        <li>
                                            <span class="btn btn-sm dropdown-item" data-target="#send-to-email-modal" data-toggle="modal" title="Send for Collection"><i class="mdi mdi-email mr-2"></i> Send for Collection</span>
                                        </li>

                                    @else
                                        <li>
                                            <span class="dropdown-item"><hr/></span>
                                        </li>
                                        <li>
                                            <span class="btn btn-sm dropdown-item" data-target="#mark-complete" data-toggle="modal" title="Send for  Payment"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Mark as Complete</span>
                                        </li>
                                    @endif

                                    
                                @endif
                                
                                
                                
                                

                            @endif
                            @if(isset($batch->status) && $batch->status == 'Reports In Payment')
                                <li>
                                    <span class="btn btn-sm dropdown-item" data-target="#send-to-email-modal" data-toggle="modal" title="Send for Collection"><i class="mdi mdi-email mr-2"></i> Send for Collection</span>
                                </li>
                            @endif
                            @if($batch->status == 'Reports In Payment' || $batch->status == 'Reports for Collection')
                                <li>
                                    <span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
                                </li>
                            @endif
                        @endif
                </div>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-modern">
            <div class="modal-header modal-header-modern">
                <h5 class="modal-title modal-title-modern">Move Batch to Verification</h5>
                <button type="button" class="close" wire:click="$set('showVerificationModal', false)">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-modern">
                <div class="alert alert-secondary" style="background-color: #e2e6ea; border-color: #d6d8db; color: #383d41;">
                   <small><i class="mdi mdi-information-outline"></i> Select the approvers for each lab section below.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-muted small font-weight-bold modal-label-small">Section</th>
                                <th class="text-muted small font-weight-bold modal-label-small">Assign Approver</th>
                                <th class="text-muted small font-weight-bold modal-label-small">Title</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sectionApprovers as $sa)
                                <tr>
                                    <td class="align-middle">{{ $sa->lab_section_name }}</td>
                                    <td>
                                        <select class="form-control form-control-sm form-control-modern" wire:model="verificationData.approver_user.{{ $sa->lab_section_id }}">
                                            <option value="">Select Approver</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm form-control-modern" wire:model="verificationData.title.{{ $sa->lab_section_id }}" placeholder="Verification Title">
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
            </div>
            <div class="modal-footer modal-footer-modern">
                <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showVerificationModal', false)">Close</button>
                <button type="button" class="btn btn-primary-modern btn-sm" wire:click="moveToVerification">Submit</button>
            </div>
        </div>
    </div>
</div>
@endif
            
        </h4>
    </div>
</div>
