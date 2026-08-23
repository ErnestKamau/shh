{{--
	ls-modal-cta-form — configure + submit CTA modal (import / parameters style).

	Props: $state ready|loading|empty, $title, $subtitle, $lottie
--}}
@php
	$state = $state ?? 'ready';
	$title = $title ?? 'Configure worksheet parameters';
	$subtitle = $subtitle ?? 'Set file type and pricing mode, then save.';
	$lottie = $lottie ?? 'https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json';
@endphp
<div
	class="ls-modal-card ls-modal-card--wide"
	data-ls-modal-type="cta-form"
	data-ls-modal-state="{{ $state }}"
	x-data="{ format: 'excel', pricing: 'per_package' }"
>
	<div class="ls-modal-card__header">
		<div>
			<h3 class="ls-modal-card__title">{{ $title }}</h3>
			<p class="ls-modal-card__subtitle">{{ $subtitle }}</p>
		</div>
		<button type="button" class="ls-modal-card__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>

	<div class="ls-modal-card__body">
		@if($state === 'loading')
			<div class="ls-modal-skel" aria-busy="true" aria-label="Loading form">
				<div class="ls-modal-callout" style="pointer-events:none;">
					<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--circle" style="width:2.25rem;height:2.25rem;min-width:2.25rem;"></span>
					<div class="ls-modal-skel__stack">
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.7rem;width:55%;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:95%;"></span>
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:70%;"></span>
					</div>
				</div>
				<div class="ls-modal-field-grid">
					@for($i = 0; $i < 4; $i++)
						<div>
							<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.5rem;width:40%;margin-bottom:0.35rem;"></span>
							<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:2rem;border-radius:8px;"></span>
						</div>
					@endfor
				</div>
			</div>
		@elseif($state === 'empty')
			@include('layouts.lab.partials.ls-ui.modals.ls-modal-empty', [
				'title' => 'No parameters available',
				'text' => 'Add analytes to this line item before configuring LOQ, MU, or TAT.',
				'lottie' => $lottie,
				'fallbackIcon' => 'mdi-tune-variant',
			])
		@else
			<div class="ls-modal-callout">
				<span class="ls-modal-callout__icon" aria-hidden="true"><i class="mdi mdi-information-outline"></i></span>
				<div>
					<p class="ls-modal-callout__title">Template guide</p>
					<p class="ls-modal-callout__text">
						Package total = No. of samples × Unit price (once) — not × number of tests.
						Names must match LIMS sample types and parameters.
					</p>
				</div>
			</div>

			<div class="ls-modal-field-grid">
				<div class="ls-field ls-compact">
					<span class="ls-field__label">File type</span>
					<div class="ls-seg" role="group" aria-label="File type">
						<button type="button" :class="{ 'is-active': format === 'excel' }" @click="format = 'excel'">Excel</button>
						<button type="button" :class="{ 'is-active': format === 'pdf' }" @click="format = 'pdf'">PDF</button>
					</div>
				</div>
				<div class="ls-field ls-compact">
					<span class="ls-field__label">Pricing mode</span>
					<div class="ls-seg" role="group" aria-label="Pricing mode">
						<button type="button" :class="{ 'is-active': pricing === 'per_package' }" @click="pricing = 'per_package'">Package</button>
						<button type="button" :class="{ 'is-active': pricing === 'per_test' }" @click="pricing = 'per_test'">Per test</button>
					</div>
				</div>
				<div class="ls-field ls-compact">
					<label class="ls-field__label" for="ls-modal-cta-section">Lab section</label>
					<input id="ls-modal-cta-section" class="form-control form-control-sm" type="text" value="Microbiology" readonly>
				</div>
				<div class="ls-field ls-compact">
					<label class="ls-field__label" for="ls-modal-cta-tat">Default TAT (days)</label>
					<input id="ls-modal-cta-tat" class="form-control form-control-sm" type="number" value="5" min="1">
				</div>
				<div class="ls-field ls-compact ls-span-2">
					<label class="ls-field__label" for="ls-modal-cta-notes">Notes</label>
					<textarea id="ls-modal-cta-notes" class="form-control form-control-sm" rows="2" placeholder="Optional handoff notes…"></textarea>
				</div>
			</div>

			<div class="ls-modal-warn" role="status">
				<i class="mdi mdi-alert-outline" aria-hidden="true"></i>
				<span>Select start date / TAT before issuing so section queues stay predictable.</span>
			</div>
		@endif
	</div>

	<div class="ls-modal-card__footer">
		@if($state === 'loading')
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn"></span>
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn" style="width:6.5rem;"></span>
		@elseif($state === 'empty')
			<button type="button" class="ls-btn">Cancel</button>
		@else
			<button type="button" class="ls-btn">Cancel</button>
			<button type="button" class="ls-btn ls-btn--accent"><i class="mdi mdi-content-save-outline"></i> Save</button>
		@endif
	</div>
</div>
