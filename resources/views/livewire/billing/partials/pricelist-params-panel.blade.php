{{--
	Livewire params picker for pricelist item modal.
	Shares visual language with quotation-params-modal-panel (ls-quote-params-*).

	Amspec: this pricelist stores the price for ONE sample.
	On a quotation, total = number of samples × this unit price (once for a package).
--}}
@php
	$isPackage = ! empty($itemForm['is_package']);
	$rows = collect($itemElementRows ?? [])
		->map(fn (array $row, int $index): array => array_merge($row, ['_index' => $index]));
	$grouped = $rows->groupBy(fn (array $r): string => (string) ($r['analysis_type_name'] ?? 'Analysis'));
@endphp
<div class="ls-quote-params-shell pricelist-params-shell">
	<div class="ls-quote-params-hero">
		<div class="ls-quote-params-hero__copy">
			<p class="ls-quote-params-hero__title">
				{{ $isPackage ? 'Choose tests in this package' : 'Choose tests to price' }}
			</p>
			<p class="ls-quote-params-hero__caption">
				@if($isPackage)
					Tick every test that belongs in this package. Set one cost and one selling price for the whole package (price for a single sample). On a quotation, the line total is: number of samples × this package price — not per test. Green badges = lab section. When VAT is ticked, the rate comes from the active tax on the Tax Regime Manager page. Package TAT is the longest turnaround among the tests you tick.
				@else
					Tick the tests to add or update on this pricelist. Enter Costprice and Sellingprice for each ticked test. Prices here are for one sample; on a quotation the total is number of samples × that unit price. Green badges = lab section. When VAT is ticked, the rate comes from the active tax on the Tax Regime Manager page.
				@endif
			</p>
		</div>
		<button
			type="button"
			class="pricelist-params-help"
			title="{{ $isPackage
				? 'Pricelist = price for 1 sample package. Quotation total = samples × package price. Tests you tick are what the package covers — they are not billed separately.'
				: 'Pricelist = price for 1 sample for each test. Quotation total for that test = samples × Sellingprice. Only ticked tests are saved.' }}"
			aria-label="Pricing help"
		>
			<i class="mdi mdi-information-outline"></i>
			<span>How pricing works</span>
		</button>
	</div>

	<div class="ls-quote-params-toolbar">
		<label class="ls-quote-params-switch">
			<input type="checkbox"
				class="ls-quote-params-switch__input"
				wire:model.live="itemParamsSelectAll">
			<span class="ls-quote-params-switch__ui" aria-hidden="true"></span>
			<span class="ls-quote-params-switch__label">Select all</span>
		</label>
		@if($isPackage && $this->itemParamsMaxTat !== null)
			<span class="pricelist-params-tat-pill" title="Package TAT = longest turnaround among selected tests">
				<i class="mdi mdi-timer-outline"></i>
				Max TAT {{ $this->itemParamsMaxTat }}d
			</span>
		@endif
	</div>

	<div class="ls-quote-params-stage">
		@if($rows->isEmpty())
			<div class="ls-quote-params-empty">
				<div class="ls-quote-params-empty__art" aria-hidden="true">
					<svg viewBox="0 0 160 160" width="88" height="88" xmlns="http://www.w3.org/2000/svg">
						<circle cx="80" cy="80" r="72" fill="#fff1f2"/>
						<path d="M62 28h36v14l22 54a28 28 0 1 1-54 0l22-54V28z" fill="#fda4af" stroke="#be123c" stroke-width="4" stroke-linejoin="round"/>
						<rect x="58" y="22" width="44" height="10" rx="4" fill="#9f1239"/>
					</svg>
				</div>
				<h4 class="ls-quote-params-empty__title">No parameters available</h4>
				<p class="ls-quote-params-empty__text">Select a sample type to load analytes for this pricelist item.</p>
			</div>
		@else
			<div class="ls-quote-analyte-table pricelist-params-table">
				<table class="ls-quote-analyte-grid pricelist-params-grid">
					<colgroup>
						<col class="pricelist-params-col--select">
						<col class="pricelist-params-col--name">
						<col class="pricelist-params-col--method">
						<col class="pricelist-params-col--tat">
						@unless($isPackage)
							<col class="pricelist-params-col--cost">
							<col class="pricelist-params-col--sell">
							<col class="pricelist-params-col--vat">
						@endunless
					</colgroup>
					<thead>
						<tr>
							<th scope="col" class="text-center" title="Include">Inc</th>
							<th scope="col">Test</th>
							<th scope="col">Method</th>
							<th scope="col" class="text-center" title="Turnaround time (days)">TAT</th>
							@unless($isPackage)
								<th scope="col">Costprice</th>
								<th scope="col">Sellingprice</th>
								<th scope="col" class="text-center" title="When ticked, VAT % comes from the active tax on Tax Regime Manager">VAT</th>
							@endunless
						</tr>
					</thead>
					<tbody>
						@foreach($grouped as $groupName => $groupRows)
							<tr class="pricelist-params-group-row">
								<td colspan="{{ $isPackage ? 4 : 7 }}">
									<span class="pricelist-params-group-label">{{ $groupName }}</span>
								</td>
							</tr>
							@foreach($groupRows as $row)
								@php
									$index = (int) ($row['_index'] ?? 0);
									$included = ! empty($row['included']);
								@endphp
								<tr wire:key="pricelist-param-row-{{ $row['analysis_element_id'] }}" class="{{ $included ? 'is-included' : '' }}">
									<td class="text-center">
										<label class="ls-quote-check pricelist-params-check" title="Include parameter">
											<input type="checkbox"
												wire:model.live="itemElementRows.{{ $index }}.included">
											<span class="ls-quote-check__box" aria-hidden="true"></span>
										</label>
									</td>
									<td>
										<div class="pricelist-params-test-cell">
											@if(! empty($row['lab_section_code']))
												<span class="pricelist-lab-section-pill" title="Lab section">{{ $row['lab_section_code'] }}</span>
											@endif
											<div class="table-primary-line">{{ $row['analyte_label'] ?: 'N/A' }}</div>
										</div>
									</td>
									<td>
										@if(! empty($row['method_label']))
											<span class="pricelist-method-pill">{{ $row['method_label'] }}</span>
										@else
											<span class="text-muted small">—</span>
										@endif
									</td>
									<td class="text-center">
										{{ $row['tat'] !== null ? $row['tat'].'d' : '—' }}
									</td>
									@unless($isPackage)
										<td>
											<input type="number"
												step="0.01"
												wire:model.blur="itemElementRows.{{ $index }}.cost_price"
												class="form-control form-control-sm item-modal-input pricelist-params-price"
												inputmode="decimal">
										</td>
										<td>
											<input type="number"
												step="0.01"
												wire:model.blur="itemElementRows.{{ $index }}.selling_price"
												class="form-control form-control-sm item-modal-input pricelist-params-price"
												inputmode="decimal">
										</td>
										<td class="text-center">
											<label class="ls-quote-check pricelist-params-check" title="When ticked, VAT % comes from the active tax on Tax Regime Manager">
												<input type="checkbox"
													wire:model.live="itemElementRows.{{ $index }}.vat">
												<span class="ls-quote-check__box" aria-hidden="true"></span>
											</label>
										</td>
									@endunless
								</tr>
							@endforeach
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>

	<div class="ls-quote-params-dock">
		<p class="ls-quote-params-foot">
			Only ticked tests are saved. Prices stay pending until you use Apply Price Changes on the items tab.
		</p>
	</div>
</div>
