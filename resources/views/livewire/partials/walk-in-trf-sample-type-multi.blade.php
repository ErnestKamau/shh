@php
    $wirePrefix = $wirePrefix ?? 'formData.sample_type_id';
    $fieldId = $fieldId ?? 'sample_type_multi';
    $rowIndex = $rowIndex ?? null;
    $selectedRaw = data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), []);
    if (! is_array($selectedRaw)) {
        $selectedRaw = filled($selectedRaw) ? [(string) $selectedRaw] : [];
    }
    $selectedIds = array_values(array_filter(array_map('strval', $selectedRaw)));
@endphp

@include('livewire.partials.walk-in-trf-id-label-multi', [
    'wireKey' => $wirePrefix,
    'fieldId' => $fieldId,
    'syncMethod' => 'setWalkInSampleTypes',
    'options' => $this->sampleTypes ?? collect(),
    'selected' => $selectedIds,
    'placeholder' => 'Choose sample type(s)…',
    'emptyHint' => 'No sample types available.',
    'searchPlaceholder' => 'Search sample types...',
])
