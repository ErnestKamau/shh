@if(empty($isEmbedded))
<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@if(!empty($isPreviewMode))Preview — @endif Test Request Report &mdash; {{ $reportNumber }}</title>
@endif
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    /* ═══════════════════════════════════════════
       AmSpec Test Request Report — matches PDF
       ═══════════════════════════════════════════ */
    @if(empty($isEmbedded))
    body { background: #e9ecef; }
    @else
    .trr-embedded-root { background: transparent; }
    .trr-embedded-root .trr-page { margin-bottom: 0; }
    @endif

    @if(!empty($hideScreenToolbar))
    /* Compact preview-doc: less chrome around the A4 sheet */
    body {
        background: #eceff3 !important;
        margin: 0;
        padding: 12px 0 20px;
    }
    .trr-page {
        margin: 0 auto !important;
        padding: 18px 22px 16px !important;
        max-width: 760px !important;
        border-color: #cfd6de !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .pg-header {
        margin-bottom: 10px !important;
        padding-bottom: 8px !important;
    }
    .report-title-bar {
        margin: 8px 0 10px !important;
        padding: 6px 0 !important;
    }
    @endif

    /* Screen toolbar (hidden on print) */
    .trr-toolbar {
        max-width: 800px;
        margin: 18px auto 8px;
        text-align: right;
        padding: 0 4px;
    }

    .trr-preview-chrome {
        max-width: 800px;
        margin: 20px auto 10px;
        background: #0f172a;
        color: #f8fafc;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
    }
    .trr-preview-chrome .trr-preview-copy {
        min-width: 0;
    }
    .trr-preview-chrome .trr-preview-badge {
        display: inline-block;
        font-size: 10px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        background: #f59e0b;
        color: #111827;
        border-radius: 999px;
        padding: 3px 10px;
        margin-bottom: 6px;
    }
    .trr-preview-chrome h1 {
        font-size: 16px;
        font-weight: 650;
        margin: 0 0 4px;
        color: #fff;
        font-family: Georgia, 'Times New Roman', serif;
    }
    .trr-preview-chrome p {
        margin: 0;
        font-size: 12px;
        color: #cbd5e1;
        line-height: 1.45;
    }
    .trr-preview-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .trr-preview-actions a,
    .trr-preview-actions button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 12px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        font-family: inherit;
    }
    .trr-preview-actions .btn-print {
        background: #fff;
        color: #0f172a;
    }
    .trr-preview-actions .btn-back {
        background: transparent;
        color: #e2e8f0;
        border-color: #475569;
    }
    .trr-preview-watermark {
        pointer-events: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-28deg);
        font-size: 72px;
        font-weight: 800;
        letter-spacing: 0.2em;
        color: rgba(148, 163, 184, 0.12);
        z-index: 0;
        text-transform: uppercase;
        white-space: nowrap;
        font-family: Georgia, 'Times New Roman', serif;
    }
    body.trr-preview-body .trr-page {
        position: relative;
        z-index: 1;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        border-color: #dbe3ee;
    }

    /* White A4-like page */
    .trr-page {
        max-width: 800px;
        margin: 0 auto 40px;
        background: #fff;
        border: 1px solid #ccc;
        padding: 28px 32px 24px;
        /* Dompdf ships with DejaVu fonts; use them for Arabic glyph coverage. */
        font-family: {{ $isRTL ? "'DejaVu Sans', 'DejaVu Serif', Arial" : 'Arial, Helvetica, sans-serif' }};
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
        width: 98%;
        border-collapse: collapse;
        margin-bottom: 10px;
        margin-left: auto;
        margin-right: auto;
        font-size: 11px;
        border: 1px solid #aaa;
    }
    .info-table td {
        padding: 4px 8px;
        vertical-align: top;
        text-align: center;
    }
    .info-table .lbl {
        font-weight: bold;
        white-space: nowrap;
        width: 140px;
    }
    .info-table .colon { width: 10px; }

    /* ── DETAILS GRID (2-column, bordered) ──────── */
    .detail-grid {
        width: 98%;
        border-collapse: collapse;
        margin-bottom: 10px;
        margin-left: auto;
        margin-right: auto;
        font-size: 11px;
    }
    .detail-grid td {
        border: 1px solid #888;
        padding: 3px 6px;
        vertical-align: top;
        text-align: center;
    }
    .detail-grid .dlbl {
        font-weight: bold;
        white-space: nowrap;
        background: #f7f7f7;
        width: 120px;
    }

    /* ── RESULTS TABLE ──────────────────────────── */
    .results-table {
        width: 98%;
        border-collapse: collapse;
        font-size: 11px;
        margin-bottom: 8px;
        margin-left: auto;
        margin-right: auto;
    }
    .results-table th {
        background: #8B1A1A;
        color: #fff;
        padding: 4px 6px;
        font-weight: bold;
        border: 1px solid #6B1212;
        text-align: center;
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
        page-break-inside: avoid;
    }
    .page-disclaimer .disclaimer-text {
        flex: 1;
        font-size: 7px;
        color: #000;
        font-weight: bold;
        line-height: 1.65;
        font-family: inherit;
    }
    .page-disclaimer .disclaimer-qr {
        flex-shrink: 0;
        margin-left: 10px;
    }
    .page-disclaimer .disclaimer-qr img {
        width: 70px;
        height: 70px;
        display: block;
    }

    @if(!empty($isPdfMode))
    @page {
        margin: 12mm 10mm 44mm 10mm;
    }
    html, body {
        width: 190mm;
    }
    body {
        background: #fff;
    }
    .trr-page {
        max-width: 190mm;
        width: 190mm;
        margin: 0;
        border: none;
        padding: 4mm 8mm 44mm 4mm;
        overflow: hidden;
    }
    .pdf-fixed-footer {
        position: fixed;
        left: 10mm;
        width: 190mm;
        right: auto;
        bottom: 2mm;
        height: 38mm;
        padding: 1.5mm 8mm 0 4mm;
        border-top: 1px solid #cfcfcf;
        background: #fff;
        z-index: 20;
    }
    .pdf-fixed-footer .meta-top {
        font-size: 8px;
        line-height: 1.25;
        color: #222;
        margin-bottom: 1mm;
    }
    .pdf-fixed-footer .meta-end {
        text-align: center;
        font-size: 8px;
        font-style: italic;
        color: #444;
        margin: .8mm 0;
    }
    .pdf-fixed-footer .meta-issued {
        text-align: center;
        font-size: 7.8px;
        color: #333;
        margin-bottom: .8mm;
    }
    .pdf-fixed-footer .meta-company {
        text-align: center;
        font-size: 8.6px;
        font-weight: bold;
        letter-spacing: .3px;
        margin-bottom: .8mm;
        text-transform: uppercase;
    }
    .pdf-fixed-footer .meta-disclaimer {
        text-align: center;
        font-size: 7.2px;
        font-style: italic;
        color: #555;
        text-transform: uppercase;
        letter-spacing: .2px;
        margin-bottom: .8mm;
    }
    .pdf-fixed-footer .meta-legal-wrap {
        display: flex;
        gap: 8px;
        align-items: flex-start;
    }
    .pdf-fixed-footer .meta-legal {
        flex: 1;
        font-size: 5.9px;
        line-height: 1.35;
        color: #000;
        font-weight: bold;
        word-break: break-word;
    }
    .pdf-fixed-footer .meta-qr img {
        width: 50px;
        height: 50px;
        display: block;
    }
    @endif

    /* ── PRINT ──────────────────────────────────── */
    @media print {
        body { background: #fff; }
        .trr-toolbar,
        .trr-preview-chrome,
        .trr-preview-watermark { display: none !important; }
        .trr-page {
            border: none;
            margin: 0;
            max-width: 100%;
            padding: 12px 18px;
            box-shadow: none;
        }
    }
</style>
@if(empty($isEmbedded))
</head>
<body @class(['trr-preview-body' => !empty($isPreviewMode)])>
@else
<div class="trr-embedded-root" dir="{{ !empty($isRTL) ? 'rtl' : 'ltr' }}">
@endif
@if(!empty($isPdfMode))
    <div class="pdf-fixed-footer">
        <div class="meta-top">
            {{ $labels['results_relate'] }}<br>
            {{ $labels['no_reproduce'] }}
        </div>
        <div class="meta-end">{{ $labels['end_of_text'] }}</div>
        <div class="meta-issued">{{ $labels['issued_on'] }} {{ $approvalDate }}.</div>
        <div class="meta-company">{{ strtoupper($company->name ?? 'AMSPEC FIRST CLASS SUPERINTENDENT COMPANY') }}</div>
        <div class="meta-disclaimer">{{ $labels['disclaimer'] }}</div>
        <div class="meta-legal-wrap">
            <div class="meta-legal">
                This document is issued by the Company subject to the Terms and Conditions at
                https://www.amspecgroup.com/terms-conditions. Any holder of this document is advised that
                information contained herein reflects the Company&#8217;s findings at the time and place of its
                intervention only and within the scope of the Client&#8217;s instructions. The Company&#8217;s sole
                responsibility is to its Client and the Company disclaims any liability to third parties.
                Any alteration, forgery or falsification of the content or appearance of this document is unlawful.
            </div>
            <div class="meta-qr">
                @if(!empty($footerQrCode))
                    <img src="{{ $footerQrCode }}" alt="Report QR Code">
                @endif
            </div>
        </div>
    </div>
@endif

@if(!empty($isPreviewMode) && empty($isEmbedded))
    <div class="trr-preview-watermark" aria-hidden="true">Draft Preview</div>
@endif

<main>

    {{-- ── Screen toolbar / preview chrome ── --}}
    @if(empty($isPdfMode) && empty($isEmbedded) && empty($hideScreenToolbar))
        @if(!empty($isPreviewMode))
            <div class="trr-preview-chrome">
                <div class="trr-preview-copy">
                    <span class="trr-preview-badge">Draft preview</span>
                    <h1>Test Request Report</h1>
                    <p>
                        This is how the final report will look with the current results and comments.
                        It does not issue a revision and is not saved as an official PDF.
                        @if(!empty($reportNumber))
                            Provisional number: <strong style="color:#fff;">{{ $reportNumber }}</strong>
                        @endif
                    </p>
                </div>
                <div class="trr-preview-actions">
                    <button type="button" class="btn-print" onclick="window.print()">Print</button>
                    <a href="{{ $batchBackUrl ?? ('/sample-workflow/batch/'.$batch->id.'/details') }}" class="btn-back">← Back to Batch</a>
                </div>
            </div>
        @else
            <div class="trr-toolbar">
                <button onclick="window.print()" style="background:#8B1A1A;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:13px;margin-right:6px;">&#128438; Print</button>
                <a href="{{ $batchBackUrl ?? ('/sample-workflow/batch/'.$batch->id.'/details') }}" style="background:#fff;color:#555;border:1px solid #aaa;padding:6px 14px;border-radius:4px;text-decoration:none;font-size:13px;">&#8592; Back to Batch</a>
            </div>
        @endif
    @endif

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
            {{-- Top-right logo slot from System Settings → Companies → Report Logos --}}
            <div class="pg-header-right">
                @if(!empty($reportLogos['top_right']))
                    <img src="{{ $reportLogos['top_right']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}" style="max-height:80px;max-width:200px;object-fit:contain;">
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
                <td>{{ $dateReceived ?? ($batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : '-') }}</td>
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
                <td class="dlbl">{{ $labels['date_of_analysis'] }} Start</td>
                <td>{{ $analysisStartDate ?? '-' }}</td>
                <td class="dlbl">{{ $labels['date_of_analysis'] }} End</td>
                <td>{{ $analysisEndDate ?? '-' }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['packaging'] ?? 'Packaging' }}</td>
                <td>{{ $trfCollectionExtras['packaging'] ?? '-' }}</td>
                <td class="dlbl">{{ $labels['sample_information'] ?? 'Sample information' }}</td>
                <td>{{ $trfCollectionExtras['sample_information'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['sample_weight'] ?? 'Sample weight' }}</td>
                <td>{{ $trfCollectionExtras['sample_weight'] ?? '-' }}</td>
                <td class="dlbl">{{ $labels['ship_name'] ?? 'Ship / vessel' }}</td>
                <td>{{ $trfCollectionExtras['ship_name'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['port_of_loading'] ?? 'Port of loading' }}</td>
                <td>{{ $trfCollectionExtras['port_of_loading'] ?? '-' }}</td>
                <td class="dlbl">{{ $labels['port_of_discharge'] ?? 'Port of discharge' }}</td>
                <td>{{ $trfCollectionExtras['port_of_discharge'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="dlbl">{{ $labels['seal_number'] ?? 'Seal number' }}</td>
                <td colspan="3">{{ $trfCollectionExtras['seal_number'] ?? '-' }}</td>
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
                                    {!! isset($cr->isitalic) && $cr->isitalic == 1 ? '<em>' . e($cr->analyte_code) . '</em>' : e($cr->analyte_code) !!}@if((int) ($cr->analyte_status_contracted ?? 0) === 1)<sup style="color:#c00;font-weight:bold;">¹</sup>@endif@if((int) ($cr->analyte_accredited ?? 1) === 0)<span style="color:#c00;font-weight:bold;">*</span>@endif
                                </td>
                                <td class="{{ isset($cr->remark) && strtoupper($cr->remark) == 'FAIL' ? 'fail' : '' }}">
                                    {{ ($cr->result_reporting_symbol ?? '') . ($cr->result !== null && $cr->result !== '' ? $cr->result : '-') }}
                                </td>
                                <td>{{ resolveReportingUnitLabel($cr->reporting_unit_id ?? null) }}</td>
                                <td>
                                    {{ app(\App\Services\StandardLimitDisplayService::class)->forCapturedResult($cr, $sample->main_standard ?? null) ?? '-' }}
                                </td>
                                <td>{{ $measureUncertaintyByCapturedResultId[$cr->id] ?? '-' }}</td>
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
                <strong>Remarks:</strong> {!! $sample->header_body !!}
            </div>
            @endif
            @if($sample->main_body)
            <div style="font-size:10px;padding:4px 0;">
                <strong>Recommendations / Interpretations:</strong> {!! $sample->main_body !!}
            </div>
            @endif
            @if($sample->notes_body)
            <div style="font-size:10px;padding:4px 0;">
                <strong>Notes:</strong> {!! $sample->notes_body !!}
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

        @if(empty($isPdfMode))
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
                @if(!empty($footerQrCode))
                    <img src="{{ $footerQrCode }}" alt="Report QR Code">
                @else
                    <div style="width:70px;height:70px;border:1px solid #bbb;font-size:8px;display:flex;align-items:center;justify-content:center;text-align:center;color:#888;">
                        QR unavailable
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>{{-- end .trr-page --}}

</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.results-table td').forEach(function (td) {
            var t = td.textContent.trim().toUpperCase();
            if (t === 'FAIL') td.classList.add('fail');
            if (t === 'PASS') td.classList.add('pass');
        });
    });
</script>
@if(empty($isEmbedded))
</body>
</html>
@else
</div>{{-- .trr-embedded-root --}}
@endif
