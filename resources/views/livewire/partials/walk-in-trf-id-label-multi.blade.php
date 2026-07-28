@php
    $wireKey = $wireKey ?? ($wirePrefix ?? 'formData.sample_type_id');
    $fieldId = $fieldId ?? 'multi_picker';
    $syncMethod = $syncMethod ?? 'setWalkInSampleTypes';
    $placeholder = $placeholder ?? 'Choose…';
    $emptyHint = $emptyHint ?? 'No options available.';
    $searchPlaceholder = $searchPlaceholder ?? 'Search…';
    $options = collect($options ?? [])
        ->map(function ($opt) {
            if (is_array($opt)) {
                return [
                    'id' => (string) ($opt['id'] ?? $opt['value'] ?? ''),
                    'label' => (string) ($opt['label'] ?? $opt['name'] ?? $opt['id'] ?? ''),
                ];
            }

            return [
                'id' => (string) ($opt->id ?? ''),
                'label' => (string) ($opt->name ?? $opt->label ?? ''),
            ];
        })
        ->filter(fn (array $opt): bool => $opt['id'] !== '')
        ->values()
        ->all();
    $selected = array_values(array_filter(array_map('strval', $selected ?? [])));
    $optionsKey = md5(json_encode([$options, $selected, $syncMethod]));
@endphp

{{-- Alpine multi picker; Livewire owns state via {{ $syncMethod }}(wireKey, ids). --}}
<div
    class="rft-param-picker"
    wire:key="id-label-multi-{{ $fieldId }}-{{ $optionsKey }}"
    wire:ignore
    x-data="rftIdLabelMultiPicker({
        wireKey: @js($wireKey),
        syncMethod: @js($syncMethod),
        options: @js($options),
        selected: @js($selected),
        placeholder: @js($placeholder),
        emptyHint: @js($emptyHint),
    })"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <button type="button" class="rft-param-picker__trigger w-100 text-left" @click.stop="toggleOpen()">
        <div class="rft-param-picker__chips">
            <template x-if="selected.length === 0">
                <span class="text-muted small" x-text="options.length ? placeholder : emptyHint"></span>
            </template>
            <template x-for="chip in visibleChips" :key="chip.id">
                <span class="rft-param-chip">
                    <span x-text="chip.label" :title="chip.label"></span>
                    <button type="button" class="rft-param-chip__remove" @click.stop="toggle(chip.id)" title="Remove" aria-label="Remove">
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
            placeholder="{{ $searchPlaceholder }}"
            x-model="search"
            @click.stop
            @keydown.stop
        >
        <div class="rft-param-picker__grid">
            <template x-for="opt in filtered" :key="opt.id">
                <label class="rft-param-option" :class="isSelected(opt.id) && 'is-selected'" @click.prevent="toggle(opt.id)">
                    <input type="checkbox" class="mt-1" :checked="isSelected(opt.id)" tabindex="-1">
                    <span x-text="opt.label" :title="opt.label"></span>
                </label>
            </template>
        </div>
        <template x-if="!options.length">
            <p class="small text-muted mb-0 mt-2" x-text="emptyHint"></p>
        </template>
        <template x-if="options.length && filtered.length === 0">
            <p class="small text-muted mb-0 mt-2">No matches.</p>
        </template>
    </div>
</div>
@error($wireKey)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
