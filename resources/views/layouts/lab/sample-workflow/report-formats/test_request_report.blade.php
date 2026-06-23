<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test Request Report &mdash; {{ $reportNumber }}</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    /* ═══════════════════════════════════════════
       AmSpec Test Request Report — matches PDF
       ═══════════════════════════════════════════ */
    body { background: #e9ecef; }

    /* Screen toolbar (hidden on print) */
    .trr-toolbar {
        max-width: 800px;
        margin: 18px auto 8px;
        text-align: right;
        padding: 0 4px;
    }

    /* White A4-like page */
    .trr-page {
        max-width: 800px;
        margin: 0 auto 40px;
        background: #fff;
        border: 1px solid #ccc;
        padding: 28px 32px 24px;
        font-family: {{ $isRTL ? "'Noto Naskh Arabic', 'Arabic Typesetting', Arial" : 'Arial, Helvetica, sans-serif' }};
        font-size: 11px;
        color: #111;
        line-height: 1.4;
    }

    /* ── PAGE HEADER ──────────────────────────────── */
    .pg-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 12px;
    }
    .pg-header-logo img {
        max-height: 80px;
        max-width: 200px;
        object-fit: contain;
    }
    .pg-header-logo .logo-text {
        font-size: 28px;
        font-weight: 900;
        color: #8B1A1A;
        letter-spacing: 1px;
    }
    .pg-header-center {
        flex: 1;
        text-align: center;
        padding: 0 20px;
    }
    .pg-header-center .cert-no {
        font-size: 11px;
        margin-bottom: 2px;
    }
    .pg-header-center .page-of {
        font-size: 10px;
        color: #444;
    }
    .pg-header-right {
        text-align: right;
    }
    .pg-header-right img {
        max-height: 64px;
        max-width: 90px;
        object-fit: contain;
    }

    /* Report title bar */
    .report-title-bar {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 6px 0;
        margin: 0 0 12px;
    }

    /* ── INFO TABLE (Attention / Client / Address) ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        font-size: 11px;
        border: 1px solid #aaa;
    }
    .info-table td {
        padding: 4px 8px;
        vertical-align: top;
    }
    .info-table .lbl {
        font-weight: bold;
        white-space: nowrap;
        width: 140px;
    }
    .info-table .colon { width: 10px; }

    /* ── DETAILS GRID (2-column, bordered) ──────── */
    .detail-grid {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        font-size: 11px;
    }
    .detail-grid td {
        border: 1px solid #888;
        padding: 3px 6px;
        vertical-align: top;
    }
    .detail-grid .dlbl {
        font-weight: bold;
        white-space: nowrap;
        background: #f7f7f7;
        width: 120px;
    }

    /* ── RESULTS TABLE ──────────────────────────── */
    .results-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-bottom: 8px;
    }
    .results-table th {
        background: #8B1A1A;
        color: #fff;
        padding: 4px 6px;
        font-weight: bold;
        border: 1px solid #6B1212;
        text-align: left;
    }
    .results-table td {
        border: 1px solid #ccc;
        padding: 3px 6px;
        vertical-align: middle;
    }
    .results-table tr:nth-child(even) td { background: #fafafa; }
    .results-table .analysis-group td {
        background: #eeeeee;
        font-weight: bold;
        font-size: 10px;
        text-transform: uppercase;
        color: #111;
    }
    .results-table .sample-subheader td {
        background: #dce6f1;
        font-weight: bold;
    }
    .fail { color: #8B1A1A; font-weight: bold; }
    .pass { color: #1e8449; }

    /* ── SIGNATURE BLOCK ────────────────────────── */
    .sig-section {
        margin-top: 18px;
        border-top: 1px solid #888;
        padding-top: 10px;
    }
    .sig-section .sig-intro {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 14px;
    }
    .sig-block {
        display: flex;
        align-items: flex-start;
        gap: 30px;
        margin-bottom: 10px;
    }
    .sig-block .sig-left { flex: 1; }
    .sig-block .sig-center {
        flex: 1;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
    }
    .sig-block .sig-center img {
        max-height: 70px;
        max-width: 200px;
        display: block;
        margin-bottom: 4px;
    }
    .sig-block .sig-right { text-align: right; flex-shrink: 0; }
    .sig-block .sig-right img {
        max-height: 90px;
        max-width: 200px;
        object-fit: contain;
    }
    .sig-name { font-weight: bold; font-size: 11px; margin-top: 4px; }
    .sig-title-line { font-size: 11px; }
    .sig-company-line { font-size: 11px; }
    .sig-img-wrap {
        min-height: 52px;
        display: flex;
        align-items: flex-end;
        margin: 6px 0;
    }
    .sig-img-wrap img {
        max-height: 52px;
        max-width: 180px;
    }
    .sig-underline {
        border-top: 1px solid #333;
        margin: 4px 0 3px;
        width: 220px;
    }

    /* ── FOOTER TEXT ────────────────────────────── */
    .report-footer-text {
        font-size: 10px;
        margin-top: 16px;
        line-height: 1.6;
    }
    .report-issued {
        font-size: 10px;
        margin-top: 6px;
        color: #333;
    }
    .report-company-tag {
        font-weight: bold;
        font-size: 11px;
        text-align: center;
        margin-top: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .disclaimer-footer {
        font-size: 9px;
        color: #555;
        text-align: center;
        margin-top: 6px;
        font-style: italic;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .end-text {
        text-align: center;
        font-size: 10px;
        margin: 12px 0 6px;
        font-style: italic;
        color: #444;
    }

    /* ── PAGE DISCLAIMER FOOTER (every page) ─────── */
    .page-disclaimer {
        margin-top: 18px;
        border-top: 1px solid #ccc;
        padding-top: 6px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .page-disclaimer .disclaimer-text {
        flex: 1;
        font-size: 7px;
        color: #000;
        font-weight: bold;
        line-height: 1.65;
        font-family: Arial, Helvetica, sans-serif;
    }
    .page-disclaimer .disclaimer-qr {
        flex-shrink: 0;
        margin-left: 10px;
    }
    .page-disclaimer .disclaimer-qr canvas {
        width: 64px !important;
        height: 64px !important;
        display: block;
    }

    /* ── PRINT ──────────────────────────────────── */
    @media print {
        body { background: #fff; }
        .trr-toolbar { display: none; }
        .trr-page {
            border: none;
            margin: 0;
            max-width: 100%;
            padding: 12px 18px;
        }
    }
</style>
</head>
<body>
<main>

    {{-- ── Screen toolbar ── --}}
    <div class="trr-toolbar">
        <button onclick="window.print()" style="background:#8B1A1A;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:13px;margin-right:6px;">&#128438; Print</button>
        <a href="/sample-workflow/batch/{{ $batch->id }}/details" style="background:#fff;color:#555;border:1px solid #aaa;padding:6px 14px;border-radius:4px;text-decoration:none;font-size:13px;">&#8592; Back to Batch</a>
    </div>

    <div class="trr-page">

        {{-- ══════════════ PAGE HEADER (logo + cert no) ══════════════ --}}
        <div class="pg-header">
            {{-- Top-left logo slot --}}
            <div class="pg-header-logo">
                @if(!empty($reportLogos['top_left']))
                    <img src="{{ $reportLogos['top_left']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}">
                @elseif($reportLogo && empty($reportLogos))
                    <img src="{{ $reportLogo }}" alt="{{ $company->name ?? 'AmSpec' }}">
                @else
                    <span class="logo-text">{{ $company->name ?? 'AmSpec' }}</span>
                @endif
            </div>
            <div class="pg-header-center">
                <div class="cert-no">{{ $labels['certificate_no'] }}: {{ $reportNumber }}</div>
                <div class="page-of">{{ sprintf($labels['page_of'], 1, $totalPages) }}</div>
            </div>
            {{-- Top-right logo slot (logo if assigned, else decorative hexagon) --}}
            <div class="pg-header-right">
                @if(!empty($reportLogos['top_right']))
                    <img src="{{ $reportLogos['top_right']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}" style="max-height:80px;max-width:200px;object-fit:contain;">
                @else
                    <svg width="72" height="80" viewBox="0 0 72 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="36,2 43,6 43,14 36,18 29,14 29,6" fill="#8B1A1A"/>
                        <polygon points="36,22 66,39 66,63 36,78 6,63 6,39" fill="none" stroke="#8B1A1A" stroke-width="2"/>
                        <polygon points="36,28 60,43 60,59 36,72 12,59 12,43" fill="none" stroke="#8B1A1A" stroke-width="1.2" stroke-dasharray="4,3"/>
                        <polygon points="36,34 54,45 54,57 36,66 18,57 18,45" fill="none" stroke="#8B1A1A" stroke-width="1" stroke-dasharray="3,3"/>
                    </svg>
                @endif
            </div>
        </div>

        {{-- ══════════════ REPORT TITLE ══════════════ --}}
        <div class="report-title-bar">{{ $labels['report_title'] }}</div>

        {{-- ══════════════ ATTENTION / CLIENT / ADDRESS ══════════════ --}}
        <table class="info-table">
            <tr>
                <td class="lbl">{{ $labels['attention'] }}</td>
                <td class="colon">:</td>
                <td>{{ $attention ?? $batch->getContactPersonDetail() }}</td>
            </tr>
            <tr>
                <td class="lbl">{{ $labels['client'] }}</td>
                <td class="colon">:</td>
                <td>{{ $customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">{{ $labels['address'] }}</td>
                <td class="colon">:</td>
                <td>{{ $customer->physical_address ?? ($customer->postal_address ?? '-') }}</td>
            </tr>
        </table>

        {{-- ══════════════ DETAIL GRID ══════════════ --}}
        <table class="detail-grid">
            <tr>
                <td class="dlbl">{{ $labels['report_no'] }}</td>
                <td style="font-weight:bold;color:#8B1A1A;">{{ $reportNumber }}</td>
                <td class="dlbl">{{ $labels['sample_no'] }}</td>
                <td>{{ $batch->batch_code }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['date_received'] }}</td>
                <td>{{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : '-' }}</td>
                <td class="dlbl">{{ $labels['date_reported'] }}</td>
                <td>{{ date('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['container_type'] }}</td>
                <td>{{ $containerType }}</td>
                <td class="dlbl">{{ $labels['sample_description'] }}</td>
                <td>{{ $sampleDescription ?? strip_tags($batch->description ?? '-') }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['weight'] }}</td>
                <td>{{ $sampleWeight }}</td>
                <td class="dlbl">{{ $labels['sampled_by'] }}</td>
                <td>{{ $batch->sampling_officer_name ?? ($batch->receivingofficer?->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['sample_temperature'] }}</td>
                <td>{{ $sampleTemperature }}</td>
                <td class="dlbl">{{ $labels['sample_preservation'] }}</td>
                <td>{{ $samplePreservation }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['production_date'] }}</td>
                <td>{{ $mfgDate }}</td>
                <td class="dlbl">{{ $labels['expiry_date'] }}</td>
                <td>{{ $expiryDate }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['lot_no'] }}</td>
                <td>{{ $batchLotNo }}</td>
                <td class="dlbl">{{ $labels['no_of_pages'] }}</td>
                <td>{{ str_pad($totalPages, 2, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['date_of_analysis'] }}</td>
                <td colspan="3">
                    @if($analysisDate && $analysisDate->start_analysis_date)
                        {{ date('d/m/Y', strtotime($analysisDate->start_analysis_date)) }}
                        @if($analysisDate->analysis_dates)
                            &ndash; {{ date('d/m/Y', strtotime($analysisDate->analysis_dates)) }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        {{-- ══════════════ TEST RESULTS ══════════════ --}}
        @forelse ($samples as $sample)

            {{-- Per-sample subheader --}}
            <table class="detail-grid" style="margin-bottom:4px;">
                <tr>
                    <td class="dlbl">{{ $labels['sample_reference'] }}</td>
                    <td><strong>{{ $sample->sample_code }}</strong></td>
                    <td class="dlbl">{{ $labels['sample_point'] }}</td>
                    <td>{{ $samplePointByIndex[$loop->index] ?? ($sample->sample_point_name ?? '-') }}</td>
                </tr>
                @if($sample->sample_condition_name)
                <tr>
                    <td class="dlbl">{{ $labels['condition'] }}</td>
                    <td colspan="3">{{ $sample->sample_condition_name }}</td>
                </tr>
                @endif
            </table>

            <table class="results-table">
                <thead>
                    <tr>
                        <th style="width:30%">{{ $labels['analyte'] }}</th>
                        <th style="width:13%">{{ $labels['results'] }}</th>
                        <th style="width:8%">{{ $labels['unit'] }}</th>
                        <th style="width:14%">{{ $labels['specification'] }}</th>
                        <th style="width:9%">{{ $labels['mu_percent'] }}</th>
                        <th style="width:26%">{{ $labels['method'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasRows = false; @endphp
                    @foreach ($sample->getSampleByAnalysisType() as $atLevel)
                        @php $captured_results = $atLevel->getCapturedResults(); @endphp
                        @if($captured_results->count() > 0)
                        <tr class="analysis-group">
                            <td colspan="6">{{ $atLevel->analysis_type_name ?? 'General' }}</td>
                        </tr>
                        @foreach ($captured_results as $cr)
                            @php $hasRows = true; @endphp
                            <tr>
                                <td>
                                    @if(isset($cr->analyte_accredited) && $cr->analyte_accredited == 1)
                                        <span title="Accredited">*</span>
                                    @endif
                                    {!! isset($cr->isitalic) && $cr->isitalic == 1 ? '<em>' . e($cr->analyte_code) . '</em>' : e($cr->analyte_code) !!}
                                </td>
                                <td class="{{ isset($cr->remark) && strtoupper($cr->remark) == 'FAIL' ? 'fail' : '' }}">
                                    {{ ($cr->result_reporting_symbol ?? '') . ($cr->result !== null && $cr->result !== '' ? $cr->result : '-') }}
                                </td>
                                <td>{{ $cr->reporting_unit_id ?? '-' }}</td>
                                <td>
                                    {{ getStandardLimitValue($cr->id, $sample->main_standard, 1) ?? '' }}
                                    {{ ($cr->main_value == 'NS') ? '--' : ($cr->main_value ?? '') }}
                                    {{ getStandardLimitValue($cr->id, $sample->main_standard) ?? '-' }}
                                </td>
                                <td>{{ $cr->measure_uncertanity ?? '-' }}</td>
                                <td>{{ strtoupper($cr->method()->name ?? ($cr->ltmethod->name ?? '-')) }}</td>
                            </tr>
                        @endforeach
                        @endif
                    @endforeach
                    @if(!$hasRows)
                    <tr>
                        <td colspan="6" style="text-align:center;color:#888;font-style:italic;padding:8px;">
                            {{ $labels['no_results'] }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            @if($sample->header_body)
            <div style="font-size:10px;padding:4px 0;border-top:1px solid #eee;margin-bottom:6px;">
                <em>*Accredited Analysis</em><br>
                <strong>Remarks:</strong> {!! $sample->header_body !!}
            </div>
            @endif
            @if($sample->main_body)
            <div style="font-size:10px;padding:4px 0;">
                {!! $sample->main_body !!}
            </div>
            @endif

        @empty
            <div style="padding:14px 0;font-style:italic;color:#888;">{{ $labels['no_samples'] }}</div>
        @endforelse

        {{-- ══════════════ ANALYSIS CONDUCTED / TEST METHOD ══════════════ --}}
        <table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:11px;">
            <tr>
                <td style="border:1px solid #aaa;padding:4px 8px;width:50%;">
                    {{ $labels['analysis_conducted'] }}: {{ $batch->getLabSectionsNames() ?: 'Laboratory' }}
                </td>
                <td style="border:1px solid #aaa;padding:4px 8px;width:50%;">
                    {{ $labels['test_method_dev'] }}
                </td>
            </tr>
        </table>

        {{-- ══════════════ SIGNATURE SECTION ══════════════ --}}
        <div class="sig-section">
            <div class="sig-intro">
                {{ $labels['signed_behalf'] }} {{ $company->name ?? 'AmSpec Inspection &amp; Testing Services' }}
            </div>

            <div class="sig-block">
                <div class="sig-left">
                    <div class="sig-name">{{ $approverUser->name ?? '&nbsp;' }}</div>
                    <div class="sig-title-line">
                        {{ $approverRole ?? '&nbsp;' }}
                        @if($company->location ?? null) | {{ $company->location }} @endif
                    </div>
                    <div class="sig-company-line">{{ $company->name ?? '&nbsp;' }}</div>
                    <div class="sig-underline" style="margin-top:30px;"></div>
                </div>
                <div class="sig-center">
                    @if(!empty($signatureSrc))
                        <img src="{{ $signatureSrc }}" alt="Signature">
                    @elseif($approverUser && $approverUser->electronic_sig)
                        @php
                            $sigSrc = $approverUser->electronic_sig;
                            if (!str_starts_with($sigSrc, 'http') && !str_starts_with($sigSrc, 'data:')) {
                                $sigSrc = rtrim(config('app.url'), '/') . '/' . ltrim($sigSrc, '/');
                            }
                        @endphp
                        <img src="{{ $sigSrc }}" alt="Signature">
                    @else
                        <span style="color:#aaa;font-size:9px;font-style:italic;">{{ $labels['no_signature'] }}</span>
                    @endif
                </div>
                <div class="sig-right">
                    @if($companyLogo)
                        <img src="{{ $companyLogo }}" alt="{{ $company->name ?? 'AmSpec' }}" style="max-height:90px;max-width:200px;object-fit:contain;">
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════ BOTTOM LOGOS ══════════════ --}}
        @if(!empty($reportLogos['bottom_left']) || !empty($reportLogos['bottom_right']))
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin:10px 0 4px;">
            <div>
                @if(!empty($reportLogos['bottom_left']))
                    <img src="{{ $reportLogos['bottom_left']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}" style="max-height:80px;max-width:200px;object-fit:contain;">
                @endif
            </div>
            <div>
                @if(!empty($reportLogos['bottom_right']))
                    <img src="{{ $reportLogos['bottom_right']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}" style="max-height:80px;max-width:200px;object-fit:contain;">
                @endif
            </div>
        </div>
        @endif

        {{-- ══════════════ FOOTER TEXT ══════════════ --}}
        <div class="report-footer-text">
            {{ $labels['results_relate'] }}<br>
            {{ $labels['no_reproduce'] }}
        </div>
        <div class="end-text">{{ $labels['end_of_text'] }}</div>
        <div class="report-issued">{{ $labels['issued_on'] }} {{ $approvalDate }}.</div>
        <div class="report-company-tag">
            {{ strtoupper($company->name ?? 'AMSPEC FIRST CLASS SUPERINTENDENT COMPANY') }}
        </div>
        <div class="disclaimer-footer">
            {{ $labels['disclaimer'] }}
        </div>

        {{-- ══════════════ PAGE DISCLAIMER FOOTER ══════════════ --}}
        <div class="page-disclaimer">
            <div class="disclaimer-text">
                This document is issued by the Company subject to the Terms and Conditions at
                https://www.amspecgroup.com/terms-conditions. Any holder of this document is advised that
                information contained herein reflects the Company&#8217;s findings at the time and place of its
                intervention only and within the scope of the Client&#8217;s instructions. The Company&#8217;s sole
                responsibility is to its Client and the Company disclaims any liability to third parties.
                Any alteration, forgery or falsification of the content or appearance of this document is unlawful.
            </div>
            <div class="disclaimer-qr">
                <canvas id="report-qr-code"></canvas>
            </div>
        </div>

    </div>{{-- end .trr-page --}}
</main>
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.results-table td').forEach(function (td) {
            var t = td.textContent.trim().toUpperCase();
            if (t === 'FAIL') td.classList.add('fail');
            if (t === 'PASS') td.classList.add('pass');
        });

        // Render QR code
        var qrCanvas = document.getElementById('report-qr-code');
        if (qrCanvas && typeof QRious !== 'undefined') {
            new QRious({
                element: qrCanvas,
                value: '{{ addslashes("Certificate: $reportNumber | " . ($company->name ?? "AmSpec") . " | " . date("d.m.Y")) }}',
                size: 128,
                background: '#ffffff',
                foreground: '#000000',
                level: 'M'
            });
        }
    });
</script>
</body>
</html>
