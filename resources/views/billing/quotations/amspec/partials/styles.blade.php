<style>
    :root {
        --quotation-primary: {{ $branding['primary'] ?? 'var(--color-primary)' }};
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

    @if(!($forPdf ?? false))
    @page {
        margin: 0;
        size: A4 portrait;
    }
    @endif

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
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        opacity: 0.07;
        z-index: 0;
        pointer-events: none;
    }

    .amspec-page-sheet {
        position: relative;
        z-index: 1;
        width: 220mm;
        min-height: auto;
        margin: 0 auto 20px;
        padding: 16mm 16mm 14mm;
        background: #ffffff;
        box-sizing: border-box;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.14);
        display: block;
        overflow: hidden;
    }

    .amspec-page-body,
    .amspec-footer-wrap,
    .amspec-page-logo {
        position: relative;
        z-index: 1;
    }

    .amspec-shell-preview .amspec-page-sheet,
    .amspec-shell-public .amspec-page-sheet {
        width: min(94vw, 268mm);
        padding: 16mm 16mm 14mm;
    }

    .amspec-shell-preview .amspec-quotation,
    .amspec-shell-public .amspec-quotation {
        font-size: 10.5pt;
    }

    .amspec-page-body {
        display: block;
    }

    .amspec-page-logo {
        margin-bottom: 6px;
    }

    .amspec-brand-row {
        margin-bottom: 2px;
        border: none !important;
        border-collapse: collapse;
    }

    .amspec-brand-row td {
        border: none !important;
        outline: none !important;
    }

    .amspec-brand-left {
        width: 55%;
        vertical-align: middle;
        padding-top: 0;
        border: none !important;
    }

    .amspec-brand-right {
        width: 45%;
        text-align: right;
        vertical-align: top;
        padding-top: 0;
        border: none !important;
    }

    .amspec-logo-wordmark {
        max-height: 62px;
        max-width: 240px;
        height: auto;
        width: auto;
        display: block;
        border: none !important;
    }

    .amspec-logo-wordmark-svg {
        height: 58px;
        width: auto;
        max-width: 240px;
        display: block;
    }

    .amspec-hex-cluster {
        width: 100px;
        max-width: 100px;
        height: auto;
        display: inline-block;
        margin: 0;
        padding: 0;
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .amspec-header-hex {
        margin: 0 0 6px 0;
        padding: 0;
        border: none !important;
        line-height: 0;
    }

    .amspec-header-top,
    .amspec-header-top td {
        border: none !important;
        outline: none !important;
    }

    .amspec-company-name {
        font-family: var(--amspec-font-heading);
        font-weight: 700;
        font-size: 11pt;
        color: var(--amspec-text);
    }

    .amspec-logo {
        max-height: 62px;
        max-width: 240px;
    }

    .amspec-contact-line {
        font-family: var(--amspec-font-body);
        font-size: 10pt;
    }

    .amspec-contact-line div {
        margin: 0 0 1px 0;
        padding: 0;
        line-height: 1.15;
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
        background-color: {{ $branding['primary'] ?? 'var(--color-primary)' }} !important;
        background: {{ $branding['primary'] ?? 'var(--color-primary)' }} !important;
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
        font-weight: 600;
        font-size: 7.5pt;
        display: block;
        margin-top: 1px;
        line-height: 1.15;
    }

    .amspec-test-table .amspec-num-cell {
        padding-left: 3px !important;
        padding-right: 3px !important;
        white-space: nowrap;
    }

    .amspec-test-table .amspec-price-cell {
        padding-left: 4px !important;
        padding-right: 5px !important;
        white-space: nowrap;
    }

    .amspec-test-table .amspec-params-cell {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .amspec-category-cell {
        background: var(--quotation-category-bg);
        padding: 8px 8px;
        border: 1px solid #bbbbbb;
        font-weight: 700;
        font-size: 10.5pt;
        color: #1f2937;
        letter-spacing: 0.01em;
    }

    .amspec-sample-description {
        display: inline-block;
        font-family: var(--amspec-font-table);
        font-weight: 700;
        font-size: 11pt;
        line-height: 1.3;
        color: #111827;
    }

    .amspec-package-cell .amspec-test-name {
        display: block;
        margin-bottom: 3px;
    }

    .amspec-package-parameters {
        color: #334155;
        font-weight: 400;
        font-size: 0.95em;
        line-height: 1.45;
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

    .amspec-signature-closing {
        margin-top: 18px;
        width: 100%;
        font-size: 10pt;
    }

    .amspec-signature-closing p {
        margin: 0;
    }

    .amspec-signature-intro {
        margin-top: 16px;
        margin-bottom: 18px;
        width: 50%;
        padding-right: 16px;
        font-size: 10pt;
    }

    .amspec-signature-intro p {
        margin: 0;
    }

    .amspec-signature-row {
        display: table;
        width: 100%;
        table-layout: fixed;
        margin-top: 8px;
        margin-bottom: 40px;
    }

    .amspec-signature {
        font-size: 10pt;
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-right: 16px;
    }

    .amspec-signature-customer {
        padding-right: 0;
        padding-left: 16px;
        border-left: 1px solid #d0d0d0;
    }

    .amspec-signature-entity {
        margin-top: 0;
        font-family: var(--amspec-font-table);
        font-weight: 700;
        font-size: 10pt;
    }

    .amspec-signature-lab .amspec-signature-entity {
        margin-top: 0;
    }

    .amspec-footer-wrap {
        margin-top: 56px;
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
        font-size: 6.25pt;
        line-height: 1.28;
        color: var(--amspec-text);
        text-align: left;
    }

    .amspec-footer-disclaimer a {
        color: {{ $branding['primary'] ?? 'var(--color-primary)' }};
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
        width: 52px;
        height: 52px;
        display: inline-block;
    }

    .amspec-category-banner {
        font-family: var(--amspec-font-table);
        font-weight: 700;
        font-size: 10pt;
        background-color: var(--quotation-category-bg);
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

    @if(!($forPdf ?? false))
    @media print {
        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        html,
        body,
        .amspec-quotation,
        .amspec-quotation * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff !important;
        }

        .amspec-preview-body,
        .amspec-download-body,
        #quotation-document {
            background: #fff !important;
            padding: 0 !important;
        }

        .preview-toolbar {
            display: none !important;
        }

        .amspec-watermark {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            opacity: 0.07 !important;
            z-index: 0 !important;
            pointer-events: none !important;
            display: block !important;
        }

        .amspec-document-shell {
            max-width: none !important;
            width: 100% !important;
        }

        .amspec-page-sheet {
            width: 100% !important;
            max-width: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
            page-break-inside: auto;
            break-inside: auto;
        }

        .amspec-page-two {
            page-break-before: always;
            break-before: page;
        }

        .amspec-shell-preview .amspec-page-sheet,
        .amspec-shell-public .amspec-page-sheet {
            width: 100% !important;
            padding: 0 !important;
        }

        .amspec-footer-wrap {
            margin-top: 56px;
            padding-top: 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .amspec-th-primary,
        .amspec-test-table .amspec-th-primary {
            background-color: {{ $branding['primary'] ?? 'var(--color-primary)' }} !important;
            background: {{ $branding['primary'] ?? 'var(--color-primary)' }} !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .amspec-th-accent,
        .amspec-test-table .amspec-th-accent {
            background-color: {{ $branding['accent'] ?? '#4CAF50' }} !important;
            background: {{ $branding['accent'] ?? '#4CAF50' }} !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .amspec-category-cell,
        .amspec-category-banner {
            background-color: #E0E0E0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .amspec-footer-disclaimer a {
            color: {{ $branding['primary'] ?? 'var(--color-primary)' }} !important;
        }
    }
    @endif
</style>
