@php
    $maroon = $branding['primary'] ?? '#800000';
    $orientation = in_array(($orientation ?? ''), ['landscape', 'portrait'], true)
        ? $orientation
        : 'landscape';
    $isPortrait = $orientation === 'portrait';
    $isWasteWater = ($variant ?? '') === 'waste_water';
    $pageMargin = $isWasteWater
        ? ($isPortrait ? '4mm 4mm' : '3mm 3mm')
        : ($isPortrait ? '6mm 7mm' : '3mm 4mm');
@endphp
<style>
    @page { size: A4 {{ $orientation }}; margin: {{ $pageMargin }}; }
    * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
    body { font-size: 8pt; color: #000; margin: 0; padding: 0; line-height: 1.2; }
    body.trf-layout-centered { margin: 0; padding: 0; }
    
    @if(empty($forPdf) || !$forPdf)
    body.trf-layout-centered {
        background: #e9ecef;
        padding: 30px 15px;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
    }
    .trf-page {
        background: #ffffff;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.12);
        border-radius: 6px;
        padding: {{ $isPortrait ? '14mm 12mm' : '12mm 16mm' }};
        margin: 0 auto;
        box-sizing: border-box;
    }
    body.trf-layout-centered.trf-orientation-landscape .trf-page {
        width: min(95vw, 297mm);
        min-height: 210mm;
    }
    body.trf-layout-centered.trf-orientation-portrait .trf-page {
        width: min(95vw, 210mm);
        min-height: 297mm;
    }
    @else
    .trf-page { width: 100%; margin: 0; }
    @endif

    /* Portrait: more breathing room between major blocks */
    body.trf-orientation-portrait .trf-header-table {
        margin-bottom: 8px;
    }
    body.trf-orientation-portrait .trf-section-title {
        margin-top: 8px;
        margin-bottom: 4px;
        padding: 7px 10px;
    }
    body.trf-orientation-portrait .trf-table {
        margin-bottom: 10px;
    }
    body.trf-orientation-portrait .trf-table td,
    body.trf-orientation-portrait .trf-table th {
        padding: 5px 6px;
        font-size: 7.5pt;
        line-height: 1.3;
    }
    body.trf-orientation-portrait .trf-footer-table {
        margin-top: 10px;
        padding-top: 4px;
    }
    body.trf-orientation-portrait .trf-footer-cell,
    body.trf-orientation-portrait .trf-table tr.trf-footer-sign-row > td.trf-footer-cell {
        padding: 5px 8px !important;
        min-height: 26px !important;
        line-height: 1.4 !important;
    }
    body.trf-orientation-portrait .trf-footer-sign-row td {
        padding-top: 3px;
        padding-bottom: 3px;
    }

    /* Landscape: compact AMSPEC-style density */
    body.trf-orientation-landscape .trf-header-table {
        margin-bottom: 2px;
    }
    body.trf-orientation-landscape .trf-table {
        margin-bottom: 0;
    }

    .trf-section-title {
        background: {{ $maroon }};
        color: #fff;
        font-weight: bold;
        font-size: 8pt;
        padding: 8px 10px;
        text-transform: uppercase;
        border: 1px solid #000;
        text-align: center;
    }
    .trf-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
    .trf-table td, .trf-table th {
        border: 1px solid #000;
        padding: 4px 5px;
        vertical-align: middle;
        font-size: 7pt;
        word-wrap: break-word;
    }
    .trf-table th {
        background: #d9d9d9;
        font-weight: bold;
        text-align: center;
        font-size: 6.5pt;
        vertical-align: middle;
    }
    .trf-header-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; table-layout: fixed; }
    .trf-header-table > tbody > tr > td { border: none; vertical-align: middle; padding: 1px 3px; }
    .trf-header-left { width: 86%; text-align: left; vertical-align: middle; padding: 6px 0; }
    .trf-header-serial { width: 14%; text-align: right; vertical-align: middle; padding: 6px 6px 6px 0; }
    .trf-header-company-grid { width: 100%; max-width: 100%; border-collapse: collapse; margin: 0 auto; table-layout: auto; }
    .trf-header-company-grid td {
        border: none;
        padding: 0px 4px;
        font-size: 7pt;
        text-align: left;
        vertical-align: top;
        line-height: 1.15;
        white-space: nowrap;
    }
    .trf-company-col-logo { width: 10%; }
    .trf-company-logo-cell { width: 10%; vertical-align: middle; text-align: left; padding-right: 15px !important; }
    .trf-company-col-left { width: 50%; padding-right: 20px; text-align: left; }
    .trf-company-col-right { width: 40%; padding-left: 20px; text-align: left; }
    .trf-company-address-cell { width: 100%; padding: 2px 4px; text-align: left; white-space: nowrap; font-size: 6.8pt; }
    .trf-company-address-cell .trf-field-label,
    .trf-company-address-cell .trf-field-value { font-size: 6.8pt; }
    .trf-company-name-cell { font-weight: bold; font-size: 10pt; padding-bottom: 2px; }
    .trf-logo { max-height: 48px; max-width: 100px; margin-right: 15px; }
    .trf-title-row td { border: none; padding: 0 0 3px; }
    .trf-title {
        font-size: 11pt;
        font-weight: bold;
        text-align: center;
        color: #000;
        text-transform: uppercase;
    }
    .trf-serial { text-align: right; font-size: 12pt; font-weight: bold; white-space: nowrap; color: {{ $maroon }}; display: block; line-height: 1.1; }
    .trf-company-center { text-align: center; }
    .trf-company-name { font-weight: bold; font-size: 8.5pt; color: #000; line-height: 1.2; }
    .trf-company-meta { font-size: 7pt; line-height: 1.2; color: #000; font-weight: normal; }
    .trf-company-side { font-size: 7pt; line-height: 1.2; text-align: right; }
    .trf-field-label { font-weight: bold; font-size: 7pt; color: #000; }
    .trf-field-value { font-size: 7pt; font-weight: normal; color: #000; }
    .trf-job-label { font-weight: bold; font-size: 7pt; color: #000; }
    .trf-check { font-family: DejaVu Sans, sans-serif; font-size: 7pt; display: inline; line-height: 1; }
    .trf-check-on::before { content: "\2611"; color: {{ $maroon }}; display: inline; }
    .trf-check-off::before { content: "\2610"; color: #000; display: inline; }
    .trf-tick { font-weight: bold; font-size: 8pt; color: {{ $maroon }}; }
    .trf-mark { font-weight: bold; font-size: 8pt; color: #000; line-height: 1; }
    .trf-check-only { display: inline-block; text-align: center; width: 100%; }
    .trf-small { font-size: 5.5pt; color: #333; }
    .trf-footer-table { width: 100%; border-collapse: collapse; margin-top: 3px; border-top: 1px solid #999; }
    .trf-footer-table td { border: none; font-size: 7pt; color: #333; padding-top: 2px; }
    .trf-center { text-align: center; vertical-align: middle; }
    .trf-right { text-align: right; }
    .trf-meta-key { font-weight: bold; display: block; font-size: 7pt; }
    .trf-meta-val { display: inline; font-size: 7pt; line-height: 1.2; word-wrap: break-word; }
    .trf-meta-cell { vertical-align: middle; padding: 8px 10px; min-height: 32px; border: 1px solid #000; word-wrap: break-word; line-height: 1.2; background: #fff; }
    .trf-collection-section-header { background: #d9d9d9 !important; }
    .trf-collection-check-cell { vertical-align: top; padding: 0; border: 1px solid #000; background: #fff; }
    .trf-dotted-leader { color: {{ $maroon }}; letter-spacing: 0.5px; }
    .trf-tick-col-header { background: #d9d9d9 !important; }
    .trf-tick-cell { background: #fff; vertical-align: middle; text-align: center; border: 1px solid #000; min-height: 18px; height: 18px; }
    .trf-water-table .trf-sample-header-row th,
    .trf-water-table .trf-sample-subheader-row th,
    .trf-water-table .trf-data-row td {
        border: 1px solid #000;
        box-sizing: border-box;
    }
    .trf-water-table .trf-data-row td {
        overflow: visible;
        min-height: 30px;
        height: 30px;
    }
    .trf-state-cell { font-size: 5pt; text-align: center; vertical-align: middle; padding: 0; overflow: hidden; background: #fff; }
    .trf-check-grid { width: 100%; border-collapse: collapse; margin: 0; }
    .trf-check-grid td { border: none; padding: 1px 3px 1px 0; font-size: 6pt; vertical-align: middle; line-height: 1.2; white-space: nowrap; }
    .trf-check-grid-split td {
        border: none;
        padding: 2px 4px;
        font-size: 6pt;
        vertical-align: middle;
        line-height: 1.25;
        white-space: nowrap;
        background: #fff;
        min-height: 17px;
    }
    .trf-check-grid-split.trf-check-grid-2 td:first-child { border-right: 1px solid #000; width: 50%; }
    .trf-check-grid-split.trf-check-grid-2 td:last-child { width: 50%; }
    .trf-check-grid-split.trf-check-grid-1 td { width: 100%; }
    .trf-check-grid-bordered {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        table-layout: fixed;
    }
    .trf-check-grid-bordered td {
        border: 1px solid #000;
        padding: 4px 5px;
        font-size: 6pt;
        vertical-align: middle;
        min-height: 20px;
        height: 20px;
        background: #fff;
        white-space: normal;
        word-break: normal;
    }
    .trf-check-grid-bordered.trf-check-grid-2 td { width: 50%; }
    .trf-check-grid-bordered.trf-check-grid-1 td { width: 100%; }
    .trf-check-grid-bordered tr td:first-child { border-left: none; }
    .trf-check-grid-bordered tr td:last-child { border-right: none; }
    .trf-check-grid-bordered tr:first-child td { border-top: none; }
    .trf-check-grid-bordered tr:last-child td { border-bottom: none; }
    .trf-check-grid-bordered.trf-check-grid-2 td {
        width: 50%;
        min-height: 17px;
        height: 17px;
        vertical-align: top;
    }
    .trf-check-grid-empty-cell {
        min-height: 17px;
        height: 17px;
    }
    .trf-check-grid-empty-placeholder {
        visibility: hidden;
    }
    .trf-check-grid-bordered tr.trf-check-grid-incomplete-row td {
        height: 17px;
        min-height: 17px;
        vertical-align: top;
    }
    .trf-check-grid-subheader-cell {
        background: #d9d9d9;
        font-weight: bold;
        font-size: 6.5pt;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        padding: 2px 4px;
    }
    .trf-check-grid-1 td { width: 100%; }
    .trf-check-grid-2 td { width: 50%; }
    .trf-subheader { background: #d9d9d9; font-size: 6.5pt; text-align: center; font-weight: bold; vertical-align: middle; padding: 1px; }
    .trf-vtext-narrow .trf-vtext-br { font-size: 5pt; line-height: 1; }
    .trf-banner-row td {
        background: {{ $maroon }};
        color: #fff;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 9pt;
        text-align: center;
        padding: 8px 10px;
    }
    .trf-banner-row td.trf-job-cell,
    .trf-banner-row td.trf-banner-cell + .trf-job-cell {
        background: #fff;
        color: #000;
        text-align: left;
        text-transform: none;
        font-weight: normal;
    }
    .trf-banner-cell {
        background: {{ $maroon }} !important;
        color: #fff !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        text-align: center !important;
    }
    .trf-no-gap { margin: 0; padding: 0; }
    .trf-vtext-box { display: table; width: 100%; height: 44px; margin: 0 auto; }
    .trf-vtext-box-inner { display: table-cell; vertical-align: middle; text-align: center; height: 44px; padding: 0; }
    .trf-vtext {
        display: inline-block;
        transform: rotate(-90deg);
        -webkit-transform: rotate(-90deg);
        white-space: nowrap;
        font-size: 5.5pt;
        line-height: 1;
        font-weight: bold;
    }
    .trf-vtext-br {
        display: inline-block;
        font-size: 5.5pt;
        line-height: 1.05;
        text-align: center;
        font-weight: bold;
    }
    .trf-vtext-wrap { text-align: center; vertical-align: middle; padding: 2px 1px; height: 50px; overflow: visible; }
    .trf-vtext-wrap.trf-col-params { height: auto; min-height: 52px; padding: 3px 2px; }
    .trf-col-params .trf-vtext-br { font-size: 6pt; line-height: 1.1; }
    .trf-vtext-col { width: 2.3%; }
    .trf-vtext-col-wide { width: 5.5%; }
    .trf-col-date-cell { font-size: 5.5pt; padding: 0 1px; text-align: center; vertical-align: middle; white-space: nowrap; overflow: hidden; line-height: 1.1; }
    .trf-col-batch-cell { font-size: 5.5pt; padding: 0 1px; text-align: center; vertical-align: middle; word-wrap: break-word; overflow: hidden; line-height: 1.1; }
    .trf-col-params-cell { font-size: 6.5pt; padding: 1px 2px; text-align: center; vertical-align: middle; word-wrap: break-word; overflow: hidden; line-height: 1.15; }
    .trf-col-temp-cell { font-size: 5.5pt; padding: 0 1px; text-align: center; vertical-align: middle; white-space: nowrap; overflow: hidden; }
    .trf-state-grid { width: 100%; max-width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; }
    .trf-state-grid td { border: none !important; padding: 0 !important; margin: 0; vertical-align: middle; }
    .trf-state-grid-item { width: 33%; text-align: center; font-size: 5pt; line-height: 1; white-space: nowrap; overflow: hidden; }
    .trf-state-label { font-size: 5pt; line-height: 1; }
    .trf-check-mini { font-size: 5pt !important; line-height: 1 !important; display: inline !important; letter-spacing: 0; }
    .trf-check-mini::before { font-size: 5pt; line-height: 1; }
    .trf-state-header { text-align: center; vertical-align: middle; padding: 1px 2px; font-size: 5pt; line-height: 1; font-weight: bold; overflow: hidden; background: #d9d9d9 !important; }
    .trf-state-header .trf-vtext-br { font-size: 5pt; line-height: 1; font-weight: bold; }
    .trf-food-table col.trf-col-serial { width: 2.5%; }
    .trf-food-table col.trf-col-sample-no { width: 5%; }
    .trf-food-table col.trf-col-desc { width: 11%; }
    .trf-food-table col.trf-col-location { width: 7%; }
    .trf-food-table col.trf-col-qty { width: 3.5%; }
    .trf-food-table col.trf-col-tick { width: 2%; }
    .trf-food-table col.trf-col-temp { width: 3.5%; }
    .trf-food-table col.trf-col-date { width: 5%; }
    .trf-food-table col.trf-col-batch { width: 5%; }
    .trf-food-table col.trf-col-sample-type { width: 14%; }
    .trf-food-table col.trf-col-params { width: 4%; }
    .trf-food-table col.trf-col-state { width: 9%; }
    .trf-food-table .trf-data-row td {
        height: auto;
        min-height: 28px;
        overflow: visible;
        vertical-align: top;
    }
    .trf-food-table .trf-data-row {
        page-break-inside: auto;
    }
    .trf-food-table .trf-col-sample-type-cell {
        font-size: 5.5pt;
        padding: 2px 3px;
        text-align: left;
        vertical-align: top;
        word-wrap: break-word;
        line-height: 1.1;
        page-break-inside: auto;
    }
    .trf-food-table .trf-text-cell {
        vertical-align: top;
        overflow: visible;
    }
    .trf-food-table .trf-state-cell {
        overflow: visible;
        vertical-align: middle;
        padding: 2px 1px;
    }
    .trf-food-table .trf-state-header {
        overflow: visible;
        font-size: 4.5pt;
        line-height: 1.05;
        padding: 2px 2px;
    }
    .trf-food-table .trf-state-cell .trf-state-label {
        font-size: 5pt;
    }
    .trf-food-table .trf-tick-cell {
        overflow: visible;
        vertical-align: middle;
        text-align: center;
    }
    .trf-water-table col.trf-col-serial { width: 3.5%; }
    .trf-water-table col.trf-col-sample-no { width: 6%; }
    .trf-water-table col.trf-col-desc { width: 20%; }
    .trf-water-table col.trf-col-location { width: 12%; }
    .trf-water-table col.trf-col-qty { width: 4%; }
    .trf-water-table col.trf-col-tick { width: 2.4%; }
    .trf-water-table col.trf-col-field { width: 5.5%; }
    .trf-water-table col.trf-col-test { width: 5%; }
    body.trf-water .trf-page {
        page-break-inside: avoid;
        width: 100%;
    }
    body.trf-water .trf-water-table tr.trf-water-collection-block,
    body.trf-water .trf-water-table tr.trf-banner-row {
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    body.trf-water .trf-water-table .trf-collection-check-cell {
        padding: 0 !important;
        vertical-align: top;
    }
    body.trf-water .trf-water-table .trf-meta-cell {
        padding: 5px 6px;
        min-height: 0 !important;
        vertical-align: top;
        line-height: 1.2;
    }
    body.trf-water .trf-check-grid-bordered.trf-check-grid-compact td,
    body.trf-water .trf-check-grid-bordered td {
        min-height: 12px;
        height: auto;
        padding: 2px 3px;
        font-size: 5.5pt;
        line-height: 1.1;
        vertical-align: top;
    }
    body.trf-water .trf-water-collection-block .trf-collection-check-cell .trf-check-grid-bordered.trf-check-grid-water-collection td {
        min-height: 14px;
        height: auto;
        padding: 3px 4px;
        font-size: 7.5pt;
        line-height: 1.15;
        vertical-align: top;
    }
    body.trf-water .trf-water-collection-block .trf-collection-check-cell .trf-check {
        font-size: 9pt;
    }
    body.trf-water .trf-water-collection-block .trf-collection-check-cell .trf-check-on::before,
    body.trf-water .trf-water-collection-block .trf-collection-check-cell .trf-check-off::before {
        color: #000;
    }
    .trf-customer-table .trf-banner-row td { border-right: 1px solid #000; }
    .trf-customer-field { vertical-align: middle; padding: 8px 10px; min-height: 32px; word-wrap: break-word; line-height: 1.2; }
    .trf-customer-field .trf-field-value { word-wrap: break-word; }
    .trf-text-cell { font-size: 6.5pt; padding: 6px 6px; vertical-align: middle; word-wrap: break-word; line-height: 1.2; }
    .trf-text-cell p { margin: 0; padding: 0; display: inline; }
    .trf-job-cell {
        vertical-align: top;
        padding: 10px 12px;
        border: 1px solid #000;
        min-height: 80px;
    }
    .trf-job-number-label {
        display: block;
        font-weight: bold;
        font-size: 8pt;
        color: {{ $maroon }};
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .trf-job-number-value { display: block; font-size: 7pt; font-weight: normal; min-height: 50px; }
    .trf-customer-left { width: 68%; vertical-align: middle; padding: 6px 8px; }
    .trf-customer-right { width: 32%; vertical-align: middle; padding: 6px 8px; }
    .trf-data-row td { min-height: 28px; height: 28px; vertical-align: middle; font-weight: normal; font-size: 6.5pt; overflow: hidden; background: #fff; }
    .trf-lab-box {
        padding: 8px 10px;
        vertical-align: top;
    }
    .trf-lab-box .trf-lab-field { margin-bottom: 4px; }
    .trf-lab-box .trf-field-label { display: inline; margin-top: 0; }
    .trf-lab-box .trf-field-value { display: inline; }
    .trf-footer-sign-row td { vertical-align: middle; padding: 0; }
    .trf-footer-cell,
    .trf-table tr.trf-footer-sign-row > td.trf-footer-cell {
        padding: 5px 10px;
        font-size: 7pt;
        line-height: 1.4;
        vertical-align: middle;
        min-height: 30px;
    }
    .trf-footer-lab-head { vertical-align: middle; }
    .trf-lab-title { font-weight: bold; font-size: 7pt; text-transform: uppercase; margin: 0; padding: 0; }
    .trf-footer-check-item { margin-left: 6px; margin-right: 2px; white-space: nowrap; font-size: 7pt; }
    .trf-footer-check-item .trf-check { font-size: 7pt; margin-right: 1px; }
    .trf-footer-cell .trf-field-label { font-weight: bold; font-size: 7pt; }
    .trf-footer-cell .trf-field-value { font-size: 7pt; font-weight: normal; margin-left: 2px; }
    .trf-footer-nowrap { white-space: nowrap; overflow: hidden; }
    .trf-footer-inline-table {
        width: 100%;
        border-collapse: collapse;
        border: none !important;
        margin: 0;
        table-layout: auto;
    }
    .trf-footer-inline-table tr { border: none !important; }
    .trf-footer-inline-table td {
        border: none !important;
        padding: 0;
        vertical-align: middle;
        white-space: nowrap;
        background: transparent;
    }
    .trf-footer-inline-label { width: auto; }
    .trf-footer-inline-option { width: 1%; padding-left: 3px !important; }
    .trf-footer-inline-option .trf-footer-check-item { margin-left: 0; }
    .trf-waste-water-table col.trf-ww-col-details { width: 33.333%; }
    .trf-waste-water-table col.trf-ww-col-apparatus { width: 33.333%; }
    .trf-waste-water-table col.trf-ww-col-method { width: 33.334%; }
    .trf-waste-water-table th,
    .trf-waste-water-table td {
        border: 1px solid #000;
        box-sizing: border-box;
        vertical-align: top;
        text-align: left;
    }
    .trf-waste-water-table th.trf-collection-section-header,
    .trf-waste-water-table .trf-banner-row td {
        text-align: center;
        vertical-align: middle;
    }
    .trf-ww-subheader {
        background: #d9d9d9;
        font-weight: bold;
        font-size: 6.5pt;
        text-align: center;
        text-transform: uppercase;
        padding: 2px 4px;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
    }
    .trf-ww-description-cell {
        vertical-align: top;
        text-align: left;
        padding: 2px 3px;
        min-height: 14px;
        background: #fff;
    }
    .trf-ww-stack-cell { vertical-align: top; padding: 0; }
    .trf-ww-inner-grid {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        table-layout: fixed;
        border: none;
    }
    .trf-ww-inner-grid td,
    .trf-ww-inner-grid th {
        border: 1px solid #000;
        box-sizing: border-box;
        vertical-align: top;
        text-align: left;
        background: #fff;
    }
    .trf-ww-inner-grid-cell {
        padding: 3px 4px;
        font-size: 7pt;
        line-height: 1.2;
        min-height: 14px;
        vertical-align: top;
        text-align: left;
        background: #fff;
    }
    .trf-ww-subheader-row {
        background: #d9d9d9;
        font-weight: bold;
        font-size: 7pt;
        text-align: center;
        text-transform: uppercase;
        padding: 3px 4px;
        border: 1px solid #000;
        vertical-align: middle;
    }
    .trf-ww-instrument-table { width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; }
    .trf-ww-instrument-row {
        padding: 1px 2px;
        font-size: 5.5pt;
        vertical-align: top;
        text-align: left;
        border-top: 1px solid #b0b0b0;
        background: #fff;
        line-height: 1.1;
    }
    .trf-ww-job-sample-inner { width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; }
    .trf-ww-job-half,
    .trf-ww-sample-half {
        vertical-align: top;
        text-align: left;
        padding: 1px 3px;
        border: none;
        background: #fff;
        line-height: 1.1;
    }
    .trf-ww-job-half { border-bottom: 1px solid #000; }
    .trf-ww-anchor-cell { vertical-align: top; text-align: left; }
    .trf-ww-field-data-cell { vertical-align: top; padding: 0; background: #fff; }
    .trf-ww-field-table { width: 100%; border-collapse: collapse; margin: 0; }
    .trf-ww-field-row {
        padding: 3px 4px;
        font-size: 6.5pt;
        vertical-align: middle;
        border-bottom: 1px solid #b0b0b0;
        min-height: 17px;
        background: #fff;
    }
    .trf-ww-field-table tr:last-child .trf-ww-field-row { border-bottom: none; }
    .trf-ww-requirements-cell { vertical-align: top; padding: 0; }
    .trf-ww-requirements-table { width: 100%; border-collapse: collapse; margin: 0; }
    .trf-ww-requirement-row {
        padding: 8px 4px;
        font-size: 6.5pt;
        vertical-align: middle;
        text-align: left;
        background: #fff;
    }
    .trf-ww-sample-number-label { margin-top: 8px; }
    body.trf-waste-water { font-size: 8.5pt; line-height: 1.2; }
    body.trf-waste-water .trf-page {
        page-break-inside: avoid;
        width: 100%;
        max-width: 100%;
    }
    body.trf-waste-water .trf-header-table,
    body.trf-waste-water .trf-waste-water-table,
    body.trf-waste-water .trf-footer-table {
        width: 100% !important;
        max-width: 100%;
    }
    body.trf-waste-water .trf-ww-customer-tbody {
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    body.trf-waste-water .trf-ww-customer-banner,
    body.trf-waste-water .trf-ww-customer-row {
        page-break-inside: avoid;
    }
    body.trf-waste-water .trf-ww-header-table,
    body.trf-waste-water .trf-ww-header-table td,
    body.trf-waste-water .trf-ww-header-company td {
        border: none !important;
        background: transparent;
    }
    body.trf-waste-water .trf-header-table { margin-bottom: 2px; border: none; page-break-after: avoid; }
    body.trf-waste-water .trf-header-table td { border: none !important; }
    body.trf-waste-water .trf-title { font-size: 11pt; }
    body.trf-waste-water .trf-title-row td { padding: 0 0 2px; }
    body.trf-waste-water .trf-logo { max-height: 34px; max-width: 64px; margin-right: 15px; }
    body.trf-waste-water .trf-header-left,
    body.trf-waste-water .trf-header-serial { padding: 2px 0; }
    body.trf-waste-water .trf-header-company-grid td {
        padding: 0px 4px;
        font-size: 7pt;
        line-height: 1.15;
        vertical-align: top;
    }
    body.trf-waste-water .trf-company-name-cell { font-size: 9pt; padding-bottom: 2px; }
    body.trf-waste-water .trf-company-col-logo { width: 10%; }
    body.trf-waste-water .trf-company-logo-cell { padding-right: 15px !important; }
    body.trf-waste-water .trf-company-col-left { width: 50%; padding-right: 15px; }
    body.trf-waste-water .trf-company-col-right { width: 40%; padding-left: 15px; }
    body.trf-waste-water .trf-company-address-cell .trf-field-label,
    body.trf-waste-water .trf-company-address-cell .trf-field-value { font-size: 7.1pt; }
    body.trf-waste-water .trf-serial { font-size: 10pt; }
    body.trf-waste-water .trf-field-label,
    body.trf-waste-water .trf-field-value { font-size: 7.5pt; }
    body.trf-waste-water .trf-check { font-size: 7.5pt; }
    body.trf-waste-water .trf-waste-water-table td,
    body.trf-waste-water .trf-waste-water-table th {
        padding: 5px 6px;
        font-size: 7.5pt;
        line-height: 1.2;
        vertical-align: top;
        text-align: left;
    }
    body.trf-waste-water .trf-waste-water-table th.trf-collection-section-header,
    body.trf-waste-water .trf-waste-water-table .trf-banner-row td,
    body.trf-waste-water .trf-ww-subheader {
        text-align: center;
        vertical-align: middle;
    }
    body.trf-waste-water .trf-banner-row td {
        font-size: 8pt;
        padding: 6px 8px;
    }
    body.trf-waste-water .trf-waste-water-table .trf-customer-field,
    body.trf-waste-water .trf-waste-water-table .trf-meta-cell,
    body.trf-waste-water .trf-waste-water-table .trf-ww-description-cell {
        padding: 6px 8px;
        min-height: 0 !important;
        vertical-align: top;
        text-align: left;
        line-height: 1.2;
    }
    body.trf-waste-water .trf-waste-water-table .trf-customer-field .trf-field-value {
        font-size: 7.5pt;
    }
    body.trf-waste-water .trf-waste-water-table .trf-job-cell {
        padding: 0 !important;
        min-height: 0 !important;
        vertical-align: top;
        height: auto !important;
    }
    body.trf-waste-water .trf-ww-job-sample-cell {
        padding: 0 !important;
        vertical-align: top;
    }
    body.trf-waste-water .trf-ww-job-half,
    body.trf-waste-water .trf-ww-sample-half {
        padding: 6px 8px;
        min-height: 0 !important;
        vertical-align: top;
        text-align: left;
        box-sizing: border-box;
    }
    body.trf-waste-water .trf-ww-job-half {
        border-bottom: 1px solid #000;
    }
    body.trf-waste-water .trf-job-number-value {
        min-height: 0 !important;
    }
    body.trf-waste-water .trf-waste-water-table .trf-job-number-label {
        font-size: 7.5pt;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    body.trf-waste-water .trf-waste-water-table .trf-job-number-value {
        font-size: 7.5pt;
        min-height: 0 !important;
        margin-bottom: 0;
        display: inline;
        line-height: 1.2;
    }
    body.trf-waste-water .trf-waste-water-table .trf-meta-cell {
        padding: 6px 8px;
        min-height: 0 !important;
        line-height: 1.2;
        vertical-align: top;
        text-align: left;
    }
    body.trf-waste-water .trf-waste-water-table .trf-collection-check-cell,
    body.trf-waste-water .trf-waste-water-table .trf-ww-stack-cell,
    body.trf-waste-water .trf-waste-water-table .trf-ww-field-data-cell,
    body.trf-waste-water .trf-waste-water-table .trf-ww-requirements-cell,
    body.trf-waste-water .trf-waste-water-table .trf-ww-method-cell {
        padding: 0 !important;
        vertical-align: top;
    }
    body.trf-waste-water .trf-collection-check-cell > table,
    body.trf-waste-water .trf-ww-stack-cell > table,
    body.trf-waste-water .trf-ww-field-data-cell > table,
    body.trf-waste-water .trf-ww-requirements-cell > table,
    body.trf-waste-water .trf-ww-method-cell > table {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
        table-layout: fixed;
    }
    body.trf-waste-water .trf-ww-section-stack,
    body.trf-waste-water .trf-ww-section-rows {
        width: 100% !important;
        max-width: 100% !important;
        border-collapse: collapse !important;
        margin: 0 !important;
        padding: 0 !important;
        table-layout: fixed;
        border: none;
    }
    body.trf-waste-water .trf-ww-section-stack td,
    body.trf-waste-water .trf-ww-section-rows td,
    body.trf-waste-water .trf-ww-section-cell {
        border: none !important;
        box-sizing: border-box;
        vertical-align: top;
        text-align: left;
        background: #fff;
        padding: 5px 6px !important;
        font-size: 7pt;
        line-height: 1.2;
        min-height: 14px;
    }
    body.trf-waste-water .trf-ww-section-stack tr:not(.trf-ww-section-last-row) td,
    body.trf-waste-water .trf-ww-section-rows tr:not(.trf-ww-section-last-row) td {
        border-bottom: 1px solid #000 !important;
        border-left: none !important;
        border-top: none !important;
    }
    body.trf-waste-water .trf-ww-section-rows.trf-check-grid-2 tr td:first-child:not([colspan]),
    body.trf-waste-water .trf-ww-section-stack tr td:first-child:not([colspan]) {
        border-right: 1px solid #000 !important;
    }
    body.trf-waste-water .trf-ww-section-stack tr td[colspan]:not([colspan="1"]) {
        border-right: none !important;
    }
    body.trf-waste-water .trf-ww-subheader-row {
        background: #d9d9d9 !important;
        font-weight: bold;
        font-size: 7pt;
        text-align: center;
        text-transform: uppercase;
        padding: 5px 6px;
        border: none !important;
        vertical-align: middle;
    }
    body.trf-waste-water .trf-waste-water-table .trf-collection-section-header {
        font-size: 7.5pt;
        padding: 5px 6px;
    }
    body.trf-waste-water .trf-ww-subheader {
        font-size: 7pt;
        padding: 5px 6px;
    }
    body.trf-waste-water .trf-ww-description-cell {
        padding: 5px 6px;
        min-height: 0 !important;
        line-height: 1.2;
    }
    body.trf-waste-water .trf-ww-instrument-row {
        padding: 5px 6px;
        font-size: 7pt;
        line-height: 1.2;
        vertical-align: top;
        text-align: left;
    }
    body.trf-waste-water .trf-ww-field-row {
        padding: 5px 6px;
        font-size: 7.5pt;
        min-height: 18px;
        line-height: 1.25;
        vertical-align: top;
        text-align: left;
    }
    body.trf-waste-water .trf-ww-requirement-row {
        padding: 10px 6px;
        font-size: 7.5pt;
        line-height: 1.25;
        min-height: 28px;
        vertical-align: middle;
        border: none !important;
    }
    body.trf-waste-water .trf-ww-requirement-label {
        font-size: 7.5pt;
        margin-left: 2px;
    }
    body.trf-waste-water .trf-check-grid td {
        white-space: normal;
        word-wrap: break-word;
    }
    body.trf-waste-water .trf-ww-section-rows td,
    body.trf-waste-water .trf-ww-section-stack td,
    body.trf-waste-water .trf-ww-section-cell {
        border: none !important;
        padding: 5px 6px !important;
    }
    body.trf-waste-water .trf-ww-section-rows.trf-check-grid-normal td,
    body.trf-waste-water .trf-ww-section-rows.trf-check-grid-normal .trf-ww-section-cell {
        padding: 4px 5px !important;
        font-size: 7pt !important;
        min-height: 16px !important;
        height: auto !important;
        line-height: 1.2 !important;
    }
    body.trf-waste-water .trf-ww-section-rows.trf-check-grid-relaxed td,
    body.trf-waste-water .trf-ww-section-rows.trf-check-grid-relaxed .trf-ww-section-cell {
        padding: 5px 6px !important;
        font-size: 7.5pt !important;
        min-height: 20px !important;
        height: auto !important;
        line-height: 1.25 !important;
    }
    body.trf-waste-water .trf-ww-method-cell .trf-ww-section-rows.trf-check-grid-relaxed td {
        min-height: 20px !important;
    }
    body.trf-waste-water .trf-check-grid-bordered { margin: 0; }
    body.trf-waste-water .trf-check-grid-bordered td {
        font-size: 7pt;
        min-height: 14px;
        height: 14px;
        padding: 2px 3px;
        vertical-align: top;
        text-align: left;
        white-space: normal;
        word-wrap: break-word;
    }
    body.trf-waste-water .trf-check-grid-bordered.trf-check-grid-2 td {
        min-height: 14px;
        height: 14px;
    }
    body.trf-waste-water .trf-check-grid-empty-cell {
        min-height: 14px;
        height: 14px;
    }
    body.trf-waste-water .trf-check-grid-bordered tr.trf-check-grid-incomplete-row td {
        min-height: 14px !important;
        height: 14px !important;
    }
    body.trf-waste-water .trf-check-grid-subheader-cell {
        font-size: 7pt;
        text-align: center;
        vertical-align: middle;
    }
    body.trf-waste-water .trf-footer-sign-row td { padding: 0; }
    body.trf-waste-water .trf-footer-cell,
    body.trf-waste-water .trf-table tr.trf-footer-sign-row > td.trf-footer-cell {
        padding: 2px 3px !important;
        font-size: 7pt;
        line-height: 1.2;
        min-height: 14px;
    }
    body.trf-waste-water .trf-footer-nowrap {
        white-space: nowrap !important;
        overflow: hidden;
    }
    body.trf-waste-water .trf-footer-inline-table td {
        font-size: 6.5pt;
        line-height: 1.15;
    }
    body.trf-waste-water .trf-footer-inline-option {
        padding-left: 2px !important;
    }
    body.trf-waste-water .trf-footer-conformity-cell,
    body.trf-waste-water .trf-footer-condition-cell {
        font-size: 6.5pt;
        padding: 2px 2px !important;
        overflow: hidden;
        vertical-align: top;
    }
    body.trf-waste-water .trf-footer-conformity-cell .trf-field-label,
    body.trf-waste-water .trf-footer-condition-cell .trf-field-label {
        font-size: 6.5pt;
    }
    body.trf-waste-water .trf-ww-conformity-block {
        width: 100%;
        overflow: hidden;
    }
    body.trf-waste-water .trf-ww-conformity-label {
        margin-bottom: 2px;
        white-space: normal;
    }
    body.trf-waste-water .trf-ww-conformity-options {
        table-layout: fixed !important;
        width: 100% !important;
    }
    body.trf-waste-water .trf-ww-conformity-options td {
        width: 50%;
        white-space: nowrap;
        padding: 1px 2px 1px 0 !important;
        overflow: hidden;
    }
    body.trf-waste-water .trf-footer-check-item {
        margin-left: 0;
        margin-right: 4px;
        font-size: 6.5pt;
    }
    body.trf-waste-water .trf-footer-check-item .trf-check { font-size: 6.5pt; }
    body.trf-waste-water .trf-footer-lab-head {
        vertical-align: middle;
        text-align: center;
        overflow: hidden;
    }
    body.trf-waste-water .trf-footer-cell .trf-field-value { font-size: 7pt; }
    body.trf-waste-water .trf-lab-title { font-size: 7pt; }
    body.trf-waste-water .trf-footer-table { margin-top: 2px; border-top: none; }
    body.trf-waste-water .trf-footer-table td { font-size: 7.5pt; padding-top: 1px; }
    body.trf-waste-water .trf-waste-water-table tr.trf-footer-sign-row:last-child .trf-footer-cell {
        min-height: 20px;
    }

    /* Portrait: keep waste-water dense so content uses the page */
    body.trf-orientation-portrait.trf-waste-water .trf-header-table {
        margin-bottom: 4px;
    }
    body.trf-orientation-portrait.trf-waste-water .trf-table,
    body.trf-orientation-portrait.trf-waste-water .trf-waste-water-table {
        margin-bottom: 4px;
    }
    body.trf-orientation-portrait.trf-waste-water .trf-footer-cell {
        padding: 2px 3px !important;
        min-height: 16px !important;
        line-height: 1.2 !important;
        font-size: 7pt !important;
    }
    body.trf-orientation-portrait.trf-waste-water .trf-footer-table {
        margin-top: 4px;
    }
    body.trf-orientation-portrait.trf-waste-water .trf-section-title {
        margin-top: 4px;
        margin-bottom: 2px;
        padding: 4px 6px;
    }
</style>
