@if(empty($isEmbedded))
<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@if(!empty($isPreviewMode))Preview — @endif Test Report &mdash; {{ $reportNumber }}</title>
@endif
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    /* ═══════════════════════════════════════════
       AmSpec Test Report — matches PDF
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
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
        overflow: hidden;
        user-select: none;
    }
    .trr-preview-watermark span {
        transform: rotate(-28deg);
        font-size: clamp(48px, 9vw, 84px);
        font-weight: 800;
        letter-spacing: 0.18em;
        color: rgba(100, 116, 139, 0.32);
        text-transform: uppercase;
        white-space: nowrap;
        font-family: Georgia, 'Times New Roman', serif;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
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
        border: 1px solid #d0d0d0;
        padding: 26px 30px 22px;
        /* Dompdf ships with DejaVu fonts; use them for Arabic glyph coverage. */
        font-family: {{ $isRTL ? "'DejaVu Sans', 'DejaVu Serif', Arial" : 'Arial, Helvetica, sans-serif' }};
        font-size: 10.5px;
        color: #1a1a1a;
        line-height: 1.45;
    }

    /* ── PAGE HEADER ──────────────────────────────── */
    .pg-header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        padding-bottom: 0;
        border-bottom: 1px solid #e5e5e5;
    }
    .pg-header td {
        border: none;
        padding: 0 0 6px;
        vertical-align: top;
    }
    .pg-header-logo {
        width: 34%;
        text-align: left;
    }
    .pg-header-logo img {
        max-height: 70px;
        max-width: 200px;
        object-fit: contain;
        display: block;
    }
    .pg-header-logo .logo-text {
        font-size: 26px;
        font-weight: 900;
        color: #8B1A1A;
        letter-spacing: 1px;
    }
    .pg-header-center {
        width: 32%;
        text-align: center;
        vertical-align: middle;
        padding: 0 8px 6px;
    }
    .pg-header-center .cert-no {
        font-size: 10.5px;
        color: #333;
        letter-spacing: 0.02em;
    }
    .pg-header-center .page-of {
        font-size: 10px;
        color: #666;
    }
    .pg-header-right {
        width: 34%;
        text-align: right;
    }
    .pg-header-right img {
        max-height: 70px;
        max-width: 160px;
        object-fit: contain;
        display: inline-block;
    }

    /* Report title bar */
    .report-title-bar {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.6px;
        padding: 8px 0 10px;
        margin: 2px 0 12px;
        color: #111;
        border-bottom: 2px solid #8B1A1A;
    }

    /* ── INFO TABLE (Attention / Client / Address) ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        font-size: 10.5px;
        border: 1px solid #bdbdbd;
    }
    .info-table td {
        padding: 5px 8px;
        vertical-align: middle;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }
    .info-table tr:last-child td { border-bottom: none; }
    .info-table .lbl {
        font-weight: bold;
        white-space: nowrap;
        width: 150px;
        color: #333;
        background: #fafafa;
    }
    .info-table .colon { width: 12px; color: #888; background: #fafafa; }

    /* ── DETAILS GRID (2-column, bordered) ──────── */
    .detail-grid {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        font-size: 10.5px;
    }
    .detail-grid td {
        border: 1px solid #c4c4c4;
        padding: 4px 7px;
        vertical-align: middle;
        text-align: left;
    }
    .detail-grid .dlbl {
        font-weight: bold;
        white-space: nowrap;
        background: #f6f6f6;
        width: 118px;
        color: #333;
    }
    .detail-grid .val-emphasize {
        font-weight: bold;
        color: #8B1A1A;
    }
    .sample-ref-grid {
        margin-bottom: 4px;
    }

    /* ── RESULTS TABLE ──────────────────────────── */
    .results-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        margin-bottom: 10px;
    }
    .results-table th {
        background: #8B1A1A;
        color: #fff;
        padding: 6px 5px;
        font-weight: bold;
        border: 1px solid #6e1515;
        text-align: center;
        letter-spacing: 0.02em;
        font-size: 9.5px;
    }
    .results-table td {
        border: 1px solid #d5d5d5;
        padding: 4px 5px;
        vertical-align: middle;
    }
    .results-table tr:nth-child(even) td { background: #fbfbfb; }
    .results-table .analysis-group td {
        background: #efefef;
        font-weight: bold;
        font-size: 9.5px;
        text-transform: uppercase;
        color: #222;
        letter-spacing: 0.04em;
        padding: 5px 6px;
    }
    .results-table .sample-subheader td {
        background: #e8eef5;
        font-weight: bold;
    }
    .fail { color: #8B1A1A; font-weight: bold; }
    .pass { color: #1e7a45; }

    /* ── REMARKS / NOTES ────────────────────────── */
    .sample-remarks,
    .sample-interpretations,
    .sample-notes,
    .sample-amendment {
        font-size: 10px;
        padding: 6px 2px;
        line-height: 1.55;
        color: #222;
    }
    .sample-remarks {
        border-top: 1px solid #e6e6e6;
        margin-top: 2px;
        margin-bottom: 4px;
    }
    .sample-amendment {
        border: 1px solid #d4b896;
        background: #fffaf0;
        padding: 8px 10px;
        margin: 8px 0 6px;
    }
    .sample-remarks strong,
    .sample-interpretations strong,
    .sample-notes strong,
    .sample-amendment strong {
        color: #111;
    }
    .sample-notes ol {
        margin: 4px 0 0 18px;
        padding: 0;
    }
    .sample-notes li {
        margin-bottom: 2px;
        color: #444;
    }

    /* ── META BOX (analysis conducted) ──────────── */
    .meta-box {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        font-size: 10.5px;
    }
    .meta-box td {
        border: 1px solid #c4c4c4;
        padding: 6px 10px;
        width: 50%;
        vertical-align: middle;
        background: #fcfcfc;
    }

    /* ── SIGNATURE BLOCK ────────────────────────── */
    .sig-section {
        margin-top: 16px;
        border-top: 1px solid #c4c4c4;
        padding-top: 10px;
        page-break-inside: avoid;
    }
    .sig-section .sig-intro {
        font-weight: bold;
        font-size: 10.5px;
        margin-bottom: 10px;
        color: #111;
    }
    .sig-block {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }
    .sig-block td {
        vertical-align: bottom;
        border: none;
        padding: 0;
    }
    .sig-block .sig-left {
        width: 62%;
        padding-right: 14px;
        vertical-align: top;
    }
    .sig-block .sig-right {
        width: 38%;
        text-align: right;
        vertical-align: middle;
    }
    .sig-block .sig-right img {
        max-height: 72px;
        max-width: 160px;
        object-fit: contain;
    }
    .sig-name { font-weight: bold; font-size: 11px; margin-top: 0; color: #111; }
    .sig-title-line { font-size: 10.5px; color: #333; }
    .sig-company-line { font-size: 10.5px; margin-bottom: 2px; color: #333; }
    .sig-image {
        margin: 2px 0 1px;
        min-height: 36px;
    }
    .sig-image img {
        max-height: 48px;
        max-width: 180px;
        display: block;
    }
    .sig-underline {
        border-top: 1px solid #555;
        margin: 1px 0 0;
        width: 200px;
    }

    /* ── FOOTER TEXT ────────────────────────────── */
    .report-footer-text {
        font-size: 10px;
        margin-top: 14px;
        line-height: 1.55;
        color: #333;
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
        letter-spacing: 0.6px;
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
        margin: 14px 0 8px;
        font-style: italic;
        color: #555;
        letter-spacing: 0.03em;
    }

    /* ── PAGE DISCLAIMER FOOTER (every page) ─────── */
    .page-disclaimer {
        margin-top: 16px;
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
        color: #222;
        font-weight: bold;
        line-height: 1.6;
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
    /*
     * Multi-page frame:
     * - thead repeats at the top of every page
     * - tfoot (after tbody) repeats at the bottom of every page
     * - modest @page margins only (large bottom margins steal body space
     *   and push all results onto later pages)
     * Avoid position:fixed for the tall Notes→signature block: DomPDF 3
     * either clips it in the margin box or parks it inside the content box.
     */
    @page {
        size: A4 portrait;
        margin: 8mm 14mm 10mm 14mm;
    }
    html {
        margin: 0;
        padding: 0;
    }
    body {
        margin: 0;
        padding: 0;
        background: #fff;
    }
    main {
        margin: 0;
        padding: 0;
        width: auto;
    }
    .trr-page {
        width: auto;
        max-width: none;
        margin: 0;
        border: none;
        padding: 0;
        overflow: visible;
    }
    .pdf-page-frame {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .pdf-page-frame > thead {
        display: table-header-group;
    }
    .pdf-page-frame > tfoot {
        display: table-footer-group;
    }
    .pdf-page-frame > tbody {
        display: table-row-group;
    }
    .pdf-page-frame > thead > tr > td,
    .pdf-page-frame > tfoot > tr > td,
    .pdf-page-frame > tbody > tr > td {
        border: none;
        padding: 0;
        vertical-align: top;
    }
    /* Allow the body cell to split across pages so thead/tfoot can repeat. */
    .pdf-page-frame > tbody > tr,
    .pdf-page-frame > tbody > tr > td,
    .pdf-frame-body {
        page-break-inside: auto;
    }
    .pdf-frame-header {
        padding-bottom: 6px;
    }
    .pdf-frame-header .pg-header {
        margin-bottom: 2px;
        border-bottom: 1px solid #e5e5e5;
    }
    .pdf-frame-header .pg-header td {
        padding-bottom: 4px;
    }
    .pdf-frame-header .pg-header-logo img,
    .pdf-frame-header .pg-header-right img {
        max-height: 48px;
        max-width: 150px;
    }
    .pdf-frame-header .report-title-bar {
        margin: 2px 0 4px;
        padding: 3px 0 5px;
        font-size: 11px;
        letter-spacing: 1.2px;
    }
    .pdf-frame-header .info-table {
        margin-bottom: 0;
        font-size: 9.5px;
    }
    .pdf-frame-header .info-table td {
        padding: 3px 6px;
    }
    .pdf-frame-header .info-table .lbl {
        width: 130px;
    }
    .pdf-frame-footer {
        padding-top: 4px;
    }
    .pdf-frame-footer .pdf-footer-closing {
        border-top: 1px solid #cfcfcf;
        padding-top: 3px;
        margin-bottom: 3px;
    }
    .pdf-frame-footer .sample-notes,
    .pdf-frame-footer .sample-amendment {
        font-size: 8px;
        padding: 2px 1px;
        line-height: 1.35;
        margin: 0;
    }
    .pdf-frame-footer .sample-amendment {
        border: 1px solid #d4b896;
        background: #fffaf0;
        padding: 4px 6px;
        margin: 3px 0;
    }
    .pdf-frame-footer .meta-box {
        margin-top: 3px;
        font-size: 8.5px;
    }
    .pdf-frame-footer .meta-box td {
        padding: 3px 6px;
    }
    .pdf-frame-footer .sig-section {
        margin-top: 4px;
        padding-top: 4px;
        border-top: 1px solid #c4c4c4;
    }
    .pdf-frame-footer .sig-section .sig-intro {
        font-size: 8.5px;
        margin-bottom: 3px;
    }
    .pdf-frame-footer .sig-name {
        font-size: 9px;
    }
    .pdf-frame-footer .sig-title-line,
    .pdf-frame-footer .sig-company-line {
        font-size: 8.5px;
    }
    .pdf-frame-footer .sig-image {
        min-height: 22px;
        margin: 1px 0;
    }
    .pdf-frame-footer .sig-image img {
        max-height: 30px;
        max-width: 120px;
    }
    .pdf-frame-footer .sig-block .sig-right img {
        max-height: 40px;
        max-width: 100px;
    }
    .pdf-frame-footer .sig-underline {
        width: 140px;
    }
    .pdf-frame-footer .bottom-logos {
        margin: 2px 0 1px;
    }
    .pdf-frame-footer .bottom-logos img {
        max-height: 32px !important;
        max-width: 100px !important;
    }
    .pdf-frame-footer .end-text {
        display: block;
        text-align: center;
        font-size: 8px;
        font-style: italic;
        color: #555;
        margin: 2px 0 3px;
    }
    .pdf-frame-footer .pdf-footer-meta {
        border-top: 1px solid #cfcfcf;
        padding-top: 2px;
    }
    .pdf-frame-footer .meta-top {
        font-size: 7px;
        line-height: 1.25;
        color: #333;
        margin-bottom: 2px;
    }
    .pdf-frame-footer .meta-issued {
        text-align: center;
        font-size: 7px;
        color: #333;
        margin-bottom: 1px;
    }
    .pdf-frame-footer .meta-company {
        text-align: center;
        font-size: 8px;
        font-weight: bold;
        letter-spacing: .4px;
        margin-bottom: 2px;
        text-transform: uppercase;
        color: #111;
    }
    .pdf-frame-footer .meta-legal-wrap {
        width: 100%;
        border-collapse: collapse;
    }
    .pdf-frame-footer .meta-legal-wrap td {
        border: none;
        vertical-align: top;
        padding: 0;
    }
    .pdf-frame-footer .meta-legal {
        font-size: 5.5px;
        line-height: 1.3;
        color: #222;
        font-weight: bold;
        padding-right: 8px;
    }
    .pdf-frame-footer .meta-qr {
        width: 48px;
        text-align: right;
    }
    .pdf-frame-footer .meta-qr img {
        width: 42px;
        height: 42px;
        display: block;
    }
    .info-table,
    .detail-grid,
    .results-table,
    .meta-box,
    .sig-block,
    .pg-header {
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
    .report-title-bar {
        margin: 2px 0 8px;
        padding: 6px 0 8px;
    }
    .results-table {
        page-break-inside: auto;
    }
    .results-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    .detail-grid {
        page-break-inside: avoid;
    }
    @endif

    /* ── PRINT ──────────────────────────────────── */
    @media print {
        body { background: #fff; }
        .trr-toolbar,
        .trr-preview-chrome { display: none !important; }
        .trr-page {
            border: none;
            margin: 0;
            max-width: 100%;
            padding: 12px 18px;
            box-shadow: none;
        }
        /* Keep the draft watermark on printed / Save-as-PDF preview copies */
        .trr-preview-watermark {
            display: flex !important;
            position: fixed;
            inset: 0;
            z-index: 9999;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .trr-preview-watermark span {
            color: rgba(100, 116, 139, 0.38) !important;
            font-size: 64pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@if(empty($isEmbedded))
</head>
<body @class(['trr-preview-body' => !empty($isPreviewMode)])>
@else
<div class="trr-embedded-root" dir="{{ !empty($isRTL) ? 'rtl' : 'ltr' }}">
@endif

@if(!empty($isPreviewMode) && empty($isEmbedded) && empty($isPdfMode))
    <div class="trr-preview-watermark" aria-hidden="true"><span>Draft Preview</span></div>
@endif

@php
    $footerNotesBodies = collect($samples ?? [])
        ->map(static fn ($sample) => $sample->notes_body ?? null)
        ->filter(static fn ($body) => filled(trim(strip_tags((string) $body))))
        ->values()
        ->all();

    $amendmentRevision = (int) ($ammendment?->version_number ?? ($batch->is_amendment ?? 0));
    $display = $amendmentDisplay ?? [];
    $amendmentRevisionLabel = $display['formattedRevision']
        ?? $display['formatted_revision']
        ?? ($amendmentRevision > 0
            ? 'R' . str_pad((string) $amendmentRevision, 2, '0', STR_PAD_LEFT)
            : null);
    $supersedesText = $display['supersedesText']
        ?? $display['supersedes_text']
        ?? ($labels['supersedes_original'] ?? 'This report supersedes the original report');
    $revisionLabel = $display['revisionLabel']
        ?? $display['revision_label']
        ?? ($labels['amendment_revision'] ?? 'Revision No.');
    $reasonLabel = $display['reasonLabel']
        ?? $display['reason_label']
        ?? ($labels['amendment_reason'] ?? 'Amendment Reason');
@endphp

<main>



    {{-- ── Screen toolbar / preview chrome ── --}}
    @if(empty($isPdfMode) && empty($isEmbedded) && empty($hideScreenToolbar))
        @if(!empty($isPreviewMode))
            <div class="trr-preview-chrome">
                <div class="trr-preview-copy">
                    <span class="trr-preview-badge">Draft preview</span>
                    <h1>Test Report</h1>
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

        @if(!empty($isPdfMode))
        <table class="pdf-page-frame">
            <thead>
                <tr>
                    <td class="pdf-frame-header">
                        @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-header')
                    </td>
                </tr>
            </thead>
            <tbody>
        @else
        {{-- ══════════════ PAGE HEADER (logo + client info) ══════════════ --}}
        @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-header')
        @endif

        @if(!empty($isPdfMode))
                <tr>
                    <td class="pdf-frame-body">
        @endif
        {{-- ══════════════ DETAIL GRID (omit unfilled TRF fields) ══════════════ --}}
        @php
            $isDetailFieldFilled = static function (mixed $value): bool {
                $normalized = trim((string) ($value ?? ''));

                if ($normalized === '' || $normalized === '-' || $normalized === '—') {
                    return false;
                }

                return ! in_array(strtolower($normalized), ['n/a', 'na', 'ns', 'null'], true);
            };

            $detailGridFields = [];
            $pushDetailField = static function (string $label, mixed $value, bool $emphasize = false) use (&$detailGridFields, $isDetailFieldFilled): void {
                if (! $isDetailFieldFilled($value)) {
                    return;
                }

                $detailGridFields[] = [
                    'label' => $label,
                    'value' => $value,
                    'emphasize' => $emphasize,
                ];
            };

            $pushDetailField($labels['report_no'], $reportNumber, true);
            $pushDetailField($labels['sample_no'], $batch->batch_code);
            $pushDetailField(
                $labels['date_received'],
                $dateReceived ?? ($batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : null)
            );
            $pushDetailField($labels['date_reported'], date('d/m/Y'));
            $pushDetailField($labels['container_type'], $containerType ?? null);
            $pushDetailField(
                $labels['sample_description'],
                $sampleDescription ?? strip_tags($batch->description ?? '')
            );
            $pushDetailField($labels['weight'], $sampleWeight ?? null);
            $pushDetailField(
                $labels['sampled_by'],
                $batch->sampling_officer_name ?? ($batch->receivingofficer?->name ?? null)
            );
            $pushDetailField($labels['sample_temperature'], $sampleTemperature ?? null);
            $pushDetailField($labels['sample_preservation'], $samplePreservation ?? null);
            $pushDetailField($labels['production_date'], $mfgDate ?? null);
            $pushDetailField($labels['expiry_date'], $expiryDate ?? null);
            $pushDetailField($labels['lot_no'], $batchLotNo ?? null);
            $pushDetailField($labels['no_of_pages'], str_pad((string) $totalPages, 2, '0', STR_PAD_LEFT));
            $pushDetailField($labels['date_of_analysis'] . ' Start', $analysisStartDate ?? null);
            $pushDetailField($labels['date_of_analysis'] . ' End', $analysisEndDate ?? null);
            $pushDetailField($labels['packaging'] ?? 'Packaging', $trfCollectionExtras['packaging'] ?? null);
            $pushDetailField($labels['sample_information'] ?? 'Sample information', $trfCollectionExtras['sample_information'] ?? null);
            $pushDetailField($labels['sample_weight'] ?? 'Sample weight', $trfCollectionExtras['sample_weight'] ?? null);
            $pushDetailField($labels['ship_name'] ?? 'Ship / vessel', $trfCollectionExtras['ship_name'] ?? null);
            $pushDetailField($labels['port_of_loading'] ?? 'Port of loading', $trfCollectionExtras['port_of_loading'] ?? null);
            $pushDetailField($labels['port_of_discharge'] ?? 'Port of discharge', $trfCollectionExtras['port_of_discharge'] ?? null);
            $pushDetailField($labels['seal_number'] ?? 'Seal number', $trfCollectionExtras['seal_number'] ?? null);

            $detailGridRows = array_chunk($detailGridFields, 2);
        @endphp
        <table class="detail-grid">
            @foreach($detailGridRows as $pair)
            <tr>
                <td class="dlbl">{{ $pair[0]['label'] }}</td>
                @if(isset($pair[1]))
                <td @class(['val-emphasize' => !empty($pair[0]['emphasize'])])>{{ $pair[0]['value'] }}</td>
                <td class="dlbl">{{ $pair[1]['label'] }}</td>
                <td @class(['val-emphasize' => !empty($pair[1]['emphasize'])])>{{ $pair[1]['value'] }}</td>
                @else
                <td colspan="3" @class(['val-emphasize' => !empty($pair[0]['emphasize'])])>{{ $pair[0]['value'] }}</td>
                @endif
            </tr>
            @endforeach
        </table>
        @if(!empty($isPdfMode))
                    </td>
                </tr>
        @endif

        {{-- ══════════════ TEST RESULTS ══════════════ --}}
        @forelse ($samples as $sample)
        @if(!empty($isPdfMode))
                <tr>
                    <td class="pdf-frame-body">
        @endif

            {{-- Per-sample subheader --}}
            <table class="detail-grid sample-ref-grid">
                <tr>
                    <td class="dlbl">{{ $labels['sample_reference'] }}</td>
                    <td><strong>{{ format_sample_code($sample->sample_code) }}</strong></td>
                    @php
                        $samplePointValue = $samplePointByIndex[$loop->index] ?? ($sample->sample_point_name ?? null);
                        $samplePointFilled = filled(trim((string) ($samplePointValue ?? '')))
                            && ! in_array(trim((string) $samplePointValue), ['-', '—'], true);
                    @endphp
                    @if($samplePointFilled)
                    <td class="dlbl">{{ $labels['sample_point'] }}</td>
                    <td>{{ $samplePointValue }}</td>
                    @else
                    <td colspan="2"></td>
                    @endif
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
                        @php
                            $includeReferenceMethod = !empty($includeReferenceMethod);
                            $resultsColspan = $includeReferenceMethod ? 9 : 8;
                        @endphp
                        <th style="width:{{ $includeReferenceMethod ? '16%' : '18%' }}">{{ $labels['analyte'] }}</th>
                        <th style="width:12%">{{ $labels['lab_section'] ?? 'Lab Section' }}</th>
                        <th style="width:9%">{{ $labels['results'] }}</th>
                        <th style="width:6%">{{ $labels['unit'] }}</th>
                        <th style="width:10%">{{ $labels['specification'] }}</th>
                        <th style="width:10%">{{ $labels['standard_name'] ?? 'Standard Name' }}</th>
                        <th style="width:6%">{{ $labels['mu_percent'] }}</th>
                        <th style="width:{{ $includeReferenceMethod ? '14%' : '19%' }}">{{ $labels['method'] }}</th>
                        @if($includeReferenceMethod)
                        <th style="width:15%">{{ $labels['reference_method'] ?? 'Reference Method' }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $hasRows = false;
                        $standardLimitDisplay = app(\App\Services\StandardLimitDisplayService::class);
                    @endphp
                    @foreach ($sample->getSampleByAnalysisType() as $atLevel)
                        @php $captured_results = $atLevel->getCapturedResults(); @endphp
                        @if($captured_results->count() > 0)
                        <tr class="analysis-group">
                            <td colspan="{{ $resultsColspan }}">{{ $atLevel->analysis_type_name ?? 'General' }}</td>
                        </tr>
                        @foreach ($captured_results as $cr)
                            @php
                                $hasRows = true;
                                $analysisMethod = $cr->method() ?: $cr->ltmethod;
                                $referenceMethodName = $analysisMethod?->referencemethod?->name ?? null;
                            @endphp
                            <tr>
                                <td>
                                    {!! isset($cr->isitalic) && $cr->isitalic == 1 ? '<em>' . e($cr->analyte_code) . '</em>' : e($cr->analyte_code) !!}@if((int) ($cr->analyte_status_contracted ?? 0) === 1)<sup style="color:#c00;font-weight:bold;">¹</sup>@endif@if((int) ($cr->analyte_accredited ?? 1) === 0)<span style="color:#c00;font-weight:bold;">*</span>@endif
                                </td>
                                <td>{{ $cr->labSection->name ?? '-' }}</td>
                                <td class="{{ isset($cr->remark) && strtoupper($cr->remark) == 'FAIL' ? 'fail' : '' }}">
                                    {{ ($cr->result_reporting_symbol ?? '') . ($cr->result !== null && $cr->result !== '' ? $cr->result : '-') }}
                                </td>
                                <td>{{ resolveReportingUnitLabel($cr->reporting_unit_id ?? null) }}</td>
                                <td>
                                    {{ $standardLimitDisplay->forCapturedResult($cr, $sample->main_standard ?? null) ?? '-' }}
                                </td>
                                <td>
                                    {{ $standardLimitDisplay->standardNameForCapturedResult($cr, $sample->main_standard ?? null) ?? '-' }}
                                </td>
                                <td>{{ $measureUncertaintyByCapturedResultId[$cr->id] ?? '-' }}</td>
                                <td>{{ strtoupper($cr->method()->name ?? ($cr->ltmethod->name ?? '-')) }}</td>
                                @if($includeReferenceMethod)
                                <td>{{ $referenceMethodName ? strtoupper($referenceMethodName) : '-' }}</td>
                                @endif
                            </tr>
                        @endforeach
                        @endif
                    @endforeach
                    @if(!$hasRows)
                    <tr>
                        <td colspan="{{ $resultsColspan }}" style="text-align:center;color:#888;font-style:italic;padding:8px;">
                            {{ $labels['no_results'] }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            @if($sample->header_body)
            <div class="sample-remarks">
                <strong>Remarks:</strong> {!! str_ireplace(['not conforming', 'non-conforming'], 'Non-Conforming', $sample->header_body) !!}
            </div>
            @endif
            @if($sample->main_body)
            <div class="sample-interpretations">
                <strong>Recommendations / Interpretations:</strong> {!! $sample->main_body !!}
            </div>
            @endif
            @if(empty($isPdfMode) && $sample->notes_body)
            <div class="sample-notes">
                <strong>Notes:</strong> {!! $sample->notes_body !!}
            </div>
            @endif

        @if(!empty($isPdfMode))
                    </td>
                </tr>
        @endif
        @empty
        @if(!empty($isPdfMode))
                <tr>
                    <td class="pdf-frame-body">
        @endif
            <div style="padding:14px 0;font-style:italic;color:#888;">{{ $labels['no_samples'] }}</div>
        @if(!empty($isPdfMode))
                    </td>
                </tr>
        @endif
        @endforelse

        @if(empty($isPdfMode))
        {{-- ══════════════ CLOSING (Notes → End of text) ══════════════ --}}
        @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-closing')

        <div class="report-footer-text">
            {{ $labels['results_relate'] }}<br>
            {{ $labels['no_reproduce'] }}
        </div>
        <div class="report-issued">{{ $labels['issued_on'] }} {{ $approvalDate }}.</div>
        <div class="report-company-tag">
            {{ strtoupper($company->name ?? 'AMSPEC FIRST CLASS SUPERINTENDENT COMPANY') }}
        </div>

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

        @if(!empty($isPdfMode))
            </tbody>
            <tfoot>
                <tr>
                    <td class="pdf-frame-footer">
                        <div class="pdf-footer-closing">
                            @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-closing')
                        </div>
                        <div class="pdf-footer-meta">
                            <div class="meta-top">
                                {{ $labels['results_relate'] }}<br>
                                {{ $labels['no_reproduce'] }}
                            </div>
                            <div class="meta-issued">{{ $labels['issued_on'] }} {{ $approvalDate }}.</div>
                            <div class="meta-company">{{ strtoupper($company->name ?? 'AMSPEC FIRST CLASS SUPERINTENDENT COMPANY') }}</div>
                            <table class="meta-legal-wrap">
                                <tr>
                                    <td class="meta-legal">
                                        This document is issued by the Company subject to the Terms and Conditions at
                                        https://www.amspecgroup.com/terms-conditions. Any holder of this document is advised that
                                        information contained herein reflects the Company&#8217;s findings at the time and place of its
                                        intervention only and within the scope of the Client&#8217;s instructions. The Company&#8217;s sole
                                        responsibility is to its Client and the Company disclaims any liability to third parties.
                                        Any alteration, forgery or falsification of the content or appearance of this document is unlawful.
                                    </td>
                                    <td class="meta-qr">
                                        @if(!empty($footerQrCode))
                                            <img src="{{ $footerQrCode }}" alt="Report QR Code">
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
        @endif

    </div>{{-- end .trr-page --}}

</main>
@if(!empty($isPdfMode))
@php
    $pageNumberText = match ($language ?? 'en') {
        'ar' => 'صفحة {PAGE_NUM} من {PAGE_COUNT}',
        'pt' => 'Página {PAGE_NUM} de {PAGE_COUNT}',
        default => 'Page {PAGE_NUM} of {PAGE_COUNT}',
    };
@endphp
<script type="text/php">
    if (isset($pdf)) {
        $pageText = {!! var_export($pageNumberText, true) !!};
        $size = 8;
        $font = $fontMetrics->getFont("DejaVu Sans");
        $width = $fontMetrics->get_text_width("Page 99 of 99", $font, $size);
        // Match @page right margin (14mm ≈ 40pt).
        $x = $pdf->get_width() - $width - 40;
        $y = $pdf->get_height() - 14;
        $pdf->page_text($x, $y, $pageText, $font, $size);
    }
</script>
@endif
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
