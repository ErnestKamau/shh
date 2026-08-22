{{--
	ls-trf-food-stepper-form — Food TRF stepper card form (Edit request details columns).
	Gallery demo + reference for Fill TRF / Direct Registration fill.
--}}
@php
	$step = (int) ($step ?? 1);
@endphp
<div class="ls-trf-stepper-form ls-trf-stepper-form--food" x-data="{ step: {{ $step }} }">
	<nav class="ls-trf-stepper-form__steps" aria-label="Food TRF sections">
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
						'name' => 'food_trf_client',
						'value' => 'Amspec Santos',
						'success' => true,
						'disabled' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Company unit / Site',
						'name' => 'food_trf_unit',
						'placeholder' => 'Search company unit…',
						'options' => [
							['value' => '1', 'label' => 'Santos Beef'],
							['value' => '2', 'label' => 'Harbor Packing'],
							['value' => '3', 'label' => 'North Pier Cold Store'],
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
						'name' => 'food_trf_contact',
						'placeholder' => 'Search contacts…',
						'options' => [
							['value' => '1', 'label' => 'ERNEST KAMAU'],
							['value' => '2', 'label' => 'Jane Doe'],
						],
						'selected' => '1',
						'success' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Email',
						'name' => 'food_trf_email',
						'type' => 'email',
						'value' => '1.kamauernest+staff99@gmail.com',
						'success' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Phone',
						'name' => 'food_trf_phone',
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
						'name' => 'food_trf_sampling_date',
						'type' => 'date',
						'value' => '2026-08-21',
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Sampling time',
						'name' => 'food_trf_sampling_time',
						'type' => 'time',
						'value' => '09:30',
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Date received',
						'name' => 'food_trf_date_received',
						'type' => 'date',
						'value' => '2026-08-21',
					])
				</div>
				<div class="ls-form-grid ls-form-grid--3 mb-3">
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Sampling location',
						'name' => 'food_trf_location',
						'placeholder' => 'Search sampling location…',
						'options' => [
							['value' => 'point 1', 'label' => 'point 1'],
							['value' => 'line A', 'label' => 'line A'],
							['value' => 'cold room', 'label' => 'cold room'],
						],
						'selected' => 'point 1',
						'success' => true,
						'allowCustom' => true,
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
						'label' => 'Transport condition',
						'name' => 'food_trf_transport',
						'type' => 'checkbox',
						'columns' => 'transport',
						'options' => [
							'chiller' => 'Chiller vehicle',
							'ambient' => 'Ambient',
							'frozen' => 'Frozen',
						],
						'selected' => ['frozen'],
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
						'label' => 'Reason of collection',
						'name' => 'food_trf_reason',
						'type' => 'radio',
						'columns' => '3',
						'options' => [
							'contract' => 'Contract',
							'haccp' => 'HACCP requirement',
							'non_contract' => 'Non-contract',
							'disputed' => 'Disputed/Audit',
						],
						'selected' => 'haccp',
					])
				</div>
				<div class="ls-form-grid ls-form-grid--2">
					@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
						'label' => 'Sampling apparatus',
						'name' => 'food_trf_apparatus',
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
						'selected' => ['sterile_bag'],
					])
					@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
						'label' => 'Method of sampling',
						'name' => 'food_trf_method',
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
						'selected' => ['astm', 'ccfra'],
					])
				</div>
			</section>
		</div>

		<div x-show="step === 3" x-cloak>
			<section class="ls-trf-stepper-form__section">
				<h5 class="ls-type-label ls-trf-stepper-form__section-title">Sample Details</h5>
				<p class="ls-type-caption ls-trf-stepper-form__hint">Open a sample to edit its details. You can edit sample data, state of sample, condition and tests to be performed.</p>
				<div class="ls-trf-sample-panel is-expanded">
					<div class="ls-trf-sample-panel__header" style="cursor:default;">
						<span class="ls-trf-sample-panel__title">Sample 1 - CHICKEN</span>
					</div>
					<div class="ls-trf-sample-panel__body">
						<div class="ls-form-grid ls-form-grid--2 mb-2">
							@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
								'label' => 'Sample type',
								'name' => 'food_trf_sample_type',
								'required' => true,
								'placeholder' => 'Search sample type…',
								'variant' => 'slate',
								'options' => [
									'nonseafood' => 'Nonseafood',
									'seafood' => 'Seafood',
									'halal' => 'Halal',
								],
								'selected' => ['nonseafood'],
							])
							@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
								'label' => 'Analysis Type',
								'name' => 'food_trf_analysis_type',
								'required' => true,
								'placeholder' => 'Search analysis type…',
								'variant' => 'slate',
								'options' => [
									'micro' => 'Microbiological',
									'chem' => 'Chemical',
									'phys' => 'Physical',
								],
								'selected' => ['micro'],
							])
						</div>
						@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns', [
							'label' => 'Tests',
							'name' => 'food_trf_tests',
							'placeholder' => 'Search and add tests…',
							'options' => [
								'campy' => ['label' => 'Campylobacter Species', 'meta' => 'AMS/M/SOP/057', 'meta_method' => 'AMS/M/SOP/057'],
								'crono' => ['label' => 'Cronobacter spp. by RT-PCR', 'meta' => 'AMS/M/SOP/059', 'meta_method' => 'AMS/M/SOP/059'],
								'ecoli' => ['label' => 'E. coli O157', 'meta' => 'AMS/M/SOP/031', 'meta_method' => 'AMS/M/SOP/031'],
							],
							'selected' => ['campy', 'crono'],
						])
						<div class="ls-form-grid ls-form-grid--2 mt-2">
							@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
								'label' => 'Sampling Point',
								'name' => 'food_trf_point',
								'value' => 'point 1',
								'required' => true,
							])
							@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
								'label' => 'Qty / Unit',
								'name' => 'food_trf_qty',
								'value' => '23',
								'suffixSelect' => ['g' => 'g', 'kg' => 'kg', 'ml' => 'ml', 'g/L' => 'g/L'],
							])
							@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
								'label' => 'State of sample',
								'name' => 'food_trf_state',
								'placeholder' => 'Search…',
								'options' => ['Satisfactory', 'Compromised', 'Rejected'],
								'selected' => 'Satisfactory',
								'success' => true,
							])
							@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
								'label' => 'Production date',
								'name' => 'food_trf_prod_date',
								'type' => 'date',
							])
						</div>
						<div class="ls-form-grid ls-form-grid--2 mt-2">
							@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
								'label' => 'Test category',
								'name' => 'food_trf_test_category',
								'type' => 'checkbox',
								'columns' => '3',
								'options' => [
									'chemistry' => 'Chemistry',
									'microbiology' => 'Microbiology',
								],
								'selected' => ['microbiology'],
							])
							@include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
								'label' => 'Sample condition',
								'name' => 'food_trf_condition',
								'type' => 'checkbox',
								'columns' => '3',
								'options' => [
									'acceptable' => 'Acceptable',
									'chilled' => 'Chilled',
									'frozen' => 'Frozen',
									'ambient' => 'Ambient',
								],
								'selected' => ['chilled'],
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
