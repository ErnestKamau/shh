@php
    $instanceId = $instanceId ?? '';
    $wirePrefix = 'checkInTrfFields.'.$instanceId;
@endphp
<div
    class="check-in-trf-collection-info mt-3"
    x-data="{ openInfo: true }"
    wire:key="checkin-trf-collection-{{ $instanceId }}"
>
    <button
        type="button"
        class="btn btn-link btn-sm p-0 mb-2 font-weight-bold text-dark text-decoration-none d-flex align-items-center w-100 justify-content-between"
        @click="openInfo = !openInfo"
    >
        <span><i class="mdi mdi-map-marker-radius mr-1"></i> Sample collection info</span>
        <i class="mdi" :class="openInfo ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
    </button>

    <div x-show="openInfo" x-collapse>
        <div class="row small">
            @foreach (config('test_request_form_fields.receive_check_in_collection_fields', []) as $field)
                @php
                    $fieldName = (string) ($field['name'] ?? '');
                    $fieldType = (string) ($field['type'] ?? 'text');
                    $fieldId = 'checkin-collection-'.$instanceId.'-'.$fieldName;
                    $modelPrefix = $wirePrefix.'.'.$fieldName;
                @endphp
                <div class="col-md-6 form-group {{ in_array($fieldType, ['checkbox', 'radio'], true) ? 'col-12' : '' }}">
                    <label class="font-weight-bold d-block">{{ $field['label'] ?? $fieldName }}</label>

                    @if ($fieldType === 'date')
                        <input
                            type="date"
                            id="{{ $fieldId }}"
                            class="form-control form-control-sm"
                            wire:model="{{ $modelPrefix }}"
                        >
                    @elseif ($fieldType === 'radio')
                        <div class="d-flex flex-wrap" style="gap: 10px 18px;">
                            @foreach (($field['options'] ?? []) as $opt)
                                @php
                                    $optValue = (string) ($opt['value'] ?? '');
                                    $optLabel = (string) ($opt['label'] ?? $optValue);
                                @endphp
                                <label class="mb-0 d-flex align-items-center" style="gap: 6px;">
                                    <input
                                        type="radio"
                                        id="{{ $fieldId }}-{{ $optValue }}"
                                        wire:model="{{ $modelPrefix }}"
                                        value="{{ $optValue }}"
                                    >
                                    <span>{{ $optLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($fieldType === 'checkbox')
                        <div class="row pt-1">
                            @foreach (($field['options'] ?? []) as $opt)
                                @php
                                    $optValue = (string) ($opt['value'] ?? '');
                                    $optLabel = (string) ($opt['label'] ?? $optValue);
                                @endphp
                                <div class="col-md-6 col-lg-4 mb-1">
                                    <div class="custom-control custom-checkbox">
                                        <input
                                            type="checkbox"
                                            id="{{ $fieldId }}-{{ $optValue }}"
                                            wire:model="{{ $modelPrefix }}.{{ $optValue }}"
                                            class="custom-control-input"
                                        >
                                        <label class="custom-control-label small font-weight-normal" for="{{ $fieldId }}-{{ $optValue }}">
                                            {{ $optLabel }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <input
                            type="text"
                            id="{{ $fieldId }}"
                            class="form-control form-control-sm"
                            wire:model="{{ $modelPrefix }}"
                            placeholder="{{ $field['placeholder'] ?? ('Enter '.strtolower((string) ($field['label'] ?? $fieldName))) }}"
                        >
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
