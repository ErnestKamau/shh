{{-- DomPDF: two logical pages, maroon/green table headers, no blank page breaks --}}
@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
@endphp
<style>
    @page {
        margin: 16mm 16mm 24mm 16mm;
        size: A4 portrait;
    }

    body.amspec-download-body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9.5pt;
        color: #000000;
        line-height: 1.35;
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
        background: transparent !important;
        box-sizing: border-box;
    }

    /* Single break before page 2 only — avoids blank middle pages */
    body.amspec-download-body .amspec-pdf-page.amspec-page-two {
        page-break-before: always;
    }

    body.amspec-download-body .amspec-pdf-page.amspec-page-one {
        page-break-after: avoid;
    }

    body.amspec-download-body .amspec-quotation {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9.5pt;
        color: #000000;
        width: 100%;
        position: static !important;
    }

    body.amspec-download-body .amspec-company-name {
        font-family: 'DejaVu Serif', serif;
        font-weight: 700;
        font-size: 11pt;
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

    body.amspec-download-body .amspec-contact-line {
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-contact-line div {
        margin: 0;
        padding: 0;
        line-height: 1.15;
    }

    body.amspec-download-body .amspec-meta-table {
        width: auto !important;
        max-width: 100%;
        table-layout: auto !important;
    }

    body.amspec-download-body .amspec-meta-label {
        width: auto !important;
        white-space: nowrap;
        padding-right: 2px;
        font-size: 9pt;
        font-weight: 700;
    }

    body.amspec-download-body .amspec-meta-value,
    body.amspec-download-body .amspec-meta-value strong {
        width: auto !important;
        padding-left: 0 !important;
        font-size: 9pt;
        font-weight: 700;
    }

    body.amspec-download-body .amspec-intro {
        margin-top: 8px;
        font-size: 9.5pt;
    }

    body.amspec-download-body .amspec-page-logo {
        margin-bottom: 6px;
    }

    body.amspec-download-body .amspec-category-cell {
        background-color: #E0E0E0 !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 10pt !important;
        font-weight: 700 !important;
    }

    body.amspec-download-body .amspec-sample-description {
        font-size: 10.5pt !important;
        font-weight: 700 !important;
        color: #111827 !important;
    }

    body.amspec-download-body .amspec-package-parameters {
        color: #334155 !important;
        font-weight: 400 !important;
        font-size: 8.5pt !important;
        line-height: 1.4 !important;
        margin-top: 2px !important;
    }

    body.amspec-download-body .amspec-test-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 8px !important;
    }

    body.amspec-download-body .amspec-test-table th,
    body.amspec-download-body .amspec-test-table td {
        word-wrap: break-word;
        overflow-wrap: break-word;
        font-size: 8.5pt;
        padding: 4px 5px;
        line-height: 1.35;
        border: 1px solid #999999;
    }

    body.amspec-download-body .amspec-test-table .amspec-num-cell {
        padding-left: 2px !important;
        padding-right: 2px !important;
        white-space: nowrap;
    }

    body.amspec-download-body .amspec-test-table .amspec-price-cell {
        padding-left: 3px !important;
        padding-right: 4px !important;
        white-space: nowrap;
    }

    body.amspec-download-body .amspec-test-table .amspec-params-cell {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    body.amspec-download-body .amspec-th-primary {
        background-color: {{ $primaryColor }} !important;
        color: #ffffff !important;
        font-size: 8.5pt !important;
        font-weight: 700 !important;
        padding: 5px 4px !important;
        text-align: center !important;
    }

    body.amspec-download-body .amspec-th-accent {
        background-color: {{ $accentColor }} !important;
        color: #ffffff !important;
        font-size: 8.5pt !important;
        font-weight: 700 !important;
        padding: 5px 4px !important;
        text-align: center !important;
    }

    body.amspec-download-body .amspec-terms {
        margin-top: 2px;
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-terms-title {
        margin-bottom: 4px;
        font-size: 10.5pt;
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
        margin-top: 8px;
        font-size: 9.5pt;
    }

    body.amspec-download-body .amspec-signature p {
        margin: 0 0 4px 0;
    }

    body.amspec-download-body .amspec-signature-entity {
        margin-top: 8px;
        font-size: 9.5pt;
    }

    body.amspec-download-body .amspec-signature .amspec-sig-space {
        margin-top: 18px;
    }

    body.amspec-download-body .amspec-footer-wrap {
        position: fixed;
        bottom: -15mm;
        left: 0;
        width: 100% !important;
        height: 15mm;
        page-break-inside: avoid;
    }

    body.amspec-download-body .amspec-footer {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    body.amspec-download-body .amspec-footer-text {
        width: 88%;
        vertical-align: middle;
        padding-right: 6px;
    }

    body.amspec-download-body .amspec-footer-qr-cell {
        width: 12%;
        vertical-align: middle;
        text-align: right;
        padding: 0;
    }

    body.amspec-download-body .amspec-footer-disclaimer {
        font-size: 6.5pt;
        line-height: 1.25;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin: 0;
        text-align: center;
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
        margin-bottom: 2px;
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
        max-width: 200px;
        max-height: 48px;
        width: auto;
        height: auto;
    }

    body.amspec-download-body .amspec-hex-cluster {
        width: 110px;
        height: 82px;
        max-width: 110px;
    }

    body.amspec-download-body .amspec-test-table tfoot {
        page-break-inside: avoid;
    }
</style>
