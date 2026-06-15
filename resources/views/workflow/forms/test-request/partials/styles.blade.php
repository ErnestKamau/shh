@php
    $maroon = $branding['primary'] ?? '#800000';
    $serialRed = '#CC0000';
@endphp
<style>
    @page { size: A4 landscape; margin: 3mm 4mm; }
    * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
    body { font-size: 8pt; color: #000; margin: 0; padding: 0; line-height: 1.2; }
    body.trf-layout-centered { margin: 0; padding: 0; }
    .trf-page { width: 100%; margin: 0; }
    .trf-section-title {
        background: {{ $maroon }};
        color: #fff;
        font-weight: bold;
        font-size: 8pt;
        padding: 3px 5px;
        text-transform: uppercase;
        border: 1px solid #000;
        text-align: center;
    }
    .trf-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
    .trf-table td, .trf-table th {
        border: 1px solid #000;
        padding: 1px 2px;
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
    .trf-header-logo { width: 10%; vertical-align: middle; }
    .trf-header-center { width: 72%; text-align: center; vertical-align: middle; padding: 6px 0; }
    .trf-header-serial { width: 18%; text-align: center; vertical-align: middle; padding: 6px 0; }
    .trf-header-company-grid { width: 100%; max-width: 100%; border-collapse: collapse; margin: 0 auto; table-layout: fixed; }
    .trf-header-company-grid td {
        border: none;
        padding: 2px 4px;
        font-size: 7pt;
        text-align: left;
        vertical-align: middle;
        line-height: 1.55;
        white-space: nowrap;
    }
    .trf-company-col-left { width: 48%; padding-right: 28px; text-align: right; }
    .trf-company-col-right { width: 48%; padding-left: 28px; text-align: left; }
    .trf-company-address-cell { width: 100%; padding: 2px 4px; text-align: center; white-space: nowrap; }
    .trf-company-name-cell { font-weight: bold; font-size: 8pt; padding-bottom: 2px; }
    .trf-logo { max-height: 42px; max-width: 80px; }
    .trf-title-row td { border: none; padding: 0 0 3px; }
    .trf-title {
        font-size: 11pt;
        font-weight: bold;
        text-align: center;
        color: #000;
        text-transform: uppercase;
    }
    .trf-serial { text-align: center; font-size: 12pt; font-weight: bold; white-space: nowrap; color: {{ $serialRed }}; display: block; line-height: 1.1; }
    .trf-company-center { text-align: center; }
    .trf-company-name { font-weight: bold; font-size: 8.5pt; color: #000; line-height: 1.2; }
    .trf-company-meta { font-size: 7pt; line-height: 1.2; color: #000; font-weight: normal; }
    .trf-company-side { font-size: 7pt; line-height: 1.2; text-align: right; }
    .trf-field-label { font-weight: bold; font-size: 7pt; color: #000; }
    .trf-field-value { font-size: 7pt; font-weight: normal; color: #000; }
    .trf-job-label { font-weight: bold; font-size: 7pt; color: #000; }
    .trf-check { font-family: DejaVu Sans, sans-serif; font-size: 7pt; display: inline; line-height: 1; }
    .trf-check-on::before { content: "\2611"; color: {{ $serialRed }}; display: inline; }
    .trf-check-off::before { content: "\2610"; color: #000; display: inline; }
    .trf-tick { font-weight: bold; font-size: 8pt; color: {{ $serialRed }}; }
    .trf-check-only { display: inline-block; text-align: center; width: 100%; }
    .trf-small { font-size: 5.5pt; color: #333; }
    .trf-footer-table { width: 100%; border-collapse: collapse; margin-top: 3px; border-top: 1px solid #999; }
    .trf-footer-table td { border: none; font-size: 7pt; color: #333; padding-top: 2px; }
    .trf-center { text-align: center; vertical-align: middle; }
    .trf-right { text-align: right; }
    .trf-meta-key { font-weight: bold; display: block; font-size: 7pt; }
    .trf-meta-val { display: inline; font-size: 7pt; line-height: 1.2; word-wrap: break-word; }
    .trf-meta-cell { vertical-align: middle; padding: 3px 4px; min-height: 18px; border: 1px solid #000; word-wrap: break-word; line-height: 1.2; background: #fff; }
    .trf-collection-section-header { background: #d9d9d9 !important; }
    .trf-collection-check-cell { vertical-align: top; padding: 0; border: 1px solid #000; background: #fff; }
    .trf-dotted-leader { color: {{ $serialRed }}; letter-spacing: 0.5px; }
    .trf-tick-col-header { background: #d9d9d9 !important; }
    .trf-tick-cell { background: #fff; vertical-align: middle; text-align: center; }
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
        border: 1px solid #b0b0b0;
        padding: 3px 4px;
        font-size: 6pt;
        vertical-align: middle;
        min-height: 17px;
        height: 17px;
        background: #fff;
    }
    .trf-check-grid-bordered.trf-check-grid-2 td { width: 50%; }
    .trf-check-grid-bordered.trf-check-grid-1 td { width: 100%; }
    .trf-check-grid-bordered tr td:first-child { border-left: none; }
    .trf-check-grid-bordered tr td:last-child { border-right: none; }
    .trf-check-grid-bordered tr:first-child td { border-top: none; }
    .trf-check-grid-bordered tr:last-child td { border-bottom: none; }
    .trf-check-grid-1 td { width: 100%; }
    .trf-check-grid-2 td { width: 50%; }
    .trf-subheader { background: #d9d9d9; font-size: 6.5pt; text-align: center; font-weight: bold; vertical-align: middle; padding: 1px; }
    .trf-vtext-narrow .trf-vtext-br { font-size: 5pt; line-height: 1; }
    .trf-banner-row td {
        background: {{ $maroon }};
        color: #fff;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 7.5pt;
        text-align: center;
        padding: 3px 4px;
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
    .trf-food-table col.trf-col-desc { width: 10%; }
    .trf-food-table col.trf-col-location { width: 9%; }
    .trf-food-table col.trf-col-qty { width: 2.5%; }
    .trf-food-table col.trf-col-tick { width: 1.6%; }
    .trf-food-table col.trf-col-temp { width: 3.5%; }
    .trf-food-table col.trf-col-date { width: 5.5%; }
    .trf-food-table col.trf-col-batch { width: 7%; }
    .trf-food-table col.trf-col-params { width: 11%; }
    .trf-food-table col.trf-col-state { width: 12%; }
    .trf-water-table col.trf-col-serial { width: 3.5%; }
    .trf-water-table col.trf-col-sample-no { width: 6%; }
    .trf-water-table col.trf-col-desc { width: 14%; }
    .trf-water-table col.trf-col-location { width: 11%; }
    .trf-water-table col.trf-col-qty { width: 3.5%; }
    .trf-water-table col.trf-col-tick { width: 2.2%; }
    .trf-water-table col.trf-col-field { width: 5.5%; }
    .trf-water-table col.trf-col-test { width: 6%; }
    .trf-customer-table .trf-banner-row td { border-right: 1px solid #000; }
    .trf-customer-field { vertical-align: middle; padding: 3px 4px; min-height: 18px; word-wrap: break-word; line-height: 1.2; }
    .trf-customer-field .trf-field-value { word-wrap: break-word; }
    .trf-text-cell { font-size: 6.5pt; padding: 1px 2px; vertical-align: middle; word-wrap: break-word; line-height: 1.15; }
    .trf-job-cell {
        vertical-align: top;
        padding: 4px 6px;
        border: 1px solid #000;
        min-height: 58px;
    }
    .trf-job-number-label {
        display: block;
        font-weight: bold;
        font-size: 8pt;
        color: {{ $maroon }};
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .trf-job-number-value { display: block; font-size: 7pt; font-weight: normal; min-height: 36px; }
    .trf-customer-left { width: 68%; vertical-align: middle; padding: 2px 4px; }
    .trf-customer-right { width: 32%; vertical-align: middle; padding: 2px 4px; }
    .trf-data-row td { min-height: 16px; height: 16px; vertical-align: middle; font-weight: normal; font-size: 6.5pt; overflow: hidden; background: #fff; }
    .trf-lab-box {
        padding: 3px 4px;
        vertical-align: top;
    }
    .trf-lab-box .trf-lab-field { margin-bottom: 3px; }
    .trf-lab-box .trf-field-label { display: inline; margin-top: 0; }
    .trf-lab-box .trf-field-value { display: inline; }
    .trf-footer-sign-row td { vertical-align: middle; padding: 0; border: 1px solid #000; }
    .trf-footer-cell { padding: 4px 6px; font-size: 7pt; line-height: 1.35; vertical-align: middle; min-height: 18px; }
    .trf-footer-lab-head { vertical-align: middle; }
    .trf-lab-title { font-weight: bold; font-size: 7pt; text-transform: uppercase; margin: 0; padding: 0; }
    .trf-footer-check-item { margin-left: 6px; margin-right: 2px; white-space: nowrap; font-size: 7pt; }
    .trf-footer-check-item .trf-check { font-size: 7pt; margin-right: 1px; }
    .trf-footer-cell .trf-field-label { font-weight: bold; font-size: 7pt; }
    .trf-footer-cell .trf-field-value { font-size: 7pt; font-weight: normal; margin-left: 2px; }
</style>
