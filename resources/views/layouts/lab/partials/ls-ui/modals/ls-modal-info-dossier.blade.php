{{--
	ls-modal-info-dossier — read-only detail / dossier information modal.

	Props:
	- $state: ready|loading|empty
	- $title, $subtitle
	- $lottie (empty)
--}}
@php
	$state = $state ?? 'ready';
	$title = $title ?? 'SW-26-0142 · Drinking Water';
	$subtitle = $subtitle ?? 'Sample details from the TRF for this integrity check.';
	$lottie = $lottie ?? 'https://assets2.lottiefiles.com/packages/lf20_qp1q7mct.json';
@endphp
<div class="ls-modal-card ls-modal-card--wide" data-ls-modal-type="info-dossier" data-ls-modal-state="{{ $state }}">
	<div class="ls-modal-card__header">
		<div>
			<h3 class="ls-modal-card__title">{{ $title }}</h3>
			<p class="ls-modal-card__subtitle">{{ $subtitle }}</p>
		</div>
		<button type="button" class="ls-modal-card__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>

	<div class="ls-modal-card__body">
		@if($state === 'loading')
			<div class="ls-modal-skel" aria-busy="true" aria-label="Loading sample details">
				<div class="ls-modal-summary">
					@for($i = 0; $i < 3; $i++)
						<div class="ls-modal-summary__item">
							<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.45rem;width:45%;"></span>
							<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.7rem;width:70%;margin-top:0.35rem;"></span>
						</div>
					@endfor
				</div>
				@for($p = 0; $p < 2; $p++)
					<div class="ls-modal-panel">
						<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.55rem;width:35%;margin-bottom:0.55rem;"></span>
						<div class="ls-modal-kv">
							@for($k = 0; $k < 4; $k++)
								<div>
									<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.45rem;width:50%;"></span>
									<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--block" style="height:0.65rem;width:75%;margin-top:0.3rem;"></span>
								</div>
							@endfor
						</div>
					</div>
				@endfor
			</div>
		@elseif($state === 'empty')
			@include('layouts.lab.partials.ls-ui.modals.ls-modal-empty', [
				'title' => 'No sample details',
				'text' => 'This dossier has no TRF fields to show yet.',
				'lottie' => $lottie,
				'fallbackIcon' => 'mdi-clipboard-text-off-outline',
			])
		@else
			<div class="ls-modal-summary" aria-live="polite">
				<div class="ls-modal-summary__item">
					<span class="ls-modal-summary__label">Condition</span>
					<p class="ls-modal-summary__value">Acceptable</p>
				</div>
				<div class="ls-modal-summary__item">
					<span class="ls-modal-summary__label">Request</span>
					<p class="ls-modal-summary__value">TRFW014/26</p>
				</div>
				<div class="ls-modal-summary__item">
					<span class="ls-modal-summary__label">Form</span>
					<p class="ls-modal-summary__value">Water TRF</p>
				</div>
			</div>

			<div class="ls-modal-panel">
				<h4 class="ls-modal-panel__title">Condition &amp; collection</h4>
				<div class="ls-modal-kv">
					<div>
						<span class="ls-modal-kv__label">Sampling point</span>
						<p class="ls-modal-kv__value">Tap · Kitchen</p>
					</div>
					<div>
						<span class="ls-modal-kv__label">Collected</span>
						<p class="ls-modal-kv__value">21 Aug 2026 · 09:40</p>
					</div>
					<div>
						<span class="ls-modal-kv__label">Temperature</span>
						<p class="ls-modal-kv__value">4.2 °C</p>
					</div>
					<div>
						<span class="ls-modal-kv__label">Qty</span>
						<p class="ls-modal-kv__value">2 × 500 mL</p>
					</div>
				</div>
			</div>

			<div class="ls-modal-panel">
				<h4 class="ls-modal-panel__title">Customer</h4>
				<div class="ls-modal-kv">
					<div>
						<span class="ls-modal-kv__label">Client name</span>
						<p class="ls-modal-kv__value">AmSpec Demo Client</p>
					</div>
					<div>
						<span class="ls-modal-kv__label">Contact</span>
						<p class="ls-modal-kv__value">+254 700 000 000</p>
					</div>
				</div>
			</div>
		@endif
	</div>

	<div class="ls-modal-card__footer ls-modal-card__footer--split">
		@if($state === 'loading')
			<span class="ls-skeleton ls-skeleton--pulse" style="height:0.7rem;width:5rem;"></span>
			<span class="ls-skeleton ls-skeleton--pulse ls-skeleton--btn"></span>
		@else
			<span class="small text-muted" style="font-size:0.7rem;">Read only</span>
			<button type="button" class="ls-btn">Close</button>
		@endif
	</div>
</div>
