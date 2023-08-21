@extends($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true])

@section('title2')
  <title> {{ isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch" }}</title>
	<style>
		
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
			
			// $labStores = getStorageByType("lab_store");
			$labStores = [];
			if($defaultClient){
				$customerDetails = App\Models\CRM\CRMCustomer::find($defaultClient);
				if($client_portal || Auth::user()->is_client == 1){
					$items = array(


						array(
							'link' => '/dashboard/crm/client-details',
							'name' => $customerDetails->name,
							'icon' => null
						),
						array(
							'link' => "#",
							'name' => "Customer Orders > ".(isset($batch->id) ? $batch->batch_code." - Order Info" : "Create New Order"),
							'icon' => null
						)
					);

				}else{

					$items = array(
						array(
							'link' => route('customers-list'),
							'name' => 'CRM',
							'icon' => null
						),
						array(
							'link' => route('customers-list'),
							'name' => 'Customer List',
							'icon' => null
						),
						array(
							'link' => route('show-customer', ['id'=>$defaultClient]),
							'name' => $customerDetails->name,
							'icon' => null
						),
						array(
							'link' => "#",
							'name' => "Customer Orders > ".(isset($batch->id) ? $batch->batch_code." - Order Info" : "Create New Order"),
							'icon' => null
						)
					);
				}
			}
			else{
				
				$items = array(
					array(
						'link' => route('dashboard-lab'),
						'name' => 'Dashboard',
						'icon' => null
					),
					array(
						'link' => route('sample-workflow', ['status'=>'All Samples']),
						'name' => 'Sample Workflow',
						'icon' => null
					),
					array(
						'link' => route('sample-workflow', ['status'=> $batch->status ?? 'Samples Reception']),
						'name' => $batch->status ?? 'Samples Reception',
						'icon' => null
					),
					array(
						'link' => '#',
						'name' =>  isset($batch->batch_code) ? $batch->batch_code." - Batch Info" : "New Batch",
						'icon' => null
					)
				);
			}
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
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
		
		
		<div class="btn-group float-right">
			<button type="button" class="btn btn-sm bg-white dropdown-toggle" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				Actions
			</button>
			<div class="dropdown-menu dropdown-menu-right">
				@if(isset($batch->id))
					<li>
						<a target="_blank" href="{{route('generateCustomerFocusIndex',['batch_id'=>$batch->id])}}" class="btn btn-sm dropdown-item"><i class="mdi mdi-eye mr-2"></i> View Customer Focus</a>
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

				@if(isset($batch->status) && $batch->status=="Samples In Lab" && Auth::user()->is_client == 0)
				<li>
					<span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-to-verification-modal">
					<i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Verification
					</span>
				</li>
				@if( $batch->prelim_report_status != 0)
				<li>
					<span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-for-approval-modal">
						<i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval
					</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
				</li>
				<?php $reportpath = '/storage'.$batch->batch_report_url; ?>
						<li>
							<a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download COA</a>
						</li>
				@endif						
				@endif
				@if(isset($batch->status) && in_array($batch->status, array("Samples In Lab,Sample Verification","Sample Approval")) && Auth::user()->is_client == 0 && $batch->prelim_report_status != 0)
					@if(auth()->user()->checkVerifyLabSampleRole() && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 2)
					<li>
						<span class="btn btn-sm dropdown-item"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
					</li>
					@endif
					@if(auth()->user()->checkVerifyLabSampleRole() && $batch->prelim_batch_status == "Sample Verification" && $batch->prelim_report_status == 1)
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
				@if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")) && Auth::user()->is_client == 0)
					@if($batch->status == "Sample Verification")
						@if($not_captured->count() == 0)
							@if(auth()->user()->checkVerifyLabSampleRole())
							<li>
								<span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#send-for-approval-modal">
									<i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Approval
								</span>
							</li>
							@endif
							<li>
								<span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
								
							</li>
						@else
							<li>
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
						<li>
							<a target="_blank" href="{{$reportpath}}" class="dropdown-item"><i class="mdi mdi-download mr-2"></i> Download COA</a>
						</li>
					
					@endif 
					@if($batch->status == "Sample Approval")
						@if ($batch->batch_report_url != '' && $batch->approve_user_id > 0 )
							@if($batch->is_qc_batch == 0)
								<li>
									<span class="btn btn-sm dropdown-item" data-target="#send-to-payments-modal" data-toggle="modal" title="Send for  Payment"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Send for Payment</span>
								</li>

							@endif
							
						@endif
						
						<li>
							<span class="btn btn-sm dropdown-item"  data-target="#view-coa-report" data-toggle="modal" title="View Sample(s) COA"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> View Report</span>
						</li>
						
						@if(in_array($batch->status,["Sample Approval","Reports for Collection","Reports In Payment"]))
						<li>
							<span class="btn btn-sm dropdown-item" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Process Results</span>
						</li>
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
				<span class="btn btn-default batch-info-trigger" style="box-shadow: rgba(33, 35, 38, 0.1) 0px 10px 20px -10px;">
					
					<i class="mdi {{isset($batch->id) ? 'mdi-chevron-double-down' : 'mdi-chevron-double-up' }}"></i> Batch Info
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
						<form class="dropdown-item" method="POST" style="cursor: pointer" action="{{ route('move-to-workflow', ['status'=>$item, 'batch_id'=>$batch->id]) }}">
							@csrf
							<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> {{ $item }}
						</form>
						@endforeach
					</div>
				</div>
				@endif
			</h5>
			<hr>
			<form class="{{isset($batch->id) ? 'hidden' : ''}}" action="{{ route('add-batch-info', ['batch'=>$batchID]) }}" class="row" id="batch-detail-form" method="POST" autocomplete="off">
				<?php $maxDate = getTodayDate(); ?>
				@csrf
				<div class="row p-2 border-bottom">
					<div class="form-group col-md-3">
						<label class="control-label">Date Collected <span class="text-danger">*</span></label>
						<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->date_collected ?? '' }}" class="form-control " name="date_collected" required>
						
					</div>
					<div class="form-group col-md-3">
						<label class="control-label">Lab Reception Date <span class="text-danger">*</span> </label>
						<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->receipt_date ?? '' }}" class="form-control " name="receipt_date" {{ $defaultClient === false ? 'required' : '' }} autocomplete="off">
						
					</div>
	
					<div class="form-group col-md-3 qc-omit-type-field {{isset($batch->id) ? ( $batch->status == 'Samples In Lab' || $batch->is_qc_batch == 1 ? 'hidden' : '') : ''}}">
												
						<label  class="control-label">Client <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer" data-toggle="modal" data-toggle="tooltip" title="Add Client" ><i class="mdi mdi-plus"></i></span></label>
						<select class="form-control qc-remove-required {{ $defaultClient === false ? '' :'no-select2' }} {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : '' }}" {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : '' }} name="crm_customer_id" id="client-select" onchange="detectChange(this)" {{ $defaultClient === false ? '' :'readonly' }}>
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
					<div class="form-group col-md-3">
						<label for="" class="control-label">Customer Contact</label>
						<select name="crm_contact_id" id="crm_contact_id" class="form-control">
							<option value="">Choose Customer First...</option>
						</select>
					</div>
					<div class="form-group col-md-3 qc-omit-type-field {{isset($batch->id) ? ( $batch->status == 'Samples In Lab' || $batch->is_qc_batch == 1 ? 'hidden' : '') : ''}} ">
						<label class="control-label"><span class='client-prefered-unit-name'>Site Location</span> <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm"  data-target="#add-company-unit" data-toggle="modal" data-toggle="tooltip" title="Add Site Location" ><i class="mdi mdi-plus"></i></span></label>
						<select class="form-control  {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : '' }}" {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : '' }} name="crm_unit_name" data-selected='{{ $batch->crm_unit_name ?? '' }}' id="client-unit-select">
							<option value="">Select Client Unit...</option>
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
					<div class="form-group qc-omit-type-field col-md-3">
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
					<div class="form-group btn-group-sm col-md-3">
						<label for="" class="control-label">Sampling Method</label>
						<select name="sampling_method_id" id="" class="form-control">
							<option value="">Select Sampling Method</option>
							@foreach($samplingmethods as $b_method)
							<option value="{{$b_method->id}}" {{isset($batch->id) && $batch->sampling_method_id == $b_method->id ? 'selected' : ''}}>{{$b_method->code}} - {{$b_method->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Quotation Number</label>
						<input type="text" class="form-control" data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="quote_no" value="{{ $batch->quote_no ?? '' }}" placeholder="Quotation Number..." />
						<small id="rft-message" class="text-danger"></small>
					</div>
					
					
					<div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Condition and Quality of Sample</label>
						<input type="text" class="form-control" autocomplete="off" value="{{$batch->condition_quality_sample ?? ''}}" name="submit_by" value="{{ $batch->submit_by ?? '' }}" placeholder="Condition and Quality of Sample..." />
					</div>
					<div class="form-group btn-group-sm col-md-3">
							<label class="control-label">Sampled By</label>
							<input type="text" name="sample_by" value="{{$batch->sampling_officer_name ?? '' }}" id="" placeholder="Sampled By..." class="form-control">
							
						</div>
					<div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Submitted By</label>
						<input type="text" class="form-control" autocomplete="off" value="{{$batch->submit_by ?? ''}}" name="submit_by" value="{{ $batch->submit_by ?? '' }}" placeholder="Submitted By..." />
					</div>
					<div class="form-group btn-group-sm col-md-3">
						<label class="control-label">Received By</label>
						<input type="text" name="receive_by" autocomplete="off" class="form-control" value="{{$batch->receiving_officer_name ?? ''}}" placeholder="Received By..." id="" class="form-control">
						
					</div>
					<div class="form-group btm-group-sm col-md-3">
						<label class="control-label">Invoice Amount</label>
						<input type="text" class="form-control" autocomplete="off" value="{{$batch->invoice_amount ?? ''}}" name="invoice_amount"  placeholder="Invoice Amount..." />
					</div>
				</div>
				<div class="row p-2 mt-3">
					
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="client_instruction_clear" value="1" {{ isset($batch->client_instruction_clear) && $batch->client_instruction_clear == 1 ? 'checked' : '' }}> Are client`s instructions clear ?
						</label>
					</div>
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="can_be_subcontracted" value="1" {{ isset($batch->can_be_subcontracted) && $batch->batch_subcontracted_client_approval == 1 ? 'checked' : '' }}>  If no can it be subcontracted to an approved Laboratory?
						</label>
					</div>
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="lab_capable" value="1" {{ isset($batch->lab_capable) && $batch->lab_capable == 1 ? 'checked' : '' }}>  Is the laboratory capable of performing the requested tests?
						</label>
					</div>
					
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="batch_subcontracted_client_approval" value="1" {{ isset($batch->batch_subcontracted_client_approval) && $batch->batch_subcontracted_client_approval == 1 ? 'checked' : '' }}>  Is the client willing for the sample to be subcontracted?
						</label>
					</div>
					<div class="form-group col-md-4 btn-group-sm">
						<label class="control-label">
							<input type="checkbox" name="sampled_by_company_personnel" value="1" {{ isset($batch->sampled_by_company_personnel) && $batch->sampled_by_company_personnel == 1 ? 'checked' : '' }}> Sampled by {{$active_company->name}} personnel?
						</label>
					</div>
					<div class="form-group btn-group-sm col-md-6">
						<label class="control-label">Samples Description</label>
						<textarea class="form-control" name="description" placeholder="Description...">{{ $batch->description ?? '' }}</textarea>
					</div>
					<div class="form-group btn-group-sm col-md-6">
						<label class="control-label">Special Remarks / Instructions</label>
						<textarea class="form-control" name="batch_instructions" placeholder="Batch Instructions...">{{ $batch->batch_instructions ?? '' }}</textarea>
					</div>
				</div>
					
				<div class="btn col-md-12 btn-default btn-sm text-primary btn-block toggle-more-fields mb-1">
					<i class="mdi mdi-chevron-double-down"></i> BL Fields
				</div>

				
				<div id="more-fields" class="hidden p-2">
					<div class="row p-2 bg-light m-3">
						
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">Consignee</label>
							<input type="text" class="form-control" name="radio_active_levels" value="{{ $batch->radio_active_levels ?? '' }}" placeholder="Consignee..." />
						</div>
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">Notify Part (1)</label>
							<input type="text" class="form-control" name="kra_office_ref" value="{{ $batch->kra_office_ref ?? '' }}" placeholder="Notify Part (1)..." />
						</div>
	
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">Notify Part (2)</label>
							<input type="text" class="form-control" name="kra_office_station" value="{{ $batch->kra_office_station ?? '' }}" placeholder="Notify Part (1)..." />
						</div>
						 
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">Place Sampled</label>
							<input type="text" class="form-control" name="where_sample_was_obtained" value="{{ $batch->where_sample_was_obtained ?? '' }}" placeholder="Where Sample was Obtained..." />
						</div>
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">Vessel Name</label>
							<input type="text" class="form-control" name="declared_commodity_code" value="{{ $batch->declared_commodity_code ?? '' }}" placeholder="Vessel Name..." />
						</div>
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">BL Number</label>
							<input type="text" class="form-control" name="declared_amount" value="{{ $batch->declared_amount ?? '' }}" placeholder="BL Number..." />
						</div>
						<div class="form-group col-md-3 btn-group-sm">
							<label class="control-label">Quantity</label>
							<input type="text" class="form-control" name="net_quantity_and_unit_of_quantity" value="{{ $batch->net_quantity_and_unit_of_quantity ?? '' }}" placeholder="Quantity..." />
						</div>
						<div class="form-group col-md-6 btn-group-sm">
							<label class="control-label">Shipper</label>
							<textarea class="form-control" name="importer_address" placeholder="Shipper...">{{ $batch->importer_address ?? '' }}</textarea>
						</div>
						<div class="form-group col-md-6 btn-group-sm">
							<label class="control-label">Port of Loading</label>
							<textarea class="form-control" name="how_sample_was_obtained" placeholder="Port of Loading...">{{ $batch->how_sample_was_obtained ?? '' }}</textarea>
						</div>
						<div class="form-group col-md-6 btn-group-sm">
							<label class="control-label">Port Of Discharge</label>
							<textarea class="form-control" name="sample_appearance_description" placeholder="Sample Appearance Description...">{{ $batch->sample_appearance_description ?? '' }}</textarea>
						</div>
						<div class="form-group col-md-6 btn-group-sm">
							<label class="control-label">Use of Goods</label>
							<textarea class="form-control" name="use_of_goods" placeholder="Use of Goods...">{{ $batch->use_of_goods ?? '' }}</textarea>
						</div>
					</div>
					
				</div>
					
				<div class="form-group col-md-12 text-center">
					@if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route')
					@else
						@if(isset($batch->id) && $batch->status == 'Samples In Lab')
						@else
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
		@if(isset($batch->id) && !$defaultClient)
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
			@if(isset($batch->id) && !$defaultClient)
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

							$arrT = array();

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
											<span class="badge badge-success badge-pill p-2"><i class="mdi mdi-thumb-up-outline"></i> Approved</span>
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
									<td>{{$ilabs->prelim_date}}</td>
									<td>{{$ilabs->expected_date}}</td>
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
						@if(isset($batch->status) && in_array($batch->status, array("Samples Reception","Samples En-Route")))
							@if(Auth::user()->is_client == 1 && $batch->status == 'Samples Reception')
							@else
							<input type="hidden" name="batch" value={{$batch->id}}>
							<button type="button" class="btn btn-danger btn-sm text-white ml-2 save-samples"><i class="mdi mdi-content-save"></i> Save</button> &nbsp; &nbsp;
							<span class="btn btn-success btn-sm create-new-sample-row float-right"><i class="mdi mdi-plus"></i> Add</span> &nbsp; &nbsp;
							<span class="btn btn-primary btn-sm duplicate-sample-row float-right mr-1"><i class="mdi mdi-content-duplicate"></i> Duplicate</span>
							<span class="btn btn-default btn-sm float-right mr-2" data-target="#clone-samples" data-toggle="modal" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"><i class="mdi mdi-compare-horizontal"></i> Clone Samples</span>
							
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
							<th>Disposal Date</th>
							<th>Main Standard <sup class="text-danger">*</sup></th>
							<th>Secondary Standard</th>
							
							<th>Sample Markings</th>
							{{-- <th>Storage</th> --}}
							{{-- <th>Slot</th> --}}
							{{-- <th>Quantity</th> --}}
							{{-- <th>UoM</th> --}}
							{{-- <th>Barcode</th> --}}
							
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
						data-labs = "{{json_encode($labSamples)}}"
						data-allLabs = "{{json_encode($labSamples)}}"
						>

					</tbody>
                </table>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
@section('script2')
<div class="carry_data hidden" data-userlabsection="{{json_encode($userLabSections)}}"></div>

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
								<input type="checkbox" class="form-check-input" value="1" name="active" />
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
		<div class="modal-content bg-light">
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<span class="btn btn-sm btn-outline-primary save-standard-value"><i class="mdi mdi-content-save"></i> Save</span>
				<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
			</div>
		</div>
	</div>
</div>
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
						{!! getPaymentReminderBody($customer->name,$batch_sample_codes) !!},<br>
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
						<label for="" class="control-label">Customer Contact</label>
						<select name="contact_id" id="" class="form-control">
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
@if(in_array($batch->status,['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']))
<div class="modal fade" id="view-coa-report" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('showBatchCOA')}}" method="get">
				
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
						<label for="" class="control-label">Report Template</label>
						<select name="template_id" id="" class="form-control">
							@foreach($report_formats as $r_format)
							<option value="{{$r_format->value}}">{{$r_format->key}}</option>
							@endforeach
						</select>
					</div>
					<input type="hidden" name="batch_id" value="{{$batch->id}}">
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-outline-success" type="submit"><i class="mdi mdi-cogs"></i> View</button>
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
								<input type="text"  name="title" value="Authorized Signatory" class="form-control">
							</div>
							<div class="form-group">
								<label for="" class="control-label">Approver</label>
								<select name="user_id" id="" class="form-control">
									@foreach($users as $user)
									<option value="{{$user->id}}">{{$user->name}}</option>
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
									<option value="0">Standard Report</option>
									{{-- <option value="1">KTDA Report</option> --}}
									<option value="2">Iran Report</option>
								</select>
							</div>
							<div class="proccesing-point hidden">
								<center>
									<img src="/images/load.gif" height="250px" width="auto" alt="">
								</center>
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
							
							<input type="hidden" name="status" value="Sample Verification">
							<input type="hidden" name="batch_id" value="{{$batch->id}}">
							<div class="form-group">
								<label for="" class="control-label">Report Level</label>
								<select name="level" id="" required class="form-control">
									<option value="">Choose Report Level</option>
									<option value="0">Final Report</option>
									<option value="1">Prelim Report</option>
									<option value="2">Draft Report</option>
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

<div id="show-sample-analysis-analytes" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog" style="min-width: 90%">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-snowflake"></i> Analysis Parameters </h4>
				<span class="btn btn-outline-danger btn-sm float-right" data-dismiss="modal">Close</span>
			</div>
			<div class="modal-body">
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="configured-analytes-tab" data-toggle="tab" href="#configured-analytes" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Parameters</a>
					</li>
					@if(isset($batch->status) && $batch->status == "Samples Reception" && Auth::user()->is_client == 0)
					<li class="nav-item">
						<a class="nav-link" id="add-analytes-tab" data-toggle="tab" href="#add-analytes" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-plus"></i> Add Analyte</a>
					</li>
					@endif
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
						<div class="table-responsive" id="sph-parent">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
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
											<div class="dropdown ml-2">
												
												<span class="dropdown-toggle float-right text-primary" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-filter "></i></span>
												<div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="analysis-types">
													
												</div>
											</div>
										</th>
										<th>Analyte</th>
										@if(Auth::user()->is_client == 0)
										{{-- <th nowrap>Reporting Symbol</th> --}}
										<th nowrap>Result</th>
										@endif
										@if(isset($batch->id) && $batch->repeat_sample_id > 0)
										<th>Prev Result (<small>+- {{$qc_config_perc}} %</small>)</th>
										@endif
										<th>Standard</th>
										@if(Auth::user()->is_client == 0)
										<th>Remarks</th>
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
								<tbody id="sample-parameters-holder"></tbody>
							</table>
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

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
{{-- @if(isset($batch->status)) --}}
	
		{{-- <link rel="stylesheet" href="/css/quilljs.css" /> --}}
		<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
	
{{-- @endif --}}
<script>
	tinymce.init({
		selector: 'textarea.editor'
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
						$('#client-unit-select').append('<option value="'+e.name+'">'+e.name+'</option>');
					});
	
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
			getLabSections(lab_id,(labdata)=>{
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
		$('.qc_type_id').on('change',(e)=>{
			var value = $('.qc_type_id').val();
			d= value.toString()

			console.log(d);
			
			if($('.qc_type_id').data('repeatsample') ==  value.toString()){
				$('.qc-repeat-batch').removeClass('hidden');
				// $('.qc-remove-required').removeAttr('required')
			}else{
				$('.qc-repeat-batch').addClass('hidden');
				// $('.qc-remove-required').addAttr('required')
			}
		});
		let getQcTypeConfig = (dataID,callback)=>{
			$.ajax({
				url:`/qualitycontrol/get/Qc-Type/Config/${dataID}/Ajax`,
				method:'GET',
				success:(data)=>{
					callback(data)
				},
				error:(data)=>{
					console.log(data);
				}
			})
		}
		$('.qc_type_id').on('change',(e)=>{
			var value = $('.qc_type_id').val()
			getQcTypeConfig(value,(data)=>{
				if(data['data'].use_existing_sample == 1){
					$('.qc-repeat-batch').removeClass('hidden')
					$.each(data['samples'],(i,obj)=>{
						var option = `<option value="${obj.id}" ${$batch && $batch.repeat_sample_id == obj.id ? `selected` : ``}>${obj.sample_code}</option>`
						$('#repeat_sample_id').append(option);
					})
					$('#repeat_sample_id').select2();
				}else{
					$('.qc-repeat-batch').addClass('hidden')
				}
			})
		});
		if($batch && $batch.repeat_sample_id > 0){
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
			$('#process-results-modal').find('#initiate-process').on('click',()=>{
				$('#process-results-modal').find('.proccesing-point').removeClass('hidden');
				
				$.ajax({
					url:"{{ route('process-raw-results', ['batch_id'=> isset($batch->id) ? $batch->id : 0]) }}",	
					data:{
						report_format : $('#process-results-modal').find('#report_format').val(),
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
		var analysisIDsBySampleCode = $('#sample-detail-rows').data('sample_analysis_ids');
		var analysisNames = $('#sample-detail-rows').data('analysis_names');
		$('#show-sample-analysis-analytes').on('show.bs.modal', function(e){

			var sampleCode = $(e.relatedTarget).data('sample_code');
			
			
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
			// console.log(parametersBySampleCode);
			var parameters = parametersBySampleCode[sampleCode];
			
			var analysisIDs = analysisIDsBySampleCode[sampleCode];
			
			var loop = 1;
			// console.log(parameters);
			// console.log('------------------------------')
			// console.log(parametersBySampleCode['2023L00217463']);
			// console.log(parameters);
			$.each(parameters, function(p, param){
				var sampleRow = sampleCodeParameters(param,loop);
				// console.log(param);
				$('#sample-parameters-holder').append(sampleRow);
				loop = loop + 1;
			});
			
			$(this).find('input[name="parameter_check_all"]').on('change',function(){
				
				if($(this).prop("checked") == true){
					
					$('input[name="parameter_check[]"]').map(function(){
						$(this).prop('checked',true);
					})
				}else{
					
					$('input[name="parameter_check[]"]').map(function(){
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

			$('.analytes-with-results-count').text($('#sample-parameters-holder').find('tr.has-result').length);
			$('.analytes-without-results-count').text($('#sample-parameters-holder').find('tr.no-result').length);

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
					<textarea class="form-control" name="header_body" placeholder="Comments..." required>{{ $headerDetails['header']->header_body ?? '' }}</textarea>
				</div>
				<div class="form-group">
					<label>Recommendations / Interpretations</label>
					<textarea class="form-control" name="main_body" placeholder="Recommendations / Interpretations..." >{{ $headerDetails['header']->main_body ?? '' }}</textarea>
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

			var action = $(e.relatedTarget).data('action');
			var mainBody = $(e.relatedTarget).data('mainbody');
			var headerBody = $(e.relatedTarget).data('headerbody');
			

			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);

			$('#sample-header-body-'+n).html(headerBody)
			$('#sample-main-body-'+n).html(mainBody);
			console.log(n);
			tinymce.init({
				selector: '#sample-header-body-'+n
			});

			tinymce.init({
				selector: '#sample-main-body-'+n
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
				$row.find('.provide-interpretation-row').data("scopetype", data.main_body);
			}
			$row.find('.initiate-interlab').data('sample',data.id);
			$row.find('.initiate-interlab').data('samplecode',data.sample_code);
			$row.find('.initiate-interlab').data('analysistype',data.lab_id);
			$row.find('[name="sample_details[is_duplicate][]"]').val(data.is_duplicate ? data.is_duplicate : 0)
			console.log('-------------------------------');
			console.log(data)
			console.log('-------------------------------')


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
					<option value="${s.id}" ${ s.id === data['main_standard'] ? 'selected' : '' }>${s.code}</option>
				`);
			});
			$row.find('[name="sample_details[secondary_standard][]"]').html('<option></option>');
			$.each(standards,function(j,s){
				$row.find('[name="sample_details[secondary_standard][]"]').append(`
					<option value="${s.id}" ${ s.id === data['secondary_standard'] ? 'selected' : '' }>${s.code}</option>
				`);
			});
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
				
				// values = ['lab 1','lab 2']

				textHolder.text(values.join(','));
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
		$('.is_qc_batch').on('change',(e)=>{
			if($(e.currentTarget).is(':checked')){
				$('.qc-type-field').removeClass('hidden')
				$('.qc-omit-type-field').addClass('hidden');
			}else{
				$('.qc-type-field').addClass('hidden')
				$('.qc-omit-type-field').removeClass('hidden');
			}
			
		});
		var editStandardModal = (data)=>{
			var body = $(`
				<form action="" class="bg-white p-3 before-save">
					
					<div class="alert alert-primary d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px"></i>
						<span class="p-2">Change <b class="analyte_name"></b> Standard Limits by updating the information below  <br>
						<span class="text-danger">By changing the standard the system will automatically clear the current result</span></span>
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
				</form>
				<div class="after-save p-3 bg-white hidden">
					<center>
						<img src="/images/suc.gif" width="30%" height="50%" alt="">
					</center>
				</div>
			`).clone();
			return body;

		}
		$('#edit-standard').on('show.bs.modal',(e)=>{
			$body = editStandardModal()
			$('#edit-standard').find('.modal-body').empty();
			$('#edit-standard').find('.modal-body').append($body);


			$('#edit-standard').find('.analyte_name_field').val($(e.relatedTarget).data('analytename'));
			$('#edit-standard').find('.analyte_name').empty();
			$('#edit-standard').find('.analyte_name').append($(e.relatedTarget).data('analytename'));
			$('#edit-standard').find('.before-save').removeClass('hidden');
			$('#edit-standard').find('.after-save').addClass('hidden');
			$('#edit-standard').find('.save-standard-value').removeClass('hidden');

			var analyte_id = $(e.relatedTarget).data('analyte')
			var standard = $(e.relatedTarget).data('standard');
			var parentTD = $(e.relatedTarget.parentNode.parentNode);
			var parentTR = $(e.relatedTarget.parentNode.parentNode.parentNode);

		
			$('#edit-standard').find('.standard_value_field').val($(e.relatedTarget).data('standardvalue'));
			var is_value_id = 0;
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
			$('#edit-standard').find('.is_standard_value').on('change',(e)=>{
				var value = $(e.currentTarget).val();
				selectedValue = $(e.currentTarget).val();
				
				if(value == 1){
					$('#edit-standard').find('.is-range').removeClass('hidden');
					$('#edit-standard').find('.is-value').addClass('hidden');
				}else{
					$('#edit-standard').find('.is-range').addClass('hidden');
					$('#edit-standard').find('.is-value').removeClass('hidden');
				}
			});
			$('#edit-standard').find('.standard_valuetype').on('change',(e)=>{
				if($('#edit-standard').find('.standard_valuetype').val() == is_value_id){
					$('#edit-standard').find('.is-value-type').removeClass('hidden');
				}else{
					$('#edit-standard').find('.is-value-type').addClass('hidden');
				}
				
			})
			$('#edit-standard').find('.save-standard-value').on('click',()=>{
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
						$('#edit-standard').find('.before-save').addClass('hidden');
						$('#edit-standard').find('.after-save').removeClass('hidden');
						$('#edit-standard').find('.save-standard-value').addClass('hidden');
						

						$(parentTD).find('.main-value-field').val(data['format_value']);
						$(parentTD).find('.standard-value-field').val(data['value']);
						$(parentTR).find('.first-result').val('');
						
					},
					error:(data)=>{
						console.log(data);
					}
				})

			});

		})

	});

	var sampleCodeParameters = function(data,loop){
		
		var readonly = '';
		$('.main-standard-name').text(data.main_standard === undefined ? '' : (data.main_standard === null ? '' : data.main_standard));
		// console.log(data);
		// ${readonly} ${ {{ Auth::user()->id }} != (data.def_operator ? data.def_operator.id : 0) ? 'readonly' : '' } //results validator by user
		@if(isset($batch->status) && $batch->status != "Samples In Lab")
			readonly = 'disabled';
		@endif
		

	
		var $oGRow = $(`
			<tr class="raw-data-row ${data.result == null ? 'no-result' : 'has-result'} ${!userLabSection.includes(data.lab_section_id) && thebatch.status == 'Samples In Lab' ? 'hiddens' : ''}" id="row-${loop}" >
				@if(isset($batch->status) && $batch->status != 'Samples In Lab' && Auth::user()->is_client == 0)
				<td class="" style="display:flex !important">
				<input type="checkbox" name="parameter_check[]" class="mr-3" value="${data.id}" id="parameter-check">
				<span class="btn btn-sm btn-default remove-analyte-row" data-toggle="tooltip" data-placement="bottom" title="delete"><i class="mdi mdi-trash-can-outline text-danger"></i></span>
				</td>
				@else
				<td></td>
				@endif
				<td  nowrap>${data.sample_detail_code}</td>
				<td  nowrap>${data.analysis_type.code}</td>
				<td  nowrap><input type="hidden" name="captured_result_id[]" value="${data.id}">${data.analyte_name}</td>
				@if(Auth::user()->is_client == 0)
				<td nowrap class="hidden" ><input type="text" {{isset($batch->status) && $batch->status != 'Samples In Lab' ? 'disabled' : ''}} name="result_reporting_symbol[${data.id}]" id="reporting-symbol" placeholder="Reporting Symbol..." value="${data.result_reporting_symbol == null ? '' :data.result_reporting_symbol }" ></td>
				<td>
					<div class="form-group">
						<input {{isset($batch->status) && $batch->status != 'Samples In Lab' ? 'disabled' : ''}} id="${data.sample_detail_code},${data.analyte_code},${data.id},${data.analyte_id}" style="min-width: 150px" type="text"
						class="form-control ${data.remark_is_manual == 0 ? 'first-result' : ''}"  id="result-${loop}" value="${data.result == null ? '' : data.result}" name="result[${data.id}]" placeholder="Result..." />
						<input type="hidden" name="result_confirm"  />
					</div>
				</td>
				@endif
				@if($batch && $batch->is_qc_batch == 1 && $batch->repeat_sample_id > 0)
				<td style="width:150px !important">
				<input type="text" class="form-control" style="width:150px" name="repeat_sample[${data.id}]" value="${data.repeatsampleresult}" disabled />
				</td>
				@endif
				<td nowrap class="">
					<div class="d-flex">
						<input type="text" class="form-control main-value-field" style="width:100px;border:0" name="main_value[${data.id}]" value=" ${data.standard_limit_value != '' ? data.standard_limit_value : ''} ${data.standard_value == null ? '-': data.standard_value}" disabled />
						<span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#edit-standard" data-standard="${data.main_standard}" data-analyte="${data.analyte_id}" data-analytename="${data.analyte_code}" data-standardvalue="${data.standard_value}"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit Standard"></i></span>	
					</div>
					
							
				<input type="hidden" class="standard-value-field" name="main_value[${data.id}]" value="${data.standard_value}"/>
				<input type="hidden" name="main_standard[${data.id}]" value="${data.main_standard}"/>
				<input type="hidden" name="secondary_standard[${data.id}]" value="${data.secondary_standard}"/>
				</td>
				@if(Auth::user()->is_client == 0)
				<td nowrap>
					<input id="${data.sample_detail_code}-${data.id}" style="min-width: 150px" type="text" 
					class="form-control disabled ${data.remark_is_manual == 0 ? 'first-remark' : 'hidden'}" readonly="true" value="${data.remark ?? ''}" name="remark[${data.id}]" placeholder="Remark..." />
					<div class="form-group is-manual ${data.remark_is_manual == 0 ? "hidden" : ""}">
						<select name="remarkmanual[${data.id}]" id="" class="form-control remarkmanual">
							<option value="PASS" ${data.remark == 'PASS' ? 'selected' : ''}>Pass</option>
							<option value="FAIL" ${data.remark == 'FAIL' ? 'selected' : ''}>Fail</option>
						</select>
					</div>
				</td>
				<td>
					<div class="form-group" name="operators" placeholder="Select Operator...">
						<select style="min-width: 150px" class="form-control item-operators"  name="operators[${data.id}]" placeholder="Select Operator..." data-selected="${data.def_operator ? data.def_operator.id : 0 }"></select>
					</div>
				</td>
				@endif
				<td  nowrap>
				<div class="form-group"  placeholder="Select Method...">
						<select style="min-width: 150px" class="form-control method-id"  name="method_id[${data.id}]" placeholder="Select Method..." data-selected="${data.method_id ? data.method_id : 0 }"></select>
					</div>
				</td>
				@if(Auth::user()->is_client == 0)
				<td  nowrap>${data.equip_name}</td>
				@endif
				<td class="text-center" nowrap>
				<div class="form-group">
				<input class="form-check" type="checkbox" {{Auth::user()->is_client == 1 ? 'disabled' : ''}}  name="subcontracted[${data.id}]" ${data.analyte_status_contracted  == 1 ? 'checked':''}/>
				</div>
				</td>
				<td class="text-small text-center">
				<div class="form-group">
				<input class="form-check" type="checkbox" {{Auth::user()->is_client == 1 ? 'disabled' : ''}}  name="accredited[${data.id}]" ${data.analyte_accredited  == 1  ? 'checked':''}/>
				</div>
				</td>
			</tr>
		`);

		var $row = $oGRow.clone();
		
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
		var methods = data.methods;
		
		if(methods != '' ){
			$row.find('select.method-id').empty();
			$.each(methods,function(r,t){
				$row.find('select.method-id').append(`<option value="${t}" ${t == selectedMethod ? `selected` : `` }>${r}</option>`)
			})

		}else{
			methods = $('#sample-detail-rows').data('methods');
			$.each(methods,function(r,t){
				$row.find('select.method-id').append(`<option value="${t.id}" ${t.id == selectedMethod ? `selected` : `` }>${t.name}</option>`)
			})

		}
		
		
		$row.find('select.item-operators').empty();
		var OPS = $('#operators-list').data('analysts');
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
	

	var sampleDetailsRow = `<tr class="editable">
		<td>
		<input type="checkbox" class="select-row-check mt-1" /></td>
		<td class="block toolbar" nowrap>
			@if(isset($batch->status) && in_array($batch->status, array("Samples En-Route" ,"Samples Reception")))
				<span class="btn edit-remove btn-default text-primary btn-sm no-data hidden toggle-row-edit-mode" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></span> &nbsp;
				<span class="btn btn-default delete-remove text-danger btn-sm delete-row" data-toggle="tooltip" title="Delete"><i class="mdi mdi-trash-can"></i></span>
			@endif
			@if(isset($batch->status) && in_array($batch->status, array("Sample Approval","Samples In Lab","Sample Verification")))
				<span class="btn btn-default interpretation-remove  no-data hidden  text-success btn-sm provide-interpretation-row" data-target="#provide-interpretations" data-toggle="modal"  data-toggle="tooltip" title="Comments and Interpretation"><i class="mdi mdi-android-messages"></i></span>
			@endif
			@if(isset($batch->status) && in_array($batch->status, array("Samples En-Route","Samples Request Review","Samples Reception","Samples In Lab")))
			<span class="btn btn-default btn-sm initiate-interlab  no-data hidden  text-warning" data-sample="" data-analysistype="" data-samplecode="" data-toggle="modal" data-target="#inter-lab-add" data-action="add"><i class="mdi mdi-swap-horizontal-bold" data-toggle="tooltip" title="Initiate inter Lab"></i></span>
			@endif
			<span class="btn btn-default parameter-remove  no-data hidden  text-info btn-sm dropdown-row" data-target="#show-sample-analysis-analytes" data-toggle="modal" data-toggle="tooltip" title="Parameters" ><i class="mdi mdi-snowflake"></i></span>
		</td>
		<td class="sample-code-field" nowrap>
			<div class="form-group form-group-sm">
				<input type="text" style="width: 70px" class="form-control form-control-sm sample-code" name="sample_details[sample_code][]" readonly="true" />
			</div>
			<span class="text"></span>
		</td>
		<td class="analysis-field" nowrap>
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required sample-analysis" multiple style="width: 200px" placeholder="Select Analysis...">
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
				<select class="form-control form-control-sm  is-required sample-lab" name="sample_details[lab_id][]" style="width: 200px" placeholder="Select..." required></select>
			</div>
			<input type="hidden" name="sample_details[is_duplicate][]" value="" class="">
			<span class="text"></span>
		</td>
		<td class="sample-condition-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required sample-condition" name="sample_details[sample_condition][]" style="width: 200px" placeholder="Select Sample Condition..." required>
					@foreach($conditions as $con)
						<option value="{{ $con->id }}">{{ $con->name }}</option>
					@endforeach
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-sample_point-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm  is-required sample-point" name="sample_details[sample_point][]" style="width: 200px" placeholder="Select..." required></select>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-product-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required sample-product" name="sample_details[product][]" style="width: 200px" placeholder="Select..." required>
					@foreach($products as $product)
					<option value="{{$product->id}}">{{$product->name}}</option>
					@endforeach
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-code-field" nowrap>
			<div class="form-group form-group-sm">
				<input type="date" style="width: 200px" class="form-control form-control-sm disposal-date" value={{$disposal_date}} name="sample_details[disposal_date][]"/>
			</div>
			<span class="text"></span>
		</td>
		<td class ="main-standard-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm is-required main-standard" name="sample_details[main_standard][]" style"width:200px" placeholder="Select Main Standard..." required >
				@if($standards)
					@foreach($standards as $standard)
					<option value="{{$standard->id}}">{{$standard->code}}</option>
					@endforeach
				@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		<td class ="secondary-standard-field">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm secondary-standard" name="sample_details[secondary_standard][]" style"width:200px" placeholder="Select Sec Standard...">
				@if($standards)
					@foreach($standards as $standard)
					<option value="{{$standard->id}}">{{$standard->code}}</option>
					@endforeach
				@endif
				</select>
			</div>
			<span class="text"></span>
		</td>
		
		<td class="comments-field" nowrap>
			<div class="form-group form-group-sm">
				<textarea rows="1" style="width: 300px" class="form-control form-control-sm sample-comments" name="sample_details[comments][]" placeholder="Sample Comments..."></textarea>
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-store-field hidden" nowrap>
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm sample-store" name="sample_details[sample_store][]" style="width: 200px" placeholder="Select Sample Storage...">
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
		<td class="sample-slot-field hidden" nowrap>
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm sample-store-slot" name="sample_details[sample_store_slot][]" style="width: 200px !important" placeholder="Select a Srore First...">
					<option></option>
				</select>
			</div>
			<span class="text"></span>
		</td>
		
		<td class="sample-quantity-field hidden">
			<div class="form-group form-group-sm">
				<input type="number" min="0" style="width: 200px !important" class="form-control form-control-sm sample-quantity"  name="sample_details[sample_quantity][]" placeholder="Sample Quantity..." />
			</div>
			<span class="text"></span>
		</td>
		<td class="sample-reporting-unit-field hidden">
			<div class="form-group form-group-sm">
				<select class="form-control form-control-sm sample-reporting-unit"  name="sample_details[sample_reporting_unit][]" style="width: 200px !important" placeholder="Select Sample Reporting Unit...">
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
		<td class="barcode-field hidden">
			<div class="form-group form-group-sm">
				<input type="text" style="width: 100px" class="form-control form-control-sm sample-barcode" name="sample_details[barcode][]" placeholder="BarCode..." />
			</div>
			<span class="text"></span>
		</td>
		

	</tr>`;

	
</script>






@endsection