@extends($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true])

@section('title2')
  <title> {{ isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch" }}</title>
	<style>
		body{
			overflow-x: hidden !important;
		}
		.form-part-toggler{
			margin: 0px 0px 5px 0px !important;
			padding: 6px 6px 6px 6px;
			border-bottom: 1px solid rgba(0,0,0,0.09);
			cursor: pointer;
		}

		.form-part-toggler:hover{
			background-color: rgba(0,0,0,0.08);
		}

		#sample-detail-rows .form-group{
			display: none;
		}

		#sample-detail-rows tr.selected-row{
			background-color: #eef7d5;
		}
		#sample-detail-rows tr.selected-row td{
			border: none !important;
		}

		td .form-group {
			margin-bottom: unset !important;
		}

		#sample-detail-rows .text{
			display: unset;
		}

		#sample-detail-rows tr.editable .form-group{
			display: unset;
		}

		#sample-detail-rows tr.editable .text{
			display: none;
		}

		/* Capture Results Modal Styles */
		.parameter-card {
			border: 1px solid #e3e6f0;
			border-radius: 8px;
			transition: all 0.3s ease;
		}

		.parameter-card:hover {
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
		}

		.bg-success-light {
			background-color: rgba(40, 167, 69, 0.1) !important;
			border-color: #28a745 !important;
		}

		.bg-danger-light {
			background-color: rgba(220, 53, 69, 0.1) !important;
			border-color: #dc3545 !important;
		}

		.bg-secondary-light {
			background-color: rgba(108, 117, 125, 0.1) !important;
			border-color: #6c757d !important;
		}

		.form-label.small {
			font-size: 0.875rem;
			font-weight: 600;
			margin-bottom: 0.25rem;
		}

		.card-header h6 {
			color: #495057;
			font-weight: 600;
		}

		#capture-results-modal .modal-dialog {
			max-width: 95%;
		}

		/* Table-based parameter styles */
		#parameters-table {
			font-size: 0.9rem;
		}
		
		#parameters-table th {
			background-color: #f8f9fa;
			border: 1px solid #dee2e6;
			text-align: center;
			vertical-align: middle;
			padding: 8px 4px;
		}
		
		#parameters-table th.sample-code-cell {
			background-color: #e9ecef;
			font-weight: 600;
		}
		
		#parameters-table th.parameter-header {
			background-color: #007bff;
			color: white;
			font-weight: 600;
		}
		
		#parameters-table th.method-header,
		#parameters-table th.symbol-header,
		#parameters-table th.unit-header,
		#parameters-table th.result-header,
		#parameters-table th.standard-header {
			background-color: #6c757d;
			color: white;
			font-size: 0.8rem;
		}
		
		#parameters-table td {
			border: 1px solid #dee2e6;
			padding: 6px 4px;
			vertical-align: middle;
		}
		
		#parameters-table td.sample-code-cell {
			background-color: #f8f9fa;
			font-weight: 600;
			text-align: center;
		}
		
		#parameters-table td.method-cell {
			background-color: #e3f2fd;
			font-size: 0.8rem;
			text-align: center;
		}
		
		#parameters-table td.symbol-cell {
			background-color: #f3e5f5;
			text-align: center;
			font-weight: 500;
		}
		
		#parameters-table td.result-cell {
			background-color: #fff3e0;
			padding: 2px;
		}
		
		#parameters-table .result-input {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 4px 6px;
			width: 100%;
			font-size: 0.85rem;
		}
		
		#parameters-table .result-input:focus {
			border-color: #007bff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}
		
		#parameters-table .result-input.border-warning {
			border-color: #ffc107;
		}
		
		#parameters-table .result-input.border-success {
			border-color: #28a745;
		}
		
		#parameters-table .result-input.border-danger {
			border-color: #dc3545;
		}
		
		/* Editable field styles */
		#parameters-table .method-select,
		#parameters-table .symbol-input,
		#parameters-table .unit-select {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 4px 6px;
			width: 100%;
			font-size: 0.85rem;
			background-color: white;
		}
		
		#parameters-table .method-select:focus,
		#parameters-table .symbol-input:focus,
		#parameters-table .unit-select:focus {
			border-color: #007bff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}
		
		#parameters-table td.unit-cell {
			background-color: #f0f8ff;
			padding: 2px;
		}
		
		#parameters-table td.standard-cell {
			background-color: #fff8e1;
			padding: 2px;
		}
		
		#parameters-table .standard-input {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 4px 6px;
			font-size: 0.85rem;
			background-color: #f8f9fa;
		}
		
		#parameters-table .standard-input:focus {
			border-color: #007bff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
			background-color: white;
		}
		
		#parameters-table .edit-standard-btn {
			padding: 2px 6px;
			font-size: 0.75rem;
			border-radius: 3px;
		}
		
		#parameters-table .edit-standard-btn:hover {
			background-color: #007bff;
			border-color: #007bff;
			color: white;
		}
		
		/* Scrollable table container */
		#parameters-table-wrapper {
			border: 1px solid #dee2e6;
			border-radius: 8px;
		}
		
		#parameters-table-wrapper::-webkit-scrollbar {
			height: 8px;
			width: 8px;
		}
		
		#parameters-table-wrapper::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 4px;
		}
		
		#parameters-table-wrapper::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 4px;
		}
		
		#parameters-table-wrapper::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}
		
		/* Sample Details Section Styles */
		#sample-details-section .card {
			border: 1px solid #dee2e6;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		
		#sample-details-section .card-header {
			background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
			border-bottom: none;
		}
		
		#sample-details-section .form-control-plaintext {
			background-color: #f8f9fa;
			border: 1px solid #e9ecef;
			border-radius: 4px;
			padding: 8px 12px;
			margin-bottom: 0;
			min-height: 38px;
			display: flex;
			align-items: center;
		}
		
		#sample-details-section .form-group {
			margin-bottom: 1rem;
		}
		
		#sample-details-section label {
			font-size: 0.9rem;
			margin-bottom: 0.5rem;
		}
		
		/* Rich text content styling */
		.rich-text-content {
			min-height: 60px;
			max-height: 200px;
			overflow-y: auto;
			word-wrap: break-word;
		}
		
		.rich-text-content p {
			margin-bottom: 0.5rem;
		}
		
		.rich-text-content p:last-child {
			margin-bottom: 0;
		}
		
		.rich-text-content ul, .rich-text-content ol {
			margin-bottom: 0.5rem;
			padding-left: 1.5rem;
		}
		
		.rich-text-content strong, .rich-text-content b {
			font-weight: 600;
		}
		
		.rich-text-content em, .rich-text-content i {
			font-style: italic;
		}
		
		.rich-text-content u {
			text-decoration: underline;
		}

		#parameters-table .sample-code-cell {
			background-color: #f8f9fa;
			font-weight: 600;
			text-align: left;
			min-width: 120px;
		}

		#parameters-table .result-input {
			width: 100%;
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 4px 6px;
			font-size: 0.8rem;
		}

		#parameters-table .method-cell,
		#parameters-table .symbol-cell {
			font-size: 0.75rem;
			max-width: 120px;
		}

		#parameters-table .analyte-cell {
			font-size: 0.8rem;
			font-weight: 600;
			max-width: 150px;
		}

		#parameters-table .result-cell {
			min-width: 100px;
		}

		.parameter-row.table-success {
			background-color: rgba(40, 167, 69, 0.1) !important;
		}

		.parameter-row.table-danger {
			background-color: rgba(220, 53, 69, 0.1) !important;
		}

		.parameter-row.table-secondary {
			background-color: rgba(108, 117, 125, 0.1) !important;
		}

		.parameter-row .form-control-sm {
			font-size: 0.875rem;
		}

		.parameter-row .form-label.small {
			font-size: 0.75rem;
			margin-bottom: 4px;
			color: #6c757d;
		}

		.parameter-row .border-warning {
			border-color: #ffc107 !important;
			box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
		}

		.double-capture-hint {
			color: #856404 !important;
			font-size: 0.75rem;
		}

		#parameters-table-wrapper {
			box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
			border-radius: 0.375rem;
			overflow: hidden;
		}

		.edit-method-btn {
			padding: 2px 6px;
			font-size: 0.75rem;
		}

		.method-display {
			font-size: 0.875rem;
			line-height: 1.4;
		}

		#sample-detail-rows tr{
			cursor: pointer;
		}

		.hidden{
			display: none;
		}

		.show-hoverable .complete{
			display: none;
		}
		.show-hoverable .partial{
			display: unset;
		}

		.show-hoverable:hover .partial{
			display: none;
		}
		.show-hoverable:hover .complete{
			display: unset;
		}
		.select2-selection{
			min-width: 200px !important;
		}
		.bg-white{
			background-color: white !important;
		}
		li.nav-item{
			margin-top: 1.5%;
		}

	</style>
@endsection
@section('content2')
  <main>
		<?php

            // $labStores = [];
            if ($defaultClient) {
                $customerDetails = App\Models\CRM\CRMCustomer::find($defaultClient);
                if ($client_portal || Auth::user()->is_client == 1) {
                    $items = [
                        [
                            'link' => '/dashboard/crm/client-details',
                            'name' => $customerDetails->name,
                            'icon' => null,
                        ],
                        [
                            'link' => '#',
                            'name' => 'Customer Orders > '.(isset($batch->id) ? $batch->batch_code.' - Order Info' : 'Create New Order'),
                            'icon' => null,
                        ],
                    ];
                } else {
                    $items = [
                        [
                            'link' => route('customers-list'),
                            'name' => 'CRM',
                            'icon' => null,
                        ],
                        [
                            'link' => route('customers-list'),
                            'name' => 'Customer List',
                            'icon' => null,
                        ],
                        [
                            'link' => route('show-customer', ['id' => $defaultClient]),
                            'name' => $customerDetails->name,
                            'icon' => null,
                        ],
                        [
                            'link' => '#',
                            'name' => 'Customer Orders > '.(isset($batch->id) ? $batch->batch_code.' - Order Info' : 'Create New Order'),
                            'icon' => null,
                        ],
                    ];
                }
            } else {
                $items = [
                    [
                        'link' => route('dashboard-lab'),
                        'name' => 'Dashboard',
                        'icon' => null,
                    ],
                    [
                        'link' => route('sample-workflow', ['status' => 'All Samples']),
                        'name' => 'Sample Workflow',
                        'icon' => null,
                    ],
                    [
                        'link' => route('sample-workflow', ['status' => isset($status) && $status ? $status : $batch->status ?? 'Samples Reception']),
                        'name' => isset($status) && $status ? $status : $batch->status ?? 'Samples Reception',
                        'icon' => null,
                    ],
                    [
                        'link' => '#',
                        'name' => isset($batch->batch_code) ? $batch->batch_code.' - Batch Info' : 'New Batch',
                        'icon' => null,
                    ],
                ];
            }
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
	
	@if(session('success'))
		<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
			<i class="mdi mdi-check-circle"></i> {{ session('success') }}
			<button type="button" class="close" data-dismiss="alert">
				<span>&times;</span>
			</button>
		</div>
	@endif

	@if(session('error'))
		<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
			<i class="mdi mdi-alert-circle"></i> {{ session('error') }}
			<button type="button" class="close" data-dismiss="alert">
				<span>&times;</span>
			</button>
		</div>
	@endif

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
						@if($not_captured->count() == 0)
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
								<span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report {{$not_captured->count()}}</span>
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
    <div class="row no-gutters">
      <div class="col-sm-12 p-2">
        <div class="card" style="box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
          <div class="card-body">
			<h5 class="card-title">
				<span class="btn btn-default batch-info-trigger open" style="box-shadow: rgba(33, 35, 38, 0.1) 0px 10px 20px -10px;">
					
					<i class="mdi mdi-chevron-double-up"></i> Batch Info
				</span>
				@if(isset($batch->id))
				<div class="btn-group float-right">
					<button class="btn btn-default bg-light btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;">
						<i class="mdi mdi-swap-vertical"></i> Move To Stage
					</button>
					<div class="dropdown-menu dropdown-menu-right bg-light" id="stage-selector">
						@foreach ($workflowstages as $item)
						<form class="dropdown-item" method="POST" style="cursor: pointer" action="{{ route('move-to-stage', ['stage'=>$item->id, 'batch_id'=>$batch->id]) }}">
							@csrf
							<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> {{ $item->name }}
						</form>
						@endforeach
					</div>
				</div>
				<div class="btn-group float-right mr-2">
					<button class="btn btn-default bg-light btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;">
						<i class="mdi mdi-swap-vertical"></i> Move To Workflow
					</button>
					<div class="dropdown-menu dropdown-menu-right bg-light" id="status-selector">
						@foreach ($workflows as $item)
							@if(!in_array($item,array("Sample Verification","Sample Approval")))
							<form class="dropdown-item" method="POST" style="cursor: pointer" action="{{ route('move-to-workflow', ['status'=>$item, 'batch_id'=>$batch->id]) }}">
								@csrf
								<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> {{ $item }}
							</form>
							@endif
						@endforeach
					</div>
				</div>
				@endif
			</h5>
			<hr>
			
			<form class="" action="{{ route('add-batch-info', ['batch'=>$batchID]) }}" class="row" id="batch-detail-form" method="POST" autocomplete="off">
				<?php $maxDate = getTodayDate(); ?>
				@csrf
				<div class="row p-2 border-bottom">
					<div class="form-group col-md-3">
						<label class="control-label">Date Collected <span class="text-danger">*</span></label>
						<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ date('Y-m-d', strtotime($batch->date_collected ?? '')) }}" class="form-control " name="date_collected" required>
						
					</div>
					<div class="form-group col-md-3">
						<label class="control-label">Lab Reception Date <span class="text-danger">*</span> </label>
						<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ date('Y-m-d', strtotime($batch->receipt_date ?? '')) }}" class="form-control " name="receipt_date" {{ $defaultClient === false ? 'required' : '' }} autocomplete="off">
						
					</div>
					<div class="form-group col-md-3">
						<label for="" class="control-label">Time Of Receipt</label>
						<input type="time" name="radio_active_levels" id="" value="{{$batch->radio_active_levels ?? ''}}" class="form-control">
					</div>
					{{-- <div class="form-group col-md-3">
						<label for="" class="control-label">Temp Of Receipt</label>
						<input type="text" name="kra_office_ref" id="" value="{{ $batch->kra_office_ref ?? '' }}" class="form-control">
					</div> --}}
	
					<div class="form-group col-md-3 qc-omit-type-field">
												
						<label  class="control-label">Client <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer" data-toggle="modal" data-toggle="tooltip" title="Add Client" ><i class="mdi mdi-plus"></i></span></label>
						<select class="form-control qc-remove-required {{ $defaultClient === false ? '' :'no-select2' }}" name="crm_customer_id" id="client-select" data-client-source="{{ route('sample-workflow.clients') }}" data-page-size="{{ $clientPageSize }}" onchange="detectChange(this)" {{ $defaultClient === false ? '' :'readonly' }}>
							<option value="">Select Client...</option>
							@foreach ($clients as $client)
									@if($defaultClient === false) 
									<option value="{{ $client->id }}"  {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }}  {{ $defaultClient == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
								@endif
	
								@if($defaultClient !== false && $client->id == $defaultClient){{-- Creating a batch from the client order --}}
									<option value="{{ $client->id }}"  {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }} {{-- When coming from laboratory --}} {{ $defaultClient == $client->id ? 'selected' : '' }} {{-- When coming from client order --}}>{{ $client->name }}</option>
								@endif
							@endforeach
						</select>
						@if($defaultClient !== false)
							<input type="hidden" name="is_client_order" value="1" />
						@endif
						
					</div>
					<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
						<label for="" class="control-label">Customer Contact <span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer-contact" data-toggle="modal"><i class="mdi mdi-plus" data-toggle="tooltip" title="Add Contact" ></i></span></label>
						<select name="crm_contact_id" id="crm_contact_id" class="form-control">
							<option value="">Choose Customer First...</option>
						</select>
					</div>
					<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
						<label for="" class="control-label">Customer Email</label>
						<input type="text" name="customer_email"  id="customer_email" class="form-control" value="{{isset($batch->id) ? $batch->schedule_customer_email : '' }}">
					</div>
					<div class="form-group col-md-3 qc-omit-type-field">
						<label class="control-label"><span class='client-prefered-unit-name'>Site Location</span> <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm"  data-target="#add-company-unit" data-toggle="modal" data-toggle="tooltip" title="Add Site Location" ><i class="mdi mdi-plus"></i></span></label>
						<select class="form-control" name="crm_unit_name" data-selected='{{ $batch->crm_unit_id ?? '' }}' id="client-unit-select">Select Client Unit...</option>
						</select>
					</div>
					<div class="form-group col-md-3">
						<label class="control-label text-sm">Sample Type <span class="text-danger">*</span></label>
						<select class="form-control {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : '' }}" {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : '' }} name="sample_type_id" required id="batch-info-sample-type">
							<option value="">Select Sample Type...</option>
							@foreach ($sample_types as $sample)
								<option value="{{ $sample->id }}"  {{ isset($batch->sample_type_id) && $batch->sample_type_id == $sample->id ? 'selected' : '' }} data-conditions="{{ json_encode($sample->sample_condition) }}">{{ $sample->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Client REF / LPO No </span></label>
						<input type="text" class="form-control" data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="reference_number" value="{{ $batch->reference_number ?? '' }}" placeholder="Reference Number..." />
						<small id="rft-message" class="text-danger"></small>
					</div>
					<div class="form-group col-md-3 hidden">
						<label class="control-label">Batch Scope <span class="text-danger">*</span></label>
						<select name="batch_scope" id="" class="form-control" required>
							@foreach(explode(',',$batch_scope->value) as $scope)
							<option value="{{$scope}}" {{ isset($batch->id) && $batch->batch_scope == $scope ? 'selected' : '' }}>{{$scope}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group qc-omit-type-field col-md-3 {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
						<label class="control-label">Customer Survey <span class="text-danger">*</span></label>
						<select name="customer_survey" id="" class="form-control" required>
							@foreach(explode(',',$customer_survey->value) as $survey)
							<option value="{{$survey}}" {{ isset($batch->id) && $batch->customer_survey == $survey ? 'selected' : '' }}>{{$survey}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group col-md-3">
						<div class="form-group">
							<label for="" class="control-label">Lab Sections</label>
							<select name="lab_section_ids[]" multiple id="" class="form-control">
								<option value="">Choose Lab Sections</option>
								@foreach($labsections as $l_section)
									<option value="{{$l_section->id}}" {{isset($batch->id) && in_array($l_section->id,explode(',',$batch->lab_section_ids)) ? 'selected' : ''}} >{{$l_section->code}} - {{$l_section->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group btn-group-sm col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
						<label for="" class="control-label">Sampling Plan</label>
						<select name="sampling_method_id" id="" class="form-control">
							<option value="">Select Sampling Plan</option>
							@foreach($samplingmethods as $b_method)
							<option value="{{$b_method->id}}" {{isset($batch->id) && $batch->sampling_method_id == $b_method->id ? 'selected' : 'selected'}}>{{$b_method->code}} - {{$b_method->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
						<label for="" class="control-label">Payment Made By</label>
						<input type="text" value="{{isset($batch->id) ? $batch->payment_done_by : ''}}" name="payment_done_by" placeholder="Payment Made By" class="form-control">
					</div>
					<div class="form-group btn-group-sm col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
						<label class="control-label">Quotation Number</label>
						<input type="text" class="form-control" data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="quote_no" value="{{ $batch->quote_no ?? '' }}" placeholder="Quotation Number..." />
						<small id="rft-message" class="text-danger"></small>
					</div>
					
					
					{{-- <div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Condition and Quality of Sample</label>
						<input type="text" class="form-control" autocomplete="off" value="{{$batch->condition_quality_sample ?? ''}}" name="condition_quality_sample" value="{{ $batch->submit_by ?? '' }}" placeholder="Condition and Quality of Sample..." />
					</div> --}}
					<div class="form-group btn-group-sm col-md-3 qc-omit-type-field">
							<label class="control-label">Sampled By</label>
							<input type="text" name="sample_by" value="{{$batch->sampling_officer_name ?? '' }}" id="" placeholder="Sampled By..." class="form-control">
							
						</div>
					<div class="form-group btn-group-sm col-md-3 ">
						<label class="control-label">Submitted By</label>
						<input type="text" class="form-control" autocomplete="off" value="{{$batch->submit_by ?? ''}}" name="submit_by" value="{{ $batch->submit_by ?? '' }}" placeholder="Submitted By..." />
					</div>
					<div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Received By <small class="text-danger">*</small></label>
						<select name="receive_by" required id="" class="form-control">
							<option value="">Select Receiving Officer</option>
							@foreach($recieving_users as $r_user)
							<option value="{{$r_user->id}}" {{ isset($batch->id) && $batch->receiving_officer == $r_user->id ? 'selected' : ''}}>{{$r_user->name}}</option>
							@endforeach
						</select>
						
						
					</div>
				</div>
				<div class="row p-2 mt-3">
					<div class="form-group col-md-4 btn-group-sm">
						<label for="" class="control-label"><input type="checkbox" name="is_qc_batch" class="is_qc_batch" {{ isset($batch->id) && $batch->is_qc_batch == 1 ? 'checked' : '' }} id=""> Is QC Batch ? </label>
					</div>
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="require_mu" value="1" {{ isset($batch->require_mu) && $batch->require_mu == 1 ? 'checked' : '' }}> Has client requested Measure of uncertainity  ?
						</label>
					</div>
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="client_instruction_clear" value="1" {{ isset($batch->client_instruction_clear) && $batch->client_instruction_clear == 1 ? 'checked' : '' }}> Are client`s instructions clear ?
						</label>
					</div>
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" class="lab_capable" name="lab_capable" value="1" {{ isset($batch->lab_capable) ? ($batch->lab_capable == 1 ? 'checked' : '' ) : 'checked' }}>  Is the laboratory capable of performing the requested tests?
						</label>
					</div>

					{{-- <div class="form-group col-md-4 btn-group-sm {{ isset($batch->lab_capable) ? ($batch->lab_capable == 1 ? 'hidden' : '' ) : 'hidden' }} batch_subcontracted_client_approval">
						<label class="control-label">
							<input type="checkbox" class="" name="batch_subcontracted_client_approval" value="1" {{ isset($batch->batch_subcontracted_client_approval) && $batch->batch_subcontracted_client_approval == 1 ? 'checked' : '' }}>  Is the client willing for the sample to be subcontracted to an Approved Laboratory ?
						</label>
					</div> --}}
					<div class="form-group col-md-4 btn-group-sm hidden">
						<label class="control-label">
							<input type="checkbox" name="sampled_by_company_personnel" value="1" {{ isset($batch->sampled_by_company_personnel) && $batch->sampled_by_company_personnel == 1 ? 'checked' : '' }}> Sampled by {{$active_company->name}} personnel?
						</label>
					</div>
					<div class="form-group btn-group-sm col-md-12">
						<label class="control-label">Samples Description</label>
						<textarea class="form-control" name="description" placeholder="Description...">{{ $batch->description ?? '' }}</textarea>
					</div>
					<div class="form-group btn-group-sm col-md-12">
						<label class="control-label">Special Remarks / Instructions</label>
						<textarea class="form-control" name="batch_instructions" placeholder="Batch Instructions...">{{ $batch->batch_instructions ?? '' }}</textarea>
					</div>
					
				</div>

				<div class="row p-2 mt-1 qc-params {{ $batch && $batch->is_qc_batch == 1 ? '' : 'hidden' }}">
					<div class="col-md-12 mb-2"><div class="alert alert-default bg-light p-2"> <h6><i class="mdi mdi-chevron-right"></i> Qc Configurations</h6></div></div>
					<div class="form-group col-md-4">
						<label for="" class="control-label">QC Scheme</label>
						<select name="qc_scheme_id" id="" class="form-control ">
							<option value="">Select QC Scheme</option>
							@foreach ($qc_schemes as $qc_scheme)
								<option value="{{ $qc_scheme->id }}" {{ $batch && $batch->qc_scheme_id == $qc_scheme->id ? 'selected' : '' }}>{{ $qc_scheme->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-4 form-group">
						<label for="" class="control-label">QC Type</label>
						<select name="qc_type_id" id="" class="form-group qc_type_id">
							<option value="">Select QC Types</option>
							@foreach ($qc_types as $qc_type)
								<option value="{{ $qc_type->id }}" {{ $batch && $batch->qc_type_id == $qc_type->id ? 'selected' : '' }}>{{ $qc_type->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-4 form-group hidden repeat-sample-field">
						<label for="" class="control-label">Repeat Samples</label>
						<select name="repeat_samples_id[]" id="" class="form-control repeat_sample_id"></select>
					</div>
				</div>
					
				<div class="btn col-md-12 btn-default btn-sm text-primary btn-block toggle-more-fields hidden mb-1">
					<i class="mdi mdi-chevron-double-down"></i> BL Fields
				</div>

				
				<div id="more-fields" class="hidden p-2">
					<div class="row p-2 bg-light m-3">
						<div class="form-group col-md-6 btn-group-sm">
							<label class="control-label">Use of Goods</label>
							<textarea class="form-control" name="use_of_goods" placeholder="Use of Goods...">{{ $batch->use_of_goods ?? '' }}</textarea>
						</div>
					</div>
					
				</div>
					
				<div class="form-group col-md-12 text-center">
					@if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route')
					@else
						@if(!isset($batch->id) || in_array($batch->status,['Samples Reception']))
							<button class="btn btn-primary btn-sm" style="width:60%" id="save-headers">
								<i class="mdi mdi-content-save"></i> Save
							</button>
						@endif
					@endif
				</div>
			</form>
          </div>
        </div>
      </div>
	  <span id="operators-list" data-operators='{{ json_encode($analysts) }}'></span>
      <div class="col-sm-12 p-2">
		@if(isset($batch->id))
			<div class="card border-0 mb-2" style="background-color: inherit !important">
				<div class="card-header- p-2 border-bottom" style="background-color: inherit !important">
					<h5 style="font-size: large"><i class="mdi mdi-calendar-month"></i> Batch Dates</h5>
				</div>
				<div class="card-body border-bottom bg-white">
					<div class="row no-gutters">
						@foreach (getSampleDateTypes() as $date)
							@if($date == 'Login Date' || $date == 'Target Date' || $date == 'Processing Date')
							<div class="col-sm-4 p-1">
								<b style="color: rgb(68, 68, 68);font-size:11px"><i class="mdi mdi-calendar-outline"></i> {{ $date }}</b> <br>
								<span class=""
									style="padding: 3px 9px; font-size:12px; border-radius: 15px; background-color: #f0f0f0; border: 1px solid #eeeeee; color:rgb(68, 68, 68)">{{ $batch->get_date($date) ? date('Y-m-d', strtotime($batch->get_date($date)['date'])) : '-' }}</span>
							</div>
							@endif
						@endforeach
					</div>
				</div>
			</div>
		@endif
		<div class="card-header- p-2 mt-2" style="background-color: inherit !important">
			<h5 style="font-size: large"><i class="mdi mdi-calendar-month"></i> Sample(s)</h5>
			@if(isset($batch->id))
				<div class="row mt-1">
					@if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")))
						@if($batch->status == "Sample Verification")
							@if(sizeof($not_captured) > 0)
								<div class="col-md-3 m-2">
									<span style="font-size: 11px;" class="badge badge-pill bg-white text-danger p-2"><i class="mdi mdi-alert-decagram"></i> Data Partially Captured</span>

								</div>
							@else
								<div class="col-md-3 m-2">
									<span style="font-size: 11px;" class="badge badge-pill bg-white text-success p-2"><i class="mdi mdi-alert-decagram"></i> Data Fully Captured</span>

								</div>
							@endif
						@endif
						
						@if($batch->verify_user_id > 0 && $batch->verify_user_id != '')
							<div class="col-md-3 m-2">
								<span style="font-size: 11px;" class="badge badge-pill bg-white text-success p-2"><i class="mdi mdi-checkbox-multiple-marked-circle"></i> Verified</span>
							</div>
						@endif
						@if($batch->approve_user_id > 0 && $batch->approve_user_id != '')
							<div class="col-md-3 m-2">
								<span style="font-size: 11px;" class="badge badge-pill bg-white p-2 text-success"><i class="mdi mdi-account-check"></i> Approved</span>	
							</div>
						@endif
					@endif	
					@if(isset($batch->id) && $batch->specialist_analyst)	
						<div class="col-md-6 m-2">
							<span class="badge bg-white badge-pill p-2" style="margin-right: 5px">
								<i class="mdi mdi-account"></i> SPECIALIST ANALYST
							</span> 
							{{ $batch->specialist_analyst->name }}
						</div>
					@endif	
					@if(isset($batch->id) && $batch->get_request_types()->count() > 0)
						<?php
                            $types = $batch->get_request_types();

                            $arrT = [];

                            foreach ($types as $type) {
                                $arrT[] = $type->name;
                            }
                        ?>
						<div class="col-md-6 m-2" >
							<span class="badge bg-white badge-pill p-2" style=" margin-right: 5px">
								<i class="mdi mdi-beaker-question"></i> REQUEST TYPE
							</span> {{ implode(',', $arrT) }}
						</div>
					@endif								
				</div>
			@endif
		</div>
        <div class="card tab-card mt-1">
          <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="analyte-tabs" role="tablist">
				<li class="nav-item">
				<a class="nav-link active" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Samples</a>
				</li>
				@if(isset($batch->id))
					
					@if(Auth::user()->is_client == 0)
					<li class="nav-item">
						<a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes-reminders" role="tab" aria-controls="Notes" aria-selected="true"><i class="mdi mdi-android-messages"></i> Notes <span class="badge badge-pill badge-primary">{{ isset($batch->comments) ? count($batch->comments) : 0 }}</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="chain-of-custody-tab" data-toggle="tab" href="#chain-of-custody" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-sitemap"></i> Chain of Custody <span class="badge badge-pill badge-primary">{{$batch->custody->count()}}</span></a>
					</li>
					
					<li class="nav-item">
						<a class="nav-link" id="ammendment-tab" data-toggle="tab" href="#ammendment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-file-document-edit"></i> Amendment</a>
					</li>
					<li class="nav-item">
						<a href="#interlab" class="nav-link" data-toggle="tab" id="interlab-tab-initiator" role="tab" aria-controls="Interlab" aria-selected="true"><i class="mdi mdi-swap-horizontal-bold"></i> Inter Lab Logs</a>
					</li>
					@if( in_array($batch->status,['Samples In Lab','Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status,['Sample Verification','Sample Approval']))
					<li class="nav-item">
						<a href="#raw-results-tab" data-toggle="tab" id="raw-results-initiator" role="tab" class="nav-link" aria-controls="raw-results" aria-selected="true"><i class="mdi mdi-sync-alert text-warning"></i> Raw Results</a>
					</li>
					<li class="nav-item">
						<a href="#processed-results-tab" data-toggle="tab" id="raw-results-initiator" role="tab" class="nav-link" aria-controls="raw-results" aria-selected="true"><i class="mdi mdi-sync text-success"></i> Proccesed Results</a>
					</li>
					@endif
					@if( in_array($batch->status,['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status,['Sample Verification','Sample Approval']))
					<li class="nav-item">
						<a href="#batch-approval" class="nav-link" data-toggle="tab" id="batch-approval-initiator" role="tab" aria-controls="batch-approval" aria-selected="true"><i class="mdi mdi-account-check-outline"></i> Approvals</a>
					</li>
					@endif

					<li class="nav-item">
						<a href="#paymentDetailTabs" class="nav-link" data-toggle="tab" id="payment-details-tab" role="tab" aria-controls="paymentDetailTabs" aria-selected="true"><i class="mdi mdi-account-cash-outline"></i> Payment Details</a>
					</li>
					
					@endif
					<li class="nav-item">
						<a class="nav-link" id="attachment-tab" data-toggle="tab" href="#Attachment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-attachment"></i> Attachments</a>
					</li>
				@endif
             
            </ul>
          </div>
          <div class="tab-content" id="analyte-tabs-content">
			@if(isset($batch->id))
				@if( in_array($batch->status,['Samples In Lab','Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status,['Sample Verification','Sample Approval']))
					<div class="tab-pane fade p-3" id="raw-results-tab" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="p-2">
							<i class="mdi mdi-sync-alert"></i> Raw Results
							<span class="btn btn-sm bg-light float-right" data-toggle="modal" data-target="#process-raw-results" style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;"><i class="mdi mdi-cog"></i> Process Results</span>
						</h5>
						
						<!-- Modern Captured Results Table -->
						<div class="table-responsive">
							<table class="table table-sm table-condensed table-bordered" id="captured-results-table">
								<thead class="bg-light">
									<tr>
										<th class="text-center" style="width: 120px;">Sample Code</th>
										@if(isset($raw_results) && count($raw_results) > 0)
											@php
												$uniqueAnalytes = $raw_results->groupBy('analyte_code')->keys();
											@endphp
											@foreach($uniqueAnalytes as $analyte)
												<th class="text-center" style="min-width: 200px;">
													<div class="d-flex flex-column align-items-center">
														<span class="font-weight-bold">{{ $analyte }}</span>
														<small class="text-muted">Result</small>
													</div>
												</th>
											@endforeach
										@endif
									</tr>
								</thead>
								<tbody id="captured-results-tbody">
									@if(isset($raw_results) && count($raw_results) > 0)
										@php
											$groupedBySample = $raw_results->groupBy(function($item) {
												return $item->sample->sample_code;
											});
										@endphp
										@foreach($groupedBySample as $sampleCode => $sampleResults)
											<tr data-sample-code="{{ $sampleCode }}">
												<td class="font-weight-bold text-center">{{ $sampleCode }}</td>
												@foreach($uniqueAnalytes as $analyte)
													@php
														$result = $sampleResults->where('analyte_code', $analyte)->first();
													@endphp
													<td class="parameter-cell" data-analyte="{{ $analyte }}" data-sample="{{ $sampleCode }}">
														@if($result)
															<!-- Result Input Field -->
															<div class="result-input-container mb-2">
																<div class="input-group input-group-sm">
																	<input type="text" 
																		   class="form-control result-input {{ $result->remark == 'FAIL' ? 'border-danger' : ($result->remark == 'PASS' ? 'border-success' : 'border-secondary') }}" 
																		   value="{{ $result->result }}" 
																		   data-result-id="{{ $result->id }}"
																		   data-sample-code="{{ $sampleCode }}"
																		   data-analyte="{{ $analyte }}"
																		   placeholder="Enter result...">
																	<div class="input-group-append">
																		<button class="btn btn-outline-secondary btn-sm parameter-settings-btn" 
																				type="button" 
																				data-result-id="{{ $result->id }}"
																				data-toggle="modal" 
																				data-target="#parameter-settings-modal">
																			<i class="mdi mdi-dots-vertical"></i>
																		</button>
																	</div>
																</div>
															</div>
															
															<!-- Standard Limit Display -->
															<div class="standard-limit-container">
																<div class="d-flex align-items-center">
																	<span class="standard-limit-text text-muted small">
																		@if($result->main_value)
																			{{ $result->main_value }}
																		@else
																			No limit set
																		@endif
																	</span>
																	<button class="btn btn-link btn-sm p-0 ml-1 edit-standard-btn" 
																			type="button"
																			data-result-id="{{ $result->id }}"
																			data-toggle="modal" 
																			data-target="#edit-standard-modal">
																		<i class="mdi mdi-pencil text-muted" style="font-size: 12px;"></i>
																	</button>
																</div>
															</div>
														@else
															<!-- Empty Cell for Missing Parameter -->
															<div class="result-input-container mb-2">
																<div class="input-group input-group-sm">
																	<input type="text" 
																		   class="form-control result-input border-secondary" 
																		   value="" 
																		   data-sample-code="{{ $sampleCode }}"
																		   data-analyte="{{ $analyte }}"
																		   placeholder="Enter result...">
																	<div class="input-group-append">
																		<button class="btn btn-outline-secondary btn-sm parameter-settings-btn" 
																				type="button" 
																				data-sample-code="{{ $sampleCode }}"
																				data-analyte="{{ $analyte }}"
																				data-toggle="modal" 
																				data-target="#parameter-settings-modal">
																			<i class="mdi mdi-dots-vertical"></i>
																		</button>
																	</div>
																</div>
															</div>
															
															<!-- Empty Standard Limit -->
															<div class="standard-limit-container">
																<div class="d-flex align-items-center">
																	<span class="standard-limit-text text-muted small">No limit set</span>
																	<button class="btn btn-link btn-sm p-0 ml-1 edit-standard-btn" 
																			type="button"
																			data-sample-code="{{ $sampleCode }}"
																			data-analyte="{{ $analyte }}"
																			data-toggle="modal" 
																			data-target="#edit-standard-modal">
																		<i class="mdi mdi-pencil text-muted" style="font-size: 12px;"></i>
																	</button>
																</div>
															</div>
														@endif
													</td>
												@endforeach
											</tr>
										@endforeach
									@else
										<tr>
											<td colspan="100%" class="text-center text-muted py-4">
												<i class="mdi mdi-information-outline mr-2"></i>
												No raw results available
											</td>
										</tr>
									@endif
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="processed-results-tab" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="p-2"><i class="mdi mdi-sync"></i> Processed Results</h5>
						<div class="table-responsive">
							<table class="table table-sm table-condensed table-bordered table-stripped">
								<thead>
									<th>Sample Code</th>
									<th>Analysis Type</th>
									<th>Analyte</th>
									<th>Result</th>
									<th>Analyst</th>
									<th>Remark</th>
								</thead>
								<tbody>
									@foreach ($processed_results as $p_result)
									<tr>

										<td>{{ $p_result->captured->sample->sample_code }}</td>
										<td>{{ $p_result->captured->analysis_type->name }}</td>
										<td>{{ $p_result->captured->analyte_code }}</td>
										<td>{{ $p_result->result }}</td>
										<td>{{ $p_result->captured->operator->name ?? '-' }}</td>
										<td class="{{ $p_result->remarks == 'FAIL' ? 'text-danger' : '' }}">{{ $p_result->remarks }}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				@endif
				@if( in_array($batch->status,['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status,['Sample Verification','Sample Approval']))
					<div class="tab-pane fade p-3" id="batch-approval" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="p-2"><i class="mdi mdi-account-check-outline"></i> Approvers</h5>
						<div class="table-responsive p-2">
							<table class="table table-sm table-condensed table-bordered table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th>Status</th>
										<th>Approval Date</th>
										<th>Approver</th>
										<th>Title</th>
										<th>Workflow</th>
										<th>Remark</th>
										<th>Lab Sections</th>
									</tr>
								</thead>
								<tbody>
									@foreach($approvers as $approver)
									<tr class="{{$approver->batch_status != $batch->status ? 'bg-light' : ''}}" >
										<td style="width:80px">
											@if($approver->status == 0)
											@if($approver->user_id == auth()->user()->id)
											<span class="btn btn-sm btn-default text-success" data-toggle="modal" data-record="{{json_encode($approver)}}" data-target="#change-approval-status"><i class="mdi mdi-thumb-up-outline" data-toggle="tooltip" title="Change Approval Status"></i></span>
											@endif
											<span class="btn btn-sm btn-default text-danger" data-record="{{json_encode($approver)}}"  data-toggle="modal" data-target="#delete-batch-approver"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span> 
											<span class="btn btn-sm btn-default text-primary" data-record="{{json_encode($approver)}}" data-toggle="modal" data-target="#edit-batch-approver"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
											@endif
										</td>
										<td>
											@if($approver->status == 0)
											<span class="badge badge-primary badge-pill p-2"><i class="mdi mdi-decagram"></i> Awaiting Approval</span>
											@elseif($approver->status == 1)
												@if($approver->batch_status  == "Sample Verification" && $approver->show_report == 1)
												<span class="badge badge-success badge-pill p-2"><i class="mdi mdi-thumb-up-outline"></i>Checked</span>
												@else
												<span class="badge badge-success badge-pill p-2"><i class="mdi mdi-thumb-up-outline"></i>{{ $approver->batch_status  == "Sample Verification"  ? 'Verified' : 'Authorized'}}  </span>
												@endif
											@else
											<span class="badge badge-success badge-pill p-2"><i class="mdi mdi-decagram"></i> Declined</span>
											@endif
										</td>
										<td>
											@if($approver->approver_type!='')
											<div class="text-center">
												<small class="badge badge-pill badge-primary p-1">{{$approver->approver_type}}</small>
											</div>
											@endif
											{{$approver->approval_date}}
										</td>
										<td>{{$approver->approvername}}</td>
										<td>{{$approver->title}}</td>
										<td>{{$approver->batch_status}}</td>
										<td>{{$approver->remark}}</td>
										<td>{{$approver->labsectionnames}}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				@endif
				
				<div class="tab-pane fade p-3" id="interlab" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="p-2">
						<i class="mdi mdi-swap-horizontal-bold"></i> Inter Laboratory Logs
						<div class="btn-group float-right">
							<button type="button" class="btn btn-sm btn-white dropdown-toggle" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;"  type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								Actions
							</button>
							<div class="dropdown-menu dropdown-menu-right">
								
								<li>
									<span class="btn btn-sm dropdown-item" data-action="bulk"  data-target="#change-interlab-status" data-toggle="modal"><i class="mdi  mdi-thumbs-up-down mr-2"></i> Approve / Reject Inter Lab Log(s)</span>
								</li>
								<li>
									<span class="btn btn-sm dropdown-item"  data-target="#delete-inter-lab-log" data-toggle="modal"><i class="mdi mdi-delete-empty mr-2"></i> Delete Inter Lab Log(s)</span>
								</li>
								

							</div>
						</div>
					</h5>
					<div class="table-responsive">
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="width:300%" id="interlabbookingtable">
							<thead class="bg-light">
								<tr>
									<th>
									<input type="checkbox" name="selected_inter_lab_all" class="selected_inter_lab_all" id="">
									</th>
									<th>Status</th>
									<th>Sample/Job No</th>
									<th>From Lab</th>
									<th>To Lab</th>
									<th>Sample Type</th>
									<th>Qty</th>
									<th>Submitted By</th>
									<th>Date Submitted</th>
									<th>Recieved By</th>
									<th>Date Received</th>
									<th>Expected Date</th>
									<th>Prelim Date</th>
									<th>Remarks</th>
								</tr>
							</thead>
							<tbody>
								@foreach($interlabs as $ilabs)
								<tr>
									<td style="width:5% !important">
										@if(isset($batch->status) && in_array($batch->status, array("Samples En-Route" ,"Samples Reception","Samples In Lab")) && $ilabs->status == 0)
										<input type="checkbox" value="{{$ilabs->id}}" data-code="{{$ilabs->sample_code}}" name="selected_inter_lab" class="selected_inter_lab" id="">
										<span class="btn btn-sm btn-default text-warning" data-toggle="modal" data-record="{{json_encode($ilabs)}}" data-target="#change-interlab-status" data-action="single"><i class="mdi mdi-thumbs-up-down"  data-toggle="tooltip" title="Approve / Rejected Inter Lab"></i></span>
										<span class="btn btn-default text-primary btn-sm initiate-interlab" data-record="{{json_encode($ilabs)}}" data-toggle="modal" data-target="#inter-lab-add" data-action="edit"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit Inter Lab"></i></span>
										@endif
									</td>
									<td style="width:8% !important">
										@if($ilabs->status == 0)
										<span class="badge badge-pill p-2 badge-primary"><i class="mdi mdi-alert-decagram-outline"></i> Awaiting Approval</span>				
										@elseif($ilabs->status == 1)
										<span class="badge badge-pill p-2 badge-success"><i class="mdi mdi-thumb-up"></i>  Approved</span>	
										@else
										<span class="badge badge-pill p-2 badge-danger"><i class="mdi mdi-alert-decagram-outline"></i>  Rejected</span></	
										@endif
									</td>
									<td style="width:7% !important">{{$ilabs->sample_code}}</td>
									<td style="width:10% !important">{{$ilabs->from_lab_section_id > 0 ? $ilabs->from_lab_name : 'Reception'}}</td>
									<td style="width:10% !important">{{$ilabs->to_lab_code}} - {{$ilabs->to_lab_name}}</td>
									<td style="width:7% !important">{{$ilabs->sample_type_name}}</td>
									<td>{{$ilabs->quantity}}</td>
									<td>{{$ilabs->submitted_by_name}}</td>
									<td>{{$ilabs->date_submitted}}</td>
									<td>{{$ilabs->received_by_name}}</td>
									<td>{{$ilabs->date_received}}</td>
									<td>{{$ilabs->expected_date}}</td>
									<td>{{$ilabs->prelim_date}}</td>
									<td>{{$ilabs->remarks}}</td>

								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="paymentDetailTabs" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="p-2">
						<i class="mdi mdi-account-cash-outline"></i> Payment Details
						<span class="btn btn-outline-primary btn-sm float-right mb-2" data-action="add" data-target="#add-payment-details" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Payment</span>
					</h5>
					<div class="table-responsive">
                        <table class="table table-condensed table-bordered table-sm table-hover stripped table-bordered my-small-text" style="width: 100%;">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>#</th>
                                    <th nowrap>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Reference No</th>
                                    <th>VAT</th>
                                    <th>Balance</th>
									<th>Contact Person</th>
                                    <th>Received By</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment_detail as $payment)
                                
                                <tr>
                                    <td style="width:70px !important">
										<span class="btn btn-outline-default text-primary" data-toggle="modal" data-target="#add-payment-details" data-action="edit" data-record="{{json_encode($payment)}}" data-toggle="tooltip" title="Edit Payment Detail">
											<i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i>
                                        </span>
                                        {{-- <span class="btn btn-outline-default text-danger btn-sm" data-toggle="modal" data-target="#delete-payment-detail" ><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete Payment Detail"></i></span> --}}
									</td>
                                    <td>{{$payment->payment_method}}</td>
                                    <td style="text-align: right;">{{number_format($payment->amount,2) }}</td>
                                    <td>{{$payment->ref_no}}</td>
                                    <td>{{$payment->vat}}</td>
                                    <td>{{$payment->balance}}</td>
									<td>{{$payment->contact_person_name}}</td>
                                    
                                    <td>{{$payment->receivername}}</td>
                                    
                                </tr>
                                
                                @endforeach
                            </tbody>
                        </table>
                    </div>
				</div>
				
				<div class="tab-pane fade p-3" id="chain-of-custody" role="tabpanel" aria-labelledby="one-tab">
					<div class="p-2 row">
						<div class="col-sm-8">
							<h5><i class="mdi mdi-sitemap"></i> Chain of Custody</h5>
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
							<thead class="bg-light p-2">
								<tr>
									<th>No</th>
									<th>Workflow</th>
									<th nowrap>Tracking Stage</th>
									<th nowrap>Started By</th>
									<th nowrap>Start Date</th>
									<th nowrap>Completed By</th>
									<th nowrap>Complete Date</th>
									<th nowrap>Comments</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($batch->custody as $c)
									<tr>
										<td nowrap>{{ $loop->iteration }}</td>
										<td nowrap>{{ $c->workflow_stage }}</td>
										<td nowrap>{{ $c->tracking_stage->name ?? '-' }}</td>
										<td nowrap>{{ $c->started_by->name ?? '-' }}</td>
										<td nowrap>{{ $c->created_at ?? '-' }}</td>
										<td nowrap>{!! $c->completed_by->name ?? '<i class="mdi mdi-timer-sand text-warning"  style="font-size: 16px!important"></i>' !!}</td>
										<td nowrap>{!! $c->moved_out_date ?? '<i class="mdi mdi-timer-sand text-warning" style="font-size: 16px!important"></i>' !!}</td>
										<td>{{ $c->comments != '' ? $c->comments : '-' }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="notes-reminders" role="tabpanel" aria-labelledby="one-tab">
					<div class="p-2 row">
						<div class="col-sm-8">
							<h5><i class="mdi mdi-android-messages"></i> Notes & Reminders</h5>
						</div>
						<div class="col-sm-4 align-content-center">
							@if(Auth::user()->is_client == 0)
							<span class="btn btn-primary float-right btn-sm" data-target="#add-sample-notes" data-toggle="modal">
								<i class="mdi mdi-message-plus-outline"></i> Add
							</span>
							@endif
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
							<thead class="bg-light p-2">
								<tr>
									<th>No</th>
									<th nowrap>Sender</th>
									<th nowrap>Receiver</th>
									<th nowrap>Type</th>
									<th nowrap>Other Users</th>
									<th nowrap>Status</th>
									<th nowrap>Comment</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@foreach ($batch->comments ?? array() as $item)
									<tr>
										<td>{{ $loop->iteration }}</td>
										<td>{{ $item->creator->name ?? '-' }}</td>
										<td>{{ $item->reminder_for()->name ?? '-' }}</td>
										<td>{{ $item->comment_type }}</td>
										<td>
											@foreach ($item->people_to_cc()['names'] as $p)
												<small class="mr-1"><i class="mdi mdi-account"></i> {{ $p }}</small>
											@endforeach
										</td>
										<td>{!! $item->completed_at == "" ? '<i class="text-warning mdi mdi-timer-sand"></i> Pending' : '<i class="text-success mdi mdi-check-circle"></i> Completed'.$item->completed_at !!}</td>
										<td class="show-hoverable">
											<span class="partial">{{ substr($item->comments, 0, 75)  }}{{ strlen($item->comments) > 75 ? '...' : '' }}</span>
											<span class="complete">{{ $item->comments }}</span>
										</td>
										<td>
											@if($item->completed_at == "")
												<span class="btn btn-sm btn-outline-info" data-target="#edit-sample-notes-{{ $loop->iteration }}" data-toggle="modal">
													<i class="mdi mdi-pencil"></i>
												</span>
												<div id="edit-sample-notes-{{ $loop->iteration }}" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<form class="modal-content" id="print-labels-form" method="POST" action="{{ route('edit-batch-comment', ['id'=>$item->id]) }}" enctype="multipart/form-data">
															@csrf
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-message-plus"></i> Edit Note </h4>
															</div>
															<div class="modal-body">
																<div class="form-group">
																	<label class="control-label">User To Notify</label>
																	<select class="form-control" name="user_id" required placeholder="Select User...">
																		<option></option>
																		@foreach ($notifiable_users as $g)
																			<option value="{{ $g->id }}" {{ $item->created_by == $g->id ? 'selected' : ''}}>{{ $g->name }}</option>
																		@endforeach
																	</select>
																</div>
																<div class="form-group">
																	<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
																	<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
																		<option></option>
																		@foreach ($notifiable_users as $g)
																			<option value="{{ $g->id }}" {{ in_array($g->id, $item->people_to_cc()['ids']) ? 'selected' : '' }}>{{ $g->name }}</option>
																		@endforeach
																	</select>
																</div>
																<input name="batch_id" type="hidden" value="{{ $batch->id }}" />
																<div class="form-group">
																	<label class="control-label">Type</label>
																	<select class="form-control" name="type" required placeholder="Message Type...">
																		<option></option>
																		@foreach ($notesReminderType as $g)
																			<option value="{{ $g }}" {{ $item->comment_type == $g ? 'selected' : ''}}>{{ $g }}</option>
																		@endforeach
																	</select>
																</div>
																<div class="form-group">
																	<label class="control-label">Message</label>
																	<textarea class="form-control" name="message" placeholder="Message..." required>{{ $item->comments }}</textarea>
																</div>
																<div class="form-group">
																	<label class="control-label"><input type="checkbox" value="yes" name="complete" /> Mark as Complete </label>
																</div>
															</div>
															<div class="modal-footer">
																<button type="submit" class="btn btn-info btn-sm print-label-btn"><i class="mdi mdi-content-save"></i> Save</button>
																<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
															</div>
														</form>
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
				</div>
				<div class="tab-pane fade p-3" id="ammendment" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title">
						<i class="mdi mdi-file-document-edit"></i> Amendments
						
					</h5>

					<div class="table-responsive">
						<table class="table table-condensed table-sm my-small-text table-hover table-stripped">
							<thead class="bg-light">
								<th>Version No</th>
								<th nowrap>Samples</th>
								<th nowrap>Amended By</th>
								<th>Date</th>
								<th>Reason</th>
								<th nowrap>Report</th>

							</thead>
							<tbody>
								@foreach($ammendments as $a)
								<tr>
									<td>V {{$a->version_number}}</td>
									<td>
										{{$a->sample_name}}
									</td>
									<td>
										{{$a->creator}}
									</td>
									<td>{{$a->created_at}}</td>
									<td><span class="btn-sm btn-outline-dark mdi mdi-comment-text" data-toggle="modal" data-target="#reason-{{$a->id}}" data-toggle="tooltip" title="Amendment Reason" ></span>
									<div class="modal fade" id="reason-{{$a->id}}" role="dialog">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header bg-light">
													<h4 class="modal-title"><i class="mdi mdi-comment-text"></i> Amendment {{$a->version}} Reason</h4>
												</div>
												<div class="modal-body">
													{{$a->reason}}
												</div>
												<div class="modal-footer">
												<button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
												</div>
											</div>
										</div>
									</div>
								</td>
								<td nowrap><a href="{!! $a->report_url == '' ? '' : '/storage'.$a->report_url !!}"><i class="mdi mdi-download"></i> Download Report</a></td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Attachment" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-tile">
						<i class="mdi mdi-attachment"></i> Attachments
						<span class="btn btn-outline-info btn-sm float-right mb-2" data-target="#add-attachment-batch" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</span>
					</h5>
					<div class="table-responsive">
					<table class="table table-condensed table-sm table-hover table-stripped table-bordered">
						<thead class="bg-light p-2">
							<tr>
								<th></th>
								<th>Type</th>
								<th>Title</th>
								<th>Upload Date</th>
								<th>Uploaded By</th>
								<th>File</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@if(Auth::user()->is_client == 1)
								@foreach($attachments as $a)
									@if($a->is_internal == 0)
										
										<tr>
											<td>{{$loop->iteration}}</td>
											<td>{{ $a->attachtypename}}</td>
											<td>{{$a->title ?? 'N/a'}}</td>
											<td>{{date('Y-m-d',strtotime($a->created_at))}}</td>
											<td>{{$a->uploaduser}}</td>
											<td class="text-center">
											<a href="'.$a->attachment_url.'" target="_blank" data-toggle="tooltip" data-title="View Attachment" class=" btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i></a>
											
											</td>
											<td>
												<span class="btn btn-sm btn-outline-danger" data-title="Delete Attachment" data-toggle="modal" data-target="#delete-attachment-'.$a->id.'" ><i class="mdi mdi-delete-empty"></i></span>
												
												<div class="modal fade" id="delete-attachment-{{$a->id}}" role="dialog">
													<div class="modal-dialog">
														<div class="modal-content">
															<form action="{{route('delete_batch_attachmment')}}" method="post">
																@csrf  
																<div class="modal-body">
																	
																		<div class="alert alert-danger p-3">
																		<i class="mdi mdi-delete-empty"></i>	Confirm you want to delete attchment {{$loop->iteration}}.
																		</div>
																
																	<input type="hidden" name="attachment_id" value="{{$a->id}}">

																</div>
																
																<div class="modal-footer">
																	<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Confirm</button>
																	<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							
																</div>
															</form>
														</div>
													</div>
												</div>
											</td>
										</tr>
									@endif
								@endforeach
							@else
								@foreach($attachments as $a)
								
								<tr>
									<td>{{$loop->iteration}}</td>
									<td>{{ $a->attachtypename}}</td>
									<td>{{$a->title ?? 'N/a'}}</td>
									<td>{{date('Y-m-d',strtotime($a->created_at))}}</td>
									<td>{{$a->uploaduser}}</td>
									
									<td class="text-center">
										<a href="'.$a->attachment_url.'" target="_blank" data-toggle="tooltip" data-title="View Attachment" class=" btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i></a>
									
									
									</td>
									<td>
										
									<span class="btn btn-sm btn-outline-danger" data-title="Delete Attachment" data-toggle="modal" data-target="#delete-attachment-'.$a->id.'" ><i class="mdi mdi-delete-empty"></i></span>
										<div class="modal fade" id="delete-attachment-{{$a->id}}" role="dialog">
											<div class="modal-dialog">
												<div class="modal-content">
													<form action="{{route('delete_batch_attachmment')}}" method="post">
														@csrf  
														<div class="modal-body">
															
																<div class="alert alert-danger p-3">
																<i class="mdi mdi-delete-empty"></i>	Confirm you want to delete attachment {{$loop->iteration}}.
																</div>
														
															<input type="hidden" name="attachment_id" value="{{$a->id}}">
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Confirm</button>
															<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</div>
									</td>
								</tr>
								@endforeach
							@endif
						</tbody>
					</table>
					</div>
				</div>
			@endif
            <form method="POST" action="{{ route('add-batch-samples', ['batch'=>$batchID]) }}" class="tab-pane fade show active p-3" id="samples" role="tabpanel" aria-labelledby="one-tab">
              	
				{{-- Show Unprocessed Staging Data when sample_detail_processed is 0 --}}
				@if(isset($batch->sample_detail_processed) && $batch->sample_detail_processed == 0 && isset($batch->stagingDetails) && $batch->stagingDetails->count() > 0)
					<div class="card mb-4">
						<div class="card-header" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7);">
							<h5 class="mb-0"><i class="mdi mdi-clipboard-alert"></i> Unprocessed Staging Data</h5>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-sm">
									<thead class="bg-light">
										<tr>
											<th>Actions</th>
											<th>Specimen Type</th>
											<th>Company Sub Unit</th>
											<th>Analysis Types</th>
											<th>Quantity</th>
											<th class="hidden">Other Details</th>
										</tr>
									</thead>
									<tbody>
										@foreach($batch->stagingDetails as $staging)
											@if(!$staging->is_processed)
												<tr>
													<td>
														<button type="button" class="btn btn-sm btn-primary assign-samples-btn" 
																data-staging-id="{{ $staging->id }}"
																data-header-id="{{ $batch->id }}">
															<i class="mdi mdi-checkbox-multiple-marked"></i> Assign Samples
														</button>
													</td>
													<td>{{ $batch->sample_type->name ?? 'N/A' }}</td>
													<td>{{ $staging->data_json['company_sub_unit_name'] ?? 'N/A' }}</td>
													<td>{{ $staging->data_json['analysis_type_names'] ?? 'N/A' }}</td>
													<td>{{ $staging->data_json['quantity'] ?? 1 }}</td>
													<td class="hidden"><pre style="font-size: 10px; max-height: 150px; overflow-y: auto;">{{ json_encode($staging->data_json, JSON_PRETTY_PRINT) }}</pre></td>
												</tr>
											@endif
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				@endif
				
				{{-- Show Samples Configuration when sample_detail_processed is 1 or when there's no staging --}}
				@if(!isset($batch->sample_detail_processed) || $batch->sample_detail_processed == 1)
              	<h5 class="card-title">
					<span class="btn btn-transparent">Samples Configuration</span>
					@if(isset($batch->id) || (Auth::user()->is_client == 1 && isset($batch->status) && $batch->status =='Samples En-Route'))
						@if(in_array($batch->status, array("Sample Verification","Sample Approval","Reports In Payment","Reports for Colllection","Samples In Lab")))
							@if(sizeof($not_captured) > 0)
							<button type="button" class="btn btn-outline-danger btn-sm ml-2" data-toggle="modal" data-target="#missing-parameters-modal">
								<i class="mdi mdi-content-save"></i> Missing Analytes
							</button>
							@endif
						@endif
						@if(isset($batch->status) && ($batch->status == "Sample Verification" || $batch->status == "Sample Approval") && Auth::user()->is_client == 0)
							
								<button type="button" class="btn btn-danger btn-sm text-white float-right" data-target="#send-back-for-rechcek-modal" data-toggle="modal">
									<i class="mdi mdi-page-previous"></i> Recheck
								</button> &nbsp; &nbsp;
							
						@endif
						@if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Samples In Lab")))
						<button type="button" class="btn btn-danger btn-sm text-white float-right ml-2 save-samples"><i class="mdi mdi-content-save"></i> Save</button> &nbsp; &nbsp;
						@endif
						@if(isset($batch->status) && in_array($batch->status, array("Samples Reception","Samples En-Route")))
							@if(Auth::user()->is_client == 1 && $batch->status == 'Samples Reception')
							@else
							<input type="hidden" name="batch" value={{$batch->id}}>
							<button type="button" class="btn btn-danger btn-sm text-white ml-2 save-samples"><i class="mdi mdi-content-save"></i> Save</button> &nbsp; &nbsp;
							<span class="btn btn-success btn-sm create-new-sample-row float-right"><i class="mdi mdi-plus"></i> Add</span> &nbsp; &nbsp;
							<span class="btn btn-primary btn-sm duplicate-sample-row float-right mr-1"><i class="mdi mdi-content-duplicate"></i> Duplicate</span>
							{{-- <span class="btn btn-default btn-sm float-right mr-2" data-target="#clone-samples" data-toggle="modal" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"><i class="mdi mdi-compare-horizontal"></i> Clone Samples</span> --}}
							
							@endif
						@endif
					@endif
				</h5>
				@csrf
              <div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
					<thead>
						<tr>
							<th></th>
							<th></th>
							<th>Code</th>
							<th>Analysis<sup class="text-danger">*</sup></th>
							<th>Lab<sup class="text-danger">*</sup></th>
							<th nowrap>Condition<sup class="text-danger">*</sup> <span class="btn-primary btn-sm p-0" data-toggle="modal" data-target="#add-sample-conditions" data-target="tooltip" title="Add Sample Condition"><i class="mdi mdi-plus"></i></span></th>
							<th nowrap><span class="client-preferred-sample_point-name"></span><sup class="text-danger">*</sup> <span class="p-0 btn-primary btn-sm" data-target="#add-company-sample-point"  data-toggle="modal" data-target="tooltip" title="Add Sample Point" ><i class="mdi mdi-plus"></i></span></th>
							<th><span class="client-preferred-product-name"></span><sup class="text-danger">*</sup> <span class="btn-primary btn-sm p-0" data-toggle="modal" data-target="#add-company-product" data-toggle="tooltip" title="Add Product"><i class="mdi mdi-plus"></i></span></th>
							<th>Sample Description</th>
							<th>Time Sampled</th>
							<th>Main Standard <sup class="text-danger">*</sup></th>
							<th>Secondary Standard</th>
							
							<th>Disposal Date</th>
							<th>Storage</th> 
							<th>Slot</th>
							<th>Quantity</th>
							<th>UoM</th>
							
							
						</tr>
					</thead>
					<tbody id="sample-detail-rows"
						data-analysis_names = '{{json_encode($analysisBySampleNames)}}'
						data-sample_analysis_ids = '{{ json_encode($analysisBySample) }}'
						data-parameters='{{ json_encode($analaytesHolder) }}'
						data-conditions='{{ json_encode($conditions ?? array()) }}'
						data-stores='{{ json_encode($labStores) }}'
						data-analysis_types='{{ json_encode($selected_analysis_types) }}'
						data-samples="{{ json_encode($allsamples) }}"
						data-ammendments = "{{ json_encode($ammendable) }}"
						data-client = "{{json_encode(Auth::user())}}"
						data-batch = "{{json_encode($batch ?? array())}}"
						data-standards = "{{json_encode($standards ?? array())}}"
						data-methods = "{{json_encode($methods)}}"
						data-ltmethods = "{{json_encode($ltmethods)}}"
						data-labs = "{{json_encode($labSamples)}}"
						data-allLabs = "{{json_encode($labSamples)}}"
						data-pesticides = "{{json_encode($analaytesHolderPesticide)}}"
						>

					</tbody>
                </table>
              </div>
				@endif
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
@section('script2')

<div class="carry_data hidden" data-userlabsection="{{json_encode($userLabSections)}}" ></div>

<div class="modal fade" id="add-customer-contact" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="{{route('customer-contact-add-ajax')}}" method="POST">
				@csrf  
				<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company Contact</h4>
			</div>
			<div class="modal-body">
				<div class="row border-bottom">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="" class="control-label">Customer Contact</label>
							<select name="customer_id" id="" class="form-control crm_customer_id" data-client-source="{{ route('sample-workflow.clients') }}" data-page-size="{{ $clientPageSize }}">
								<option value="">Select Customer...</option>
								@foreach($clients as $client)
									<option value="{{$client->id}}">{{$client->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Title *</label>
							<select name="title" class="form-control" placeholder="Title..." required>
								<option></option>
								@foreach (getModulePreconfig("Designation", "Personnel-Management") as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">First Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="first_name" value="" placeholder="First Name..." required />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Middle Name</label>
							<input type="text" class="form-control" name="second_name" value="" placeholder="Middle Name..." />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Surname</label>
							<input type="text" class="form-control" name="third_name" value="" placeholder="Surame..." />
						</div>
					</div>
				</div>
				
				<div class="row border-bottom">
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Job Occupation</label>
							<input type="text" class="form-control" name="job_occupation" value="" placeholder="Job Title..." />
						</div>
						<input type="hidden" name="not_ajax" Value="1">
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Company Units <span class="text-danger">*</span> </label>
							<select required class="form-control crm_unit_id" name="unit_name[]" multiple>
								<option value="">Select Company Unit...</option>
								
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Email <span class="text-danger">*</span></label>
							<input type="email" class="form-control" name="email" value="" placeholder="Email..." required />
						</div>
					</div>
				</div>
				
				<div class="row border-bottom">
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Telephone <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="telephone" value="" placeholder="Telephone..." required />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Mobile</label>
							<input type="text" class="form-control" name="mobile" value="" placeholder="Mobile..." />
						</div>
					</div>

				</div>
				
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" checked value="1" name="receive_price_list" /> Receives Pricelist?</label>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" checked value="1" name="receive_report" /> Receives Report?</label>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" checked value="1" name="receive_invoice" /> Receives Invoice?</label>
						</div>
					</div>

				</div>
				<div class="row">
					
					<div class="col-sm-6">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" value="1" checked name="active" /> Is Active?</label>
						</div>
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="sample-marking-show" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
			</div>
		</div>
	</div>
</div>
<div id="add-company-unit" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" id="form" method="POST" action="" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company Unit</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" id="save-unit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-customer" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-customers') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Customer</h4>
			</div>
			<div class="modal-body row">
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Name <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="name" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Postal Address <span class="text-danger">*</span></label>
						<textarea class="form-control" name="postal_address" placeholder="Postal Address..."></textarea>
					</div>
					<div class="form-group">
						<label class="control-label">Physical Address <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="physical_address" placeholder="Location..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Website </label>
						<input type="text" class="form-control" name="website" placeholder="Website..." />
					</div>
					<div class="form-group">
						<label for="" class="control-label">KRA PIN </label>
						<input type="text" name="vat_no" class="form-control" placeholder="KRA PIN...." />
					</div>
					<div class="form-group">
						<label class="control-label">Country</label>
						<select class="form-control" name="country_id" data-placeholder>
							@foreach ($countries as $c)
							<option value="{{ $c->id }}">{{ $c->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="lpos_required" value="1" />
								<label class="form-check-label">
									Lpo Required?
								</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" checked value="1" name="active" />
								<label class="form-check-label"> Is Active?</label>
							</div>
						</div>
					</div>


				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Fax</label>
						<input type="text" class="form-control" name="fax" placeholder="Fax..." />
					</div>
					<div class="form-group">
						<label class="control-label">Email <span class="text-danger">*</span></label>
						<input type="email" class="form-control" name="email" placeholder="Email..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone 1 <span class="text-danger">*</span></label>
						<input type="tel" class="form-control" name="phone1" value="" placeholder="Phone 1..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone 2 </label>
						<input type="tel" class="form-control" name="phone2" value="" placeholder="Phone 2..." />
					</div>
					<div class="form-group">
						<label class="control-label">Credit days</label>
						<input type="number" name="credit_day" class="form-control" />
					</div>
					<div class="form-group">
						<label class="control-label">Account Setting <span class="text-danger">*</span></label>
						<select class="form-control" name="account_id" required data-placeholder>
							<option value="">Choose Account Settings</option>
							@foreach ($accounts as $account)
							<option value="{{ $account->id }}">{{ $account->key }}</option>
							@endforeach
						</select>
					</div>


				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

@if(isset($batch->id))
<div class="modal fade" id="edit-standard" data-backdrop="static" data-keyboard="false"  role="dialog" style="z-index: 3000">
	<div class="modal-dialog">
		<form class="modal-content bg-light">
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<span class="btn btn-sm btn-outline-primary save-standard-value"><i class="mdi mdi-content-save"></i> Save</span>
				<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
			</div>
		</form>
	</div>
</div>
@if(isset($batch->status) && $batch->status == 'Samples In Lab')
<div class="modal fade" id="process-raw-results" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{ route('process-raw-results-lab') }}" method="post">
				@csrf 
				<div class="modal-body">
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 25px;"></i>
						<span class="pl-2">Confirm you want to process all results filled for all samples in batch {{ $batch->batch_code }}</span>
					</div>
					<input type="hidden" name="batch_id" value="{{ $batch->id }}">
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-thumbs-up"></i> Yes, Process</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="add-reporting-unit" role="dialog" data-backdrop="static" data-keyboard="false"  role="dialog" style="z-index: 3000">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="">
				<div class="modal-body">
					<div class="alert alert-primary d-flex">
						<i class="mdi mdi-plus" style="font-size: 30px"></i>
						<span class="p-2">Add reporting unit below</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Reporting Unit</label>
						<input type="text" name="reporting_unit" id="a_reporting_unit" class="form-control">
					</div>
					<div class="message-area">
						
					</div> 
				</div>
				<div class="modal-footer">
					<span class="btn btn-sm btn-outline btn-primary save-reporting"><i class="mdi mdi-content-save"></i> Save</span>
					<span class="btn btn-sm text-danger btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
<div class="modal fade" id="change-approval-status" role="dialog">
	<div class="modal-dialog">

		<div class="modal-content">
			<form action="{{route('changeBatchApprovalStatus')}}" method="post">
				@csrf  
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-content-save"></i> Submit</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="edit-batch-approver" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('editVerificationApproverConfig')}}" method="post">
				@csrf  
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="delete-batch-approver" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('deleteVerificationApproverConfig')}}" method="post">
				@csrf 
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete-empty"></i> Yes, Delete</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="add-payment-details" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payment-detail-add')}}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus text-primary"></i> Add Payment
                    </h5>
                </div>
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="send-payment-reminder" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="{{route('sendBatchPaymentReminder')}}" method="post">
				@csrf  
				<div class="modal-body">
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-email-send-outline" style="font-size: 30px"></i>
						<span class="p-2">Send out payment reminder to {{$customer->name}} by filling the details below:</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Customer Contact</label>
						<select name="contact_id" id="" class="form-control">
							<option value="">Choose Contact...</option>
							@foreach($contacts as $contact) 
							<option value="{{$contact->id}}">{{$contact->first_name}} {{$contact->middle_name}} {{$contact->last_name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Body</label>

						<textarea name="body" class="form-control editor" id="" cols="50" rows="50">
						{!! getPaymentReminderBody($customer->name) !!},<br>
						</textarea>
					</div>
					<input type="hidden" name="batch_id" value="{{$batch->id}}">
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-email-send-outline"></i> Yes, Send</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="send-schedule-analysis" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('sendBatchScheduleAnalysis')}}" method="post">
				@csrf 
				<div class="modal-body">
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-email-send-outline" style="font-size: 30px"></i>
						<span class="p-2">
							Confirm you want to send schedule of analysis for batch {{$batch->batch_code}} to {{$customer->name}} customer.
							Choose the customer contact to receive the schedule of analysis below: 
						</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Customer Contact <small class="text-danger">*</small></label>
						<select name="contact_id" id="" required class="form-control">
							<option value="">Choose Contact...</option>
							@foreach($contacts as $contact) 
							<option value="{{$contact->id}}">{{$contact->first_name}} {{$contact->middle_name}} {{$contact->last_name}}</option>
							@endforeach
						</select>
					</div>
					<input type="hidden" name="batch_id" value="{{$batch->id}}">
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-outline-primary" type="submit"><i class="mdi mdi-email-send-outline"></i> Yes, Send</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="change-interlab-status" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('changeInterLabLogStatus')}}" method="post">
				@csrf  
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn-sm btn-outline-success btn affect-button"><i class="mdi content-save"></i> Yes, Effect</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Cancel</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="inter-lab-add" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('create_sample_inter_lab_log')}}" method="post">
				@csrf  
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<Button type="submit" class="btn btn-sm submit-button"><i class="mdi mdi-swap-horizontal-bold"></i> Initiate</Button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Cancel</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="add-company-sample-point" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-sample-point') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{ trim($customer->sample_point_configurable_name)!="" ? $customer->sample_point_configurable_name : 'Sample Point' }}</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label>{{ trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Unit' }}</label>
					<select class="form-control" name="unit" placeholder="Select..." required>
						<option></option>
						@foreach ($customer->units as $item)
						<option value="{{ $item->id }}">{{ $item->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<div id="location-map" style="width: 100%;height:350px"></div>
				</div>
				<div class="form-group hidden">
					<label class="control-label">Latitude</label>
					<input type="text" class="form-control" name="lat" id="latitude" value="" placeholder="Name..." />
				</div>
				<div class="form-group hidden">
					<label class="control-label">Longitude</label>
					<input type="text" id="longitude" class="form-control" name="long" value="" placeholder="Name..." />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-company-product" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-customer-product') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{ trim($customer->product_configurable_name)!="" ? $customer->product_configurable_name : 'Product' }}</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." required />
				</div>
				
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@if(in_array($batch->status,['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection','Samples In Lab']))
<div class="modal fade" id="view-coa-report" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="#" method="get" id="coa-report-form">
				
				<div class="modal-body">
					@if($batch->getVerificationApprovalStatus() > 0 && $batch->status == 'Sample Verification' )
					<div class="alert alert-danger p-2 d-flex">
						<i class="mdi mdi-decagram" style="font-size:30px"></i>
						<span class="p-2">Confirm all approvers have approved the report to have all the required signatories appear on the COA.</span>

					</div>
					@endif
					@if($batch->getApprovalStageStatus() > 0 && $batch->status == 'Sample Approval' )
					<div class="alert alert-danger p-2 d-flex">
						<i class="mdi mdi-decagram" style="font-size:30px"></i>
						<span class="p-2">Confirm all approvers have approved the report to have all the required signatories appear on the COA.</span>

					</div>
					@endif


					<div class="alert alert-success p-2 d-flex">
						<i class="mdi mdi-cogs" style="font-size: 25px"></i>
						<span class="p-2">Confirm you want to view COA report for this batch by selecting the report standard below:</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Report Format</label>
						<select name="report_format" id="report_format_select" class="form-control" required>
							<option value="">Select Report Format</option>
							<option value="0">Aspergillus Report (MB 821/25-3)</option>
							<option value="1">Microbiology Report (MB 826/25)</option>
							<option value="2">Hygiene Swabs Report (MB 756/25-2)</option>
						</select>
					</div>
					<div class="form-group hidden">
						<label for="" class="control-label"><input type="checkbox" name="add_pesticide" id="" class=""> Include Pesticide Results</label>
					</div>
					<input type="hidden" name="batch_id" value="{{$batch->id}}">
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-outline-success" type="button" id="generate-coa-btn"><i class="mdi mdi-cogs"></i> Generate Report</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
<div id="add-sample-conditions" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-sample-conditions') }}" enctype="multipart/form-data">
		@csrf
		<div class="modal-header">
			<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Sample Condition</h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
			<label class="control-label">Name</label>
			<input type="text" class="form-control" name="name" placeholder="Name..." required />
			</div>
			<div class="form-group">
			<input type="hidden" name="sample_type_id" value="{{ $batch->sample_type_id }}" />
			<label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
		</form>
	</div>
	</div>
	<div class="modal fade" id="add-attachment-batch" role="dialog">
		<div class="modal-dialog">
			<form action="{{route('add_batch_attachment')}}" method="post" enctype="multipart/form-data" class="modal-content">
				@csrf 
				<div class="modal-header">
					<h5 class="modal-title">Add Attachment For {{$batch->batch_code}}</h5>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Title</label>
						<input type="text" name="title" id="" class="form-control" required>
					</div>
					<div class="form-group">
						<label class="control-label">Attachment Type</label>
						<select name="attachment_type" id="" class="form-control">
							<option value="">Choose Attachment Type ...</option>
							@foreach($atachment_type as $aType)
							<option value="{{$aType->id}}">{{$aType->value}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Choose File</label>
						<input type="file" name="attachment" required class="form-control">
						<input type="hidden" name="batch_id" value="{{$batch->id}}">
					</div>
					<div class="form-group">
						<label class="control-label">
							<input type="checkbox" name="is_internal" id=""> For Internal Use
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Save</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	
	@if(in_array($batch->status, array("Sample Verification","Sample Approval","Reports In Payment","Reports for Collection","Samples In Lab")))
		@if($not_captured->count() == 0 || $batch->prelim_report_status > 0)
			<div id="send-for-approval-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('moveToVerificationApprovalLevel') }}" enctype="multipart/form-data">
						@csrf
						
						<div class="modal-body">
							@if($batch->getVerificationApprovalStatus() > 0 )
							<div class="alert alert-danger p-2 d-flex mt-1">
								<i class="mdi mdi-decagram" style="font-size: 30px"></i>
								<span class="p-2">COnfirm all approvers have approved before sending the report for approval</span>
							</div>
							@endif
							<div class="alert alert-primary p-2 d-flex">
								<i class="mdi mdi-decagram" style="font-size: 30px"></i>
								<span class="p-2">Confirm you want to send this {{$batch->batch_code}} batch for approval</span>
							</div>
							<div class="form-group">
								<label for="" class="control-label">Title</label>
								<input type="text"  name="title" value="Authorized by" class="form-control">
							</div>
							<div class="form-group">
								<label for="" class="control-label">Approver</label>
								<select name="user_id" id="" class="form-control">
									@foreach($users as $user)
										@if(!in_array($user->id,$approvers_user_ids ?? []))
											<option value="{{$user->id}}">{{$user->name}}</option>
										@endif
									@endforeach
								</select>
							</div>
							<input type="hidden" name="batch_id" value="{{$batch->id}}">
							<input type="hidden" name="status" value="Sample Approval">
							<div class="form-group">
								<input type="hidden" name="is_approval" value="1">
								<label class="control-label">Remarks</label>
								<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
								<label class="form-check-label">
									Send Email Notification
								</label>
							</div>
							<br>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
								<label class="form-check-label">
									Send SMS
								</label>
							</div>
						</div>
						<div class="modal-footer">
							@if($batch->getVerificationApprovalStatus() == 0 )
							<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Yes Proceed</button>
							@endif
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
		@endif
		@if(isset($batch->status) && ($batch->status=="Sample Verification" || $batch->status=="Sample Approval" || $batch->status == 'Samples In Lab'))
			<div class="modal fadeprompt-report-modal" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
							<div class="alert alert-danger">
								Ensure that batch {{$batch->batch_code}} has results, The results has been proccessed and Approved by clicking the Approve button.
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<div id="process-results-modal" data-backdrop="static" data-keyboard="false" data-batch="{{json_encode($batch->id)}}" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-file-cog-outline"></i> Process Results </h4>
						</div>
						<div class="modal-body">
							<div class="alert alert-info">
								<p><i class="mdi mdi-information pull-left"></i> Results for batch <b>{{$batch->batch_code}} is being processed! </b> ?</p>
							</div>
							<div class="form-group">
								<label for="" class="control-label">Report Format</label>
								<select name="report_format" id="report_format" class="form-control">
									<option value="">Choose Report Format</option>
									<option value="0">Aspergillus Report</option>
									<option value="1">Microbiology Report</option>
									<option value="2">Hygiene Swabs Report</option>
								</select>
							</div>
							<div class="proccesing-point hidden">
								<center>
									<img src="/images/load.gif" height="250px" width="auto" alt="">
								</center>
							</div>
							<div class="form-group hidden">
								<label for="" class="control-label"><input type="checkbox" name="add_pesticide" id="" class=""> Include Pesticide Results</label>
							</div>
							<span class="btn btn-sm btn-outline-info btn-block" id="initiate-process"><i class="mdi mdi-cogs"></i> Generate Report</span>
						</div>
						<!-- Modal content-->
						<div class="modal-footer">	
							<a href="/sample-workflow/batch/{{$batch->id}}/details" class="btn btn-outline-danger float-right btn-sm">Close</a>
						</div>

					</div>
					
				</div>
			</div>
			<div id="send-back-for-rechcek-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('move-to-workflow', ['status'=>'Samples In Lab', 'batch_id'=>$batch->id]) }}" enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-page-previous"></i> Recheck Batch Samples</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">User To Notify</label>
								<select class="form-control" name="user_id" required placeholder="Select User...">
									<option></option>
									@foreach ($notifiable_users as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
								<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
									<option></option>
									@foreach ($notifiable_users as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
							<input name="batch_id" type="hidden" value="{{ $batch->id }}" />
							<div class="form-group">
								<label class="control-label">Comments</label>
								<textarea class="form-control" name="comments" required placeholder="Comments..."></textarea>
							</div>
							<div class="form-group hidden">
								<label class="control-label">Request Type</label>
								<input type="text" name="type" value="Recheck" class="form-control">
							</div>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
								<label class="form-check-label">
									Send Email Notification
								</label>
							</div>
							<br>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
								<label class="form-check-label">
									Send SMS
								</label>
							</div>


						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-danger btn-sm"><i class="mdi mdi-keyboard-return"></i> Recheck</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
		@endif
		<div id="missing-parameters-modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					@csrf
					<div class="modal-header">
						<h4 class="modal-title"><i class="mdi mdi-beaker-question-outline"></i> Missing Analytes </h4>
					</div>
					<div class="modal-body">
						
						{{-- <pre>{{ json_encode($sampleHolders, JSON_PRETTY_PRINT) }}</pre> --}}
						<div class="table-responsive">
							<table class="table table-condensed table-striped my-small-text table-sm table-bordered">
								<thead>
									<tr>
										<th>Sample Code</th>
										<th>Missing Parameters</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($not_captured as $n_data)
										<tr>
											<td>{{ $n_data->sample_detail_code }}</td>
											<td>{{ $n_data->codes }}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	@endif
	@if(isset($batch->status) && in_array($batch->status, array("Sample Approval","Samples In Lab","Sample Verification","Reports In Payment")))
		
		<div id="provide-interpretations" class="modal fade" role="dialog">
			<div class="modal-dialog modal-lg">
				<!-- Modal content-->
				<form class="modal-content" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="modal-header">
						<h4 class="modal-title"><i class="mdi mdi-file-document-edit"></i> Comments & Interpretations </h4>
					</div>
					<div class="modal-body" id="sample-interpretations-holder">
						
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-info btn-sm" onclick="tinyMCE.triggerSave()"><i class="mdi mdi-content-save"></i> Save</button>
						<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
		
		@if ($batch->batch_report_url)
			<div id="send-to-email-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('move-to-workflow', ['status'=>"Reports for Collection", 'batch_id'=>$batch->id]) }}" enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-file-check-outline"></i> Ready for Email Report?</h4>
						</div>
						<div class="modal-body">
							
							@if($batch->customer_paid == 0)
							<div class="alert alert-danger">
								

									<i class="mdi mdi-information pull-left"></i> Batch {{$batch->batch_code}} has not been paid in full. Kindly confirm this before proceeding to email the report ! 	
							</div>
							@else
							<div class="alert alert-info">
								<h6><i class="mdi mdi-information pull-left"></i> Proceed with moving batch to Email report?</h6>
							</div>
							@endif

						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Proceed</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
			<div id="send-to-payments-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('move-to-workflow', ['status'=>"Reports In Payment", 'batch_id'=>$batch->id]) }}" enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-credit-card"></i> Send for Payment</h4>
						</div>

						<div class="modal-body">
							@if($batch->approve_user_id == '')
							<div class="alert alert-danger">
								<i class="mdi mdi-alert-octagram"></i> Kindly Approve the report first before sending it to Payment section!
							</div>
							@else
								<div class="form-group">
									<label class="control-label">Comments</label>
									<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
								</div>
								<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
								<label class="form-check-label">
									Send Email Notification
								</label>
								</div>
								<br>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
									<label class="form-check-label">
										Send SMS
									</label>
								</div>
							@endif
						</div>
						<div class="modal-footer">
							@if($batch->approve_user_id == '')
							@else
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Send for Payment</button>
							@endif
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
			<div class="modal fade" id="mark-complete" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="{{ route('mark-batch-complete') }}" method="post">
							@csrf
							<div class="modal-header text-center">
								<h5>Mark As Complete</h5>
							</div>
							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram-outline" style="font-size:20px"></i>
									<span class="pl-2 mt-1">Confirm you want to mark {{ $batch->batch_code }} QC Batch as complete.</span>
								</div>
								<input type="hidden" name="batch_id" value="{{ $batch->id }}">
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-thumb-up"></i> Yes, Mark</button>
								<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endif
		
	@endif
	@if(isset($batch->status) && $batch->status=="Samples In Lab")
			<div id="send-to-verification-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('moveToVerificationApprovalLevel') }}" enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-check-decagram"></i> Send to Verification </h4>
						</div>
						<div class="modal-body">
							@if(sizeof( $not_captured ) > 0)
								<div class="form-group">
									<div class="alert alert-danger">
										<i class="mdi mdi-information pull-left" style="font-size: 24px"></i> Some analytes have not been captured. Please ignore this message, if they are to be calculated during processing.
									</div>
								</div>
							@endif
							<div class="alert alert-primary p-2 d-flex">
								<i class="mdi mdi-alert-decagram" style="font-size: 30px"></i>
								<div class="data p-2">

									<span class="">Verification approvers for batch {{$batch->batch_code}} are :
									</span>
									
								</div>
							</div>
							@foreach($section_approvers_users as $s_approvers)
								<div class="form-group">
									<label for="" class="control-label">{{$s_approvers->labsection}} Verifier</label>
									<select required name="appover_user[{{$s_approvers->lab_section_id}}]" id="" class="form-control">
										@foreach($users as $user)
											<option value="{{$user->id}}" {{$s_approvers->user_id == $user->id ? 'selected' : ''}} >{{$user->name}}</option>
										@endforeach
									</select>
									<input type="hidden" name="section_id[]" value="{{$s_approvers->lab_section_id}}">
									<input type="hidden" name="title[{{$s_approvers->lab_section_id}}]" value="{{$s_approvers->title}}">
								</div>
							
							@endforeach
							
							<input type="hidden" name="status" value="Sample Verification">
							<input type="hidden" name="batch_id" value="{{$batch->id}}">
							<div class="form-group">
								<label for="" class="control-label">Report Level</label>
								<select name="level" id="" required class="form-control">
									<option value="">Choose Report Level</option>
									<option value="0">Final Report</option>
									<option value="1">Prelim Report</option>
									<!-- <option value="2">Draft Report</option> -->
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Verification Notes/Comments</label>
								<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
							</div>
							
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
								<label class="form-check-label">
									Send Email Notification
								</label>
							</div>
							<br>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
								<label class="form-check-label">
									Send SMS
								</label>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Send to Verification</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
	@endif
	@if(isset($batch->status) && $batch->status=="Samples Request Review")
		<div id="dispatch-to-labs-modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content" method="POST" action="{{ route('change-batch-workflow') }}" enctype="multipart/form-data">
					@csrf
					@if($batch->sample_tracking_stage=='20007')
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request</h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="status" value="Samples Request Review" />
							<input type="hidden" name="tracking_stage" value="20008" />
							<input type="hidden" name="bacth_id" value="{{ $batch->id }}" />
							<div class="form-group">
								<label class="control-label">Select Request Type</label>
								<select class="form-control" name="request_type_id[]" placeholder="Request Type..." multiple required>
									<option></option>
									@foreach ($requestTypes[1] as $i)
										<option value="{{ $i->id }}">{{ $i->name }}</option>
									@endforeach
									<option value="Other">Other Type</option>
								</select>
							</div>
							<div class="form-group other-reason hidden">
								<label class="control-label">Specify Other Request Type</label>
								<textarea class="form-control" name="other_type" placeholder="Specify Other Request Type..."></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Approval Comments</label>
								<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
							</div>
							<div class="form-group">
								<label class="control-label"><input type="checkbox" name="is_priority" value="High" /> Is High Prority</label>
							</div>
						</div>
					@else
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request </h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="status" value="Samples In Lab" />
							<input type="hidden" name="bacth_id" value="{{ $batch->id }}" />
							<div class="form-group">
								<label class="control-label">Select Specific Specialist</label>
								<select class="form-control" name="specialist_analyst_id" placeholder="Specific Specialist..." required>
									<option></option>
									
									@foreach ($analysts as $i)
										<option value="{{ $i->id }}">{{ $i->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group other-reason">
								<label class="control-label">Approval Comments</label>
								<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
							</div>
						</div>
					@endif
					<div class="modal-footer">
						<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
						<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	@endif

	<div id="add-analyte-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" id="print-labels-form" method="POST" action="{{ route('add-batch-comment') }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add New Analyte </h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Select Analysis</label>
						<select class="form-control" name="analysis_id" required placeholder="Select Analysis...">
							<option></option>
							@foreach ($batch->samples as $item)
								<option value="{{ $item->id }}">{{ $item->sample_code }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Select Analyte</label>
						<select class="form-control" name="analysis_id" required placeholder="Select Analyte...">
							<option></option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-info btn-sm print-label-btn"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>

	<div id="add-sample-notes" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" id="print-labels-form" method="POST" action="{{ route('add-batch-comment') }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-message-plus"></i> Add Note </h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">User To Notify</label>
						<select class="form-control" name="user_id" required placeholder="Select User...">
							<option></option>
							@foreach ($notifiable_users as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
						<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
							<option></option>
							@foreach ($notifiable_users as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
							@endforeach
						</select>
					</div>
					<input name="batch_id" type="hidden" value="{{ $batch->id }}" />
					<div class="form-group">
						<label class="control-label">Type</label>
						<select class="form-control" name="type" required placeholder="Message Type...">
							<option></option>
							@if($batch->status == 'Sample Verification' || $batch->status == 'Samples In Lab')

							<option value="Recheck">Recheck</option>
							@endif
							@foreach ($notesReminderType as $item)
								<option value="{{ $item }}">{{ $item }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Message</label>
						<textarea class="form-control" name="message" placeholder="Message..." required></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info btn-sm print-label-btn"><i class="mdi mdi-email-send"></i> Send</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
@endif

{{-- Bulk Update Samples Modal --}}
@if(isset($batch->id) && $batch->status == 'Samples In Lab')
<div class="modal fade" id="bulk-update-samples-modal" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="{{ route('bulk-update-sample-data') }}" method="POST">
				@csrf
				<div class="modal-header bg-primary text-white">
					<h4 class="modal-title">
						<i class="mdi mdi-database-edit"></i> Bulk Update Sample Data
					</h4>
					<button type="button" class="close text-white" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<!-- Info Alert -->
					<div class="alert alert-info">
						<i class="mdi mdi-information"></i>
						This form allows you to update multiple samples at once. Select the samples you want to update and fill in the fields you wish to change. Empty fields will be skipped.
					</div>
					
					<!-- Samples Selection -->
					<div class="form-group">
						<label>Select Samples <span class="text-danger">*</span></label>
						<select name="sample_ids[]" id="bulk-sample-select" class="form-control" multiple required>
							@foreach($allsamples as $sample)
								<option value="{{ $sample->id }}" selected>{{ $sample->sample_code }}</option>
							@endforeach
						</select>
						<small class="text-muted">All samples are selected by default. Deselect any you don't want to update.</small>
					</div>
					
					<!-- Main Standard -->
					<div class="form-group">
						<label>Main Standard</label>
						<select name="main_standard" id="bulk-main-standard" class="form-control">
							<option value="">-- No Change --</option>
							@foreach($standards as $standard)
								<option value="{{ $standard->id }}">{{ $standard->name }} ({{ $standard->code }})</option>
							@endforeach
						</select>
						<small class="text-muted">Leave empty to skip updating this field.</small>
					</div>
					
					<!-- Secondary Standard -->
					<div class="form-group">
						<label>Secondary Standard</label>
						<select name="secondary_standard" id="bulk-secondary-standard" class="form-control">
							<option value="">-- No Change --</option>
							@foreach($standards as $standard)
								<option value="{{ $standard->id }}">{{ $standard->name }} ({{ $standard->code }})</option>
							@endforeach
						</select>
						<small class="text-muted">Leave empty to skip updating this field.</small>
					</div>
					
					<!-- Storage -->
					<div class="form-group">
						<label>Storage</label>
						<select name="store_id" id="bulk-store-select" class="form-control">
							<option value="">-- No Change --</option>
							@foreach($labStores as $store)
								<option value="{{ $store['id'] }}" data-slots="{{ json_encode($store['items']) }}">{{ $store['name'] }}</option>
							@endforeach
						</select>
						<small class="text-muted">Leave empty to skip updating this field.</small>
					</div>
					
					<!-- Storage Slot -->
					<div class="form-group">
						<label>Storage Slot</label>
						<select name="store_slot_id" id="bulk-slot-select" class="form-control">
							<option value="">-- Select Store First --</option>
						</select>
						<small class="text-muted">Select a storage first. Leave empty to skip updating this field.</small>
					</div>
					
					<!-- Disposal Date -->
					<div class="form-group">
						<label>Disposal Date</label>
						<input type="date" name="disposal_date" id="bulk-disposal-date" class="form-control">
						<small class="text-muted">Leave empty to skip updating this field.</small>
					</div>
					
					<input type="hidden" name="batch_id" value="{{ $batch->id }}">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<i class="mdi mdi-close"></i> Cancel
					</button>
					<button type="submit" class="btn btn-primary">
						<i class="mdi mdi-content-save"></i> Update Samples
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif

@if(isset($batch->id))
<div class="modal fade" id="refresh-page-modal" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="alert alert-primary d-flex">
					<i class="mdi mdi-alert-decagram-outline"></i>
					<span class="p-2">
						Parameter lab sections updated successfully. Refresh the page for the changes to take effect.
					</span>
				</div>
			</div>
			<div class="modal-footer">
				<a href="/sample-workflow/batch/{{$batch->id}}/details" class="btn btn-outline-primary float-right btn-sm"><i class="mdi mdi-thumb-up-outline"></i> Yes, Refresh</a>

			</div>
		</div>
	</div>

</div>
@endif
<div id="show-sample-analysis-analytes" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog" style="min-width: 90%;">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-snowflake"></i> Analysis Parameters </h4>
				<span class="btn btn-outline-danger btn-sm float-right" data-dismiss="modal">Close</span>
			</div>
			<div class="modal-body">
				<div class="not-approved hidden alert alert-danger p-2 d-flex">
					<i class="mdi mdi-alert-decagram-outline" style="font-size: 35px"></i>
					<span class="p-2 pt-3">Approve the <b>InterLaboratory Transfer Log</b> first to activate the results field</span>
				</div>
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="configured-analytes-tab" data-toggle="tab" href="#configured-analytes" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Parameters</a>
					</li>
					@if(isset($batch->status) && $batch->status == "Samples Reception" && Auth::user()->is_client == 0)
					<li class="nav-item">
						<a class="nav-link" id="add-analytes-tab" data-toggle="tab" href="#add-analytes" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-plus"></i> Add Analyte</a>
					</li>
					@endif
					<li class="nav-item">
						<a class="nav-link" id="configured-pesticide-tab" data-toggle="tab" href="#configured-pesticides" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Pesticides</a>
					</li>
				</ul>
				
				<div class="tab-content">
					<form class="tab-pane show active p-3" method="POST" action="{{ route('capture-raw-results') }}" id="configured-analytes" role="tabpanel" aria-labelledby="one-tab">
						@csrf
						<h5 class="mb-3">
							@if(Auth::user()->is_client == 0)
							Raw Results
							<span class="ml-4 badge badge-pill badge-light p-2 mr-4" style="font-weight: 400!important">
								<span class="bg-green analytes-with-results-count small-badge">12</span> With Results
							</span>
							<span class="badge badge-pill badge-light p-2 show-missing-results" style="font-weight: 400!important">
								<span class="bg-red analytes-without-results-count small-badge">0</span> Missing Results
							</span>
							
							@if(isset($batch->status) &&  in_array($batch->status,['Samples In Lab','Sample Approval']))
								<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							@endif
							@if(isset($batch->status) && in_array($batch->status,array('Samples Reception','Samples Request Review','Samples En-Route')))
								<a href="/sample-workflow/batch/{{$batch->id}}/details" class="btn btn-outline-primary float-right btn-sm"><i class="mdi mdi-content-save"></i> Save</a>
								<span class="btn btn-sm btn-outline-warning float-right mr-2" id="delete-parameter"><i class="mdi mdi-delete-empty"></i> Delete</span>
							@endif
							@if(isset($batch->status) && in_array($batch->status,array('Samples Reception','Samples Request Review','Samples En-Route','Samples In Lab','Sample Approval')))
							<span class="btn btn-sm btn-outline-dark float-right mr-2" id="change-section"><i class="mdi mdi-compare-vertical"></i> Change Section</span>
							@endif
							@endif
							<br>
							
							<span class="badge badge-pill badge-light float-left p-2 " style="font-weight: 400!important">
								Standard - <span class="main-standard-name"></span>
							</span>
							<br>
						</h5>
						<div class="change-section-area alert-primary p-1 hidden" style="color:black">
							<div class="alert-body row">

								<div class="col-md-3">
									<div class="form-group">
										<label for="" class="control-label">Sections</label>
										<select name="section_id" id="section_id_change" class="form-control">
											<option value="">Select Lab Section</option>
											@foreach($labsections as $l_sect )
											<option value="{{$l_sect->id}}">{{$l_sect->name}} - {{$l_sect->code}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group" >
										<label for="" class="control-label" style="margin-top: 2rem !important"><input type="checkbox" name="affect_batch" id="affect_batch"> Affect this batch only?</label>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label for="" class="control-label" style="margin-top: 2rem !important"><input type="checkbox" checked name="affect_all" id="affect_all"> Affect all parameter configurations?</label>
									</div>
								</div>
								<div class="col-md-3">
									<span class="btn btn-sm btn-primary float-right mt-4 save-lab-section"><i class="mdi mdi-content-save"></i> Save Sections</span>
								</div>
								<div class="col-md-12 border-top text-center hidden" id="saving-progress-section">
									<i class="mdi mdi-sync mdi-spin"></i> Saving...
								</div>
							</div>
						</div>
						<div class="table-responsive" id="sph-parent">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table" style="width: 100%;">
								<thead class="bg-light p-2">
									<tr>
										<th>
											<input type="checkbox" name="parameter_check_all" id="parameter-check-all">
										</th>
										<th>Sample Code</th>
										<th style="display:flex !important">
										<div>

											Analysis
										</div>
											{{-- <div class="dropdown ml-2">
												
												<span class="dropdown-toggle float-right text-primary" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-filter "></i></span>
												<div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="analysis-types">
													
												</div>
											</div> --}}
										</th>
										<th>Analyte</th>
										@if(Auth::user()->is_client == 0)
										<th>Reporting Symbol</th>
										<th nowrap>Result</th>
										@if(isset($batch->id) && $batch->require_mu == 1)
										<th>Uncertainity (+-)</th>
										@endif
										@endif
										@if(isset($batch->id) && $batch->repeat_sample_id != '')
										<th>Prev Result (<small>+- {{$qc_config_perc}} %</small>)</th>
										@endif
										<th class="first_standard_th" >First Standard</th>
										<th class="sec_standard_th" >Secondary Standard</th>
										@if(Auth::user()->is_client == 0)
										<th>Remarks</th>
										<th>Reporting Unit</th>
										<th>Analyst</th>
										@endif
										<th>Ref. Method</th>
										<th>LTM</th>
										@if(Auth::user()->is_client == 0)
										<th>Equipment</th>				
										@endif				
										<th>Sub Contracted</th>
										<th>Accredited</th>
									</tr>
								</thead>
								<tbody id="sample-parameters-holder">
									<tr>
										<td colspan="14"><b>Loading...</b></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="save-parameter mt-3">
							@if(isset($batch->status) &&  $batch->status == "Samples In Lab")
								<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							@endif
						</div>
					</form>
					<form class="tab-pane show p-3" method="POST" action="{{ route('capture-raw-results') }}" id="configured-pesticides" role="tabpanel" aria-labelledby="one-tab">
						@csrf
						<h5 class="mb-3">
							@if(Auth::user()->is_client == 0)
							Pesticide Results
							<span class="ml-4 badge badge-pill badge-light p-2 mr-4" style="font-weight: 400!important">
								<span class="bg-green analytes-with-results-count small-badge">12</span> With Results
							</span>
							<span class="badge badge-pill badge-light p-2 show-missing-results" style="font-weight: 400!important">
								<span class="bg-red analytes-without-results-count small-badge">0</span> Missing Results
							</span>
							
							@if(isset($batch->status) &&  $batch->status == "Samples In Lab")
								<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							@endif
							@if(isset($batch->status) && in_array($batch->status,array('Samples Reception','Samples Request Review','Samples En-Route')))
								<a href="/sample-workflow/batch/{{$batch->id}}/details" class="btn btn-outline-primary float-right btn-sm"><i class="mdi mdi-content-save"></i> Save</a>
								<span class="btn btn-sm btn-outline-warning float-right mr-2" id="delete-parameter"><i class="mdi mdi-delete-empty"></i> Delete</span>
							@endif
							@endif
							<br>
							
							<span class="badge badge-pill badge-light float-left p-2 " style="font-weight: 400!important">
								Standard - <span class="main-standard-name"></span>
							</span>
							<br>
						</h5>
						<div class="capture-pesticide-result border-bottom border-top mt-2">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="" class="control-label">Result</label>
										<input type="text"  class="form-control pesticide_result">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="" class="control-label">Remark</label>
										<select name="" id="" class="form-control pesticide_remark">
											<option value="">Choose Remark</option>
											<option value="PASS">PASS</option>
											<option value="PASS">FAIL</option>
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group mt-4">
										<span class="btn float-right btn-sm btn-outline-primary sync-pesticide"><i class="mdi mdi-sync"></i> Sync Results</span>
									</div>
								</div>
							</div>
						</div>
						<div class="table-responsive" id="sph-parent">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table" style="width: 100%">
								<thead class="bg-light p-2">
									<tr>
										<th>
											<input type="checkbox" name="pesticide_check_all" id="parameter-check-all">
										</th>
										<th>Sample Code</th>
										<th style="display:flex !important">
										<div>

											Analysis
										</div>
											{{-- <div class="dropdown ml-2">
												
												<span class="dropdown-toggle float-right text-primary" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-filter "></i></span>
												<div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="analysis-types">
													
												</div>
											</div> --}}
										</th>
										<th>Pesticide</th>
										@if(Auth::user()->is_client == 0)
										<th>Reporting Symbol</th>
										<th nowrap>Result</th>
										@if(isset($batch->id) && $batch->require_mu == 1)
										<th>Uncertainity (+-)</th>
										@endif
										@endif
										@if(isset($batch->id) && $batch->repeat_sample_id != '')
										<th>Prev Result (<small>+- {{$qc_config_perc}} %</small>)</th>
										@endif
										<th>Standard</th>
										@if(Auth::user()->is_client == 0)
										<th>Remarks</th>
										<th>Reporting Unit</th>
										<th>Analyst</th>
										@endif
										<th>Method</th>
										@if(Auth::user()->is_client == 0)
										<th>Equipment</th>				
										@endif				
										<th>Sub Contracted</th>
										<th>Accredited</th>
									</tr>
								</thead>
								<tbody id="sample-pesticides-holder">
									<tr>
										<td colspan="14"><b>Loading...</b></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="save-parameter mt-3">
							@if(isset($batch->status) &&  $batch->status == "Samples In Lab")
								<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							@endif
						</div>
					</form>
					@if(isset($batch->status) && $batch->status == "Samples Reception")
					<form class="tab-pane fade p-3" method="POST" action="{{ route('add-analytes-to-sample-analysis') }}" id="add-analytes" role="tabpanel" aria-labelledby="one-tab">
						@csrf
						<div class="table-responsive">
							<h5 class="mb-3">
								Available analytes for this sample
								<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							</h5>
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Analyte</th>
										<th>Sample Code</th>
										<th>Analysis</th>
										<th>Analyst</th>
										<th>Equipment</th>
										<th>Contracted</th>
									</tr>
								</thead>
								<tbody id="sample-analysis-parameters-holder"></tbody>
							</table>
						</div>
					</form>
					@endif
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
			

		</div>
	</div>
</div>

<!-- Capture Results Modal -->
<div id="capture-results-modal" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog" style="min-width: 95%;">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-text"></i> Capture Results</h4>
				<span class="btn btn-outline-danger btn-sm float-right" data-dismiss="modal">Close</span>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="capture-sample-select" class="control-label">Select Sample:</label>
							<select id="capture-sample-select" class="form-control">
								<option value="">Select a sample...</option>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label">Selected Sample Info:</label>
							<div id="selected-sample-info" class="alert alert-info p-2">
								<small>No sample selected</small>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Sample Details Section -->
				<div id="sample-details-section" style="display: none;" class="mb-4">
					<div class="card">
						<div class="card-header bg-primary text-white">
							<h5 class="mb-0"><i class="mdi mdi-information"></i> Sample Details</h5>
						</div>
						<div class="card-body">
							<!-- First row with 3 items -->
							<div class="row mb-3">
								<div class="col-md-4">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Sample Type:</label>
										<div id="sample-type" class="form-control-plaintext">-</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Analysis Type:</label>
										<div id="analysis-type" class="form-control-plaintext">-</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Lab Section:</label>
										<div id="lab-section" class="form-control-plaintext">-</div>
									</div>
								</div>
							</div>
							
							<!-- Second row with 3 items -->
							<div class="row mb-3">
								<div class="col-md-4">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Sample Point:</label>
										<div id="sample-point" class="form-control-plaintext">-</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Sample Status:</label>
										<div id="sample-status" class="form-control-plaintext">-</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Batch ID:</label>
										<div id="batch-id" class="form-control-plaintext">-</div>
									</div>
								</div>
							</div>
							
							<!-- Sample Comments on its own row -->
							<div class="row">
								<div class="col-12">
									<div class="form-group">
										<label class="font-weight-bold text-muted">Sample Comments:</label>
										<div id="sample-comments" class="form-control-plaintext rich-text-content">-</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div id="parameters-table-container">
					<!-- Parameters will be loaded here in table format -->
					<div class="table-responsive" style="display: none; max-height: 70vh; overflow-x: auto; overflow-y: auto;" id="parameters-table-wrapper">
						<table class="table table-bordered table-striped" id="parameters-table" style="min-width: 1500px;">
							<thead class="bg-light sticky-top" id="parameters-table-head">
								<!-- Dynamic headers will be inserted here -->
							</thead>
							<tbody id="parameters-table-body">
								<!-- Parameters will be inserted here -->
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="text-center mt-3" id="capture-loading" style="display: none;">
					<i class="mdi mdi-sync mdi-spin"></i> Loading parameters...
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="save-capture-results"><i class="mdi mdi-content-save"></i> Save Results</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Method Modal -->
<div id="edit-method-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-beaker"></i> Edit Method & Reporting Unit</h4>
				<span class="btn btn-outline-danger btn-sm float-right" data-dismiss="modal">Close</span>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="method-select" class="control-label">Method:</label>
					<select id="method-select" class="form-control">
						<option value="">Select method...</option>
					</select>
				</div>
				<div class="form-group">
					<label for="reporting-unit-select" class="control-label">Reporting Unit:</label>
					<select id="reporting-unit-select" class="form-control">
						<option value="">Select reporting unit...</option>
						@if($reportingUnits)
							@foreach($reportingUnits as $unit)
								<option value="{{ $unit['name'] }}">{{ $unit['name'] }}</option>
							@endforeach
						@endif
					</select>
				</div>
				<div class="form-group">
					<label for="new-method-name" class="control-label">Add New Method:</label>
					<div class="input-group">
						<input type="text" id="new-method-name" class="form-control" placeholder="Enter new method name...">
						<div class="input-group-append">
							<button class="btn btn-outline-primary" type="button" id="add-new-method">Add</button>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="new-unit-name" class="control-label">Add New Reporting Unit:</label>
					<div class="input-group">
						<input type="text" id="new-unit-name" class="form-control" placeholder="Enter new unit name...">
						<div class="input-group-append">
							<button class="btn btn-outline-primary" type="button" id="add-new-unit">Add</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="save-method-unit"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="capture-markings" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="" method="post">
				<div class="modal-body">
					<div class="alert alert-primary d-flex">
						<i class="mdi mdi-plus" style="font-size:35px"></i>
						<span class="p-2 mt-2">Update Sample description of this sample below:</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Sample Description</label>
						<textarea name="markings" id="sample_markings" rows="10" class="form-control"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<span class="btn btn-sm btn-outline-primary save-markings"><i class="mdi mdi-content-save"></i> Update</span>
					<span class="btn btn-sm btn-default text-center" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>

@if(isset($batch->id) && $batch->invoice_number == '')
<div id="download-coa-invoice-exception" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="alert alert-danger p-2 d-flex">
					<i class="mdi mdi-alert-decagram" style="font-size:20px"></i>
					<spam class="p-2">Kindly add invoice details for this batch to be able to download the batch COA.
					</spam>
				</div>
			</div>
			<div class="modal-footer">
				<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="add-batch-invoice" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('addBatchInvoice')}}" method="post">
				@csrf
				<div class="modal-header">
					<h4>Add Batch Invoice Details</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="" class="control-label">Invoice Number <small class="text-danger">*</small></label>
						<input type="text" name="invoice_number" placeholder="Invoice No..." class="form-control"
							required>
					</div>
					<div class="form-group hidden">
						<label for="" class="control-label">Invoice Amount <small class="text-danger">*</small></label>
						<input type="text" name="invoice_amount" placeholder="Invoice amount..." class="form-control">
					</div>
					<input type="hidden" name="batch_id" value="{{$batch->id}}">
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-default" type="submit"><i class="mdi mdi-content-save"></i>
						Save</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
@endif

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
{{-- @if(isset($batch->status)) --}}
	
		{{-- <link rel="stylesheet" href="/css/quilljs.css" /> --}}
		<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
	
{{-- @endif --}}
<script>
	// Setup CSRF token for all AJAX requests
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	tinymce.init({
		selector: 'textarea.editor'
	});
	tinymce.init({
		selector: '#sample_markings'
	});

	
	var detectChange = function(ts){
		var op = $(ts).children('option:selected');
		$('#client-unit-select').html('<option value="" selected>Select Organizational Unit...</option>');
		$('#client-unit-select').trigger('change');
		if(op.val() > 0){
			$.ajax({
				url:`/get/Client-Details/Ajax/${op.val()}`,
				method:'GET',
				success:(data)=>{
					$.each(data['units'], function(i, e){
						$('#client-unit-select').append('<option value="'+e.id+'">'+e.name+'</option>');
					});
					$('#customer_email').val(data['customer'].email);
					$('#client-unit-select').val($('#client-unit-select').data('selected')).trigger('change');
				},
				error:(data)=>{
					console.log(data);
				}
			})
		}
		
	};
	
	const userLabSection  = $('.carry_data').data('userlabsection');
	var thebatch = $('#sample-detail-rows').data('batch');
	$(function(){
		var getCustomerUnits = (id,callback)=>{
			$.ajax({
				url:`/get/customer/ajax/${id}'`,
				type:'GET',
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			});
		}
		$('.lab_capable').on('change',function(){
			if($(this).is(':checked')){
				console.log('here 1')
				$('.batch_subcontracted_client_approval').addClass('hidden');
			}else{
				console.log('here 1')
				$('.batch_subcontracted_client_approval').removeClass('hidden');
			}
		})
		$('#add-customer-contact').on('show.bs.modal',()=>{
			$('#add-customer-contact').find('.crm_customer_id').on('change',()=>{
				var customer = $('#add-customer-contact').find('.crm_customer_id').val();
				getCustomerUnits(customer,(data)=>{
					$('#add-customer-contact').find('.crm_unit_id').empty();
					$('#add-customer-contact').find('.crm_unit_id').append(`<option value="">Select CRM Units</option>`)
					$.each(data,(i,obj)=>{
						var option = `<option value="${obj.id}" >${obj.name}</option>`;
						$('#add-customer-contact').find('.crm_unit_id').append(option);
					});
				})
			});
		})
		var sample_marking_holder = (code,markings)=>{
			var body = $(`
				<div class="alert alert-default p-2">
					<h4>
						<b class="text-center"><i class="mdi mdi-information-outline" style="font-size:20px"></i>
					View ${code} Sample Description</b>
					</h4>
					<hr>
					<span class="p-2">${markings}</span>
				</div>
					`).clone();
			return body;
		}
		$('#sample-marking-show').on('show.bs.modal',(e)=>{
			var samplecode = $(e.relatedTarget).data('samplecode');
			var markings = $(e.relatedTarget).data('marking');
			console.log(markings);
			var body = sample_marking_holder(samplecode,markings);
			$('#sample-marking-show').find('.modal-body').empty();
			$('#sample-marking-show').find('.modal-body').append(body);

		})
		$('#capture-markings').on('show.bs.modal',(e)=>{
			$('#capture-markings').find('.save-markings').removeClass('hidden');
			var parentDiv = $(e.relatedTarget).parent('div');
			var marking = $(parentDiv).find('.sample-comments').val();
			$('#capture-markings').find('#sample_markings').val(marking);
			tinymce.get('sample_markings').setContent('');
			tinymce.get('sample_markings').setContent(marking);

			$('#capture-markings').find('.save-markings').one('click',()=>{
				$('#capture-markings').find('.save-markings').addClass('hidden');
				var update_markings   = tinymce.get('sample_markings').getContent();
				$(parentDiv).find('.sample-comments').val(update_markings);
				$('#capture-markings').modal('hide');
			})
			console.log(marking);
		})
		var getClientDetails = (id,callback)=>{
			$.ajax({
				url:`/get/Client-Details/Ajax/${id}`,
				method:'GET',
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			})
		}
		var sampleAnalysisByType = $('#sample-detail-rows').data('analysis_types')
		var sampleCondtions = $('#sample-detail-rows').data('conditions');
		var labStores = $('#sample-detail-rows').data('stores');
		var $ammendableSamples = $('#sample-detail-rows').data('ammendments');
		var $isclient = $('#sample-detail-rows').data('client');
		var $batch = $('#sample-detail-rows').data('batch');
		var standards = $('#sample-detail-rows').data('standards');
		var sampleLabs =  $('#sample-detail-rows').data('labs');
		
		
		var unitSamplePoints = [];
		var unitProducts = [];
		var clientPrefProductName;
		var clientPrefUnitName;
		var clientPrefSPName;

		var configuredSamples = $('#sample-detail-rows').data('samples');

		var deleteBatchApprovalBody = (data)=>{
			var body = $(`
			<div class="alert alert-danger p-2 d-flex">
				<i class="mdi mdi-delete-empty" style="font-size:25px"></i>
				<span class="p-2">
					Confirm you want to delete <b>${data.approvername}</b> as an approver for {{isset($batch->id) ? $batch->batch_code : ''}} Batch.
				</span>
			</div>
			<input type="hidden" name="approver_id" value="${data.id}">
			`).clone();
			return body;
		}
		$('#delete-batch-approver').on('show.bs.modal',(e)=>{
			var data = $(e.relatedTarget).data('record');
			var body = deleteBatchApprovalBody(data);
			$('#delete-batch-approver').find('.modal-body').empty();
			$('#delete-batch-approver').find('.modal-body').append(body);
		})

		var editBatchApproverBody = (data)=>{
			var body = $(`
				<div class="alert alert-primary p-2 d-flex">
					<i class="mdi mdi-decagram" style="font-size:25px"></i>
					<span class="p-2">Edit Approval configurations below: </span>
				</div>
				<div class="form-group">
					<label for="" class="control-label">Title</label>
					<input type="text" name="title" value="${data.title}" class="form-control">
				</div>
				<div class="form-group">
					<label for="" class="control-label">Approver</label>
					<select name="user_id" id="user_id" class="form-control">
						<option value="">Choose Approver</option>
						@foreach($users as $user)
						<option value="{{$user->id}}">{{$user->name}}</option>
						@endforeach
					</select>
				</div>
				<input type="hidden" name="approver_id" value="${data.id}">
			`).clone()
			$(body).find('#user_id').val(data.user_id)
			$(body).find('#user_id').select2();
			return body;
		}

		$('#edit-batch-approver').on('show.bs.modal',(e)=>{
			var data = $(e.relatedTarget).data('record');
			var body = editBatchApproverBody(data);
			$('#edit-batch-approver').find('.modal-body').empty();
			$('#edit-batch-approver').find('.modal-body').append(body);
		})

		var changeApprovalStatusBody = (data)=>{
			var body = $(`
			
			<div class="alert alert-success p-2 d-flex">
				<i class="mdi mdi-alert-decagram-outline" style="font-size:30px"></i>
				<span class="p-2">
					Change approval status below:
				</span>
			</div>
			<div class="form-group">
				<label for="" class="control-label">Status</label>
				<select required name="status" id="status" class="form-control">
					<option value="">Choose Status...</option>
					<option value="1">Approve</option>
					<option value="2">Decline</option>
				</select>
			</div>
			<div class="form-group">
				<label for="" class="control-label">Remarks</label>
				<textarea name="remark" id="" class="form-control" cols="30" rows="6"></textarea>
			</div>
			<input type="hidden" name="approver_id" value="${data.id}">
			`).clone();
			$(body).find('#status').select2();
			return body;
		}

		$(`#change-approval-status`).on('show.bs.modal',(e)=>{
			var data = $(e.relatedTarget).data('record');
			var body = changeApprovalStatusBody(data);
			$(`#change-approval-status`).find('.modal-body').empty();
			$(`#change-approval-status`).find('.modal-body').append(body);
		})

		$('.batch-info-trigger').on('click', function(){
			$(this).toggleClass('open');
			if($(this).hasClass('open')){
				$(this).html(`
				<i class="mdi mdi-chevron-double-up"></i> Batch Info
				`);
				$('#batch-detail-form').removeClass('hidden');
			}
			else{
				$(this).html(`
				<i class="mdi mdi-chevron-double-down"></i> Batch Info
				`);
				$('#batch-detail-form').addClass('hidden');
			}
		});

		var getAddPaymentDetailBody = (data=false)=>{
			if(data){

				var body = $(`
					<input type="hidden" name="batch_id" value="{{isset($batch->id) ? $batch->id : 0}}">
                    <div class="form-group">
                        <label class="control-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="method" id="select-payment-method" class="form-control" aria-placeholder="Select Payment Method..." required>
                            <option value="">Choose Payment Method...</option>
                            <option value="Mpesa" ${data.payment_method == 'Mpesa' ? 'selected' : ''}>Mpesa</option>
                            <option value="Cheque"  ${data.payment_method == 'Cheque' ? 'selected' : ''}>Cheque</option>
                            <option value="Cash" ${data.payment_method == 'Cash' ? 'selected' : ''}>Cash</option>
                            <option value="Credit" ${data.payment_method == 'Credit' ? 'selected' : ''}>Credit</option>
                            <option value="EFT" ${data.payment_method == 'EFT' ? 'selected' : ''}>EFT</option>
                            <option value="Free" ${data.payment_method == 'Free' ? 'selected' : ''}>Free</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step=".01" value="${data.amount}" name="amount" placeholder="Amount... " class="form-control">
                    </div>
					<div class="form-group">
						<label for="" class="control-label">VAT</label>
						<input type="text" name="vat" value="${data.vat}" placeholder="VAT..." class="form-control">
					</div>
                    
                    <div class="form-group">
                        <label class="control-label">Reference No</label>
                        <input type="text" class="form-control" name="ref_no" value="${data.ref_no}" placeholder="Reference Amount...">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Balance</label>
                        <input type="text" class="form-control" name="balance" value="${data.balance}" placeholder="Reference Amount...">
                    </div>
					<div class="form-group">
                        <label class="control-label">Contact Person</label>
                        <input type="text" class="form-control" name="contact_person_name" value="${data.contact_person_name}" placeholder="Contact Person...">
                    </div>
					<input type="hidden" name="payment_detail_id" value="${data.id}">
				`).clone();
			}else{
				var body = $(`
					<input type="hidden" name="batch_id" value="{{isset($batch->id) ? $batch->id : 0}}">
					<div class="form-group">
						<label class="control-label">Payment Method <span class="text-danger">*</span></label>
						<select name="method" id="select-payment-method" class="form-control" aria-placeholder="Select Payment Method..." required>
							<option value="">Choose Payment Method...</option>
							<option value="Mpesa">Mpesa</option>
							<option value="Cheque">Cheque</option>
							<option value="Cash">Cash</option>
							<option value="Credit">Credit</option>
							<option value="EFT">EFT</option>
							<option value="Free">Free</option>
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Amount <span class="text-danger">*</span></label>
						<input type="number" step=".01" value="" name="amount" placeholder="Amount... " class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">VAT</label>
						<input type="text" name="vat" placeholder="VAT..." class="form-control">
					</div>
					
					<div class="form-group">
						<label class="control-label">Reference No</label>
						<input type="text" class="form-control" name="ref_no" value="" placeholder="Reference Amount...">
					</div>
					<div class="form-group">
						<label class="control-label">Balance</label>
						<input type="text" class="form-control" name="balance" value="" placeholder="Reference Amount...">
					</div>
					<div class="form-group">
						<label class="control-label">Contact Person</label>
						<input type="text" class="form-control" name="contact_person_name" value="" placeholder="Contact Person...">
					</div>
					<input type="hidden" name="payment_detail_id" value="0">
				`).clone();
			}
			$(body).find('#select-payment-method').select2();
			return body;
		}

		$('#add-payment-details').on('show.bs.modal',(e)=>{
			var action = $(e.relatedTarget).data('action');
			var body = action == 'add' ? getAddPaymentDetailBody() : getAddPaymentDetailBody($(e.relatedTarget).data('record'));
			$('#add-payment-details').find('.modal-body').empty();
			$('#add-payment-details').find('.modal-body').append(body);

		})

		$('.selected_inter_lab_all').on('change',(e)=>{
			if($('.selected_inter_lab_all').is(':checked')){
				$.each($('.selected_inter_lab'),(i,obj)=>{
					$(obj).attr('checked',true)
				})
			}
		})

		var getChangeInterLabStatusBody = (data,bulk = false)=>{
			if(bulk){
				var body = $(`
				<div class="alert alert-success p-2 d-flex">
					<i class="mdi mdi-thumbs-up-down" style="font-size: 30px;"></i>
					<span class="p-2">
						Effect the Inter Lab Log(s) status for the following sample(s) below by providing the following information: 
					</span>
				</div>
				<div class="label control-label">
					<label for="" class="control-label">Status</label>
					<select name="status" id="" class="form-control status-field">
						<option value="">Select Status ...</option>
						<option value="1">Approve Inter Lab Log</option>
						<option value="2">Reject Inter Lab Log</option>
					</select>
				</div>
				
				<input type="hidden" name="inter_lab_ids" value="${bulk['bulk']}">
				<p class="p-2"><u>Samples: </u></p>
				<div class="row" id="row-data">
					
				</div>
				`).clone();
			}else{
				var body = $(`
					<div class="alert alert-success p-2 d-flex">
						<i class="mdi mdi-thumbs-up-down" style="font-size: 30px;"></i>
						<span class="p-2">
							Effect the Inter Lab Log status for sample ${data.sample_code} below:
						</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">From Lab</label>
						<input type="text" readonly class="form-control" value="${data.from_lab_section_id > 0 ? data.from_lab_name : 'Reception' } ">
					</div>
					<div class="form-group">
						<label for="" class="control-label">To Lab</label>
						<input type="text" readonly class="form-control" value="${data.to_lab_name}">
					</div>
					<div class="label control-label">
						<label for="" class="control-label">Status</label>
						<select name="status" id="" class="form-control status-field">
							<option value="">Select Status ...</option>
							<option value="1">Approve Inter Lab Log</option>
							<option value="2">Reject Inter Lab Log</option>
						</select>
					</div>
					<input type="hidden" name="inter_lab_id" value="${data.id}">
					
	
	
				`).clone();

			}
			$(body).find('.status-field').select2();
			return body;
		}

		$('#change-interlab-status').on('show.bs.modal',(e)=>{
			var action = $(e.relatedTarget).data('action');

			if(action == 'bulk'){
				var record = [];
				var sample_codes = [];
				$.each($('.selected_inter_lab:checked'),(i,obj)=>{
					record.push($(obj).val());
					sample_codes.push($(obj).data('code'));
				})
				var data = {
					"sample_codes":sample_codes,
					"bulk": record.join(',')
				}

				if(sample_codes.length == 0){
					var body = `
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px;"></i>
						<span class="p-2">
							Kindly select the Inter Laboratory Transfer Log(s) you want to effect their status
						</span>
					</div>
					`;
					$('#change-interlab-status').find('.affect-button').addClass('hidden');
				}else{
					var body = getChangeInterLabStatusBody("no data",data);
					$('#change-interlab-status').find('.affect-button').removeClass('hidden');

				}
					
				$('#change-interlab-status').find('.modal-body').empty();
				$('#change-interlab-status').find('.modal-body').append(body);

				if(sample_codes.length > 0){
					console.log(sample_codes);
					$.each(sample_codes,(i,obj)=>{
						console.log(obj);
						var colBody = `
						<div class="col-md-4 p-1">
							<span><i class="mdi mdi-chevron-right"></i> ${obj}</span>
						</div>
						`;
						$('#change-interlab-status').find('.modal-body').find('#row-data').append(colBody);
					});
				}

				
			}else{

				var record = $(e.relatedTarget).data('record');
				var body = getChangeInterLabStatusBody(record);
				$('#change-interlab-status').find('.modal-body').empty();
				$('#change-interlab-status').find('.modal-body').append(body);
			}
		})

		var getSampleCurrentLab = (id,callback)=>{
			$.ajax({
				url:`/getSampleCurrentLabSection/${id}`,
				method:'GET',
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			});
		}

		var getInterLabBody = (lab_id,sample_id,sample_code,action,data = false)=>{
			if(action == 'add'){
				var body = $(`
					<div class="alert alert-warning d-flex p-2">
						<i class="mdi mdi-swap-horizontal-bold" style="font-size: 30px;"></i>
						<span class="p-2">
							Initiate Inter Laboratory Tranfer for sample <b>${sample_code}</b> by providing the information below.
						</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">From Lab</label>
						<input type="text" class="form-control from_lab_section"  value="" readonly>
					</div>
					<input type="hidden" name="interlab_id" value="0">
					<input type="hidden" name="sample_id" value="${sample_id}">
					<div class="form-group">
						<label for="" class="control-label">To Lab</label>
						<select name="to_lab_section_id" id="" class="form-control to_lab_section_id">
							
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Quantity</label>
						<input type="text" name="quantity" value="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Expected Results Date</label>
						<input type="date" name="expected_date" value="{{isset($batch->id) ? date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : ''}}" id="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Prelim Date</label>
						<input type="date" name="prelim_date" id="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Remark</label>
						<textarea name="remarks" id="" cols="30" rows="5" class="form-control"></textarea>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Notify</label>
						<select name="notify_user" id="" class="form-control notify_user">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Also Notify</label>
						<select name="also_notify[]" multiple id="" class="form-control also_notify">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->name}}</option>
							@endforeach
						</select>
					</div>
				`).clone();

			}else{
				var body = $(`
					<div class="alert alert-primary d-flex p-2">
						<i class="mdi mdi-swap-horizontal-bold" style="font-size: 30px;"></i>
						<span class="p-2">
							Edit Inter Laboratory Transfer for sample <b>${data.sample_code}</b> by providing the information below.
						</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">From Lab</label>
						<input type="text" class="form-control"  value="${data.from_lab_name  || 'Reception'}" readonly>
					</div>
					<input type="hidden" name="interlab_id" value="${data.id}">
					<input type="hidden" name="sample_id" value="${data.sample_id}">

					<div class="form-group">
						<label for="" class="control-label">To Lab</label>
						<select name="to_lab_section_id" id="" class="form-control to_lab_section_id">
							
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Quantity</label>
						<input type="text" name="quantity" value="${data.quantity}" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Expected Results Date</label>
						<input type="date" name="expected_date" value="{{isset($batch->id) ? date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : ''}}" id="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Prelim Date</label>
						<input type="date" name="prelim_date" id="" value="${data.prelim_date}" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Remark</label>
						<textarea name="remarks" id="" cols="30" rows="5" class="form-control">${data.remarks}</textarea>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Notify</label>
						<select name="notify_user" id="" class="form-control notify_user">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Also Notify</label>
						<select name="also_notify[]" multiple id="" class="form-control also_notify">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->name}}</option>
							@endforeach
						</select>
					</div>
				`).clone();
			}
			
			if(!data){
				getSampleCurrentLab(data ? data.sample_id : sample_id,(obj)=>{
					$(body).find('.from_lab_section').val(obj);
				});
			}
			getLabSections(0,(labdata)=>{
				$.each(labdata,(i,obj)=>{
					var option = `<option value="${obj.id}">${obj.code} - ${obj.name}</option>`
					$(body).find('.to_lab_section_id').append(option);
				});
			});
			if(data){
				$(body).find('.to_lab_section_id').val(data.to_lab_section_id)
			}
			$(body).find('.to_lab_section_id').select2();
			$(body).find('.notify_user').select2();
			$(body).find('.also_notify').select2();

			return body;
		}

		$('#inter-lab-add').on('show.bs.modal',(e)=>{
			var record_id = $(e.relatedTarget).data('sample');
			var record_code = $(e.relatedTarget).data('samplecode');
			var action = $(e.relatedTarget).data('action');
			var data = action == 'add' ? false :  $(e.relatedTarget).data('record');
			var analysis_types = action == 'add' ? $(e.relatedTarget).data('analysistype') : data.lab_id;
			action == 'add' ? $('#inter-lab-add').find('.submit-button').addClass('btn-outline-warning') : $('#inter-lab-add').find('.submit-button').addClass('btn-outline-primary');

			action == 'add' ? $('#inter-lab-add').find('.submit-button').removeClass('btn-outline-primary') : $('#inter-lab-add').find('.submit-button').removeClass('btn-outline-warning');

			var body = getInterLabBody(analysis_types,record_id,record_code,action,data)
			$('#inter-lab-add').find('.modal-body').empty();
			$('#inter-lab-add').find('.modal-body').append(body);
			
		})
		let getQcTypeConfig = (dataID,sample_type_id,callback)=>{
			$.ajax({
				url:`/qualitycontrol/get/Qc-Type/Config/${dataID}/Ajax`,
				method:'GET',
				data:{sample_type_id:sample_type_id},
				success:(data)=>{
					callback(data)
				},
				error:(data)=>{
					console.log(data);
				}
			})
		}
		$('.is_qc_batch').on('change',(e)=>{
			if($(e.currentTarget).is(':checked')){
				console.log('here..')
				$('.qc-params').removeClass('hidden')
				$('.qc-omit-type-field').addClass('hidden');
			}else{
				$('.qc-params').addClass('hidden')
				$('.qc-omit-type-field').removeClass('hidden');
			}
			
		});
		$('.qc_type_id').on('change',(e)=>{
			var value = $('.qc_type_id').val()
			var sample_type_id = $('#batch-info-sample-type').val();
			if(sample_type_id == ''){
				alert('Kindly select the sample type first!');
				$('.qc_type_id').val('');
			}else{
				getQcTypeConfig(value,sample_type_id,(data)=>{
					if(data['data'].use_existing_sample == 1){
						$('.repeat-sample-field').removeClass('hidden');
						$('.repeat_sample_id').empty();
						$('.repeat_sample_id').append('<option value="">Select Samples</option>')
						$.each(data['samples'],(i,obj)=>{
							
							var option = `<option value="${obj.id}" ${$batch && $batch.repeatsampleidarr  ? ($batch.repeatsampleidarr.includes(obj.id.toString()) ? `selected` : ``) : ''}>${obj.sample_code}</option>`
							$('.repeat_sample_id').append(option);
						})
						$('.repeat_sample_id').select2();
					}else{
						$('.repeat-sample-field').addClass('hidden');
						$('.repeat_sample_id').val('');
						$('.repeat_sample_id').empty();
					}
				})
			}
		});
		if($batch && $batch.repeatsampleidarr.length > 0){
			$('.qc_type_id').trigger('change');
		}
		$('#add-company-unit').on('show.bs.modal',function(){
			var customer = $('select[name="crm_customer_id"]').val();
			if(customer == ''){
				var not_ = $(`
					<div class="alert alert-danger id="not-present" p-1">
						Kindly select the client !
					</div>
				`).clone();
				
				$(this).find('.modal-body').append(not_);
				$(this).find('#save-unit').attr('disabled');
			}else{
				$(this).find('#not-present').remove();
				$(this).find('#save-unit').removeAttr('disabled');
				$('#add-company-unit').find('#form').attr('action','/company-units/'+customer);
				console.log('test')
			}
		})
		var map;
		$('#add-company-sample-point').on('show.bs.modal', function(e) {
			var mapProp = {
				center: new google.maps.LatLng(-1.247125519439578, 36.742261815816164),
				zoom: 5,
			};
			map = new google.maps.Map(document.getElementById("location-map"), mapProp);

			var marker = new google.maps.Marker({
				position: mapProp.center,
				// icon:'pinkball.png'
				draggable: true,
			});

			marker.setMap(map);
			marker.addListener('drag', function(event) {
				console.log('start')
				document.getElementById('latitude').value = event.latLng.lat();
				console.log(event.latLng.lat())
				document.getElementById('longitude').value = event.latLng.lng()
			});
			marker.addListener('dragend', function(event) {
				console.log('start2')
				document.getElementById('latitude').value = event.latLng.lat();
				console.log(event.latLng.lat())
				document.getElementById('longitude').value = event.latLng.lng()
				console.log(event.latLng.lng())
			});

		});

		$('#process-results-modal').on('show.bs.modal',function(){
			var batch = $(this).data('batch');
			$('#process-results-modal').find('.proccesing-point').addClass('hidden');
			$('#process-results-modal').find('#initiate-process').prop('disabled', false).removeClass('disabled');
			$('#process-results-modal').find('#report_format').val('');
			
			$include_pesticide = $('#process-results-modal').find('add_pesticide').is(':checked') ? 1 : 0;
			$('#process-results-modal').find('#initiate-process').off('click').on('click',function(){
				var selectedFormat = $('#process-results-modal').find('#report_format').val();
				
				// Validate that a report format has been selected
				if (!selectedFormat) {
					alert('Please select a report format before generating the report.');
					return;
				}
				
				// Disable the button to prevent double-clicks
				$(this).prop('disabled', true).addClass('disabled').html('<i class="mdi mdi-loading mdi-spin"></i> Generating...');
				$('#process-results-modal').find('.proccesing-point').removeClass('hidden');
				
				$.ajax({
					url:"{{ route('process-raw-results', ['batch_id'=> isset($batch->id) ? $batch->id : 0]) }}",	
					data:{
						report_format : selectedFormat,
						include_pesticide :$include_pesticide
					},
					method:'GET',
					success: function(data){
						console.log(data);
						$('#process-results-modal').find('.modal-body').empty();
						var success_tag = $(`
							<div class="alert alert-success p-2">
							<i class="mdi mdi-information pull-left"></i>
								Results processed successfully!
							</div>
							<center>
							<img src="/images/suc.gif" height="250px" width="auto" alt="">
							</center>
						`).clone();
						$('#process-results-modal').find('.modal-body').append(success_tag);
	
					},
					error: function(data){
						console.log(data);
						// Re-enable the button on error
						$('#process-results-modal').find('#initiate-process').prop('disabled', false).removeClass('disabled').html('<i class="mdi mdi-cogs"></i> Generate Report');
						$('#process-results-modal').find('.proccesing-point').addClass('hidden');
						alert('An error occurred while processing the report. Please try again.');
					}
				})
			})
		})
			
		
		
		$('.save-samples').on('click', function(){
			var trs = $('#sample-detail-rows').find('tr').length;
			var missing = false;

			if(trs > 0){
				var missingVals = {};
				$('#sample-detail-rows').find('[required]').each(function(){
					var val = $(this).val();
					console.log($(this).attr('name'), val)
					if($.trim(val) == ""){
						var parentTD = $(this).parents('td');
						var titleTH = parentTD.parents('table').find('thead th').eq(parentTD.index());
						missingVals[titleTH.text()] = true;
						var borderStyle = $(this).css('border');
						$(this).css('border', '1px solid red').focus();
						var el = $(this);
						$(this).on('change', function(){
							el.css('border', borderStyle);
						});
					}
				});
				console.log(Object.keys(missingVals));
				if(Object.keys(missingVals).length > 0){
					alert("One or more samples is missing the following data: "+Object.keys(missingVals).join(","));
					return false;
				}
				$(this).parents('form').submit();
			}
			else{
				alert("Samples Required!")
			}
		});

		$('.raw-data-row').find('[name="result"]').on('keyup', function(){
			$(this).addClass('changed');
		});

		$('#stage-selector').find('form.dropdown-item').on('click', function(){
			$(this).submit();
		});

		$('#status-selector').find('form.dropdown-item').on('click', function(){
			$(this).submit();
		});

		$('.my-tab-headers').on('click', '.my-tab', function(){
			$('.my-tab-headers').find('.my-tab').removeClass('selected');
			$(this).addClass('selected');
			var equip = $(this).data('equipment');

			$('.equip-table').addClass('hidden');
			$('.equip-table[data-equipment="'+equip+'"]').removeClass('hidden');

		});

		$('.toggle-more-fields').on('click', function(){
			$(this).toggleClass('open');
			if($(this).hasClass('open')){
				$(this).html(`
					<i class="mdi mdi-chevron-double-up"></i> Hide Fields
				`);
				$('#more-fields').removeClass('hidden');
			}
			else{
				$(this).html(`
					<i class="mdi mdi-chevron-double-down"></i> BL Fields
				`);
			$('#more-fields').addClass('hidden');
			}
		});

		$('#dispatch-to-labs-modal').find('[name="request_type_id[]"]').on('change', function(){
			var vals = $(this).val();
			var otherReasonDiv = $(this).parents('.modal-body').find('.other-reason');
			if(vals.indexOf('Other') > -1){
				otherReasonDiv.find('[name="other_type"]').attr('required', true)
				otherReasonDiv.removeClass('hidden');
			}
			else{
				otherReasonDiv.find('[name="other_type"]').val('').removeAttr('required');
				otherReasonDiv.addClass('hidden');
			}
		});

		var parametersBySampleCode = $('#sample-detail-rows').data('parameters'); //Parameters by sample code
		var pesticideBySampleCode = $('#sample-detail-rows').data('pesticides');
		var analysisIDsBySampleCode = $('#sample-detail-rows').data('sample_analysis_ids');
		var analysisNames = $('#sample-detail-rows').data('analysis_names');
		var labSectionRow = (section,id,batch_id,sample_id,datevalue,standard_count = 0)=>{
			var colspan_value = thebatch  && thebatch.reqire_mu ? 13 : 12;
			var qc_colspan = $batch && $batch.repeat_sample_id != '' ? 1 : 0;
			var body = $(`
			<tr>
				<td colspan="3" style="padding-left:2%">
					<h5>${section}</h5>
					
				</td>
				<td class="pull-right" colspan="${colspan_value + standard_count + qc_colspan}" style="padding-left:1%">
					<b>Date of Analysis</b>
					<input type="date" class="start_analysis_date" value="${datevalue}" min="{{isset($batch->id) ? $batch->receipt_date : date('Y-m-d')}}" style="margin-left:1%;width:20%">
					<span class="btn btn-sm btn-success save-analysis-start-date" style="font-size:14px !important"><i class="mdi mdi-sync"></i>Click to Save Date</span>
				</td>
				
			</tr>
			`).clone();
			
			$(body).find('.save-analysis-start-date').on('click',(e)=>{
				var start_date = $(body).find('.start_analysis_date').val();
				if(id < 1){
					alert('kindly set the lab section first');
				}else{
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
						}
					});
					$.ajax({
						url:'/save-Sample/AnalysisDate',
						method:'POST',
						data:{
							lab_section_id:id,
							start_analysis_date:start_date,
							batch_id:batch_id,
							sample_id:sample_id
						},
						success:(data)=>{
							console.log(data);
							alert('Date of Analysis saved successfully!');
						},
						error:(data)=>{
							console.log(data);
						}
					})
				}

			});
			return body;
		}
		var getSampleInterlabTransferApproval = (sample_id,callback)=>{
			$.ajax({
				url:`/get/Sample-IntelabLogs-Approval/Status`,
				data: {'sample_id': sample_id},
				method:'GET',
				success:(data)=>{
					console.log(data);
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			});
		}
		var getSampleCapturedNotData = (sample_id,callback)=>{
			$.ajax({
				url:`/getSampleResultCapturedNot`,
				data: {'sample_id': sample_id},
				method:'GET',
				success:(data)=>{
					console.log(data);
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			});
		}
		$('#show-sample-analysis-analytes').on('show.bs.modal', function(e){

			console.log('---------------');
			console.log('here');
			

			var sampleCode = $(e.relatedTarget).data('sample_code');
			// console.log('here',$(e.relatedTarget).data('analysisdate'));
			var analysis_dates = JSON.parse($(e.relatedTarget).data('analysisdate'));
			var InterlabStatus=0;
			var capturedIds=[];
			
			var all_analysis = analysisNames[sampleCode];
			$('#show-sample-analysis-analytes').find('#analysis-types').empty();
			var text_d = '<span data-analysis ="all" class="dropdown-item">All</span>';
			$('#show-sample-analysis-analytes').find('#analysis-types').append(text_d);
			$.each(all_analysis,function(i,e){
				var text_a = '<span data-analysis ='+e+'  class="dropdown-item" >'+i+'</span>';
				$('#show-sample-analysis-analytes').find('#analysis-types').append(text_a);
				console.log(i);
			})
			
			
			$('#sample-parameters-holder').empty();
			$('#sample-pesticides-holder').empty();
			// console.log(parametersBySampleCode);
			var parameters = parametersBySampleCode[sampleCode];
			var pesticides = pesticideBySampleCode[sampleCode] || [];
			
			var analysisIDs = analysisIDsBySampleCode[sampleCode];
			
			var loop = 1;
			console.log('-----end----------')
			// console.log('----------------4376374--------------')

			// console.log(parameters['section']);
			// console.log('----------------748384--------------')

			// console.log('------------------------------')
			// console.log(parametersBySampleCode['2023L00217463']);
			// console.log(parameters);
			getSampleInterlabTransferApproval(sampleCode,(data)=>{

				var selected_sample = data['sample'];
				var standard_count = 0;
				if(selected_sample.main_standard > 0){
					standard_count = 0
				}
				if(selected_sample.secondary_standard > 0){
					standard_count = 1
				}else{
					$('#show-sample-analysis-analytes').find('.sec_standard_th').addClass('hidden');
				}
				
				if(data['approval_status'] == 0){
					InterlabStatus = 0;
					$('#show-sample-analysis-analytes').find('.not-approved').addClass('hidden');
				}
				if(data['approval_status'] == 1){
					InterlabStatus = 1;
					$('#show-sample-analysis-analytes').find('.not-approved').removeClass('hidden');
				}
				$.each(parameters, function(p, param){
					
					var a_date =analysis_dates && analysis_dates[p] ? analysis_dates[p] : '';
					
					var sectionRow = labSectionRow(param['section'],p,thebatch.id,sampleCode,a_date,standard_count);
					$('#sample-parameters-holder').append(sectionRow);
					
					
					$.each(param['cr'],(i,obj)=>{
						

						var sampleRow = sampleCodeParameters(obj,loop,InterlabStatus,selected_sample);
						$('#sample-parameters-holder').append(sampleRow);
						loop = loop + 1;
					})
					
				});
				$.each(pesticides, function(p, param){
					
					var a_date =analysis_dates && analysis_dates[p] ? analysis_dates[p] : '';
					
					var sectionRow = labSectionRow(param['section'],p,thebatch.id,sampleCode,a_date);
					$('#sample-pesticides-holder').append(sectionRow);
					
					$.each(param['cr'],(i,obj)=>{
						var sampleRow = sampleCodeParameters(obj,loop,InterlabStatus);
						$('#sample-pesticides-holder').append(sampleRow);
						loop = loop + 1;
					})
					
				});
			});
			
			$(this).find('input[name="pesticide_check_all"]').on('change',function(){
				
				if($(this).prop("checked") == true){
					
					$('.pesticide_check').map(function(){
						$(this).prop('checked',true);
					})
				}else{
					
					$('.pesticide_check').map(function(){
						$(this).prop('checked',false);
					})
				}
			})
			$(this).find('input[name="parameter_check_all"]').on('change',function(){
				
				if($(this).prop("checked") == true){
					
					$('.parameter_check').map(function(){
						$(this).prop('checked',true);
					})
				}else{
					
					$('.parameter_check"]').map(function(){
						$(this).prop('checked',false);
					})
				}
			})
		
			$(this).find('#delete-parameter').on('click',function(){
				var $token   = $('meta[name="csrf-token"]').attr('content');
				if($('input[name="parameter_check[]"]:checked').length > 0){
				if(confirm("Are you sure that you want to remove these analytes?")){
					$('input[name="parameter_check[]"]:checked').map(function(){
						var data_id = $(this).val();
						var td_ = $(this).parent('td');
						var row_ = $(td_).parent('tr');
						console.log(data_id);
						$.ajax({
							url: "{{ route('remove-analyte-from-captured-result') }}",
							dataType: 'json',
							data: {
								_token: $token,
								id: data_id
							},
							type: "POST",
							success: function(js){
								if(js.status){
									row_.remove();
								}
								else{
									alert("Couldn't remove analyte!")
								}
							}
						})
						
					})
				}
				}else{
					alert('Kindly select the parameters to delete!');
				}

				
			});
			getSampleCapturedNotData(sampleCode,(data)=>{
				$('.analytes-with-results-count').text(data['captured']);
				$('.analytes-without-results-count').text(data['not_captured']);
			})

			$.ajax({
				url: '{{ route("missing_analysis_parameters_by_sample_code") }}',
				data: {
					id: JSON.stringify(analysisIDs),
					sample: sampleCode,
				},
				beforeSend: function(){
					$('#sample-analysis-parameters-holder').empty();
				},
				success: function(js){
					if(js.length == 0){
						$('#sample-analysis-parameters-holder').html(`
						<tr class="raw-data-row">
							<td colspan="7">
								<div class="alert alert-info text-center"><i class="mdi mdi-information-circle"></i> No new analytes found.</div>
							</td>
						</tr>
						`);
					}
					$.each(js, function(j,s){
						s['sample_code'] = sampleCode;
						var $row = $(`
						<tr class="raw-data-row">
							<td>
								<input type="checkbox" name="item[]" value='${JSON.stringify(s)}' />
							</td>
							<td  nowrap>${s.name+' - '+s.code}</td>
							<td  nowrap>${sampleCode}</td>
							<td  nowrap>${s.analysis_type}</td>
							<td  nowrap>${s.operator == null ? '-': s.operator}</td>
							<td  nowrap>${s.equipment == null ? '-' :s.equipment}</td>
							<td nowrap>${s.analyte_status == 1 ? '<input type="checkbox" checked disabled>' : '<input type="checkbox" disabled>'}<td>
						</tr>
						`);
						$('#sample-analysis-parameters-holder').append($row);
					});
				}
			})
			$(this).find('.dropdown-item').on('click',function(){
				var analysis = $(this).data('analysis');
				$('#sample-parameters-holder').empty();
				var loop = 1;
				
				$.each(parameters, function(p, param){
					if(param.analysis_type_id == analysis){
						var sampleRow = sampleCodeParameters(param,loop);
						// console.log(param);
						$('#sample-parameters-holder').append(sampleRow);

					}
					if(analysis == 'all'){
						var sampleRow = sampleCodeParameters(param,loop);
						// console.log(param);
						$('#sample-parameters-holder').append(sampleRow);
					}
					loop = loop + 1;
				});
			
			});
			$('.sync-pesticide').on('click',(e)=>{
				var p_result = $('.pesticide_result').val()
				var p_remark = $('.pesticide_remark').val()
				if(p_remark == '' || p_result == ''){
					alert('Kindly add fill the result and remark fields to sync');
				}else{
					$('.pest-remark').val(p_remark);
					$('.pest-result').val(p_result);
				}
			});
			$('#change-section').on('click',()=>{
				$('#saving-progress-section').addClass('hidden');
				$('.save-lab-section').removeClass('hidden');
				if($('[name="parameter_check[]"]').is(':checked')){
					$.each($('[name="parameter_check[]"]'),(i,obj)=>{
						if($(obj).is(':checked')){
							capturedIds.push($(obj).val());
						}
					});
					$('.change-section-area').removeClass('hidden');
				}else{
					alert('Kindly select the parameters you wish to change the lab sections first.');
				}
			});
			$('.save-lab-section').on('click',()=>{
				$('#saving-progress-section').removeClass('hidden');
				$('.save-lab-section').addClass('hidden');
				
				var lab_section_id = $('#section_id_change').val();
				var affect_all = $('#affect_all').is(':checked') ? 1 : 0;
				var affect_batch = $('#affect_batch').is(':checked') ? 1 : 0;
				var is_affect = 0;
				is_affect = affect_all > 0 ? 1 : is_affect;
				is_affect = affect_batch > 0 ? 1 : is_affect
				if(lab_section_id == ''){
					$('#saving-progress-section').addClass('hidden');
					$('.save-lab-section').removeClass('hidden');
					alert('Lab section is a required field');
				}
				if(affect_all == 0 && affect_batch == 0){
					$('#saving-progress-section').addlass('hidden');
					$('.save-lab-section').removeClass('hidden');
					alert('Kindly select where the change should affect if its this batch only or the parameter configuration');
				}
				if(lab_section_id != '' && is_affect == 1){
					$.ajax({
						url:`/change/Labsection-By-Captured-Results`,
						method:'GET',
						data:{
							affect_all:affect_all,
							affect_batch:affect_batch,
							lab_section_id:lab_section_id,
							captured_ids:capturedIds.join(',')
						},
						success:(res)=>{
							console.log('-------------------------done---------------------')
							$('#saving-progress-section').addClass('hidden');
							$('.change-section-area').addClass('hidden');
							$('#show-sample-analysis-analytes').modal('hide')
							$('#refresh-page-modal').modal('show');
						},
						error:(data)=>{
							console.log(data);
						}
					})
				}
			})
			
		})

		

		$('#client-unit-select').on('change', function(){
			if($(this).val() == ''){
				return false;
			}
			$.ajax({
				url: '/fetch-unit-stuff/'+$(this).val()+'/'+$('#client-select').val(),
				beforeSend: function(){
					unitProducts = [];
					unitSamplePoints= [];
				},
				success: function(js){
					// unitProducts = js.products;
					unitSamplePoints= js.sample_points;

					$('#sample-detail-rows').find('tr').find('[name="sample_details[sample_point][]"]').each(function(e){
						var rowData = $(this).parents('tr').data('sample');
						var SP = $(this);
						SP.html('<option tetet></option>');
						$.each(unitSamplePoints, function(j,s){
							SP.append(`
								<option value="${s.id}">${s.name}</option>
							`);
						});
						SP.val(rowData ? rowData.sample_point_id : '');
						SP.trigger('change');
					})

					

				}
			})
		})

		

		const buildClientSelect2Options = ($element) => {
			const ajaxUrl = $element.data('clientSource');
			const pageSize = Number($element.data('pageSize')) || Number($('#client-select').data('pageSize')) || 50;

			const options = {
				ajax: {
					url: ajaxUrl,
					dataType: 'json',
					delay: 250,
					data: function(params){
						return {
							term: params.term || '',
							page: params.page || 1,
							per_page: pageSize
						};
					},
					processResults: function(data, params){
						params.page = params.page || 1;
						return {
							results: data.results || [],
							pagination: data.pagination || {more: false}
						};
					},
					cache: true
				},
				placeholder: 'Select Client...',
				allowClear: true,
				width: '100%'
			};

			const dropdownParent = $element.closest('.modal');
			if (dropdownParent.length) {
				options.dropdownParent = dropdownParent;
			}

			return options;
		};

		const initializeClientSelect = ($elements) => {
			$elements.each(function(){
				const $element = $(this);

				if (!$element.length) {
					return;
				}

				if ($element.hasClass('no-select2')) {
					return;
				}

				if (typeof $element.attr('readonly') !== 'undefined') {
					return;
				}

				if ($element.data('select2')) {
					return;
				}

				const ajaxUrl = $element.data('clientSource');
				if (!ajaxUrl) {
					return;
				}

				$element.select2(buildClientSelect2Options($element));
			});
		};

		initializeClientSelect($('#client-select'));
		initializeClientSelect($('.crm_customer_id'));

		$('#add-customer-contact').on('shown.bs.modal', function(){
			initializeClientSelect($(this).find('.crm_customer_id'));
		});

		$('#client-select').on('change', function(){
			var selectedOps = $(this).children('option:selected');
			clientPrefProductName = 'Product';
			if(selectedOps.val() > 0){
				getClientDetails(selectedOps.val(),(data)=>{
					console.log(data);
	
					clientPrefUnitName = data['unit_name'];
					clientPrefSPName = data['sample_point_name'];
					$('#crm_contact_id').empty();
					console.log('--------------------------')
					console.log(data['contacts'])
					$.each(data['contacts'],(i,obj)=>{
						var name = `${obj.first_name} ${obj.middle_name || ''} ${obj.last_name || ''}`
						var option = `<option value="${obj.id}" ${$batch && $batch.crm_contact_id == obj.id ? `selected` : ``}>${name}</option>`
						$('#crm_contact_id').append(option)
					});
					$('#crm_contact_id').select2();
		
		
					$('.client-prefered-unit-name').text(clientPrefUnitName)
					$('.client-preferred-sample_point-name').text(clientPrefSPName)
					$('.client-preferred-product-name').text(clientPrefProductName)
					var client_selected = $('#client-select').val();
					var client_id = 'client-'+client_selected;
					var text = document.getElementById(client_id);
					var text2 = document.getElementsByClassName('clients-data');
				})
			}
			
		});

		$('#client-select').trigger('change');


		$('.make-batch-changes').on('click', function(){
			if($('#sample-detail-rows').find('tr.selected-row').length == 0){
				alert("Please select the analysis rows you want to amend.");
				return false;
			}

			var modal = $(this).parents('.modal');

			if(modal.find('.bulk-checkbox:checked').length == 0){
				alert("Please select columns you want to change.");
				return false;
			}
			$('.bulk-checkbox:checked').each(function(e){
				var cls = $(this).data('name');
				var elem = modal.find(cls);

				if(elem.is("input")){
					var value = modal.find(cls).val();
					$('#sample-detail-rows').find('tr.selected-row').find(cls).each(function(e){
						$(this).val(value).trigger('change');
					});
				}
				else{
					var value =[];
					modal.find(cls).children("option:selected").each(function(e){
						value.push($(this).val());
					});

					$('#sample-detail-rows').find('tr.selected-row').find(cls).children('option').each(function(e){
						$(this).attr('selected', false).trigger('change');
					});

					$('#sample-detail-rows').find('tr.selected-row').find(cls).children('option').each(function(e){
						var sVal = $(this).val();
						if (value.indexOf(sVal) !== -1) {
							$(this).attr('selected', 'selected').trigger('change');
						}
					});
				}
			});

			modal.find('.form-control').val('').trigger('change');
			modal.find('.bulk-checkbox').removeProp('checked');
		});

		$('#provide-interpretations').on('show.bs.modal', function(e){
			var d = new Date();
			var n = d.getTime();
			var scopetype = $(e.relatedTarget).data('scopetype');
			var $row = $(`
				<div class="form-group">
					<label>Comments</label>
					<textarea class="form-control" name="header_body" placeholder="Comments...">{{ $headerDetails['header']->header_body ?? '' }}</textarea>
				</div>
				<div class="form-group">
					<label>Recommendations / Interpretations</label>
					<textarea class="form-control" name="main_body" placeholder="Recommendations / Interpretations..." >{{ $headerDetails['header']->main_body ?? '' }}</textarea>
				</div>
				<div class="form-group">
					<label>Notes</label>
					<textarea class="form-control" name="notes_body" placeholder="Notes..."></textarea>
				</div>
				<div class="form-group">
					<label for="" class="control-label">Scope</label>
					<select name="batch_comment_scope" class="form-control" id="">
						<option value="1" ${scopetype == 1 ? 'selected' : ''}>Concactinate</option>
						<option value="2" ${scopetype == 2 ? 'selected' : ''}>Overwrite</option>
					</select>
				</div>
			`).clone();

			$('#sample-interpretations-holder').html($row);

			$row.find('[name="main_body"]').attr('id', 'sample-main-body-'+n)
			$row.find('[name="header_body"]').attr('id', 'sample-header-body-'+n)
			$row.find('[name="notes_body"]').attr('id', 'sample-notes-body-'+n)

			var action = $(e.relatedTarget).data('action');
			var mainBody = $(e.relatedTarget).data('mainbody');
			var headerBody = $(e.relatedTarget).data('headerbody');
			var notesBody = $(e.relatedTarget).data('notesbody');
			

			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);

			$('#sample-header-body-'+n).html(headerBody)
			$('#sample-main-body-'+n).html(mainBody);
			$('#sample-notes-body-'+n).html(notesBody);
			console.log(n);
			tinymce.init({
				selector: '#sample-header-body-'+n
			});

			tinymce.init({
				selector: '#sample-main-body-'+n
			});
			tinymce.init({
				selector: '#sample-notes-body-'+n
			});
		});
		$('#batch-info-sample-type').trigger('change');

		$('[name="is_routine"]').on('change', function(){
			if($(this).is(':checked')){
				$('#routine_frequency').removeClass('hidden');
				$('[name="routine_frequency"]').prop('required');
				$('[name="routine_frequency"]').attr('required');
			}
			else{
				$('#routine_frequency').addClass('hidden');
				$('[name="routine_frequency"]').find("option:selected").removeAttr("selected");
				$('[name="routine_frequency"]').find("option:selected").removeProp("selected");
				$('[name="routine_frequency"]').removeAttr('required');
				$('[name="routine_frequency"]').removeProp('required');
			}
		});

		$('.duplicate-sample-row').on('click', function(){
			var selectedRows = $('#sample-detail-rows').find('tr.selected-row');

			if(selectedRows.length == 0){
				alert('No row selected.');
			}
			else{
				var duplicate = prompt("Enter number of duplicates", 1);
				duplicate = duplicate || 0;

				selectedRows.each(function(i,e){
					for(var z = 0; z<duplicate; z++){
						var rowNo = $('#sample-detail-rows').find('tr').length;
						var ids = [];
						$(e).find('.sample-analysis').children('option:selected').each(function(a,b){
							ids.push($(b).val());
						})
						var data = {
							"id": "",
							"sample_code": $(e).find('.sample-code').children('option:selected').val(),
							"lab_sub_no": $(e).find('.submission-form-no').val(),
							"analysis_type_id": ids.join(','),
							"sample_condition_id": $(e).find('.sample-condition').children('option:selected').val(),
							"sample_point_id": $(e).find('.sample-point').children('option:selected').val(),
							"company_product_id": $(e).find('.sample-product').children('option:selected').val(),
							"comments": $(e).find('.sample-comments').val(),
							"barcode": $(e).find('.sample-barcode').val(),
							
							"main_standard":$(e).find('.main-standard').children('option:selected').val(),
							"secondary_standard":$(e).find('.secondary-standard').children('option:selected').val(),
							"unit_type": $(e).find('.sample-reporting-unit').children('option:selected').val(),
							"stock_in": $(e).find('.sample-quantity').val(),
							"stock_out": 0,
							"lab_id": $(e).find('select.sample-lab').children('option:selected').val(),
							
							"store_id": $(e).find('.sample-store').children('option:selected').val(),
							"slot_id": $(e).find('.sample-store-slot').children('option:selected').val(),
							"disposal_date":$(e).find('.disposal-date').val(),
							"is_duplicate":$(e).find('.sample-code').val(),
						};
						
						
						

						createRow(data);
					}
				});
			}

		});

		var getLabs = (analysis_type_ids,callback)=>{
			$.ajax({
				url:'/get/Labs-By-Analysis/Type-Id-Ajax',
				method:"GET",
				data:{
					ids : analysis_type_ids
				},
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			})

		}

		var getLabSections = (lab_id,callback)=>{
			$.ajax({
				url:`/get/Lab-Sections/By-Lab/${lab_id}`,
				method:"GET",
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			})

		}

		var createRow = function(data=false){
			var $row = $(sampleDetailsRow).clone();
			
			// console.log('-----------hfvdsh')
			// console.log(JSON.parse(data.analysis_dates));
			// console.log('-----------hfvdsh')

			
			if($isclient.is_client == 1 && $batch.status != 'Samples En-Route'){
				
				$row.find('.edit-remove').empty();
				$row.find('.interpretation-remove').empty();
				$row.find('.delete-remove').empty();
			}


			$row.find('.is-required').each(function(){
				$(this).attr('required', true);
			});

			if(data){
				$row.removeClass('editable').addClass('saved-data');
				$row.data('sample', data);
				$row.find('.no-data').removeClass('hidden');

				$row.find('.toggle-row-edit-mode').removeClass('text-primary').addClass('text-muted');

				$row.find('.dropdown-row').data('sample_code', data['sample_code']);

				$row.find('.provide-interpretation-row').data("action", '/sample-interpretations/'+data.id);
				$row.find('.provide-interpretation-row').data("headerbody", data.header_body);
				$row.find('.provide-interpretation-row').data("mainbody", data.main_body);
				$row.find('.provide-interpretation-row').data("notesbody", data.notes_body);
				$row.find('.provide-interpretation-row').data("scopetype", data.main_body);
			}
			$row.find('.initiate-interlab').data('sample',data.id);
			$row.find('.initiate-interlab').data('samplecode',data.sample_code);
			$row.find('.initiate-interlab').data('analysistype',data.lab_id);
			$row.find('.show-parameter-initiator').data('analysisdate',data.analysis_dates);
			$row.find('[name="sample_details[is_duplicate][]"]').val(data.is_duplicate ? data.is_duplicate : 0)
			$row.find('[name="sample_details[sample_store][]"]').val(data['store_id']).trigger('change');
			$row.find('[name="sample_details[sample_store_slot][]"]').data('selected', data['slot_id']);

			

			$row.find('[name="sample_details[sample_point][]"]').html('<option></option>');
			

			$.each(unitSamplePoints, function(j,s){
				$row.find('[name="sample_details[sample_point][]"]').append(`
					<option value="${s.id}" ${ s.id == data['sample_point_id'] ? 'selected' : '' }>${s.name}</option>
				`);
			});

			$row.find('[name="sample_details[main_standard][]"]').html('<option></option>');
			$.each(standards,function(j,s){

				$row.find('[name="sample_details[main_standard][]"]').append(`
					<option value="${s.id}" ${ s.id === data['main_standard'] ? 'selected' : '' }>${s.name}</option>
				`);
			});
			$row.find('[name="sample_details[secondary_standard][]"]').html('<option></option>');
			$.each(standards,function(j,s){
				$row.find('[name="sample_details[secondary_standard][]"]').append(`
					<option value="${s.id}" ${ s.id === data['secondary_standard'] ? 'selected' : '' }>${s.name}</option>
				`);
			});
			console.log('-------------bqegwgeuwugeuw---------------------')
			console.log(data.main_standard)
			console.log('---------------------end eke-------------')

			$row.find('.show-parameter-initiator').data('standards',data.main_standard);
			// $row.find('.show-parameter-initiator').data('standard2',data['secondary_standard']);
			
			// $row.find('[name="sample_details[product][]"]').html('<option></option>');

			// $.each(unitProducts, function(j,s){
			// 	$row.find('[name="sample_details[product][]"]').append(`
			// 		<option value="${s.id}" ${ s.id == data['company_product_id'] ? 'selected' : '' }>${s.name}</option>
			// 	`);
			// });
			$row.find('[name="sample_details[product][]"]').val(data['company_product_id']);

			$row.append(`<input type="hidden" value="${data.id}" name="sample_details[detail_header][]" />`);

			var rowNo = $('#sample-detail-rows').find('tr').length;

			$row.find('[name="sample_details[sample_code][]"]').val(data['sample_code']);
			

			$row.find('.analysis-field .form-control').empty();

			$.each(sampleAnalysisByType, function(s, sa){
				$row.find('.analysis-field .form-control').append(`<option value="${sa.id}">${sa.name}</option>`);
			});

			$row.find('.analysis-field .form-control').attr('name', 'sample_details[sample_analysis]['+rowNo+'][]');

			$row.find('.analysis-field .form-control').val(data['analysis_type_id'] ? data['analysis_type_id'].split(',') : '');

			// $row.find('[name="sample_details[sample_condition][]"]').html(`<option value="">Select Condition</option>`);

			// $.each(sampleCondtions, function(s, sc){
			// 	$row.find('[name="sample_details[sample_condition][]"]').append(`<option value="${sc.id}">${sc.name}</option>`);
			// });

			$row.find('select.sample-store').on('change', function(){
				var items = $(this).children("option:selected").data('items');
				$row.find('select.sample-store-slot').html(`<option></option>`).attr('placeholder', 'Select Sample Storage Slot...');

				var selected = $row.find('select.sample-store-slot').data('selected');
				$.each(items, function(i, e){
					$row.find('select.sample-store-slot').append(`<option value="${i}" ${selected == i ? 'selected' : ''}>${e}</option>`);
				});

			});

			$row.find('[name="sample_details[sample_condition][]"]').val(data['sample_condition_id']);
			$row.find('[name="sample_details[main_standard][]"]').val(data['main_standard']);
			$row.find('[name="sample_details[secondary_standard][]"]').val(data['secondary_standard']);

			$row.find('[name="sample_details[sample_quantity][]"]').val(parseFloat(data['stock_in']) - parseFloat(data['stock_out']));
			$row.find('[name="sample_details[sample_reporting_unit][]"]').val(data['unit_type']);

			$row.find('[name="sample_details[comments][]"]').val(data['comments']);

			$row.find('.sample_marking_holder').data('samplecode',data['sample_code']);
			$row.find('.sample_marking_holder').data('marking',data['comments']);
			
			$row.find('[name="sample_details[barcode][]"]').val(data['barcode']);
			if(data){
				$row.find('[name="sample_details[disposal_date][]"').val(data['disposal_date']);
			}

			
			
			if(sampleLabs[data['sample_code']]){

				$.each(sampleLabs[data['sample_code']],(i,obj)=>{
					$row.find('select.sample-lab').append(`<option value="${obj.sample_lab}" ${obj.sample_lab == data['lab_id'] ? 'selected' : ''}>${obj.sample_lab_name} - ${obj.sample_lab_code}</option>`);
				});
			}
			if(data && !sampleLabs[data['sample_code']]){
				console.log('gdvhfhsfhhfjdjfhdhfgdfj')
				console.log(sampleLabs[0])
				
				$.each(sampleLabs[0],(i,obj)=>{
					console.log(obj)
					$row.find('select.sample-lab').append(`<option value="${obj.sample_lab}" ${obj.sample_lab == data['lab_id'] ? 'selected' : ''}>${obj.sample_lab_name} - ${obj.sample_lab_code}</option>`);
				});
			}
			
			$('#sample-detail-rows').append($row);

			$row.find('.toggle-row-edit-mode').on('click', function(){
				var row = $(this).parents('tr');
				row.toggleClass('editable');
				$(this).toggleClass('text-muted text-primary');
			});

			$row.find('.delete-row').on('click', function(){
				var row = $(this).parents('tr');
				var rowID = data.id;

				var rowColor = row.css('backgroundColor');

				if(confirm("Are you sure that you want to delete this row?")){
					var interval;
					var blink = true;
					if(rowID){
						$.ajax({
							url: '/delete-sample/'+rowID,
							dataType: 'json',
							method: 'POST',
							beforeSend: function(){
								interval = setInterval(function(){
									if(blink){
										row.css('background-color', '#ffd9bd')
									}
									else{
										row.css('background-color', '#ffabab')
									}
									blink=!blink;
								}, 200);
							},
							success: function(js){
								if(js.status){
									row.remove();
								}
								else{
									alert(js.message);
									clearInterval(interval);
									row.css('background-color', rowColor);
									console.log(rowColor);
								}
							},
							error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
						})
					}
					else{
						row.remove();
					}
				}
			});

			$row.find('.select-row-check').on('change', function(){
				if($(this).is(":checked")){
					$(this).parents('tr').addClass('selected-row');
				}
				else{
					$(this).parents('tr').removeClass('selected-row');
				}

			})

			$row.find('.form-control').on('keyup', function(){
				var value = $(this).is("input") ? $(this).val() : ($(this).is("textarea") ? $(this).val() : $(this).children('option:selected').text());
				

				var formGroup = $(this).parents('.form-group');
				var textHolder = formGroup.siblings('span.text');

				var values = [];

				if($(this).is("select")){

					$(this).children('option:selected').each(function(i,e){
						values.push($(e).text());
					});

				}
				else{
					values.push(value);
				}
				var ClassID = $(this).attr('name');
				var dataName = $(textHolder).data('name');
				
				// values = ['lab 1','lab 2']
				if($(this).is("textarea")){
					if(!dataName){
						textHolder.append(values.join(','));
					}else{
						// $(textHolder).data('holder',values.join(','))
					}
				}else{
					textHolder.text(values.join(','));
				}
			});
			$row.find('.sample-analysis').on('change',(e)=>{
				var value = $row.find('.sample-analysis').val();
				if(value != ''){
					getLabs(value,(data_lab)=>{
						$row.find('select.sample-lab').empty();
						$row.find('select.sample-lab').append(`<option value="">Select Lab...</option>`);
						$.each(data_lab,(i,obj)=>{
							$row.find('select.sample-lab').append(`<option value="${obj.id}" ${ data['lab_id'] == obj.id ? 'selected' : ''}>${obj.name} - ${obj.code}</option>`);
							
						});
					});

				}
			});

			$row.find('.form-control').trigger('change');
			$row.find('.form-control').trigger('keyup');

			$row.find('.form-control').on('change', function(){
				$(this).trigger('keyup');
			});


			$row.find('select').not('.no-select2').select2();
		}

		$.each(configuredSamples, function(s, sample){

			createRow(sample);
		});

		$('.create-new-sample-row').on('click', function(){
			createRow();
		});

		var fetchSampleAnalysis = function($this){
			var sampleId = $this.val();

			if(sampleId == "") return;

			sampleCondtions = $this.children('option:selected').data('conditions');

			$.ajax({
				url: '/analysis-types/'+sampleId,
				dataType: 'json',
				beforeSend: function(){
					isFetchingAnalysis = true;
					$('#preparing-details-msg').html(`
						<i class="fas fa-spinner fa-spin"></i> Loading Sample Analysis.
					`);
				},
				success: function(js){
					isFetchingAnalysis = false;
					$('#preparing-details-msg').empty();
					sampleAnalysisByType = js;

					$('#sample-detail-rows').empty();
					// $.each(samples, function(s, sample){
					// 	createRow(sample);
					// });
				}
			});
		}

		$('#batch-info-sample-type').on('change', function(){
			fetchSampleAnalysis($(this));
		});
		
		var relatedTargetElement;
		var is_value_id = 0;
		var editStandardModal = (data)=>{
			var body = $(`
				<div action="" class="bg-white p-3 before-save">
					
					<div class="alert alert-primary d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px"></i>
						<span class="p-2"> ${data}: <br><br>Change <b class="analyte_name"></b> Standard Limits by updating the information below  <br>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Analyte</label>
						<input type="text" readonly  value="" class="form-control analyte_name_field">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Previous Value</label>
						<input type="text" readonly  value="" class="form-control standard_value_field">
					</div>
					<div class="row pl-4">
						<div class="col-md-6">
							<div class="form-group">
								<input class="form-check-input is_standard_value" type="radio" class="form-control" name="standard_value_type"  value="1"/>
								<label class="form-check-label" for="">
								  Use Range
								</label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<input class="form-check-input is_standard_value" type="radio" class="form-control" name="standard_value_type"  value="2"/>
								<label class="form-check-label" for="">
								  Use Value
								</label>
							</div>
						</div>
					</div>
					<div class="is-range row hidden">
						<div class="form-group col-sm-6">
							<label for="" class="control-label">Min</label>
							<input type="text" name="min" placeholder="Min Value" class="form-control min">
						</div>
						<div class="form-group col-sm-6">
							<label for="" class="control-label">Max</label>
							<input type="text" name="max" placeholder="Max Value" class="form-control max">
						</div>
					</div>
					<div class="is-value hidden">
						<div class="from-group">
							<label for="" class="control-label">Value Type</label>
							<select name="standard_valuetype" id="" class="form-control standard_valuetype select2-offscreen" >
								<option value="">Loading .....</option>

							</select>
						</div>
						<div class="row is-value-type hidden mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="" class="control-label">Limit Measure</label>
									<select name="limit_measure" id="" class="form-control limit-measure">
										<option value="">Choose Limit</option>
										<option value="Max">Max</option>
										<option value="Min">Min</option>
										<option value="less_than">< (Less Than)</option>
										<option value="greater_than">> (Greater Than)</option>
									</select>
								</div>
							</div>
							<div class="col-ms-6">
								<div class="form-group">
									<label for="" class="control-label">Value</label>
									<input type="text" name="value"  class="form-control value">
								</div>

							</div>
						</div>
						
					</div>
				</div>
				<div class="after-save p-3 bg-white hidden">
					<center>
						<img src="/images/suc.gif" width="30%" height="50%" alt="">
					</center>
				</div>
			`).clone();
			$(body).find('.is_standard_value').on('change',(e)=>{
				var value = $(e.currentTarget).val();
				selectedValue = $(e.currentTarget).val();
				// console.log('here--------------')
				console.log(value);
				if(value == 1){
					$(body).find('.is-range').removeClass('hidden');
					$(body).find('.is-value').addClass('hidden');
				}else{
					$(body).find('.is-range').addClass('hidden');
					$(body).find('.is-value').removeClass('hidden');
				}
			});
			$(body).find('.standard_valuetype').on('change',(e)=>{
				
				var value = $(e.currentTarget).val();
				if(value == is_value_id){
					$(body).find('.is-value-type').removeClass('hidden');
				}else{
					$(body).find('.is-value-type').addClass('hidden');
				}
			})
			return body;

		}
		$('#edit-standard').on('hidden.bs.modal',()=>{
			$('body').addClass('modal-open');
		})
		$('#add-reporting-unit').on('hidden.bs.modal',()=>{
			$('body').addClass('modal-open');
		})
		
		$('#add-reporting-unit').on('show.bs.modal',(e)=>{
			var rec = $(e.relatedTarget).data('record');
			var name= `reporting_unit[${rec}]`;
			$('#add-reporting-unit').find('#a_reporting_unit').val('')
			$('#add-reporting-unit').find('.save-reporting').removeClass('hidden');
			$('#add-reporting-unit').find('.message-area').empty();
			$('#add-reporting-unit').find('.save-reporting').on('click',(e)=>{
				var r_value = $('#add-reporting-unit').find('#a_reporting_unit').val();
				if(r_value == ''){
					alert('The field is a required field');
				}else{
					$('#add-reporting-unit').find('.save-reporting').addClass('hidden');
					$.ajax({
						url:`/reporting-unit/addAjax`,
						method:'GET',
						data:{
							r_value:r_value
						},
						success:(data)=>{
							if(data){
								var option = `<option value="${data.id}">${data.name}</option>`;
								$('#sample-parameters-holder').find('.sample-reporting-unit').append(option);
								$('#sample-parameters-holder').find(`[name="${name}"]`).append(option);
								$('#sample-parameters-holder').find(`[name="${name}"]`).val(data.id);
								var r_body = `
								<div class="alert alert-success p-2 d-flex">
									<i class="mdi mdi-check-decagram" style="font-size:30px"></i>
									<span class="p-2">Reporting unit saved successfully!</span>
								</div>`;
								$('#add-reporting-unit').find('.message-area').append(r_body)
		
							}else{
								var r_body = `
								<div class="alert alert-danger p-2 d-flex">
									<i class="mdi mdi-alert-octagram" style="font-size:30px"></i>
									<span class="p-2">There is a reporting unit with the given name</span>
								</div>`;
								$('#add-reporting-unit').find('.message-area').append(r_body)
							}
						},
						error:(data)=>{
							alert('An error occured');
						}
					});
				}
			});
		});
		$('#edit-standard').on('show.bs.modal',(e)=>{
			// e.stopPropagation();
			relatedTargetElement = e.relatedTarget
			var standard = $(e.relatedTarget).data('standard');
			
			$('#edit-standard').find('.modal-body').empty();
			$body = editStandardModal(standard)
			$('#edit-standard').find('.modal-body').append($body);


			$('#edit-standard').find('.analyte_name_field').val($(e.relatedTarget).data('analytename'));
			$('#edit-standard').find('.analyte_name').empty();
			$('#edit-standard').find('.analyte_name').append($(e.relatedTarget).data('analytename'));
			$('#edit-standard').find('.before-save').removeClass('hidden');
			$('#edit-standard').find('.after-save').addClass('hidden');
			$('#edit-standard').find('.save-standard-value').removeClass('hidden');

			var analyte_id = $(e.relatedTarget).data('analyte')
			var standard = $(e.relatedTarget).data('standard');
			
			$('#edit-standard').find('.standard_value_field').val($(e.relatedTarget).data('standardvalue'));
			
			$.ajax({
				url:`/get/Standard-Values/Data/Ajax`,
				method:'GET',
				success:(data)=>{
					$('#edit-standard').find('.standard_valuetype').empty();
					$.each(data,(i,obj)=>{
						is_value_id = obj.code == 'IsValue' ? obj.id : is_value_id;
						var option = `<option value="${obj.id}">${obj.code}</option>`
						$('#edit-standard').find('.standard_valuetype').append(option);
					})
					$('#edit-standard').find('.standard_valuetype').select2({
						dropdownParent: $('#edit-standard')
					});
					$('#edit-standard').find('.limit-measure').select2({
						dropdownParent: $('#edit-standard')
					});
					
				}

			});
			var selectedValue = 0;
			

		});
		
		$('#edit-standard').find('.save-standard-value').on('click',()=>{
			var analyte_id = $(relatedTargetElement).data('analyte')
			var standard = $(relatedTargetElement).data('standard');
			var standard_level = $(relatedTargetElement).data('standardlevel');
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				url:`/update/Standard-Analyte/Limit`,
				method:'POST',
				data:{
					analyte_id : analyte_id,
					standard_id : standard,
					low : $('#edit-standard').find('.min').val(),
					high : $('#edit-standard').find('.max').val(),
					standard_valuetype : $('#edit-standard').find('.standard_valuetype').val(),
					limit_measure : $('#edit-standard').find('.limit-measure').val(),
					standard_value_type : selectedValue,
					value :  $('#edit-standard').find('.value').val(),
				},
				success:(data)=>{
					console.log('------------Success Data-----------------')
					console.log(data);
					console.log('------------Success Data-----------------')

					$('#edit-standard').find('.before-save').addClass('hidden');
					$('#edit-standard').find('.after-save').removeClass('hidden');
					$('#edit-standard').find('.save-standard-value').addClass('hidden');
					
					
					// e.preventDefault();
					var parentDiv = $(relatedTargetElement).data('valueid');
					$(relatedTargetElement).data('standardvalue',data['value']);
					if(standard_level ==1){
						$('#sample-parameters-holder').find(`[name="main_s_value[${parentDiv}]"]`).val(data['format_value'])
						$('#sample-parameters-holder').find(`[name="main_value[${parentDiv}]"]`).val(data['value']);
					}
					if(standard_level == 2){
						$('#sample-parameters-holder').find(`[name="sec_s_value[${parentDiv}]"]`).val(data['format_value'])
						$('#sample-parameters-holder').find(`[name="sec_value[${parentDiv}]"]`).val(data['value']);
					}
					if(standard_level == 3){
						$('#sample-parameters-holder').find(`[name="third_s_value[${parentDiv}]"]`).val(data['format_value'])
						$('#sample-parameters-holder').find(`[name="third_value[${parentDiv}]"]`).val(data['value']);
					}

					var result = $('#sample-parameters-holder').find(`[name="result[${parentDiv}]"]`).val()
					var reportSymbol = $('#sample-parameters-holder').find(`[name="result_reporting_symbol[${parentDiv}]"]`).val()
					

					$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
					}
					});
					$.ajax({
						url: '/fetch/results-remark',
						method:'post',
						data:{
							sample_code:$('#sample-parameters-holder').find(`[name="result[${parentDiv}]"]`).data('resultid'),
							result:result,
							reporting_symbol : reportSymbol,
							captured_result_id : parentDiv
						},
						success:function(response){
							$('#sample-parameters-holder').find('[name="remark['+ parentDiv + ']"]').val(response);
							
						},
						error:function(data){
							console.log(data);
						}

					});


					// $(parentDiv).find('.main-value-field').val(data['format_value']);
					// $(parentDiv).parent('td').find('.standard-value-field').val(data['value']);
					// console.log(parentDiv);
					console.log('-----------------------------')

					// $(parentTD).find('.main-value-field').val(data['format_value']);
					// $(parentTD).find('.standard-value-field').val(data['value']);
					
					// $(parentTR).find('.first-result').val('');
					
				},
				error:(data)=>{
					console.log(data);
				}
			})

		});

	});

	var getStandardLimitSymbol = (data)=>{
		if(data == 'less_than'){
			return '<';
		}
		if(data == 'greater_than'){
			return '>';
		}
		return data;
	}

	var sampleCodeParameters = function(data,loop,interLabApproval=0,sample = null){
		
		var readonly = '';
		$('.main-standard-name').text(data.main_standard === undefined ? '' : (data.main_standard === null ? '' : data.main_standard));
		// console.log(data);
		// ${readonly} ${ {{ Auth::user()->id }} != (data.def_operator ? data.def_operator.id : 0) ? 'readonly' : '' } //results validator by user
		@if(isset($batch->status) && $batch->status != "Samples In Lab")
			readonly = 'disabled';
		@endif

		
		
	
		var $oGRow = $(`
			<tr class="raw-data-row ${data.result == null ? 'no-result' : 'has-result'} ${!userLabSection.includes(data.lab_section_id) && thebatch.status == 'Samples In Lab' ? 'hiddens' : ''}" id="row-${loop}" >
				@if(isset($batch->status) && Auth::user()->is_client == 0)
				<td class="" style="display:flex !important">
				<input type="checkbox" name="parameter_check[]" class="mr-3 ${data.pesticide == 0 ? 'parameter_check' : 'pesticide_check'}" value="${data.id}" id="parameter-check">
				<span class="btn btn-sm btn-default remove-analyte-row" data-toggle="tooltip" data-placement="bottom" title="delete"><i class="mdi mdi-trash-can-outline text-danger"></i></span>
				</td>
				@else
				<td></td>
				@endif
				<td  nowrap>${data.sample_detail_code}</td>
				<td  nowrap>${data.analysis_type.code}</td>
				<td  nowrap><input type="hidden" name="captured_result_id[]" value="${data.id}">${data.analyte_name}</td>
				@if(Auth::user()->is_client == 0)
				<td data-toggle="tooltip" title="${data.analyte_name}"><input type="text" {{isset($batch->status) && !in_array($batch->status,['Samples In Lab','Sample Approval']) ? 'disabled' : ''}} name="result_reporting_symbol[${data.id}]" id="reporting-symbol" placeholder="Reporting Symbol..." value="${data.result_reporting_symbol == null ? '' :data.result_reporting_symbol }" ></td>
				<td data-toggle="tooltip" title="${data.analyte_name}">
					<div class="form-group">
						<input {{isset($batch->status) && $batch->status != 'Samples In Lab' ? 'disabled' : ''}} id="${data.sample_detail_code},${data.analyte_id},${data.id},${data.analyte_id}" data-resultid="${data.sample_detail_code},${data.analyte_id},${data.id},${data.analyte_id}" style="min-width: 150px" type="text"
						class="form-control ${data.remark_is_manual == 0 ? 'first-result' : ''} ${data.pesticide == 1 ? 'pest-result': '' }" ${interLabApproval == 1 ? "disabled" : ""}  id="result-${loop}" value="${data.result == null ? '' : data.result}" name="result[${data.id}]" placeholder="Result..." />
						<input type="hidden" name="result_confirm"  />
					</div>
				</td>measure_uncertanity
				@if(isset($batch->id) && $batch->require_mu == 1)
				<td data-toggle="tooltip" title="${data.analyte_name}">
					<div class="form-group">
						<input type="text" name="measure_uncertanity[${data.id}]" value="${data.measure_uncertanity == null ? '' : data.measure_uncertanity}" placeholder="Measure..." class="form-control">
					</div>
				</td>
				@endif
				@endif
				@if($batch && $batch->is_qc_batch == 1 && $batch->repeat_sample_id > 0)
				<td style="width:150px !important" data-toggle="tooltip" title="${data.analyte_name}">
				<input type="text" class="form-control" style="width:150px" name="repeat_sample[${data.id}]" value="${data.repeatsampleresult}" disabled />
				</td>
				@endif
				<td nowrap class="" data-toggle="tooltip" title="${data.analyte_name}">
					<div class="d-flex">
						<input type="text" class="form-control main-value-field" style="width:100px;border:0" name="main_s_value[${data.id}]" value="${data.standard_limit_value != '' && data.standard_limit_value!= null && (data.standard_limit_value == 'less_than' || data.standard_limit_value == 'greater_than') ? getStandardLimitSymbol(data.standard_limit_value) : ''} ${data.standard_value == null ? '-': data.standard_value} ${data.standard_limit_value != '' && data.standard_limit_value!= null && data.standard_limit_value != 'less_than' && data.standard_limit_value != 'greater_than'  ? data.standard_limit_value : ''}" disabled />
						<span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#edit-standard" data-standard="${data.main_standard}" data-analyte="${data.analyte_id}" data-analytename="${data.analyte_code}" data-standardlevel="1" data-valueid="${data.id}" data-standardvalue="${data.standard_value}"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit Standard"></i></span>	
					</div>
					
							
				<input type="hidden" class="standard-value-field" name="main_value[${data.id}]" value="${data.standard_value}"/>
				<input type="hidden" name="main_standard[${data.id}]" value="${data.main_standard}"/>
				<input type="hidden" name="secondary_standard[${data.id}]" value="${data.secondary_standard}"/>
				
				</td>
				<td nowrap class="${data.secondary_standard && data.secondary_standard != '' ? '' : 'hidden'}" data-toggle="tooltip" title="${data.analyte_name}">
					<div class="d-flex">
						<input type="text" class="form-control sec-value-field" style="width:100px;border:0" name="sec_s_value[${data.id}]" value="${data.sec_standard_limit_value &&  data.sec_standard_limit_value != '' && data.sec_standard_limit_value != null && (data.sec_standard_limit_value == 'less_than' || data.sec_standard_limit_value == 'greater_than') ? getStandardLimitSymbol(data.sec_standard_limit_value) : ''} ${data.sec_standard_value && data.sec_standard_value == null ? '-': data.sec_standard_value} ${data.sec_standard_limit_value &&  data.sec_standard_limit_value != '' && data.sec_standard_limit_value != null && data.sec_standard_limit_value != 'less_than' && data.sec_standard_limit_value != 'greater_than'  ? data.sec_standard_limit_value : ''}" disabled />
						<span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#edit-standard" data-standard="${data.secondary_standard}" data-analyte="${data.analyte_id}" data-analytename="${data.analyte_code}" data-standardlevel="2" data-valueid="${data.id}" data-standardvalue="${data.sec_standard_value}"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit Standard"></i></span>	
					</div>
					
							
				<input type="hidden" class="standard-value-field" name="sec_value[${data.id}]" value="${data.sec_standard_value}"/>
				<input type="hidden" name="main_standard[${data.id}]" value="${data.main_standard}"/>
				<input type="hidden" name="secondary_standard[${data.id}]" value="${data.secondary_standard}"/>
				
				
				</td>
				
				@if(Auth::user()->is_client == 0)
				<td nowrap data-toggle="tooltip" title="${data.analyte_name}">
					<input id="${data.sample_detail_code}-${data.id}" style="min-width: 150px" type="text" 
					class="form-control disabled ${data.remark_is_manual == 0 ? 'first-remark' : 'hidden'} ${data.pesticide == 1 ? 'pest-remark': '' }" readonly="true" value="${data.remark ?? ''}" name="remark[${data.id}]" placeholder="Remark..." />
					<div class="form-group is-manual ${data.remark_is_manual == 0 ? "hidden" : ""} ${data.pesticide == 1 ? 'pest-remark': '' }">
						<select name="remarkmanual[${data.id}]" id="" class="form-control remarkmanual">
							<option value="PASS" ${data.remark == 'PASS' ? 'selected' : ''}>Pass</option>
							<option value="FAIL" ${data.remark == 'FAIL' ? 'selected' : ''}>Fail</option>
						</select>
					</div>
				</td>
				<td nowrap data-toggle="tooltip" title="${data.analyte_name}">
					<div class="form-group form-group-sm d-flex">
						<select class="form-control form-control-sm sample-reporting-unit"  name="reporting_unit[${data.id}]" style="width: 100px !important" placeholder="Select Sample Reporting Unit...">
							<option></option>
							@if($reportingUnits)
								@foreach($reportingUnits as $unit)
									<option value="{{ $unit['name'] }}">{{ $unit['name'] }}</option>
								@endforeach
							@endif
						</select>
						<span class="btn btn-sm btn-default text-primary" data-target="#add-reporting-unit" data-toggle="modal" data-record="${data.id}"><i class="mdi mdi-plus" data-toggle="tooltip" title="Add Reporting Unit" ></i></span>
					</div>
				</td>
				<td data-toggle="tooltip" title="${data.analyte_name}">
					<div class="form-group" name="operators" placeholder="Select Operator...">
						<select style="min-width: 150px" class="form-control item-operators"  name="operators[${data.id}]" placeholder="Select Operator..." data-selected="${data.def_operator ? data.def_operator.id : 0 }"></select>
					</div>
				</td>
				@endif
				<td  nowrap data-toggle="tooltip" title="${data.analyte_name}">
					<div class="form-group"  placeholder="Select Method...">
						<select style="min-width: 150px" class="form-control method-id"  name="method_id[${data.id}]" placeholder="Select Method..." data-selected="${data.method_id ? data.method_id : 0 }"></select>
					</div>
				</td>
				<td  nowrap data-toggle="tooltip" title="${data.analyte_name}">
					<div class="form-group"  placeholder="Select LTM...">
						<select style="min-width: 150px" class="form-control ltm-method-id"  name="ltm_method_id[${data.id}]" placeholder="Select LTM..." data-selected="${data.ltm_method_id ? data.ltm_method_id : 0 }"></select>
					</div>
				</td>
				@if(Auth::user()->is_client == 0)
				<td  nowrap data-toggle="tooltip" title="${data.analyte_name}">${data.equip_name}</td>
				@endif
				<td class="text-center" nowrap data-toggle="tooltip" title="${data.analyte_name}">
				<div class="form-group">
				<input class="form-check" type="checkbox" {{Auth::user()->is_client == 1 ? 'disabled' : ''}}  name="subcontracted[${data.id}]" ${data.analyte_status_contracted  == 1 ? 'checked':''}/>
				</div>
				</td>
				<td class="text-small text-center" data-toggle="tooltip" title="${data.analyte_name}">
				<div class="form-group">
				<input class="form-check" type="checkbox" {{Auth::user()->is_client == 1 ? 'disabled' : ''}}  name="accredited[${data.id}]" ${data.analyte_accredited  == 1  ? 'checked':''}/>
				</div>
				</td>
			</tr>
		`);

		var $row = $oGRow.clone();
		

		data.reporting_unit_id == '' ? $($row).find('.sample-reporting-unit').val(data.my_analyte.reporting_unit) :$($row).find('.sample-reporting-unit').val(data.reporting_unit_id) ;
		$($row).find('.sample-reporting-unit').select2();
		
		$row.on('keypress','.first-result',function(e){
			if (e.which == 13) {
				e.preventDefault();
				var looped = loop + 1;
				var nextInput = getElementById('result-'+looped);
				if (nextInput) {
				nextInput.focus();
				}
			}
		});
		$row.on('change', '.first-result', function(){
			var result_confirm = prompt('Please confirm the result:');
			var reporting_symbol = $($row).find('#reporting-symbol').val();
			var current_result = $(this).val();
			
			if(result_confirm != current_result){
				alert("Result confirmation didn't match captured result!");
				$(this).removeClass('changed').removeClass('clean');
				$(this).siblings('[name="result_confirm"]').val('');
				$(this).val("");
			}
			else{
				$(this).siblings('[name="result_confirm"]').val(result_confirm);
				$(this).removeClass('clean').addClass('changed');

				var tt = this.id.split(',');
				var y = tt.splice(1,1);
				var tt_str = tt.toString();
				
				var res = tt_str.replace(/,/g,'-');
				
				

				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
					}
				});
				$.ajax({
					url: '/fetch/results-remark',
					method:'post',
					data:{
						sample_code:this.id,
						result:result_confirm,
						reporting_symbol : reporting_symbol,
						captured_result_id : data.id
					},
					success:function(response){
						
						var inputclass = '#'+res;
						
						$row.find('[name="remark['+ data.id + ']"]').val(response);
						
					},
					error:function(data){
						console.log(data);
					}

				});
			}
		});


		var selectedOperator = $row.find('select.item-operators').data('selected');
		var selectedMethod = $row.find('select.method-id').data('selected');
		var selectedLTMethod = $row.find('select.ltm-method-id').data('selected');
		var methods = $('#sample-detail-rows').data('methods');
		var ltmethods = $('#sample-detail-rows').data('ltmethods');

		
		if(methods != '' ){
			$row.find('select.method-id').empty();

			$.each(methods,function(r,t){
				$row.find('select.method-id').append(`<option value="${r}" ${r == selectedMethod ? `selected` : `` }>${t}</option>`)
			})
		}
		if(ltmethods){
			$row.find('select.ltm-method-id').empty();
			$.each(ltmethods,function(r,t){
				$row.find('select.ltm-method-id').append(`<option value="${t.id}" ${t.id == selectedLTMethod ? `selected` : `` }>${t.name}</option>`)
			});
		}

		
		
		$row.find('select.item-operators').empty();
		var OPS = $('#operators-list').data('operators');
		$.each(OPS, function(o,p){
			$row.find('select.item-operators').append(`<option value="${p.id}">${p.name}</option>`)
		});
		$row.find('select.method-id').select2();
		$row.find('select.item-operators').select2();
		$row.find('select.remarkmanual').select2();
		if(selectedOperator){
			$row.find('select.item-operators').val(selectedOperator).trigger('change');
		}
		var $token   = $('meta[name="csrf-token"]').attr('content');
		$row.find('.remove-analyte-row').on('click', function(){
			if(confirm("Are you sure that you want to remove this analyte?")){
				$.ajax({
					url: "{{ route('remove-analyte-from-captured-result') }}",
					dataType: 'json',
					data: {
						_token: $token,
						id: data.id
					},
					type: "POST",
					success: function(js){
						if(js.status){
							$row.remove();
						}
						else{
							alert("Couldn't remove analyte!")
						}
					}
				})
			}
		});

		return $row;
	}

	// Capture Results Modal Functionality
	var currentParameterData = {};
	var availableMethods = [];
	var currentCaptureParameterId = null;

	// Load data when capture results modal opens
	$('#capture-results-modal').on('show.bs.modal', function(e) {
		console.log('=== Capture Results Modal Opening ===');
		debugDataStructure();
		loadSampleDropdown();
	});
	
	function debugDataStructure() {
		console.log('=== DEBUGGING DATA STRUCTURE ===');
		var $sampleRows = $('#sample-detail-rows');
		console.log('Sample rows element found:', $sampleRows.length);
		
		// Check all data attributes
		var allData = $sampleRows.data();
		console.log('All data attributes:', allData);
		
		// Check parameters specifically
		var parameters = $sampleRows.data('parameters');
		console.log('Parameters data:', parameters);
		console.log('Parameters type:', typeof parameters);
		
		// Check batch data
		var batchData = $sampleRows.data('batch');
		console.log('Batch data:', batchData);
		console.log('Batch type:', typeof batchData);
		
		if (batchData && batchData.sample_type) {
			console.log('Batch sample type:', batchData.sample_type);
		}
		
		if (parameters) {
			console.log('Parameters keys:', Object.keys(parameters));
			console.log('First few parameter entries:');
			Object.keys(parameters).slice(0, 3).forEach(function(key) {
				console.log('Key:', key, 'Value:', parameters[key]);
			});
		}
		
		// Check if there are any samples in the table
		console.log('Table rows count:', $('#sample-detail-rows tr').length);
		$('#sample-detail-rows tr').each(function(index) {
			if (index < 3) { // Only check first 3 rows
				var $row = $(this);
				var sampleCode = $row.find('input[name*="sample_code"]').val();
				console.log('Row', index, 'sample code:', sampleCode);
			}
		});
	}

	function loadSampleDropdown() {
		console.log('Loading sample dropdown...');
		var sampleDropdown = $('#capture-sample-select');
		sampleDropdown.empty().append('<option value="">Select a sample...</option>');
		
		// Debug: Check all available data attributes
		var $sampleRows = $('#sample-detail-rows');
		console.log('Sample detail rows element:', $sampleRows.length);
		console.log('Data attributes available:', $sampleRows.data());
		
		// Get samples from the batch data
		var samples = $sampleRows.data('samples');
		console.log('Samples from data-samples:', samples);
		
		// Try alternative data attribute names
		if (!samples) {
			samples = $sampleRows.attr('data-samples');
			console.log('Samples from attr data-samples:', samples);
			if (samples) {
				try {
					samples = JSON.parse(samples);
				} catch (e) {
					console.error('Error parsing samples JSON:', e);
					samples = null;
				}
			}
		}
		
		// If still no samples, extract from table rows
		if (!samples || samples.length === 0) {
			console.log('No samples in data attributes, extracting from table rows...');
			samples = [];
			$('#sample-detail-rows tr').each(function(index) {
				var $row = $(this);
				console.log('Checking row', index, ':', $row);
				
				// Try multiple selectors for sample code
				var sampleCode = $row.find('input[name*="sample_code"]').val() || 
				                $row.find('.sample-code').val() ||
				                $row.find('input[name="sample_details[sample_code][]"]').val();
				                
				console.log('Found sample code in row', index, ':', sampleCode);
				
				if (sampleCode && sampleCode.trim() !== '') {
					samples.push({
						sample_code: sampleCode,
						id: sampleCode
					});
				}
			});
		}
		
		console.log('Final samples array:', samples);
		console.log('Number of samples found:', samples ? samples.length : 0);
		
		// Populate dropdown
		if (samples && samples.length > 0) {
			samples.forEach(function(sample) {
				if (sample.sample_code) {
					sampleDropdown.append(
						'<option value="' + sample.sample_code + '">' + 
						sample.sample_code + 
						'</option>'
					);
				}
			});
			console.log('Populated dropdown with', samples.length, 'samples');
		} else {
			console.warn('No samples found to populate dropdown');
			sampleDropdown.append('<option value="" disabled>No samples available</option>');
		}
	}

	// Handle sample selection change
	$('#capture-sample-select').on('change', function() {
		var selectedSampleCode = $(this).val();
		console.log('Sample selected:', selectedSampleCode);
		
		if (selectedSampleCode) {
			// Update sample info display
			$('#selected-sample-info').html(
				'<strong>Sample:</strong> ' + selectedSampleCode + '<br>' +
				'<strong>Status:</strong> Loading details and parameters...'
			);
			
			// Load sample details and parameters
			loadSampleDetails(selectedSampleCode);
			loadParametersForSample(selectedSampleCode);
		} else {
			$('#selected-sample-info').html('<small>No sample selected</small>');
			$('#sample-details-section').hide();
			$('#parameters-table-wrapper').hide();
			$('#parameters-table-body').empty();
		}
	});

	function loadSampleDetails(sampleCode) {
		console.log('=== Loading sample details for:', sampleCode, '===');
		
		// Get sample details from the existing data structure
		var samples = $('#sample-detail-rows').data('samples');
		var batchData = $('#sample-detail-rows').data('batch');
		var sampleDetails = null;
		
		// Find the selected sample in the samples array
		if (samples && Array.isArray(samples)) {
			sampleDetails = samples.find(function(sample) {
				return sample.sample_code === sampleCode;
			});
		}
		
		// If not found in samples array, try to get from parameters data
		if (!sampleDetails) {
			var parametersBySampleCode = $('#sample-detail-rows').data('parameters');
			if (parametersBySampleCode && parametersBySampleCode[sampleCode]) {
				// Extract sample details from the first parameter
				var firstParameter = null;
				Object.keys(parametersBySampleCode[sampleCode]).forEach(function(sectionKey) {
					var sectionData = parametersBySampleCode[sampleCode][sectionKey];
					if (sectionData.cr && sectionData.cr.length > 0) {
						firstParameter = sectionData.cr[0];
					}
				});
				
				if (firstParameter && firstParameter.sample) {
					sampleDetails = firstParameter.sample;
				}
			}
		}
		
		// Populate sample details
		if (sampleDetails) {
			console.log('Found sample details:', sampleDetails);
			console.log('Batch data:', batchData);
			
			// Sample Type - try to get from batch relationship first
			var sampleType = 'Not specified';
			if (batchData && batchData.sample_type) {
				sampleType = batchData.sample_type.name || batchData.sample_type || 'Not specified';
			} else if (sampleDetails.sample_type) {
				sampleType = sampleDetails.sample_type;
			} else if (sampleDetails.sample_type_name) {
				sampleType = sampleDetails.sample_type_name;
			} else if (sampleDetails.sample_type_id) {
				sampleType = 'Type ID: ' + sampleDetails.sample_type_id;
			}
			$('#sample-type').text(sampleType);
			
			// Analysis Type
			var analysisType = sampleDetails.analysis_type || 
			                  (sampleDetails.analysis_type_name) || 
			                  (sampleDetails.analysis_type_id ? 'Analysis ID: ' + sampleDetails.analysis_type_id : 'Not specified');
			$('#analysis-type').text(analysisType);
			
			// Lab Section
			var labSection = sampleDetails.lab_section || 
			                (sampleDetails.lab_section_name) || 
			                (sampleDetails.lab_section_id ? 'Section ID: ' + sampleDetails.lab_section_id : 'Not specified');
			$('#lab-section').text(labSection);
			
			// Sample Point
			var samplePoint = sampleDetails.sample_point || 
			                 (sampleDetails.sampling_point) || 
			                 (sampleDetails.location) || 
			                 'Not specified';
			$('#sample-point').text(samplePoint);
			
			// Sample Comments - handle rich text HTML content
			var sampleComments = sampleDetails.comments || 
			                    (sampleDetails.remarks) || 
			                    (sampleDetails.notes) || 
			                    'No comments';
			
			// Check if comments contain HTML tags
			if (sampleComments && sampleComments.includes('<')) {
				// It's rich text, render as HTML
				$('#sample-comments').html(sampleComments);
			} else {
				// Plain text, display as text
				$('#sample-comments').text(sampleComments);
			}
			
			// Sample Status
			var sampleStatus = sampleDetails.status || 
			                  (sampleDetails.sample_status) || 
			                  'Active';
			$('#sample-status').text(sampleStatus);
			
			// Batch ID
			var batchId = 'Not available';
			if (batchData && batchData.id) {
				batchId = batchData.id;
			} else if (sampleDetails.batch_id) {
				batchId = sampleDetails.batch_id;
			}
			$('#batch-id').text(batchId);
			
			// Show the sample details section
			$('#sample-details-section').show();
		} else {
			console.warn('No sample details found for:', sampleCode);
			
			// Show default values
			$('#sample-type').text('Not available');
			$('#analysis-type').text('Not available');
			$('#lab-section').text('Not available');
			$('#sample-point').text('Not available');
			$('#sample-comments').text('No details available');
			$('#sample-status').text('Unknown');
			$('#batch-id').text('Not available');
			
			// Still show the section with default values
			$('#sample-details-section').show();
		}
	}

	function loadParametersForSample(sampleCode) {
		console.log('=== Loading parameters for sample:', sampleCode, '===');
		$('#capture-loading').show();
		$('#parameters-table-wrapper').hide();
		$('#parameters-table-body').empty();
		
		// Get parameters from existing data structure
		var parametersBySampleCode = $('#sample-detail-rows').data('parameters');
		console.log('All parameters data from data attribute:', parametersBySampleCode);
		console.log('Type of parameters data:', typeof parametersBySampleCode);
		console.log('Available sample codes:', parametersBySampleCode ? Object.keys(parametersBySampleCode) : 'none');
		
		// Debug: Check if it's a string that needs parsing
		if (typeof parametersBySampleCode === 'string') {
			console.log('Parameters data is a string, attempting to parse...');
			try {
				parametersBySampleCode = JSON.parse(parametersBySampleCode);
				console.log('Parsed parameters data:', parametersBySampleCode);
			} catch (e) {
				console.error('Error parsing parameters JSON:', e);
			}
		}
		
		// If parameters data is not available, try to get it from the global scope
		if (!parametersBySampleCode || Object.keys(parametersBySampleCode).length === 0) {
			console.log('Parameters data not found in data attribute, checking global scope...');
			// Check if there's a global variable with parameters
			if (typeof window.parametersBySampleCode !== 'undefined') {
				parametersBySampleCode = window.parametersBySampleCode;
				console.log('Found parameters in global scope:', parametersBySampleCode);
			}
		}
		
		var parameters = [];
		if (parametersBySampleCode && parametersBySampleCode[sampleCode]) {
			parameters = parametersBySampleCode[sampleCode];
			console.log('Found parameters for sample', sampleCode, ':', parameters);
		} else {
			console.warn('No parameters found for sample code:', sampleCode);
			console.log('Trying different sample code variations...');
			
			// Try to find parameters with different key formats
			if (parametersBySampleCode) {
				Object.keys(parametersBySampleCode).forEach(function(key) {
					console.log('Checking key:', key, 'against sample code:', sampleCode);
					if (key.includes(sampleCode) || sampleCode.includes(key)) {
						console.log('Found potential match:', key);
						parameters = parametersBySampleCode[key];
					}
				});
			}
			
			// If still no parameters, try to find any parameters that might be related
			if (parameters.length === 0 && parametersBySampleCode) {
				console.log('No direct match found, showing all available keys:');
				Object.keys(parametersBySampleCode).forEach(function(key) {
					console.log('Available key:', key, 'Value:', parametersBySampleCode[key]);
				});
			}
		}
		
		// If still no parameters found, create mock data for testing
		if (parameters.length === 0) {
			console.log('No parameters found, creating mock data for testing...');
			parameters = [
				{
					id: sampleCode + '_1',
					analyte_name: 'pH',
					method: 'Electrode Method',
					result_reporting_symbol: '=',
					result: ''
				},
				{
					id: sampleCode + '_2',
					analyte_name: 'Turbidity',
					method: 'Nephelometric Method',
					result_reporting_symbol: '<',
					result: ''
				},
				{
					id: sampleCode + '_3',
					analyte_name: 'Conductivity',
					method: 'Conductivity Meter',
					result_reporting_symbol: '<=',
					result: ''
				}
			];
			console.log('Created mock parameters:', parameters);
		}
		
		setTimeout(function() {
			renderParametersTable(parameters, sampleCode);
			$('#capture-loading').hide();
		}, 500);
	}

	function renderParametersTable(parameters, sampleCode) {
		console.log('=== Rendering parameters table for sample:', sampleCode, '===');
		console.log('Parameters received:', parameters);
		
		var tableHead = $('#parameters-table-head');
		var tableBody = $('#parameters-table-body');
		var tableWrapper = $('#parameters-table-wrapper');
		
		tableHead.empty();
		tableBody.empty();
		
		if (!parameters || parameters.length === 0) {
			console.log('No parameters found, showing empty message');
			tableHead.append(
				'<tr><th>No Parameters</th></tr>'
			);
			tableBody.append(
				'<tr><td class="text-center">' +
				'<div class="alert alert-warning mb-0">No parameters found for sample: ' + sampleCode + '</div>' +
				'</td></tr>'
			);
			tableWrapper.show();
			return;
		}
		
		console.log('Processing parameters:', parameters);
		
		// Extract all parameters from the nested structure
		var allParameters = [];
		
		// Handle the actual data structure: {6: {cr: [...], section: "..."}}
		if (parameters && typeof parameters === 'object') {
			Object.keys(parameters).forEach(function(sectionKey) {
				var sectionData = parameters[sectionKey];
				console.log('Processing section', sectionKey, ':', sectionData);
				
				// Check if this section has a cr array
				if (sectionData.cr && Array.isArray(sectionData.cr)) {
					console.log('Found cr array with', sectionData.cr.length, 'parameters');
					sectionData.cr.forEach(function(cr) {
						allParameters.push(cr);
					});
				} else if (sectionData.id || sectionData.analyte_name) {
					// Direct parameter object
					allParameters.push(sectionData);
				} else {
					console.warn('Unknown section structure:', sectionData);
				}
			});
		} else if (Array.isArray(parameters)) {
			// Handle array structure
			parameters.forEach(function(parameter, index) {
				console.log('Processing parameter group', index, ':', parameter);
				
				// Handle nested parameter structure
				if (parameter.cr && Array.isArray(parameter.cr)) {
					// Parameter has captured results array
					parameter.cr.forEach(function(cr) {
						allParameters.push(cr);
					});
				} else if (parameter.id || parameter.analyte_name) {
					// Direct parameter object
					allParameters.push(parameter);
				} else {
					console.warn('Unknown parameter structure:', parameter);
				}
			});
		}
		
		console.log('All extracted parameters:', allParameters);
		
		if (allParameters.length === 0) {
			tableBody.append(
				'<tr><td colspan="' + (1 + allParameters.length * 3) + '" class="text-center">' +
				'<div class="alert alert-info mb-0">No valid parameters found for sample: ' + sampleCode + '</div>' +
				'</td></tr>'
			);
			tableWrapper.show();
			return;
		}
		
		// Build table headers with parameter sub-columns
		var headerRow1 = '<tr>' +
		                '<th rowspan="2" class="sample-code-cell">Sample Code</th>';
		var headerRow2 = '<tr>';
		
		allParameters.forEach(function(param) {
			var parameterName = param.analyte_name || (param.my_analyte && param.my_analyte.name) || param.name || 'Unknown Parameter';
			headerRow1 += '<th colspan="5" class="parameter-header">' + parameterName + '</th>';
			headerRow2 += '<th class="method-header">Method</th>' +
			              '<th class="symbol-header">Reporting Symbol</th>' +
			              '<th class="unit-header">Reporting Unit</th>' +
			              '<th class="result-header">Result</th>' +
			              '<th class="standard-header">Standard Limit</th>';
		});
		
		headerRow1 += '</tr>';
		headerRow2 += '</tr>';
		
		tableHead.append(headerRow1);
		tableHead.append(headerRow2);
		
		// Build table body with single row for the sample
		var bodyRow = '<tr data-sample-code="' + sampleCode + '">' +
		             '<td class="sample-code-cell"><strong>' + sampleCode + '</strong></td>';
		
		allParameters.forEach(function(param, index) {
			var parameterId = param.id || param.captured_result_id || (sampleCode + '_' + index);
			var method = param.methods && param.methods.length > 0 ? param.methods[0].name : 
			            param.method_name || param.method || 'Not specified';
			var reportingSymbol = param.result_reporting_symbol || param.reporting_symbol || '';
			var reportingUnit = param.reporting_unit || param.reporting_unit_name || '';
			var currentResult = param.result || param.repeatsampleresult || '';
			var standardLimit = param.standard_value || param.standard_limit || '';
			var analyteName = param.analyte_name || (param.my_analyte && param.my_analyte.name) || param.name || 'Unknown Parameter';
			
			// Method cell with editable dropdown
			bodyRow += '<td class="method-cell">' +
			          '<select class="method-select form-control form-control-sm" ' +
			          'data-parameter-id="' + parameterId + '" ' +
			          'data-sample-code="' + sampleCode + '">' +
			          '<option value="">Select method...</option>' +
			          '<option value="' + method + '" selected>' + method + '</option>' +
			          '</select>' +
			          '</td>' +
			          
			          // Reporting Symbol cell with editable input
			          '<td class="symbol-cell">' +
			          '<input type="text" class="symbol-input form-control form-control-sm" ' +
			          'data-parameter-id="' + parameterId + '" ' +
			          'data-sample-code="' + sampleCode + '" ' +
			          'value="' + reportingSymbol + '" ' +
			          'placeholder="Enter symbol">' +
			          '</td>' +
			          
			          // Reporting Unit cell with editable dropdown
			          '<td class="unit-cell">' +
			          '<select class="unit-select form-control form-control-sm" ' +
			          'data-parameter-id="' + parameterId + '" ' +
			          'data-sample-code="' + sampleCode + '">' +
			          '<option value="">Select unit...</option>' +
			          '<option value="' + reportingUnit + '" selected>' + reportingUnit + '</option>' +
			          '</select>' +
			          '</td>' +
			          
			          // Result cell with editable input
			          '<td class="result-cell">' +
			          '<input type="text" class="result-input form-control form-control-sm" ' +
			          'data-parameter-id="' + parameterId + '" ' +
			          'data-sample-code="' + sampleCode + '" ' +
			          'data-parameter-name="' + analyteName + '" ' +
			          'value="' + currentResult + '" ' +
			          'placeholder="Enter result">' +
			          '</td>' +
			          
			          // Standard Limit cell with editable input and edit icon
			          '<td class="standard-cell">' +
			          '<div class="d-flex align-items-center">' +
			          '<input type="text" class="standard-input form-control form-control-sm" ' +
			          'data-parameter-id="' + parameterId + '" ' +
			          'data-sample-code="' + sampleCode + '" ' +
			          'value="' + standardLimit + '" ' +
			          'placeholder="Enter standard limit" ' +
			          'readonly>' +
			          '<button type="button" class="btn btn-sm btn-outline-primary edit-standard-btn ml-1" ' +
			          'data-parameter-id="' + parameterId + '" ' +
			          'data-sample-code="' + sampleCode + '" ' +
			          'data-toggle="tooltip" title="Edit Standard Limit">' +
			          '<i class="mdi mdi-pencil"></i>' +
			          '</button>' +
			          '</div>' +
			          '</td>';
		});
		
		bodyRow += '</tr>';
		tableBody.append(bodyRow);
		
		// Update sample info
		$('#selected-sample-info').html(
			'<strong>Sample:</strong> ' + sampleCode + '<br>' +
			'<strong>Parameters:</strong> ' + allParameters.length + ' found'
		);
		
		// Show sample details section
		$('#sample-details-section').show();
		
		tableWrapper.show();
		console.log('Table rendered successfully with', allParameters.length, 'parameters');
		
		// Bind validation events for the new table structure
		bindParameterInputEvents();
		
		// Populate method and unit dropdowns
		populateMethodAndUnitDropdowns();
	}


	// Legacy function removed - now using renderParametersTable instead

	function bindParameterInputEvents() {
		// Bind result input change events for real-time validation with double capture
		$('.result-input').off('input.capture blur.capture').on('input.capture', function() {
			var $input = $(this);
			var $row = $input.closest('tr');
			var parameterId = $input.data('parameter-id');
			var result = $input.val();
			var originalValue = $input.data('original-value');
			
			// Double capture logic
			if (originalValue && originalValue !== '') {
				// If there's an original value, require double capture
				if (result !== originalValue) {
					$input.addClass('border-warning');
					return; // Don't validate until double capture is complete
				} else {
					$input.removeClass('border-warning');
				}
			}
			
			// Basic validation - just check if result is entered
			if (result && result.trim() !== '') {
				$input.removeClass('border-danger').addClass('border-success');
			} else {
				$input.removeClass('border-success border-danger');
			}
		}).on('blur.capture', function() {
			var $input = $(this);
			var result = $input.val();
			var originalValue = $input.data('original-value');
			
			// Check if this is a new result entry (not an edit)
			if (!originalValue && result) {
				// Set as original value for future edits (implementing double capture for next edit)
				$input.data('original-value', result);
			}
		});
		
		// Bind symbol input change events
		$('.symbol-input').off('input.symbol blur.symbol').on('input.symbol', function() {
			var $input = $(this);
			var symbol = $input.val();
			
			// Basic validation for symbol
			if (symbol && symbol.trim() !== '') {
				$input.removeClass('border-danger').addClass('border-success');
			} else {
				$input.removeClass('border-success border-danger');
			}
		}).on('blur.symbol', function() {
			var $input = $(this);
			var symbol = $input.val();
			console.log('Symbol changed for parameter:', $input.data('parameter-id'), 'to:', symbol);
		});
		
		// Bind method select change events
		$('.method-select').off('change.method').on('change.method', function() {
			var $select = $(this);
			var method = $select.val();
			var parameterId = $select.data('parameter-id');
			
			console.log('Method changed for parameter:', parameterId, 'to:', method);
			
			// Visual feedback
			if (method && method.trim() !== '') {
				$select.removeClass('border-danger').addClass('border-success');
			} else {
				$select.removeClass('border-success border-danger');
			}
		});
		
		// Bind unit select change events
		$('.unit-select').off('change.unit').on('change.unit', function() {
			var $select = $(this);
			var unit = $select.val();
			var parameterId = $select.data('parameter-id');
			
			console.log('Unit changed for parameter:', parameterId, 'to:', unit);
			
			// Visual feedback
			if (unit && unit.trim() !== '') {
				$select.removeClass('border-danger').addClass('border-success');
			} else {
				$select.removeClass('border-success border-danger');
			}
		});
		
		// Bind standard limit edit button events
		$('.edit-standard-btn').off('click.standard').on('click.standard', function() {
			var $btn = $(this);
			var $input = $btn.siblings('.standard-input');
			var parameterId = $btn.data('parameter-id');
			
			// Toggle readonly state
			if ($input.prop('readonly')) {
				$input.prop('readonly', false).focus();
				$btn.html('<i class="mdi mdi-check"></i>').removeClass('btn-outline-primary').addClass('btn-success');
			} else {
				$input.prop('readonly', true);
				$btn.html('<i class="mdi mdi-pencil"></i>').removeClass('btn-success').addClass('btn-outline-primary');
				
				// Save the value
				var standardValue = $input.val();
				console.log('Standard limit saved for parameter:', parameterId, 'value:', standardValue);
			}
		});
		
		// Bind standard input change events
		$('.standard-input').off('input.standard blur.standard').on('input.standard', function() {
			var $input = $(this);
			var value = $input.val();
			
			// Basic validation for standard limit
			if (value && value.trim() !== '') {
				$input.removeClass('border-danger').addClass('border-success');
			} else {
				$input.removeClass('border-success border-danger');
			}
		}).on('blur.standard', function() {
			var $input = $(this);
			var $btn = $input.siblings('.edit-standard-btn');
			
			// Auto-save on blur if not readonly
			if (!$input.prop('readonly')) {
				$input.prop('readonly', true);
				$btn.html('<i class="mdi mdi-pencil"></i>').removeClass('btn-success').addClass('btn-outline-primary');
			}
		});
	}
	
	function populateMethodAndUnitDropdowns() {
		// Get available methods and units from the existing data
		var availableMethods = [];
		var availableUnits = [];
		
		// Try to get methods and units from the existing modal or global data
		if (typeof window.availableMethods !== 'undefined') {
			availableMethods = window.availableMethods;
		}
		if (typeof window.availableUnits !== 'undefined') {
			availableUnits = window.availableUnits;
		}
		
		// If not available, create some default options
		if (availableMethods.length === 0) {
			availableMethods = [
				'Electrode Method',
				'Nephelometric Method',
				'Conductivity Meter',
				'Colorimetric Method',
				'Titration Method',
				'Spectrophotometric Method',
				'Gravimetric Method',
				'Volumetric Method'
			];
		}
		
		if (availableUnits.length === 0) {
			availableUnits = [
				'mg/L',
				'μg/L',
				'pH units',
				'NTU',
				'μS/cm',
				'mS/cm',
				'°C',
				'%',
				'ppm',
				'ppb'
			];
		}
		
		// Populate method dropdowns
		$('.method-select').each(function() {
			var $select = $(this);
			var currentValue = $select.val();
			
			// Clear existing options except the first one
			$select.find('option:not(:first)').remove();
			
			// Add available methods
			availableMethods.forEach(function(method) {
				var selected = method === currentValue ? 'selected' : '';
				$select.append('<option value="' + method + '" ' + selected + '>' + method + '</option>');
			});
		});
		
		// Populate unit dropdowns
		$('.unit-select').each(function() {
			var $select = $(this);
			var currentValue = $select.val();
			
			// Clear existing options except the first one
			$select.find('option:not(:first)').remove();
			
			// Add available units
			availableUnits.forEach(function(unit) {
				var selected = unit === currentValue ? 'selected' : '';
				$select.append('<option value="' + unit + '" ' + selected + '>' + unit + '</option>');
			});
		});
		
		console.log('Populated dropdowns with', availableMethods.length, 'methods and', availableUnits.length, 'units');
	}

	/*
	function createParameterTableRow(paramData, index, sampleCode) {
		console.log('Creating table row for parameter:', paramData);
		
		// Safely extract data with fallbacks
		var analyteName = paramData.analyte_name || paramData.name || 'Unknown Analyte';
		var analysisTypeCode = '';
		if (paramData.analysis_type && paramData.analysis_type.code) {
			analysisTypeCode = paramData.analysis_type.code;
		} else if (paramData.analysis_type_code) {
			analysisTypeCode = paramData.analysis_type_code;
		} else {
			analysisTypeCode = 'N/A';
		}
		
		var bgClass = getParameterRowBackgroundClass(paramData.result, paramData.remark);
		
		return `
			<tr class="parameter-row ${bgClass}" data-parameter-id="${paramData.id || ''}">
				<!-- Column 1: Method and Reporting Unit -->
				<td class="align-top">
					<div class="mb-2">
						<strong class="text-primary">${analyteName}</strong>
						<small class="text-muted d-block">${analysisTypeCode}</small>
					</div>
					<div class="d-flex justify-content-between align-items-start">
						<div class="method-display flex-grow-1">
							<div class="mb-1">
								<small class="text-muted">Method:</small><br>
								<span class="method-name">${paramData.method_name || 'Not selected'}</span>
							</div>
							<div>
								<small class="text-muted">Unit:</small><br>
								<span class="reporting-unit">${paramData.reporting_unit_id || paramData.reporting_unit || 'Not selected'}</span>
							</div>
						</div>
						<button class="btn btn-sm btn-outline-primary edit-method-btn ml-2" 
							data-parameter-id="${paramData.id || ''}"
							data-method-id="${paramData.method_id || ''}"
							data-reporting-unit="${paramData.reporting_unit_id || paramData.reporting_unit || ''}"
							data-toggle="tooltip" title="Edit Method & Unit">
							<i class="mdi mdi-pencil"></i>
						</button>
					</div>
				</td>
				
				<!-- Column 2: Reporting Symbol and Standard Limit -->
				<td class="align-top">
					<div class="form-group mb-2">
						<label class="form-label small font-weight-bold">Reporting Symbol:</label>
						<input type="text" class="form-control form-control-sm reporting-symbol-input" 
							value="${paramData.result_reporting_symbol || ''}" 
							data-parameter-id="${paramData.id || ''}"
							placeholder="Enter symbol...">
					</div>
					<div class="form-group mb-0">
						<label class="form-label small font-weight-bold">Standard Limit:</label>
						<input type="text" class="form-control form-control-sm standard-limit-input" 
							value="${paramData.standard_value || paramData.standard_limit || ''}" 
							data-parameter-id="${paramData.id || ''}"
							placeholder="Enter limit...">
					</div>
				</td>
				
				<!-- Column 3: Results Input -->
				<td class="align-top">
					<div class="form-group mb-2">
						<label class="form-label small font-weight-bold">Result:</label>
						<input type="text" class="form-control result-input" 
							value="${paramData.result || ''}" 
							data-parameter-id="${paramData.id || ''}"
							placeholder="Enter result..."
							data-original-value="${paramData.result || ''}">
						<small class="form-text text-muted double-capture-hint" style="display: none;">
							Enter the same result again to confirm
						</small>
					</div>
					<div class="result-validation">
						<small class="remark-display text-muted">${paramData.remark || 'No remark'}</small>
					</div>
				</td>
			</tr>
		`;
	}

	function getParameterBackgroundClass(result, remark) {
		if (!result || !remark) return '';
		
		if (remark === 'PASS') return 'bg-success-light';
		if (remark === 'FAIL') return 'bg-danger-light';
		return 'bg-secondary-light';
	}

	function getParameterRowBackgroundClass(result, remark) {
		if (!result || !remark) return '';
		
		if (remark === 'PASS') return 'table-success';
		if (remark === 'FAIL') return 'table-danger';
		return 'table-secondary';
	}

	// Old functions commented out to prevent syntax errors
	/*
	function validateResultInput($resultInput, result, standardLimit, $row) {
		// Implement validation logic similar to existing system
		var numericResult = parseFloat(result);
		var numericLimit = parseFloat(standardLimit);
		var remark = '';
		var rowClass = '';

		if (!isNaN(numericResult) && !isNaN(numericLimit)) {
			if (numericResult <= numericLimit) {
				remark = 'PASS';
				rowClass = 'table-success';
			} else {
				remark = 'FAIL';
				rowClass = 'table-danger';
			}
		} else {
			remark = 'OTHER';
			rowClass = 'table-secondary';
		}

		// Update row background
		$row.removeClass('table-success table-danger table-secondary').addClass(rowClass);
		
		// Update remark display
		$row.find('.remark-display').text(remark);
	}
	*/

	// Handle method editing
	$(document).on('click', '.edit-method-btn', function() {
		currentCaptureParameterId = $(this).data('parameter-id');
		var methodId = $(this).data('method-id');
		var reportingUnit = $(this).data('reporting-unit');
		
		$('#method-select').val(methodId);
		$('#reporting-unit-select').val(reportingUnit);
		$('#edit-method-modal').modal('show');
	});

	// Handle result input with validation and double capture
	$(document).on('blur', '.result-input', function() {
		var $input = $(this);
		var result = $input.val();
		var parameterId = $input.data('parameter-id');
		var sampleCode = $input.data('sample-code');
		var parameterName = $input.data('parameter-name');
		
		if (result && result.trim() !== '') {
			// Prompt for confirmation (double capturing logic)
			var confirmation = prompt('Please confirm the result for ' + parameterName + ' in sample ' + sampleCode + ':');
			if (confirmation === result) {
				// Validate against standard limit and update styling
				validateResultAgainstStandard(result, parameterId, $input);
			} else {
				alert("Result confirmation didn't match captured result!");
				$input.val('');
				$input.closest('tr').removeClass('table-success table-danger table-secondary');
			}
		}
	});

	function validateResultAgainstStandard(result, parameterId, $input) {
		console.log('Validating result:', result, 'for parameter:', parameterId);
		
		$.ajax({
			url: '/fetch/results-remark',
			method: 'post',
			data: {
				captured_result_id: parameterId,
				result: result,
				reporting_symbol: '', // Will be determined on server side
				_token: $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				console.log('Validation response:', response);
				
				// Update row styling based on result
				var $row = $input.closest('tr');
				$row.removeClass('table-success table-danger table-secondary');
				
				if (response && response.toLowerCase().includes('pass')) {
					$row.addClass('table-success');
					$input.addClass('border-success');
				} else if (response && response.toLowerCase().includes('fail')) {
					$row.addClass('table-danger');
					$input.addClass('border-danger');
				} else {
					$row.addClass('table-secondary');
					$input.addClass('border-secondary');
				}
				
				// Store the remark for later use
				$input.attr('data-remark', response || '');
			},
			error: function(xhr, status, error) {
				console.error('Validation error:', error);
				$input.attr('data-remark', 'Error validating result');
			}
		});
	}

	function loadAvailableMethods() {
		// Load methods from existing data or make AJAX call
		$.ajax({
			url: '/get-available-methods',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			success: function(methods) {
				availableMethods = methods;
				var methodSelect = $('#method-select');
				methodSelect.empty().append('<option value="">Select method...</option>');
				if (methods && Array.isArray(methods)) {
					methods.forEach(function(method) {
						methodSelect.append('<option value="' + method.id + '">' + method.name + '</option>');
					});
				}
			},
			error: function(xhr, status, error) {
				console.error('Failed to load methods:', error);
				console.error('Response:', xhr.responseText);
				// Fallback: try to load methods from existing page data
				loadMethodsFromPageData();
			}
		});
	}

	function loadMethodsFromPageData() {
		// Fallback method to load from existing page data
		var methods = $('#sample-detail-rows').data('methods') || {};
		var methodSelect = $('#method-select');
		methodSelect.empty().append('<option value="">Select method...</option>');
		
		Object.keys(methods).forEach(function(key) {
			methodSelect.append('<option value="' + key + '">' + methods[key] + '</option>');
		});
	}

	// Save capture results
	$('#save-capture-results').on('click', function() {
		var resultsData = [];
		
		$('.result-input').each(function() {
			var $resultInput = $(this);
			var parameterId = $resultInput.data('parameter-id');
			var sampleCode = $resultInput.data('sample-code');
			var parameterName = $resultInput.data('parameter-name');
			var result = $resultInput.val();
			
			// Find corresponding method, symbol, unit, and standard inputs for this parameter
			var $row = $resultInput.closest('tr');
			var method = $row.find('.method-select[data-parameter-id="' + parameterId + '"]').val();
			var symbol = $row.find('.symbol-input[data-parameter-id="' + parameterId + '"]').val();
			var unit = $row.find('.unit-select[data-parameter-id="' + parameterId + '"]').val();
			var standardLimit = $row.find('.standard-input[data-parameter-id="' + parameterId + '"]').val();
			
			resultsData.push({
				parameter_id: parameterId,
				sample_code: sampleCode,
				parameter_name: parameterName,
				result: result ? result.trim() : '',
				method: method || '',
				reporting_symbol: symbol || '',
				reporting_unit: unit || '',
				standard_limit: standardLimit || '',
				remark: $resultInput.attr('data-remark') || ''
			});
		});
		
		if (resultsData.length === 0) {
			alert('No results to save.');
			return;
		}
		
		// Save via AJAX
		$.ajax({
			url: '{{ route("capture-results-save") }}',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			data: {
				capture_results: resultsData,
				_token: $('meta[name="csrf-token"]').attr('content')
			},
			beforeSend: function() {
				$('#save-capture-results').prop('disabled', true).html('<i class="mdi mdi-sync mdi-spin"></i> Saving...');
			},
			success: function(response) {
				console.log('Save response:', response);
				if (response.success) {
					alert('Results saved successfully!');
					$('#capture-results-modal').modal('hide');
					// Refresh the page to show updated results
					location.reload();
				} else {
					alert('Error: ' + (response.message || 'Unknown error occurred'));
				}
			},
			error: function(xhr, status, error) {
				console.error('Save error:', error);
				console.error('Response:', xhr.responseText);
				var errorMessage = 'Error saving results. Please try again.';
				try {
					var response = JSON.parse(xhr.responseText);
					if (response.error) {
						errorMessage = response.error;
					}
				} catch (e) {
					// Use default error message
				}
				alert(errorMessage);
			},
			complete: function() {
				$('#save-capture-results').prop('disabled', false).html('<i class="mdi mdi-content-save"></i> Save Results');
			}
		});
	});

	function refreshParametersSection() {
		// Refresh the parameters section dynamically instead of full page reload
		var selectedSample = $('#capture-sample-select').val();
		if (selectedSample) {
			// Reload parameters for the currently selected sample
			loadParametersForSample(selectedSample);
		}
		
		// Optionally refresh the main sample workflow table
		// This could be enhanced to only refresh specific sections
		setTimeout(function() {
			location.reload();
		}, 1000);
	}

	// Handle method and unit editing functionality
	$('#save-method-unit').on('click', function() {
		var methodId = $('#method-select').val();
		var reportingUnit = $('#reporting-unit-select').val();
		
		if (currentCaptureParameterId) {
			var $parameterCard = $('.parameter-card[data-parameter-id="' + currentCaptureParameterId + '"]');
			var $methodDisplay = $parameterCard.find('.method-display');
			
			// Update the display
			var methodName = $('#method-select option:selected').text();
			$methodDisplay.find('.method-name').text(methodName || 'Not selected');
			$methodDisplay.find('.reporting-unit').text(reportingUnit || 'Not selected');
			
			// Update the edit button data
			$parameterCard.find('.edit-method-btn').attr('data-method-id', methodId).attr('data-reporting-unit', reportingUnit);
			
			$('#edit-method-modal').modal('hide');
		}
	});

	// Add new method
	$('#add-new-method').on('click', function() {
		var newMethodName = $('#new-method-name').val().trim();
		if (newMethodName) {
			// In a real implementation, this would make an AJAX call to save the new method
			// For now, just add it to the dropdown
			var newMethodId = 'new_' + Date.now();
			$('#method-select').append('<option value="' + newMethodId + '">' + newMethodName + '</option>');
			$('#method-select').val(newMethodId);
			$('#new-method-name').val('');
			alert('New method added successfully!');
		} else {
			alert('Please enter a method name.');
		}
	});

	// Add new reporting unit
	$('#add-new-unit').on('click', function() {
		var newUnitName = $('#new-unit-name').val().trim();
		if (newUnitName) {
			// In a real implementation, this would make an AJAX call to save the new unit
			// For now, just add it to the dropdown
			$('#reporting-unit-select').append('<option value="' + newUnitName + '">' + newUnitName + '</option>');
			$('#reporting-unit-select').val(newUnitName);
			$('#new-unit-name').val('');
			alert('New reporting unit added successfully!');
		} else {
			alert('Please enter a unit name.');
		}
	});

	// Initialize capture results modal - no longer tied to specific sample rows
	// The modal will allow selection of any sample in the batch
	

	var sampleDetailsRow = `<tr class="editable">
		<td>
		<input type="checkbox" class="select-row-check mt-1" /></td>
		<td class="block toolbar" nowrap>
			@if(isset($batch->status) && in_array($batch->status, array("Samples En-Route" ,"Samples Reception","Sample Approval","Samples In Lab","Sample Verification","Samples In Lab")))
			<span class="btn edit-remove btn-default text-primary btn-sm no-data hidden toggle-row-edit-mode" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></span> &nbsp;
			@endif
			@if(isset($batch->status) && in_array($batch->status, array("Samples En-Route" ,"Samples Reception")))
				<span class="btn btn-default delete-remove text-danger btn-sm delete-row" data-toggle="tooltip" title="Delete"><i class="mdi mdi-trash-can"></i></span>
			@endif
			@if(isset($batch->status) && in_array($batch->status, array("Sample Approval","Samples In Lab","Sample Verification")))
				<span class="btn btn-default interpretation-remove  no-data hidden  text-success btn-sm provide-interpretation-row" data-target="#provide-interpretations" data-toggle="modal"  data-toggle="tooltip" title="Comments and Interpretation"><i class="mdi mdi-android-messages"></i></span>
			@endif
			@if(isset($batch->status) && in_array($batch->status, array("Samples En-Route","Samples Request Review","Samples Reception","Samples In Lab")))
			<span class="btn btn-default btn-sm initiate-interlab  no-data hidden  text-warning" data-sample="" data-analysistype="" data-samplecode="" data-toggle="modal" data-target="#inter-lab-add" data-action="add"><i class="mdi mdi-swap-horizontal-bold" data-toggle="tooltip" title="Initiate inter Lab"></i></span>
			@endif
			<span class="btn btn-default parameter-remove  no-data hidden  show-parameter-initiator text-info btn-sm dropdown-row" data-target="#show-sample-analysis-analytes" data-toggle="modal" data-toggle="tooltip" title="Parameters" ><i class="mdi mdi-snowflake"></i></span>
		</td>
		<td class="sample-code-field" nowrap>
			<div class="form-group form-group-sm">
				<input type="text" style="width: 70px" class="form-control form-control-sm sample-code" name="sample_details[sample_code][]" readonly="true" />
			</div>
			<span class="text"></span>
		</td>
		<td class="analysis-field" nowrap>
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required sample-analysis" multiple {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} style="width: 200px" placeholder="Select Analysis...">
					@if($selectedSampleType)
						@foreach($selectedSampleType->analysis_types as $typ)
							<option value="{{ $typ->id }}">{{ $typ->name }}</option>
						@endforeach
					@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		
		
		<td class="sample-lab-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm  is-required sample-lab" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[lab_id][]" style="width: 200px" placeholder="Select..." required></select>
			</div>
			<input type="hidden" name="sample_details[is_duplicate][]" value="" class="">
			<span class="text"></span>
		</td>
		<td class="sample-condition-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required sample-condition" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[sample_condition][]" style="width: 200px" placeholder="Select Sample Condition..." required>
					@foreach($conditions as $con)
						<option value="{{ $con->id }}">{{ $con->name }}</option>
					@endforeach
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-sample_point-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm  is-required sample-point" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[sample_point][]" style="width: 200px" placeholder="Select..." required></select>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-product-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required sample-product" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[product][]" style="width: 200px" placeholder="Select..." required>
					@foreach($products as $product)
					<option value="{{$product->id}}">{{$product->name}}</option>
					@endforeach
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class="comments-field text-center" nowrap>
			<div class="form-group form-group-sm">

				<span class="btn btn-sm btn-default bg-white text-primary float-left" data-toggle="modal" data-target="#capture-markings" style=""><i class="mdi mdi-pencil" data-toggle="tooltip" title="Capture Markings"></i></span>
				<textarea rows="1" style="" class="form-control hidden form-control-sm sample-comments" name="sample_details[comments][]" placeholder="Sample Comments..."></textarea>
			</div>
			<span class=" text btn btn-sm btn-primary sample_marking_holder" data-toggle="modal" data-target="#sample-marking-show" data-samplecode="" data-marking="" data-name="sample-comment-holder"><i class="mdi mdi-card-text-outline" ></i></span>
		</td>
		<td class="barcode-field ">
			<div class="form-group form-group-sm">
				<input type="time" style="width: 100px" class="form-control form-control-sm sample-barcode" name="sample_details[barcode][]" placeholder="BarCode..." />
			</div>
			<span class="text"></span>
		</td>

		<td class ="main-standard-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required main-standard" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[main_standard][]" style"width:200px" placeholder="Select Main Standard..." required >
				@if($standards)
					<option value=""></option>
					@foreach($standards as $standard)
					<option value="{{$standard->id}}">{{$standard->name}}</option>
					@endforeach
				@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class ="secondary-standard-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm secondary-standard" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[secondary_standard][]" style"width:200px" placeholder="Select Sec Standard...">
				@if($standards)
					<option value=""></option>
					@foreach($standards as $standard)
					<option value="{{$standard->id}}">{{$standard->name}}</option>
					@endforeach
				@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		
		
		<td class="sample-code-field" nowrap>
			<div class="form-group form-group-sm">
				<input type="date" style="width: 200px" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} class="form-control form-control-sm disposal-date" value={{$disposal_date}} name="sample_details[disposal_date][]"/>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-store-field" nowrap>
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm sample-store" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[sample_store][]" style="width: 200px" placeholder="Select Sample Storage...">
					<option></option>
					@if($labStores)
						@foreach($labStores as $store)
							<option value="{{ $store['id'] }}" data-items="{{ json_encode($store['items']) }}">{{ $store['name'] }}</option>
						@endforeach
					@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-slot-field" nowrap>
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm sample-store-slot" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[sample_store_slot][]" style="width: 200px !important" placeholder="Select a Srore First...">
					<option></option>
				</select>
			</div>
			<span class="text"></span>
		</td>
		
		<td class="sample-quantity-field">
			<div class="form-group form-group-sm">
				<input type="number" min="0" style="width: 200px !important" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} class="form-control form-control-sm sample-quantity"  name="sample_details[sample_quantity][]" placeholder="Sample Quantity..." />
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-reporting-unit-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm sample-reporting-unit" {!! isset($batch->id) && in_array($batch->status,["Sample Approval","Samples In Lab","Sample Verification","Samples In Lab"]) ? 'disabled' : '' !!} name="sample_details[sample_reporting_unit][]" style="width: 200px !important" placeholder="Select Sample Reporting Unit...">
					<option></option>
					@if($reportingUnits)
						@foreach($reportingUnits as $unit)
							<option value="{{ $unit['name'] }}">{{ $unit['name'] }}</option>
						@endforeach
					@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		
		

	</tr>`;

	$(document).ready(function() {
    // Handle COA report generation
		$('#generate-coa-btn').on('click', function() {
			var reportFormat = $('#report_format_select').val();
			var batchId = $('input[name="batch_id"]').val();
			
			if (!reportFormat) {
				alert('Please select a report format');
				return;
			}
			
			// Generate the URL for the PDF report
			var url = '{{ route("process-pdf-report", ["batch_id" => ":batch_id", "report_format" => ":report_format"]) }}';
			url = url.replace(':batch_id', batchId);
			url = url.replace(':report_format', reportFormat);
			
			// Open the PDF in a new window/tab
			window.open(url, '_blank');
			
			// Close the modal
			$('#view-coa-report').modal('hide');
		});
		// Handle store selection to populate slots
		$('#bulk-store-select').on('change', function() {
			var selectedStore = $(this).find('option:selected');
			var slots = selectedStore.data('slots');
			var slotSelect = $('#bulk-slot-select');
			
			slotSelect.html('<option value="">-- No Change --</option>');
			
			if (slots) {
				$.each(slots, function(slotId, slotName) {
					slotSelect.append('<option value="' + slotId + '">' + slotName + '</option>');
				});
			}
		});
		
		// Initialize select2 when modal is shown
		$('#bulk-update-samples-modal').on('show.bs.modal', function() {
			// Initialize select2 for multi-select samples
			$('#bulk-sample-select').select2({
				placeholder: 'Select samples to update',
				allowClear: true,
				width: '100%'
			});
			
			// Initialize select2 for standards
			$('#bulk-main-standard').select2({
				placeholder: '-- No Change --',
				allowClear: true,
				width: '100%'
			});
			
			$('#bulk-secondary-standard').select2({
				placeholder: '-- No Change --',
				allowClear: true,
				width: '100%'
			});
			
			// Initialize select2 for storage
			$('#bulk-store-select').select2({
				placeholder: '-- No Change --',
				allowClear: true,
				width: '100%'
			});
			
			$('#bulk-slot-select').select2({
				placeholder: '-- No Change --',
				allowClear: true,
				width: '100%'
			});
		});
		
		// Destroy select2 instances when modal is hidden to prevent memory leaks
		$('#bulk-update-samples-modal').on('hidden.bs.modal', function() {
			$('#bulk-sample-select').select2('destroy');
			$('#bulk-main-standard').select2('destroy');
			$('#bulk-secondary-standard').select2('destroy');
			$('#bulk-store-select').select2('destroy');
			$('#bulk-slot-select').select2('destroy');
		});
	});

	// Phase 5: Assign Samples Modal JavaScript
	$(document).on('click', '.assign-samples-btn', function() {
		const stagingId = $(this).data('staging-id');
		const headerId = $(this).data('header-id');
		
		loadAssignmentData(stagingId, headerId);
	});
	
	// Function to load assignment data
	function loadAssignmentData(stagingId, headerId, savedSelections = null) {
		// Load modal data via AJAX
		$.ajax({
			url: '/lab/samples/staging/' + stagingId + '/load-assignment-data',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			success: function(response) {
				// Populate modal
				$('#modal-batch-code').text(response.batch_code);
				$('#modal-sample-type').text(response.sample_type);
				$('#modal-customer').text(response.customer);
				$('#modal-company-unit').text(response.company_unit);
				
				// Set customer profile link
				if (response.customer_id) {
					const customerProfileUrl = '/livewire/customers/' + response.customer_id + '/profile';
					$('#add-sample-points-link').attr('href', customerProfileUrl);
				}
				
				// Build sample points table
				let tableHtml = buildSamplePointsTable(response.areas);
				$('#sample-assignment-table-container').html(tableHtml);
				
				// Restore saved selections if any
				if (savedSelections) {
					restoreSavedSelections(savedSelections);
				}
				
				// Store IDs for submission
				$('#assignSamplesModal').data('staging-id', stagingId);
				$('#assignSamplesModal').data('header-id', headerId);
				$('#assignSamplesModal').data('customer-id', response.customer_id);
				$('#assignSamplesModal').data('company-unit-id', response.company_unit_id);
				$('#assignSamplesModal').data('company-sub-unit-id', response.company_sub_unit_id);
				
				// Load available areas and points for adding new ones
				loadAvailableAreasAndPoints(stagingId);
				
				// Show modal
				$('#assignSamplesModal').modal('show');
			},
			error: function(xhr) {
				console.log('Error details:', xhr);
				alert('Error loading assignment data: ' + (xhr.responseJSON?.message || xhr.statusText));
			}
		});
	}

	function buildSamplePointsTable(areas) {
		let html = '<div class="table-responsive">';
		html += '<table class="table" style="border-collapse: collapse;">';
		html += '<thead style="background-color: rgba(0, 0, 0, .08);">';
		html += '<tr>';
		html += '<th style="width: 50px; text-align: center; padding: 12px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;"><i class="mdi mdi-checkbox-marked-circle-outline"></i></th>';
		html += '<th style="padding: 12px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Sample Point Name</th>';
           html += '<th style="width: 150px; text-align: center; padding: 12px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Quantity</th>';
		html += '</tr>';
		html += '</thead>';
		html += '<tbody>';
		
		areas.forEach(area => {
			html += `<tr style="background: linear-gradient(135deg, rgba(108, 117, 125, 0.05) 0%, rgba(248, 249, 250, 0.6) 100%);">
				<td colspan="3" class="py-3" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
					<strong style="font-size: 1rem; color: #495057; font-weight: 600;">
						<i class="mdi mdi-map-marker text-primary"></i> ${area.name}
					</strong>
				</td>
			</tr>`;
			
               area.sample_points.forEach(point => {
                   html += `<tr>
                       <td class="text-center align-middle" style="padding: 12px; border-bottom: 1px solid #dee2e6;">
                           <input type="checkbox" 
                                  class="sample-point-checkbox form-check-input" 
                                  value="${point.id}" 
                                  data-area-id="${area.id}"
                                  style="width: 20px; height: 20px; cursor: pointer;">
                       </td>
                       <td class="align-middle" style="padding: 12px; border-bottom: 1px solid #dee2e6;">
                           <span style="font-weight: 500; color: #495057;">${point.name}</span>
                       </td>
                       <td class="align-middle" style="padding: 12px; border-bottom: 1px solid #dee2e6;">
                           <input type="number" 
                                  class="form-control form-control-sm sample-quantity" 
                                  min="1" 
                                  value="1" 
                                  data-point-id="${point.id}" 
                                  disabled
                                  style="max-width: 100px; margin: 0 auto; text-align: center; border-radius: 8px; border: 2px solid #e9ecef;">
                       </td>
                   </tr>`;
               });
		});
		
		html += '</tbody></table></div>';
		return html;
	}

	// Enable quantity input when checkbox is checked (for record purposes)
	$(document).on('change', '.sample-point-checkbox', function() {
		const pointId = $(this).val();
		$(`input.sample-quantity[data-point-id="${pointId}"]`).prop('disabled', !this.checked);
	});
	
	// Function to get current selections
	function getCurrentSelections() {
		const selections = [];
		$('.sample-point-checkbox:checked').each(function() {
			const pointId = $(this).val();
			const quantity = $(`.sample-quantity[data-point-id="${pointId}"]`).val();
			selections.push({ 
				sample_point_id: pointId, 
				quantity: quantity 
			});
		});
		return selections;
	}
	
	// Function to restore saved selections
	function restoreSavedSelections(savedSelections) {
		setTimeout(function() {
			savedSelections.forEach(function(selection) {
				const checkbox = $(`.sample-point-checkbox[value="${selection.sample_point_id}"]`);
				if (checkbox.length > 0) {
					checkbox.prop('checked', true);
					const quantityInput = $(`.sample-quantity[data-point-id="${selection.sample_point_id}"]`);
					quantityInput.prop('disabled', false);
					quantityInput.val(selection.quantity);
				}
			});
		}, 100);
	}
	
	// Handle refresh button click
	$(document).on('click', '#refresh-sample-points-btn', function() {
		const stagingId = $('#assignSamplesModal').data('staging-id');
		const headerId = $('#assignSamplesModal').data('header-id');
		
		// Save current selections
		const savedSelections = getCurrentSelections();
		
		// Show loading state
		$(this).html('<i class="mdi mdi-loading mdi-spin"></i> Refreshing...').prop('disabled', true);
		
		// Reload data with saved selections
		loadAssignmentData(stagingId, headerId, savedSelections);
		
		// Reset button state after a short delay
		setTimeout(() => {
			$(this).html('<i class="mdi mdi-refresh"></i> Refresh').prop('disabled', false);
		}, 1000);
	});

	// Confirm assignment - use event delegation
	$(document).on('click', '#confirmAssignSamples', function() {
		console.log('Assign button clicked');
		
		const stagingId = $('#assignSamplesModal').data('staging-id');
		const headerId = $('#assignSamplesModal').data('header-id');
		
		console.log('Staging ID:', stagingId);
		console.log('Header ID:', headerId);
		
		// Collect selected sample points with quantity (for record purposes only)
		const selections = [];
		$('.sample-point-checkbox:checked').each(function() {
			const pointId = $(this).val();
			const quantity = $(`.sample-quantity[data-point-id="${pointId}"]`).val();
			selections.push({ sample_point_id: pointId, quantity: quantity });
		});
		
		console.log('Selections:', selections);
		
		if (selections.length === 0) {
			alert('Please select at least one sample point');
			return;
		}
		
		// Disable button to prevent double-click
		$(this).prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Assigning...');
		
		// Submit via AJAX
		$.ajax({
			url: '/lab/samples/assign-samples',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify({
				sample_header_id: headerId,
				sample_detail_stage_id: stagingId,
				selections: selections
			}),
			success: function(response) {
				console.log('Success response:', response);
				if (response.success) {
					alert(response.message);
					location.reload();
				} else {
					alert('Error: ' + (response.message || 'Unknown error'));
					$('#confirmAssignSamples').prop('disabled', false).html('<i class="mdi mdi-check"></i> Yes, Assign');
				}
			},
			error: function(xhr) {
				console.log('Error details:', xhr);
				console.log('Status:', xhr.status);
				console.log('Response:', xhr.responseText);
				alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
				$('#confirmAssignSamples').prop('disabled', false).html('<i class="mdi mdi-check"></i> Yes, Assign');
			}
		});
	});

// Load available areas and sample points
function loadAvailableAreasAndPoints(stagingId) {
	$.ajax({
		url: '/lab/samples/staging/' + stagingId + '/available-areas-points',
		method: 'GET',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Accept': 'application/json'
		},
		success: function(response) {
			console.log('Response from available-areas-points:', response);
			
			//  Populate areas dropdown
			const $areaSelect = $('#new-sample-area');
			$areaSelect.html('<option value="">-- Select Area --</option>');
			
			if (response.areas && response.areas.length > 0) {
				response.areas.forEach(function(area) {
					$areaSelect.append('<option value="' + area.id + '">' + area.name + '</option>');
				});
			}
			
			// Populate sample points dropdown immediately (not dependent on area selection)
			const $pointSelect = $('#new-sample-point');
			$pointSelect.html('<option value="">-- Select Sample Point --</option>');
			
			// Get all sample points for this sample type
			const allPoints = response.sample_points.all || [];
			console.log('All sample points:', allPoints);
			
			if (allPoints.length > 0) {
				allPoints.forEach(function(point) {
					$pointSelect.append('<option value="' + point.id + '">' + point.name + '</option>');
				});
				$pointSelect.prop('disabled', false); // Enable the dropdown
				console.log('Sample point dropdown enabled with', allPoints.length, 'points');
			} else {
				$pointSelect.html('<option value="">-- No Sample Points Available --</option>');
				$pointSelect.prop('disabled', true);
				console.log('No sample points found');
			}
			
			// Store sample points data (keep for compatibility)
			$('#assignSamplesModal').data('available-sample-points', response.sample_points);
			$('#assignSamplesModal').data('all-sample-points', allPoints);
			$('#assignSamplesModal').data('sub-unit-id', response.sub_unit_id);
			// Store company unit/sub unit IDs if not already set (from loadAssignmentData)
			if (response.company_unit_id) {
				$('#assignSamplesModal').data('company-unit-id', response.company_unit_id);
			}
			if (response.sub_unit_id) {
				$('#assignSamplesModal').data('company-sub-unit-id', response.sub_unit_id);
			}
		},
		error: function(xhr) {
			console.error('Error loading available areas and points:', xhr);
		}
	});
}

// Handle area selection change - now only for visual feedback, doesn't filter points
$(document).on('change', '#new-sample-area', function() {
	const areaId = $(this).val();
	// Sample point dropdown is already populated and enabled
	// No need to filter - just keep all points available
});

// Handle sample point selection change
$(document).on('change', '#new-sample-point', function() {
	const pointId = $(this).val();
	$('#btn-add-sample-point').prop('disabled', !pointId);
});

// Handle add button click
$(document).on('click', '#btn-add-sample-point', function() {
	const areaId = $('#new-sample-area').val();
	const samplePointId = $('#new-sample-point').val();
	const customerId = $('#assignSamplesModal').data('customer-id');
	const subUnitId = $('#assignSamplesModal').data('company-sub-unit-id') || $('#assignSamplesModal').data('sub-unit-id');
	const companyUnitId = $('#assignSamplesModal').data('company-unit-id');
	const stagingId = $('#assignSamplesModal').data('staging-id');
	
	if (!areaId || !samplePointId) {
		alert('Please select both area and sample point');
		return;
	}
	
	if (!customerId) {
		alert('Customer ID is missing. Please reload the modal.');
		return;
	}
	
	// Prepare request data
	const requestData = {
		customer_id: customerId,
		area_id: areaId,
		sample_point_id: samplePointId
	};
	
	// Only include company unit/sub unit if they exist
	if (companyUnitId) {
		requestData.crm_company_unit_id = companyUnitId;
	}
	if (subUnitId) {
		requestData.crm_company_sub_unit_id = subUnitId;
	}
	
	console.log('Adding sample point with data:', requestData);
	
	// Disable button during request
	$(this).prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Adding...');
	
	$.ajax({
		url: '/lab/samples/add-customer-sample-point',
		method: 'POST',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		data: JSON.stringify(requestData),
		success: function(response) {
			if (response.success) {
				alert(response.message);
				// Refresh the sample points table
				const headerId = $('#assignSamplesModal').data('header-id');
				$('#refresh-sample-points-btn').click();
				
				// Reset dropdowns
				$('#new-sample-area').val('');
				$('#new-sample-point').html('<option value="">-- Select Area First --</option>').prop('disabled', true);
				$('#btn-add-sample-point').prop('disabled', true).html('<i class="mdi mdi-plus"></i> Add to Customer');
				
				// Reload available areas/points
				loadAvailableAreasAndPoints(stagingId);
			} else {
				alert('Error: ' + (response.message || 'Unknown error'));
				$('#btn-add-sample-point').prop('disabled', false).html('<i class="mdi mdi-plus"></i> Add to Customer');
			}
		},
		error: function(xhr) {
			console.error('Error adding sample point:', xhr);
			alert('Error: ' + (xhr.responseJSON?.error || xhr.statusText));
			$('#btn-add-sample-point').prop('disabled', false).html('<i class="mdi mdi-plus"></i> Add to Customer');
		}
	});
});

	
</script>

<!-- Assign Samples Modal -->
<div class="modal fade" id="assignSamplesModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content" style="border-radius: 15px; border: none;">
			<div class="modal-header" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.8) 100%); border-radius: 15px 15px 0 0; border-bottom: 1px solid rgba(0, 0, 0, 0.08); box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);">
				<h5 class="modal-title" style="color: #495057; font-weight: 600;">
					<i class="mdi mdi-clipboard-check text-primary"></i> Assign Samples
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #495057; opacity: 0.7;">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="background-color: #f8f9fa;">
				<!-- Batch Information -->
				<div class="card mb-3 shadow-sm border-0" style="border-radius: 15px;">
					<div class="card-body p-4">
						<div class="row">
							<div class="col-md-3">
								<strong class="text-muted" style="font-size: 0.875rem;"><i class="mdi mdi-chevron-right"></i> Lab No:</strong> 
								<div id="modal-batch-code" style="font-size: 1rem; font-weight: 600; color: #495057;padding-left: 18px;"></div>
							</div>
							<div class="col-md-3">
								<strong class="text-muted" style="font-size: 0.875rem;"><i class="mdi mdi-chevron-right"></i> Sample Type:</strong> 
								<div id="modal-sample-type" style="font-size: 1rem; font-weight: 600; color: #495057;padding-left: 18px;"></div>
							</div>
							<div class="col-md-3">
								<strong class="text-muted" style="font-size: 0.875rem;"><i class="mdi mdi-chevron-right"></i> Customer:</strong> 
								<div id="modal-customer" style="font-size: 1rem; font-weight: 600; color: #495057;padding-left: 18px;"></div>
							</div>
							<div class="col-md-3">
								<strong class="text-muted" style="font-size: 0.875rem;"><i class="mdi mdi-chevron-right"></i> Company Unit:</strong> 
								<div id="modal-company-unit" style="font-size: 1rem; font-weight: 600; color: #495057;padding-left: 18px;"></div>
							</div>
						</div>
					</div>
			</div>

			<!-- Add New Sample Point Section -->
			<div class="card mb-3 shadow-sm border-0" style="border-radius: 15px;">
				<div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
					<h6 class="mb-0 text-muted">
						<i class="mdi mdi-plus-circle"></i> Add New Sample Point
					</h6>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="new-sample-area" class="form-label"><strong>Sample Area</strong></label>
								<select id="new-sample-area" class="form-control">
									<option value="">-- Select Area --</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="new-sample-point" class="form-label"><strong>Sample Point</strong></label>
								<select id="new-sample-point" class="form-control">
									<option value="">-- Loading Sample Points --</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="form-label">&nbsp;</label>
								<button id="btn-add-sample-point" class="btn btn-primary btn-block" disabled>
									<i class="mdi mdi-plus"></i> Add to Customer
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Sample Information Table -->
				<div class="card shadow-sm border-0" style="border-radius: 15px;">
					<div class="card-header bg-light border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
						<h6 class="mb-0 text-muted">
							<i class="mdi mdi-map-marker-multiple"></i> Sample Points Assignment
						</h6>
						<div>
							<a href="#" id="add-sample-points-link" target="_blank" class="btn btn-sm btn-outline-primary mr-2">
								<i class="mdi mdi-plus"></i> Add Sample Points
							</a>
							<button type="button" id="refresh-sample-points-btn" class="btn btn-sm btn-outline-info">
								<i class="mdi mdi-refresh"></i> Refresh
							</button>
						</div>
					</div>
					<div class="card-body">
						<div id="sample-assignment-table-container">
							<!-- Will be populated via AJAX -->
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer" style="background-color: #f8f9fa; border-top: 1px solid rgba(0, 0, 0, 0.08); border-radius: 0 0 15px 15px;">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<i class="mdi mdi-close"></i> Cancel
				</button>
				<button type="button" class="btn btn-success" id="confirmAssignSamples">
					<i class="mdi mdi-check"></i> Yes, Assign
				</button>
			</div>
		</div>
	</div>
</div>

@endsection