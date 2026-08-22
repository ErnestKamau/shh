@php
    $wirePrefix = $wirePrefix ?? 'formData.sample_type_id';
    $fieldId = $fieldId ?? 'sample_type_multi';
    $rowIndex = $rowIndex ?? null;
    $selectedRaw = data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), []);
    if (! is_array($selectedRaw)) {
        $selectedRaw = filled($selectedRaw) ? [(string) $selectedRaw] : [];
    }
    $selectedIds = array_values(array_filter(array_map('strval', $selectedRaw)));
    $options = collect($this->walkInSampleTypeOptions ?? [])
        ->map(function ($opt) {
            if (is_array($opt)) {
                return [
                    'value' => (string) ($opt['id'] ?? $opt['value'] ?? ''),
                    'label' => (string) ($opt['label'] ?? $opt['name'] ?? $opt['id'] ?? ''),
                ];
            }

            return [
                'value' => (string) ($opt->id ?? ''),
                'label' => (string) ($opt->name ?? $opt->label ?? ''),
            ];
        })
        ->filter(fn (array $opt): bool => $opt['value'] !== '')
        ->values()
        ->all();
    $showLabel = ! ($hideLabel ?? false);
@endphp

<div wire:key="walk-in-sample-type-{{ $fieldId }}-{{ $rowIndex ?? 'x' }}">
@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
    'label' => $showLabel ? ($label ?? 'Sample type') : null,
    'id' => 'field_'.$fieldId,
    'name' => 'walk_in_sample_type_'.$fieldId,
    'required' => (bool) ($required ?? false),
    'multiple' => false,
    'placeholder' => 'Search sample type…',
    'variant' => 'slate',
    'options' => $options,
    'selected' => $selectedIds !== [] ? [$selectedIds[0]] : [],
    'wireIgnore' => true,
    'dataSyncMethod' => 'setWalkInSampleTypes',
    'dataSyncKey' => $wirePrefix,
    'extraSelectClass' => 'livewire-select2 walk-in-ls-select2 walk-in-ls-select2--sample-type',
    'selectedValuesJson' => json_encode($selectedIds !== [] ? [$selectedIds[0]] : []),
])
</div>
