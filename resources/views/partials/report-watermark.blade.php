{{-- Global report background watermark (company settings). --}}
@php
    $reportWatermarkSrc = $reportWatermarkSrc
        ?? ($watermarkSrc ?? null)
        ?? app(\App\Services\Reports\ReportWatermarkService::class)->src(
            $company ?? null,
            (bool) ($forPdf ?? $isPdf ?? false)
        );
@endphp
@if(!empty($reportWatermarkSrc))
    <img src="{{ $reportWatermarkSrc }}" alt="" class="system-report-watermark" aria-hidden="true">
    <style>
        .system-report-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            opacity: 0.08;
            width: 48%;
            max-width: 420px;
            z-index: 0;
            pointer-events: none;
        }
    </style>
@endif
