{{-- Clinical Form Display - Imara LIMS Design System --}}
<div class="clinical-form-display" data-form-instance-id="{{ $instance->id }}" style="background: transparent;">

    {{-- Process each section --}}
    @foreach($formData['sections'] as $section)
        {{-- Clinical Section Card --}}
        <div class="clinical-section-card">
            {{-- Section Title --}}
            <div class="clinical-section-header">
                <h3 class="clinical-section-title">
                    <i class="mdi mdi-folder-outline" style="color: #0059bb; margin-right: 0.75rem;"></i>
                    {{ $section['title'] }}
                </h3>
                @if($section['description'])
                    <p class="clinical-section-description">{{ $section['description'] }}</p>
                @endif
            </div>

            {{-- Section Content --}}
            <div class="clinical-section-content">
                @foreach($section['element_holders'] as $holder)
                    @if($holder['holder_type'] === 'rows')
                        {{-- Rows Section Display (Table) --}}
                        <div class="clinical-rows-holder">
                            @if(!empty($holder['rows_data']))
                                <div class="clinical-table-wrapper">
                                    <table class="clinical-data-table">
                                        <thead>
                                            <tr class="clinical-table-header">
                                                <th class="clinical-row-index">#</th>
                                                @foreach($holder['elements'] as $element)
                                                    <th class="clinical-table-th">
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
                                                <tr class="clinical-table-row">
                                                    <td class="clinical-row-index">{{ $arrayIndex + 1 }}</td>
                                                    @foreach($holder['elements'] as $element)
                                                        <td class="clinical-table-td">
                                                            @php
                                                                $elementId = $element['id'];
                                                                $savedValue = $rowData[$elementId] ?? null;
                                                                $displayValue = $savedValue['display_value'] ?? $savedValue['value'] ?? 'N/A';
                                                                $isSignature = $element['element_type'] === 'signature' || str_contains((string)$displayValue, '/storage/personnel-signature/');
                                                                $isMediaField = in_array($element['element_type'], ['file', 'camera_photo'], true);
                                                                $mediaPath = '';
                                                                $mediaUrl = null;
                                                                $isImageMedia = false;

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

                                                                        $pathForExt = parse_url($candidate, PHP_URL_PATH) ?: $candidate;
                                                                        $isImageMedia = (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)$/i', $pathForExt)
                                                                            || $element['element_type'] === 'camera_photo';
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($isSignature && $displayValue && $displayValue !== 'N/A')
                                                                <img src="{{ $displayValue }}" alt="Signature" class="clinical-signature">
                                                            @elseif($isMediaField && $mediaUrl)
                                                                @if($isImageMedia)
                                                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                                                        <img src="{{ $mediaUrl }}" alt="Uploaded image" class="clinical-upload-preview">
                                                                    </a>
                                                                @else
                                                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="clinical-file-link">
                                                                        <i class="mdi mdi-file-document-outline"></i>
                                                                        {{ basename($mediaPath) ?: 'View file' }}
                                                                    </a>
                                                                @endif
                                                            @else
                                                                <span class="clinical-field-value">{{ $displayValue }}</span>
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
                                    <i class="mdi mdi-file-document-outline"></i>
                                    <p>No data available</p>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Regular Section Display (Field Grid) --}}
                        <div class="clinical-fields-holder">
                            @php
                                $fieldCount = count($holder['elements']);
                                $gridCols = $fieldCount === 1 ? 'clinical-grid-1' : ($fieldCount === 2 ? 'clinical-grid-2' : 'clinical-grid-3');
                            @endphp
                            <div class="clinical-fields-grid {{ $gridCols }}">
                                @foreach($holder['elements'] as $element)
                                    <div class="clinical-field">
                                        <label class="clinical-field-label">
                                            {{ $element['label'] }}
                                            @if($element['is_required'])
                                                <span class="clinical-required">*</span>
                                            @endif
                                        </label>
                                        @php
                                            $savedValue = $element['saved_values'][0] ?? null;
                                            $displayValue = $savedValue['display_value'] ?? $savedValue['value'] ?? 'N/A';
                                            $isSignature = $element['element_type'] === 'signature' || str_contains((string)$displayValue, '/storage/personnel-signature/');
                                            $isMediaField = in_array($element['element_type'], ['file', 'camera_photo'], true);
                                            $mediaPath = '';
                                            $mediaUrl = null;
                                            $isImageMedia = false;

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

                                                    $pathForExt = parse_url($candidate, PHP_URL_PATH) ?: $candidate;
                                                    $isImageMedia = (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)$/i', $pathForExt)
                                                        || $element['element_type'] === 'camera_photo';
                                                }
                                            }
                                        @endphp
                                        <div class="clinical-field-value-box">
                                            @if($isSignature && $displayValue && $displayValue !== 'N/A')
                                                <img src="{{ $displayValue }}" alt="Signature" class="clinical-signature">
                                            @elseif($isMediaField && $mediaUrl)
                                                @if($isImageMedia)
                                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                                        <img src="{{ $mediaUrl }}" alt="Uploaded image" class="clinical-upload-preview">
                                                    </a>
                                                @else
                                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="clinical-file-link">
                                                        <i class="mdi mdi-file-document-outline"></i>
                                                        {{ basename($mediaPath) ?: 'View file' }}
                                                    </a>
                                                @endif
                                            @else
                                                <span class="clinical-field-value">{{ $displayValue }}</span>
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

<style>
/* Clinical Form Display Styling - Imara LIMS Design */

.clinical-form-display {
    width: 100%;
}

/* Section Card */
.clinical-section-card {
    background: #ffffff;
    border-radius: 4px;
    margin-bottom: 2rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease;
}

.clinical-section-card:hover {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

/* Section Header */
.clinical-section-header {
    background: #f3f4f5;
    padding: 1.5rem;
    border-bottom: 1px solid #e1e3e4;
}

.clinical-section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #191c1d;
    margin: 0;
    display: flex;
    align-items: center;
}

.clinical-section-description {
    font-size: 0.875rem;
    color: #414754;
    margin: 0.5rem 0 0 2rem;
}

/* Section Content */
.clinical-section-content {
    padding: 1.5rem;
}

/* Fields Holder */
.clinical-fields-holder {
    width: 100%;
}

.clinical-fields-grid {
    display: grid;
    gap: 1.5rem;
    width: 100%;
}

.clinical-grid-1 {
    grid-template-columns: 1fr;
}

.clinical-grid-2 {
    grid-template-columns: repeat(2, 1fr);
}

.clinical-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 1200px) {
    .clinical-grid-3 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .clinical-grid-2,
    .clinical-grid-3 {
        grid-template-columns: 1fr;
    }
}

/* Field */
.clinical-field {
    display: flex;
    flex-direction: column;
}

.clinical-field-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    color: #414754;
    margin-bottom: 0.5rem;
}

.clinical-required {
    color: #ba1a1a;
    margin-left: 0.25rem;
}

/* Field Value Box */
.clinical-field-value-box {
    background: #ffffff;
    border: 1px solid #e1e3e4;
    border-radius: 4px;
    padding: 1rem;
    min-height: 2.5rem;
    display: flex;
    align-items: center;
    transition: border-color 0.2s ease;
}

.clinical-field-value-box:hover {
    border-color: #c1c6d7;
}

.clinical-field-value {
    color: #191c1d;
    font-weight: 500;
    font-size: 0.9375rem;
    word-break: break-word;
}

/* Table Styles */
.clinical-rows-holder {
    margin-top: 1rem;
}

.clinical-table-wrapper {
    overflow-x: auto;
    border: 1px solid #e1e3e4;
    border-radius: 4px;
    background: #ffffff;
}

.clinical-data-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.clinical-table-header {
    background: #f3f4f5;
    border-bottom: 2px solid #e1e3e4;
}

.clinical-table-th {
    padding: 1rem;
    text-align: left;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #191c1d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    border-right: 1px solid #e1e3e4;
}

.clinical-table-th:last-child {
    border-right: none;
}

.clinical-row-index {
    width: 60px;
    text-align: center;
    background: #edeeef;
    font-weight: 600;
}

.clinical-table-row {
    border-bottom: 1px solid #e1e3e4;
    transition: background-color 0.15s ease;
}

.clinical-table-row:hover {
    background-color: #f8f9fa;
}

.clinical-table-row:last-child {
    border-bottom: none;
}

.clinical-table-td {
    padding: 1rem;
    font-size: 0.9375rem;
    color: #191c1d;
    border-right: 1px solid #e1e3e4;
    vertical-align: middle;
}

.clinical-table-td:last-child {
    border-right: none;
}

/* Signature Image */
.clinical-signature {
    max-width: 150px;
    max-height: 80px;
    border: 1px solid #e1e3e4;
    border-radius: 4px;
    display: block;
}

.clinical-upload-preview {
    max-width: 220px;
    max-height: 180px;
    border: 1px solid #e1e3e4;
    border-radius: 4px;
    display: block;
}

.clinical-file-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #0059bb;
    font-weight: 500;
}

/* Empty State */
.clinical-empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6c757d;
}

.clinical-empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.6;
}

.clinical-empty-state p {
    margin: 0;
    font-size: 0.9375rem;
}
</style>
