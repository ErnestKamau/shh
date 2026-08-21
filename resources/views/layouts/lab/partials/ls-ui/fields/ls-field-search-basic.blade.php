{{--
	ls-field-search-basic — searchable single select (view/edit style).
	Props: $label, $hint, $options, $selected, $success (bool), $disableSuccess (bool),
	       $id, $name (optional form field), $required, $placeholder, $attrs (html string on wrap),
	       $allowCustom (bool — commit typed text as value),
	       Livewire: $wireModel, $wireLive (bool, default true when wireModel set)
--}}
@php
	$id = $id ?? 'ls-sbasic-'.uniqid();
	$options = $options ?? ['Demi Wilkinson', 'Candice Cano', 'Phoenix Mando', 'Natali Craig', 'United States of America', 'United Arab Emirates'];
	$normalized = collect($options)->map(function ($o) {
		if (is_array($o)) {
			return [
				'value' => (string) ($o['value'] ?? $o['id'] ?? $o['label'] ?? ''),
				'label' => (string) ($o['label'] ?? $o['text'] ?? $o['value'] ?? ''),
				'meta' => $o['meta'] ?? null,
			];
		}

		return ['value' => (string) $o, 'label' => (string) $o, 'meta' => null];
	})->values()->all();
	$selected = $selected ?? null;
	$selectedLabel = null;
	if ($selected !== null && $selected !== '') {
		$selectedLabel = collect($normalized)->firstWhere('value', (string) $selected)['label'] ?? (string) $selected;
	}
	$disableSuccess = (bool) ($disableSuccess ?? false);
	$showSuccess = ! $disableSuccess && ! empty($success);
	$wireModel = $wireModel ?? null;
	$wireLive = isset($wireLive) ? (bool) $wireLive : ($wireModel !== null && $wireModel !== '');
	$allowCustom = (bool) ($allowCustom ?? false);
@endphp
<div
	class="ls-field ls-combo ls-search-basic ls-compact {{ $showSuccess ? 'is-success' : '' }} {{ ! empty($error) ? 'is-error' : '' }}"
	data-ls-search-basic="1"
	data-ls-disable-success="{{ $disableSuccess ? '1' : '0' }}"
	@if(! empty($attrs)) {!! $attrs !!} @endif
	x-data="{
		open: false,
		q: @js($selectedLabel ?? ''),
		selected: @js(($selected !== null && $selected !== '') ? (string) $selected : null),
		options: @js($normalized),
		disableSuccess: @js($disableSuccess),
		wireModel: @js($wireModel),
		wireLive: @js($wireLive),
		allowCustom: @js($allowCustom),
		get filtered() {
			const q = (this.q || '').trim().toLowerCase();
			if (!q || (this.selected && this.q === this.labelFor(this.selected))) return this.options;
			return this.options.filter(o => o.label.toLowerCase().includes(q));
		},
		labelFor(value) {
			const hit = this.options.find(o => String(o.value) === String(value));
			return hit ? hit.label : '';
		},
		syncWire(value) {
			if (!this.wireModel || typeof this.$wire?.set !== 'function') {
				return;
			}
			this.$wire.set(this.wireModel, value ?? '', this.wireLive);
		},
		commitValue(value, label) {
			const next = value == null ? '' : String(value);
			this.selected = next === '' ? null : next;
			this.q = label ?? (next ? (this.labelFor(next) || next) : '');
			this.open = false;
			if (this.$refs.hidden) {
				this.$refs.hidden.value = next;
				this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
			}
			this.syncWire(next);
			this.$el.dispatchEvent(new CustomEvent('ls-search-basic:change', {
				bubbles: true,
				detail: { id: this.$refs.hidden?.id || '', name: this.$refs.hidden?.name || '', value: next, label: this.q, meta: null }
			}));
		},
		pick(opt) {
			this.commitValue(opt.value, opt.label);
		},
		commitCustom() {
			if (!this.allowCustom) {
				return;
			}
			const next = (this.q || '').trim();
			if (!next) {
				this.commitValue('', '');
				return;
			}
			this.commitValue(next, next);
		},
		clear() {
			this.q = '';
			this.selected = null;
			this.open = true;
			if (this.$refs.hidden) {
				this.$refs.hidden.value = '';
				this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
			}
			this.syncWire('');
			this.$el.dispatchEvent(new CustomEvent('ls-search-basic:change', {
				bubbles: true,
				detail: { id: this.$refs.hidden?.id || '', name: this.$refs.hidden?.name || '', value: '', label: '', meta: null }
			}));
		},
		setOptions(next) {
			this.options = Array.isArray(next) ? next : [];
		}
	}"
	:class="{ 'is-open': open, 'is-success': !disableSuccess && !!selected && !open }"
	@click.outside="open = false; if (allowCustom) commitCustom()"
>
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	@if(! empty($name) || ! empty($wireModel))
		<input
			type="hidden"
			x-ref="hidden"
			id="{{ $id }}"
			name="{{ $name ?? $id }}"
			value="{{ $selected ?? '' }}"
			@if(! empty($required)) required @endif
		>
	@endif
	<div class="ls-field__control">
		<div class="ls-combo__search-wrap">
			<i class="mdi mdi-magnify"></i>
			<input
				type="text"
				x-model="q"
				@focus="open = true"
				@input="open = true; selected = null; if ($refs.hidden) { $refs.hidden.value = ''; }"
				@keydown.enter.prevent="allowCustom ? commitCustom() : (filtered[0] && pick(filtered[0]))"
				@blur="if (allowCustom) { setTimeout(() => commitCustom(), 120) }"
				placeholder="{{ $placeholder ?? 'Type to search…' }}"
				autocomplete="off"
				@if(empty($name) && empty($wireModel)) id="{{ $id }}" @endif
			>
		</div>
		<button type="button" class="ls-field__icon-btn" x-show="q" @click="clear()" aria-label="Clear">
			<i class="mdi mdi-close-circle"></i>
		</button>
		@unless($disableSuccess)
			<i class="mdi mdi-check-circle" x-show="selected && !open" style="padding-right:0.45rem;"></i>
		@endunless
		<button type="button" class="ls-field__icon-btn" @click="open = !open" aria-label="Toggle">
			<i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
		</button>
	</div>
	<div class="ls-combo__menu" role="listbox">
		<template x-for="opt in filtered" :key="opt.value">
			<button type="button" class="ls-combo__item" :class="{ 'is-active': selected === opt.value }" @click="pick(opt)">
				<span x-text="opt.label"></span>
				<i class="mdi mdi-check" x-show="selected === opt.value"></i>
			</button>
		</template>
		<p class="ls-field__hint px-2 py-1 mb-0" x-show="filtered.length === 0 && !allowCustom">No matches</p>
		<p class="ls-field__hint px-2 py-1 mb-0" x-show="filtered.length === 0 && allowCustom && q">Press Enter to use “<span x-text="q"></span>”</p>
	</div>
	@if($error ?? null)
		<p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $error }}</p>
	@elseif($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
