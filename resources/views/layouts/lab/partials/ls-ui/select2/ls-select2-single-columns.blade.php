{{--
	ls-select2-single-columns — single searchable select with label + meta columns.
	Alpine + click-away. Use wire:ignore + $wire.call so Livewire morph does not
	reset selection / reopen the menu.

	Props: $label, $id, $name, $options ({value,label,meta?}), $selected,
	       $placeholder, $required, $hint, $error,
	       $wireModel, $wireMethod (preferred Livewire method name, receives id string),
	       $wireLive
--}}
@php
	$id = $id ?? 'ls-s2sc-'.uniqid();
	$options = $options ?? [];
	$normalized = collect($options)->map(function ($o) {
		if (is_array($o)) {
			return [
				'value' => (string) ($o['value'] ?? $o['id'] ?? ''),
				'label' => (string) ($o['label'] ?? $o['text'] ?? $o['name'] ?? ''),
				'meta' => (string) ($o['meta'] ?? $o['category'] ?? ''),
			];
		}

		return ['value' => (string) $o, 'label' => (string) $o, 'meta' => ''];
	})->values()->filter(fn ($o) => $o['value'] !== '')->values()->all();
	$selected = ($selected !== null && $selected !== '') ? (string) $selected : null;
	$wireModel = $wireModel ?? null;
	$wireMethod = $wireMethod ?? null;
	$wireLive = isset($wireLive) ? (bool) $wireLive : true;
	$wireClearMethod = $wireClearMethod ?? null;
@endphp
<div
	wire:ignore
	class="ls-field ls-combo ls-compact ls-select2-single-columns {{ ! empty($required) ? 'ls-field--required' : '' }} {{ ! empty($error) ? 'is-error' : '' }}"
	data-ls-select2-single-columns="1"
	x-data="{
		open: false,
		q: '',
		selected: @js($selected),
		options: @js($normalized),
		wireModel: @js($wireModel),
		wireMethod: @js($wireMethod),
		wireClearMethod: @js($wireClearMethod),
		wireLive: @js($wireLive),
		get filtered() {
			const q = (this.q || '').trim().toLowerCase();
			if (!q) return this.options;
			return this.options.filter(o =>
				(o.label || '').toLowerCase().includes(q)
				|| (o.meta || '').toLowerCase().includes(q)
			);
		},
		labelFor(value) {
			const hit = this.options.find(o => String(o.value) === String(value));
			return hit ? hit.label : '';
		},
		metaFor(value) {
			const hit = this.options.find(o => String(o.value) === String(value));
			return hit ? (hit.meta || '') : '';
		},
		displayLabel() {
			return this.selected ? this.labelFor(this.selected) : '';
		},
		close() {
			this.open = false;
			this.q = '';
		},
		commit(value) {
			const next = value == null || value === '' ? null : String(value);
			this.selected = next;
			this.close();
			if (typeof this.$wire?.call !== 'function') {
				return;
			}
			if (next === null) {
				if (this.wireClearMethod) {
					this.$wire.call(this.wireClearMethod);
				} else if (this.wireModel) {
					this.$wire.set(this.wireModel, '', this.wireLive);
				}
				return;
			}
			if (this.wireMethod) {
				this.$wire.call(this.wireMethod, next);
				return;
			}
			if (this.wireModel) {
				this.$wire.set(this.wireModel, next, this.wireLive);
			}
		},
		pick(opt) {
			this.commit(opt.value);
		},
		clear() {
			this.commit(null);
			this.open = true;
			this.$nextTick(() => this.$refs.search?.focus());
		}
	}"
	@mousedown.outside="close()"
	@keydown.escape.window="close()"
	:class="{ 'is-open': open }"
>
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	<input
		type="hidden"
		x-ref="hidden"
		id="{{ $id }}"
		name="{{ $name ?? $id }}"
		:value="selected || ''"
		@if(! empty($required)) required @endif
	>
	<div
		class="ls-field__control ls-select2-single-columns__control"
		@click="open = true; $nextTick(() => $refs.search?.focus())"
	>
		<div class="ls-select2-single-columns__value" x-show="!open" x-cloak>
			<span class="ls-select2-choice-with-meta" x-show="selected">
				<span class="ls-select2-choice-with-meta__label" x-text="displayLabel()"></span>
				<span class="ls-select2-choice-with-meta__meta" x-show="metaFor(selected)" x-text="metaFor(selected)"></span>
			</span>
			<span class="ls-select2-single-columns__placeholder" x-show="!selected">{{ $placeholder ?? 'Search…' }}</span>
		</div>
		<div class="ls-combo__search-wrap" x-show="open" x-cloak>
			<i class="mdi mdi-magnify"></i>
			<input
				type="text"
				x-ref="search"
				x-model="q"
				@focus="open = true"
				@keydown.enter.prevent="filtered[0] && pick(filtered[0])"
				@keydown.escape.prevent="close()"
				placeholder="{{ $placeholder ?? 'Search…' }}"
				autocomplete="off"
			>
		</div>
		<button type="button" class="ls-field__icon-btn" x-show="selected" @click.stop="clear()" aria-label="Clear">
			<i class="mdi mdi-close-circle"></i>
		</button>
		<button type="button" class="ls-field__icon-btn" @click.stop="open = !open" aria-label="Toggle">
			<i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
		</button>
	</div>
	<div class="ls-combo__menu ls-select2-single-columns__menu" x-ref="menu" x-show="open" x-cloak role="listbox">
		<template x-for="opt in filtered" :key="opt.value">
			<button
				type="button"
				class="ls-combo__item ls-select2-single-columns__item"
				:class="{ 'is-active': selected === opt.value }"
				@mousedown.prevent="pick(opt)"
			>
				<span class="ls-select2-meta-row ls-select2-meta-row--spread">
					<span class="ls-select2-meta-row__label" x-text="opt.label"></span>
					<span class="ls-select2-meta-row__meta" x-show="opt.meta" x-text="opt.meta"></span>
				</span>
				<i class="mdi mdi-check" x-show="selected === opt.value"></i>
			</button>
		</template>
		<p class="ls-field__hint px-2 py-1 mb-0" x-show="filtered.length === 0">No matches</p>
	</div>
	@if($error ?? null)
		<p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $error }}</p>
	@elseif($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
