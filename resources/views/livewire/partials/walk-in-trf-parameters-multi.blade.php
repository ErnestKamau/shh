{{--
  Tests picker — same LS multi-columns Select2 as Edit request details modal.
--}}
@php
    $wirePrefix = $wirePrefix ?? 'formData.parameters';
    $fieldId = $fieldId ?? 'parameters_multi';
    $rowIndex = $rowIndex ?? 0;
    $picker = $this->walkInParameterPickerState((int) $rowIndex);
    $selectedIds = array_values(array_map('strval', $picker['selected'] ?? []));
    $selectOptions = $this->walkInParameterSelectOptions((int) $rowIndex);
    $showLabel = ! ($hideLabel ?? false);
@endphp

<div wire:key="walk-in-tests-{{ $fieldId }}-{{ $rowIndex }}">
    @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns', [
        'label' => $showLabel ? ($label ?? 'Tests') : null,
        'id' => 'field_'.$fieldId,
        'name' => 'walk_in_tests_'.$fieldId,
        'required' => (bool) ($required ?? false),
        'placeholder' => 'Search and add tests…',
        'options' => $selectOptions,
        'selected' => $selectedIds,
        'wireIgnore' => true,
        'dataWireField' => $wirePrefix,
        'dataSyncMethod' => 'setWalkInParameters',
        'dataSyncKey' => (string) ((int) $rowIndex),
        'extraSelectClass' => 'livewire-select2 walk-in-ls-select2',
        'selectedValuesJson' => json_encode($selectedIds),
    ])
</div>
