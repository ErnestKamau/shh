{{--
	ls-trf-field-grid — column grids imitating TRF / request-view field types.
--}}
<div class="ls-form-panel mb-3">
	<h4 class="ls-form-panel__title"><i class="mdi mdi-flask-outline"></i> Sample card fields (2-col / affix)</h4>
	<div class="ls-form-grid ls-form-grid--2">
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Sampling Point',
			'name' => 'trf_point',
			'value' => 'point 1',
			'required' => true,
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
			'label' => 'Qty / Unit',
			'name' => 'trf_qty',
			'value' => '23',
			'suffixSelect' => ['g' => 'g', 'kg' => 'kg', 'ml' => 'ml', 'L' => 'L'],
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
			'label' => 'Temperature',
			'name' => 'trf_temp',
			'value' => '4.2',
			'prefix' => '<i class="mdi mdi-thermometer"></i>',
			'suffix' => '°C',
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Batch / Lot',
			'name' => 'trf_lot',
			'placeholder' => 'LOT-…',
		])
	</div>
</div>

<div class="ls-form-panel mb-3">
	<h4 class="ls-form-panel__title"><i class="mdi mdi-calendar-clock"></i> Dates &amp; times (3-col)</h4>
	<div class="ls-form-grid ls-form-grid--3">
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Date sampled',
			'name' => 'trf_date_sampled',
			'type' => 'date',
			'value' => '2026-08-21',
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Time sampled',
			'name' => 'trf_time_sampled',
			'type' => 'time',
			'value' => '09:30',
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Date received',
			'name' => 'trf_date_received',
			'type' => 'date',
			'value' => '2026-08-21',
		])
	</div>
</div>

<div class="ls-form-panel mb-3">
	<h4 class="ls-form-panel__title"><i class="mdi mdi-form-select"></i> Selects &amp; combos (12-col spans)</h4>
	<div class="ls-form-grid ls-form-grid--12">
		<div class="ls-span-6">
			@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
				'label' => 'Client / customer',
				'placeholder' => 'Search customer…',
				'options' => ['Amspec Santos', 'Harbor Foods', 'North Pier Labs', 'Blue Bay Co'],
			])
		</div>
		<div class="ls-span-6">
			@include('layouts.lab.partials.ls-ui.fields.ls-field-status-select', [
				'label' => 'Sample condition',
				'options' => [
					['value' => 'ok', 'label' => 'Satisfactory', 'color' => '#22c55e'],
					['value' => 'warn', 'label' => 'Compromised', 'color' => '#d97706'],
					['value' => 'bad', 'label' => 'Rejected', 'color' => '#ef4444'],
				],
				'selected' => 'ok',
			])
		</div>
		<div class="ls-span-12">
			@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
				'label' => 'Analysis types / tests',
				'name' => 'trf_tests',
				'options' => [
					'micro' => 'Microbiological',
					'chem' => 'Chemical',
					'phys' => 'Physical',
					'sensory' => 'Sensory',
				],
				'selected' => ['micro', 'chem'],
				'hint' => 'Select2 multi — search in dropdown only.',
			])
		</div>
	</div>
</div>

<div class="ls-form-panel mb-3">
	<h4 class="ls-form-panel__title"><i class="mdi mdi-checkbox-marked-outline"></i> Choice fields (TRF chips)</h4>
	@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
		'label' => 'Transport condition',
		'name' => 'trf_transport_condition',
		'type' => 'checkbox',
		'columns' => 'transport',
		'options' => [
			'chiller' => 'Chiller vehicle',
			'ambient' => 'Ambient',
			'frozen' => 'Frozen',
		],
		'selected' => ['chiller'],
	])
	@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
		'label' => 'Method of sampling',
		'name' => 'trf_method_of_sampling',
		'type' => 'checkbox',
		'columns' => 'method',
		'options' => [
			'apha' => 'APHA',
			'others' => 'Others',
			'dm' => 'DM',
			'saso' => 'SASO',
			'us_fda' => 'US FDA',
			'sop' => 'SOP',
			'astm' => 'ASTM',
			'ccfra' => 'CCFRA',
		],
		'selected' => ['apha', 'sop'],
	])
	@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
		'label' => 'Sampling apparatus',
		'name' => 'trf_sampling_apparatus',
		'type' => 'checkbox',
		'columns' => 'apparatus',
		'options' => [
			'sterile_bag' => 'Sterile bag',
			'grabber' => 'Grabber',
			'sterile_bottle' => 'Sterile bottle',
			'others' => 'Others',
			'sterile_swab' => 'Sterile swab',
			'air_sampler' => 'Air sampler',
		],
		'selected' => ['sterile_bag', 'sterile_bottle'],
	])
	@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
		'label' => 'Reason of collection',
		'name' => 'trf_reason_of_collection',
		'type' => 'radio',
		'columns' => '3',
		'options' => [
			'contract' => 'Contract',
			'haccp' => 'HACCP requirement',
			'regulatory' => 'Regulatory',
			'complaint' => 'Complaint',
			'routine' => 'Routine monitoring',
			'other' => 'Other',
		],
		'selected' => 'contract',
		'hint' => 'Radio tiles — single choice (same chip chrome as checkboxes).',
	])
</div>

<div class="ls-form-panel mb-3">
	<h4 class="ls-form-panel__title"><i class="mdi mdi-text-box-outline"></i> Long text &amp; contact</h4>
	<div class="ls-form-grid ls-form-grid--2">
		<div>
			<label class="ls-field__label">Sample description</label>
			<textarea class="ls-textarea" placeholder="Describe the sample…">Bacon — chilled, intact packaging.</textarea>
		</div>
		<div>
			<label class="ls-field__label">Notes / remarks</label>
			<textarea class="ls-textarea" placeholder="Internal notes…"></textarea>
			@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
				'label' => 'Email',
				'name' => 'trf_email',
				'type' => 'email',
				'placeholder' => 'contact@customer.com',
			])
			@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
				'label' => 'Number of samples',
				'name' => 'trf_nsamples',
				'type' => 'number',
				'value' => '3',
			])
		</div>
	</div>
</div>

<div class="ls-form-panel">
	<h4 class="ls-form-panel__title"><i class="mdi mdi-map-marker-radius-outline"></i> Collection meta</h4>
	<div class="ls-form-grid ls-form-grid--2 mb-2">
		@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
			'label' => 'Sampling location',
			'name' => 'trf_loc',
			'prefix' => '<i class="mdi mdi-map-marker"></i>',
			'placeholder' => 'Site / GPS ref',
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Thermometer ID',
			'name' => 'trf_thermometer',
			'placeholder' => 'Optional',
		])
	</div>
	@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
		'label' => 'Transport condition',
		'name' => 'trf_transport_meta',
		'type' => 'checkbox',
		'columns' => 'transport',
		'options' => [
			'chiller' => 'Chiller vehicle',
			'ambient' => 'Ambient',
			'frozen' => 'Frozen',
		],
		'selected' => ['ambient'],
	])
	@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
		'label' => 'Method of sampling',
		'name' => 'trf_method_meta',
		'type' => 'checkbox',
		'columns' => 'method',
		'options' => [
			'apha' => 'APHA',
			'others' => 'Others',
			'dm' => 'DM',
			'saso' => 'SASO',
			'us_fda' => 'US FDA',
			'sop' => 'SOP',
			'astm' => 'ASTM',
			'ccfra' => 'CCFRA',
		],
		'selected' => ['dm', 'astm'],
	])
</div>
