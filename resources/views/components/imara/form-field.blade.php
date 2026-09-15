@props([
    'label' => '',
    'required' => false,
    'for' => null,
    'error' => null,
])

<div class="form-group">
    <label class="control-label" @if($for) for="{{ $for }}" @endif>
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    {{ $slot }}
    @if($error)<span class="text-danger small d-block mt-1">{{ $error }}</span>@endif
</div>
