{{-- Optional sample photo embedded when include_photo_in_report is set --}}
@php
    $photoContext = $sampleDetailContexts[$sampleIndex] ?? [];
    $samplePhotoDataUri = trim((string) ($photoContext['sample_photo_data_uri'] ?? ''));
@endphp
@if($samplePhotoDataUri !== '')
<div class="trr-sample-photo" style="margin: 8px 0 12px; page-break-inside: avoid;">
    <div style="font-size: 11px; font-weight: 700; margin-bottom: 4px;">
        {{ $labels['sample_photo'] ?? 'Sample photo' }}
    </div>
    <img
        src="{{ $samplePhotoDataUri }}"
        alt="Sample photo"
        style="max-width: 220px; max-height: 160px; width: auto; height: auto; object-fit: contain; border: 1px solid #ccc;"
    >
</div>
@endif
