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
    $hasOptions = count($options) > 0;
@endphp
<div
    class="walk-in-trf-parameters-wrap"
    wire:ignore
    data-row-index="{{ $rowIndex }}"
    data-livewire-model="{{ $wirePrefix }}"
    data-selected="{{ json_encode(array_values($selectedParams)) }}"
    data-options="{{ json_encode($options) }}"
>
    <div class="walk-in-trf-parameters-actions d-flex align-items-center justify-content-between flex-wrap mb-1">
        <span class="walk-in-trf-parameters-count text-muted {{ ($compact ?? true) ? 'small' : '' }}">
            @if($hasOptions)
                {{ count($selectedParams) }}/{{ count($options) }} selected
            @endif
        </span>
        <span class="walk-in-trf-parameters-action-btns">
            <button
                type="button"
                class="btn btn-link p-0 walk-in-trf-params-select-all {{ ($compact ?? true) ? 'small' : '' }}"
                data-walk-in-params-action="select-all"
                @disabled(! $hasOptions)
                title="Select every parameter for this analysis type"
            >
                Select all
            </button>
            <span class="text-muted px-1" aria-hidden="true">·</span>
            <button
                type="button"
                class="btn btn-link p-0 walk-in-trf-params-clear {{ ($compact ?? true) ? 'small' : '' }}"
                data-walk-in-params-action="clear"
                @disabled(! $hasOptions)
                title="Clear selected parameters"
            >
                Clear
            </button>
        </span>
    </div>
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
