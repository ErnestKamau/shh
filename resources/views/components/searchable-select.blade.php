@props([
    'options' => [],
    'placeholder' => 'Search...',
    'emptyLabel' => null,
    'limit' => 50,
    'size' => 'default',
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

    $containerClass = $size === 'sm' ? 'single-select-container single-select-sm' : 'single-select-container';
@endphp

<div
    x-data="{
        open: false,
        search: '',
        disabled: @js((bool) $disabled),
        selected: @entangle($attributes->wire('model')->value()){{ $attributes->wire('model')->hasModifier('live') ? '.live' : '' }},
        options: @js($normalizedOptions),
        emptyLabel: @js($emptyLabel),
        limit: {{ (int) $limit }},
        get filteredOptions() {
            if (! this.search) {
                return this.options.slice(0, this.limit);
            }

            const term = this.search.toLowerCase();

            return this.options.filter((opt) => opt.name.toLowerCase().includes(term));
        },
        selectOption(id) {
            this.selected = id;
            this.open = false;
            this.search = '';
        },
        clearSelection() {
            this.selected = '';
            this.search = '';
        },
        getSelectedName() {
            if (this.selected === null || this.selected === undefined || this.selected === '') {
                return '';
            }

            const opt = this.options.find((o) => String(o.id) === String(this.selected));

            return opt ? opt.name : '';
        },
        toggleOpen() {
            if (! this.disabled) {
                this.open = ! this.open;
            }
        }
    }"
    class="searchable-dropdown-wrapper"
    {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.defer', 'wire:model.blur', 'options', 'placeholder', 'emptyLabel', 'limit', 'size', 'disabled', 'class']) }}
>
    <div
        class="{{ $containerClass }} @if($disabled) opacity-75 @endif"
        @click="toggleOpen()"
        :class="{ 'pe-none': disabled }"
    >
        <input
            type="text"
            x-model="search"
            :placeholder="getSelectedName() || emptyLabel || @js($placeholder)"
            @focus="if (!disabled) open = true"
            class="form-control searchable-input-single"
            autocomplete="off"
            :disabled="disabled"
            @click.stop
        >
        <template x-if="getSelectedName() && emptyLabel && !disabled">
            <button
                type="button"
                class="btn btn-link btn-sm p-0 ms-1 text-muted searchable-clear-btn"
                @click.stop="clearSelection()"
                title="Clear"
            >
                <i class="mdi mdi-close-circle"></i>
            </button>
        </template>
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
                        @click="selectOption(opt.id)"
                        class="option-item"
                        :class="{ 'selected': String(selected) === String(opt.id) }"
                    >
                        <i class="mdi mdi-check-circle text-primary" x-show="String(selected) === String(opt.id)"></i>
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
        .searchable-dropdown-wrapper {
            position: relative;
        }

            .searchable-input-single {
                border: none;
                outline: none;
                box-shadow: none !important;
                padding: 4px 0;
                width: 100%;
                background: transparent;
            }

            .searchable-input-single:focus {
                border: none !important;
                box-shadow: none !important;
            }

            .single-select-container {
                position: relative;
                min-height: 45px;
                border: 1px solid #ced4da;
                border-radius: 12px;
                padding: 8px 40px 8px 12px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
            }

            .single-select-sm {
                min-height: 38px;
                padding: 6px 35px 6px 10px;
                border-radius: 8px;
            }

            .single-select-container:hover {
                border-color: #007bff;
                box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
            }

            .single-select-container:has(.searchable-input-single:focus) {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            .dropdown-arrow {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                transition: transform 0.3s ease;
                font-size: 20px;
                color: #6c757d;
                pointer-events: none;
            }

            .dropdown-arrow.rotated {
                transform: translateY(-50%) rotate(180deg);
            }

            .searchable-clear-btn {
                line-height: 1;
                flex-shrink: 0;
            }

            .dropdown-list {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                margin-top: 4px;
                background: white;
                border: 1px solid #ced4da;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 1050;
                max-height: 300px;
                overflow-y: auto;
            }

            .options-list {
                padding: 8px;
            }

            .option-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s ease;
                font-size: 14px;
            }

            .option-item:hover {
                background: #f8f9fa;
            }

            .option-item.selected {
                background: rgba(0, 123, 255, 0.08);
                font-weight: 500;
            }

            .option-item i {
                font-size: 18px;
            }

            .no-results {
                padding: 20px;
                text-align: center;
                color: #6c757d;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }

            .no-results i {
                font-size: 24px;
            }
        </style>
@endonce
