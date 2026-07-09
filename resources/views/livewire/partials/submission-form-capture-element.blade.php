@php
    $label = (string) ($element->label ?? $element->name);
    $type = (string) ($element->element_type ?? 'text');
    $name = (string) ($element->name ?? '');
    $required = (bool) ($element->is_required ?? false);
    $rowIndex = null;
    if (preg_match('/\.(\d+)$/', (string) $wirePrefix, $rowIndexMatch)) {
        $rowIndex = (int) $rowIndexMatch[1];
    }
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
    @case('contact_signature')
        <div class="acc-signature-pad trf-signature-pad" wire:ignore>
            <canvas
                id="trf-sig-{{ $fieldId }}-canvas"
                class="trf-signature-canvas"
                data-field="{{ $fieldId }}"
                data-livewire-model="{{ $wirePrefix }}"
                style="width: 100%; height: 120px; touch-action: none;"
            ></canvas>
            <div class="acc-signature-actions mt-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary trf-signature-clear"
                    data-canvas="trf-sig-{{ $fieldId }}-canvas"
                    data-input="field_{{ $fieldId }}"
                >Clear</button>
            </div>
        </div>
        <input type="hidden" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}">
        @break
    @case('analysis_type_select')
        <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}" class="form-control form-control-sm">
            <option value="">-- Select Analysis Type --</option>
            @foreach($this->analysisTypes as $analysisType)
                <option value="{{ $analysisType->id }}">{{ $analysisType->name }}</option>
            @endforeach
        </select>
        @break
    @case('analysis_elements_select')
        @include('livewire.partials.walk-in-trf-parameters-cell', [
            'fieldId' => $fieldId,
            'rowIndex' => $rowIndex ?? 0,
            'wirePrefix' => $wirePrefix,
            'compact' => false,
        ])
        @if($this->parametersForRow($rowIndex)->isEmpty())
            <small class="text-muted d-block mt-1">Select an analysis type to load parameters.</small>
        @endif
        @break
    @default
        @if($name === 'analysis_type_id')
            <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}" class="form-control form-control-sm">
                <option value="">-- Select Analysis Type --</option>
                @foreach($this->analysisTypes as $analysisType)
                    <option value="{{ $analysisType->id }}">{{ $analysisType->name }}</option>
                @endforeach
            </select>
        @elseif(in_array($name, ['parameter', 'parameters'], true))
            @include('livewire.partials.walk-in-trf-parameters-cell', [
                'fieldId' => $fieldId,
                'rowIndex' => $rowIndex ?? 0,
                'wirePrefix' => $wirePrefix,
                'compact' => false,
            ])
            @if($this->parametersForRow($rowIndex)->isEmpty())
                <small class="text-muted d-block mt-1">Select an analysis type to load parameters.</small>
            @endif
        @else
            <input type="text" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @endif
@endswitch

@error(str_replace('formData.', 'formData.', $wirePrefix))
    <div class="invalid-feedback d-block small">{{ $message }}</div>
@enderror
