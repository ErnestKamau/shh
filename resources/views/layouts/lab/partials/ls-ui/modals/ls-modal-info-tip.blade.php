{{--
	ls-modal-info-tip — educational / tip information modal card.

	Props:
	- $state: ready|loading|empty
	- $eyebrow, $title, $body (string)
	- $lottie (empty state)
--}}
@php
	$state = $state ?? 'ready';
	$eyebrow = $eyebrow ?? 'Lab tip';
	$title = $title ?? 'Put the primary action at the end of the scan path';
	$body = $body ?? 'After reading modal content, users expect the next step at the bottom. Keep dismiss and confirm in the footer — not only in the header.';
	$lottie = $lottie ?? 'https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json';
@endphp
<div class="ls-modal-card" data-ls-modal-type="info-tip" data-ls-modal-state="{{ $state }}">
	<div class="ls-modal-card__header ls-modal-card__header--soft">
		<div>
			<p class="ls-modal-card__eyebrow">{{ $eyebrow }}</p>
			<h3 class="ls-modal-card__title">{{ $title }}</h3>
		</div>
		<button type="button" class="ls-modal-card__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>

	<div class="ls-modal-card__body">
		@if($state === 'loading')
			<div class="ls-modal-skel" aria-busy="true" aria-label="Loading tip">
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.55rem;width:4.5rem;"></span>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.9rem;width:92%;"></span>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.65rem;width:100%;"></span>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.65rem;width:78%;"></span>
				<div class="ls-modal-compare mt-2">
					<div class="ls-modal-skel" style="border:1px solid #e2e8f0;border-radius:10px;padding:0.65rem;">
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.55rem;width:40%;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.85rem;width:70%;margin-top:0.4rem;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:85%;margin-top:0.45rem;"></span>
					</div>
					<div class="ls-modal-skel" style="border:1px solid #e2e8f0;border-radius:10px;padding:0.65rem;">
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.55rem;width:40%;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.85rem;width:70%;margin-top:0.4rem;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:85%;margin-top:0.45rem;"></span>
					</div>
				</div>
			</div>
		@elseif($state === 'empty')
			@include('layouts.lab.partials.ls-ui.modals.ls-modal-empty', [
				'title' => 'No tip selected',
				'text' => 'Pick a lab tip from the gallery or documentation to preview it here.',
				'lottie' => $lottie,
				'fallbackIcon' => 'mdi-lightbulb-outline',
			])
		@else
			<p class="mb-3" style="font-size:0.78rem;color:var(--ls-muted);line-height:1.5;margin:0 0 0.85rem;">{{ $body }}</p>
			<div class="ls-modal-compare">
				<div class="ls-modal-compare__card">
					<span class="ls-modal-compare__badge ls-modal-compare__badge--bad" aria-hidden="true"><i class="mdi mdi-close"></i></span>
					<p class="ls-modal-compare__label">Avoid</p>
					<p class="ls-modal-compare__sample" style="letter-spacing:0;font-weight:500;">ACTION</p>
					<ul class="ls-modal-compare__meta">
						<li>CTA only in header</li>
						<li>Long scan jump back up</li>
					</ul>
				</div>
				<div class="ls-modal-compare__card">
					<span class="ls-modal-compare__badge ls-modal-compare__badge--good" aria-hidden="true"><i class="mdi mdi-check"></i></span>
					<p class="ls-modal-compare__label">Prefer</p>
					<p class="ls-modal-compare__sample" style="letter-spacing:0.08em;font-size:0.72rem;">ACTION</p>
					<ul class="ls-modal-compare__meta">
						<li>CTA in footer</li>
						<li>Ends the reading path</li>
					</ul>
				</div>
			</div>
		@endif
	</div>

	<div class="ls-modal-card__footer">
		@if($state === 'loading')
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn"></span>
		@else
			<button type="button" class="ls-btn">Got it</button>
		@endif
	</div>
</div>
