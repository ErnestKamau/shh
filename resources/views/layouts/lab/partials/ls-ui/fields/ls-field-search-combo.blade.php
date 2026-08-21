{{--
	ls-field-search-combo — searchable single-select combo (large lists).
	Props: $label, $hint, $placeholder, $options (list of strings or [value,label]), $selected, $id
--}}
@php
	$id = $id ?? 'ls-combo-'.uniqid();
	$options = $options ?? ['Croatia', 'Cyprus', 'Czechia', 'Austria', 'Belgium', 'Bulgaria'];
	$normalized = collect($options)->map(function ($opt) {
		if (is_array($opt)) {
			return ['value' => $opt['value'] ?? $opt['label'], 'label' => $opt['label'] ?? $opt['value']];
		}
		return ['value' => $opt, 'label' => $opt];
	})->values()->all();
@endphp
<div
	class="ls-field ls-combo"
	x-data="{
		open: false,
		q: '',
		selected: @js($selected ?? null),
		options: @js($normalized),
		get filtered() {
			const q = this.q.trim().toLowerCase();
			if (!q) return this.options;
			return this.options.filter(o => o.label.toLowerCase().includes(q));
		}
	}"
	:class="{ 'is-open': open }"
	@click.outside="open = false"
>
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">{{ $label }}</label>
	@endif
	<div class="ls-field__control">
		<div class="ls-combo__search-wrap">
			<i class="mdi mdi-magnify"></i>
			<input
				id="{{ $id }}"
				type="text"
				x-model="q"
				@focus="open = true"
				placeholder="{{ $placeholder ?? 'Type to search…' }}"
				autocomplete="off"
			>
		</div>
		<button type="button" class="ls-field__icon-btn" @click="open = !open" aria-label="Toggle">
			<i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
		</button>
	</div>
	<div class="ls-combo__menu" role="listbox">
		<template x-for="opt in filtered" :key="opt.value">
			<button
				type="button"
				class="ls-combo__item"
				:class="{ 'is-active': selected === opt.value }"
				@click="selected = opt.value; q = opt.label; open = false"
			>
				<span x-text="opt.label"></span>
				<i class="mdi mdi-check" x-show="selected === opt.value"></i>
			</button>
		</template>
		<p class="ls-field__hint px-2 py-1 mb-0" x-show="filtered.length === 0">No matches</p>
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
