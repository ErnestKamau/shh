{{--
	ls-field-status-select — status indicator select with colored dots.
	Props: $label, $hint, $options (list of [value,label,color]), $selected, $id,
	       $name (optional form field), $required
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
<div
	class="ls-field ls-combo ls-compact"
	x-data="{
		open: false,
		selected: @js($selected),
		options: @js($options),
		get current() {
			return this.options.find(o => String(o.value) === String(this.selected)) || this.options[0] || { value: '', label: 'Select…', color: '#94a3b8' };
		},
		pick(opt) {
			this.selected = opt.value;
			this.open = false;
			if (this.$refs.hidden) {
				this.$refs.hidden.value = opt.value;
				this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
			}
		}
	}"
	:class="{ 'is-open': open }"
	@click.outside="open = false"
>
	@if(! empty($label))
		<label class="ls-field__label" for="{{ $id }}">
			{{ $label }}@if(! empty($required))<span class="ls-req">*</span>@endif
		</label>
	@endif
	@if(! empty($name))
		<input
			type="hidden"
			x-ref="hidden"
			id="{{ $id }}"
			name="{{ $name }}"
			value="{{ $selected ?? '' }}"
			@if(! empty($required)) required @endif
		>
	@endif
	<div class="ls-field__control">
		<button type="button" class="ls-combo__trigger" @click="open = !open" aria-haspopup="listbox" :aria-expanded="open">
			<span class="ls-combo__dot" :style="'background:' + current.color"></span>
			<span x-text="current.label"></span>
			<i class="mdi mdi-chevron-down ls-combo__chevron"></i>
		</button>
	</div>
	<div class="ls-combo__menu" role="listbox">
		<template x-for="opt in options" :key="opt.value">
			<button
				type="button"
				class="ls-combo__item"
				:class="{ 'is-active': String(selected) === String(opt.value) }"
				role="option"
				@click="pick(opt)"
			>
				<span class="ls-combo__dot" :style="'background:' + opt.color"></span>
				<span x-text="opt.label"></span>
				<i class="mdi mdi-check" x-show="String(selected) === String(opt.value)"></i>
			</button>
		</template>
	</div>
	@if($hint ?? null)
		<p class="ls-field__hint">{{ $hint }}</p>
	@endif
</div>
