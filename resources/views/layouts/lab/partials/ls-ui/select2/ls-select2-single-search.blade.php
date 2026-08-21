{{--
	ls-select2-single-search — Select2 single searchable shell.
	Props: $label, $name, $options, $selected, $hint, $id
--}}
@php
	$id = $id ?? ($name ?? 'ls-s2s-'.uniqid());
	$options = $options ?? [
		'demi' => 'Demi Steiner',
		'candice' => 'Candice Cano',
		'alex' => 'Alex Rivera',
	];
@endphp
<div class="ls-field">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">{{ $label }}</label>
	@endif
	<div class="ls-select2-single ls-select2-single-wrap">
		<i class="mdi mdi-magnify" aria-hidden="true"></i>
		<select
			id="{{ $id }}"
			name="{{ $name ?? $id }}"
			class="ls-select2-single-el form-control"
			data-placeholder="{{ $placeholder ?? 'Search…' }}"
			style="width: 100%;"
		>
			<option value=""></option>
			@foreach($options as $value => $optLabel)
				<option value="{{ $value }}" @selected(($selected ?? null) === $value)>{{ $optLabel }}</option>
			@endforeach
		</select>
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
