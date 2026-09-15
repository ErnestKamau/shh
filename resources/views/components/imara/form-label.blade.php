@props([
    'for' => null,
    'required' => false,
])

<label
    {{ $attributes->merge(['class' => 'control-label']) }}
    @if($for) for="{{ $for }}" @endif
>
    {{ $slot }}
    @if($required)
        <span class="text-danger">*</span>
    @endif
</label>
