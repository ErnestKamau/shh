@php
    $wirePrefix = $wirePrefix ?? ('formData.' . ($field['name'] ?? ''));
    $fieldId = $fieldId ?? ($field['name'] ?? 'field');
    $rowIndex = $rowIndex ?? null;
    $fieldName = $field['name'] ?? '';
    $compact = $compact ?? false;
    $hideLabel = $hideLabel ?? false;
    $useCrmSelectors = (bool) ($useCrmSelectors ?? true);
    $showCrmActions = (bool) ($showCrmActions ?? true);
    $optionCols = $optionCols ?? ($field['option_cols'] ?? null);
    $optionTight = (bool) ($optionTight ?? ($field['option_tight'] ?? false));
    $optionColClass = match ((int) $optionCols) {
        4 => 'col-3',
        3 => 'col-4',
        2 => 'col-6',
        default => 'col-6',
    };
    $optionRowClass = 'row pt-1';
    if ($optionCols !== null && (int) $optionCols > 1) {
        $optionRowClass .= ' rft-option-grid rft-option-grid--cols-'.(int) $optionCols;
        if ($optionTight) {
            $optionRowClass .= ' rft-option-grid--tight';
        }
    }
    $defaultOptionGridClass = match ($fieldName) {
        'transport_condition' => 'trf-option-grid trf-option-grid--transport',
        'sampling_apparatus' => 'trf-option-grid trf-option-grid--apparatus',
        'method_of_sampling' => 'trf-option-grid trf-option-grid--method',
        'reason_of_collection' => 'trf-option-grid trf-option-grid--compact',
        default => 'trf-option-grid trf-option-grid--auto',
    };
    $optionGridClass = $optionGridClass ?? $defaultOptionGridClass;
    $controlClass = $compact ? 'form-control form-control-xs' : 'form-control form-control-sm';
    $compactStyle = $compact ? 'padding: 2px 5px; height: auto; font-size: 11px;' : '';
    $labelClass = $compact ? 'font-weight-bold text-secondary small' : 'ls-field__label';
    $outerLabelShown = ! $hideLabel
        && ! $compact
        && ! in_array($field['type'] ?? '', ['client_select', 'client_unit_select', 'client_contact_select', 'customer_sample_point_select', 'signature'], true)
        && ! in_array($fieldName, ['customer_name', 'client_name', 'customer', 'client', 'company_unit_id', 'contact_person', 'sampling_location', 'sampling_point', 'customer_email', 'email', 'customer_tax_id', 'state_of_sample', 'sample_type_id', 'sample_type', 'analysis_type_id', 'analysis_type', 'analysis_types', 'parameters', 'parameter', 'sample_temp', 'field_sample_temp', 'sample_temperature', 'field_data_temperature'], true);
    // When parent already printed a label (hideLabel) or this partial printed one, LS includes must not repeat it.
    $lsFieldLabel = ($hideLabel || $compact || $outerLabelShown)
        ? null
        : ($field['label'] ?? $fieldName);
    $walkInFieldErrorKeys = match (true) {
        in_array($fieldName, ['customer_name', 'client_name', 'customer', 'client'], true) || ($field['type'] ?? '') === 'client_select' => [
            'formData.customer_name',
            'formData.client_name',
            'formData.customer',
            'formData.client',
        ],
        ($field['type'] ?? '') === 'client_contact_select' || $fieldName === 'contact_person' => [
            'formData.contact_person',
        ],
        ($field['type'] ?? '') === 'client_unit_select' || $fieldName === 'company_unit_id' => [
            'formData.company_unit_id',
        ],
        default => [$wirePrefix],
    };
    $fieldError = null;
    foreach ($walkInFieldErrorKeys as $errorKey) {
        if ($errors->has($errorKey)) {
            $fieldError = $errors->first($errorKey);
            break;
        }
    }
    $usesInlineFieldError = in_array($fieldName, ['parameter', 'parameters'], true)
        || ($field['type'] ?? '') === 'analysis_elements_select'
        || in_array($fieldName, ['customer_name', 'client_name', 'customer', 'client', 'company_unit_id', 'contact_person'], true)
        || in_array($field['type'] ?? '', ['client_select', 'client_unit_select', 'client_contact_select'], true)
        || in_array($fieldName, ['customer_email', 'email', 'mobile_number', 'customer_phone', 'tel_fax_no', 'phone', 'telephone', 'customer_address', 'address', 'physical_address'], true)
        || in_array($field['type'] ?? '', ['date', 'time', 'number'], true)
        || ($field['type'] ?? '') === 'state_of_sample';
@endphp
@if($outerLabelShown)
    <label for="field_{{ $fieldId }}" class="{{ $labelClass }}">
        {{ $field['label'] ?? $fieldName }}
        @if($field['required'] ?? false)
            <span class="ls-req text-danger">*</span>
        @endif
    </label>
@endif

@if($fieldName === 'job_number' || $fieldName === 'crm_contact_id')
    {{-- Hidden in walk-in TRF modal --}}
@elseif($useCrmSelectors && (in_array($fieldName, ['customer_name', 'client_name', 'customer', 'client'], true) || ($field['type'] ?? '') === 'client_select'))
    @php
        $clientOptions = $this->customers->map(fn ($cust) => [
            'value' => (string) $cust->id,
            'label' => (string) $cust->name,
        ])->values()->all();
        $clientSelected = (string) ($this->selectedCrmCustomerId ?? '');
    @endphp
    <div class="trf-field-col {{ $showCrmActions ? 'trf-field-col--with-action' : '' }}">
        <div class="trf-field-col__main">
            @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                'label' => ($hideLabel ?? false) ? null : ($field['label'] ?? 'Client'),
                'id' => 'field_'.$fieldId,
                'name' => 'selectedCrmCustomerId',
                'placeholder' => 'Search client…',
                'options' => $clientOptions,
                'selected' => $clientSelected !== '' ? $clientSelected : null,
                'success' => $clientSelected !== '',
                'wireModel' => 'selectedCrmCustomerId',
                'wireLive' => true,
                'required' => (bool) ($field['required'] ?? false),
                'error' => $fieldError,
            ])
        </div>
        @if($showCrmActions)
            <button type="button"
                class="btn btn-xs btn-outline-primary trf-field-col__action"
                wire:click="openWalkInAddCustomerModal"
                title="Add client"
                aria-label="Add client">
                <i class="mdi mdi-plus" aria-hidden="true"></i>
            </button>
        @endif
    </div>
@elseif(in_array($fieldName, ['sample_type_id', 'sample_type'], true) || ($field['type'] ?? '') === 'sample_type_select')
    @include('livewire.partials.walk-in-trf-sample-type-multi', [
        'wirePrefix' => $wirePrefix,
        'fieldId' => $fieldId,
        'rowIndex' => $rowIndex,
        'hideLabel' => $hideLabel,
        'label' => $field['label'] ?? 'Sample type',
        'required' => (bool) ($field['required'] ?? false),
    ])
@elseif(in_array($fieldName, ['analysis_type', 'analysis_types'], true))
    @include('livewire.partials.walk-in-trf-analysis-type-multi', [
        'wirePrefix' => $wirePrefix,
        'fieldId' => $fieldId,
        'rowIndex' => $rowIndex,
        'hideLabel' => $hideLabel,
        'label' => $field['label'] ?? 'Analysis Type',
        'required' => (bool) ($field['required'] ?? false),
    ])
@elseif(($field['type'] ?? '') === 'analysis_type_select' || $fieldName === 'analysis_type_id')
    @include('livewire.partials.walk-in-trf-analysis-type-multi', [
        'wirePrefix' => $wirePrefix,
        'fieldId' => $fieldId,
        'rowIndex' => $rowIndex,
        'hideLabel' => $hideLabel,
        'label' => $field['label'] ?? 'Analysis Type',
        'required' => (bool) ($field['required'] ?? false),
    ])
@elseif(($field['type'] ?? '') === 'rich_text' && $fieldName === 'sample_sampling_point_description')
    @include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
        'label' => ($hideLabel ?? false) ? null : ($field['label'] ?? 'Sample & sampling point description'),
        'value' => data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), ''),
        'wireModel' => $wirePrefix,
        'editorId' => 'field-'.$fieldId,
        'rowLabel' => 'Sample collection',
        'compact' => true,
    ])
@elseif(($field['type'] ?? '') === 'rich_text')
    @include('livewire.partials.submission-rich-text-editor', [
        'fieldId' => $fieldId,
        'wirePrefix' => $wirePrefix,
        'value' => data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), ''),
        'rowIndex' => $rowIndex ?? 0,
    ])
@elseif($fieldName === 'state_of_sample')
    @php
        $stateOptions = collect($field['options'] ?? [])->map(function ($opt) {
            if (is_array($opt)) {
                return [
                    'value' => (string) ($opt['value'] ?? $opt['label'] ?? ''),
                    'label' => (string) ($opt['label'] ?? $opt['value'] ?? ''),
                ];
            }

            return [
                'value' => (string) $opt,
                'label' => (string) $opt,
            ];
        })->filter(fn ($opt) => $opt['value'] !== '')->values()->all();
        $stateSelected = (string) data_get($this, $wirePrefix, '');
    @endphp
    <div wire:key="walk-in-state-{{ $fieldId }}-{{ md5($stateSelected) }}">
        @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
            'label' => ($hideLabel ?? false) ? null : ($field['label'] ?? 'State of sample'),
            'id' => 'field_'.$fieldId,
            'name' => $wirePrefix,
            'placeholder' => 'Search…',
            'options' => $stateOptions,
            'selected' => $stateSelected !== '' ? $stateSelected : null,
            'success' => $stateSelected !== '',
            'wireModel' => $wirePrefix,
            'required' => (bool) ($field['required'] ?? false),
            'error' => $fieldError,
        ])
    </div>
@elseif(in_array($fieldName, ['sample_temp', 'field_sample_temp', 'sample_temperature', 'field_data_temperature'], true))
    @php
        $tempValue = data_get($this, $wirePrefix);
    @endphp
    <div class="ls-field {{ filled($tempValue) ? 'is-success' : '' }}">
        @if(! ($hideLabel ?? false))
            <label class="ls-field__label" for="field_{{ $fieldId }}">
                {{ $field['label'] ?? ($fieldName === 'field_data_temperature' ? 'Field data — Temperature (°C)' : 'Sample Temp (°C)') }}
                @if($field['required'] ?? false)
                    <span class="ls-req">*</span>
                @endif
            </label>
        @endif
        <div class="ls-field__control">
            <span class="ls-field__affix ls-field__affix--prefix"><i class="mdi mdi-thermometer"></i></span>
            <input
                id="field_{{ $fieldId }}"
                type="number"
                step="any"
                class="ls-field__input"
                wire:model.defer="{{ $wirePrefix }}"
                placeholder="Temp"
            >
            <span class="ls-field__affix ls-field__affix--suffix">°C</span>
        </div>
    </div>
@elseif(in_array($fieldName, ['parameter', 'parameters'], true) || ($field['type'] ?? '') === 'analysis_elements_select')
    @include('livewire.partials.walk-in-trf-parameters-cell', [
        'fieldId' => $fieldId,
        'rowIndex' => $rowIndex ?? 0,
        'wirePrefix' => $wirePrefix,
        'formData' => $this->formData ?? [],
    ])
@elseif(($field['type'] ?? '') === 'signature')
    @php
        $sigFieldName = $fieldName ?: 'customer_rep_signature';
        $canvasId = 'trf-sig-'.$fieldId;
    @endphp
    @if(! $hideLabel && ! $compact)
        <label class="font-weight-bold text-secondary small d-block">
            {{ $field['label'] ?? 'Signature' }}
            @if($field['required'] ?? false)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    <div class="acc-signature-pad trf-signature-pad" wire:ignore>
        <canvas id="{{ $canvasId }}-canvas" class="trf-signature-canvas" data-field="{{ $fieldId }}" data-livewire-model="{{ $wirePrefix }}" style="width: 100%; height: {{ $compact ? '80' : '140' }}px; touch-action: none; display: block; background: #fff;"></canvas>
        <div class="acc-signature-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary trf-signature-clear" data-canvas="{{ $canvasId }}-canvas" data-input="field_{{ $fieldId }}">Clear</button>
        </div>
    </div>
    <input type="hidden" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}">
@elseif(($field['type'] ?? '') === 'textarea')
    <textarea id="field_{{ $fieldId }}" wire:model.defer="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        placeholder="Enter {{ strtolower($field['label'] ?? $fieldName) }}" rows="{{ $compact ? 2 : 2 }}"
        @if($compactStyle) style="{{ $compactStyle }}" @endif
        @if($field['readonly'] ?? false) readonly @endif></textarea>
@elseif(($field['type'] ?? '') === 'radio')
    <div class="{{ $compact ? 'trf-option-grid trf-option-grid--auto' : $optionGridClass }}" role="radiogroup" aria-label="{{ $field['label'] ?? $fieldName }}">
        @foreach(($field['options'] ?? []) as $opt)
            @php
                $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
            @endphp
            <label class="trf-option-chip" for="field_{{ $fieldId }}_{{ $loop->index }}">
                <input type="radio" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}"
                    value="{{ $optValue }}" class="@error($wirePrefix) is-invalid @enderror">
                <span class="trf-option-chip__label">{{ $optLabel }}</span>
            </label>
        @endforeach
    </div>
@elseif(($field['type'] ?? '') === 'select')
    @php
        $isMulti = in_array($fieldName, [
            'sampling_apparatus',
            'method_of_sampling',
            'reason_of_collection',
            'transport_condition',
            'sampling_source',
            'sample_types_ww',
            'sampling_technique',
            'field_data_requirements',
            'sample_condition',
            'test_category',
            'test_requirements',
        ], true);
        $selectOptions = ($fieldName === 'sampled_by')
            ? \App\Services\Sampleworkflow\SampledByParty::selectOptions()
            : ($field['options'] ?? []);
    @endphp
    @if($isMulti)
        <div class="{{ $optionGridClass }}" role="group" aria-label="{{ $field['label'] ?? $fieldName }}">
            @foreach($selectOptions as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <label class="trf-option-chip" for="field_{{ $fieldId }}_{{ Str::slug((string) $optValue) }}">
                    <input type="checkbox" id="field_{{ $fieldId }}_{{ Str::slug((string) $optValue) }}"
                        wire:model="{{ $wirePrefix }}.{{ $optValue }}">
                    <span class="trf-option-chip__label">{{ $optLabel }}</span>
                </label>
            @endforeach
        </div>
    @else
        <select id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
            class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
            @if($compactStyle) style="{{ $compactStyle }}" @endif>
            <option value="">Select option</option>
            @foreach($selectOptions as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <option value="{{ $optValue }}">{{ $optLabel }}</option>
            @endforeach
        </select>
    @endif
@elseif(($field['type'] ?? '') === 'checkbox')
    @if(!empty($field['options']) && is_array($field['options']))
        <div class="{{ $compact ? 'trf-option-grid trf-option-grid--auto' : $optionGridClass }}" role="group" aria-label="{{ $field['label'] ?? $fieldName }}">
            @foreach($field['options'] as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <label class="trf-option-chip" for="field_{{ $fieldId }}_{{ Str::slug((string) $optValue) }}">
                    <input type="checkbox" id="field_{{ $fieldId }}_{{ Str::slug((string) $optValue) }}"
                        wire:model="{{ $wirePrefix }}.{{ $optValue }}">
                    <span class="trf-option-chip__label">{{ $optLabel }}</span>
                </label>
            @endforeach
        </div>
    @else
        <label class="trf-option-chip" for="field_{{ $fieldId }}" style="width:auto; max-width:100%;">
            <input type="checkbox" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
                class="@error($wirePrefix) is-invalid @enderror">
            <span class="trf-option-chip__label">{{ $field['label'] ?? $fieldName }}</span>
        </label>
    @endif
@elseif($useCrmSelectors && (($field['type'] ?? '') === 'client_unit_select' || $fieldName === 'company_unit_id'))
    @php
        $unitOptions = $this->customerCompanyUnits->map(fn ($unit) => [
            'value' => (string) $unit->id,
            'label' => (string) $unit->name,
        ])->values()->all();
        $unitSelected = (string) data_get($this, $wirePrefix, '');
        $selectedCustomerId = trim((string) ($this->selectedCrmCustomerId ?? ''));
        if ($selectedCustomerId === '') {
            $selectedCustomerId = 'none';
        }
    @endphp
    <div class="trf-field-col {{ $showCrmActions ? 'trf-field-col--with-action' : '' }}" wire:key="walk-in-company-unit-{{ $selectedCustomerId }}-{{ count($unitOptions) }}">
        <div class="trf-field-col__main">
            @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                'label' => ($hideLabel ?? false) ? null : ($field['label'] ?? 'Company unit / Site name'),
                'id' => 'field_'.$fieldId,
                'name' => $wirePrefix,
                'placeholder' => $selectedCustomerId === 'none' || $selectedCustomerId === ''
                    ? 'Select a client first…'
                    : (count($unitOptions) === 0 ? 'No company units for this client' : 'Search company unit…'),
                'options' => $unitOptions,
                'selected' => $unitSelected !== '' ? $unitSelected : null,
                'success' => $unitSelected !== '',
                'wireModel' => $wirePrefix,
                'wireLive' => true,
                'required' => (bool) ($field['required'] ?? false),
                'error' => $fieldError,
            ])
        </div>
        @if($showCrmActions)
            <button type="button" class="btn btn-xs btn-outline-primary trf-field-col__action"
                wire:click="openWalkInAddUnitModal" title="Add company unit" aria-label="Add company unit">
                <i class="mdi mdi-plus" aria-hidden="true"></i>
            </button>
        @endif
    </div>
@elseif($useCrmSelectors && (($field['type'] ?? '') === 'client_contact_select' || $fieldName === 'contact_person'))
    @php
        $contactOptions = $this->customerContacts->map(function ($contact) {
            $contactLabel = trim(implode(' ', array_filter([
                (string) ($contact->first_name ?? ''),
                (string) ($contact->middle_name ?? ''),
                (string) ($contact->last_name ?? ''),
            ])));

            return [
                'value' => (string) $contact->id,
                'label' => $contactLabel !== '' ? $contactLabel : 'Contact',
                'meta' => [
                    'email' => (string) ($contact->email ?? ''),
                    'phone' => (string) ($contact->phone ?? $contact->mobile ?? ''),
                ],
            ];
        })->values()->all();
        $contactSelected = (string) data_get($this, $wirePrefix, '');
        $unitIdForContacts = trim((string) ($this->formData['company_unit_id'] ?? ''));
        $unitGateHint = $unitIdForContacts === ''
            ? 'Select a company unit first…'
            : (count($contactOptions) === 0 ? 'No CRM contacts linked to this company unit.' : null);
    @endphp
    <div class="trf-field-col {{ $showCrmActions ? 'trf-field-col--with-action' : '' }}" wire:key="walk-in-contact-{{ $unitIdForContacts !== '' ? $unitIdForContacts : 'none' }}-{{ count($contactOptions) }}">
        <div class="trf-field-col__main">
            @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                'label' => ($hideLabel ?? false) ? null : ($field['label'] ?? 'Contact person'),
                'id' => 'field_'.$fieldId,
                'name' => $wirePrefix,
                'placeholder' => $unitGateHint ?? 'Search contacts…',
                'options' => $contactOptions,
                'selected' => $contactSelected !== '' ? $contactSelected : null,
                'success' => $contactSelected !== '',
                'wireModel' => $wirePrefix,
                'wireLive' => true,
                'required' => (bool) ($field['required'] ?? false),
                'error' => $fieldError,
            ])
        </div>
        @if($showCrmActions)
            <button type="button" class="btn btn-xs btn-outline-primary trf-field-col__action"
                wire:click="openWalkInAddContactModal" title="Add customer contact" aria-label="Add customer contact">
                <i class="mdi mdi-plus" aria-hidden="true"></i>
            </button>
        @endif
    </div>
@elseif($useCrmSelectors && (($field['type'] ?? '') === 'customer_sample_point_select' || in_array($fieldName, ['sampling_location', 'sampling_point'], true)))
    @php
        $locationWireValue = data_get($this, $wirePrefix);
        if (is_array($locationWireValue)) {
            $locationWireValue = $locationWireValue[$rowIndex ?? 0] ?? '';
        }
        $locationWireValue = trim((string) $locationWireValue);
        $knownPointIds = $this->customerSamplePoints->pluck('id')->map(fn ($id) => (string) $id);
        $orphanLocation = $locationWireValue !== '' && ! $knownPointIds->contains($locationWireValue)
            ? $locationWireValue
            : null;
        $pointOptions = $this->customerSamplePoints->map(fn ($point) => [
            'value' => (string) $point->id,
            'label' => (string) $point->display_name,
        ])->values()->all();
        if ($orphanLocation !== null) {
            array_unshift($pointOptions, ['value' => $orphanLocation, 'label' => $orphanLocation]);
        }
        $selectedUnitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        $locationHint = $selectedUnitId === ''
            ? 'Select a company unit first'
            : ($pointOptions === [] ? 'No sampling locations for '.($this->selectedCompanyUnitName ?? 'selected unit') : null);
        $locationWireLive = false;
    @endphp
    <div class="trf-field-col {{ $showCrmActions ? 'trf-field-col--with-action' : '' }}" wire:key="walk-in-point-{{ $selectedUnitId !== '' ? $selectedUnitId : 'none' }}-{{ count($pointOptions) }}-{{ $rowIndex ?? 'x' }}-{{ md5($locationWireValue) }}">
        <div class="trf-field-col__main">
            @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                'label' => ($hideLabel ?? false) ? null : ($field['label'] ?? 'Sampling location'),
                'id' => 'field_'.$fieldId,
                'name' => $wirePrefix,
                'placeholder' => $locationHint ?? 'Search sampling location…',
                'options' => $pointOptions,
                'selected' => $locationWireValue !== '' ? $locationWireValue : null,
                'success' => $locationWireValue !== '',
                'wireModel' => $wirePrefix,
                'wireLive' => $locationWireLive,
                'allowCustom' => true,
                'required' => (bool) ($field['required'] ?? false),
            ])
        </div>
        @if($showCrmActions)
            <button type="button" class="btn btn-xs btn-outline-primary trf-field-col__action"
                wire:click="openWalkInAddPointModal(@js($fieldName !== '' ? $fieldName : 'sampling_location'), @js($rowIndex))"
                title="Add sampling location"
                aria-label="Add sampling location">
                <i class="mdi mdi-plus"></i>
            </button>
        @endif
    </div>
@elseif($fieldName === 'customer_tax_id')
    {{-- Removed from walk-in TRF --}}
@elseif($fieldName === 'customer_email' || $fieldName === 'email')
    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
        'label' => $lsFieldLabel,
        'id' => 'field_'.$fieldId,
        'name' => $wirePrefix,
        'type' => 'email',
        'wireModel' => $wirePrefix,
        'placeholder' => $compact ? '' : 'Enter email',
        'disabled' => (bool) ($field['readonly'] ?? false),
        'success' => filled(data_get($this, $wirePrefix)),
        'error' => $fieldError,
    ])
@elseif(($field['type'] ?? '') === 'date')
    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
        'label' => $lsFieldLabel,
        'id' => 'field_'.$fieldId,
        'name' => $wirePrefix,
        'type' => 'date',
        'wireModel' => $wirePrefix,
        'success' => filled(data_get($this, $wirePrefix)),
        'error' => $fieldError,
    ])
@elseif(($field['type'] ?? '') === 'time' || $fieldName === 'sampling_time')
    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
        'label' => $lsFieldLabel,
        'id' => 'field_'.$fieldId,
        'name' => $wirePrefix,
        'type' => 'time',
        'wireModel' => $wirePrefix,
        'success' => filled(data_get($this, $wirePrefix)),
        'error' => $fieldError,
    ])
@elseif(($field['type'] ?? '') === 'datetime-local')
    <input type="datetime-local" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        @if($compactStyle) style="{{ $compactStyle }}" @endif>
@elseif(in_array($fieldName, ['sampling_point_manual', 'manual_sampling_point'], true))
    @php
        $samplingPointValue = (string) data_get($this, $wirePrefix, '');
        $crmSamplingPointOptions = collect();
        if ($useCrmSelectors && method_exists($this, 'getCustomerSamplePointsProperty')) {
            $crmSamplingPointOptions = $this->customerSamplePoints
                ->map(fn ($point) => [
                    'value' => (string) $point->display_name,
                    'label' => (string) $point->display_name,
                ])
                ->filter(fn (array $opt): bool => $opt['value'] !== '')
                ->values();
        }
        $knownSamplingPointValues = $crmSamplingPointOptions->pluck('value')->all();
        if ($samplingPointValue !== '' && ! in_array($samplingPointValue, $knownSamplingPointValues, true)) {
            $crmSamplingPointOptions = $crmSamplingPointOptions
                ->prepend(['value' => $samplingPointValue, 'label' => $samplingPointValue])
                ->values();
        }
    @endphp
    @if($crmSamplingPointOptions->isNotEmpty())
        <div class="d-flex align-items-center" style="gap: 0.35rem;">
            <div class="flex-grow-1 min-width-0">
                @include('layouts.lab.partials.ls-ui.fields.ls-field-select', [
                    'label' => $lsFieldLabel,
                    'id' => 'field_'.$fieldId,
                    'name' => $wirePrefix,
                    'wireModel' => $wirePrefix,
                    'placeholder' => 'Select sampling point…',
                    'options' => $crmSamplingPointOptions->all(),
                    'success' => filled($samplingPointValue),
                    'error' => $fieldError,
                ])
            </div>
            @if(method_exists($this, 'openWalkInAddPointModal'))
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary flex-shrink-0"
                    title="Add sampling point"
                    wire:click="openWalkInAddPointModal(@js($fieldName), @js($rowIndex ?? null))"
                >
                    <i class="mdi mdi-plus"></i>
                </button>
            @endif
        </div>
    @else
        <div wire:key="walk-in-sampling-point-{{ $fieldId }}-{{ md5($samplingPointValue) }}">
            @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                'label' => $lsFieldLabel,
                'id' => 'field_'.$fieldId,
                'name' => $wirePrefix,
                'wireModel' => $wirePrefix,
                'placeholder' => $compact ? '' : 'Enter '.strtolower($field['label'] ?? 'sampling point'),
                'disabled' => (bool) ($field['readonly'] ?? false),
                'success' => filled($samplingPointValue),
                'error' => $fieldError,
            ])
        </div>
    @endif
@elseif(($field['type'] ?? '') === 'number')
    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
        'label' => $lsFieldLabel,
        'id' => 'field_'.$fieldId,
        'name' => $wirePrefix,
        'type' => 'number',
        'wireModel' => $wirePrefix,
        'placeholder' => $compact ? '' : 'Enter '.strtolower($field['label'] ?? $fieldName),
        'success' => filled(data_get($this, $wirePrefix)),
        'error' => $fieldError,
    ])
@else
    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
        'label' => $lsFieldLabel,
        'id' => 'field_'.$fieldId,
        'name' => $wirePrefix,
        'wireModel' => $wirePrefix,
        'placeholder' => $compact ? '' : 'Enter '.strtolower($field['label'] ?? $fieldName),
        'disabled' => (bool) ($field['readonly'] ?? false),
        'success' => filled(data_get($this, $wirePrefix)),
        'error' => $fieldError,
    ])
@endif

@if($fieldError && ! $usesInlineFieldError)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $fieldError }}</div>
@endif
