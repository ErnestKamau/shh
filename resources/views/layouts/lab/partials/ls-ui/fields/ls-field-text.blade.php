{{--
	ls-field-text — ordinary text input column.
	Props: $label, $name, $value, $placeholder, $hint, $error, $success, $required, $disabled, $type, $id
--}}
@php
	$id = $id ?? ($name ?? 'ls-field-'.uniqid());
	$type = $type ?? 'text';
	$stateClass = ($error ?? null) ? 'is-error' : (($success ?? null) ? 'is-success' : '');
	if (! empty($disabled)) {
		$stateClass .= ' is-disabled';
	}
@endphp
<div class="ls-field {{ $stateClass }}">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<div class="ls-field__control">
		<input
			type="{{ $type }}"
			id="{{ $id }}"
			name="{{ $name ?? $id }}"
			class="ls-field__input"
			value="{{ $value ?? '' }}"
			placeholder="{{ $placeholder ?? '' }}"
			@if(! empty($disabled)) disabled @endif
		>
		@if(($error ?? null) || ($success ?? null) || ! empty($showClear))
			<button type="button" class="ls-field__icon-btn" tabindex="-1" aria-label="Clear">
				@if($success ?? null)
					<i class="mdi mdi-check-circle" style="color:#059669;"></i>
				@elseif($error ?? null)
					<i class="mdi mdi-alert-circle" style="color:#dc2626;"></i>
				@else
					<i class="mdi mdi-close"></i>
				@endif
			</button>
		@endif
	</div>
	@if($error ?? null)
		<p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $error }}</p>
	@elseif($success ?? null)
		<p class="ls-field__msg ls-field__msg--success"><i class="mdi mdi-check-circle-outline"></i> {{ $success }}</p>
	@elseif($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
