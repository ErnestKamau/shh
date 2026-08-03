@php
    $isFlat = $rowIndex === null;
    $effectiveRowIndex = $isFlat ? -1 : (int) $rowIndex;
    $wirePrefix = $wirePrefix ?? ($isFlat ? 'formData.parameters' : ('formData.parameters.'.$effectiveRowIndex));
    $picker = $this->walkInParameterPickerState($isFlat ? null : $effectiveRowIndex);
    $selectedParams = $picker['selected'];
    $options = $picker['options'];
@endphp

{{--
  Stable wire:key (no options hash) so Livewire morphs do not remount mid-selection.
  Options refresh via hydrateFromWire / walk-in-params-row-reset.
--}}
<div
    class="rft-param-picker"
    wire:key="param-picker-{{ $isFlat ? 'flat' : $effectiveRowIndex }}"
    wire:ignore
    x-data="rftParamPickerUi({
        rowIndex: {{ $effectiveRowIndex }},
        flat: {{ $isFlat ? 'true' : 'false' }},
        options: @js($options),
        selected: @js($selectedParams),
    })"
    @keydown.escape.window="if (open) closePanel()"
    @click.outside="if (open) closePanel()"
    @walk-in-parameters-loading.window="setAnalysisLoading($event.detail)"
    @walk-in-params-row-reset.window="
        const raw = $event.detail;
        const payload = Array.isArray(raw) ? (raw[0] || {}) : (raw || {});
        if (!flat && payload.rowIndex !== undefined && Number(payload.rowIndex) !== Number(rowIndex)) {
            return;
        }
        if (Array.isArray(payload.options)) {
            options = payload.options.map((value) => String(value));
        }
        if (Array.isArray(payload.selected)) {
            selected = payload.selected.map((value) => String(value));
        } else {
            selected = [];
        }
        dirty = false;
    "
>
    <button type="button"
        class="rft-param-picker__trigger w-100 text-left"
        @click.stop="toggleOpen()"
        :aria-expanded="open ? 'true' : 'false'">
        <div class="rft-param-picker__chips" aria-live="polite">
            <template x-if="isLoading">
                <span class="text-primary small d-inline-flex align-items-center">
                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                    Loading parameters…
                </span>
            </template>
            <template x-if="!isLoading && selected.length === 0">
                <span class="text-muted small" x-text="options.length ? 'Choose parameters…' : 'Select analysis type first'"></span>
            </template>
            <span x-show="!isLoading" class="d-inline-flex flex-wrap">
                <template x-for="chip in visibleChips" :key="chip">
                    <span class="rft-param-chip">
                        <span x-text="chip" :title="chip"></span>
                        <button type="button" class="rft-param-chip__remove" @click.stop="removeChip(chip)" title="Remove" aria-label="Remove parameter">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </span>
                </template>
                <template x-if="hiddenCount > 0">
                    <span class="rft-param-picker__more" x-text="'+' + hiddenCount"></span>
                </template>
            </span>
        </div>
        <i class="mdi mdi-chevron-down text-muted rft-param-picker__caret" :class="open && 'mdi-rotate-180'"></i>
    </button>

    <div
        class="rft-param-picker__panel"
        x-show="open"
        x-cloak
        @click.stop
        :class="openUp ? 'is-up' : ''"
    >
        <div x-show="isLoading" class="text-center text-primary small py-4" role="status" aria-live="polite">
            <span class="spinner-border spinner-border-sm mr-2" aria-hidden="true"></span>
            Loading parameters…
        </div>
        <div x-show="!isLoading" class="d-flex align-items-center justify-content-between mb-2">
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
            x-show="!isLoading"
            @click.stop
            @keydown.stop
        >
        <div class="rft-param-picker__grid" x-show="!isLoading">
            <template x-for="name in filtered" :key="name">
                <label class="rft-param-option" :class="isSelected(name) && 'is-selected'" @click.prevent="toggle(name)">
                    <input type="checkbox" class="mt-1" :checked="isSelected(name)" tabindex="-1">
                    <span x-text="name" :title="name"></span>
                </label>
            </template>
        </div>
        <template x-if="!isLoading && !options.length">
            <p class="small text-muted mb-0 mt-2">Choose an analysis type on this sample to load parameters.</p>
        </template>
        <template x-if="!isLoading && options.length && filtered.length === 0">
            <p class="small text-muted mb-0 mt-2">No parameters match your search.</p>
        </template>
    </div>
</div>
@error($wirePrefix)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
