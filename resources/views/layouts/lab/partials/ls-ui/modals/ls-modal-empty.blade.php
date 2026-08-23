{{--
	ls-modal-empty — centered empty state with optional Lottie.

	Props:
	- $title (string)
	- $text (string)
	- $lottie (string|null) — JSON animation URL
	- $fallbackIcon (string) — mdi class without "mdi " prefix, default mdi-flask-empty-outline
	- $actionLabel (string|null)
	- $actionClass (string)
--}}
@php
	$title = $title ?? 'Nothing here yet';
	$text = $text ?? 'Check back later or try another filter.';
	$lottie = $lottie ?? null;
	$fallbackIcon = $fallbackIcon ?? 'mdi-flask-empty-outline';
	$actionLabel = $actionLabel ?? null;
	$actionClass = $actionClass ?? 'ls-btn';
@endphp
<div class="ls-modal-empty">
	@if($lottie)
		<div
			class="ls-modal-empty__lottie"
			data-ls-lottie="{{ $lottie }}"
			aria-hidden="true"
		>
			<span class="ls-modal-empty__fallback" data-ls-lottie-fallback>
				<i class="mdi {{ $fallbackIcon }}" aria-hidden="true"></i>
			</span>
		</div>
	@else
		<span class="ls-modal-empty__fallback" aria-hidden="true">
			<i class="mdi {{ $fallbackIcon }}" aria-hidden="true"></i>
		</span>
	@endif
	<p class="ls-modal-empty__title">{{ $title }}</p>
	<p class="ls-modal-empty__text">{{ $text }}</p>
	@if($actionLabel)
		<div class="ls-modal-empty__action">
			<button type="button" class="{{ $actionClass }}">{{ $actionLabel }}</button>
		</div>
	@endif
</div>
