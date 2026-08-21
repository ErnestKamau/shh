{{--
	ls-select2-multi-dropdown-search — multi Select2, search bar in dropdown, no avatars.
	Props: $label, $name, $options (value=>label OR list of {value,label}), $selected, $hint,
	       $variant (burgundy|slate), $id, $required, $multiple (default true; false = single),
	       Livewire: $dataWireField, $dataSelectLive, $extraSelectClass, $selectedValuesJson, $wireIgnore
--}}
@php
	$id = $id ?? 'ls-s2mds-'.uniqid();
	$options = $options ?? [
		'family' => 'Family',
		'family_law' => 'Family in law',
		'coworkers' => 'Co-workers',
		'friends' => 'Friends',
		'basketball' => 'Basketball Club',
		'investors' => 'Startup Investor Colleagues',
	];
	$isMultiple = (bool) ($multiple ?? true);
	$defaultSelected = $isMultiple ? ['family', 'family_law', 'coworkers'] : [];
	$selected = array_values(array_map('strval', (array) ($selected ?? $defaultSelected)));
	$variant = $variant ?? 'slate';
	$normalized = [];
	foreach ($options as $value => $optLabel) {
		if (is_array($optLabel) && array_key_exists('value', $optLabel)) {
			$normalized[] = [
				'value' => (string) ($optLabel['value'] ?? ''),
				'label' => (string) ($optLabel['label'] ?? $optLabel['value'] ?? ''),
			];
			continue;
		}
		$normalized[] = [
			'value' => (string) $value,
			'label' => is_array($optLabel) ? (string) ($optLabel['label'] ?? $value) : (string) $optLabel,
		];
	}
	$selectName = $name ?? $id;
	if ($isMultiple) {
		$selectName .= '[]';
	}
@endphp
<div class="ls-field ls-compact {{ ! empty($required) ? 'ls-field--required' : '' }}">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<div class="ls-select2-multi ls-select2-{{ $variant }}" @if(! empty($wireIgnore)) wire:ignore @endif>
		<select
			id="{{ $id }}"
			name="{{ $selectName }}"
			class="ls-select2-multi-dropdown-search-el form-control {{ $extraSelectClass ?? '' }}"
			@if($isMultiple) multiple @endif
			data-placeholder="{{ $placeholder ?? 'Select…' }}"
			data-ls-multi-dropdown-search="1"
			@if(! $isMultiple) data-ls-single="1" @endif
			@if(! empty($required)) required @endif
			@if(! empty($dataWireField)) data-wire-field="{{ $dataWireField }}" @endif
			@if(! empty($dataSelectLive)) data-select-live="{{ $dataSelectLive }}" @endif
			@if(! empty($selectedValuesJson)) data-selected-values="{{ $selectedValuesJson }}" @endif
			style="width: 100%;"
		>
			@if(! $isMultiple)
				<option value=""></option>
			@endif
			@foreach($normalized as $opt)
				<option value="{{ $opt['value'] }}" @selected(in_array($opt['value'], $selected, true))>{{ $opt['label'] }}</option>
			@endforeach
		</select>
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
