@php
    $wirePrefix = $wirePrefix ?? 'formData.sample_type_id';
    $fieldId = $fieldId ?? 'sample_type_multi';
    $rowIndex = $rowIndex ?? null;
    $selectedRaw = data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), []);
    if (! is_array($selectedRaw)) {
        $selectedRaw = filled($selectedRaw) ? [(string) $selectedRaw] : [];
    }
    $selectedIds = array_values(array_filter(array_map('strval', $selectedRaw)));
    $sampleTypeOptions = collect($this->sampleTypes ?? []);
    $optionsKey = md5(json_encode($selectedIds));
@endphp

{{-- wire:ignore keeps Select2 alive across Livewire morphs; sync via setWalkInSampleTypes(). --}}
<div
    class="rft-sample-type-multi"
    wire:key="sample-type-multi-{{ $fieldId }}-{{ $optionsKey }}"
    wire:ignore
    x-data="rftSampleTypeMultiSelect({
        wireKey: @js($wirePrefix),
        rowIndex: @js($rowIndex),
        selected: @js($selectedIds),
        fieldId: @js($fieldId),
    })"
    x-init="init()"
>
    <select
        id="field_{{ $fieldId }}"
        class="form-control form-control-sm rft-sample-type-multi__select no-select2"
        multiple
        x-ref="select"
    >
        @foreach($sampleTypeOptions as $sampleType)
            <option
                value="{{ $sampleType->id }}"
                @selected(in_array((string) $sampleType->id, $selectedIds, true))
            >{{ $sampleType->name }}</option>
        @endforeach
    </select>
</div>
@error($wirePrefix)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
