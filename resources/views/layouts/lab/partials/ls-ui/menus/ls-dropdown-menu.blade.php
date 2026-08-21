{{--
	ls-dropdown-menu — Actions-style dropdown (Alpine).
	Props: $label, $variant (secondary|outline|icon|kebab), $items (list of [icon, label, danger?]), $align (left|right)
--}}
@php
	$label = $label ?? 'Actions';
	$variant = $variant ?? 'secondary';
	$align = $align ?? 'right';
	$items = $items ?? [
		['icon' => 'mdi-eye-outline', 'label' => 'View quotation'],
		['icon' => 'mdi-file-clock-outline', 'label' => 'Awaiting approval'],
		['icon' => 'mdi-share-circle', 'label' => 'Request approval'],
		['icon' => 'mdi-printer', 'label' => 'Process PDF'],
		['icon' => 'mdi-content-duplicate', 'label' => 'Duplicate'],
		['icon' => 'mdi-delete-empty', 'label' => 'Delete', 'danger' => true],
	];
@endphp
<div
	class="ls-dropdown {{ $align === 'right' ? 'ls-dropdown--right' : '' }}"
	x-data="{ open: false }"
	@click.outside="open = false"
	@keydown.escape.window="open = false"
>
	@if($variant === 'kebab' || $variant === 'icon')
		<button
			type="button"
			class="ls-icon-btn ls-dropdown__toggle"
			:aria-expanded="open"
			@click="open = !open"
			aria-haspopup="menu"
			title="{{ $label }}"
		>
			<i class="mdi {{ $variant === 'kebab' ? 'mdi-dots-vertical' : 'mdi-dots-horizontal' }}"></i>
		</button>
	@elseif($variant === 'outline')
		<button
			type="button"
			class="ls-btn ls-dropdown__toggle"
			:aria-expanded="open"
			@click="open = !open"
			aria-haspopup="menu"
		>
			{{ $label }} <i class="mdi mdi-chevron-down"></i>
		</button>
	@else
		<button
			type="button"
			class="ls-btn ls-btn--secondary-fill ls-dropdown__toggle"
			:aria-expanded="open"
			@click="open = !open"
			aria-haspopup="menu"
		>
			<i class="mdi mdi-dots-horizontal"></i> {{ $label }}
			<i class="mdi mdi-chevron-down"></i>
		</button>
	@endif

	<ul class="ls-dropdown__menu" x-show="open" x-cloak x-transition.opacity.duration.120ms role="menu">
		@foreach($items as $item)
			<li role="none">
				<button
					type="button"
					class="ls-dropdown__item {{ ! empty($item['danger']) ? 'ls-dropdown__item--danger' : '' }}"
					role="menuitem"
					@click="open = false"
				>
					@if(! empty($item['icon']))
						<i class="mdi {{ $item['icon'] }}" aria-hidden="true"></i>
					@endif
					<span>{{ $item['label'] }}</span>
				</button>
			</li>
		@endforeach
	</ul>
</div>
