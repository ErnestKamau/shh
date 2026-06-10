{{-- Clinical Form Display - Imara LIMS Design System --}}
<div class="clinical-form-display" data-form-instance-id="{{ $instance->id }}" style="background: transparent;">

    {{-- Process each section --}}
    @foreach($formData['sections'] as $section)
        {{-- Clinical Section Card --}}
        <div class="clinical-section-card">
            {{-- Section Title --}}
            <div class="clinical-section-header">
                <h3 class="clinical-section-title">
                    <i class="mdi mdi-folder-outline" style="color: #6D0A0E; margin-right: 0.75rem;"></i>
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
                                                                $isMediaField = in_array($element['element_type'], ['file', 'camera_photo', 'image_upload'], true);
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
                                                                            || in_array($element['element_type'], ['camera_photo', 'image_upload'], true);
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
                                            $isMediaField = in_array($element['element_type'], ['file', 'camera_photo', 'image_upload'], true);
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
                                                        || in_array($element['element_type'], ['camera_photo', 'image_upload'], true);
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
/* Clinical Form Display Styling - Modern sleek design */
.clinical-form-display {
    width: 100%;
    font-family: inherit;
}

/* Section Card */
.clinical-section-card {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.04);
    border-radius: 12px;
    margin-bottom: 2rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.clinical-section-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04), 0 2px 6px rgba(0, 0, 0, 0.03);
    transform: translateY(-1px);
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
    color: #64748b;
    margin-bottom: 0.4rem;
}

.clinical-required {
    color: #ef4444;
    margin-left: 0.25rem;
}

/* Field Value Box */
.clinical-field-value-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.875rem 1rem;
    min-height: 2.75rem;
    display: flex;
    align-items: center;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.clinical-field-value-box:hover {
    border-color: #cbd5e1;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.clinical-field-value {
    color: #0f172a;
    font-weight: 500;
    font-size: 0.95rem;
    word-break: break-word;
}

/* Table Styles */
.clinical-rows-holder {
    margin-top: 0.5rem;
}

.clinical-table-wrapper {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.01);
}

.clinical-data-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.clinical-table-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.clinical-table-th {
    padding: 1rem 1.25rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.clinical-row-index {
    width: 60px;
    text-align: center;
    background: #f8fafc;
    font-weight: 600;
    color: #94a3b8;
}

.clinical-table-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease;
}

.clinical-table-row:hover {
    background-color: #f8fafc;
}

.clinical-table-row:last-child {
    border-bottom: none;
}

.clinical-table-td {
    padding: 1rem 1.25rem;
    font-size: 0.95rem;
    color: #0f172a;
    font-weight: 500;
    vertical-align: middle;
}

/* Signature Image */
.clinical-signature {
    max-width: 120px;
    max-height: 60px;
    border-radius: 4px;
    display: block;
}

.clinical-upload-preview {
    max-width: 180px;
    max-height: 140px;
    border-radius: 6px;
    display: block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.clinical-file-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6D0A0E;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.15s ease;
}

.clinical-file-link:hover {
    color: #8c1419;
}

/* Empty State */
.clinical-empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.clinical-empty-state i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

.clinical-empty-state p {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 500;
}
</style>
