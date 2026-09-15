@props([
    'label' => '',
    'required' => false,
    'error' => '',
])

<div {{ $attributes->class(['form-group']) }}>
    @if ($label !== '')
        <label class="control-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($error !== '')
        <div class="invalid-feedback d-block">{{ $error }}</div>
    @endif
</div>
