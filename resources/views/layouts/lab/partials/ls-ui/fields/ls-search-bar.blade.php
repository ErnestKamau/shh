{{--
	ls-search-bar — pill search + optional filter button variants.
	Props: $placeholder, $variant (detached|ghost|inline|plain), $value,
	       $wireModel (optional Livewire path), $ariaLabel
--}}
@php
	$variant = $variant ?? 'detached';
	$placeholder = $placeholder ?? 'Search anything…';
@endphp
<div class="ls-search-bar {{ $variant === 'inline' ? 'is-integrated' : '' }} {{ $variant === 'plain' ? 'is-plain' : '' }}">
	<div class="ls-search-bar__field">
		<i class="mdi mdi-magnify" aria-hidden="true"></i>
		<input
			type="search"
			placeholder="{{ $placeholder }}"
			aria-label="{{ $ariaLabel ?? 'Search' }}"
			@if(! empty($wireModel))
				wire:model.live.debounce.250ms="{{ $wireModel }}"
			@else
				value="{{ $value ?? '' }}"
			@endif
		>
		@if($variant === 'inline')
			<button type="button" class="ls-search-bar__filter ls-search-bar__filter--inline" aria-label="Filter">
				<i class="mdi mdi-tune-variant"></i>
			</button>
		@endif
	</div>
	@if($variant === 'detached')
		<button type="button" class="ls-search-bar__filter" aria-label="Filter"><i class="mdi mdi-tune-variant"></i></button>
	@elseif($variant === 'ghost')
		<button type="button" class="ls-search-bar__filter ls-search-bar__filter--ghost" aria-label="Filter"><i class="mdi mdi-tune-variant"></i></button>
	@endif
</div>
