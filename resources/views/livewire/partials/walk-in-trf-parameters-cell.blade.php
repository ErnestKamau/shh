@php
    $fieldId = $fieldId ?? 'parameters';
    $rowIndex = $rowIndex ?? 0;
    $wirePrefix = $wirePrefix ?? ('formData.parameters.'.$rowIndex);
    $controlClass = ($compact ?? true) ? 'form-control form-control-xs' : 'form-control form-control-sm';
    $compactStyle = ($compact ?? true) ? 'padding: 2px 5px; height: auto; font-size: 11px;' : '';
    $raw = $this->formData['parameters'][$rowIndex] ?? [];
    $selectedParams = is_array($raw)
        ? $raw
        : ($raw !== '' && $raw !== null ? [(string) $raw] : []);
    $options = $this->parametersForRow($rowIndex)->pluck('name')->values()->all();
@endphp
<div
    class="walk-in-trf-parameters-wrap"
    wire:ignore
    data-row-index="{{ $rowIndex }}"
    data-livewire-model="{{ $wirePrefix }}"
    data-selected="{{ json_encode(array_values($selectedParams)) }}"
    data-options="{{ json_encode($options) }}"
>
    <select
        id="field_{{ $fieldId }}"
        multiple
        class="{{ $controlClass }} walk-in-trf-parameters-select no-select2 @error($wirePrefix) is-invalid @enderror"
        data-row-index="{{ $rowIndex }}"
        data-livewire-model="{{ $wirePrefix }}"
        style="width: 100%; min-width: 0; {{ $compactStyle }}"
    >
        @foreach($options as $optionName)
            <option value="{{ $optionName }}" @selected(in_array($optionName, $selectedParams, true))>{{ $optionName }}</option>
        @endforeach
    </select>
</div>
@error($wirePrefix)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
