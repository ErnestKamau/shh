{{--
	ls-select2-multi-columns — multi Select2 with label + meta columns in dropdown (no avatars).
	Props: $label, $name, $options as value => [label, meta] OR list of {value,label,meta*},
	       $selected, $hint, $id, $required,
	       Livewire: $dataWireField, $dataSelectLive, $extraSelectClass, $selectedValuesJson,
	                 $metaMethodKey / $metaLabKey for data-meta-method / data-meta-lab
--}}
@php
	$id = $id ?? 'ls-s2mc-'.uniqid();
	$options = $options ?? [
		'finance' => ['label' => 'Finance', 'meta' => '2,568'],
		'healthcare' => ['label' => 'Healthcare', 'meta' => '1,204'],
		'software' => ['label' => 'Software', 'meta' => '3,910'],
		'construction' => ['label' => 'Construction', 'meta' => '842'],
		'education' => ['label' => 'Education', 'meta' => '1,055'],
	];
	$selected = array_values(array_map(
		'strval',
		(array) ($selected ?? ['finance', 'healthcare', 'software'])
	));
	$normalized = [];
	foreach ($options as $value => $opt) {
		if (is_array($opt) && array_key_exists('value', $opt)) {
			$normalized[] = [
				'value' => (string) ($opt['value'] ?? ''),
				'label' => (string) ($opt['label'] ?? $opt['value'] ?? ''),
				'meta' => (string) ($opt['meta'] ?? ''),
				'meta_method' => (string) ($opt['meta_method'] ?? ''),
				'meta_lab' => (string) ($opt['meta_lab'] ?? ''),
				'meta_analysis_type' => (string) ($opt['meta_analysis_type'] ?? ''),
			];
			continue;
		}
		$normalized[] = [
			'value' => (string) $value,
			'label' => is_array($opt) ? (string) ($opt['label'] ?? $value) : (string) $opt,
			'meta' => is_array($opt) ? (string) ($opt['meta'] ?? '') : '',
			'meta_method' => is_array($opt) ? (string) ($opt['meta_method'] ?? '') : '',
			'meta_lab' => is_array($opt) ? (string) ($opt['meta_lab'] ?? '') : '',
			'meta_analysis_type' => is_array($opt) ? (string) ($opt['meta_analysis_type'] ?? '') : '',
		];
	}
@endphp
<div class="ls-field ls-compact {{ ! empty($required) ? 'ls-field--required' : '' }}">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<div class="ls-select2-multi ls-select2-slate" @if(! empty($wireIgnore)) wire:ignore @endif>
		<select
			id="{{ $id }}"
			name="{{ ($name ?? $id) }}[]"
			class="ls-select2-multi-columns-el form-control {{ $extraSelectClass ?? '' }}"
			multiple
			data-placeholder="{{ $placeholder ?? 'Select…' }}"
			data-ls-multi-columns="1"
			@if(! empty($required)) required @endif
			@if(! empty($dataWireField)) data-wire-field="{{ $dataWireField }}" @endif
			@if(! empty($dataSelectLive)) data-select-live="{{ $dataSelectLive }}" @endif
			@if(! empty($dataSyncMethod)) data-sync-method="{{ $dataSyncMethod }}" @endif
			@if(! empty($dataSyncKey)) data-sync-key="{{ $dataSyncKey }}" @endif
			@if(! empty($selectedValuesJson)) data-selected-values="{{ $selectedValuesJson }}" @endif
			style="width: 100%;"
		>
			@foreach($normalized as $opt)
				<option
					value="{{ $opt['value'] }}"
					data-meta="{{ $opt['meta'] }}"
					data-meta-method="{{ $opt['meta_method'] }}"
					data-meta-lab="{{ $opt['meta_lab'] }}"
					data-meta-analysis-type="{{ $opt['meta_analysis_type'] }}"
					@selected(in_array($opt['value'], $selected, true))
				>{{ $opt['label'] }}</option>
			@endforeach
		</select>
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
