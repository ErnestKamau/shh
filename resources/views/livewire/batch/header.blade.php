<div>
    <h4 class="pt-4 pr-4 pl-4 pb-3">
        <i class="mdi mdi-layers-triple"></i>
        @if(isset($batch->id) && $batch->prelim_report_status == 1)
            <span class="badge badge-info p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">Prelim</span>
        @elseif(isset($batch->id) && $batch->prelim_report_status == 2)
             <span class="badge badge-info p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">Draft</span>
        @else
         <span class="badge badge-pill bg-white pt-2 pb-2 pr-3 pl-3" style="font-weight: 400!important">{!! isset($batch->priority) && $batch->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $batch->priority ?? '' }}</span>
         @endif
        {{ isset($batch->batch_code) ? $batch->batch_code.' Batch Info' : 'New Batch' }} <small class="text-muted"> {!! isset($batch->batch_code) ? '<i class="mdi mdi-sitemap"></i> '.$batch->tracking_stage()->name : '' !!}</small>
        
        @if(isset($batch->id))
        <a href="{{ route('batch-worksheets', ['batch' => $batch->id]) }}" class="btn btn-sm ml-2 btn-info float-right mr-2" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;">
            <i class="mdi mdi-clipboard-text"></i> Worksheets
        </a>
        @endif
        <div class="btn-group float-right">
            <button type="button" class="btn btn-sm bg-white dropdown-toggle" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-schedule-analysis"><i class="mdi mdi-email-send mr-2"></i> Send Schedule of Analysis</span>
                        </li>
                        @endif
                        <li>
                            <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-payment-reminder"><i class="mdi mdi-email-send mr-2"></i> Send Payment Reminder</span>
                        </li>
                        @endif
                    @endif
                
                @if(isset($batch->status) && $batch->status=="Samples In Lab" && Auth::user()->is_client == 0 && $status == 'Samples In Lab')
                <li>
                    <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#bulk-update-samples-modal">
                        <i class="mdi mdi-database-edit mr-2"></i> Update Sample Data
                    </span>
                </li>
                <li>
                    <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-to-verification-modal">
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
        
    </h4>
</div>
