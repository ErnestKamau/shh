{{--
	ls-field-status-select — status indicator select with colored dots.
	Props: $label, $hint, $options (list of [value,label,color]), $selected, $id
--}}
@php
	$id = $id ?? 'ls-status-'.uniqid();
	$options = $options ?? [
		['value' => 'backlog', 'label' => 'Backlog', 'color' => '#ef4444'],
		['value' => 'completed', 'label' => 'Completed', 'color' => '#22c55e'],
		['value' => 'in_progress', 'label' => 'In Progress', 'color' => '#2563eb'],
		['value' => 'waiting', 'label' => 'Waiting', 'color' => '#a855f7'],
	];
	$selected = $selected ?? ($options[1]['value'] ?? null);
	$selectedOption = collect($options)->firstWhere('value', $selected) ?? $options[0];
@endphp
<div class="ls-field ls-combo" x-data="{ open: false }" :class="{ 'is-open': open }" @click.outside="open = false">
	@if(! empty($label))
		<span class="ls-field__label">{{ $label }}</span>
	@endif
	<div class="ls-field__control">
		<button type="button" class="ls-combo__trigger" @click="open = !open" aria-haspopup="listbox" :aria-expanded="open">
			<span class="ls-combo__dot" style="background: {{ $selectedOption['color'] }};"></span>
			<span>{{ $selectedOption['label'] }}</span>
			<i class="mdi mdi-chevron-down ls-combo__chevron"></i>
		</button>
	</div>
	<div class="ls-combo__menu" role="listbox">
		@foreach($options as $opt)
			<button
				type="button"
				class="ls-combo__item {{ ($opt['value'] ?? '') === $selected ? 'is-active' : '' }}"
				role="option"
			>
				<span class="ls-combo__dot" style="background: {{ $opt['color'] }};"></span>
				<span>{{ $opt['label'] }}</span>
				@if(($opt['value'] ?? '') === $selected)
					<i class="mdi mdi-check"></i>
				@endif
			</button>
		@endforeach
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
