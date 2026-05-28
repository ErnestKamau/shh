@props([
    'options' => [],
    'wireModel' => '',
    'placeholder' => 'Search...',
    'emptyMessage' => 'No matches found.',
    'inputRef' => 'searchInput',
    'live' => false,
    'pickerKey' => 'config-search-select',
])

<div wire:key="{{ $pickerKey }}"
     class="position-relative pw-combobox config-search-picker"
     :class="{ 'config-search-picker--open': open }"
     data-picker-options='@json($options)'
     data-wire-model="{{ $wireModel }}"
     data-placeholder="{{ $placeholder }}"
     data-empty-message="{{ $emptyMessage }}"
     x-data="configSearchSelectFromEl()"
     x-init="init()"
     @click.outside="close()">
    <div class="form-control pw-measurand-field d-flex flex-wrap align-items-center gap-1"
         @click="openDropdown('{{ $inputRef }}')">
        <input type="text"
               x-ref="{{ $inputRef }}"
               x-model="search"
               @focus="openDropdown('{{ $inputRef }}')"
               @keydown.escape.prevent="close()"
               class="pw-measurand-input border-0 p-0 m-0 w-100"
               :placeholder="placeholderText">
    </div>
    <div x-show="open"
         x-cloak
         class="search-dropdown pw-measurand-dropdown">
        <template x-for="option in filteredOptions()" :key="option.value + '-' + option.label">
            <button type="button"
                    class="search-dropdown-item"
                    :class="{ 'active': String(option.value) === String(selected) }"
                    @click.prevent="choose(option.value)"
                    x-text="option.label"></button>
        </template>
        <div class="search-dropdown-empty text-muted small px-3 py-2" x-show="filteredOptions().length === 0" x-text="emptyText"></div>
    </div>
</div>
@if($live)
    <select class="d-none" wire:model.live="{{ $wireModel }}">
@else
    <select class="d-none" wire:model="{{ $wireModel }}">
@endif
    @foreach($options as $opt)
        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
    @endforeach
</select>
