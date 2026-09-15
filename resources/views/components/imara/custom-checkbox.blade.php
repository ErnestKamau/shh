@props([
    'label' => '',
    'id' => null,
])

@php
    $inputId = $id ?? 'imara-checkbox-'.str_replace('.', '', uniqid('', true));
@endphp

<div @class(['form-group', 'mb-0'])>
    <div class="custom-control custom-checkbox">
        <input
            type="checkbox"
            id="{{ $inputId }}"
            {{ $attributes->except('class')->merge(['class' => 'custom-control-input']) }}
        />
        <label class="custom-control-label" for="{{ $inputId }}">{{ $label }}</label>
    </div>
</div>
