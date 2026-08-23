{{--
	ls-modal-cta-confirm — choice / confirm CTA modal (collection-style).

	Props: $state ready|loading|empty, $title, $lottie
--}}
@php
	$state = $state ?? 'ready';
	$title = $title ?? 'Issue lab section worksheet';
	$lottie = $lottie ?? 'https://assets9.lottiefiles.com/packages/lf20_u4yrau.json';
@endphp
<div
	class="ls-modal-card"
	data-ls-modal-type="cta-confirm"
	data-ls-modal-state="{{ $state }}"
	x-data="{ privateOn: true, contrib: 'controlled' }"
>
	<div class="ls-modal-card__header">
		<div>
			<h3 class="ls-modal-card__title">{{ $title }}</h3>
			<p class="ls-modal-card__subtitle">Choose visibility and contribution rules, then confirm.</p>
		</div>
		<button type="button" class="ls-modal-card__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>

	<div class="ls-modal-card__body">
		@if($state === 'loading')
			<div class="ls-modal-skel" aria-busy="true" aria-label="Loading options">
				<div class="ls-modal-skel__row" style="justify-content:space-between;">
					<div class="ls-modal-skel__stack">
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.7rem;width:5rem;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:11rem;"></span>
					</div>
					<span class="ls-skeleton ls-skeleton--pulse" style="width:2.5rem;height:1.35rem;border-radius:999px;"></span>
				</div>
				<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.55rem;width:7rem;margin-top:0.35rem;"></span>
				@for($i = 0; $i < 2; $i++)
					<div style="border:1px solid #e2e8f0;border-radius:10px;padding:0.65rem;margin-top:0.35rem;">
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.7rem;width:40%;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:90%;margin-top:0.35rem;"></span>
					</div>
				@endfor
			</div>
		@elseif($state === 'empty')
			@include('layouts.lab.partials.ls-ui.modals.ls-modal-empty', [
				'title' => 'Select a batch first',
				'text' => 'Worksheet issue needs an open batch with assigned samples.',
				'lottie' => $lottie,
				'fallbackIcon' => 'mdi-package-variant-closed',
				'actionLabel' => 'Open workflow board',
				'actionClass' => 'ls-btn',
			])
		@else
			<div class="ls-card-collection__row" style="padding-top:0;">
				<div>
					<p class="ls-card-collection__row-title">Section-only</p>
					<p class="ls-card-collection__row-sub">Visible only to the assigned lab section.</p>
				</div>
				<button type="button" class="ls-toggle" :class="{ 'is-on': privateOn }" @click="privateOn = !privateOn" :aria-pressed="privateOn.toString()"></button>
			</div>
			<p class="ls-card-collection__section">Assignment mode</p>
			<button type="button" class="ls-choice-card" :class="{ 'is-selected': contrib === 'controlled' }" @click="contrib = 'controlled'">
				<p class="ls-choice-card__title">Controlled</p>
				<p class="ls-choice-card__body">Only analysts listed on the worksheet can enter results.</p>
			</button>
			<button type="button" class="ls-choice-card" :class="{ 'is-selected': contrib === 'open' }" @click="contrib = 'open'">
				<p class="ls-choice-card__title">Open section</p>
				<p class="ls-choice-card__body">Any member of the lab section can contribute results.</p>
			</button>
		@endif
	</div>

	<div class="ls-modal-card__footer">
		@if($state === 'loading')
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn"></span>
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn" style="width:7rem;"></span>
		@elseif($state === 'empty')
			<button type="button" class="ls-btn">Cancel</button>
			<button type="button" class="ls-btn ls-btn--accent" disabled>Confirm</button>
		@else
			<button type="button" class="ls-btn">Cancel</button>
			<button type="button" class="ls-btn ls-btn--accent">Confirm issue</button>
		@endif
	</div>
</div>
