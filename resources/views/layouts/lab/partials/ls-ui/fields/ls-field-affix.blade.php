{{--
	ls-field-affix — text input with prefix and/or suffix slots.
	Props: $label, $name, $value, $placeholder, $hint, $required, $prefix, $suffix, $suffixSelect, $actionLabel, $id
--}}
@php
	$id = $id ?? ($name ?? 'ls-affix-'.uniqid());
@endphp
<div class="ls-field">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<div class="ls-field__control">
		@if(! empty($prefix))
			<span class="ls-field__affix ls-field__affix--prefix">{!! $prefix !!}</span>
		@endif
		<input
			type="{{ $type ?? 'text' }}"
			id="{{ $id }}"
			name="{{ $name ?? $id }}"
			class="ls-field__input"
			value="{{ $value ?? '' }}"
			placeholder="{{ $placeholder ?? '' }}"
		>
		@if(! empty($suffixSelect) && is_array($suffixSelect))
			<span class="ls-field__affix ls-field__affix--suffix">
				<select name="{{ ($name ?? $id).'_unit' }}" aria-label="Unit">
					@foreach($suffixSelect as $optValue => $optLabel)
						<option value="{{ is_int($optValue) ? $optLabel : $optValue }}">{{ $optLabel }}</option>
					@endforeach
				</select>
			</span>
		@elseif(! empty($actionLabel))
			<button type="button" class="ls-field__action-btn">{{ $actionLabel }}</button>
		@elseif(! empty($suffix))
			<span class="ls-field__affix ls-field__affix--suffix">{!! $suffix !!}</span>
		@endif
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
