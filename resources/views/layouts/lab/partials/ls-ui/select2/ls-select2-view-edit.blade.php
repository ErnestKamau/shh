{{--
	ls-select2-view-edit — view chips vs editable multi Select2 (no avatars).
	Props: $label, $mode (view|edit), $options, $selected, $name, $id
--}}
@php
	$id = $id ?? 'ls-s2ve-'.uniqid();
	$mode = $mode ?? 'edit';
	$options = $options ?? [
		'insan' => 'Insan Kamil',
		'john' => 'John Maleo',
		'jacob' => 'Jacob Kowalski',
	];
	$selected = $selected ?? array_keys($options);
@endphp
<div class="ls-field ls-compact" x-data="{ mode: @js($mode) }">
	<div class="d-flex align-items-center justify-content-between mb-1">
		@if(! empty($label))
			<label class="ls-field__label mb-0" for="{{ $id }}">{{ $label }}</label>
		@endif
		<button type="button" class="ls-btn" style="padding:0.2rem 0.5rem;font-size:0.65rem;" @click="mode = mode === 'view' ? 'edit' : 'view'" x-text="mode === 'view' ? 'Edit' : 'View'"></button>
	</div>
	<div x-show="mode === 'view'" class="ls-select2-view">
		@foreach($selected as $value)
			@if(isset($options[$value]))
				<span class="ls-select2-view__chip">{{ $options[$value] }}</span>
			@endif
		@endforeach
	</div>
	<div x-show="mode === 'edit'" x-cloak>
		<div class="ls-select2-multi ls-select2-slate">
			<select
				id="{{ $id }}"
				name="{{ ($name ?? $id) }}[]"
				class="ls-select2-view-edit-el form-control"
				multiple
				data-placeholder="Select…"
				style="width: 100%;"
			>
				@foreach($options as $value => $optLabel)
					<option value="{{ $value }}" @selected(in_array($value, (array) $selected, true))>{{ $optLabel }}</option>
				@endforeach
			</select>
		</div>
	</div>
</div>
