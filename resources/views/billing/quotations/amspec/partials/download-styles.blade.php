{{-- DomPDF: two logical pages, maroon/green table headers, no blank page breaks --}}
@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
@endphp
<style>
    @page {
        margin: 12mm 18mm 32mm 18mm;
        size: A4 portrait;
    }

    /*
     * DomPDF: position:fixed is relative to the page CONTENT box.
     * Use a negative bottom offset to pull the footer into the reserved
     * bottom margin on every page (same approach as test_request_report).
     * The footer must remain a direct child of <body>.
     */

    body.amspec-download-body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10pt;
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
        overflow: visible;
    }

    body.amspec-download-body .amspec-page-body {
        padding: 0 1.5mm;
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
        font-size: 10pt;
        color: #000000;
        width: 100%;
        position: static !important;
    }

    body.amspec-download-body .amspec-company-name {
        font-family: 'DejaVu Serif', serif;
        font-weight: 700;
        font-size: 11pt;
    }

    body.amspec-download-body .amspec-company-address {
        font-family: 'DejaVu Serif', serif;
        font-weight: 700;
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-meta-label,
    body.amspec-download-body .amspec-meta-value,
    body.amspec-download-body .amspec-meta-value strong {
        font-family: 'DejaVu Serif', serif;
    }

    body.amspec-download-body .amspec-contact-line,
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

    body.amspec-download-body .amspec-logo-wordmark {
        max-height: 57px !important;
        max-width: 239px !important;
        height: auto !important;
        width: auto !important;
        border: none !important;
        margin-bottom: 6px !important;
    }

    body.amspec-download-body .amspec-hex-cluster {
        width: auto !important;
        max-width: 109px !important;
        max-height: 59px !important;
        height: 59px !important;
        border: 0 !important;
        outline: none !important;
        margin-bottom: 6px !important;
    }

    body.amspec-download-body .amspec-header-logos {
        margin-top: -4px;
    }

    body.amspec-download-body .amspec-header-block {
        margin-top: -2mm !important;
    }

    body.amspec-download-body .amspec-header-logos td {
        vertical-align: middle !important;
        padding-bottom: 16px !important;
    }

    body.amspec-download-body .amspec-header-logos .amspec-logo-wordmark,
    body.amspec-download-body .amspec-header-logos .amspec-hex-cluster {
        margin-bottom: 0 !important;
        vertical-align: middle !important;
    }

    body.amspec-download-body .amspec-header-hex-wrap {
        display: inline-block;
        line-height: 0;
        min-height: 0;
        text-align: right;
        vertical-align: middle;
        margin-top: -4px;
    }

    body.amspec-download-body .amspec-header-customer-contact {
        display: inline-block;
        text-align: left;
        min-width: 180px;
        margin-top: 4px;
    }

    body.amspec-download-body .amspec-customer-name {
        font-family: 'DejaVu Serif', serif;
        font-weight: 700;
        font-size: 11pt;
    }

    body.amspec-download-body .amspec-customer-address {
        font-family: 'DejaVu Serif', serif;
        font-weight: 700;
        font-size: 9pt;
    }

    body.amspec-download-body .amspec-category-banner {
        background-color: #E0E0E0 !important;
        font-size: 10pt !important;
        font-weight: 700 !important;
        text-align: left !important;
        padding: 5px 8px !important;
    }

    body.amspec-download-body .amspec-th-sub {
        display: block;
        font-size: 7pt !important;
        font-weight: 600 !important;
        line-height: 1.1 !important;
        margin-top: 1px !important;
    }

    body.amspec-download-body .amspec-totals td.text-center {
        text-align: center !important;
    }

    body.amspec-download-body .amspec-contact-line,
    body.amspec-download-body .amspec-meta-value {
        word-wrap: break-word;
        overflow-wrap: break-word;
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
        max-width: 100%;
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
        font-size: 9pt !important;
        font-weight: 700 !important;
        padding: 5px 4px !important;
        text-align: center !important;
    }

    body.amspec-download-body .amspec-th-accent {
        background-color: {{ $accentColor }} !important;
        color: #ffffff !important;
        font-size: 9pt !important;
        font-weight: 700 !important;
        padding: 5px 4px !important;
        text-align: center !important;
    }

    body.amspec-download-body .amspec-terms {
        margin-top: 4px;
        font-size: 10.5pt;
        line-height: 1.4;
    }

    body.amspec-download-body .amspec-terms-title {
        margin-bottom: 8px;
        font-size: 11.5pt;
    }

    body.amspec-download-body .amspec-terms ol {
        padding-left: 18px;
        margin: 0;
    }

    body.amspec-download-body .amspec-terms li {
        margin-bottom: 5px;
        line-height: 1.35;
    }

    body.amspec-download-body .amspec-terms-link {
        font-size: 10pt;
    }

    body.amspec-download-body .amspec-header-block,
    body.amspec-download-body .amspec-header-block td {
        border: none !important;
        outline: none !important;
    }

    body.amspec-download-body .amspec-header-contact-lines {
        display: inline-block;
        text-align: left;
        min-width: 180px;
    }

    body.amspec-download-body .amspec-signature-closing {
        margin-top: 8px;
        width: 100%;
        font-size: 9.5pt;
    }

    body.amspec-download-body .amspec-signature-closing p {
        margin: 0;
    }

    body.amspec-download-body .amspec-signature-intro {
        margin-top: 10px;
        margin-bottom: 16px;
        width: 50%;
        padding-right: 12px;
        font-size: 9.5pt;
    }

    body.amspec-download-body .amspec-signature-intro p {
        margin: 0 0 4px 0;
    }

    body.amspec-download-body .amspec-signature-row {
        display: table;
        width: 100%;
        table-layout: fixed;
        margin-top: 10px;
        margin-bottom: 40px;
    }

    body.amspec-download-body .amspec-signature {
        margin-top: 0;
        font-size: 9.5pt;
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-right: 12px;
    }

    body.amspec-download-body .amspec-signature-customer {
        padding-right: 0;
        padding-left: 12px;
        border-left: 1px solid #d0d0d0;
    }

    body.amspec-download-body .amspec-signature p {
        margin: 0 0 4px 0;
    }

    body.amspec-download-body .amspec-signature-entity {
        margin-top: 0;
        font-size: 9.5pt;
    }

    body.amspec-download-body .amspec-signature-lab .amspec-signature-entity {
        margin-top: 0;
        padding-top: 4px;
    }

    body.amspec-download-body .amspec-signature .amspec-sig-space {
        margin-top: 18px;
    }

    body.amspec-download-body .amspec-footer-wrap {
        position: fixed;
        bottom: -25mm;
        left: 0;
        right: 0;
        width: auto;
        height: auto;
        min-height: 0;
        page-break-inside: avoid;
        margin: 0;
        padding: 0 1.5mm 3mm 1.5mm;
        box-sizing: border-box;
        z-index: 2;
        background: #ffffff;
    }

    body.amspec-download-body .amspec-page-one .amspec-page-body,
    body.amspec-download-body .amspec-page-two .amspec-page-body {
        padding-bottom: 0;
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

    body.amspec-download-body .amspec-footer-qr {
        width: 52px;
        height: 52px;
        border: none;
        display: inline-block;
    }

    body.amspec-download-body .amspec-footer-disclaimer {
        font-size: 6pt;
        line-height: 1.25;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin: 0;
        text-align: left;
        font-family: 'DejaVu Sans', sans-serif;
    }

    body.amspec-download-body .amspec-footer-disclaimer a {
        color: {{ $primaryColor }};
        word-break: break-all;
    }

    body.amspec-download-body .amspec-page-sheet {
        position: relative;
        overflow: visible;
    }

    body.amspec-download-body .amspec-page-body,
    body.amspec-download-body .amspec-page-logo {
        position: relative;
        z-index: 1;
    }

    body.amspec-download-body .amspec-brand-row {
        width: 100%;
        margin-bottom: 2px;
        border: none !important;
        border-collapse: collapse;
    }

    body.amspec-download-body .amspec-brand-row td {
        border: none !important;
        outline: none !important;
    }

    body.amspec-download-body .amspec-brand-left {
        width: 58%;
        vertical-align: middle;
        border: none !important;
        padding-right: 12px !important;
    }

    body.amspec-download-body .amspec-brand-right {
        width: 42%;
        text-align: right;
        vertical-align: top;
        border: none !important;
    }

    body.amspec-download-body .amspec-test-table tfoot {
        page-break-inside: avoid;
    }

    body.amspec-download-body .amspec-test-table tbody tr {
        page-break-inside: avoid;
    }

    body.amspec-download-body .amspec-test-table {
        margin-bottom: 0 !important;
    }
</style>
