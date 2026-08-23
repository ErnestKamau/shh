{{--
	ls-modal-gallery-demo — Alpine state switcher around a modal include.

	Required: $include (Blade view basename under layouts.lab.partials.ls-ui.modals.)
	Optional: $label, $includeProps (array merged into each state include)
--}}
@php
	$include = $include ?? null;
	$label = $label ?? 'Modal';
	$view = $include ? 'layouts.lab.partials.ls-ui.modals.'.$include : null;
	$includeProps = is_array($includeProps ?? null) ? $includeProps : [];
@endphp
@if($view)
	<div class="ls-modal-demo" x-data="{ state: 'ready' }">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
			<span class="small text-muted mb-0" style="font-size:0.72rem;font-weight:600;">{{ $label }}</span>
			<div class="ls-modal-demo__states" role="group" aria-label="{{ $label }} state">
				<button type="button" :class="{ 'is-active': state === 'ready' }" @click="state = 'ready'">Ready</button>
				<button type="button" :class="{ 'is-active': state === 'loading' }" @click="state = 'loading'">Loading</button>
				<button type="button" :class="{ 'is-active': state === 'empty' }" @click="state = 'empty'">Empty</button>
			</div>
		</div>
		<div x-show="state === 'ready'">
			@include($view, array_merge($includeProps, ['state' => 'ready']))
		</div>
		<div x-show="state === 'loading'" x-cloak>
			@include($view, array_merge($includeProps, ['state' => 'loading']))
		</div>
		<div x-show="state === 'empty'" x-cloak>
			@include($view, array_merge($includeProps, ['state' => 'empty']))
		</div>
	</div>
@endif
