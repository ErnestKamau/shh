{{--
	ls-field-option-chips — TRF-style checkbox/radio option tiles (Edit request details).
	Props: $label, $name, $type (checkbox|radio), $options (value=>label), $selected (string|array), $columns (transport|method|apparatus|3|auto),
	       $disabled (bool — read-only view)
--}}
@php
	$id = $id ?? ($name ?? 'ls-opts-'.uniqid());
	$type = $type ?? 'checkbox';
	$columns = $columns ?? 'auto';
	$options = $options ?? [];
	$selected = $selected ?? ($type === 'checkbox' ? [] : null);
	$selectedList = is_array($selected) ? $selected : (($selected !== null && $selected !== '') ? [(string) $selected] : []);
	$gridMod = match ($columns) {
		'transport' => 'ls-option-grid--transport',
		'method' => 'ls-option-grid--method',
		'apparatus' => 'ls-option-grid--apparatus',
		'3' => 'ls-option-grid--3',
		default => 'ls-option-grid--auto',
	};
	$isDisabled = ! empty($disabled);
@endphp
<div class="ls-field {{ $isDisabled ? 'is-disabled' : '' }}">
	@if(! empty($label))
		<span class="ls-field__label">
			@if(! empty($icon))
				<i class="mdi {{ $icon }}" aria-hidden="true"></i>
			@endif
			{{ $label }}
		</span>
	@endif
	<div class="ls-option-grid {{ $gridMod }} {{ $isDisabled ? 'ls-option-grid--readonly' : '' }}" role="group" aria-label="{{ $label ?? $name }}">
		@foreach($options as $value => $optLabel)
			@php
				$optValue = is_int($value) ? (string) $optLabel : (string) $value;
				$optText = is_array($optLabel) ? (string) ($optLabel['label'] ?? $optValue) : (string) $optLabel;
				$isChecked = in_array($optValue, $selectedList, true) || in_array($optText, $selectedList, true);
			@endphp
			<label class="ls-option-chip {{ $isChecked ? 'is-checked' : '' }} {{ $isDisabled && ! $isChecked ? 'is-muted' : '' }}">
				<input
					type="{{ $type }}"
					class="ls-option-chip__input"
					name="{{ $type === 'checkbox' ? ($name ?? $id).'[]' : ($name ?? $id) }}"
					value="{{ $optValue }}"
					@checked($isChecked)
					@if($isDisabled) disabled @endif
				>
				<span class="ls-option-chip__label">{{ $optText }}</span>
			</label>
		@endforeach
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
