{{-- Global report background watermark (company settings). --}}
{{-- PDF output paints the watermark on every page via ReportWatermarkService::applyToPdf, --}}
{{-- so this partial only renders for on-screen HTML previews. --}}
@php
    $reportWatermarkForPdf = (bool) ($forPdf ?? $isPdf ?? false);
    $reportWatermarkSrc = $reportWatermarkForPdf
        ? ''
        : ($reportWatermarkSrc
            ?? ($watermarkSrc ?? null)
            ?? app(\App\Services\Reports\ReportWatermarkService::class)->src($company ?? null));
@endphp
@if(!empty($reportWatermarkSrc))
    <img src="{{ $reportWatermarkSrc }}" alt="" class="system-report-watermark" aria-hidden="true">
    <style>
        .system-report-watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            opacity: {{ \App\Services\Reports\ReportWatermarkService::OPACITY }};
            z-index: 0;
            pointer-events: none;
        }
    </style>
@endif
