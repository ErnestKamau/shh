<style>
    :root {
        --quotation-primary: {{ $branding['primary'] ?? '#6D0A0E' }};
        --quotation-accent: {{ $branding['accent'] ?? '#4CAF50' }};
        --quotation-category-bg: #E0E0E0;
        --amspec-text: #000000;
        --amspec-muted: #333333;
        /* AmSpec Quotation Format-1.pdf: Calibri body, Lucida Bright titles, Arial table headers, Aptos Narrow footer */
        --amspec-font-body: Calibri, Carlito, 'Segoe UI', sans-serif;
        --amspec-font-heading: 'Lucida Bright', 'Lucida Fax', 'Lucida Sans', Georgia, serif;
        --amspec-font-table: Arial, Helvetica, sans-serif;
        --amspec-font-footer: 'Aptos Narrow', 'Roboto Condensed', Calibri, Arial, sans-serif;
    }

    @page {
        margin: 0;
        size: A4 portrait;
    }

    .amspec-preview-body {
        margin: 0;
        background: #d8d8d8;
        padding: 16px 4px;
    }

    .amspec-document-shell {
        margin: 0 auto;
        background: transparent;
        width: 100%;
    }

    .amspec-shell-embedded {
        max-width: min(98vw, 260mm);
    }

    .amspec-shell-preview,
    .amspec-shell-public {
        max-width: min(99vw, 320mm);
    }

    .preview-toolbar {
        max-width: min(99vw, 320mm);
        margin: 0 auto 12px;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .amspec-btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-family: var(--amspec-font-body);
        font-size: 11px;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .amspec-btn-primary {
        background: var(--quotation-primary);
        color: #fff;
    }

    .amspec-btn-secondary {
        background: #fff;
        color: #333;
        border-color: #ccc;
    }

    .amspec-quotation {
        font-family: var(--amspec-font-body);
        font-size: 10pt;
        color: var(--amspec-text);
        line-height: 1.35;
        position: relative;
    }

    .amspec-quotation table {
        width: 100%;
        border-collapse: collapse;
    }

    .amspec-watermark {
        position: fixed;
        top: 35%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.06;
        width: 280px;
        z-index: 0;
        pointer-events: none;
    }

    .amspec-page-sheet {
        position: relative;
        z-index: 1;
        width: 220mm;
        min-height: auto;
        margin: 0 auto 20px;
        padding: 14mm 14mm 12mm;
        background: #ffffff;
        box-sizing: border-box;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.14);
        display: block;
    }

    .amspec-shell-preview .amspec-page-sheet,
    .amspec-shell-public .amspec-page-sheet {
        width: min(94vw, 268mm);
        padding: 15mm 16mm 14mm;
    }

    .amspec-shell-preview .amspec-quotation,
    .amspec-shell-public .amspec-quotation {
        font-size: 10.5pt;
    }

    .amspec-page-body {
        display: block;
    }

    .amspec-page-logo {
        margin-bottom: 10px;
    }

    .amspec-brand-row {
        margin-bottom: 4px;
    }

    .amspec-brand-left {
        width: 55%;
        vertical-align: middle;
    }

    .amspec-brand-right {
        width: 45%;
        text-align: right;
        vertical-align: top;
    }

    .amspec-logo-wordmark {
        max-height: 48px;
        max-width: 200px;
    }

    .amspec-logo-wordmark-svg {
        height: 42px;
        width: auto;
        max-width: 200px;
    }

    .amspec-hex-cluster {
        width: 110px;
        height: auto;
        display: inline-block;
    }

    .amspec-page-two:not(.amspec-pdf-page) {
        page-break-before: always;
        break-before: page;
    }

    .amspec-company-name {
        font-family: var(--amspec-font-heading);
        font-weight: 700;
        font-size: 11pt;
        color: var(--amspec-text);
    }

    .amspec-logo {
        max-height: 55px;
        max-width: 180px;
    }

    .amspec-contact-line {
        font-family: var(--amspec-font-body);
        font-size: 10pt;
    }

    .amspec-company-address {
        font-weight: 700;
    }

    .amspec-meta-label {
        width: 130px;
        font-family: var(--amspec-font-body);
        font-weight: 700;
        font-size: 10pt;
        white-space: nowrap;
    }

    .amspec-meta-value {
        font-family: var(--amspec-font-body);
        font-size: 10pt;
        font-weight: 700;
        padding-left: 4px;
    }

    .amspec-meta-value strong {
        font-weight: 700;
    }

    .amspec-intro {
        margin-top: 14px;
        font-size: 10pt;
    }

    #quotation-document .amspec-th-primary,
    .amspec-document-shell .amspec-th-primary,
    .amspec-test-table .amspec-th-primary {
        font-family: var(--amspec-font-table) !important;
        font-weight: 700 !important;
        padding: 6px 4px !important;
        border: 1px solid #999999 !important;
        text-align: center !important;
        font-size: 9pt !important;
        vertical-align: middle !important;
        background-color: {{ $branding['primary'] ?? '#6D0A0E' }} !important;
        background: {{ $branding['primary'] ?? '#6D0A0E' }} !important;
        color: #ffffff !important;
    }

    #quotation-document .amspec-th-accent,
    .amspec-document-shell .amspec-th-accent,
    .amspec-test-table .amspec-th-accent {
        font-family: var(--amspec-font-table) !important;
        font-weight: 700 !important;
        padding: 6px 4px !important;
        border: 1px solid #999999 !important;
        text-align: center !important;
        font-size: 9pt !important;
        vertical-align: middle !important;
        background-color: {{ $branding['accent'] ?? '#4CAF50' }} !important;
        background: {{ $branding['accent'] ?? '#4CAF50' }} !important;
        color: #ffffff !important;
    }

    .amspec-th-sub {
        font-weight: 400;
        font-size: 8pt;
        display: block;
        margin-top: 2px;
    }

    .amspec-category-cell {
        background: var(--quotation-category-bg);
        padding: 5px 6px;
        border: 1px solid #bbbbbb;
        font-weight: 700;
        font-size: 9.5pt;
    }

    .amspec-test-row td {
        padding: 5px 6px;
        border: 1px solid #cccccc;
        vertical-align: top;
        font-size: 9.5pt;
        font-family: var(--amspec-font-body);
    }

    .amspec-totals td {
        padding: 5px 6px;
        border: 1px solid #cccccc;
        font-size: 9.5pt;
        font-family: var(--amspec-font-body);
    }

    .amspec-terms {
        margin-top: 4px;
        font-size: 10pt;
    }

    .amspec-terms-title {
        font-family: var(--amspec-font-table);
        font-weight: 700;
        margin-bottom: 8px;
    }

    .amspec-terms ol {
        padding-left: 18px;
        margin: 0;
    }

    .amspec-terms li {
        margin-bottom: 6px;
    }

    .amspec-signature {
        margin-top: 18px;
        font-size: 10pt;
    }

    .amspec-signature-entity {
        margin-top: 24px;
        font-family: var(--amspec-font-table);
        font-weight: 700;
        font-size: 10pt;
    }

    .amspec-footer-wrap {
        margin-top: 16px;
        padding-top: 0;
        width: 100%;
    }

    .amspec-footer {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .amspec-footer-text {
        width: 86%;
        vertical-align: middle;
        padding-right: 12px;
    }

    .amspec-footer-disclaimer {
        margin: 0;
        font-family: var(--amspec-font-footer);
        font-size: 7.5pt;
        line-height: 1.35;
        color: var(--amspec-text);
        text-align: left;
    }

    .amspec-footer-disclaimer a {
        color: {{ $branding['primary'] ?? '#6D0A0E' }};
        text-decoration: none;
        font-weight: 400;
    }

    .amspec-footer-qr-cell {
        width: 14%;
        vertical-align: middle;
        text-align: right;
        padding: 0;
    }

    .amspec-footer-qr {
        width: 72px;
        height: 72px;
        display: inline-block;
        border: 4px solid #ffffff;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    #quotation-document {
        overflow: visible;
        background: #ececec;
        padding: 16px 8px;
        border-radius: 4px;
    }

    #quotation-document .amspec-document-shell {
        background: transparent;
    }

    @media print {
        .amspec-preview-body,
        .amspec-download-body,
        #quotation-document {
            background: #fff;
            padding: 0;
        }

        .preview-toolbar {
            display: none !important;
        }

        .amspec-page-sheet {
            width: 210mm;
            box-shadow: none;
            margin-bottom: 0;
            min-height: auto;
            page-break-after: avoid;
        }

        .amspec-page-two {
            page-break-before: always;
        }

        .amspec-footer-wrap {
            margin-top: 14px;
            padding-top: 0;
        }
    }
</style>
