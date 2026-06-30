@php
    $label = (string) ($element->label ?? $element->name);
    $type = (string) ($element->element_type ?? 'text');
    $required = (bool) ($element->is_required ?? false);
@endphp

<label for="field_{{ $fieldId }}" class="font-weight-bold text-secondary small">
    {{ $label }}
    @if($required)<span class="text-danger">*</span>@endif
</label>

@switch($type)
    @case('textarea')
        <textarea id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm" rows="2"></textarea>
        @break
    @case('date')
        <input type="date" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @break
    @case('number')
        <input type="number" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @break
    @case('email')
        <input type="email" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @break
    @case('select')
        <select id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
            <option value="">Select option</option>
            @foreach(($element->options ?? []) as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <option value="{{ $optValue }}">{{ $optLabel }}</option>
            @endforeach
        </select>
        @break
    @case('radio')
        @foreach(($element->options ?? []) as $opt)
            @php
                $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
            @endphp
            <div class="custom-control custom-radio">
                <input type="radio" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}" value="{{ $optValue }}" class="custom-control-input">
                <label class="custom-control-label small" for="field_{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
            </div>
        @endforeach
        @break
    @case('checkbox')
        @if(is_array($element->options) && $element->options !== [])
            @foreach($element->options as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}.{{ $optValue }}" class="custom-control-input">
                    <label class="custom-control-label small" for="field_{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
                </div>
            @endforeach
        @else
            <div class="custom-control custom-checkbox pt-1">
                <input type="checkbox" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="custom-control-input">
                <label class="custom-control-label small" for="field_{{ $fieldId }}">{{ $label }}</label>
            </div>
        @endif
        @break
    @case('signature')
        <input type="hidden" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}">
        <div class="small text-muted">Signature captured on submit</div>
        @break
    @default
        <input type="text" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
@endswitch

@error(str_replace('formData.', 'formData.', $wirePrefix))
    <div class="invalid-feedback d-block small">{{ $message }}</div>
@enderror
