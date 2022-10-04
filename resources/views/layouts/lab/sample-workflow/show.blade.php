
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

	</style>
@endsection
@section('content2')
  <main>
		<?php
			$show_ = isset($batch->id) ? $batch->processed_results()->count() : 0;
			$equipment_data = isset($batch->id) ? $batch->get_captured_tally() : array();

			$headerDetails = isset($batch->id) ? $batch->report_header_details() : array();

			$labStores = getStorageByType("lab_store");

			$reportingUnits = getReportingUnits();

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
						'link' => route('sample-workflow', ['status'=>$batch->status ?? 'Samples Reception']),
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
			@if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")))
				@if($batch->status == "Sample Verification")
				<i class="mdi mdi-layers-triple"></i> <span class="badge badge-pill bg-white pt-2 pb-2 pr-3 pl-3" style="font-weight: 400!important">{!! isset($batch->priority) && $batch->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $batch->priority ?? '' }}</span>
						{{ isset($batch->batch_code) ? $batch->batch_code.' Batch Info' : 'New Batch' }} <small class="text-muted">{!! sizeof($not_captured) > 0 ? '<span style="font-size: 11px;" class="badge badge-pill bg-white text-danger p-2"><i class="mdi mdi-alert-decagram"></i> Data Partially Captured</span>' : '<span style="font-size: 11px;" class="badge badge-pill bg-white text-success p-2"><i class="mdi mdi-alert-decagram"></i> Data Fully Captured</span>' !!}</small>
				@else
					<i class="mdi mdi-layers-triple"></i> <span class="badge badge-pill bg-white pt-2 pb-2 pr-3 pl-3" style="font-weight: 400!important">{!! isset($batch->priority) && $batch->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $batch->priority ?? '' }}</span>
						{{ isset($batch->batch_code) ? $batch->batch_code.' Batch Info' : 'New Batch' }} <small class=""> {!! $batch->verify_user_id > 0 && $batch->verify_user_id != '' ? '<span style="font-size: 11px;" class="badge badge-pill bg-white text-success p-2"><i class="mdi mdi-checkbox-multiple-marked-circle"></i> Verified</span>' : '' !!}  {!! $batch->approve_user_id > 0 && $batch->approve_user_id != '' ? '<span style="font-size: 11px;" class="badge badge-pill bg-white p-2 text-success"><i class="mdi mdi-account-check"></i> Approved</span>' : '' !!}</small>
				@endif
			@else
				<i class="mdi mdi-layers-triple"></i> <span class="badge badge-pill bg-white pt-2 pb-2 pr-3 pl-3" style="font-weight: 400!important">{!! isset($batch->priority) && $batch->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $batch->priority ?? '' }}</span>
				{{ isset($batch->batch_code) ? $batch->batch_code.' Batch Info' : 'New Batch' }} <small class="text-muted"> {!! isset($batch->batch_code) ? '<i class="mdi mdi-sitemap"></i> '.$batch->tracking_stage()->name : '' !!}</small>
			@endif
			@if(isset($batch->status) && $batch->status=="Samples Request Review")
				<!-- <button class="btn btn-outline-primary btn-sm float-right"  data-target="#dispatch-to-labs-modal" data-toggle="modal" title="Approve Request"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request</button> -->
			@endif
			@if(isset($batch->status) && $batch->status=="Samples In Lab" && Auth::user()->is_client == 0)
				{{-- @if($equipment_data['captured'] > 0) --}}
					<button class="btn btn-outline-danger btn-sm float-right" data-target="#send-to-verification-modal" data-toggle="modal" title="Send To Verification"><i class="mdi mdi-check-decagram"></i> Send To Verification</button>
				{{-- @else
					<button class="btn btn-outline-dark btn-sm float-right" disabled><i class="mdi mdi-check-decagram"></i> Send To Verification</button>
				@endif --}}
			@endif
			@if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")) && Auth::user()->is_client == 0)
				@if($batch->status == "Sample Verification")
					@if($batch->processed_results()->count() > 0)
						<button class="btn btn-outline-primary btn-sm float-right ml-1" data-target="#send-for-approval-modal" data-toggle="modal" title="Send for Approval"><i class="mdi mdi-check-decagram"></i> Send for Approval</button>
						<?php
						$path = '/storage'.$batch->batch_report_url;
							?>
						<a href="{{$path}}" target="_blank" class="float-right ml-1  btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
					@else
					<?php
						$path = '/storage'.$batch->batch_report_url;
							?>
					<a href="{{$path}}" target="_blank" class="float-right ml-1  btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
						<button class="btn btn-outline-primary btn-sm float-right ml-1" disabled ><i class="mdi mdi-check-decagram"></i> Send for Approval</button>
					@endif
					<button class="btn btn-outline-success btn-sm float-right"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i> Process Results</button>
				@endif
				@if($batch->status == "Sample Approval")
					@if ($batch->batch_report_url != '' && $batch->approve_user_id > 0 )
						

						<button class="btn btn-outline-primary btn-sm float-right ml-1" data-target="#send-to-payments-modal" data-toggle="modal" title="Send for  Payment"><i class="mdi mdi-credit-card-outline"></i> Send for Payment</button>
						<?php
							$path = '/storage'.$batch->batch_report_url;
						?>
						<a href="{{$path}}" target="_blank" class="float-right ml-1 btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
					@else
						
						<button class="btn btn-outline-dark btn-sm float-right ml-1" data-target="#prompt-report-modal" data-toggle="modal" title="Send for Payment"><i class="mdi mdi-credit-card-outline"></i> Send for Payment</button>
					@endif
					@if($batch->approve_user_id <= 0 && $batch->verify_user_id != auth()->user()->id)
					<a href="{{ route('approve-batch-analysis',['id'=>$batch->id]) }}" class="btn btn-info btn-sm float-right ml-1" ><i class="mdi mdi-check-circle"></i> Approve</a>
					<button class="btn btn-outline-success btn-sm float-right" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i> Process Results</button>

					 @else
					<button disabled class="btn btn-info btn-sm float-right ml-1" ><i class="mdi mdi-check-circle"></i> Approve</button>
					<button class="btn btn-success btn-sm float-right" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i> Process Results</button>
					@endif

				@endif
				@if(isset($batch->status) && $batch->status == 'Reports In Payment')
				<button class="btn btn-primary btn-sm float-right ml-1" data-target="#send-to-email-modal" data-toggle="modal" title="Send for Collection"><i class="mdi mdi-email"></i> Send for Collection</button>
				@endif
				@if($batch->status == 'Reports In Payment' || $batch->status == 'Reports for Collection')
				<?php 
					$path = '/storage'.$batch->batch_report_url;
				?>
				<a href="{{$path}}" target="_blank" class="float-right ml-1 btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
				@endif
				
				
				
				<!-- <button class="btn btn-outline-success btn-sm float-right" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i>  $batch->status == "Sample Verification" ? "Process Results" : "" }}</button> -->
				<!-- <a href="{{ route('certificate-analysis',['id'=>$batch->id]) }}" class="btn btn-outline-warning btn-sm mr-1 float-right"> Certificate of Analysis</a> -->
				@endif


			@if(isset($batch->id) && !$defaultClient)
				<div class="btn-group mt-2">
					<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="mdi mdi-swap-vertical"></i> Move To Workflow
					</button>
					<div class="dropdown-menu" id="status-selector">
						@foreach (getSampleWorflowStages() as $item)
							<form class="dropdown-item" method="POST" style="cursor: pointer" action="{{ route('move-to-workflow', ['status'=>$item, 'batch_id'=>$batch->id]) }}">
								@csrf
								<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> {{ $item }}
							</form>
						@endforeach
					</div>
				</div>
				<div class="btn-group mt-2">
					<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="mdi mdi-swap-vertical"></i> Move To Stage
					</button>
					<div class="dropdown-menu" id="stage-selector">
						@foreach (getWorkflowStage_Stages($batch->status) as $item)
							<form class="dropdown-item" method="POST" style="cursor: pointer" action="{{ route('move-to-stage', ['stage'=>$item->id, 'batch_id'=>$batch->id]) }}">
								@csrf
								<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> {{ $item->name }}
							</form>
						@endforeach
					</div>
				</div>
			@endif

		</h4>
		<div class="pl-2 pr-2 pb-3 row">
			@if($batch->specialist_analyst ?? '')
				<div class="col-sm-4" style="font-size: 16px">
					<span class="badge bg-white badge-pill p-2" style="margin-right: 5px">
						<i class="mdi mdi-account"></i> SPECIALIST ANALYST
					</span> {{ $batch->specialist_analyst->name }}
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
				<div class="col-sm-8" style="font-size: 14px">
					<span class="badge bg-white badge-pill p-2" style="font-size: 13px; margin-right: 5px">
						<i class="mdi mdi-beaker-question"></i> REQUEST TYPE
					</span> {{ implode(',', $arrT) }}
				</div>
			@endif
		</div>
    <div class="row no-gutters">
      <div class="col-sm-4 p-2">
        <div class="card">
          <div class="card-body">
						<h5 class="card-title">
							<i class="mdi mdi-pencil-outline"></i> Batch Info
							@if(isset($batch->report_file_path) && trim($batch->report_file_path) != "")
								<a class="float-right btn btn-outline-danger btn-sm rounded-pill pl-3 pr-3" href="{{ $batch->report_file_path }}">
									<i class="mdi mdi-download"></i> View Report
								</a>
							@endif
						</h5>
						<hr>
						<form action="{{ route('add-batch-info', ['batch'=>$batchID]) }}" method="POST">
							<?php $maxDate = getTodayDate(); ?>
							@csrf
							
							<div class="form-group">
								<label class="control-label">Date Collected <span class="text-danger">*</span></label>
								<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->date_collected ?? '' }}" class="form-control " name="date_collected" required>
								{{-- <div class="datepicker date input-group">
									<input type="text" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->date_collected ?? '' }}" class="form-control " name="date_collected" required>
									<div class="input-group-append">
										<span class="input-group-text"><i class="mdi mdi-clock"></i></span>
									</div>
								</div> --}}
							</div>
							<div class="form-group">
								<label class="control-label">Lab Reception Date <span class="text-danger">*</span> </label>
								<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->receipt_date ?? '' }}" class="form-control " name="receipt_date" {{ $defaultClient === false ? 'required' : '' }} autocomplete="off">
								{{-- <div class="datepicker date input-group">
									<input type="text" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->receipt_date ?? '' }}" class="form-control " name="receipt_date" required autocomplete="off">
									<div class="input-group-append">
										<span class="input-group-text"><i class="mdi mdi-clock"></i></span>
									</div>
								</div> --}}
							</div>

							<div class="form-group {{isset($batch->id) ? ( $batch->status == 'Samples In Lab' ? 'hidden' : '') : ''}}">
														
								<label  class="control-label">Client <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer" data-toggle="modal" data-toggle="tooltip" title="Add Client" ><i class="mdi mdi-plus"></i></span></label>
								<select class="form-control {{ $defaultClient === false ? '' :'no-select2' }} {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : '' }}" {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : '' }} name="crm_customer_id" id="client-select" required onchange="detectChange(this)" {{ $defaultClient === false ? '' :'readonly' }}>
									<option value="">Select Client...</option>
									@foreach (getClients() as $client)
										 @if($defaultClient === false) {{-- Creating a batch from the normal process --}}
											<option value="{{ $client->id }}"  {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }} {{-- When coming from laboratory --}} {{ $defaultClient == $client->id ? 'selected' : '' }} {{-- When coming from client order --}}
												data-units="{{ json_encode($client->units) }}"
												data-quote="{{json_encode($client->quotes)}}"
												data-unit_name='{{ trim($client->unit_configurable_name) == '' ? 'Site Location' : $client->unit_configurable_name }}'
												data-sample_point_name='{{ trim($client->sample_point_configurable_name)  == '' ? 'Sample Point' : $client->sample_point_configurable_name }}'
												data-product_name='{{ trim($client->product_configurable_name) == '' ? 'Product' : $client->product_configurable_name }}'>{{ $client->name }}</option>
										@endif

										@if($defaultClient !== false && $client->id == $defaultClient){{-- Creating a batch from the client order --}}
											<option value="{{ $client->id }}"  {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }} {{-- When coming from laboratory --}} {{ $defaultClient == $client->id ? 'selected' : '' }} {{-- When coming from client order --}}
												data-units="{{ json_encode($client->units) }}"
												data-unit_name='{{ trim($client->unit_configurable_name) == '' ? 'Unit' : $client->unit_configurable_name }}'
												data-sample_point_name='{{ trim($client->sample_point_configurable_name)  == '' ? 'Sample Point' : $client->sample_point_configurable_name }}'
												data-product_name='{{ trim($client->product_configurable_name) == '' ? 'Product' : $client->product_configurable_name }}'>{{ $client->name }}</option>
										@endif
									@endforeach
								</select>
								@if($defaultClient !== false)
									<input type="hidden" name="is_client_order" value="1" />
								@endif
								
							</div>
							<div class="form-group">
								<label class="control-label"><span class='client-prefered-unit-name'>Site Location</span> <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm"  data-target="#add-company-unit" data-toggle="modal" data-toggle="tooltip" title="Add Site Location" ><i class="mdi mdi-plus"></i></span></label>
								<select class="form-control {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : '' }}" {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : '' }} name="crm_unit_name" data-selected='{{ $batch->crm_unit_name ?? '' }}' id="client-unit-select" required>
									<option value="">Select Client Unit...</option>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label text-sm">Sample Type <span class="text-danger">*</span></label>
								<select class="form-control {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : '' }}" {{ isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : '' }} name="sample_type_id" required id="batch-info-sample-type">
									<option value="">Select Sample Type...</option>
									@foreach (getSampleTypes() as $sample)
										<option value="{{ $sample->id }}"  {{ isset($batch->sample_type_id) && $batch->sample_type_id == $sample->id ? 'selected' : '' }} data-conditions="{{ json_encode($sample->sample_condition) }}">{{ $sample->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">RFT Form No <span class="text-danger">*(unique)</span></label>
								<input type="text" class="form-control" data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="reference_number" value="{{ $batch->reference_number ?? '' }}" placeholder="Reference Number..." required />
								<small id="rft-message" class="text-danger"></small>
							</div>
							<div class="form-group">
								<label class="control-label">Batch Scope <span class="text-danger">*</span></label>
								<select name="batch_scope" id="" class="form-control" required>
									@foreach(explode(',',$batch_scope->value) as $scope)
									<option value="{{$scope}}" {{ isset($batch->id) && $batch->batch_scope == $scope ? 'selected' : '' }}>{{$scope}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Customer Survey <span class="text-danger">*</span></label>
								<select name="customer_survey" id="" class="form-control" required>
									@foreach(explode(',',$customer_survey->value) as $survey)
									<option value="{{$survey}}" {{ isset($batch->id) && $batch->customer_survey == $survey ? 'selected' : '' }}>{{$survey}}</option>
									@endforeach
								</select>
							</div>
							<!-- <div class="form-group btn-group-sm">
								<label class="control-label">Ammendment Number</label>
								<input type="text" class="form-control" name="document_number" value="{{ $batch->document_number ?? '' }}" placeholder="Ammendment Number..." />
							</div> -->
							@if(isset($batch->id))
							<div class="form-group clients-data">
								<label class="control-label">Quotation</label>

								<select name="quote_id" id="select-quotation" class="form-control">
									<option value="">Select Quote</option>
									<?php $quotes = getCustomerQuote($batch->crm_customer_id)?>
									@foreach($quotes as $quote)
									<option value="{{$quote->id}}" {{$quote->id == $batch->quote_id ? 'selected':''}}>{{$quote->quote_number}}</option>
									@endforeach
								</select>

							</div>
							@endif
							<div class="form-group btn-group-sm">
								<label class="control-label">Samples Description</label>
								<textarea class="form-control" name="description" placeholder="Description...">{{ $batch->description ?? '' }}</textarea>
							</div>

							<div class="form-group btn-group-sm">
									<label class="control-label">Sampled By</label>
									<input type="text" name="sample_by" value="{{$batch->sampling_officer_name ?? '' }}" id="" placeholder="Sampled By..." class="form-control">
									<!-- <select name="sampling_officer" class="form-control" placeholder="Select Sampling Officer...">
										<option></option>
										@foreach (getUsers() as $item)
											<option value="{{ $item->id }}" {{ isset($item->id) && $item->id == ($batch->sampling_officer ?? 0) ? 'selected' : '' }}>{{ $item->name }}</option>
										@endforeach
									</select> -->
								</div>
								<div class="form-group btn-group-sm">
								<label class="control-label">Submitted By</label>
								<input type="text" class="form-control" value="{{$batch->submit_by ?? ''}}" name="submit_by" value="{{ $batch->submit_by ?? '' }}" placeholder="Submitted By..." />
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">Received By</label>
								<input type="text" name="receive_by" class="form-control" value="{{$batch->receiving_officer_name ?? ''}}" placeholder="Received By..." id="" class="form-control">
								<!-- <select name="receiving_officer" class="form-control" placeholder="Select Receiving Officer...">
									<option></option>
									@foreach (getUsers() as $item)
										<option value="{{ $item->id }}" {{ isset($item->id) && $item->id == ($batch->receiving_officer ?? 0) ? 'selected' : '' }}>{{ $item->name }}</option>
									@endforeach
								</select> -->
							</div>


							
							<div class="form-group btn-group-sm">
								<label class="control-label"><?php $active_company = getActiveCompany()?>
									<input type="checkbox" name="agreement" value="1" {{ isset($batch->user_agreement) && $batch->user_agreement == 1 ? 'checked' : '' }}> I agree to {{$active_company->name}} terms and conditions?
								</label>
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label"><?php $active_company = getActiveCompany()?>
									<input type="checkbox" name="sampled_by_company_personnel" value="1" {{ isset($batch->sampled_by_company_personnel) && $batch->sampled_by_company_personnel == 1 ? 'checked' : '' }}> Sampled by {{$active_company->name}} personnel?
								</label>
							</div>
							
								<!-- <div class="form-group btn-group-sm">
								<label class="control-label">
									<input type="checkbox" name="is_ammendment" value="1" {{ isset($batch->is_ammendment) && $batch->is_ammendment > 0 ? 'checked' : '' }}> Is Ammendment?
								</label>
							</div> -->
							<div class="btn btn-default btn-sm text-primary btn-block toggle-more-fields mb-1">
								<i class="mdi mdi-chevron-double-down"></i> More Fields
							</div>
							<div id="more-fields" class="hidden">
							<div class="form-group btn-group-sm">
								<label class="control-label">
									<input type="checkbox" name="is_routine" value="1" {{ isset($batch->is_routine) && $batch->is_routine == 1 ? 'checked' : '' }}> Is Routine?
								</label>
							</div>
							<div class="form-group btn-group-sm {{ isset($batch->is_routine) && $batch->is_routine == 1 ? '' : 'hidden' }}" id="routine_frequency">
									<label class="control-label">Routine Frequency <small class="text-danger">*required for routine sample</small></label>
									<select name="routine_frequency" class="form-control">
										<option value="">Select Frequency</option>
										@foreach (getRoutineFrequency() as $k=>$item)
											<option value="{{ $k }}" {{ isset($batch->routine_frequency) && $batch->routine_frequency == $k ? 'selected' : '' }}>{{ $item }}</option>
										@endforeach
									</select>
								</div>
							<div class="form-group">
								<label class="control-label">Date Expected</label>
								<input type="date" placeholder="Date Expected" value="{{ $batch->date_expected ?? '' }}" class="form-control " name="date_expected">
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">Customer Address</label>
								<textarea class="form-control" name="importer_address" placeholder="Customer Address...">{{ $batch->importer_address ?? '' }}</textarea>
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">Radioactive Level</label>
								<input type="text" class="form-control" name="radio_active_levels" value="{{ $batch->radio_active_levels ?? '' }}" placeholder="Radioactive Level..." />
							</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Office REF</label>
									<input type="text" class="form-control" name="kra_office_ref" value="{{ $batch->kra_office_ref ?? '' }}" placeholder="Office REF..." />
								</div>

								<div class="form-group btn-group-sm">
									<label class="control-label">Office/Station</label>
									<input type="text" class="form-control" name="kra_office_station" value="{{ $batch->kra_office_station ?? '' }}" placeholder="Office/Station..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">How Sample was Obtained</label>
									<textarea class="form-control" name="how_sample_was_obtained" placeholder="How Sample was Obtained...">{{ $batch->how_sample_was_obtained ?? '' }}</textarea>
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Where Sample was Obtained</label>
									<input type="text" class="form-control" name="where_sample_was_obtained" value="{{ $batch->where_sample_was_obtained ?? '' }}" placeholder="Where Sample was Obtained..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Declared Commodity Code</label>
									<input type="text" class="form-control" name="declared_commodity_code" value="{{ $batch->declared_commodity_code ?? '' }}" placeholder="Declared Commodity Code..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Declared Amount</label>
									<input type="text" class="form-control" name="declared_amount" value="{{ $batch->declared_amount ?? '' }}" placeholder="Declared Amount..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Net Quantity and Unit of Quantity</label>
									<input type="text" class="form-control" name="net_quantity_and_unit_of_quantity" value="{{ $batch->net_quantity_and_unit_of_quantity ?? '' }}" placeholder="Net Quantity and Unit of Quantity..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Use of Goods</label>
									<textarea class="form-control" name="use_of_goods" placeholder="Use of Goods...">{{ $batch->use_of_goods ?? '' }}</textarea>
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Sample Appearance Description</label>
									<textarea class="form-control" name="sample_appearance_description" placeholder="Sample Appearance Description...">{{ $batch->sample_appearance_description ?? '' }}</textarea>
								</div>

							</div>
							<div class="form-group">
								@if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route')
								@else
									@if(isset($batch->id) && $batch->status == 'Samples In Lab')
									@else
										<button class="btn btn-outline-primary btn-lg btn-block" id="save-headers">
											<i class="mdi mdi-content-save"></i> Save
										</button>
									@endif
								@endif
							</div>
						</form>
          </div>
        </div>
      </div>
      <div class="col-sm-8 p-2">
				@if(isset($batch->id))
				<div class="card mb-2">
					<div class="card-header">
						<header style="font-size: large"><i class="mdi mdi-calendar-month"></i> Batch Dates</header>
					</div>
					<div class="card-body">
						<div class="row no-gutters">
							@foreach (getSampleDateTypes() as $date)
								@if($date == 'Login Date' || $date == 'Target Date' || $date == 'Processing Date')
								<div class="col-sm-4 p-1">
									<b style="color: rgb(68, 68, 68)"><i class="mdi mdi-calendar-outline"></i> {{ $date }}</b>
									<span class="float-right mr-2"
										style="padding: 3px 9px; font-size:12px; border-radius: 15px; background-color: #f0f0f0; border: 1px solid #eeeeee; color:rgb(68, 68, 68)">{{ $batch->get_date($date) ? date('Y-m-d', strtotime($batch->get_date($date)['date'])) : '-' }}</span>
								</div>
								@endif
							@endforeach
						</div>
					</div>
				</div>
				@endif
        <div class="card tab-card">
          <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="analyte-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Samples</a>
							</li>
							@if(isset($batch->id))
								{{-- <li class="nav-item">
									<a class="nav-link" id="raw-results-tab" data-toggle="tab" href="#raw-results" role="tab" aria-controls="Raw-Results" aria-selected="true"><i class="mdi mdi-clipboard-text-multiple"></i> Parameters</a>
								</li> --}}
								@if(Auth::user()->is_client == 0)
								@if(in_array($batch->status, array("Sample Approval","Sample Verification", "Reports In Payment","Reports for Collection")))
								<!-- <li class="nav-item">
									<a class="nav-link" id="processed-results-tab" data-toggle="tab" href="#processed-results" role="tab" aria-controls="Processed-Results" aria-selected="true"><i class="mdi mdi-clipboard-text"></i> Processed Results</a>
								</li> -->
								@endif
								<li class="nav-item">
									<a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes-reminders" role="tab" aria-controls="Notes" aria-selected="true"><i class="mdi mdi-android-messages"></i> Notes <span class="badge badge-pill badge-primary">{{ isset($batch->comments) ? count($batch->comments) : 0 }}</span></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="chain-of-custody-tab" data-toggle="tab" href="#chain-of-custody" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-sitemap"></i> Chain of Custody <span class="badge badge-pill badge-primary">{{$batch->custody->count()}}</span></a>
								</li>
								
								<li class="nav-item">
									<a class="nav-link" id="ammendment-tab" data-toggle="tab" href="#ammendment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-file-document-edit"></i> Amendment</a>
								</li>
								
								@if(in_array($batch->status, array("Sample Approval","Samples In Lab", "Sample Verification")))
								<!-- <li class="nav-item">
									<a class="nav-link" id="data-from-equipment-results-tab" data-toggle="tab" href="#data-from-equipment-results" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-file-cog"></i> Equipment Data</a>
								</li> -->
								@endif
								@endif
								<li class="nav-item">
									<a class="nav-link" id="attachment-tab" data-toggle="tab" href="#Attachment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-attachment"></i> Attachments</a>
								</li>
							@endif
              {{-- <li class="nav-item">
                <a class="nav-link disabled" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="Notes" aria-selected="false">Notes</a>
              </li> --}}
            </ul>
          </div>
          <div class="tab-content" id="analyte-tabs-content">
						@if(isset($batch->id))
							@if(in_array($batch->status, array("Sample Approval", "Samples In Lab", "Sample Verification")))
								<div class="tab-pane fade p-3" id="data-from-equipment-results" role="tabpanel" aria-labelledby="one-tab">
									<div class="p-2 row">
										<div class="col-sm-8">
											<h5><i class="mdi mdi-file-cog"></i> Data From Equipment</h5>
										</div>
									</div>
									<div class="table-responsive">
										<?php
											$equipment = array_keys($equipment_data['items']);
										?>
										<div class="table my-tab-headers row no-gutters">
											@foreach ($equipment as $eq)
												<span data-equipment="{{ $eq }}" class="my-tab {{ $loop->iteration == 1 ? 'selected' : '' }}">{{ $eq }}</span>
											@endforeach
										</div>
										{{-- <pre>{{ json_encode($equipment_data, JSON_PRETTY_PRINT) }}</pre> --}}
									</div>
									<div class="table-responsive mt-3">
										@foreach ($equipment as $eq)
											<?php $elems = array_values($equipment_data['items'][$eq]); ?>
											<?php $scodes = array_keys($equipment_data['items'][$eq]); ?>
											<div data-equipment="{{ $eq }}" class="equip-table {{ $loop->iteration == 1 ? '' : 'hidden' }}">
												<span class="btn btn-outline-info mb-3"><i class="mdi mdi-cog-refresh-outline"></i> Pull Data From {{ $eq }}</span>
												<table class="table my-small-text table-condensed table-bordered table-sm">
													<thead>
														<tr>
															<th>Sample Code</th>
															<th>Date</th>
															<?php $analyteKeys = array(); ?>
															@foreach ($elems as $e=>$a)
																@foreach ($a as $i=>$j)
																	@if(!isset($analyteKeys[$i]))
																	<th>{{ $i }}</th>
																	<?php
																		if(!isset($analyteKeys[$i])){
																			$analyteKeys[$i] = true;
																		}
																	?>
																	@endif
																@endforeach
															@endforeach
														</tr>
													</thead>
													<tbody>
														@foreach ($elems as $e=>$a)
														<?php $data_not_set = true; ?>
															<tr>
																{{-- <td>
																	<pre>{{ json_encode($a, JSON_PRETTY_PRINT) }}</pre>
																</td> --}}
																<td>{{ $scodes[$e] }}</td>
																@foreach ($analyteKeys as $i=>$b)
																	@if (isset($a[$i]))
																		@if ($data_not_set)
																			@if ($a[$i][1]!=null)
																				<td>{{ $a[$i][1] }}</td>
																			@else
																				<td> - </td>
																			@endif
																			<?php $data_not_set = false; ?>
																		@endif
																		<td>{{ $a[$i][0] }}</td>
																	@else
																		<td></td>
																	@endif
																@endforeach
															</tr>
														@endforeach
													</tbody>
												</table>
											</div>
										@endforeach
									</div>
								</div>
							@endif
							@if(in_array($batch->status, array("Sample Approval","Sample Verification", "Reports In Payment", "Reports for Collection")))
								<div class="tab-pane fade p-3" id="processed-results" role="tabpanel" aria-labelledby="one-tab">
									<div class="p-2 row">
										<div class="col-sm-8">
											<h5><i class="mdi mdi-clipboard-text"></i> Processed Results</h5>
										</div>
									</div>
									<div class="table-responsive">
										<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
											<thead class="bg-light p-2">
												<tr>
													<th>Analyte</th>
													<th>Sample Code</th>
													<th nowrap>Result</th>
													<th nowrap>Reporting Unit</th>
													<th>Analyst</th>
													<th>Equipment</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($batch->processed_results() as $res)
													<tr>
														<td>{{ $res->analyte_code }}</td>
														<td>{{ $res->sample_detail_code }}</td>
														<td>{{ $res->reporting_symbol."".$res->result }}</td>
														<td>{{ $res->unit_code }}</td>
														<td>{{ $res->operator }}</td>
														<td>{{ $res->equipment }}</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>
							@endif
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
																					@foreach (getNotifiableUsers() as $g)
																						<option value="{{ $g->id }}" {{ $item->created_by == $g->id ? 'selected' : ''}}>{{ $g->name }}</option>
																					@endforeach
																				</select>
																			</div>
																			<div class="form-group">
																				<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
																				<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
																					<option></option>
																					@foreach (getNotifiableUsers() as $g)
																						<option value="{{ $g->id }}" {{ in_array($g->id, $item->people_to_cc()['ids']) ? 'selected' : '' }}>{{ $g->name }}</option>
																					@endforeach
																				</select>
																			</div>
																			<input name="batch_id" type="hidden" value="{{ $batch->id }}" />
																			<div class="form-group">
																				<label class="control-label">Type</label>
																				<select class="form-control" name="type" required placeholder="Message Type...">
																					<option></option>
																					@foreach (getNotesReminderTypes() as $g)
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
											<?php $ammendments = getBatchAmmendmentsById($batch->id)?>
											@foreach($ammendments as $a)
											<tr>
												<td>V {{$a->version_number}}</td>
												<td>
													{{$a->sample_name}}
												</td>
												<td>
													<?php $user = getUserById($a->created_by_id)?>
													{{$user->name}}
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
													<?php
														
													 	$user_upload = getUserById($a->uploaded_by)
													?>
													<tr>
														<td>{{$loop->iteration}}</td>
														<td>{{ isset(getsystemconfigbyid($a->id)->id) ? getsystemconfigbyid($a->id)->value : 'General'}}</td>
														<td>{{$a->title ?? 'N/a'}}</td>
														<td>{{date('Y-m-d',strtotime($a->created_at))}}</td>
														<td>{{$user_upload->name}}</td>
														<td class="text-center">
														{!! $a->view == 1 ? '<a href="'.$a->attachment_url.'" target="_blank" data-toggle="tooltip" data-title="View Attachment" class=" btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i></a>' : '-' !!}
														
														</td>
														<td>
															{!! $a->delete == 1 ? '<span class="btn btn-sm btn-outline-danger" data-title="Delete Attachment" data-toggle="modal" data-target="#delete-attachment-'.$a->id.'" ><i class="mdi mdi-delete-empty"></i></span>' : '-' !!}
															
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
											<?php $user_upload = getUserById($a->uploaded_by)?>
											<tr>
												<td>{{$loop->iteration}}</td>
												<td>{{ isset(getsystemconfigbyid($a->attachment_type)->id) ? getsystemconfigbyid($a->attachment_type)->value : 'General'}}</td>
												<td>{{$a->title ?? 'N/a'}}</td>
												<td>{{date('Y-m-d',strtotime($a->created_at))}}</td>
												<td>{{$user_upload->name}}</td>
												
												<td class="text-center">
													{!! $a->view == 1 ? '<a href="'.$a->attachment_url.'" target="_blank" data-toggle="tooltip" data-title="View Attachment" class=" btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i></a>' : '-' !!}
												
												
												</td>
												<td>
													
												{!! $a->delete == 1 ? '<span class="btn btn-sm btn-outline-danger" data-title="Delete Attachment" data-toggle="modal" data-target="#delete-attachment-'.$a->id.'" ><i class="mdi mdi-delete-empty"></i></span>' : '-' !!}
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
										{{-- @if($equipment_data['captured'] > 0) --}}
											<button type="button" class="btn btn-danger btn-sm text-white float-right" data-target="#send-back-for-rechcek-modal" data-toggle="modal">
												<i class="mdi mdi-page-previous"></i> Recheck
											</button> &nbsp; &nbsp;
										{{-- @endif --}}
									@endif
									@if(isset($batch->status) && in_array($batch->status, array("Samples Reception","Samples En-Route")))
										@if(Auth::user()->is_client == 1 && $batch->status == 'Samples Reception')
										@else
										<input type="hidden" name="batch" value={{$batch->id}}>
										<button type="button" class="btn btn-danger btn-sm text-white ml-2 save-samples"><i class="mdi mdi-content-save"></i> Save</button> &nbsp; &nbsp;
										<span class="btn btn-success btn-sm create-new-sample-row float-right"><i class="mdi mdi-plus"></i> Add</span> &nbsp; &nbsp;
										<span class="btn btn-primary btn-sm duplicate-sample-row float-right mr-1"><i class="mdi mdi-content-duplicate"></i> Duplicate</span>
										<span data-toggle="modal" data-target="#batch-edit-modal"
											class="btn btn-transparent text-info btn-sm batch-edit-row float-right mr-1">
											<i class="mdi mdi-pencil-box-multiple"></i> Batch Edit
										</span>
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
											<th nowrap>Condition<sup class="text-danger">*</sup> <span class="btn-primary btn-sm p-0" data-toggle="modal" data-target="#add-sample-conditions" data-target="tooltip" title="Add Sample Condition"><i class="mdi mdi-plus"></i></span></th>
											<th nowrap><span class="client-preferred-sample_point-name"></span><sup class="text-danger">*</sup> <span class="p-0 btn-primary btn-sm" data-target="#add-company-sample-point"  data-toggle="modal" data-target="tooltip" title="Add Sample Point" ><i class="mdi mdi-plus"></i></span></th>
											<th><span class="client-preferred-product-name"></span><sup class="text-danger">*</sup> <span class="btn-primary btn-sm p-0" data-toggle="modal" data-target="#add-company-product" data-toggle="tooltip" title="Add Product"><i class="mdi mdi-plus"></i></span></th>
											<th>Main Standard <sup class="text-danger">*</sup></th>
											<th>Secondary Standard <sup class="text-danger">*</sup></th>
											<th>Lab Submission No</th>
											<th>Comments</th>
											<th>Storage</th>
											<th>Slot</th>
											<th>Quantity</th>
											<th>UoM</th>
											<th>Barcode</th>
											
										</tr>
									</thead>
									<?php
										$analaytesHolder = array();
										$analysisBySample = array();
										$analysisBySampleNames = array();
										foreach ($batch->captured_results ?? array() as $item){
											
											if(!isset($analaytesHolder[$item->sample_detail_code])){
												$analaytesHolder[$item->sample_detail_code] = array();
											}
											$operators = $item->operators();
											if(sizeof($operators)> 0){

												$item->ops = $item->operators();
											}else{
												$item->ops = getOperators();
											}
											$item->equip_name = $item->equipment()->name ?? '-';
											
											$item->def_operator = $item->defacto_analyst();
											$item->analysis_type = $item->analysis_type;
											$analyte = getAnalyteByID($item->analyte_id);
											
											if(isset($analyte->id)){

												$item->analyte_name = $analyte->name;  
												
												
												
											}else{
												$item->analyte_name = $item->analyte_code;  
												
											}
											$item->methods = $analyte->methods();
											$analaytesHolder[$item->sample_detail_code][] = $item;
											$sample_details_test = getSampleDetailById($item->sample_detail_id);
											if(isset($sample_details_test->id)){
												$standard = getStandardByid($sample_details_test->main_standard);
												$sec = getStandardByid($sample_details_test->secondary_standard);
												if(isset($standard->id)){
													$analyte_standard = getAnalyteStandardValue($item->analyte_id,$standard->id);
													if(isset($analyte_standard->id)){
														if($analyte_standard->standard_value_type == 'is_range'){
															$item->standard_value = $analyte_standard->low.' - '.$analyte_standard->high;
														}elseif($analyte_standard->standard_value_type == 'is_standard_value'){
															$value_id = getStandardValuebyID($analyte_standard->standard_value_id);
															if(isset($value_id->id)){
																if($value_id->code == 'IsValue'){
																	$item->standard_value = $analyte_standard->standard_is_value;
																}else{
																	$item->standard_value = $value_id->code;
																}
															}
														}
													}else{
														$item->standard_value = 'NS';
													}
													$item->main_standard = $standard->code;
													
												}
												if(isset($sec->id)){
													$item->secondary_standard = $sec->code;
												}
											}
										}

										foreach ($batch->samples ?? array() as $sample) {
											if(!isset($analysisBySample[$sample->sample_code])){
												$analysisBySample[$sample->sample_code] = array();
											}
											$analysisBySample[$sample->sample_code] = array_merge(explode(",",$sample->analysis_type_id), $analysisBySample[$sample->sample_code]);
											foreach($analysisBySample[$sample->sample_code] as $id){
												$analysis = getAnalysisTypeID($id);
												if(!isset($analysisBySampleNames[$sample->sample_code])){
													$analysisBySampleNames[$sample->sample_code] = [];
												}
												$analysisBySampleNames[$sample->sample_code][$analysis->name] = $analysis->id;
											}
										}

										// echo json_encode($batch->all_samples());
									?>
									<tbody id="sample-detail-rows"
										data-analysis_names = '{{json_encode($analysisBySampleNames)}}'
										data-sample_analysis_ids = '{{ json_encode($analysisBySample) }}'
										data-parameters='{{ json_encode($analaytesHolder) }}'
										data-conditions='{{ json_encode($selectedSampleType->sample_condition ?? array()) }}'
										data-stores='{{ json_encode($labStores) }}'
										data-analysis_types='{{ json_encode($selectedSampleType->analysis_types ?? array()) }}'
										data-samples="{{ json_encode(isset($batch->id) ? $batch->all_samples() : array()) }}"
										data-ammendments = "{{ json_encode($ammendable) }}"
										data-client = "{{json_encode(Auth::user())}}"
										data-batch = "{{json_encode($batch ?? array())}}"
										data-standards = "{{json_encode($standards ?? array())}}"
										data-methods = "{{json_encode($methods)}}"
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
	<?php 
	$customer = getCrmCustomerByID($batch->crm_customer_id);
	?>
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
						<label>{{ trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Unit' }}</label>
						<select class="form-control" name="unit" placeholder="Select..." required>
							<option></option>
							@foreach ($customer->units as $item)
							<option value="{{ $item->id }}">{{ $item->name }}</option>
							@endforeach
						</select>
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
		<?php $requestTypes = getRequestTypes(); ?>
		@if(in_array($batch->status, array("Sample Verification","Sample Approval","Reports In Payment","Reports for Collection","Samples In Lab")))
			@if($batch->processed_results()->count() > 0)
				<div id="send-for-approval-modal" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<form class="modal-content" method="POST" action="{{ route('move-to-workflow', ['status'=>'Sample Approval', 'batch_id'=>$batch->id]) }}" enctype="multipart/form-data">
							@csrf
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-check-decagram"></i> Send to Sample Approval </h4>
							</div>
							<div class="modal-body">
								<div class="form-group">
									<input type="hidden" name="is_approval" value="1">
									<label class="control-label">Approval Notes/Comments</label>
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
								<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Yes Proceed</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			@endif
			@if(isset($batch->status) && ($batch->status=="Sample Verification" || $batch->status=="Sample Approval"))
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
										<center>
											<img src="/images/load.gif" height="250px" width="auto" alt="">
										</center>
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
										@foreach (getNotifiableUsers() as $item)
											<option value="{{ $item->id }}">{{ $item->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
									<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
										<option></option>
										@foreach (getNotifiableUsers() as $item)
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
										@foreach ($not_captured as $samC=>$vals)
											<?php $anals = array_values($vals); ?>
											<tr>
												<td>{{ $samC }}</td>
												<td>{{ implode(',', $anals) }}</td>
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
							<div class="modal-body" id="sample-interpretations-holder"></div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-info btn-sm" onclick="tinyMCE.triggerSave()"><i class="mdi mdi-content-save"></i> Save</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
				<div id="process-results-modals" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<!-- Modal content-->
						<form class="modal-content" method="POST" action="{{ route('report-interpretations', ['batch_id'=>$batch->id]) }}" enctype="multipart/form-data">
							@csrf
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-file-document-edit"></i> Provide Interpretations </h4>
							</div>
							<div class="modal-body">
								<ul class="nav nav-tabs" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="report-interpretations-tab" data-toggle="tab" href="#report-interpretations" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-typewriter"></i> Email Body</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="report-header-tab" data-toggle="tab" href="#report-header-details" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-page-layout-header"></i> Report Header/Footer Details</a>
									</li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane fade p-3" id="report-header-details" role="tabpanel" aria-labelledby="one-tab">
										<h4>Report </h4>
										<div class="form-group">
											<label>Title</label>
											<input type="text" class="form-control" value="{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->title ?? $headerDetails['client_header']->title) : '' }}" name="report_title" placeholder="Report Title..." required />
										</div>
										<div class="form-group">
											<label>To</label>
											<input type="text" class="form-control" value="{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->to ?? $headerDetails['client_header']->to) : '' }}" name="report_to" placeholder="Report To..." required />
										</div>
										<div class="form-group">
											<label>C.C.</label>
											<input type="text" class="form-control" value="{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->cc ?? $headerDetails['client_header']->cc) : '' }}" name="report_cc" placeholder="Report C.C..." required />
										</div>
										<div class="form-group">
											<label>From</label>
											<input type="text" class="form-control" value="{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->from ?? $headerDetails['client_header']->from) : '' }}" name="report_from" placeholder="Report From..." required />
										</div>
										<div class="form-group">
											<label>Date</label>
											<input type="date" class="form-control" value="{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->date ?? $headerDetails['client_header']->date) : '' }}" name="report_date" placeholder="Report Date..." required />
										</div>
										{{-- <div class="form-group">
											<label>Ref</label>
											<input type="text" class="form-control" value="{{ $batch->reference_number }}" name="report_ref" placeholder="Report Ref..." readonly />
										</div> --}}
										<div class="form-group">
											<label>Re</label>
											<textarea class="form-control editor" name="report_re" placeholder="Report Re..." required>{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->re ?? $headerDetails['client_header']->re) : '' }}</textarea>
										</div>
										<hr>
										<div class="form-group">
											<label>For</label>
											<input type="text" class="form-control" value="{{ isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->for ?? $headerDetails['client_header']->for) : '' }}" name="report_for" placeholder="Report For..." required />
										</div>
										<div class="form-group">
											<label><input type="checkbox" name="update_client_headers" value="1"/> Update client report header defaults.</label>
										</div>
									</div>
									<div class="tab-pane show active p-3" id="report-interpretations" role="tabpanel" aria-labelledby="one-tab">
										<div class="form-group">
											<label>Declared Amount</label>
											<input type="text" class="form-control" value="{{ $batch->declared_amount ?? '' }}" name="declared_amount" placeholder="Declared Amount..." />
										</div>
										<div class="form-group">
											<label>Final Declared Amount</label>
											<input type="text" class="form-control" value="{{ $batch->final_declared_amount ?? '' }}" name="final_declared_amount" placeholder="Final Declared Amount..." />
										</div>
										<div class="form-group">
											<label>Outgoing Email Body</label>
											<textarea class="form-control editor" name="outgoing_email_body" placeholder="Outgoing Email Body..." required>{{ $headerDetails['header']->outgoing_email_body ?? '' }}</textarea>
										</div>
									</div>
								</div>
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
						<form class="modal-content" method="POST" action="{{ route('move-to-workflow', ['status'=>'Sample Verification', 'batch_id'=>$batch->id]) }}" enctype="multipart/form-data">
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
								@foreach (getNotifiableUsers() as $item)
									<option value="{{ $item->id }}">{{ $item->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
							<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
								<option></option>
								@foreach (getNotifiableUsers() as $item)
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
								@foreach (getNotesReminderTypes() as $item)
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
	@if(isset($batch->status) && in_array($batch->status, array("Samples Reception", "Samples En-Route")))
	<div id="batch-edit-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-pencil-box-multiple"></i> Batch Edit </h4>
				</div>
				<div class="modal-body">
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-analysis"> Select Analysis</label>
						<select name="sample_analysis" class="form-control form-control-sm sample-analysis" multiple placeholder="Select Analysis..."></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-condition"> Sample Condition</label>
						<select  name="sample_condition" class="form-control form-control-sm sample-condition" placeholder="Select Sample Condition..." required></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label "><input type="checkbox" class="bulk-checkbox" data-name=".sample-point"> <span class="client-preferred-sample_point-name">Sample Point</span></label>
						<select  name="sample_point" class="form-control form-control-sm sample-point" placeholder="Select..." required></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-product"> <span class="client-preferred-product-name">Product</span></label>
						<select  name="sample_product" class="form-control form-control-sm sample-product" placeholder="Select..." required></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-barcode"> Barcode</label>
						<input  name="sample_barcode" class="form-control form-control-sm sample-barcode" placeholder="Select..." >
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-comments"> Comments</label>
						<input  name="sample_comments" class="form-control form-control-sm sample-comments" placeholder="Select..." >
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-gps"> GPS</label>
						<input  name="sample_gps" class="form-control form-control-sm sample-gps" placeholder="Select...">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-info btn-sm make-batch-changes" data-dismiss="modal"><i class="mdi mdi-refresh"></i> Change</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	@endif
	<div id="show-sample-analysis-analytes" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog">
		<div class="modal-dialog modal-lg">
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
											<th nowrap>Reporting Symbol</th>
											<th nowrap>Result</th>
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
		@if(isset($batch->status))
			{{-- @if($equipment_data['captured'] > 0) --}}
				{{-- <link rel="stylesheet" href="/css/quilljs.css" /> --}}
				<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
			{{-- @endif --}}
		@endif
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
	<script>
		
		@if(isset($batch->status) && $batch->status=="Sample Approval")
			@if($equipment_data['captured'] > 0)
				tinymce.init({
					selector: 'textarea.editor'
				});
			@endif
		@endif
		var detectChange = function(ts){
			var op = $(ts).children('option:selected');
			$('#client-unit-select').html('<option value="" selected>Select Organizational Unit...</option>');
			$('#client-unit-select').trigger('change');
			$.each(op.data('units'), function(i, e){
				$('#client-unit-select').append('<option value="'+e.name+'">'+e.name+'</option>');
			});

			$('#client-unit-select').val($('#client-unit-select').data('selected')).trigger('change');
		};

		$(function(){
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
				$.ajax({
					url:"{{ route('process-raw-results', ['batch_id'=> isset($batch->id) ? $batch->id : 0 ]) }}",		
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
				
			
			// $('input[name="reference_number"]').blur(function(){
			// 	var value_ = $(this).val();
			// 	var batch = $(this).data('batch');
			// 	var $token   = $('meta[name="csrf-token"]').attr('content');
			// 	$('#rft-message').empty()
			// 	$.ajax({
			// 		url: "{{ route('check_rft_no') }}",
			// 		dataType: 'json',
			// 		data: {
			// 			_token: $token,
			// 			batch_id: batch,
			// 			rft_no : value_
			// 		},
			// 		type: "POST",
			// 		success: function(js){
			// 			if(js == 'success'){
			// 				$(this).removeClass('border');
			// 				$(this).removeClass('border-danger');
			// 				$('#save-headers').removeAttr('disabled');
							
			// 			}else{
			// 				$(this).addClass('border');
			// 				$(this).addClass('border-danger');
			// 				$('#save-headers').prop('disabled',true);
			// 				$('#rft-message').append('RFT No already exists!')
			// 			}
			// 		}
			// 	})
			// 	// console.log(batch);
			// })
			var sampleAnalysisByType = $('#sample-detail-rows').data('analysis_types')
			var sampleCondtions = $('#sample-detail-rows').data('conditions');
			var labStores = $('#sample-detail-rows').data('stores');
			var $ammendableSamples = $('#sample-detail-rows').data('ammendments');
			var $isclient = $('#sample-detail-rows').data('client');
			var $batch = $('#sample-detail-rows').data('batch');
			var standards = $('#sample-detail-rows').data('standards');
			
			var unitSamplePoints = [];
			var unitProducts = [];
			var clientPrefProductName;
			var clientPrefUnitName;
			var clientPrefSPName;

			var configuredSamples = $('#sample-detail-rows').data('samples');


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
						<i class="mdi mdi-chevron-double-down"></i> More Fields
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
				var parameters = parametersBySampleCode[sampleCode];
				console.log(parametersBySampleCode);
				var analysisIDs = analysisIDsBySampleCode[sampleCode];
				
				var loop = 1;
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
						unitProducts = js.products;
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

						$('#sample-detail-rows').find('tr').find('[name="sample_details[product][]"]').each(function(){
							var rowData = $(this).parents('tr').data('sample');
							var P = $(this);
							P.html('<option tetet></option>');
							$.each(unitProducts, function(j,s){
								P.append(`
									<option value="${s.id}">${s.name}</option>
								`);
							});
							P.val(rowData ? rowData.company_product_id : '');
							P.trigger('change');
						});

					}
				})
			})

			$('#client-select').on('change', function(){
				var selectedOps = $(this).children('option:selected');
				clientPrefProductName = selectedOps.data('product_name');
				clientPrefUnitName = selectedOps.data('unit_name');
				clientPrefSPName = selectedOps.data('sample_point_name');


				$('.client-prefered-unit-name').text(clientPrefUnitName)
				$('.client-preferred-sample_point-name').text(clientPrefSPName)
				$('.client-preferred-product-name').text(clientPrefProductName)
				var client_selected = $('#client-select').val();
				var client_id = 'client-'+client_selected;
				var text = document.getElementById(client_id);
				var text2 = document.getElementsByClassName('clients-data');
				
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
				var $row = $(`<div class="form-group">
					<label>Comments</label>
					<textarea class="form-control" name="header_body" placeholder="Comments..." required>{{ $headerDetails['header']->header_body ?? '' }}</textarea>
				</div>
				<div class="form-group">
					<label>Recommendations / Interpretations</label>
					<textarea class="form-control" name="main_body" placeholder="Recommendations / Interpretations..." >{{ $headerDetails['header']->main_body ?? '' }}</textarea>
				</div>`).clone();

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

			$('#batch-edit-modal').on('show.bs.modal', function(){
				$(this).find('.form-control').val('');
				var modal = $(this);
				modal.find('select.sample-analysis').html('');
				$.each(sampleAnalysisByType, function(s, sc){
					modal.find('select.sample-analysis').append(`<option value="${sc.id}">${sc.name}</option>`);
				});

				modal.find('select.sample-condition').html('');
				$.each(sampleCondtions, function(s, sc){
					modal.find('select.sample-condition').append(`<option value="${sc.id}">${sc.name}</option>`);
				});

				modal.find('select.sample-point').html('');
				$.each(unitSamplePoints, function(j,s){
					modal.find('select.sample-point').append(`<option value="${s.id}">${s.name}</option>`);
				});

				modal.find('select.sample-product').html('');
				$.each(unitProducts, function(j,s){
					modal.find('select.sample-product').append(`<option value="${s.id}" >${s.name}</option>`);
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
								
								"store_id": $(e).find('.sample-store').children('option:selected').val(),
								"slot_id": $(e).find('.sample-store-slot').children('option:selected').val(),
							};
							
							console.log(data)
							

							createRow(data);
						}
					});
				}

			});

			var createRow = function(data=false){
				var $row = $(sampleDetailsRow).clone();
				// console.log($ammeendableSamples);

				// console.log($batch);
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

					$row.find('.toggle-row-edit-mode').removeClass('text-primary').addClass('text-muted');

					$row.find('.dropdown-row').data('sample_code', data['sample_code']);

					$row.find('.provide-interpretation-row').data("action", '/sample-interpretations/'+data.id);
					$row.find('.provide-interpretation-row').data("headerbody", data.header_body);
					$row.find('.provide-interpretation-row').data("mainbody", data.main_body);
				}

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
				$row.find('[name="sample_details[product][]"]').html('<option></option>');

				$.each(unitProducts, function(j,s){
					$row.find('[name="sample_details[product][]"]').append(`
						<option value="${s.id}" ${ s.id == data['company_product_id'] ? 'selected' : '' }>${s.name}</option>
					`);
				});

				$row.append(`<input type="hidden" value="${data.id}" name="sample_details[detail_header][]" />`);

				var rowNo = $('#sample-detail-rows').find('tr').length;

				$row.find('[name="sample_details[sample_code][]"]').val(data['sample_code']);
				$row.find('[name="sample_details[submission_no][]"]').val(data['lab_sub_no']);

				$row.find('.analysis-field .form-control').empty();

				$.each(sampleAnalysisByType, function(s, sa){
					$row.find('.analysis-field .form-control').append(`<option value="${sa.id}">${sa.name}</option>`);
				});

				$row.find('.analysis-field .form-control').attr('name', 'sample_details[sample_analysis]['+rowNo+'][]');

				$row.find('.analysis-field .form-control').val(data['analysis_type_id'] ? data['analysis_type_id'].split(',') : '');

				$row.find('[name="sample_details[sample_condition][]"]').html(`<option value="">Select Condition</option>`);

				$.each(sampleCondtions, function(s, sc){
					$row.find('[name="sample_details[sample_condition][]"]').append(`<option value="${sc.id}">${sc.name}</option>`);
				});

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

					textHolder.text(values.join(','));
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
				<tr class="raw-data-row ${data.result == null ? 'no-result' : 'has-result'}" id="row-${loop}" >
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
					<td nowrap ><input type="text" {{isset($batch->status) && $batch->status != 'Samples In Lab' ? 'disabled' : ''}} name="result_reporting_symbol[${data.id}]" id="reporting-symbol" placeholder="Reporting Symbol..." value="${data.result_reporting_symbol == null ? '' :data.result_reporting_symbol }" ></td>
					<td>
						<div class="form-group">
							<input {{isset($batch->status) && $batch->status != 'Samples In Lab' ? 'disabled' : ''}} id="${data.sample_detail_code},${data.analyte_code},${data.id},${data.analyte_id}" style="min-width: 150px" type="text"
							class="form-control first-result" id="result-${loop}" value="${data.result == null ? '' : data.result}" name="result[${data.id}]" placeholder="Result..." />
							<input type="hidden" name="result_confirm"  />
						</div>
					</td>
					@endif
					<td nowrap> <input type="text" class="form-control" style="width:100px" name="main_value[${data.id}]" value="${data.standard_value == null ? '-': data.standard_value}" disabled />
					<input type="hidden" name="main_value[${data.id}]" value="${data.standard_value}"/>
					<input type="hidden" name="main_standard[${data.id}]" value="${data.main_standard}"/>
					<input type="hidden" name="secondary_standard[${data.id}]" value="${data.secondary_standard}"/>
					</td>
					@if(Auth::user()->is_client == 0)
					<td nowrap>
						<input id="${data.sample_detail_code}-${data.id}" style="min-width: 150px" type="text" 
						class="form-control disabled first-result" readonly="true" value="${data.remark ?? ''}" name="remark[${data.id}]" placeholder="Remark..." />
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
					<input class="form-check" type="checkbox" {{Auth::user()->is_client == 1 ? 'disabled' : ''}}  name="accredited[${data.id}]" ${data.analyte_accredited  == 1 ? 'checked':''}/>
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
				console.log(reporting_symbol);
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
					console.log(tt_str);

					var res = tt_str.replace(/,/g,'-');
					
					console.log("here 2 : " + this.id);

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
						},
						success:function(response){
						    console.log("here : " + this.id);
							console.log(response);
							var inputclass = '#'+res;
							console.log(inputclass);
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
			$.each(data.ops, function(o,p){
				$row.find('select.item-operators').append(`<option value="${p.id}">${p.name}</option>`)
			});
			$row.find('select.method-id').select2();
			$row.find('select.item-operators').select2();
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
					<span class="btn edit-remove btn-default text-primary btn-sm toggle-row-edit-mode" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></span> &nbsp;
					<span class="btn btn-default delete-remove text-danger btn-sm delete-row" data-toggle="tooltip" title="Delete"><i class="mdi mdi-trash-can"></i></span>
				@endif
				@if(isset($batch->status) && in_array($batch->status, array("Sample Approval","Samples In Lab","Sample Verification")))
					<span class="btn btn-default interpretation-remove text-success btn-sm provide-interpretation-row" data-target="#provide-interpretations" data-toggle="modal"  data-toggle="tooltip" title="Comments and Interpretation"><i class="mdi mdi-android-messages"></i></span>
				@endif
				<span class="btn btn-default parameter-remove text-info btn-sm dropdown-row" data-target="#show-sample-analysis-analytes" data-toggle="modal" data-toggle="tooltip" title="Parameters" ><i class="mdi mdi-snowflake"></i></span>
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
			<td class="sample-condition-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm is-required sample-condition" name="sample_details[sample_condition][]" style="width: 200px" placeholder="Select Sample Condition..." required>
						@if($selectedSampleType)
							@foreach($selectedSampleType->sample_condition as $con)
								<option value="{{ $con->id }}">{{ $con->name }}</option>
							@endforeach
						@endif
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
					<select class="form-control form-control-sm is-required sample-product" name="sample_details[product][]" style="width: 200px" placeholder="Select..." required></select>
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
					<select class="form-control form-control-sm is-required secondary-standard" name="sample_details[secondary_standard][]" style"width:200px" placeholder="Select Sec Standard..." required>
					@if($standards)
						@foreach($standards as $standard)
						<option value="{{$standard->id}}">{{$standard->code}}</option>
						@endforeach
					@endif
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class="submission-form-field" nowrap>
				<div class="form-group form-group-sm">
					<input type="text" style="width: 100px" class="form-control form-control-sm submission-form-no" name="sample_details[submission_no][]"  />
				</div>
				<span class="text"></span>
			</td>
			<td class="comments-field" nowrap>
				<div class="form-group form-group-sm">
					<textarea rows="1" style="width: 200px" class="form-control form-control-sm sample-comments" name="sample_details[comments][]" placeholder="Sample Comments..."></textarea>
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-store-field" nowrap>
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
			<td class="sample-slot-field" nowrap>
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm sample-store-slot" name="sample_details[sample_store_slot][]" style="width: 200px !important" placeholder="Select a Srore First...">
						<option></option>
					</select>
				</div>
				<span class="text"></span>
			</td>
			
			<td class="sample-quantity-field">
				<div class="form-group form-group-sm">
					<input type="number" min="0" style="width: 200px !important" class="form-control form-control-sm sample-quantity"  name="sample_details[sample_quantity][]" placeholder="Sample Quantity..." />
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-reporting-unit-field">
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
			<td class="barcode-field">
				<div class="form-group form-group-sm">
					<input type="text" style="width: 100px" class="form-control form-control-sm sample-barcode" name="sample_details[barcode][]" placeholder="BarCode..." />
				</div>
				<span class="text"></span>
			</td>
			

		</tr>`;
	</script>
@endsection