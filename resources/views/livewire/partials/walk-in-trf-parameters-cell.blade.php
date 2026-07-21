@php
    $rowIndex = (int) ($rowIndex ?? 0);
    $wirePrefix = $wirePrefix ?? ('formData.parameters.'.$rowIndex);
    $picker = $this->walkInParameterPickerState($rowIndex);
    $selectedParams = $picker['selected'];
    $options = $picker['options'];
    $optionsKey = md5(json_encode($options));
@endphp

{{--
  Full wire:ignore keeps Alpine UI interactive after selections.
  Livewire remains source of truth via setWalkInParameters().
--}}
<div
    class="rft-param-picker"
    wire:key="param-picker-{{ $rowIndex }}-{{ $optionsKey }}"
    wire:ignore
    x-data="rftParamPickerUi({
        rowIndex: {{ $rowIndex }},
        options: @js($options),
        selected: @js($selectedParams),
    })"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <button type="button" class="rft-param-picker__trigger w-100 text-left" @click.stop="toggleOpen()">
        <div class="rft-param-picker__chips">
            <template x-if="selected.length === 0">
                <span class="text-muted small" x-text="options.length ? 'Choose parameters…' : 'Select analysis type first'"></span>
            </template>
            <template x-for="chip in visibleChips" :key="chip">
                <span class="rft-param-chip">
                    <span x-text="chip" :title="chip"></span>
                    <button type="button" class="rft-param-chip__remove" @click.stop="toggle(chip)" title="Remove" aria-label="Remove parameter">
                        <i class="mdi mdi-close"></i>
                    </button>
                </span>
            </template>
            <template x-if="hiddenCount > 0">
                <span class="rft-param-picker__more" x-text="'+' + hiddenCount"></span>
            </template>
        </div>
        <i class="mdi mdi-chevron-down text-muted rft-param-picker__caret" :class="open && 'mdi-rotate-180'"></i>
    </button>

    <div
        class="rft-param-picker__panel"
        x-show="open"
        x-cloak
        x-transition
        @click.stop
        :class="openUp ? 'is-up' : ''"
    >
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small text-muted">
                <span x-text="selected.length"></span>/<span x-text="options.length"></span> selected
            </span>
            <span class="rft-param-picker__actions">
                <button type="button" class="btn btn-link btn-sm p-0 mr-2" @click.prevent="selectAll()" :disabled="!options.length">Select all</button>
                <button type="button" class="btn btn-link btn-sm p-0" @click.prevent="clearAll()" :disabled="!selected.length">Clear</button>
            </span>
        </div>
        <input
            type="search"
            class="form-control form-control-sm"
            placeholder="Search parameters..."
            x-model="search"
            @click.stop
            @keydown.stop
        >
        <div class="rft-param-picker__grid">
            <template x-for="name in filtered" :key="name">
                <label class="rft-param-option" :class="isSelected(name) && 'is-selected'" @click.prevent="toggle(name)">
                    <input type="checkbox" class="mt-1" :checked="isSelected(name)" tabindex="-1">
                    <span x-text="name" :title="name"></span>
                </label>
            </template>
        </div>
        <template x-if="!options.length">
            <p class="small text-muted mb-0 mt-2">Choose an analysis type on this sample to load parameters.</p>
        </template>
        <template x-if="options.length && filtered.length === 0">
            <p class="small text-muted mb-0 mt-2">No parameters match your search.</p>
        </template>
    </div>
</div>
@error($wirePrefix)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
