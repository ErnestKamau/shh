{{--
	Quotation prep — pricelist picker (append lines on select).
	Expects: $header, $pricelistChooser (AcceptanceFormPricingService::buildPricelistChooserPayload)
--}}
@php
	$chooser = $pricelistChooser ?? [
		'needs_choice' => false,
		'selected_pricelist_id' => $header->pricelist_id ? (string) $header->pricelist_id : null,
		'pricelists' => [],
	];
	$selectedPricelistId = (string) ($chooser['selected_pricelist_id'] ?? '');
	$needsChoice = (bool) ($chooser['needs_choice'] ?? false);
	$selectedPricelistCard = null;
	foreach ($chooser['pricelists'] as $card) {
		if ($selectedPricelistId !== '' && $selectedPricelistId === (string) $card['id']) {
			$selectedPricelistCard = $card;
			break;
		}
	}
	$lineCount = is_countable($details ?? null) ? count($details) : 0;
	$activeBillingMode = $selectedPricelistCard['billing_mode'] ?? 'per_package';
	$activeBillingLabel = $selectedPricelistCard['billing_mode_label'] ?? 'Per package';
@endphp

<div
	class="ls-quote-commercial-chooser"
	id="ls-quote-commercial-chooser"
	data-quotation-id="{{ $header->id }}"
	data-select-pricelist-url="{{ route('quotation.select_pricelist', ['id' => $header->id]) }}"
	data-detach-pricelist-url="{{ route('quotation.detach_pricelist', ['id' => $header->id]) }}"
	data-eligible-url="{{ route('quotation.eligible_pricelists', ['id' => $header->id]) }}"
	data-selected-pricelist="{{ $selectedPricelistId }}"
	data-needs-choice="{{ $needsChoice ? '1' : '0' }}"
	data-pricelist-billing-mode="{{ $activeBillingMode === 'per_test' ? 'per_test' : 'per_package' }}"
>
	<div class="ls-quote-commercial-chooser__intro">
		<p class="ls-quote-commercial-chooser__lede mb-0">
			Choose a customer pricelist to append quotation lines. Lines are added — existing rows are kept.
			@if($needsChoice)
				<span class="ls-quote-commercial-chooser__nudge ls-motion-shake" title="Choose which pricelist to use">
					<i class="mdi mdi-gesture-tap"></i> Pick a pricelist
				</span>
			@endif
		</p>
	</div>

	<div class="ls-quote-commercial-summary" aria-live="polite">
		<div class="ls-quote-commercial-summary__item">
			<span class="ls-quote-commercial-summary__label">List mode</span>
			<strong class="ls-quote-commercial-summary__value" id="ls-quote-commercial-summary-mode">
				{{ $selectedPricelistCard ? $activeBillingLabel : '—' }}
			</strong>
		</div>
		<div class="ls-quote-commercial-summary__item">
			<span class="ls-quote-commercial-summary__label">Pricelist</span>
			<strong class="ls-quote-commercial-summary__value" id="ls-quote-commercial-summary-pricelist">
				@if($selectedPricelistCard)
					{{ $selectedPricelistCard['code'] !== '' ? $selectedPricelistCard['code'] : 'Selected' }}
				@elseif($needsChoice)
					Not selected
				@else
					—
				@endif
			</strong>
		</div>
		<div class="ls-quote-commercial-summary__item">
			<span class="ls-quote-commercial-summary__label">Lines</span>
			<strong class="ls-quote-commercial-summary__value">{{ $lineCount }}</strong>
		</div>
	</div>

	<div class="ls-quote-commercial-chooser__section">
		<p class="ls-field__label mb-1">
			Pricelist
			@if($needsChoice)
				<span class="text-danger">*</span>
			@endif
			<button
				type="button"
				class="ls-quote-commercial-info"
				data-toggle="tooltip"
				data-placement="top"
				title="Package lists add one line per package. Per-test lists add one line per parameter."
				aria-label="Pricelist help"
			>
				<i class="mdi mdi-information-outline" aria-hidden="true"></i>
			</button>
		</p>
		<p class="ls-quote-commercial-chooser__section-hint">Eligible lists for this customer. Selecting appends lines and binds this quotation to the list.</p>
		@if(empty($chooser['pricelists']))
			<div class="ls-quote-pricelist-empty">
				<span class="ls-icon-tile__glyph ls-icon--amber ls-motion-pulse">
					<i class="mdi mdi-alert-decagram" aria-hidden="true"></i>
				</span>
				<div>
					<strong>No eligible pricelist</strong>
					<p class="mb-0 text-muted small">Assign a pricelist to this customer under Billing → Pricelists.</p>
				</div>
			</div>
		@else
			<div class="ls-icon-grid ls-quote-pricelist-grid" role="radiogroup" aria-label="Pricelist">
				@foreach($chooser['pricelists'] as $card)
					@php
						$isSelected = $selectedPricelistId !== '' && $selectedPricelistId === (string) $card['id'];
						$modeLabel = (string) ($card['billing_mode_label'] ?? 'Per package');
						$pricelistTip = trim(
							($card['description'] ?: $card['code'])
							.' · '.$modeLabel
							.' · '.(int) ($card['item_count'] ?? 0).' items'
						);
					@endphp
					<button
						type="button"
						class="ls-icon-tile ls-quote-pricelist-tile {{ $isSelected ? 'is-active' : '' }} {{ $needsChoice && ! $isSelected ? 'ls-motion-shake-soft' : '' }}"
						data-pricelist-id="{{ $card['id'] }}"
						data-pricelist-code="{{ $card['code'] }}"
						data-billing-mode="{{ $card['billing_mode'] ?? 'package' }}"
						data-toggle="tooltip"
						data-placement="top"
						title="{{ $pricelistTip }}"
						role="radio"
						aria-checked="{{ $isSelected ? 'true' : 'false' }}"
					>
						<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg {{ $card['is_master'] ? 'ls-icon--slate' : 'ls-icon--green' }}">
							<i class="mdi {{ $isSelected ? 'mdi-check-decagram ls-motion-check' : 'mdi-format-list-bulleted-type' }}" aria-hidden="true"></i>
						</span>
						<span class="ls-icon-tile__label">{{ $card['code'] !== '' ? $card['code'] : 'Pricelist' }}</span>
						<span class="ls-icon-tile__name">
							{{ $modeLabel }}
							@if($card['currency'] !== '') · {{ $card['currency'] }}@endif
							· {{ (int) $card['item_count'] }} items
							@if((int) ($card['sample_type_count'] ?? 0) > 0)
								· {{ (int) $card['sample_type_count'] }} sample types
							@endif
						</span>
					</button>
				@endforeach
			</div>
			<p class="ls-quote-pricelist-status small text-muted mb-0 mt-2" id="ls-quote-pricelist-status" aria-live="polite">
				@if($selectedPricelistCard)
					Using {{ $selectedPricelistCard['code'] }} ({{ $activeBillingLabel }})@if(!empty($selectedPricelistCard['description'])) — {{ $selectedPricelistCard['description'] }}@endif.
				@endif
			</p>
		@endif
	</div>

	@if($selectedPricelistId !== '')
		<div class="ls-quote-commercial-chooser__section mt-2">
			<button type="button" class="btn btn-sm btn-outline-secondary" id="ls-quote-detach-pricelist">
				<i class="mdi mdi-link-variant-off"></i> Detach pricelist (manual pricing)
			</button>
		</div>
	@endif

	<ul class="ls-quote-commercial-notes mb-0">
		<li>Package lists: one quotation line per package (samples × unit price once).</li>
		<li>Per-test lists: one quotation line per test row from the list.</li>
		<li>While a pricelist is bound, new lines follow the list billing mode.</li>
	</ul>
</div>
