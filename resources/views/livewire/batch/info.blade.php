<div class="workflow-board-panel">
	<div class="workflow-board-panel-header">
		<h5>
			<i class="mdi mdi-information-outline"></i>
			Batch details
		</h5>
		<button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm batch-info-trigger">
			<i class="mdi mdi-chevron-double-down"></i> Batch info
		</button>
	</div>
	<div class="workflow-board-panel-body flush-top">
		<form action="{{ route('add-batch-info', ['batch' => $batchID]) }}" class="row {{ $batchID ? 'hidden' : '' }}" id="batch-detail-form"
			method="POST" autocomplete="off">
			<?php $maxDate = getTodayDate(); ?>
			@csrf
			<div class="row p-2 border-bottom">
				<div class="form-group col-md-3">
					<label class="control-label">Date Collected <span class="text-danger">*</span></label>
					<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date"
						value="{{ date('Y-m-d', strtotime($batch->date_collected ?? '')) }}" class="form-control "
						name="date_collected" required>

				</div>
				<div class="form-group col-md-3">
					<label class="control-label">Lab Reception Date <span class="text-danger">*</span> </label>
					<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date"
						value="{{ date('Y-m-d', strtotime($batch->receipt_date ?? '')) }}" class="form-control "
						name="receipt_date" {{ $defaultClient === false ? 'required' : '' }} autocomplete="off">

				</div>
				<div class="form-group col-md-3">
					<label for="" class="control-label">Time Of Receipt</label>
					<input type="time" name="radio_active_levels" id="" value="{{$batch->radio_active_levels ?? ''}}"
						class="form-control">
				</div>
				{{-- <div class="form-group col-md-3">
					<label for="" class="control-label">Temp Of Receipt</label>
					<input type="text" name="kra_office_ref" id="" value="{{ $batch->kra_office_ref ?? '' }}"
						class="form-control">
				</div> --}}

				<div class="form-group col-md-3 qc-omit-type-field">

					<label class="control-label">Client <span class="text-danger">*</span> <span
							class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer"
							data-toggle="modal" data-toggle="tooltip" title="Add Client"><i
								class="mdi mdi-plus"></i></span></label>
					<select class="form-control qc-remove-required {{ $defaultClient === false ? '' : '' }}"
						name="crm_customer_id" id="client-select"
						data-client-source="{{ route('sample-workflow.clients') }}"
						data-page-size="{{ $clientPageSize }}" onchange="detectChange(this)" {{ $defaultClient === false ? '' : '' }}>
						<option value="">Select Client...</option>
						@foreach ($clients as $client)
							@if($defaultClient === false)
								<option value="{{ $client->id }}" {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }} {{ $defaultClient == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
							@endif

							@if($defaultClient !== false && $client->id == $defaultClient){{-- Creating a batch from the
								client order --}}
								<option value="{{ $client->id }}" {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }} {{-- When coming from laboratory
									--}} {{ $defaultClient == $client->id ? 'selected' : '' }} {{-- When coming from client order
									--}}>{{ $client->name }}</option>
							@endif
						@endforeach
					</select>
					@if($defaultClient !== false)
						<input type="hidden" name="is_client_order" value="1" />
					@endif

				</div>
				<div
					class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Customer Contact <span class="btn-primary p-0 btn-sm"
							style="margin: 0px !important;" data-target="#add-customer-contact" data-toggle="modal"><i
								class="mdi mdi-plus" data-toggle="tooltip" title="Add Contact"></i></span></label>
					<select name="crm_contact_id" id="crm_contact_id" class="form-control"
						data-selected="{{ $batch->crm_contact_id ?? '' }}">
						<option value="">Choose Customer First...</option>
					</select>
				</div>
				<div
					class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Customer Email</label>
					<input type="text" name="customer_email" id="customer_email" class="form-control"
						value="{{isset($batch->id) ? $batch->schedule_customer_email : '' }}">
				</div>
				<div class="form-group col-md-3 qc-omit-type-field">
					<label class="control-label"><span class='client-prefered-unit-name'>Site Location</span> <span
							class="text-danger">*</span> <span class="btn-primary p-0 btn-sm"
							data-target="#add-company-unit" data-toggle="modal" data-toggle="tooltip"
							title="Add Site Location"><i class="mdi mdi-plus"></i></span></label>
					<select class="form-control" name="crm_unit_name" data-selected="{{ $batch->crm_unit_id ?? '' }}"
						id="client-unit-select">
						<option value="">Select Site Location...</option>
					</select>
				</div>
				<div class="form-group col-md-3">
					<label class="control-label text-sm">Sample Type <span class="text-danger">*</span></label>
					<select class="form-control" name="sample_type_id" required id="batch-info-sample-type">
						<option value="">Select Sample Type...</option>
						@foreach ($sample_types as $sample)
							<option value="{{ $sample->id }}" {{ isset($batch->sample_type_id) && $batch->sample_type_id == $sample->id ? 'selected' : '' }}
								data-conditions="{{ json_encode($sample->sample_condition) }}">{{ $sample->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group btn-group-sm col-md-3">
					<label class="control-label">Client REF / LPO No </span></label>
					<input type="text" class="form-control"
						data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="reference_number"
						value="{{ $batch->reference_number ?? '' }}" placeholder="Reference Number..." />
					<small id="rft-message" class="text-danger"></small>
				</div>
				<div class="form-group col-md-3 hidden">
					<label class="control-label">Batch Scope <span class="text-danger">*</span></label>
					<select name="batch_scope" id="" class="form-control" required>
						@foreach(explode(',', $batch_scope->value) as $scope)
							<option value="{{$scope}}" {{ isset($batch->id) && $batch->batch_scope == $scope ? 'selected' : '' }}>{{$scope}}</option>
						@endforeach
					</select>
				</div>
				<div
					class="form-group qc-omit-type-field col-md-3 {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label class="control-label">Customer Survey <span class="text-danger">*</span></label>
					<select name="customer_survey" id="" class="form-control" required>
						@foreach(explode(',', $customer_survey->value) as $survey)
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
								<option value="{{$l_section->id}}" {{isset($batch->id) && in_array($l_section->id, explode(',', $batch->lab_section_ids)) ? 'selected' : ''}}>
									{{$l_section->code}} - {{$l_section->name}}
								</option>
							@endforeach
						</select>
					</div>
				</div>
				<div
					class="form-group btn-group-sm col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Sampling Plan</label>
					<select name="sampling_method_id" id="" class="form-control">
						<option value="">Select Sampling Plan</option>
						@foreach($samplingmethods as $b_method)
							<option value="{{$b_method->id}}" {{isset($batch->id) && $batch->sampling_method_id == $b_method->id ? 'selected' : 'selected'}}>{{$b_method->code}} -
								{{$b_method->name}}
							</option>
						@endforeach
					</select>
				</div>
				<div
					class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Payment Made By</label>
					<input type="text" value="{{isset($batch->id) ? $batch->payment_done_by : ''}}"
						name="payment_done_by" placeholder="Payment Made By" class="form-control">
				</div>
				<div
					class="form-group btn-group-sm col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label class="control-label">Quotation Number</label>
					<input type="text" class="form-control"
						data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="quote_no"
						value="{{ $batch->quote_no ?? '' }}" placeholder="Quotation Number..." />
					<small id="rft-message" class="text-danger"></small>
				</div>


				{{-- <div class="form-group btn-group-sm col-md-3">
					<label class="control-label">Condition and Quality of Sample</label>
					<input type="text" class="form-control" autocomplete="off"
						value="{{$batch->condition_quality_sample ?? ''}}" name="condition_quality_sample"
						value="{{ $batch->submit_by ?? '' }}" placeholder="Condition and Quality of Sample..." />
				</div> --}}
				<div class="form-group btn-group-sm col-md-3 qc-omit-type-field">
					<label class="control-label">Sampled By</label>
					<input type="text" name="sample_by" value="{{$batch->sampling_officer_name ?? '' }}" id=""
						placeholder="Sampled By..." class="form-control">

				</div>
				<div class="form-group btn-group-sm col-md-3 ">
					<label class="control-label">Submitted By</label>
					<input type="text" class="form-control" autocomplete="off" value="{{$batch->submit_by ?? ''}}"
						name="submit_by" value="{{ $batch->submit_by ?? '' }}" placeholder="Submitted By..." />
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
					<label for="" class="control-label"><input type="checkbox" name="is_qc_batch" class="is_qc_batch" {{ isset($batch->id) && $batch->is_qc_batch == 1 ? 'checked' : '' }} id=""> Is QC Batch ?
					</label>
				</div>
				<div class="form-group col-md-4 btn-group-sm">
					<label class="control-label">
						<input type="checkbox" name="require_mu" value="1" {{ isset($batch->require_mu) && $batch->require_mu == 1 ? 'checked' : '' }}> Has client requested Measure of uncertainity ?
					</label>
				</div>
				<div class="form-group col-md-4 btn-group-sm">
					<label class="control-label">
						<input type="checkbox" name="client_instruction_clear" value="1" {{ isset($batch->client_instruction_clear) && $batch->client_instruction_clear == 1 ? 'checked' : '' }}> Are client`s instructions clear ?
					</label>
				</div>
				<div class="form-group col-md-4 btn-group-sm">
					<label class="control-label">
						<input type="checkbox" class="lab_capable" name="lab_capable" value="1" {{ isset($batch->lab_capable) ? ($batch->lab_capable == 1 ? 'checked' : '') : 'checked' }}> Is
						the laboratory capable of performing the requested tests?
					</label>
				</div>

				{{-- <div
					class="form-group col-md-4 btn-group-sm {{ isset($batch->lab_capable) ? ($batch->lab_capable == 1 ? 'hidden' : '' ) : 'hidden' }} batch_subcontracted_client_approval">
					<label class="control-label">
						<input type="checkbox" class="" name="batch_subcontracted_client_approval" value="1" {{
							isset($batch->batch_subcontracted_client_approval) &&
						$batch->batch_subcontracted_client_approval == 1 ? 'checked' : '' }}> Is the client willing for
						the sample to be subcontracted to an Approved Laboratory ?
					</label>
				</div> --}}
				<div class="form-group col-md-4 btn-group-sm hidden">
					<label class="control-label">
						<input type="checkbox" name="sampled_by_company_personnel" value="1" {{ isset($batch->sampled_by_company_personnel) && $batch->sampled_by_company_personnel == 1 ? 'checked' : '' }}> Sampled by {{$active_company->name}} personnel?
					</label>
				</div>
				<div class="form-group btn-group-sm col-md-12">
					<label class="control-label">Samples Description</label>
					<textarea class="form-control" name="description"
						placeholder="Description...">{{ $batch->description ?? '' }}</textarea>
				</div>
				<div class="form-group btn-group-sm col-md-12">
					<label class="control-label">Special Remarks / Instructions</label>
					<textarea class="form-control" name="batch_instructions"
						placeholder="Batch Instructions...">{{ $batch->batch_instructions ?? '' }}</textarea>
				</div>

			</div>

			<div class="row p-2 mt-1 qc-params {{ $batch && $batch->is_qc_batch == 1 ? '' : 'hidden' }}">
				<div class="col-md-12 mb-2">
					<div class="workflow-board-section-label mb-2">
						<i class="mdi mdi-flask-outline"></i>
						QC configurations
					</div>
				</div>
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


			<div id="more-fields" class="hidden">
				<div class="row workflow-board-filter-nested mx-0 mb-3">
					<div class="form-group col-md-6 btn-group-sm">
						<label class="control-label">Use of Goods</label>
						<textarea class="form-control" name="use_of_goods"
							placeholder="Use of Goods...">{{ $batch->use_of_goods ?? '' }}</textarea>
					</div>
				</div>

			</div>

			<div class="form-group col-md-12 text-center pt-2 border-top" style="border-color: #f1f5f9 !important;">
				@if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route')
				@else
					{{-- Allow save for all active stages as per user request --}}
					<button type="submit" class="btn btn-primary btn-sm btn-action-sm" style="width:60%; height:auto; min-height:36px;" id="save-headers">
						<i class="mdi mdi-content-save"></i> Save
					</button>
				@endif
			</div>
		</form>
	</div>
</div>