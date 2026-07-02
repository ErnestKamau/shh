{{-- Clinical Form Display - Request view captured details --}}
<div class="clinical-form-display" data-form-instance-id="{{ $instance->id }}">

    @foreach($formData['sections'] as $section)
        @php
            $sectionTitle = trim((string) ($section['title'] ?? ''));
        @endphp
        @if(strcasecmp($sectionTitle, 'TRF storage') === 0)
            @continue
        @endif
        @php
            $sectionCollapseId = 'clinical-section-collapse-'.$section['id'];
        @endphp
        <div class="clinical-section-card">
            <button
                type="button"
                class="clinical-section-header clinical-section-toggle"
                data-toggle="collapse"
                data-target="#{{ $sectionCollapseId }}"
                aria-expanded="true"
                aria-controls="{{ $sectionCollapseId }}"
            >
                <span class="clinical-section-header-text">
                    <span class="clinical-section-title">
                        <i class="mdi mdi-folder-outline clinical-section-icon" aria-hidden="true"></i>
                        {{ $section['title'] }}
                    </span>
                    @if($section['description'])
                        <span class="clinical-section-description">{{ $section['description'] }}</span>
                    @endif
                </span>
                <i class="mdi mdi-chevron-down clinical-section-chevron" aria-hidden="true"></i>
            </button>

            <div class="collapse show clinical-section-content" id="{{ $sectionCollapseId }}">
                @foreach($section['element_holders'] as $holder)
                    @if($holder['holder_type'] === 'rows')
                        <div class="clinical-rows-holder">
                            @if(!empty($holder['rows_data']))
                                <div class="clinical-table-wrapper">
                                    <table class="clinical-data-table">
                                        <thead>
                                            <tr class="clinical-table-header">
                                                <th class="clinical-row-index" scope="col">#</th>
                                                @foreach($holder['elements'] as $element)
                                                    <th class="clinical-table-th" scope="col">
                                                        {{ $element['label'] }}
                                                        @if($element['is_required'])
                                                            <span class="clinical-required">*</span>
                                                        @endif
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($holder['rows_data'] as $arrayIndex => $rowData)
                                                @php
                                                    $rowHasData = false;
                                                    foreach ($holder['elements'] as $rowElement) {
                                                        $rowCell = $rowData[$rowElement['id']] ?? null;
                                                        $rowDisplay = $rowCell['display_value'] ?? $rowCell['value'] ?? 'N/A';
                                                        if ($rowDisplay !== '' && $rowDisplay !== 'N/A' && $rowDisplay !== null) {
                                                            $rowHasData = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <tr @class(['clinical-table-row', 'clinical-table-row--populated' => $rowHasData])>
                                                    <td class="clinical-row-index">{{ $arrayIndex + 1 }}</td>
                                                    @foreach($holder['elements'] as $element)
                                                        <td class="clinical-table-td">
                                                            @php
                                                                $elementId = $element['id'];
                                                                $savedValue = $rowData[$elementId] ?? null;
                                                                $displayValue = $savedValue['display_value'] ?? $savedValue['value'] ?? 'N/A';
                                                                $isEmptyValue = $displayValue === '' || $displayValue === 'N/A' || $displayValue === null;
                                                                $isSignature = $element['element_type'] === 'signature' || str_contains((string) $displayValue, '/storage/personnel-signature/');
                                                                $isMediaField = in_array($element['element_type'], ['file', 'camera_photo', 'image_upload'], true);
                                                                $mediaPath = '';
                                                                $mediaUrl = null;

                                                                if ($isMediaField && $savedValue) {
                                                                    $candidate = trim((string) ($savedValue['value'] ?? ''));
                                                                    $decoded = json_decode($candidate, true);

                                                                    if (is_array($decoded) && !empty($decoded)) {
                                                                        $first = $decoded[0] ?? null;
                                                                        if (is_string($first)) {
                                                                            $candidate = trim($first);
                                                                        } elseif (is_array($first)) {
                                                                            foreach (['url', 'path', 'file_path', 'value'] as $key) {
                                                                                if (!empty($first[$key]) && is_string($first[$key])) {
                                                                                    $candidate = trim($first[$key]);
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    $mediaPath = $candidate;

                                                                    if ($candidate !== '' && $candidate !== 'N/A') {
                                                                        $mediaUrl = $instance->resolveUploadedMediaUrl($candidate);
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($isSignature && ! $isEmptyValue)
                                                                <img src="{{ $displayValue }}" alt="Signature" class="clinical-signature">
                                                            @elseif($isMediaField && $mediaUrl)
                                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="clinical-file-link">
                                                                    <i class="mdi mdi-paperclip" aria-hidden="true"></i>
                                                                    {{ basename($mediaPath) ?: 'View file' }}
                                                                </a>
                                                            @else
                                                                <span @class(['clinical-field-value', 'clinical-field-value--empty' => $isEmptyValue])>{{ $displayValue }}</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="clinical-empty-state">
                                    <i class="mdi mdi-file-document-outline" aria-hidden="true"></i>
                                    <p>No data available</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="clinical-fields-holder">
                            @php
                                $fieldCount = count($holder['elements']);
                                $gridCols = $fieldCount === 1 ? 'clinical-grid-1' : ($fieldCount === 2 ? 'clinical-grid-2' : 'clinical-grid-3');
                            @endphp
                            <div class="clinical-fields-grid {{ $gridCols }}">
                                @foreach($holder['elements'] as $element)
                                    @php
                                        $savedValue = $element['saved_values'][0] ?? null;
                                        $displayValue = $savedValue['display_value'] ?? $savedValue['value'] ?? 'N/A';
                                        $isEmptyValue = $displayValue === '' || $displayValue === 'N/A' || $displayValue === null;
                                        $isSignature = $element['element_type'] === 'signature' || str_contains((string) $displayValue, '/storage/personnel-signature/');
                                        $isTextarea = $element['element_type'] === 'textarea';
                                        $isMediaField = in_array($element['element_type'], ['file', 'camera_photo', 'image_upload'], true);
                                        $mediaPath = '';
                                        $mediaUrl = null;

                                        if ($isMediaField && $savedValue) {
                                            $candidate = trim((string) ($savedValue['value'] ?? ''));
                                            $decoded = json_decode($candidate, true);

                                            if (is_array($decoded) && !empty($decoded)) {
                                                $first = $decoded[0] ?? null;
                                                if (is_string($first)) {
                                                    $candidate = trim($first);
                                                } elseif (is_array($first)) {
                                                    foreach (['url', 'path', 'file_path', 'value'] as $key) {
                                                        if (!empty($first[$key]) && is_string($first[$key])) {
                                                            $candidate = trim($first[$key]);
                                                            break;
                                                        }
                                                    }
                                                }
                                            }

                                            $mediaPath = $candidate;

                                            if ($candidate !== '' && $candidate !== 'N/A') {
                                                $mediaUrl = $instance->resolveUploadedMediaUrl($candidate);
                                            }
                                        }

                                        $valueBoxClass = 'clinical-field-value-box';
                                        if ($isSignature) {
                                            $valueBoxClass .= ' clinical-field-value-box--signature';
                                        } elseif ($isTextarea) {
                                            $valueBoxClass .= ' clinical-field-value-box--textarea';
                                        }
                                    @endphp
                                    <div class="clinical-field">
                                        <span class="clinical-field-label" id="clinical-field-{{ $element['id'] }}">
                                            {{ $element['label'] }}
                                            @if($element['is_required'])
                                                <span class="clinical-required">*</span>
                                            @endif
                                        </span>
                                        <div
                                            class="{{ $valueBoxClass }}"
                                            role="textbox"
                                            aria-readonly="true"
                                            aria-labelledby="clinical-field-{{ $element['id'] }}"
                                        >
                                            @if($isSignature && ! $isEmptyValue)
                                                <img src="{{ $displayValue }}" alt="Signature" class="clinical-signature">
                                            @elseif($isMediaField && $mediaUrl)
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="clinical-file-link">
                                                    <i class="mdi mdi-paperclip" aria-hidden="true"></i>
                                                    {{ basename($mediaPath) ?: 'View file' }}
                                                </a>
                                            @else
                                                <span @class(['clinical-field-value', 'clinical-field-value--empty' => $isEmptyValue])>{{ $displayValue }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
