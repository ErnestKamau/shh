{{--
	ls-soft-card — white body, baby-blue header when expanded, slate title, optional chevron.
	Props: $title, $expanded (bool), $collapsible (bool), $body (html string) or use $slot via @include with sections
	For Blade includes without components, pass $bodyHtml or nest content after include via capture.
--}}
@php
	$expanded = $expanded ?? true;
	$collapsible = $collapsible ?? true;
	$title = $title ?? 'Sample card';
@endphp
<div
	class="ls-soft-card {{ $expanded ? 'is-expanded' : '' }}"
	@if($collapsible)
		x-data="{ open: {{ $expanded ? 'true' : 'false' }} }"
		:class="{ 'is-expanded': open }"
	@endif
>
	@if($collapsible)
		<button type="button" class="ls-soft-card__header" @click="open = !open">
			<span>{{ $title }}</span>
			<i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
		</button>
		<div class="ls-soft-card__body" x-show="open" x-cloak>
			{!! $bodyHtml ?? '' !!}
			{{ $slot ?? '' }}
		</div>
	@else
		<div class="ls-soft-card__header" style="cursor: default;">
			<span>{{ $title }}</span>
		</div>
		<div class="ls-soft-card__body">
			{!! $bodyHtml ?? '' !!}
			{{ $slot ?? '' }}
		</div>
	@endif
</div>
