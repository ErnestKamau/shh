@php
	$maxDate = getTodayDate();
	$selectedLabId = $batch->lab_id ?? null;
	if (! $selectedLabId && ! empty($batch->lab_section_ids)) {
		$firstSectionId = trim(explode(',', (string) $batch->lab_section_ids)[0] ?? '');
		$selectedLabId = $labsections->firstWhere('id', $firstSectionId)?->lab_id;
	}
	$modeOfService = strtolower((string) ($batch->batch_scope ?? ''));
	if (! in_array($modeOfService, ['express', 'normal', 'confidential'], true)) {
		$modeOfService = strtolower((string) ($batch->priority ?? '')) === 'express' ? 'express' : 'normal';
	}
	$dateCollected = filled($batch->date_collected ?? null)
		? date('Y-m-d', strtotime((string) $batch->date_collected))
		: '';
	$receiptDate = filled($batch->receipt_date ?? null)
		? date('Y-m-d', strtotime((string) $batch->receipt_date))
		: '';
	$clientOptions = [];
	foreach ($clients as $client) {
		if ($defaultClient === false || (string) $client->id === (string) $defaultClient) {
			$clientOptions[(string) $client->id] = $client->name;
		}
	}
	$contactOptions = [];
	foreach ($customerContacts as $contact) {
		$contactOptions[(string) $contact->id] = trim(implode(' ', array_filter([
			$contact->first_name,
			$contact->middle_name ?? '',
			$contact->last_name,
		])));
	}
	$labOptions = [];
	foreach ($labs as $lab) {
		$labOptions[(string) $lab->id] = $lab->code.' - '.$lab->name;
	}
	$receiverOptions = [];
	foreach ($recieving_users as $rUser) {
		$receiverOptions[(string) $rUser->id] = $rUser->name;
	}
	$toSearchOptions = static function (array $map): array {
		$out = [];
		foreach ($map as $value => $label) {
			$out[] = ['value' => (string) $value, 'label' => (string) $label];
		}

		return $out;
	};
	$clientSearchOptions = $toSearchOptions($clientOptions);
	$contactSearchOptions = $toSearchOptions($contactOptions);
	$labSearchOptions = $toSearchOptions($labOptions);
	$receiverSearchOptions = $toSearchOptions($receiverOptions);
	$selectedClientId = (string) ($batch->crm_customer_id ?? ($defaultClient !== false ? $defaultClient : ''));
	$selectedContactId = (string) ($batch->crm_contact_id ?? '');
	$selectedUnitId = (string) ($batch->crm_unit_id ?? '');
	$selectedReceiverId = isset($batch->id) ? (string) ($batch->receiving_officer ?? '') : '';
	$labelAction = static function (string $target, string $title): string {
		return '<span class="ls-label-action" data-target="'.e($target).'" data-toggle="modal" title="'.e($title).'" role="button" tabindex="0"><i class="mdi mdi-plus"></i></span>';
	};
@endphp

<div class="ls-soft-card is-expanded batch-details-card" id="laboratory-acceptance-part-5">
	<div class="ls-soft-card__header" style="cursor: default;">
		<span>
			<i class="mdi mdi-information-outline" aria-hidden="true"></i>
			Batch details
		</span>
	</div>
	<div class="ls-soft-card__body">
		<form action="{{ route('add-batch-info', ['batch' => $batchID]) }}" id="batch-detail-form"
			method="POST" autocomplete="off">
			@csrf

			<div class="ls-form-grid ls-form-grid--4 batch-details-fields">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Date Collected',
					'lsName' => 'date_collected',
					'lsId' => 'batch-date-collected',
					'type' => 'date',
					'lsType' => 'date',
					'lsValue' => $dateCollected,
					'max' => $maxDate,
					'required' => true,
					'lsPlaceholder' => 'Lab Reception Date',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Lab Reception Date',
					'lsName' => 'receipt_date',
					'lsId' => 'batch-receipt-date',
					'type' => 'date',
					'lsType' => 'date',
					'lsValue' => $receiptDate,
					'max' => $maxDate,
					'required' => $defaultClient === false,
					'autocomplete' => 'off',
					'lsPlaceholder' => 'Lab Reception Date',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Time Of Receipt',
					'lsName' => 'radio_active_levels',
					'lsId' => 'batch-time-of-receipt',
					'type' => 'time',
					'lsType' => 'time',
					'lsValue' => $batch->radio_active_levels ?? '',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Client',
					'lsId' => 'client-select',
					'lsName' => 'crm_customer_id',
					'required' => true,
					'placeholder' => 'Select Client...',
					'options' => $clientSearchOptions,
					'selected' => $selectedClientId !== '' ? $selectedClientId : null,
					'disableSuccess' => true,
					'labelSuffix' => $labelAction('#add-customer', 'Add Client'),
					'extraFieldClass' => 'qc-omit-type-field mb-0',
					'attrs' => 'data-client-source="'.e(route('sample-workflow.clients')).'" data-page-size="'.e((string) $clientPageSize).'"',
				])
				@if($defaultClient !== false)
					<input type="hidden" name="is_client_order" value="1" />
				@endif

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Customer Contact',
					'lsId' => 'crm_contact_id',
					'lsName' => 'crm_contact_id',
					'placeholder' => 'Choose contact...',
					'options' => $contactSearchOptions,
					'selected' => $selectedContactId !== '' ? $selectedContactId : null,
					'disableSuccess' => true,
					'labelSuffix' => $labelAction('#add-customer-contact', 'Add Contact'),
					'extraFieldClass' => 'qc-omit-type-field mb-0'.($batch && $batch->is_qc_batch == 1 ? ' hidden' : ''),
					'attrs' => 'data-selected="'.e($selectedContactId).'"',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Customer Email',
					'lsName' => 'customer_email',
					'lsId' => 'customer_email',
					'lsValue' => isset($batch->id) ? $batch->schedule_customer_email : '',
					'extraFieldClass' => 'qc-omit-type-field'.($batch && $batch->is_qc_batch == 1 ? ' hidden' : ''),
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'labelHtml' => '<span class="client-prefered-unit-name">Site Location</span>',
					'lsId' => 'client-unit-select',
					'lsName' => 'crm_unit_name',
					'required' => true,
					'placeholder' => 'Select Site Location...',
					'options' => [],
					'selected' => $selectedUnitId !== '' ? $selectedUnitId : null,
					'disableSuccess' => true,
					'labelSuffix' => $labelAction('#add-company-unit', 'Add Site Location'),
					'extraFieldClass' => 'qc-omit-type-field mb-0',
					'attrs' => 'data-selected="'.e($selectedUnitId).'"',
				])

				<div class="ls-field mb-0">
					<label class="ls-field__label" for="batch-info-sample-type">
						Sample Type <span class="ls-req">*</span>
					</label>
					@if(! empty($batchSampleTypes) && count($batchSampleTypes) > 0)
						<div class="ls-field__control ls-field__control--readonly-pills"
							title="Sample types come from samples in this batch. Change them on the Samples tab.">
							@foreach($batchSampleTypes as $sampleTypeRow)
								<span class="ls-pill ls-pill--info">{{ $sampleTypeRow['name'] }}</span>
							@endforeach
						</div>
						<input type="hidden" name="sample_type_id" id="batch-info-sample-type"
							value="{{ $batch->sample_type_id ?? ($batchSampleTypes[0]['id'] ?? '') }}">
					@else
						<div class="ls-select2-single ls-select2-single-wrap">
							<i class="mdi mdi-magnify" aria-hidden="true"></i>
							<select class="ls-select2-single-el form-control" name="sample_type_id" required
								id="batch-info-sample-type" data-placeholder="Select Sample Type...">
								<option value="">Select Sample Type...</option>
								@foreach ($sample_types as $sample)
									<option value="{{ $sample->id }}"
										@selected(isset($batch->sample_type_id) && (string) $batch->sample_type_id === (string) $sample->id)
										data-conditions="{{ e(json_encode($sample->sample_condition ?? [])) }}">
										{{ $sample->name }}
									</option>
								@endforeach
							</select>
						</div>
					@endif
				</div>

				<div>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Client REF / LPO No',
						'lsName' => 'reference_number',
						'lsId' => 'batch-reference-number',
						'lsValue' => $batch->reference_number ?? '',
						'lsPlaceholder' => 'Reference Number...',
						'inputAttrs' => 'data-batch="'.e(isset($batch->id) ? json_encode($batch->id) : 0).'"',
					])
					<small id="rft-message" class="text-danger d-block mt-1"></small>
				</div>

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Mode of Payment',
					'lsId' => 'mode-of-payment',
					'lsValue' => $selectedModeOfPayment ?? '',
					'lsPlaceholder' => 'Select client to determine payment mode...',
					'readonly' => true,
					'omitName' => true,
					'extraFieldClass' => 'qc-omit-type-field'.($batch && $batch->is_qc_batch == 1 ? ' hidden' : ''),
				])

				<div class="ls-field mb-0 qc-omit-type-field {{ $batch && $batch->is_qc_batch == 1 ? 'hidden' : '' }}">
					<label class="ls-field__label" for="batch-mode-of-service">Mode of Service</label>
					<div class="ls-select2-single ls-select2-single-wrap">
						<select name="batch_scope" id="batch-mode-of-service" class="ls-select2-single-el form-control">
							<option value="normal" @selected($modeOfService === 'normal')>Normal</option>
							<option value="express" @selected($modeOfService === 'express')>Express</option>
							<option value="confidential" @selected($modeOfService === 'confidential')>Confidential</option>
						</select>
					</div>
				</div>

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Lab',
					'lsId' => 'batch-lab-id',
					'lsName' => 'lab_id',
					'placeholder' => 'Select Lab...',
					'options' => $labSearchOptions,
					'selected' => $selectedLabId ? (string) $selectedLabId : null,
					'disableSuccess' => true,
					'extraFieldClass' => 'mb-0',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Payment Made By',
					'lsName' => 'payment_done_by',
					'lsId' => 'batch-payment-done-by',
					'lsValue' => isset($batch->id) ? $batch->payment_done_by : '',
					'lsPlaceholder' => 'Payment Made By',
					'extraFieldClass' => 'qc-omit-type-field'.($batch && $batch->is_qc_batch == 1 ? ' hidden' : ''),
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Sampled By',
					'lsName' => 'sample_by',
					'lsId' => 'batch-sampled-by',
					'lsValue' => $batch->sampling_officer_name ?? '',
					'lsPlaceholder' => 'Sampled By...',
					'extraFieldClass' => 'qc-omit-type-field',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Submitted By',
					'lsName' => 'submit_by',
					'lsId' => 'batch-submitted-by',
					'lsValue' => $batch->submit_by ?? '',
					'lsPlaceholder' => 'Submitted By...',
					'autocomplete' => 'off',
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Received By',
					'lsId' => 'batch-received-by',
					'lsName' => 'receive_by',
					'required' => true,
					'placeholder' => 'Select user...',
					'options' => $receiverSearchOptions,
					'selected' => $selectedReceiverId !== '' ? $selectedReceiverId : null,
					'disableSuccess' => true,
					'extraFieldClass' => 'mb-0',
				])
			</div>

			<div class="batch-details-checkboxes-section">
				<div class="batch-details-checkboxes-panel ls-form-panel">
					<div class="ls-form-grid ls-form-grid--4 batch-details-checkbox-grid">
						<label class="ls-check">
							<input type="checkbox" name="is_qc_batch" class="is_qc_batch" {{ isset($batch->id) && $batch->is_qc_batch == 1 ? 'checked' : '' }}>
							<span>Is QC Batch?</span>
						</label>
						<label class="ls-check">
							<input type="checkbox" name="require_mu" value="1" {{ isset($batch->require_mu) && $batch->require_mu == 1 ? 'checked' : '' }}>
							<span>Has client requested Measure of uncertainity?</span>
						</label>
						<label class="ls-check">
							<input type="checkbox" name="client_instruction_clear" value="1" {{ isset($batch->client_instruction_clear) ? ($batch->client_instruction_clear == 1 ? 'checked' : '') : 'checked' }}>
							<span>Are client`s instructions clear?</span>
						</label>
						<label class="ls-check">
							<input type="checkbox" class="lab_capable" name="lab_capable" value="1" {{ isset($batch->lab_capable) ? ($batch->lab_capable == 1 ? 'checked' : '') : 'checked' }}>
							<span>Is the laboratory capable of performing the requested tests?</span>
						</label>
						<label class="ls-check">
							<input type="checkbox" name="is_shelf_life" value="1" {{ isset($batch->id) && ! empty($batch->is_shelf_life) ? 'checked' : '' }}>
							<span>Shelf life study?</span>
						</label>
					</div>
				</div>
			</div>

			<div class="batch-details-description">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-inline', [
					'label' => 'Samples Description',
					'lsName' => 'description',
					'lsId' => 'batch-description',
					'lsValue' => $batch->description ?? '',
					'placeholder' => 'Description...',
					'enableEditor' => true,
					'editorId' => 'batch-description-editor',
				])
			</div>

			@if(! empty($exportationSampleInfo))
				@php
					$expFields = $exportationSampleInfo['fields'] ?? [];
					$expLabels = [
						'date_received' => 'Date received',
						'packaging' => 'Packaging',
						'sample_weight' => 'Sample weight',
						'sample_information' => 'Sample information',
						'ship_name' => 'Ship / vessel',
						'port_of_loading' => 'Port of loading',
						'port_of_discharge' => 'Port of discharge',
						'seal_number' => 'Seal',
					];
				@endphp
				<div class="ls-form-panel batch-details-export-panel mt-3 mb-2">
					<h4 class="ls-form-panel__title">
						<i class="mdi mdi-ferry"></i>
						Exportation sample information
						<span class="ls-field__hint mb-0 ml-1" style="display:inline;font-weight:400;text-transform:none;letter-spacing:0;">
							({{ $exportationSampleInfo['form_name'] ?? 'TRF Exportation' }} — check before approval)
						</span>
					</h4>
					<div class="ls-form-grid ls-form-grid--4">
						@foreach($expLabels as $key => $label)
							<div class="ls-field mb-0 is-disabled">
								<span class="ls-field__label">{{ $label }}</span>
								<div class="ls-field__control">
									<div class="ls-field__input" style="background:#f8fafc;">
										{{ filled(trim((string) ($expFields[$key] ?? ''))) ? $expFields[$key] : '—' }}
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			@endif

			<div class="ls-form-grid ls-form-grid--3 qc-params mt-2 {{ $batch && $batch->is_qc_batch == 1 ? '' : 'hidden' }}">
				<div class="ls-field mb-0">
					<label class="ls-field__label" for="batch-qc-scheme">QC Scheme</label>
					<div class="ls-select2-single ls-select2-single-wrap">
						<select name="qc_scheme_id" id="batch-qc-scheme" class="ls-select2-single-el form-control">
							<option value="">Select QC Scheme</option>
							@foreach ($qc_schemes as $qc_scheme)
								<option value="{{ $qc_scheme->id }}" @selected($batch && (string) $batch->qc_scheme_id === (string) $qc_scheme->id)>
									{{ $qc_scheme->name }}
								</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="ls-field mb-0">
					<label class="ls-field__label" for="batch-qc-type">QC Type</label>
					<div class="ls-select2-single ls-select2-single-wrap">
						<select name="qc_type_id" id="batch-qc-type" class="ls-select2-single-el form-control qc_type_id">
							<option value="">Select QC Types</option>
							@foreach ($qc_types as $qc_type)
								<option value="{{ $qc_type->id }}" @selected($batch && (string) $batch->qc_type_id === (string) $qc_type->id)>
									{{ $qc_type->name }}
								</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="ls-field mb-0 hidden repeat-sample-field">
					<label class="ls-field__label" for="batch-repeat-samples">Repeat Samples</label>
					<div class="ls-select2-single ls-select2-single-wrap">
						<select name="repeat_samples_id[]" id="batch-repeat-samples" class="ls-select2-single-el form-control repeat_sample_id"></select>
					</div>
				</div>
			</div>

			<div class="batch-details-actions text-center pt-3 mt-2">
				@if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route')
				@else
					<button type="submit" class="ls-btn ls-btn--accent" id="save-headers">
						<i class="mdi mdi-content-save" aria-hidden="true"></i> Save
					</button>
				@endif
			</div>
		</form>
	</div>
</div>
