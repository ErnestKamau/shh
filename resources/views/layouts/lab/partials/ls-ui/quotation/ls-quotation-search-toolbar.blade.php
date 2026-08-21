{{--
	ls-quotation-search-toolbar — compact gallery search bar (detached/ghost) + filter dropdown.
	Expects Livewire QuotationManager properties in scope.
--}}
@php
	$activeFilterCount = collect([
		$customerFilter ?? '',
		$quotationTypeFilter ?? '',
		$labSectionFilter ?? '',
		$startDate ?? '',
		$endDate ?? '',
	])->filter(static fn ($v): bool => $v !== null && $v !== '')->count();

	if (($sortField ?? 'created_at') !== 'created_at' || ($sortDirection ?? 'desc') !== 'desc') {
		$activeFilterCount++;
	}
@endphp

<div
	class="ls-quotation-search-toolbar"
	x-data
	@click.outside="if ($wire.filtersOpen) $wire.closeFilters()"
	@keydown.escape.window="if ($wire.filtersOpen) $wire.closeFilters()"
>
	<div class="ls-quotation-search-toolbar__row">
		<div class="ls-search-bar ls-quotation-search-toolbar__search">
			<div class="ls-search-bar__field">
				<i class="mdi mdi-magnify" aria-hidden="true"></i>
				<input
					type="search"
					wire:model.live.debounce.300ms="search"
					placeholder="Quote #, customer, contact…"
					aria-label="Search quotations"
				>
			</div>
			<button
				type="button"
				class="ls-search-bar__filter {{ $activeFilterCount > 0 ? '' : 'ls-search-bar__filter--ghost' }} {{ $filtersOpen ? 'is-open' : '' }}"
				wire:click="toggleFilters"
				aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}"
				aria-haspopup="dialog"
				aria-label="Open filters"
			>
				<i class="mdi mdi-tune-variant"></i>
				@if($activeFilterCount > 0)
					<span class="ls-quotation-search-toolbar__badge">{{ $activeFilterCount }}</span>
				@endif
			</button>
		</div>

		@if($activeFilterCount > 0 || ($search ?? '') !== '')
			<button type="button" class="ls-quotation-search-toolbar__clear" wire:click="clearFilters" title="Clear filters">
				<i class="mdi mdi-close-circle-outline"></i>
				<span>Clear</span>
			</button>
		@endif
	</div>

	@if($filtersOpen)
		<div class="ls-quotation-filter-panel">
			<div class="ls-quotation-filter-panel__head">
				<span><i class="mdi mdi-filter-variant"></i> Filters</span>
				<button type="button" class="ls-quotation-filter-panel__close" wire:click="closeFilters" aria-label="Close filters">
					<i class="mdi mdi-close"></i>
				</button>
			</div>

			<div class="ls-quotation-filter-panel__grid ls-compact">
				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-customer">Customer</label>
					<div class="ls-field__control">
						<select id="q-filter-customer" class="ls-field__input" wire:model.live="customerFilter">
							<option value="">All customers</option>
							@foreach($customers as $customer)
								<option value="{{ $customer->id }}">{{ $customer->name }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-type">Type</label>
					<div class="ls-field__control">
						<select id="q-filter-type" class="ls-field__input" wire:model.live="quotationTypeFilter">
							<option value="">All types</option>
							<option value="Analysis">Analysis</option>
							<option value="General">General</option>
						</select>
					</div>
				</div>

				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-lab">Lab section</label>
					<div class="ls-field__control">
						<select id="q-filter-lab" class="ls-field__input" wire:model.live="labSectionFilter">
							<option value="">All sections</option>
							@foreach($labSections as $section)
								<option value="{{ $section->id }}">{{ $section->name }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-sort">Sort by</label>
					<div class="ls-field__control">
						<select id="q-filter-sort" class="ls-field__input" wire:model.live="sortField">
							<option value="status">Status</option>
							<option value="created_at">Created</option>
							<option value="quote_date">Quote date</option>
							<option value="expiring_date">Expiry</option>
							<option value="quote_number">Quote #</option>
							<option value="customer">Customer</option>
							<option value="total_amount">Total</option>
							<option value="prepared_by_name">Prepared by</option>
						</select>
					</div>
				</div>

				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-dir">Direction</label>
					<div class="ls-field__control">
						<select id="q-filter-dir" class="ls-field__input" wire:model.live="sortDirection">
							<option value="asc">Ascending</option>
							<option value="desc">Descending</option>
						</select>
					</div>
				</div>

				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-from">From</label>
					<div class="ls-field__control">
						<input id="q-filter-from" type="date" class="ls-field__input" wire:model.live="startDate">
					</div>
				</div>

				<div class="ls-field">
					<label class="ls-field__label" for="q-filter-to">To</label>
					<div class="ls-field__control">
						<input id="q-filter-to" type="date" class="ls-field__input" wire:model.live="endDate">
					</div>
				</div>
			</div>

			<div class="ls-quotation-filter-panel__foot">
				<button type="button" class="ls-btn" wire:click="clearFilters">
					Clear all
				</button>
				<button type="button" class="ls-btn ls-btn--secondary-fill" wire:click="closeFilters">
					Done
				</button>
			</div>
		</div>
	@endif
</div>
