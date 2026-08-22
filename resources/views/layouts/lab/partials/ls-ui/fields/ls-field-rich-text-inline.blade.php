{{--
	ls-field-rich-text-inline — full-column rich text (card / form rows).
	Props: $label, $name, $id, $value (HTML), $hint, $error, $required, $disabled,
	       $compact (bool), $wireModel, $editorId, $placeholder
	Demo: omit $wireModel — renders static chrome (gallery). With $wireModel + $editorId, wire TinyMCE via Alpine.
--}}
@php
	$id = $id ?? ($name ?? 'ls-rich-'.uniqid());
	$editorId = $editorId ?? ($id.'-editor');
	$html = (string) ($value ?? '<p>Composite sample from line 3 — retain cold chain notes and batch reference on label.</p>');
	$isLive = ! empty($wireModel);
	$stateClass = ($error ?? null) ? 'is-error' : '';
	if (! empty($disabled)) {
		$stateClass .= ' is-disabled';
	}
	if (! empty($compact)) {
		$stateClass .= ' ls-rich-text--compact';
	}
@endphp
<div class="ls-field ls-rich-text ls-rich-text--inline {{ trim($stateClass) }}">
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $editorId }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif

	<div class="ls-rich-text__shell" @if($isLive) wire:ignore @endif>
		@if($isLive)
			<div
				x-data="lsRichTextInline({
					editorId: @js($editorId),
					wireKey: @js($wireModel),
				})"
				x-init="mount()"
				x-on:trf-destroy-editors.window="destroy()"
			>
				<textarea
					id="{{ $editorId }}"
					name="{{ $name ?? $id }}"
					class="ls-rich-text__textarea"
					placeholder="{{ $placeholder ?? '' }}"
				>{!! $html !!}</textarea>
			</div>
		@else
			<div class="ls-rich-text__toolbar" role="toolbar" aria-label="Formatting">
				<button type="button" class="ls-rich-text__tool is-active" title="Bold" disabled><i class="mdi mdi-format-bold"></i></button>
				<button type="button" class="ls-rich-text__tool" title="Italic" disabled><i class="mdi mdi-format-italic"></i></button>
				<button type="button" class="ls-rich-text__tool" title="Underline" disabled><i class="mdi mdi-format-underline"></i></button>
				<span class="ls-rich-text__tool-sep" aria-hidden="true"></span>
				<button type="button" class="ls-rich-text__tool" title="Bullet list" disabled><i class="mdi mdi-format-list-bulleted"></i></button>
				<button type="button" class="ls-rich-text__tool" title="Numbered list" disabled><i class="mdi mdi-format-list-numbered"></i></button>
			</div>
			<div
				class="ls-rich-text__body"
				id="{{ $editorId }}"
				contenteditable="true"
				role="textbox"
				aria-multiline="true"
				data-placeholder="{{ $placeholder ?? 'Describe the sample…' }}"
			>{!! $html !!}</div>
		@endif
	</div>

	@if($error ?? null)
		<p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $error }}</p>
	@elseif($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
