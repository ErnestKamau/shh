@if(empty($isEmbedded))
<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@if(!empty($isPreviewMode))Preview — @endif Shelf Life Study Report &mdash; {{ $reportNumber }}</title>
@endif
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    /* ═══════════════════════════════════════════
       AmSpec Shelf Life Study Report — matches PDF
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
        font-size: 11pt;
        color: #1a1a1a;
        line-height: 1.4;
    }

    /* ── PAGE HEADER ──────────────────────────────── */
    .pg-header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2px;
        padding-bottom: 0;
        border-bottom: none;
    }
    .pg-header td {
        border: none;
        padding: 0;
        vertical-align: top;
    }
    .pg-header-logo {
        width: 58%;
        text-align: left;
    }
    .pg-header-logo img {
        max-height: 110px;
        max-width: 360px;
        object-fit: contain;
        display: block;
    }
    .pg-header-logo .logo-text {
        font-size: 28px;
        font-weight: 900;
        color: #8B1A1A;
        letter-spacing: 1px;
        display: block;
    }
    .pg-header-company {
        font-size: 10pt;
        font-weight: bold;
        color: #111;
        margin-top: 4px;
        line-height: 1.3;
    }
    .pg-header-address {
        width: 42%;
        text-align: right;
        font-size: 9pt;
        line-height: 1.35;
        color: #111;
    }
    .pg-header-address div {
        margin: 0;
    }

    /* Report title bar */
    .report-title-bar {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        padding: 8px 0 4px;
        margin: 2px 0 8px;
        color: #111;
        border-top: none;
        border-bottom: 3px solid #8B1A1A;
    }

    /* ── INFO TABLE (Attention / Client / Address) ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 11pt;
        border: 1px solid #000;
    }
    .info-table td {
        padding: 4px 8px;
        vertical-align: middle;
        text-align: left;
        border: 1px solid #000;
        background: #fff;
        color: #111;
    }

    /* ── DETAILS GRID (2-column, bordered) ──────── */
    .detail-grid {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 0;
        font-size: 11pt;
        table-layout: fixed;
    }
    .detail-grid col.detail-col-half { width: 50%; }
    .detail-grid + .detail-grid {
        margin-top: -1px;
    }
    .info-table + .detail-grid {
        margin-top: -1px;
    }
    .detail-grid td {
        border: 1px solid #000;
        padding: 4px 7px;
        vertical-align: middle;
        text-align: left;
        background: #fff;
        color: #111;
    }
    .detail-grid .val-emphasize {
        font-weight: bold;
        color: #8B1A1A;
    }
    .sample-ref-grid {
        margin-bottom: 4px;
    }
    .sample-detail-grid {
        margin-top: 6px;
        margin-bottom: 0;
    }
    .sls-conditions {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 10px;
        font-size: 10pt;
        table-layout: fixed;
    }
    .sls-conditions th {
        background: #8B1A1A;
        color: #fff;
        text-align: left;
        padding: 6px 8px;
        border: 1px solid #6e1515;
        font-size: 10pt;
        letter-spacing: 0.02em;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .sls-conditions td {
        border: 1px solid #000;
        padding: 4px 7px;
        vertical-align: top;
        background: #fff;
    }
    .sls-conditions .sls-cond-label {
        font-weight: bold;
        width: 34%;
    }
    .trr-sample-block + .trr-sample-block {
        margin-top: 18px;
        padding-top: 12px;
        border-top: 1px dashed #d0d0d0;
    }
    .trr-sample-photo-page {
        margin-top: 18px;
        padding-top: 14px;
        border-top: 1px dashed #d0d0d0;
        page-break-before: always;
        page-break-inside: avoid;
    }
    .trr-sample-photo-heading {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 12px;
        font-size: 10pt;
    }
    .trr-sample-photo-heading th {
        background: #8B1A1A;
        color: #fff;
        text-align: left;
        padding: 6px 8px;
        border: 1px solid #6e1515;
        font-size: 10pt;
        letter-spacing: 0.02em;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .trr-sample-photo-frame {
        width: 150mm;
        height: 100mm;
        margin: 8mm auto 0;
        padding: 0;
        border: 1px solid #ccc;
        overflow: hidden;
        text-align: center;
    }
    .trr-sample-photo-frame img {
        width: 150mm;
        height: 100mm;
        display: block;
        border: 0;
    }

    /* ── RESULTS TABLE ──────────────────────────── */
    .results-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        margin-top: 10px;
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
        font-size: 9pt;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .results-table th:first-child {
        text-align: left;
    }
    .results-table td {
        border: 1px solid #000;
        padding: 4px 5px;
        vertical-align: middle;
        text-align: center;
    }
    .results-table td:first-child {
        text-align: left;
    }
    .results-table tr:nth-child(even) td { background: #fbfbfb; }
    .results-table tr.results-lab-section td {
        background: #fff;
        text-align: left;
        font-weight: normal;
        padding: 5px 8px;
        border: 1px solid #000;
    }
    .results-section-heading {
        margin: 12px 0 0;
        padding: 5px 8px;
        background: #efe8e8;
        border: 1px solid #000;
        border-bottom: 0;
        font-size: 9.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #222;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .results-section-heading + .results-table {
        margin-top: 0;
    }
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
    .sample-notes > strong {
        display: block;
        margin-bottom: 2px;
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
        margin-top: 8px;
        font-size: 11pt;
    }
    .meta-box td {
        border: 1px solid #000;
        padding: 6px 10px;
        width: 50%;
        vertical-align: middle;
        background: #fff;
    }

    /* ── SIGNATURE BLOCK ────────────────────────── */
    .sig-section {
        margin-top: 14px;
        border-top: none;
        padding-top: 4px;
        page-break-inside: avoid;
    }
    .sig-section .sig-intro {
        font-weight: bold;
        font-size: 11pt;
        margin-bottom: 8px;
        color: #111;
    }
    .sig-name { font-weight: bold; font-size: 11pt; margin-top: 0; color: #111; }
    .sig-title-line { font-size: 11pt; color: #111; }
    .sig-company-line { font-size: 11pt; margin-bottom: 6px; color: #111; }
    .sig-image-box {
        display: inline-block;
        border: none;
        padding: 4px 0;
        min-width: 140px;
        min-height: 42px;
        margin-top: 2px;
    }
    .sig-image-box img {
        max-height: 48px;
        max-width: 180px;
        display: block;
    }
    .sig-image-box .sig-missing {
        color: #aaa;
        font-size: 9pt;
        font-style: italic;
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
        font-size: 10pt;
        margin: 28px 0 8px;
        font-style: italic;
        color: #555;
        letter-spacing: 0.03em;
    }

    /* ── PAGE DISCLAIMER FOOTER (every page) ─────── */
    .page-disclaimer,
    .pdf-footer-legal {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        border-top: 1px solid #999;
        padding-top: 6px;
    }
    .page-disclaimer td,
    .pdf-footer-legal td {
        border: none;
        vertical-align: top;
        padding: 0;
    }
    .page-disclaimer .disclaimer-text,
    .pdf-footer-legal .pdf-footer-disclaimer {
        font-size: 6pt;
        color: #222;
        line-height: 1.35;
        text-align: left;
        padding-right: 8px;
    }
    .page-disclaimer .disclaimer-qr,
    .pdf-footer-legal .pdf-footer-qr {
        width: 58px;
        text-align: right;
    }
    .page-disclaimer .disclaimer-qr img,
    .pdf-footer-legal .pdf-footer-qr img {
        width: 54px;
        height: 54px;
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
     * DomPDF: `*` reset zeroes html margins (used as page margins).
     * position:fixed is relative to the CONTENT box; negative offsets pull
     * the header/footer into the reserved margins on every page.
     * Fixed elements only repeat when they are direct children of <body>.
     */
    @page {
        size: A4 portrait;
    }
    html {
        margin: 54mm 12mm 28mm 12mm;
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
        font-size: 10pt;
    }
    .pdf-doc-header {
        position: fixed;
        top: -50mm;
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
        vertical-align: top;
    }
    .pdf-doc-header .pg-header-logo img {
        max-height: 28mm;
        max-width: 90mm;
    }
    .pdf-doc-header .logo-text {
        font-size: 26px;
    }
    .pdf-doc-header .pg-header-company {
        font-size: 9pt;
        margin-top: 2px;
    }
    .pdf-doc-header .pg-header-address {
        font-size: 8.5pt;
        line-height: 1.3;
    }
    .pdf-doc-header .report-title-bar {
        margin: 2px 0 0;
        padding: 3px 0 2px;
        font-size: 11pt;
        letter-spacing: 1px;
        border-top: none;
        border-bottom: 2.5px solid #8B1A1A;
    }
    .pdf-doc-footer {
        position: fixed;
        bottom: -24mm;
        left: 0;
        right: 0;
        width: auto;
        margin: 0;
        padding: 0;
        background: #fff;
    }
    .pdf-doc-footer .pdf-footer-legal {
        margin-top: 0;
        border-top: 1px solid #999;
        padding-top: 3px;
    }
    .pdf-doc-footer .pdf-footer-disclaimer {
        font-size: 6pt;
        line-height: 1.3;
        font-weight: normal;
        text-align: left;
    }
    .pdf-doc-footer .pdf-footer-qr {
        width: 48px;
    }
    .pdf-doc-footer .pdf-footer-qr img {
        width: 44px;
        height: 44px;
    }
    .info-table,
    .detail-grid,
    .results-table,
    .meta-box,
    .pg-header {
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
    .info-table {
        font-size: 9pt;
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    .info-table td {
        padding: 3px 6px;
    }
    .info-table + .detail-grid {
        margin-top: -1px;
    }
    .detail-grid {
        margin-bottom: 0;
        font-size: 9pt;
        page-break-inside: auto;
    }
    .detail-grid td {
        padding: 3px 5px;
    }
    .sample-detail-grid {
        page-break-after: avoid;
    }
    .trr-sample-block + .trr-sample-block {
        page-break-before: always;
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }
    .trr-sample-photo-page {
        page-break-before: always;
        page-break-inside: avoid;
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }
    .trr-sample-photo-heading {
        font-size: 9pt;
        margin-bottom: 10px;
        page-break-after: avoid;
    }
    .trr-sample-photo-heading th {
        padding: 5px 7px;
        font-size: 9pt;
    }
    .trr-sample-photo-frame {
        width: 150mm;
        height: 100mm;
        margin: 8mm auto 0;
    }
    .trr-sample-photo-frame img {
        width: 150mm;
        height: 100mm;
        display: block;
    }
    .results-table {
        font-size: 8pt;
        margin-top: 8px;
        margin-bottom: 6px;
        page-break-inside: auto;
    }
    .results-table th {
        padding: 4px 3px;
        font-size: 7.5pt;
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
    .sample-interpretations,
    .sample-notes {
        font-size: 9pt;
        padding: 3px 0;
        page-break-inside: avoid;
    }
    .meta-box {
        font-size: 9pt;
        page-break-inside: avoid;
    }
    .sig-section {
        page-break-inside: avoid;
    }
    .sig-section .sig-intro,
    .sig-name,
    .sig-title-line,
    .sig-company-line {
        font-size: 9pt;
    }
    .sig-image-box img {
        max-height: 36px;
        max-width: 140px;
    }
    .end-text {
        font-size: 9pt;
        margin: 16px 0 4px;
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
        .trr-sample-photo-page {
            page-break-before: always;
            page-break-inside: avoid;
            margin-top: 0;
            padding-top: 0;
            border-top: none;
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

@php
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
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-header')
</div>
<div class="pdf-doc-footer">
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-legal-footer')
</div>
@endif

<main>

    {{-- ── Screen toolbar / preview chrome ── --}}
    @if(empty($isPdfMode) && empty($isEmbedded) && empty($hideScreenToolbar))
        @if(!empty($isPreviewMode))
            <div class="trr-preview-chrome">
                <div class="trr-preview-copy">
                    <span class="trr-preview-badge">Draft preview</span>
                    <h1>Shelf Life Study Report</h1>
                    <p>
                        This is how the final shelf life report will look with the current results and study conditions.
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

        {{-- ══════════════ One complete report section per sample ══════════════ --}}
        @forelse ($samples as $sample)
            <div @class(['trr-sample-block' => true, 'trr-sample-block-first' => $loop->first])>
            @if(empty($isPdfMode))
                @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-header')
            @endif

            @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-client-info')

            @include('layouts.lab.sample-workflow.report-formats.partials.trr-sample-detail-grid', [
                'sampleIndex' => $loop->index,
            ])

            @php
                $context = $sampleDetailContexts[$loop->index] ?? [];
                $conditions = is_array($context['conditions'] ?? null)
                    ? $context['conditions']
                    : ($shelfLifeConditionsBySampleDetailId[$sample->id] ?? []);
            @endphp
            <table class="sls-conditions">
                <thead>
                    <tr>
                        <th colspan="2">{{ $labels['accelerated_conditions'] ?? 'Accelerated Shelf-Life Study Conditions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="sls-cond-label">{{ $labels['study_type'] ?? 'Study Type' }}</td>
                        <td>{{ $conditions['study_type'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="sls-cond-label">{{ $labels['accelerated_temperature'] ?? 'Accelerated Temperature' }}</td>
                        <td>{{ $conditions['accelerated_temperature'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="sls-cond-label">{{ $labels['study_duration'] ?? 'Study Duration' }}</td>
                        <td>{{ $conditions['study_duration'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="sls-cond-label">{{ $labels['relative_humidity'] ?? 'Relative Humidity' }}</td>
                        <td>{{ $conditions['relative_humidity'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="sls-cond-label">{{ $labels['evaluation_type'] ?? 'Evaluation Type' }}</td>
                        <td>{{ $conditions['evaluation_type'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="sls-cond-label">{{ $labels['sampling_frequency'] ?? 'Sampling Frequency' }}</td>
                        <td>{{ $conditions['sampling_frequency'] ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>

            @php
                $standardLimitDisplay = app(\App\Services\StandardLimitDisplayService::class);
                $sampleResultRows = [];
                foreach ($sample->getSampleByAnalysisType() as $atLevel) {
                    foreach ($atLevel->getCapturedResults() as $cr) {
                        $sectionId = trim((string) ($cr->lab_section_id ?? ''));
                        $sectionName = trim((string) ($cr->labSection?->name ?? ''));
                        if ($sectionName === '') {
                            $sectionName = $labels['lab_section'] ?? 'Lab Section';
                        }
                        $sampleResultRows[] = [
                            'cr' => $cr,
                            'lab_section_id' => $sectionId !== '' ? $sectionId : '__unassigned__',
                            'lab_section_name' => $sectionName,
                            'conclusion' => $conclusionByCapturedResultId[$cr->id] ?? '—',
                            'spec' => $standardLimitDisplay->forCapturedResult($cr, $sample->main_standard ?? null) ?? '-',
                            'spec_standard' => $standardLimitDisplay->standardNameForCapturedResult($cr, $sample->main_standard ?? null) ?? '-',
                        ];
                    }
                }

                $resultsByLabSection = app(\App\Services\Sampleworkflow\TestRequestReportDataService::class)
                    ->groupResultRowsByLabSection(
                        $sampleResultRows,
                        (string) ($labels['lab_section'] ?? 'Lab Section'),
                    );

                $resultsColspan = 7;
            @endphp

            @forelse ($resultsByLabSection as $section)
                <div class="results-section-heading">{{ $section['name'] }}</div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width:18%">{{ $labels['analyte'] }}</th>
                            <th style="width:12%">{{ $labels['results'] }}</th>
                            <th style="width:8%">{{ $labels['unit'] }}</th>
                            <th style="width:14%">{{ $labels['specification'] }}</th>
                            <th style="width:16%">{{ $labels['standard_name'] ?? 'Specification Standard' }}</th>
                            <th style="width:18%">{{ $labels['method'] }}</th>
                            <th style="width:14%">{{ $labels['conclusion'] ?? 'Conclusion' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['rows'] as $row)
                            @php
                                $cr = $row['cr'];
                                $conclusion = $row['conclusion'];
                            @endphp
                            <tr>
                                <td>
                                    {!! isset($cr->isitalic) && $cr->isitalic == 1 ? '<em>' . e($cr->analyte_code) . '</em>' : e($cr->analyte_code) !!}@if((int) ($cr->analyte_status_contracted ?? 0) === 1)<sup style="color:#c00;font-weight:bold;">¹</sup>@endif@if((int) ($cr->analyte_accredited ?? 1) === 0)<span style="color:#c00;font-weight:bold;">*</span>@endif
                                </td>
                                <td class="{{ isset($cr->remark) && strtoupper($cr->remark) == 'FAIL' ? 'fail' : '' }}">
                                    {{ ($cr->result_reporting_symbol ?? '') . ($cr->result !== null && $cr->result !== '' ? $cr->result : '-') }}
                                </td>
                                <td>{{ resolveReportingUnitLabel($cr->reporting_unit_id ?? null) }}</td>
                                <td>{{ $row['spec'] }}</td>
                                <td>{{ $row['spec_standard'] }}</td>
                                <td>{{ strtoupper($cr->method()?->name ?? $cr->ltmethod?->name ?? '-') }}</td>
                                <td class="{{ strtoupper((string) $conclusion) === 'FAIL' ? 'fail' : (strtoupper((string) $conclusion) === 'PASS' ? 'pass' : '') }}">
                                    {{ $conclusion }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @empty
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width:18%">{{ $labels['analyte'] }}</th>
                            <th style="width:12%">{{ $labels['results'] }}</th>
                            <th style="width:8%">{{ $labels['unit'] }}</th>
                            <th style="width:14%">{{ $labels['specification'] }}</th>
                            <th style="width:16%">{{ $labels['standard_name'] ?? 'Specification Standard' }}</th>
                            <th style="width:18%">{{ $labels['method'] }}</th>
                            <th style="width:14%">{{ $labels['conclusion'] ?? 'Conclusion' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="{{ $resultsColspan }}" style="text-align:center;color:#888;font-style:italic;padding:8px;">
                                {{ $labels['no_results'] }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforelse

            @if($sample->header_body)
            <div class="sample-remarks">
                <strong>Remarks:</strong> {!! str_ireplace(['not conforming', 'non-conforming'], 'Non-Conforming', $sample->header_body) !!}
            </div>
            @endif

            @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-closing', [
                'sampleIndex' => $loop->index,
            ])

            <div class="sample-notes" style="margin-top:8px;">
                <strong>{{ $labels['notes'] ?? 'Note' }}:</strong>
                <ol style="margin:4px 0 0 18px;padding:0;">
                    <li>{{ $labels['shelf_life_note_1'] }}</li>
                    <li>{{ $labels['shelf_life_note_2'] }}</li>
                </ol>
            </div>

            @include('layouts.lab.sample-workflow.report-formats.partials.trr-sample-photo', [
                'sampleIndex' => $loop->index,
                'sampleCode' => $sample->sample_code ?? '',
            ])

            @if(empty($isPdfMode))
                @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-legal-footer')
            @endif

            </div>{{-- .trr-sample-block --}}

        @empty
            <div style="padding:14px 0;font-style:italic;color:#888;">{{ $labels['no_samples'] }}</div>
        @endforelse

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
        // Match @page left margin (12mm ≈ 34pt). Sit above the disclaimer band.
        $x = 34;
        $y = $pdf->get_height() - 78;
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
