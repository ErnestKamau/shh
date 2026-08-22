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
                'value' => (string) $at->id,
                'label' => $label,
            ];
        })
        ->values()
        ->all();
    $showLabel = ! ($hideLabel ?? false);
@endphp

<div wire:key="walk-in-analysis-type-{{ $fieldId }}-{{ $rowIndex ?? 'x' }}">
@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
    'label' => $showLabel ? ($label ?? 'Analysis Type') : null,
    'id' => 'field_'.$fieldId,
    'name' => 'walk_in_analysis_type_'.$fieldId,
    'required' => (bool) ($required ?? false),
    'placeholder' => 'Search analysis type…',
    'variant' => 'slate',
    'options' => $analysisOptions,
    'selected' => $selectedIds,
    'wireIgnore' => true,
    'dataSyncMethod' => 'setWalkInAnalysisTypes',
    'dataSyncKey' => $wirePrefix,
    'extraSelectClass' => 'livewire-select2 walk-in-ls-select2',
    'selectedValuesJson' => json_encode($selectedIds),
])
</div>
