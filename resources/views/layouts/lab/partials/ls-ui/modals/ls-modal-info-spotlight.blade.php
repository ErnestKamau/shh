{{--
	ls-modal-info-spotlight — status / empty spotlight information modal.

	Ready state is intentionally illustration-led (empty-friendly).
	Props: $state ready|loading|empty, $title, $text, $lottie
--}}
@php
	$state = $state ?? 'ready';
	$title = $title ?? 'No worksheets issued yet';
	$text = $text ?? 'When samples are assigned to a lab section, worksheets will appear here.';
	$lottie = $lottie ?? 'https://assets9.lottiefiles.com/packages/lf20_u4yrau.json';
	$emptyTitle = $emptyTitle ?? 'Nothing to highlight';
	$emptyText = $emptyText ?? 'Open this modal when you have a status message or empty queue to show.';
@endphp
<div class="ls-modal-card" data-ls-modal-type="info-spotlight" data-ls-modal-state="{{ $state }}">
	<div class="ls-modal-card__header ls-modal-card__header--soft">
		<div>
			<p class="ls-modal-card__eyebrow">Status</p>
			<h3 class="ls-modal-card__title">Lab section queue</h3>
		</div>
		<button type="button" class="ls-modal-card__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>

	<div class="ls-modal-card__body">
		@if($state === 'loading')
			<div class="ls-modal-empty" aria-busy="true" aria-label="Loading">
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--circle" style="width:5.5rem;height:5.5rem;min-width:5.5rem;"></span>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.85rem;width:12rem;margin-top:0.75rem;"></span>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.6rem;width:16rem;margin-top:0.35rem;"></span>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn" style="margin-top:0.85rem;width:7rem;"></span>
			</div>
		@elseif($state === 'empty')
			@include('layouts.lab.partials.ls-ui.modals.ls-modal-empty', [
				'title' => $emptyTitle,
				'text' => $emptyText,
				'lottie' => $lottie,
				'fallbackIcon' => 'mdi-inbox-outline',
			])
		@else
			<div class="ls-modal-spotlight-hero">
				<div
					class="ls-modal-empty__lottie"
					data-ls-lottie="{{ $lottie }}"
					aria-hidden="true"
				>
					<span class="ls-modal-empty__fallback" data-ls-lottie-fallback>
						<i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i>
					</span>
				</div>
			</div>
			<div class="ls-modal-spotlight-copy">
				<h4>{{ $title }}</h4>
				<p>{{ $text }}</p>
			</div>
		@endif
	</div>

	<div class="ls-modal-card__footer" style="justify-content:center;">
		@if($state === 'loading')
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn" style="width:8rem;"></span>
		@elseif($state === 'empty')
			<button type="button" class="ls-btn">Close</button>
		@else
			<button type="button" class="ls-btn">Browse samples</button>
			<button type="button" class="ls-btn ls-btn--accent">Got it</button>
		@endif
	</div>
</div>
