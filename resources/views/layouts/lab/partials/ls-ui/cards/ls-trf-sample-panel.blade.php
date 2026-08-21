{{--
	ls-trf-sample-panel — sample accordion panel for TRF edit/view.
	Borrows soft-card baby-blue expanded header + collection burgundy accents.
	Props: $title, $expanded (bool), $collapsible (bool), $bodyHtml, $mode (edit|view)
--}}
@php
	$expanded = $expanded ?? false;
	$collapsible = $collapsible ?? true;
	$title = $title ?? 'Sample';
	$mode = $mode ?? 'edit';
@endphp
<div
	class="ls-trf-sample-panel ls-soft-card {{ $expanded ? 'is-expanded' : '' }} {{ $mode === 'view' ? 'ls-trf-sample-panel--view' : '' }}"
	@if($collapsible)
		x-data="{ open: {{ $expanded ? 'true' : 'false' }} }"
		:class="{ 'is-expanded': open }"
	@endif
>
	@if($collapsible)
		<button type="button" class="ls-soft-card__header ls-trf-sample-panel__header" @click="open = !open">
			<span class="ls-trf-sample-panel__title">{{ $title }}</span>
			<i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
		</button>
		<div class="ls-soft-card__body ls-trf-sample-panel__body" x-show="open" x-cloak>
			{!! $bodyHtml ?? '' !!}
			{{ $slot ?? '' }}
		</div>
	@else
		<div class="ls-soft-card__header ls-trf-sample-panel__header" style="cursor: default;">
			<span class="ls-trf-sample-panel__title">{{ $title }}</span>
		</div>
		<div class="ls-soft-card__body ls-trf-sample-panel__body">
			{!! $bodyHtml ?? '' !!}
			{{ $slot ?? '' }}
		</div>
	@endif
</div>
