@php
    $wirePrefix = $wirePrefix ?? 'formData.analysis_type_id';
    $fieldId = $fieldId ?? 'analysis_type_multi';
    $rowIndex = $rowIndex ?? null;
    $selectedRaw = data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), []);
    if (! is_array($selectedRaw)) {
        $selectedRaw = filled($selectedRaw) ? [(string) $selectedRaw] : [];
    }
    $selectedIds = array_values(array_filter(array_map('strval', $selectedRaw)));
    $analysisOptions = $this->analysisTypesForRow($rowIndex)
        ->map(fn ($at) => ['id' => (string) $at->id, 'label' => (string) $at->name])
        ->values()
        ->all();
@endphp

@include('livewire.partials.walk-in-trf-id-label-multi', [
    'wireKey' => $wirePrefix,
    'fieldId' => $fieldId,
    'syncMethod' => 'setWalkInAnalysisTypes',
    'options' => $analysisOptions,
    'selected' => $selectedIds,
    'placeholder' => 'Choose analysis type(s)…',
    'emptyHint' => 'Select sample type(s) first.',
    'searchPlaceholder' => 'Search analysis types...',
])
