{{--
	ls-field-select — ordinary <select> column matching the ls-field-text visual shell.
	Props: $label, $id, $name, $wireModel, $placeholder, $options (list of [value,label] or value=>label),
	       $selected, $hint, $error, $success, $required, $disabled
--}}
@php
	$fieldId = $id ?? ($name ?? 'ls-select-'.uniqid());
	$fieldName = $name ?? $fieldId;
	$fieldOptions = collect($options ?? [])
		->map(function ($option, $key) {
			if (is_array($option)) {
				return ['value' => (string) ($option['value'] ?? ''), 'label' => (string) ($option['label'] ?? '')];
			}

			return ['value' => (string) $key, 'label' => (string) $option];
		})
		->values();
	$selectedValue = (string) ($selected ?? '');
	$stateClass = ($error ?? null) ? 'is-error' : ((! empty($success)) ? 'is-success' : '');
	if (! empty($disabled)) {
		$stateClass .= ' is-disabled';
	}
	$fieldClass = trim('ls-field '.($extraFieldClass ?? '').' '.$stateClass);
@endphp
<div class="{{ $fieldClass }}">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $fieldId }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<div class="ls-field__control">
		<select
			id="{{ $fieldId }}"
			name="{{ $fieldName }}"
			class="ls-field__input"
			@if(! empty($wireModel))
				wire:model.defer="{{ $wireModel }}"
			@endif
			@if(! empty($required)) required @endif
			@if(! empty($disabled)) disabled @endif
		>
			<option value="" @selected($selectedValue === '')>{{ $placeholder ?? 'Select…' }}</option>
			@foreach($fieldOptions as $option)
				<option value="{{ $option['value'] }}" @selected($option['value'] === $selectedValue)>{{ $option['label'] }}</option>
			@endforeach
		</select>
		@if(! empty($success))
			<span class="ls-field__icon-btn" tabindex="-1" aria-hidden="true">
				<i class="mdi mdi-check-circle" style="color:#059669;"></i>
			</span>
		@elseif($error ?? null)
			<span class="ls-field__icon-btn" tabindex="-1" aria-hidden="true">
				<i class="mdi mdi-alert-circle" style="color:#dc2626;"></i>
			</span>
		@endif
	</div>
	@if($error ?? null)
		<p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ is_array($error) ? implode(' ', $error) : $error }}</p>
	@elseif($hint ?? null)
		<p class="ls-field__hint">{{ is_array($hint) ? implode(' ', $hint) : $hint }}</p>
	@endif
</div>
