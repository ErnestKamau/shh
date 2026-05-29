<div class="workflow-board-panel" id="laboratory-acceptance-part-5">
	<div class="workflow-board-panel-header">
		<h5>
			<i class="mdi mdi-information-outline"></i>
			Batch details
		</h5>
	</div>
	<div class="workflow-board-panel-body flush-top">
		<form action="{{ route('add-batch-info', ['batch' => $batchID]) }}" class="row" id="batch-detail-form"
			method="POST" autocomplete="off">
			@php $maxDate = getTodayDate(); @endphp
			@csrf
			<div class="row p-2 w-100 mx-0">
				<div class="form-group col-md-3">
					<label class="control-label">Date Collected <span class="text-danger">*</span></label>
					<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date"
						value="{{ date('Y-m-d', strtotime($batch->date_collected ?? '')) }}" class="form-control"
						name="date_collected" required>
				</div>
				<div class="form-group col-md-3">
					<label class="control-label">Lab Reception Date <span class="text-danger">*</span></label>
					<input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date"
						value="{{ date('Y-m-d', strtotime($batch->receipt_date ?? '')) }}" class="form-control"
						name="receipt_date" {{ $defaultClient === false ? 'required' : '' }} autocomplete="off">
				</div>
				<div class="form-group col-md-3">
					<label for="" class="control-label">Time Of Receipt</label>
					<input type="time" name="radio_active_levels" value="{{ $batch->radio_active_levels ?? '' }}"
						class="form-control">
				</div>

				<div class="form-group col-md-3 qc-omit-type-field">
					<label class="control-label">Client <span class="text-danger">*</span>
						<span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer"
							data-toggle="modal" data-toggle="tooltip" title="Add Client"><i class="mdi mdi-plus"></i></span>
					</label>
					<select class="form-control qc-remove-required"
						name="crm_customer_id" id="client-select"
						data-client-source="{{ route('sample-workflow.clients') }}"
						data-page-size="{{ $clientPageSize }}" onchange="detectChange(this)">
						<option value="">Select Client...</option>
						@foreach ($clients as $client)
							@if($defaultClient === false)
								<option value="{{ $client->id }}" {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
							@endif
							@if($defaultClient !== false && $client->id == $defaultClient)
								<option value="{{ $client->id }}" {{ isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : '' }} {{ $defaultClient == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
							@endif
						@endforeach
					</select>
					@if($defaultClient !== false)
						<input type="hidden" name="is_client_order" value="1" />
					@endif
				</div>

				<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Customer Contact
						<span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer-contact" data-toggle="modal">
							<i class="mdi mdi-plus" data-toggle="tooltip" title="Add Contact"></i>
						</span>
					</label>
					<select name="crm_contact_id" id="crm_contact_id" class="form-control"
						data-selected="{{ $batch->crm_contact_id ?? '' }}">
						<option value="">Choose Customer First...</option>
					</select>
				</div>

				<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Customer Email</label>
					<input type="text" name="customer_email" id="customer_email" class="form-control"
						value="{{ isset($batch->id) ? $batch->schedule_customer_email : '' }}">
				</div>

				<div class="form-group col-md-3 qc-omit-type-field">
					<label class="control-label"><span class="client-prefered-unit-name">Site Location</span> <span class="text-danger">*</span>
						<span class="btn-primary p-0 btn-sm" data-target="#add-company-unit" data-toggle="modal" data-toggle="tooltip" title="Add Site Location"><i class="mdi mdi-plus"></i></span>
					</label>
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
					<label class="control-label">Client REF / LPO No</label>
					<input type="text" class="form-control"
						data-batch="{{ isset($batch->id) ? json_encode($batch->id) : 0 }}" name="reference_number"
						value="{{ $batch->reference_number ?? '' }}" placeholder="Reference Number..." />
					<small id="rft-message" class="text-danger"></small>
				</div>

				@php
					$selectedLabId = $batch->lab_id ?? null;
					if (!$selectedLabId && !empty($batch->lab_section_ids)) {
						$firstSectionId = trim(explode(',', (string) $batch->lab_section_ids)[0] ?? '');
						$selectedLabId = $labsections->firstWhere('id', $firstSectionId)?->lab_id;
					}
					$modeOfService = strtolower((string) ($batch->batch_scope ?? ''));
					if (!in_array($modeOfService, ['express', 'normal', 'confidential'], true)) {
						$modeOfService = strtolower((string) ($batch->priority ?? '')) === 'express' ? 'express' : 'normal';
					}
				@endphp

				<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label class="control-label">Mode of Payment</label>
					<input type="text" id="mode-of-payment" class="form-control" readonly
						value="{{ $selectedModeOfPayment ?? '' }}" placeholder="Select client to determine payment mode...">
				</div>

				<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label class="control-label">Mode of Service</label>
					<select name="batch_scope" class="form-control">
						<option value="normal" {{ $modeOfService === 'normal' ? 'selected' : '' }}>Normal</option>
						<option value="express" {{ $modeOfService === 'express' ? 'selected' : '' }}>Express</option>
						<option value="confidential" {{ $modeOfService === 'confidential' ? 'selected' : '' }}>Confidential</option>
					</select>
				</div>

				<div class="form-group col-md-3">
					<label for="" class="control-label">Lab</label>
					<select name="lab_id" class="form-control">
						<option value="">Select Lab...</option>
						@foreach($labs as $lab)
							<option value="{{ $lab->id }}" {{ (string) $selectedLabId === (string) $lab->id ? 'selected' : '' }}>
								{{ $lab->code }} - {{ $lab->name }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="form-group btn-group-sm col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Sampling Plan</label>
					<select name="sampling_method_id" class="form-control">
						<option value="">Select Sampling Plan</option>
						@foreach($samplingmethods as $b_method)
							<option value="{{ $b_method->id }}" {{ isset($batch->id) && $batch->sampling_method_id == $b_method->id ? 'selected' : '' }}>{{ $b_method->code }} - {{ $b_method->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group col-md-3 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label for="" class="control-label">Payment Made By</label>
					<input type="text" value="{{ isset($batch->id) ? $batch->payment_done_by : '' }}"
						name="payment_done_by" placeholder="Payment Made By" class="form-control">
				</div>

				<div class="form-group btn-group-sm col-md-3 qc-omit-type-field">
					<label class="control-label">Sampled By</label>
					<input type="text" name="sample_by" value="{{ $batch->sampling_officer_name ?? '' }}"
						placeholder="Sampled By..." class="form-control">
				</div>

				<div class="form-group btn-group-sm col-md-3">
					<label class="control-label">Submitted By</label>
					<input type="text" class="form-control" autocomplete="off" value="{{ $batch->submit_by ?? '' }}"
						name="submit_by" placeholder="Submitted By..." />
				</div>

				<div class="form-group btn-group-sm col-md-3">
					<label class="control-label">Received By <small class="text-danger">*</small></label>
					<select name="receive_by" required class="form-control">
						<option value="">Select user...</option>
						@foreach($recieving_users as $r_user)
							<option value="{{ $r_user->id }}" {{ isset($batch->id) && $batch->receiving_officer == $r_user->id ? 'selected' : '' }}>{{ $r_user->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="col-12 batch-details-checkboxes-section">
					<div class="batch-details-checkboxes-panel">
						<div class="row mx-0">
							<div class="form-group col-md-6 col-lg-3 btn-group-sm mb-2 mb-lg-3">
								<label class="control-label d-block mb-0">
									<input type="checkbox" name="is_qc_batch" class="is_qc_batch" {{ isset($batch->id) && $batch->is_qc_batch == 1 ? 'checked' : '' }}> Is QC Batch?
								</label>
							</div>
							<div class="form-group col-md-6 col-lg-3 btn-group-sm mb-2 mb-lg-3">
								<label class="control-label d-block mb-0">
									<input type="checkbox" name="require_mu" value="1" {{ isset($batch->require_mu) && $batch->require_mu == 1 ? 'checked' : '' }}> Has client requested Measure of uncertainity?
								</label>
							</div>
							<div class="form-group col-md-6 col-lg-3 btn-group-sm mb-2 mb-lg-3">
								<label class="control-label d-block mb-0">
									<input type="checkbox" name="client_instruction_clear" value="1" {{ isset($batch->client_instruction_clear) && $batch->client_instruction_clear == 1 ? 'checked' : '' }}> Are client`s instructions clear?
								</label>
							</div>
							<div class="form-group col-md-6 col-lg-3 btn-group-sm mb-0 mb-lg-3">
								<label class="control-label d-block mb-0">
									<input type="checkbox" class="lab_capable" name="lab_capable" value="1" {{ isset($batch->lab_capable) ? ($batch->lab_capable == 1 ? 'checked' : '') : 'checked' }}> Is the laboratory capable of performing the requested tests?
								</label>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group btn-group-sm col-md-12">
					<label class="control-label">Samples Description</label>
					<textarea class="form-control" name="description" placeholder="Description...">{{ $batch->description ?? '' }}</textarea>
				</div>
				<div class="form-group btn-group-sm col-md-12">
					<label class="control-label">Purpose</label>
					<textarea class="form-control" name="reason_for_submission" placeholder="Purpose...">{{ $batch->reason_for_submission ?? '' }}</textarea>
				</div>
				<div class="form-group btn-group-sm col-md-12">
					<label class="control-label">Safety Precautions</label>
					<textarea class="form-control" name="batch_instructions" placeholder="Safety Precautions...">{{ $batch->batch_instructions ?? '' }}</textarea>
				</div>

				<div class="row p-2 mt-1 qc-params w-100 mx-0 {{ $batch && $batch->is_qc_batch == 1 ? '' : 'hidden' }}">
					<div class="form-group col-md-4">
						<label for="" class="control-label">QC Scheme</label>
						<select name="qc_scheme_id" class="form-control">
							<option value="">Select QC Scheme</option>
							@foreach ($qc_schemes as $qc_scheme)
								<option value="{{ $qc_scheme->id }}" {{ $batch && $batch->qc_scheme_id == $qc_scheme->id ? 'selected' : '' }}>{{ $qc_scheme->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-4 form-group">
						<label for="" class="control-label">QC Type</label>
						<select name="qc_type_id" class="form-group qc_type_id">
							<option value="">Select QC Types</option>
							@foreach ($qc_types as $qc_type)
								<option value="{{ $qc_type->id }}" {{ $batch && $batch->qc_type_id == $qc_type->id ? 'selected' : '' }}>{{ $qc_type->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-4 form-group hidden repeat-sample-field">
						<label for="" class="control-label">Repeat Samples</label>
						<select name="repeat_samples_id[]" class="form-control repeat_sample_id"></select>
					</div>
				</div>
			</div>

			<div class="form-group col-md-12 text-center pt-3 border-top" style="border-color: #f1f5f9 !important;">
				@if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route')
				@else
					<button type="submit" class="btn btn-primary" style="padding: 8px 28px; font-size: 14px; border-radius: 4px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;" id="save-headers">
						<i class="mdi mdi-content-save" style="margin-right: 6px;"></i> Save
					</button>
				@endif
			</div>
		</form>
	</div>
</div>
