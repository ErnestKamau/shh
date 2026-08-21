{{--
	ls-field-select2-multi — label + .ls-select2-multi shell (burgundy soft pills).
	Props: $label, $name, $options (value=>label), $selected (array), $hint, $required, $id, $multiple (default true)
--}}
@php
	$id = $id ?? ($name ?? 'ls-s2m-'.uniqid());
	$options = $options ?? [
		'nonseafood' => 'Nonseafood',
		'micro' => 'Microbiological',
		'chem' => 'Chemical',
	];
	$selected = $selected ?? array_keys($options);
	$multiple = $multiple ?? true;
@endphp
<div class="ls-field">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<div class="ls-select2-multi">
		<select
			id="{{ $id }}"
			name="{{ ($name ?? $id).($multiple ? '[]' : '') }}"
			class="ls-select2-multi-el form-control"
			@if($multiple) multiple @endif
			data-placeholder="{{ $placeholder ?? 'Select…' }}"
			style="width: 100%;"
		>
			@foreach($options as $value => $optLabel)
				<option value="{{ $value }}" @selected(in_array($value, (array) $selected, true))>{{ $optLabel }}</option>
			@endforeach
		</select>
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
