{{--
	ls-field-text — ordinary text / date / time / textarea column.

	Props (prefer ls* keys — parent @include scope can overwrite bare $id/$value/$name/$type):
	  $lsId / $id, $lsName / $name, $lsValue / $value, $lsPlaceholder / $placeholder,
	  $lsType / $type, $hint, $error, $success, $required, $disabled, $readonly, $max, $min,
	  $autocomplete, $inputAttrs, $labelSuffix, $multiline, $rows, $extraFieldClass,
	  $wireModel, $omitName, $disableLocalSuccess, $showClear
--}}
@php
	/**
	 * Parent Blade @include merges get_defined_vars() over explicit data, so a parent
	 * foreach ($x as $value) or $id can clobber include props. Prefer ls* keys.
	 */
	$normalizeLsString = static function (mixed $raw): string {
		if ($raw === null) {
			return '';
		}
		if (is_string($raw) || is_int($raw) || is_float($raw) || is_bool($raw)) {
			return (string) $raw;
		}
		if ($raw instanceof \Stringable) {
			return (string) $raw;
		}
		if ($raw instanceof \Illuminate\Support\Collection) {
			$raw = $raw->all();
		}
		if (is_array($raw) || $raw instanceof \Traversable) {
			return collect($raw)
				->flatten()
				->filter(static fn ($item): bool => is_scalar($item) || $item === null)
				->map(static fn ($item): string => (string) ($item ?? ''))
				->filter(static fn (string $item): bool => $item !== '')
				->implode(', ');
		}

		return '';
	};

	$fieldName = $normalizeLsString($lsName ?? $name ?? '');
	$fieldId = $normalizeLsString($lsId ?? $id ?? ($fieldName !== '' ? $fieldName : null) ?? ('ls-field-'.uniqid()));
	$fieldValue = $normalizeLsString($lsValue ?? $value ?? '');
	$fieldPlaceholder = $normalizeLsString($lsPlaceholder ?? $placeholder ?? '');
	$rawType = $lsType ?? $type ?? 'text';
	$fieldType = is_string($rawType) || is_int($rawType) || is_float($rawType)
		? (string) $rawType
		: 'text';
	if ($fieldType === '') {
		$fieldType = 'text';
	}
	$multiline = ! empty($multiline) || $fieldType === 'textarea';
	$omitName = ! empty($omitName);
	$hasWire = ! empty($wireModel);
	$disableLocalSuccess = (bool) ($disableLocalSuccess ?? false);
	$useLocalSuccess = $hasWire && ! $disableLocalSuccess && ! $multiline;
	$stateClass = ($error ?? null) ? 'is-error' : ((! $useLocalSuccess && ($success ?? null)) ? 'is-success' : '');
	if (! empty($disabled)) {
		$stateClass .= ' is-disabled';
	}
	$fieldClass = trim('ls-field '.($extraFieldClass ?? '').' '.$stateClass);
@endphp
<div
	class="{{ $fieldClass }}"
	@if($useLocalSuccess)
		x-data="{ filled: @js($fieldValue !== '') }"
		:class="{ 'is-success': filled && !@js((bool) ($error ?? null)), 'is-error': @js((bool) ($error ?? null)) }"
	@endif
>
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $fieldId }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
			@if(! empty($labelSuffix))
				{!! $labelSuffix !!}
			@endif
		</label>
	@endif
	<div class="ls-field__control{{ $multiline ? ' ls-field__control--textarea' : '' }}">
		@if($multiline)
			<textarea
				id="{{ $fieldId }}"
				@unless($omitName) name="{{ $fieldName !== '' ? $fieldName : $fieldId }}" @endunless
				class="ls-field__input ls-textarea"
				rows="{{ $rows ?? 3 }}"
				placeholder="{{ $fieldPlaceholder }}"
				@if(! empty($required)) required @endif
				@if(! empty($disabled)) disabled @endif
				@if(! empty($readonly)) readonly @endif
				@if(isset($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
				@if(! empty($inputAttrs)) {!! $inputAttrs !!} @endif
				@if(! empty($wireModel)) wire:model.defer="{{ $wireModel }}" @endif
			>{{ $fieldValue }}</textarea>
		@else
			<input
				type="{{ $fieldType }}"
				id="{{ $fieldId }}"
				@unless($omitName) name="{{ $fieldName !== '' ? $fieldName : $fieldId }}" @endunless
				class="ls-field__input"
				@if(! empty($wireModel))
					wire:model.defer="{{ $wireModel }}"
				@else
					value="{{ $fieldValue }}"
				@endif
				@if($useLocalSuccess)
					@input="filled = !!$event.target.value"
					@change="filled = !!$event.target.value"
				@endif
				placeholder="{{ $fieldPlaceholder }}"
				@if(! empty($required)) required @endif
				@if(! empty($disabled)) disabled @endif
				@if(! empty($readonly)) readonly @endif
				@if(! empty($max)) max="{{ $max }}" @endif
				@if(! empty($min)) min="{{ $min }}" @endif
				@if(isset($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
				@if(! empty($inputAttrs)) {!! $inputAttrs !!} @endif
			>
		@endif
		@if($useLocalSuccess)
			<span class="ls-field__icon-btn" tabindex="-1" aria-hidden="true" x-show="filled && !@js((bool) ($error ?? null))" x-cloak>
				<i class="mdi mdi-check-circle" style="color:#059669;"></i>
			</span>
		@elseif(($error ?? null) || (is_string($success ?? null) && ($success ?? '') !== '') || ! empty($showClear))
			<button type="button" class="ls-field__icon-btn" tabindex="-1" aria-label="Clear">
				@if(is_string($success ?? null) && ($success ?? '') !== '')
					<i class="mdi mdi-check-circle" style="color:#059669;"></i>
				@elseif($error ?? null)
					<i class="mdi mdi-alert-circle" style="color:#dc2626;"></i>
				@else
					<i class="mdi mdi-close"></i>
				@endif
			</button>
		@elseif(! empty($success) && ! $multiline)
			<span class="ls-field__icon-btn" tabindex="-1" aria-hidden="true">
				<i class="mdi mdi-check-circle" style="color:#059669;"></i>
			</span>
		@endif
	</div>
	@if($error ?? null)
		<p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ is_array($error) ? implode(' ', $error) : $error }}</p>
	@elseif(is_string($success ?? null) && ($success ?? '') !== '' && ! $useLocalSuccess)
		<p class="ls-field__msg ls-field__msg--success"><i class="mdi mdi-check-circle-outline"></i> {{ $success }}</p>
	@elseif($hint ?? null)
		<p class="ls-field__hint">{{ is_array($hint) ? implode(' ', $hint) : $hint }}</p>
	@endif
</div>
