@props([
    'options' => [],
    'placeholder' => 'Search...',
    'limit' => 50,
    'disabled' => false,
])

@php
    $normalizedOptions = collect($options)->map(function ($opt) {
        if (is_array($opt)) {
            return [
                'id' => (string) ($opt['id'] ?? $opt['value'] ?? ''),
                'name' => (string) ($opt['name'] ?? $opt['label'] ?? ''),
            ];
        }

        if (is_object($opt)) {
            return [
                'id' => (string) ($opt->id ?? ''),
                'name' => (string) ($opt->name ?? $opt->label ?? ''),
            ];
        }

        return $opt;
    })->values()->all();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        disabled: @js((bool) $disabled),
        selected: @entangle($attributes->wire('model')->value()){{ $attributes->wire('model')->hasModifier('live') ? '.live' : '' }},
        options: @js($normalizedOptions),
        limit: {{ (int) $limit }},
        get filteredOptions() {
            if (! this.search) {
                return this.options.slice(0, this.limit);
            }

            const term = this.search.toLowerCase();

            return this.options.filter((opt) => opt.name.toLowerCase().includes(term));
        },
        isSelected(id) {
            return (this.selected || []).map(String).includes(String(id));
        },
        toggleOption(id) {
            const key = String(id);
            const current = (this.selected || []).map(String);

            if (current.includes(key)) {
                this.selected = current.filter((item) => item !== key);
            } else {
                this.selected = [...current, key];
            }
        },
        removeOption(id) {
            const key = String(id);
            this.selected = (this.selected || []).map(String).filter((item) => item !== key);
        },
        getSelectedOptions() {
            const current = (this.selected || []).map(String);

            return this.options.filter((opt) => current.includes(String(opt.id)));
        },
        toggleOpen() {
            if (! this.disabled) {
                this.open = ! this.open;
            }
        }
    }"
    class="searchable-dropdown-wrapper searchable-multi-select"
    {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.defer', 'wire:model.blur', 'options', 'placeholder', 'limit', 'disabled', 'class']) }}
>
    <div
        class="multi-select-container @if($disabled) opacity-75 @endif"
        @click="toggleOpen()"
        :class="{ 'pe-none': disabled }"
    >
        <div class="multi-select-chips" @click.stop>
            <template x-for="opt in getSelectedOptions()" :key="opt.id">
                <span class="multi-select-chip">
                    <span x-text="opt.name"></span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-white" @click.stop="removeOption(opt.id)">
                        <i class="mdi mdi-close-circle"></i>
                    </button>
                </span>
            </template>
            <input
                type="text"
                x-model="search"
                placeholder="{{ $placeholder }}"
                @focus="if (!disabled) open = true"
                class="form-control searchable-input-single multi-select-search"
                autocomplete="off"
                :disabled="disabled"
            >
        </div>
        <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
    </div>

    <div
        x-show="open && !disabled"
        @click.away="open = false"
        x-transition
        class="dropdown-list"
        style="display: none;"
    >
        <template x-if="filteredOptions.length > 0">
            <div class="options-list">
                <template x-for="opt in filteredOptions" :key="opt.id">
                    <div
                        @click="toggleOption(opt.id)"
                        class="option-item"
                        :class="{ 'selected': isSelected(opt.id) }"
                    >
                        <i class="mdi mdi-check-circle text-primary" x-show="isSelected(opt.id)"></i>
                        <span x-text="opt.name"></span>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="filteredOptions.length === 0">
            <div class="no-results">
                <i class="mdi mdi-alert-circle-outline"></i>
                <span>No results found</span>
            </div>
        </template>
    </div>
</div>

@once
    <style>
        .searchable-multi-select .multi-select-container {
                position: relative;
                min-height: 90px;
                border: 1px solid #ced4da;
                border-radius: 12px;
                padding: 8px 40px 8px 12px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .searchable-multi-select .multi-select-container:hover {
                border-color: #007bff;
                box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
            }

            .searchable-multi-select .multi-select-chips {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 6px;
                min-height: 74px;
            }

            .searchable-multi-select .multi-select-chip {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 4px 10px;
                background-color: #007bff;
                color: white;
                border-radius: 16px;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .searchable-multi-select .multi-select-search {
                flex: 1;
                min-width: 120px;
            }
        </style>
@endonce
