{{-- DomPDF: two-page layout with footer pinned to bottom of each logical page --}}
@php
    $primaryColor = $branding['primary'] ?? '#6D0A0E';
@endphp
<style>
    @page {
        margin: 12mm 11mm 12mm 11mm;
        size: A4 portrait;
    }

    body.amspec-download-body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9.5pt;
        color: #000000;
        line-height: 1.3;
    }

    body.amspec-download-body .amspec-document-shell,
    body.amspec-download-body .amspec-shell-download {
        margin: 0;
        padding: 0;
        max-width: none;
        width: 100%;
    }

    body.amspec-download-body .amspec-shell-download .amspec-page-sheet {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
        background: #ffffff;
        box-sizing: border-box;
    }

    /* Page 1 ends here; page 2 starts on next sheet — single break only */
    body.amspec-download-body .amspec-pdf-page.amspec-page-one {
        page-break-after: always;
    }

    body.amspec-download-body .amspec-pdf-page.amspec-page-two {
        page-break-before: avoid;
        page-break-after: avoid;
    }

    body.amspec-download-body .amspec-pdf-page-layout {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /*
     * Printable height ≈ 297mm − 24mm margins = 273mm.
     * Reserve ~24mm for footer row; main content uses the rest.
     */
    body.amspec-download-body .amspec-pdf-page-main {
        vertical-align: top;
        padding: 0;
    }

    body.amspec-download-body .amspec-pdf-page-footer-cell {
        vertical-align: bottom;
        height: 24mm;
        padding-top: 6px;
        page-break-inside: avoid;
    }

    body.amspec-download-body .amspec-quotation {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9.5pt;
        color: #000000;
        width: 100%;
    }

    body.amspec-download-body .amspec-company-name {
        font-family: 'DejaVu Serif', serif;
        font-weight: 700;
        font-size: 10.5pt;
    }

    body.amspec-download-body .amspec-contact-line,
    body.amspec-download-body .amspec-meta-label,
    body.amspec-download-body .amspec-meta-value,
    body.amspec-download-body .amspec-intro,
    body.amspec-download-body .amspec-test-row td,
    body.amspec-download-body .amspec-totals td,
    body.amspec-download-body .amspec-terms,
    body.amspec-download-body .amspec-signature,
    body.amspec-download-body .amspec-th-primary,
    body.amspec-download-body .amspec-th-accent,
    body.amspec-download-body .amspec-terms-title,
    body.amspec-download-body .amspec-signature-entity,
    body.amspec-download-body .amspec-footer-disclaimer {
        font-family: 'DejaVu Sans', sans-serif;
    }

    body.amspec-download-body .amspec-contact-line,
    body.amspec-download-body .amspec-meta-value {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    body.amspec-download-body .amspec-company-address {
        font-weight: 700;
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-meta-label,
    body.amspec-download-body .amspec-meta-value,
    body.amspec-download-body .amspec-meta-value strong {
        font-size: 9pt;
        font-weight: 700;
    }

    body.amspec-download-body .amspec-intro {
        margin-top: 10px;
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-page-logo {
        margin-bottom: 6px;
    }

    body.amspec-download-body .amspec-category-cell {
        background-color: #E0E0E0;
    }

    body.amspec-download-body .amspec-test-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 10px !important;
    }

    body.amspec-download-body .amspec-test-table th,
    body.amspec-download-body .amspec-test-table td {
        word-wrap: break-word;
        overflow-wrap: break-word;
        font-size: 7.5pt;
        padding: 2px 2px;
        line-height: 1.25;
    }

    body.amspec-download-body .amspec-th-primary,
    body.amspec-download-body .amspec-th-accent {
        font-size: 7pt !important;
        padding: 3px 2px !important;
    }

    body.amspec-download-body .amspec-terms {
        margin-top: 2px;
        font-size: 8.5pt;
    }

    body.amspec-download-body .amspec-terms-title {
        margin-bottom: 4px;
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-terms ol {
        padding-left: 16px;
        margin: 0;
    }

    body.amspec-download-body .amspec-terms li {
        margin-bottom: 2px;
        line-height: 1.25;
    }

    body.amspec-download-body .amspec-signature {
        margin-top: 10px;
        font-size: 8.5pt;
    }

    body.amspec-download-body .amspec-signature p {
        margin: 0 0 4px 0;
    }

    body.amspec-download-body .amspec-signature-entity {
        margin-top: 10px;
        font-size: 8.5pt;
    }

    body.amspec-download-body .amspec-signature .amspec-sig-space {
        margin-top: 18px;
    }

    body.amspec-download-body .amspec-footer-wrap {
        margin-top: 0;
        width: 100%;
    }

    body.amspec-download-body .amspec-footer {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    body.amspec-download-body .amspec-footer-text {
        width: 78%;
        vertical-align: middle;
        padding-right: 6px;
    }

    body.amspec-download-body .amspec-footer-qr-cell {
        width: 22%;
        vertical-align: middle;
        text-align: right;
        padding: 0;
    }

    body.amspec-download-body .amspec-footer-disclaimer {
        font-size: 6pt;
        line-height: 1.25;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin: 0;
    }

    body.amspec-download-body .amspec-footer-disclaimer a {
        color: {{ $primaryColor }};
        word-break: break-all;
    }

    body.amspec-download-body .amspec-footer-qr {
        width: 52px;
        height: 52px;
        border: none;
    }

    body.amspec-download-body .amspec-watermark {
        display: none;
    }

    body.amspec-download-body .amspec-brand-row {
        width: 100%;
        margin-bottom: 4px;
    }

    body.amspec-download-body .amspec-brand-left {
        width: 55%;
        vertical-align: middle;
    }

    body.amspec-download-body .amspec-brand-right {
        width: 45%;
        text-align: right;
        vertical-align: top;
    }

    body.amspec-download-body .amspec-logo-wordmark {
        width: 180px;
        height: 42px;
        max-width: 180px;
        max-height: 42px;
    }

    body.amspec-download-body .amspec-hex-cluster {
        width: 110px;
        height: 82px;
        max-width: 110px;
    }

    /* Keep totals + footer together when possible */
    body.amspec-download-body .amspec-test-table tfoot {
        page-break-inside: avoid;
    }

    body.amspec-download-body .amspec-pdf-page-footer-cell .amspec-footer-wrap {
        page-break-before: avoid;
    }
</style>
