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
        ->map(function ($at) {
            $sampleTypeName = trim((string) (optional($at->sample_type)->name ?? ''));
            $label = (string) $at->name;
            if ($sampleTypeName !== '') {
                $label .= ' ('.$sampleTypeName.')';
            }

            return [
                'id' => (string) $at->id,
                'label' => $label,
            ];
        })
        ->values()
        ->all();
    $sampleTypeIdsForRow = array_values(array_filter(array_map('strval', (array) (
        data_get($this->formData ?? [], 'sample_type_id.'.($rowIndex ?? 0))
        ?? data_get($this->formData ?? [], 'sample_type.'.($rowIndex ?? 0))
        ?? []
    ))));
@endphp

@include('livewire.partials.walk-in-trf-id-label-multi', [
    'wireKey' => $wirePrefix,
    'fieldId' => $fieldId,
    'syncMethod' => 'setWalkInAnalysisTypes',
    'options' => $analysisOptions,
    'selected' => $selectedIds,
    'remountWhen' => md5(json_encode($sampleTypeIdsForRow)),
    'placeholder' => 'Choose analysis type(s)…',
    'emptyHint' => 'Select sample type first.',
    'searchPlaceholder' => 'Search analysis types…',
])
