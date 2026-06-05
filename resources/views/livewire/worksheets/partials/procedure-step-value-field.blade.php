@php
    $valueType = $step->value_type ?: 'text';
    $stepInputType = match ($valueType) {
        'number' => 'number',
        'time' => 'time',
        'datetime' => 'datetime-local',
        'date' => 'date',
        default => 'text',
    };
    $requiresDoubleEntry = in_array($stepInputType, ['text', 'number'], true);
    $isSelectType = in_array($valueType, ['method_select', 'equipment_select', 'custom_select'], true);
    $wireModel = $wireModel ?? '';
    $confirmLabel = $confirmLabel ?? $step->step;
    $inputClass = ($inputClass ?? 'form-control') . ($compact ?? false ? ' form-control-sm' : '');
@endphp

@if($isSelectType)
    <select
        class="{{ $inputClass }}"
        wire:model.live.debounce.500ms="{{ $wireModel }}"
        wire:change="autosaveStepValue(@js($step->id), @js($capturedResultId ?? null))"
    >
        <option value="">Select...</option>
        @if($valueType === 'method_select')
            @foreach($this->methodOptions as $opt)
                <option value="{{ $opt->id }}">{{ $opt->label }}</option>
            @endforeach
        @elseif($valueType === 'equipment_select')
            @foreach($this->equipmentOptions as $opt)
                <option value="{{ $opt->id }}">{{ $opt->label }}</option>
            @endforeach
        @elseif($valueType === 'custom_select')
            @foreach(($step->select_options ?? []) as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        @endif
    </select>
@else
    <input
        type="{{ $stepInputType }}"
        class="{{ $inputClass }}"
        @if($requiresDoubleEntry)
            data-double-entry-confirm="1"
            data-confirm-label="{{ $confirmLabel }}"
        @endif
        wire:model.live.debounce.1000ms="{{ $wireModel }}"
        wire:blur="autosaveStepValue(@js($step->id), @js($capturedResultId ?? null))"
        @if($valueType === 'number') step="any" @endif
    >
@endif
