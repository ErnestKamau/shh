@php
    $configBlocks = !empty($configFieldsGroupedForCapture)
        ? $configFieldsGroupedForCapture
        : (($configFields ?? collect())->isNotEmpty()
            ? [['section' => null, 'fields' => $configFields]]
            : []);
@endphp

@foreach($configBlocks as $blockIndex => $block)
<section class="procedure-section mb-4" wire:key="cfg-section-{{ $blockIndex }}-ws-{{ $this->selectedWorksheetId }}">
    <div class="card shadow-sm border-0 procedure-section-card mb-4">
        <div class="card-header procedure-section-header bg-white">
            <h6 class="mb-0">
                @if($block['section'])
                <i class="mdi mdi-folder-outline text-success"></i>
                {{ $block['section']->title }}
                @if($block['section']->description)
                <small class="text-muted d-block fw-normal mt-1">{{ $block['section']->description }}</small>
                @endif
                @else
                <i class="mdi mdi-cog-outline text-success"></i>
                Configurable Fields
                @endif
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="row g-4 p-3">
                @foreach($block['fields'] as $field)
                <div class="col-xl-4 col-lg-6 mb-0" wire:key="cfg-{{ $field->id }}-ws-{{ $this->selectedWorksheetId }}-cr-{{ $selectedResults->isNotEmpty() ? $selectedResults->first()->id : 'none' }}">
                    <div class="config-field-block">
                        <label class="form-label fw-medium">
                            {{ $field->label }}
                            @if($field->is_required)
                            <span class="text-danger">*</span>
                            @endif
                        </label>
                        @if($field->help_text)
                        <small class="text-muted d-block mb-2">{{ $field->help_text }}</small>
                        @endif
                        @if($selectedResults->isNotEmpty())
                        <div class="config-field-row mb-3">
                            @if($selectedResults->count() > 1)
                            <small class="text-muted d-block mb-1">Same value for {{ $selectedResults->count() }} selected samples</small>
                            @endif
                            @php
                                $type = $field->field_type ?: (
                                    in_array($field->model_tied_to ?? '', ['users','sample_details','sample_types','methods','captured_results','report_formats'])
                                        ? 'dataset'
                                        : 'input'
                                );
                            @endphp
                            @if(\App\Models\Procedures\ProcedureConfigField::isSampleDerivedType($field->field_type))
                            <input type="text"
                                class="form-control bg-light"
                                readonly
                                value="{{ $this->resolveConfigFieldDisplayValue($field, $selectedResults->first()) }}">
                            <small class="text-muted d-block mt-1">From linked sample (read-only).</small>
                            @elseif($type === 'datetime')
                            <input type="datetime-local"
                                class="form-control"
                                wire:model.live.debounce.1000ms="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                wire:blur="autosaveConfigField({{ $field->id }})">
                            @elseif($type === 'date')
                            <input type="date"
                                class="form-control"
                                wire:model.live.debounce.1000ms="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                wire:blur="autosaveConfigField({{ $field->id }})">
                            @elseif($type === 'number')
                            <input type="number"
                                class="form-control"
                                wire:model.live.debounce.1000ms="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                wire:blur="autosaveConfigField({{ $field->id }})">
                            @elseif($type === 'checkbox')
                            <div class="form-check">
                                <input type="checkbox"
                                    class="form-check-input"
                                    wire:model.live="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                    value="1"
                                    wire:blur="autosaveConfigField({{ $field->id }})">
                            </div>
                            @elseif($type === 'textarea')
                            <textarea
                                class="form-control"
                                rows="2"
                                wire:model.live.debounce.1000ms="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                wire:blur="autosaveConfigField({{ $field->id }})"></textarea>
                            @elseif($type === 'input' || $type === '' || $type === null)
                            <input type="text"
                                class="form-control"
                                wire:model.live.debounce.1000ms="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                wire:blur="autosaveConfigField({{ $field->id }})">
                            @elseif($type === 'dataset')
                            <div wire:ignore
                                class="procedure-select2-wrap"
                                data-select-type="config"
                                data-config-mode="single"
                                data-captured-result-id="{{ $selectedResults->first()->id }}"
                                data-field-id="{{ $field->id }}"
                                data-initial='@json(isset($configFieldValues[$selectedResults->first()->id][$field->id]) && $configFieldValues[$selectedResults->first()->id][$field->id] ? [$configFieldValues[$selectedResults->first()->id][$field->id]] : [])'>
                                <select class="form-control procedure-select2 no-select2" data-placeholder="Select...">
                                    <option value="">Select...</option>
                                    @foreach($this->getDatasetOptions($field->model_tied_to ?? '', $field) as $option)
                                    <option value="{{ $option->id }}" @if(isset($configFieldValues[$selectedResults->first()->id][$field->id]) && $configFieldValues[$selectedResults->first()->id][$field->id] == $option->id) selected @endif>{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @elseif($type === 'dataset_multiselect')
                            <div wire:ignore
                                class="procedure-select2-wrap"
                                data-select-type="config"
                                data-config-mode="multiple"
                                data-captured-result-id="{{ $selectedResults->first()->id }}"
                                data-field-id="{{ $field->id }}"
                                data-initial='@json($configFieldValues[$selectedResults->first()->id][$field->id] ?? [])'>
                                <select class="form-control procedure-select2 no-select2" multiple="multiple" data-placeholder="Select...">
                                    @foreach($this->getDatasetOptions($field->model_tied_to ?? '', $field) as $option)
                                    <option value="{{ $option->id }}" @if(isset($configFieldValues[$selectedResults->first()->id][$field->id]) && is_array($configFieldValues[$selectedResults->first()->id][$field->id]) && in_array($option->id, $configFieldValues[$selectedResults->first()->id][$field->id])) selected @endif>{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endforeach
