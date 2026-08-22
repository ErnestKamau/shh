{{--
	ls-search-bar — pill search + optional filter button variants.
	Props: $placeholder, $variant (detached|ghost|inline|plain), $value,
	       $wireModel (optional Livewire path), $ariaLabel
	       Alpine (parent x-data): $alpineModel, $filterAlpine, $filterClick,
	       $filterOpenAlpine, $filterBadgeAlpine
--}}
@php
	$variant = $variant ?? 'detached';
	$placeholder = $placeholder ?? 'Search anything…';
	$useAlpine = filled($alpineModel ?? null);
	$useFilterAlpine = $useAlpine && ! empty($filterAlpine);
@endphp
<div class="ls-search-bar {{ $variant === 'inline' ? 'is-integrated' : '' }} {{ $variant === 'plain' ? 'is-plain' : '' }} {{ ! empty($fullWidth) ? 'ls-search-bar--full' : '' }}">
	<div class="ls-search-bar__field">
		<i class="mdi mdi-magnify" aria-hidden="true"></i>
		<input
			type="search"
			placeholder="{{ $placeholder }}"
			aria-label="{{ $ariaLabel ?? 'Search' }}"
			@if($useAlpine)
				x-model="{{ $alpineModel }}"
				@keydown.stop
			@elseif(! empty($wireModel))
				wire:model.live.debounce.250ms="{{ $wireModel }}"
			@else
				value="{{ $value ?? '' }}"
			@endif
		>
		@if($variant === 'inline')
			<button
				type="button"
				class="ls-search-bar__filter ls-search-bar__filter--inline"
				aria-label="Filter"
				@if($useFilterAlpine)
					@click.prevent="{{ $filterClick ?? 'filtersOpen = !filtersOpen' }}"
					:class="{ 'is-open': {{ $filterOpenAlpine ?? 'filtersOpen' }} }"
				@endif
			>
				<i class="mdi mdi-tune-variant"></i>
			</button>
		@endif
	</div>
	@if($variant === 'detached')
		<button
			type="button"
			class="ls-search-bar__filter {{ ! empty($filterBadgeAlpine) ? '' : 'ls-search-bar__filter--ghost' }}"
			aria-label="Filter"
			@if($useFilterAlpine)
				@click.prevent="{{ $filterClick ?? 'toggleFilters()' }}"
				:class="{
					'is-open': {{ $filterOpenAlpine ?? 'filtersOpen' }},
					'ls-search-bar__filter--ghost': ({{ $filterBadgeAlpine ?? 'activeFilterCount' }} || 0) === 0
				}"
				:aria-expanded="{{ $filterOpenAlpine ?? 'filtersOpen' }} ? 'true' : 'false'"
			@endif
		>
			<i class="mdi mdi-tune-variant"></i>
			@if($useFilterAlpine && ! empty($filterBadgeAlpine))
				<span class="ls-quotation-search-toolbar__badge" x-show="({{ $filterBadgeAlpine }}) > 0" x-text="{{ $filterBadgeAlpine }}" x-cloak></span>
			@endif
		</button>
	@elseif($variant === 'ghost')
		<button type="button" class="ls-search-bar__filter ls-search-bar__filter--ghost" aria-label="Filter"><i class="mdi mdi-tune-variant"></i></button>
	@endif
</div>
