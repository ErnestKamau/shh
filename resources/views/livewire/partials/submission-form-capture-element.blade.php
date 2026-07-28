@php
    $label = (string) ($element->label ?? $element->name);
    $type = (string) ($element->element_type ?? 'text');
    $name = (string) ($element->name ?? '');
    $required = (bool) ($element->is_required ?? false);
    $rowIndex = null;
    if (preg_match('/\.(\d+)$/', (string) $wirePrefix, $rowIndexMatch)) {
        $rowIndex = (int) $rowIndexMatch[1];
    }
@endphp

<label for="field_{{ $fieldId }}" class="font-weight-bold text-secondary small">
    {{ $label }}
    @if($required)<span class="text-danger">*</span>@endif
</label>

@switch($type)
    @case('textarea')
        <textarea id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm" rows="2"></textarea>
        @break
    @case('date')
        <input type="date" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @break
    @case('number')
        <input type="number" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @break
    @case('email')
        <input type="email" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @break
    @case('select')
        <select id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
            <option value="">Select option</option>
            @foreach(($element->options ?? []) as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <option value="{{ $optValue }}">{{ $optLabel }}</option>
            @endforeach
        </select>
        @break
    @case('radio')
        @foreach(($element->options ?? []) as $opt)
            @php
                $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
            @endphp
            <div class="custom-control custom-radio">
                <input type="radio" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}" value="{{ $optValue }}" class="custom-control-input">
                <label class="custom-control-label small" for="field_{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
            </div>
        @endforeach
        @break
    @case('checkbox')
        @if(is_array($element->options) && $element->options !== [])
            @foreach($element->options as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}.{{ $optValue }}" class="custom-control-input">
                    <label class="custom-control-label small" for="field_{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
                </div>
            @endforeach
        @else
            <div class="custom-control custom-checkbox pt-1">
                <input type="checkbox" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="custom-control-input">
                <label class="custom-control-label small" for="field_{{ $fieldId }}">{{ $label }}</label>
            </div>
        @endif
        @break
    @case('signature')
    @case('contact_signature')
        <div class="acc-signature-pad trf-signature-pad" wire:ignore>
            <canvas
                id="trf-sig-{{ $fieldId }}-canvas"
                class="trf-signature-canvas"
                data-field="{{ $fieldId }}"
                data-livewire-model="{{ $wirePrefix }}"
                style="width: 100%; height: 120px; touch-action: none;"
            ></canvas>
            <div class="acc-signature-actions mt-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary trf-signature-clear"
                    data-canvas="trf-sig-{{ $fieldId }}-canvas"
                    data-input="field_{{ $fieldId }}"
                >Clear</button>
            </div>
        </div>
        <input type="hidden" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}">
        @break
    @case('rich_text')
        @include('livewire.partials.submission-rich-text-editor', [
            'fieldId' => $fieldId,
            'wirePrefix' => $wirePrefix,
            'value' => data_get($this->formData, str_replace('formData.', '', $wirePrefix), ''),
            'rowIndex' => $rowIndex ?? 0,
        ])
        @break
    @case('sample_type_select')
        @include('livewire.partials.walk-in-trf-sample-type-multi', [
            'wirePrefix' => $wirePrefix,
            'fieldId' => $fieldId,
            'rowIndex' => $rowIndex,
        ])
        @break
    @case('analysis_type_select')
        @include('livewire.partials.walk-in-trf-analysis-type-multi', [
            'wirePrefix' => $wirePrefix,
            'fieldId' => $fieldId,
            'rowIndex' => $rowIndex,
        ])
        @break
    @case('analysis_elements_select')
        @include('livewire.partials.walk-in-trf-parameters-cell', [
            'fieldId' => $fieldId,
            'rowIndex' => $rowIndex,
            'wirePrefix' => $wirePrefix,
            'compact' => false,
        ])
        @if($this->parametersForRow($rowIndex)->isEmpty())
            <small class="text-muted d-block mt-1">Select an analysis type to load parameters.</small>
        @endif
        @break
    @case('client_contact_select')
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span></span>
            @if(method_exists($this, 'openWalkInAddContactModal') || method_exists($this, 'openAddSamplePointModal'))
            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1"
                @if(method_exists($this, 'openWalkInAddContactModal'))
                    wire:click="openWalkInAddContactModal"
                @endif
                title="Add customer contact">
                <i class="mdi mdi-plus"></i>
            </button>
            @endif
        </div>
        <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}" class="form-control form-control-sm">
            <option value="">Select contact person</option>
            @foreach(($this->customerContacts ?? collect()) as $contact)
                @php
                    $contactLabel = is_array($contact)
                        ? ($contact['name'] ?? 'Contact')
                        : trim(implode(' ', array_filter([
                            (string) ($contact->first_name ?? ''),
                            (string) ($contact->middle_name ?? ''),
                            (string) ($contact->last_name ?? ''),
                        ])));
                    $contactId = is_array($contact) ? ($contact['id'] ?? '') : $contact->id;
                @endphp
                <option value="{{ $contactId }}">{{ $contactLabel !== '' ? $contactLabel : 'Contact' }}</option>
            @endforeach
        </select>
        @break
    @case('customer_sample_point_select')
    @case('sample_point_select')
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span></span>
            @if(method_exists($this, 'openWalkInAddPointModal'))
            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1"
                wire:click="openWalkInAddPointModal(@js($name !== '' ? $name : 'sampling_location'), @js($rowIndex))"
                title="Add sample point">
                <i class="mdi mdi-plus"></i>
            </button>
            @elseif(method_exists($this, 'openAddSamplePointModal'))
            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1"
                wire:click="openAddSamplePointModal('trf', @js($name !== '' ? $name : 'sampling_location'), @js($rowIndex))"
                title="Add sample point">
                <i class="mdi mdi-plus"></i>
            </button>
            @endif
        </div>
        <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}" class="form-control form-control-sm">
            <option value="">Select sampling {{ $name === 'sampling_point' ? 'point' : 'location' }}</option>
            @foreach(($this->customerSamplePoints ?? collect()) as $point)
                @php
                    $pointId = is_array($point) ? ($point['id'] ?? '') : $point->id;
                    $pointLabel = is_array($point) ? ($point['name'] ?? $pointId) : $point->display_name;
                @endphp
                <option value="{{ $pointId }}">{{ $pointLabel }}</option>
            @endforeach
        </select>
        @break
    @default
        @if($name === 'analysis_type_id' || $type === 'analysis_type_select')
            @include('livewire.partials.walk-in-trf-analysis-type-multi', [
                'wirePrefix' => $wirePrefix,
                'fieldId' => $fieldId,
                'rowIndex' => $rowIndex,
            ])
        @elseif($name === 'sample_type_id' || $type === 'sample_type_select')
            @include('livewire.partials.walk-in-trf-sample-type-multi', [
                'wirePrefix' => $wirePrefix,
                'fieldId' => $fieldId,
                'rowIndex' => $rowIndex,
            ])
        @elseif(in_array($name, ['parameter', 'parameters'], true) || $type === 'analysis_elements_select')
            @include('livewire.partials.walk-in-trf-parameters-cell', [
                'fieldId' => $fieldId,
                'rowIndex' => $rowIndex,
                'wirePrefix' => $wirePrefix,
                'compact' => false,
            ])
            @if($this->parametersForRow($rowIndex)->isEmpty())
                <small class="text-muted d-block mt-1">Select an analysis type to load parameters.</small>
            @endif
        @elseif($type === 'rich_text')
            @include('livewire.partials.submission-rich-text-editor', [
                'fieldId' => $fieldId,
                'wirePrefix' => $wirePrefix,
                'value' => data_get($this->formData, str_replace('formData.', '', $wirePrefix), ''),
                'rowIndex' => $rowIndex ?? 0,
            ])
        @elseif(in_array($name, ['sampling_location', 'sampling_point', 'location'], true))
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span></span>
                @if(method_exists($this, 'openWalkInAddPointModal'))
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1"
                    wire:click="openWalkInAddPointModal(@js($name), @js($rowIndex))"
                    title="Add sample point">
                    <i class="mdi mdi-plus"></i>
                </button>
                @elseif(method_exists($this, 'openAddSamplePointModal'))
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1"
                    wire:click="openAddSamplePointModal('trf', @js($name), @js($rowIndex))"
                    title="Add sample point">
                    <i class="mdi mdi-plus"></i>
                </button>
                @endif
            </div>
            <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}" class="form-control form-control-sm">
                <option value="">Select sampling {{ $name === 'sampling_point' ? 'point' : 'location' }}</option>
                @foreach(($this->customerSamplePoints ?? collect()) as $point)
                    @php
                        $pointId = is_array($point) ? ($point['id'] ?? '') : $point->id;
                        $pointLabel = is_array($point) ? ($point['name'] ?? $pointId) : $point->display_name;
                    @endphp
                    <option value="{{ $pointId }}">{{ $pointLabel }}</option>
                @endforeach
            </select>
        @elseif($name === 'contact_person')
            <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}" class="form-control form-control-sm">
                <option value="">Select contact person</option>
                @foreach(($this->customerContacts ?? collect()) as $contact)
                    @php
                        $contactLabel = is_array($contact)
                            ? ($contact['name'] ?? 'Contact')
                            : trim(implode(' ', array_filter([
                                (string) ($contact->first_name ?? ''),
                                (string) ($contact->middle_name ?? ''),
                                (string) ($contact->last_name ?? ''),
                            ])));
                        $contactId = is_array($contact) ? ($contact['id'] ?? '') : $contact->id;
                    @endphp
                    <option value="{{ $contactId }}">{{ $contactLabel !== '' ? $contactLabel : 'Contact' }}</option>
                @endforeach
            </select>
        @elseif($name === 'sampling_time')
            <input type="time" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @else
            <input type="text" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}" class="form-control form-control-sm">
        @endif
@endswitch

@error(str_replace('formData.', 'formData.', $wirePrefix))
    <div class="invalid-feedback d-block small">{{ $message }}</div>
@enderror
