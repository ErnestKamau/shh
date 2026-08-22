{{--
	ls-trf-water-stepper-form — Water TRF stepper card form (Edit request details columns).
	Differs from Food: Test requirement chips, water-oriented sample types, no food method/apparatus grids by default.
--}}
@php
	$step = (int) ($step ?? 1);
@endphp
<div class="ls-trf-stepper-form ls-trf-stepper-form--water" x-data="{ step: {{ $step }} }">
	<nav class="ls-trf-stepper-form__steps" aria-label="Water TRF sections">
		@foreach([1 => 'Customer', 2 => 'Collection', 3 => 'Samples'] as $n => $label)
			<button
				type="button"
				class="ls-trf-stepper-form__step"
				:class="{ 'is-active': step === {{ $n }}, 'is-done': step > {{ $n }} }"
				@click="step = {{ $n }}"
			>
				<span class="ls-trf-stepper-form__step-index">{{ $n }}</span>
				{{ $label }}
			</button>
			@if($n < 3)
				<span class="ls-trf-stepper-form__divider" aria-hidden="true"></span>
			@endif
		@endforeach
	</nav>

	<div class="ls-trf-stepper-form__body">
		<div x-show="step === 1" x-cloak>
			<section class="ls-trf-stepper-form__section">
				<h5 class="ls-type-label ls-trf-stepper-form__section-title">Customer Details</h5>
				<div class="ls-form-grid ls-form-grid--2">
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Client',
						'name' => 'water_trf_client',
						'value' => 'Amspec Santos',
						'success' => true,
						'disabled' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Company unit / Site',
						'name' => 'water_trf_unit',
						'placeholder' => 'Search company unit…',
						'options' => [
							['value' => '1', 'label' => 'Santos Plant'],
							['value' => '2', 'label' => 'Desalination Unit'],
						],
						'selected' => '1',
						'success' => true,
					])
				</div>
			</section>
			<section class="ls-trf-stepper-form__section">
				<h5 class="ls-type-label ls-trf-stepper-form__section-title">Contact</h5>
				<div class="ls-form-grid ls-form-grid--3">
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Contact name',
						'name' => 'water_trf_contact',
						'placeholder' => 'Search contacts…',
						'options' => [
							['value' => '1', 'label' => 'ERNEST KAMAU'],
							['value' => '2', 'label' => 'Site Supervisor'],
						],
						'selected' => '1',
						'success' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Email',
						'name' => 'water_trf_email',
						'type' => 'email',
						'value' => '1.kamauernest+staff99@gmail.com',
						'success' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Phone',
						'name' => 'water_trf_phone',
						'value' => '074567824224',
						'success' => true,
					])
				</div>
			</section>
		</div>

		<div x-show="step === 2" x-cloak>
			<section class="ls-trf-stepper-form__section">
				<h5 class="ls-type-label ls-trf-stepper-form__section-title">Sample Collection Data</h5>
				<div class="ls-form-grid ls-form-grid--3 mb-3">
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Sampling date',
						'name' => 'water_trf_sampling_date',
						'type' => 'date',
						'value' => '2026-08-21',
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Sampling time',
						'name' => 'water_trf_sampling_time',
						'type' => 'time',
						'value' => '10:15',
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Date received',
						'name' => 'water_trf_date_received',
						'type' => 'date',
						'value' => '2026-08-21',
					])
				</div>
				<div class="ls-form-grid ls-form-grid--2 mb-3">
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Sampling location',
						'name' => 'water_trf_location',
						'placeholder' => 'Search sampling location…',
						'options' => [
							['value' => 'tap A', 'label' => 'tap A'],
							['value' => 'tank 2', 'label' => 'tank 2'],
							['value' => 'outlet B', 'label' => 'outlet B'],
						],
						'selected' => 'tap A',
						'success' => true,
						'allowCustom' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
						'label' => 'Transport condition',
						'name' => 'water_trf_transport',
						'type' => 'checkbox',
						'columns' => 'transport',
						'options' => [
							'chiller' => 'Chiller vehicle',
							'ambient' => 'Ambient',
							'frozen' => 'Frozen',
						],
						'selected' => ['ambient'],
					])
				</div>
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Thermometer ID',
					'name' => 'water_trf_thermometer',
					'placeholder' => 'Optional',
				])
			</section>
		</div>

		<div x-show="step === 3" x-cloak>
			<section class="ls-trf-stepper-form__section">
				<h5 class="ls-type-label ls-trf-stepper-form__section-title">Sample Details</h5>
				<p class="ls-type-caption ls-trf-stepper-form__hint">Water TRF: sample type, analysis, tests, qty/unit, and test requirements.</p>
				<div class="ls-trf-sample-panel is-expanded">
					<div class="ls-trf-sample-panel__header" style="cursor:default;">
						<span class="ls-trf-sample-panel__title">Sample 1 · Drinking Water</span>
					</div>
					<div class="ls-trf-sample-panel__body">
						<div class="ls-form-grid ls-form-grid--2 mb-2">
							@include('layouts.lab.partials.ls-ui.select2.ls-select2-single-columns', [
								'label' => 'Sample type',
								'name' => 'water_trf_sample_type',
								'placeholder' => 'Search…',
								'required' => true,
								'options' => [
									['value' => '1', 'label' => 'Drinking Water', 'meta' => 'Water'],
									['value' => '2', 'label' => 'Process Water', 'meta' => 'Water'],
									['value' => '3', 'label' => 'Waste Water', 'meta' => 'Water'],
								],
								'selected' => '1',
							])
							@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
								'label' => 'Analysis Type',
								'name' => 'water_trf_analysis_type',
								'required' => true,
								'placeholder' => 'Search analysis type…',
								'variant' => 'slate',
								'options' => [
									'micro' => 'Microbiological',
									'chem' => 'Chemical Analysis',
									'legionella' => 'Legionella',
								],
								'selected' => ['micro', 'chem'],
							])
						</div>
						@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns', [
							'label' => 'Tests',
							'name' => 'water_trf_tests',
							'placeholder' => 'Search and add tests…',
							'options' => [
								'tpc' => ['label' => 'Total Plate Count', 'meta' => 'AMS/W/SOP/001', 'meta_method' => 'AMS/W/SOP/001'],
								'coli' => ['label' => 'Coliforms', 'meta' => 'AMS/W/SOP/012', 'meta_method' => 'AMS/W/SOP/012'],
								'ph' => ['label' => 'pH', 'meta' => 'AMS/W/SOP/020', 'meta_method' => 'AMS/W/SOP/020'],
							],
							'selected' => ['tpc', 'coli'],
						])
						<div class="ls-form-grid ls-form-grid--2 mt-2">
							@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
								'label' => 'Qty / Unit',
								'name' => 'water_trf_qty',
								'value' => '500',
								'suffixSelect' => ['mL' => 'mL', 'L' => 'L', 'CFU/100 mL' => 'CFU/100 mL'],
							])
							@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
								'label' => 'Sample Temp',
								'name' => 'water_trf_temp',
								'value' => '4.2',
								'prefix' => '<i class="mdi mdi-thermometer"></i>',
								'suffix' => '°C',
							])
						</div>
						<div class="mt-2">
							@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
								'label' => 'Test requirement',
								'name' => 'water_trf_test_requirements',
								'type' => 'checkbox',
								'columns' => '3',
								'options' => [
									'microbiology' => 'Microbiology',
									'legionella' => 'Legionella',
									'chemistry' => 'Chemical Analysis',
								],
								'selected' => ['microbiology', 'chemistry'],
								'hint' => 'Water-only field (not on Food TRF).',
							])
						</div>
						<div class="mt-2">
							@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
								'label' => 'Sample condition',
								'name' => 'water_trf_condition',
								'type' => 'checkbox',
								'columns' => '3',
								'options' => [
									'acceptable' => 'Acceptable',
									'chilled' => 'Chilled',
									'ambient' => 'Ambient',
								],
								'selected' => ['acceptable'],
							])
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>

	<div class="ls-trf-stepper-form__footer">
		<div class="ls-trf-stepper-form__dots" aria-hidden="true">
			@foreach([1, 2, 3] as $n)
				<span class="ls-trf-stepper-form__dot" :class="{ 'is-active': step === {{ $n }} }"></span>
			@endforeach
		</div>
		<div class="ls-trf-stepper-form__actions">
			<button type="button" class="ls-btn" @click="step = Math.max(1, step - 1)">Back</button>
			<button type="button" class="ls-btn ls-btn--primary" @click="step = Math.min(3, step + 1)" x-text="step === 3 ? 'Save changes' : 'Continue'"></button>
		</div>
	</div>
</div>
