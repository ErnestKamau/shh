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
        color: rgba(100, 116, 139, 0.16);
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
        margin-bottom: 4px;
        padding-bottom: 0;
        border-bottom: none;
    }
    .pg-header td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }
    .pg-header-logo {
        width: 38%;
        text-align: left;
    }
    .pg-header-logo img {
        max-height: 110px;
        max-width: 300px;
        object-fit: contain;
        display: block;
    }
    .pg-header-logo .logo-text {
        font-size: 28px;
        font-weight: 900;
        color: #8B1A1A;
        letter-spacing: 1px;
    }
    .pg-header-center {
        width: 24%;
        text-align: center;
        vertical-align: middle;
        padding: 0 6px;
    }
    .pg-header-center .cert-no {
        font-size: 10.5px;
        color: #333;
        letter-spacing: 0.02em;
        line-height: 1.35;
    }
    .pg-header-center .page-of {
        font-size: 10px;
        color: #666;
    }
    .pg-header-right {
        width: 38%;
        text-align: right;
    }
    .pg-header-right img {
        max-height: 110px;
        max-width: 260px;
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
        padding: 6px 0 5px;
        margin: 0 0 0;
        color: #111;
        border-top: 1px solid #d8d8d8;
        border-bottom: 2px solid #8B1A1A;
    }

    /* ── INFO TABLE (Attention / Client / Address) ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0 0;
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
        margin: 0 0 8px;
        font-size: 10.5px;
        table-layout: fixed;
    }
    .detail-grid col.detail-col-label { width: 16%; }
    .detail-grid col.detail-col-value { width: 34%; }
    .detail-grid + .detail-grid,
    .info-table + .detail-grid {
        margin-top: -1px;
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
        width: 16%;
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
        max-height: 90px;
        max-width: 200px;
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

    .bottom-logos {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0 2px;
    }
    .bottom-logos td {
        border: none;
        padding: 0;
        vertical-align: bottom;
    }
    .bottom-logos td:first-child { text-align: left; }
    .bottom-logos td:last-child { text-align: right; }
    .bottom-logos img {
        max-height: 95px;
        max-width: 240px;
        object-fit: contain;
    }

    @if(!empty($isPdfMode))
    /*
     * Verified against this dompdf build (storage/tmp-pdf-test harness):
     * - The `*` reset zeroes html margins, which dompdf uses as the page
     *   margins — so page margins MUST be re-asserted on `html` here.
     * - position:fixed is relative to the CONTENT box; negative offsets pull
     *   the header/footer into the reserved margins on every page.
     * - Fixed elements only repeat when they are direct children of <body>.
     * - Client details sit in the body (page 1) so they sit flush against batch info.
     */
    @page {
        size: A4 portrait;
    }
    html {
        /* Header band is logos + title only; client info flows in the body. */
        margin: 32mm 12mm 100mm 12mm;
        padding: 0;
    }
    body, main {
        margin: 0;
        padding: 0;
        background: #fff;
    }
    .trr-page {
        width: auto;
        max-width: none;
        margin: 0;
        border: none;
        padding: 0;
        overflow: visible;
    }
    .pdf-doc-header {
        position: fixed;
        /* Pull up into the 32mm top margin (4mm from the page edge). */
        top: -28mm;
        left: 0;
        right: 0;
        width: auto;
        margin: 0;
        padding: 0;
        background: #fff;
    }
    .pdf-doc-header .pg-header {
        margin-bottom: 0;
        border-bottom: none;
    }
    .pdf-doc-header .pg-header td {
        padding: 0;
        border: none;
        vertical-align: middle;
    }
    .pdf-doc-header .pg-header-logo img,
    .pdf-doc-header .pg-header-right img {
        max-height: 22mm;
        max-width: 68mm;
    }
    .pdf-doc-header .logo-text {
        font-size: 22px;
    }
    .pdf-doc-header .cert-no {
        font-size: 9.5px;
    }
    .pdf-doc-header .report-title-bar {
        margin: 0;
        padding: 3px 0 3px;
        font-size: 11px;
        letter-spacing: 1px;
        border-top: 1px solid #d8d8d8;
        border-bottom-width: 1.5px;
    }
    .pdf-body-client-info .info-table {
        margin: 0 0 0;
        font-size: 9px;
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    .pdf-body-client-info .info-table td {
        padding: 2px 5px;
    }
    .pdf-body-client-info .info-table .lbl {
        width: 120px;
    }
    .pdf-body-client-info + .detail-grid {
        margin-top: -1px;
    }
    .pdf-doc-footer {
        position: fixed;
        /* Pull down into the 100mm bottom margin (6mm from the page edge). */
        bottom: -94mm;
        left: 0;
        right: 0;
        width: auto;
        margin: 0;
        padding: 0;
        background: #fff;
    }
    .pdf-doc-footer .pdf-footer-closing {
        border-top: 1px solid #cfcfcf;
        padding-top: 2px;
        margin-bottom: 2px;
    }
    .pdf-doc-footer .sample-notes,
    .pdf-doc-footer .sample-amendment {
        font-size: 7.5px;
        padding: 1px 0;
        line-height: 1.3;
        margin: 0;
    }
    .pdf-doc-footer .sample-amendment {
        border: 1px solid #d4b896;
        background: #fffaf0;
        padding: 3px 5px;
        margin: 2px 0;
    }
    .pdf-doc-footer .sample-amendment div[style] {
        margin-top: 1px !important;
    }
    .pdf-doc-footer .meta-box {
        margin-top: 2px;
        font-size: 8px;
    }
    .pdf-doc-footer .meta-box td {
        padding: 2px 5px;
    }
    .pdf-doc-footer .sig-section {
        margin-top: 3px;
        padding-top: 3px;
        border-top: 1px solid #c4c4c4;
    }
    .pdf-doc-footer .sig-section .sig-intro {
        font-size: 8px;
        margin-bottom: 2px;
    }
    .pdf-doc-footer .sig-name {
        font-size: 8.5px;
    }
    .pdf-doc-footer .sig-title-line,
    .pdf-doc-footer .sig-company-line {
        font-size: 8px;
    }
    .pdf-doc-footer .sig-image {
        min-height: 18px;
        margin: 0;
    }
    .pdf-doc-footer .sig-image img {
        max-height: 28px;
        max-width: 110px;
    }
    .pdf-doc-footer .sig-block .sig-right img {
        max-height: 18mm;
        max-width: 42mm;
    }
    .pdf-doc-footer .sig-underline {
        width: 130px;
        margin-top: 0;
    }
    .pdf-doc-footer .bottom-logos {
        margin: 2px 0 1px;
    }
    .pdf-doc-footer .bottom-logos img {
        max-height: 20mm !important;
        max-width: 52mm !important;
    }
    .pdf-doc-footer .end-text {
        display: block;
        text-align: center;
        font-size: 7.5px;
        font-style: italic;
        color: #555;
        margin: 2px 0 3px;
    }
    .pdf-doc-footer .pdf-footer-meta {
        border-top: 1px solid #cfcfcf;
        padding-top: 2px;
    }
    .pdf-doc-footer .meta-top {
        font-size: 6.5px;
        line-height: 1.2;
        color: #333;
        margin-bottom: 1px;
    }
    .pdf-doc-footer .meta-issued {
        text-align: center;
        font-size: 6.5px;
        color: #333;
        margin-bottom: 1px;
    }
    .pdf-doc-footer .meta-company {
        text-align: center;
        font-size: 7.5px;
        font-weight: bold;
        letter-spacing: .3px;
        margin-bottom: 2px;
        text-transform: uppercase;
        color: #111;
    }
    .pdf-doc-footer .meta-legal-wrap {
        width: 100%;
        border-collapse: collapse;
    }
    .pdf-doc-footer .meta-legal-wrap td {
        border: none;
        vertical-align: top;
        padding: 0;
    }
    .pdf-doc-footer .meta-legal {
        font-size: 5px;
        line-height: 1.25;
        color: #222;
        font-weight: bold;
        padding-right: 6px;
    }
    .pdf-doc-footer .meta-qr {
        width: 42px;
        text-align: right;
    }
    .pdf-doc-footer .meta-qr img {
        width: 38px;
        height: 38px;
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
    .detail-grid {
        margin-bottom: 6px;
        font-size: 9.5px;
        page-break-inside: auto;
        page-break-after: avoid;
    }
    .detail-grid td {
        padding: 3px 5px;
    }
    .sample-ref-grid {
        margin-bottom: 3px;
        page-break-after: avoid;
    }
    .results-table {
        font-size: 9px;
        margin-bottom: 6px;
        page-break-inside: auto;
    }
    .results-table th {
        padding: 4px 3px;
        font-size: 8.5px;
    }
    .results-table td {
        padding: 3px 3px;
    }
    .results-table thead {
        display: table-header-group;
    }
    .results-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    .sample-remarks,
    .sample-interpretations {
        font-size: 9px;
        padding: 3px 0;
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
            /* DomPDF uses print media; page gutters come from the html margin,
               so any padding here would misalign the body with the fixed
               header/footer. Keep the padding for browser printing only. */
            padding: {{ !empty($isPdfMode) ? '0' : '12px 18px' }};
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
            color: rgba(100, 116, 139, 0.18) !important;
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

@include('partials.report-watermark', [
    'company' => $company ?? null,
    'forPdf' => !empty($isPdfMode),
])

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

@if(!empty($isPdfMode))
{{-- Repeating header / footer. Must be direct children of <body> (outside
     <main>) or DomPDF only paints them on the first page. --}}
<div class="pdf-doc-header">
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-header', ['showClientInfo' => false])
</div>
<div class="pdf-doc-footer">
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
</div>
@endif

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

        @if(empty($isPdfMode))
        {{-- ══════════════ PAGE HEADER (logo + client info) ══════════════ --}}
        @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-header')
        @else
        {{-- Client details flow with batch info (page 1 only; not in fixed header) --}}
        <div class="pdf-body-client-info">
            @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-client-info')
        </div>
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
            @include('layouts.lab.sample-workflow.report-formats.partials.trr-detail-grid-cols')
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

        {{-- ══════════════ TEST RESULTS ══════════════ --}}
        @forelse ($samples as $sample)

            {{-- Per-sample subheader --}}
            <table class="detail-grid sample-ref-grid">
                @include('layouts.lab.sample-workflow.report-formats.partials.trr-detail-grid-cols')
                <tr>
                    <td class="dlbl">{{ $labels['sample_reference'] }}</td>
                    <td class="detail-val"><strong>{{ format_sample_code($sample->sample_code) }}</strong></td>
                    @php
                        $samplePointValue = $samplePointByIndex[$loop->index] ?? ($sample->sample_point_name ?? null);
                        $samplePointFilled = filled(trim((string) ($samplePointValue ?? '')))
                            && ! in_array(trim((string) $samplePointValue), ['-', '—'], true);
                    @endphp
                    @if($samplePointFilled)
                    <td class="dlbl">{{ $labels['sample_point'] }}</td>
                    <td class="detail-val">{{ $samplePointValue }}</td>
                    @else
                    <td colspan="2">&nbsp;</td>
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

        @empty
            <div style="padding:14px 0;font-style:italic;color:#888;">{{ $labels['no_samples'] }}</div>
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
        // Match @page right margin (12mm ≈ 34pt).
        $x = $pdf->get_width() - $width - 34;
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
