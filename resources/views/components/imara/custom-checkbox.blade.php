@props([
    'label' => '',
    'id' => null,
    'value' => '1',
])

@php
    $inputId = $id ?? 'imara-switch-'.\Illuminate\Support\Str::random(6);
@endphp

<div class="custom-control custom-switch">
    <input
        type="checkbox"
        value="{{ $value }}"
        {{ $attributes->merge(['class' => 'custom-control-input', 'id' => $inputId]) }}
    />
    <label class="custom-control-label" for="{{ $inputId }}">{{ $label }}</label>
</div>
