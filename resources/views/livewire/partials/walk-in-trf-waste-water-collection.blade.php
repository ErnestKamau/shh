@php
    $extraRows = data_get($this->formData ?? [], 'extra_sampling_equipment', []);
    $descEl = $collectionField('sample_sampling_point_description');
    $descValue = (string) data_get($this->formData ?? [], 'sample_sampling_point_description', '');
@endphp

{{-- Row 1: Sampling Date | Sampling Time | Sampling Location --}}
<div class="trf-collection-trio" wire:key="ww-collection-row-1">
    <div wire:key="ww-field-sampling-date">
        @php $el = $collectionField('sampling_date'); @endphp
        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
    </div>
    <div wire:key="ww-field-sampling-time">
        @php $el = $collectionField('sampling_time'); @endphp
        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
    </div>
    <div wire:key="ww-field-sampling-location">
        @php $el = $collectionField('sampling_location'); @endphp
        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
    </div>
</div>

{{-- Row 2: Description (rich text cell) | Apparatus (3-col of 6) | Method --}}
<div class="trf-collection-trio trf-collection-trio--ww-row2" wire:key="ww-collection-row-2">
    <div wire:key="ww-field-description" class="trf-ww-description-cell">
        @if($descEl)
            <label class="ls-field__label mb-1">{{ $descEl->label ?? 'Sample & sampling point description' }}</label>
            @include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
                'label' => null,
                'value' => $descValue,
                'wireModel' => 'formData.sample_sampling_point_description',
                'editorId' => 'ww-sample-sampling-point-description',
                'rowLabel' => 'Sample collection',
                'compact' => true,
            ])
        @endif
    </div>
    <div wire:key="ww-field-apparatus">
        @include('livewire.partials.walk-in-trf-waste-water-apparatus', [
            'collectionField' => $collectionField,
            'fieldMapper' => $fieldMapper,
            'wwApparatus' => $collectionField('sampling_apparatus'),
            'extraRows' => $extraRows,
        ])
    </div>
    <div wire:key="ww-field-method">
        @php $el = $collectionField('method_of_sampling'); @endphp
        @if($el)
            @include('livewire.sampleworkflow.test-request-field-render', [
                'field' => $fieldMapper->toField($el),
                'optionGridClass' => 'trf-option-grid trf-option-grid--method',
            ])
        @endif
    </div>
</div>

{{-- Row 3: Reason (2-col) | Technique (1-col) | Source (3-col) --}}
<div class="trf-ww-collection-row3" wire:key="ww-collection-row-3">
    <div class="trf-ww-collection-row3__reason">
        @php $el = $collectionField('reason_of_collection'); @endphp
        @if($el)
            @include('livewire.sampleworkflow.test-request-field-render', [
                'field' => $fieldMapper->toField($el),
                'optionGridClass' => 'trf-option-grid trf-option-grid--compact trf-option-grid--ww-reason',
            ])
        @endif
    </div>
    <div class="trf-ww-collection-row3__technique">
        @php $el = $collectionField('sampling_technique'); @endphp
        @if($el)
            @include('livewire.sampleworkflow.test-request-field-render', [
                'field' => $fieldMapper->toField($el),
                'optionGridClass' => 'trf-option-grid trf-option-grid--ww-technique',
            ])
        @endif
    </div>
    <div class="trf-ww-collection-row3__source">
        @php $el = $collectionField('sampling_source'); @endphp
        @if($el)
            @include('livewire.sampleworkflow.test-request-field-render', [
                'field' => $fieldMapper->toField($el),
                'optionGridClass' => 'trf-option-grid trf-option-grid--ww-source',
            ])
        @endif
    </div>
</div>

{{-- Row 4: Transport | Sample types (LWS-036 checkboxes) --}}
<div class="trf-ww-collection-row4" wire:key="ww-collection-row-4">
    <div wire:key="ww-field-transport">
        @php $el = $collectionField('transport_condition'); @endphp
        @if($el)
            @include('livewire.sampleworkflow.test-request-field-render', [
                'field' => $fieldMapper->toField($el),
                'optionGridClass' => 'trf-option-grid trf-option-grid--ww-transport',
            ])
        @endif
    </div>
    <div wire:key="ww-field-sample-types">
        @php $el = $collectionField('sample_types_ww'); @endphp
        @if($el)
            @include('livewire.sampleworkflow.test-request-field-render', [
                'field' => array_merge($fieldMapper->toField($el), ['label' => 'Sample types']),
                'optionGridClass' => 'trf-option-grid trf-option-grid--ww-sample-types',
            ])
        @endif
    </div>
</div>

{{-- Row 5: Field data (4-col input grid) --}}
<div class="trf-ww-field-data-block mt-2" wire:key="ww-collection-row-5">
    <div class="trf-ww-field-data-block__title ls-type-label mb-2">Field data</div>
    <div class="trf-ww-field-data-grid">
        @foreach([
            'field_data_quantity' => 'Quantity',
            'field_data_appearance' => 'Appearance',
            'field_data_color' => 'Color',
            'field_data_odor' => 'Odor',
            'field_data_ph' => 'pH',
            'field_data_temperature' => 'Temperature',
            'field_data_free_chlorine' => 'Free chlorine',
        ] as $fieldName => $fallbackLabel)
            @php $el = $collectionField($fieldName); @endphp
            @if($el)
                <div class="trf-ww-field-data-grid__cell" wire:key="ww-field-data-{{ $fieldName }}">
                    <div class="ls-field">
                        <label class="ls-field__label" for="ww-{{ $fieldName }}">{{ $el->label ?? $fallbackLabel }}</label>
                        <div class="ls-field__control">
                            <input id="ww-{{ $fieldName }}"
                                type="text"
                                class="ls-field__input form-control form-control-sm"
                                wire:model.defer="formData.{{ $fieldName }}"
                                placeholder="{{ $fallbackLabel }}">
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

{{-- Kept in schema for PDF/lab use; not part of the 5 visual rows --}}
<div class="d-none" wire:key="ww-field-hidden-schema">
    @php $el = $collectionField('date_received'); @endphp
    @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
    @php $el = $collectionField('field_data_requirements'); @endphp
    @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
</div>
