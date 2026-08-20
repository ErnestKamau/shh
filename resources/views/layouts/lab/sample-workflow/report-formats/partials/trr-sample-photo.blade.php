{{-- Sample photo on a dedicated report page when include_photo_in_report is set --}}
@php
    $photoContext = $sampleDetailContexts[$sampleIndex] ?? [];
    $samplePhotoDataUri = trim((string) ($photoContext['sample_photo_data_uri'] ?? ''));
    $photoSampleCode = trim((string) ($sampleCode ?? ($sample->sample_code ?? '')));
@endphp
@if($samplePhotoDataUri !== '')
<div class="trr-sample-photo-page">
    <table class="trr-sample-photo-heading">
        <thead>
            <tr>
                <th>
                    {{ $labels['sample_photo'] ?? 'Sample Photo' }}
                    @if($photoSampleCode !== '')
                        — {{ $photoSampleCode }}
                    @endif
                </th>
            </tr>
        </thead>
    </table>
    <div class="trr-sample-photo-frame">
        <img
            src="{{ $samplePhotoDataUri }}"
            alt="{{ $labels['sample_photo'] ?? 'Sample Photo' }}"
        >
    </div>
</div>
@endif
