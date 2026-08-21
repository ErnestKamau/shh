{{--
	Quotation show — left-rail header form (LS gallery).
	Expects: $header, $customers, $labSections, $companyUnits, $samplePoints, $currencies
--}}
@php
	$selectedLabSectionIds = $header->labSections->pluck('id')->map(fn ($id) => (string) $id)->all();

	$clientOptions = collect($customers ?? [])->map(fn ($customer) => [
		'value' => (string) $customer->id,
		'label' => (string) $customer->name,
		'meta' => [
			'currency_id' => $customer->currency_id ? (string) $customer->currency_id : '',
			'zoho_customer_id' => (string) ($customer->zoho_customer_id ?? ''),
		],
	])->values()->all();

	$clientContacts = getQuotationCustomerContacts(
		(string) $header->crm_customer_id,
		$header->crm_customer_contact_id ? (string) $header->crm_customer_contact_id : null
	);
	$contactOptions = $clientContacts->map(function ($contact) {
		$label = trim(($contact->first_name ?? '').' '.($contact->middle_name ?? '').' '.($contact->last_name ?? ''));

		return [
			'value' => (string) $contact->id,
			'label' => $label !== '' ? $label : 'Contact',
		];
	})->values()->all();

	$labSectionOptions = collect($labSections ?? [])->mapWithKeys(fn ($section) => [
		(string) $section->id => [
			'label' => (string) $section->name,
			'meta' => (string) ($section->code ?: ''),
		],
	])->all();

	$companyUnitOptions = collect($companyUnits ?? [])->mapWithKeys(fn ($unit) => [
		(string) $unit->id => (string) $unit->name,
	])->all();

	$samplePointSelectOptions = collect($samplePoints ?? [])->mapWithKeys(fn ($point) => [
		(string) $point->id => (string) ($point->display_name ?? $point->name),
	])->all();

	$currencyOptions = collect($currencies ?? [])->map(fn ($currency) => [
		'value' => (string) $currency->id,
		'label' => trim($currency->code.' - '.$currency->description),
	])->values()->all();
@endphp

<aside class="ls-quotation-rail">
	<form
		action="{{ route('add-quotation-header') }}"
		method="POST"
		id="quotation-header-form"
		class="ls-quotation-header-form ls-quotation-rail__card"
		data-assigned-contact="{{ $header->crm_customer_contact_id }}"
		data-sample-point-units='@json(collect($samplePoints ?? [])->mapWithKeys(fn ($p) => [(string) $p->id => (string) ($p->crm_company_unit_id ?? '')])->all())'
	>
		@csrf
		<input type="hidden" name="quote_id" value="{{ $header->id }}">
		<input type="hidden" name="quote_code" value="{{ $header->quote_number }}">

		<div class="ls-quotation-rail__head">
			<div class="ls-quotation-rail__eyebrow">Header</div>
			<h3 class="ls-quotation-rail__title">{{ $header->quote_number }}</h3>
			<p class="ls-quotation-rail__subtitle">Client, location, schedule</p>
		</div>

		<section class="ls-quotation-rail__section">
			<h4 class="ls-quotation-rail__section-title"><i class="mdi mdi-account-outline"></i> Client &amp; type</h4>
			<div class="ls-quotation-rail__stack">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Quotation Number',
					'id' => 'quote-number-display',
					'name' => 'quote_code_display',
					'value' => $header->quote_number,
					'disabled' => true,
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Client',
					'id' => 'select-client',
					'name' => 'client',
					'required' => true,
					'placeholder' => 'Type to search clients…',
					'options' => $clientOptions,
					'selected' => $header->crm_customer_id ? (string) $header->crm_customer_id : null,
					'disableSuccess' => true,
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Client Contact',
					'id' => 'select-client-contact',
					'name' => 'client_contact',
					'required' => true,
					'placeholder' => 'Select client contact…',
					'options' => $contactOptions,
					'selected' => $header->crm_customer_contact_id ? (string) $header->crm_customer_contact_id : null,
					'disableSuccess' => true,
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-status-select', [
					'label' => 'Quotation Type',
					'id' => 'select-quotation-type',
					'name' => 'quotation_type',
					'required' => true,
					'selected' => $header->quotation_type ?: 'Analysis',
					'options' => [
						['value' => 'Analysis', 'label' => 'Analysis Quotation', 'color' => '#2563eb'],
						['value' => 'General', 'label' => 'General Quotation', 'color' => '#64748b'],
					],
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Prepared By',
					'id' => 'prepared-by-display',
					'name' => 'prepared_by_display',
					'value' => $header->prepared_by_name,
					'disabled' => true,
				])
				<input type="hidden" name="prepared_by" value="{{ $header->prepared_by_name }}">

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Subject',
					'id' => 'quote-subject',
					'name' => 'subject',
					'value' => $header->subject,
					'placeholder' => 'Quotation for …',
				])
			</div>
		</section>

		<section class="ls-quotation-rail__section">
			<h4 class="ls-quotation-rail__section-title"><i class="mdi mdi-map-marker-outline"></i> Location and Lab Section</h4>
			<div class="ls-quotation-rail__stack">
				@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
					'label' => 'Company Unit',
					'id' => 'quote-company-unit',
					'name' => 'crm_company_unit_id',
					'multiple' => false,
					'selected' => $header->crm_company_unit_id ? [(string) $header->crm_company_unit_id] : [],
					'options' => $companyUnitOptions,
					'placeholder' => 'Select company unit…',
					'variant' => 'slate',
				])

				@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
					'label' => 'Sampling Location',
					'id' => 'quote-sample-point',
					'name' => 'sample_point_id',
					'multiple' => false,
					'selected' => $header->sample_point_id ? [(string) $header->sample_point_id] : [],
					'options' => $samplePointSelectOptions,
					'placeholder' => 'Select sample point…',
					'variant' => 'slate',
				])

				@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns', [
					'label' => 'Lab Section(s)',
					'id' => 'select-lab-sections',
					'name' => 'lab_section_ids',
					'required' => true,
					'selected' => $selectedLabSectionIds,
					'options' => $labSectionOptions,
					'placeholder' => 'Select lab section(s)…',
					'hint' => 'Tests/parameters limited to these section(s).',
				])
			</div>
		</section>

		<section class="ls-quotation-rail__section">
			<h4 class="ls-quotation-rail__section-title"><i class="mdi mdi-calendar-outline"></i> Schedule &amp; currency</h4>
			<div class="ls-quotation-rail__stack">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Quotation Date',
					'id' => 'quotation-date',
					'name' => 'quotation_date',
					'type' => 'date',
					'required' => true,
					'value' => $header->quote_date,
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Expiry Date',
					'id' => 'expire-date',
					'name' => 'expire_date',
					'type' => 'date',
					'required' => true,
					'value' => $header->expiring_date,
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
					'label' => 'Currency',
					'id' => 'currency-id-edit',
					'name' => 'currency_id',
					'required' => true,
					'placeholder' => 'Type to search currency…',
					'options' => $currencyOptions,
					'selected' => $header->currency_id ? (string) $header->currency_id : null,
					'disableSuccess' => true,
				])

				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Laboratory Ref',
					'id' => 'laboratory-ref',
					'name' => 'laboratory_ref',
					'value' => $header->laboratory_ref,
					'placeholder' => 'Auto-generated if empty',
				])
			</div>
		</section>

		<input type="hidden" id="display-currency-edit" value="{{ $header->currency ? $header->currency->code.' - '.$header->currency->description : '' }}">
		<select id="select-zoho-customer-edit" class="d-none" aria-hidden="true"></select>

		<div class="ls-quotation-rail__footer">
			<button type="submit" class="btn btn-quotation-primary btn-block">
				<i class="mdi mdi-content-save-outline"></i> Save header
			</button>
		</div>
	</form>
</aside>
