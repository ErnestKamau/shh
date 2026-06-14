@php
    $maroon = $branding['primary'] ?? '#800000';
@endphp
<style>
    @page { size: A4 landscape; margin: 8mm 10mm; }
    * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
    body { font-size: 8pt; color: #000; margin: 0; padding: 0; line-height: 1.2; }
    body.trf-layout-centered { margin: 0; padding: 0; }
    .trf-page { width: 100%; margin: 0; }
    .trf-accent { color: {{ $maroon }}; }
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
        vertical-align: top;
        font-size: 7pt;
        word-wrap: break-word;
        overflow: hidden;
    }
    .trf-table th {
        background: #d9d9d9;
        font-weight: bold;
        text-align: center;
        font-size: 6.5pt;
        vertical-align: middle;
    }
    .trf-header-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
    .trf-header-table td { border: none; vertical-align: top; padding: 1px 3px; }
    .trf-logo { max-height: 42px; max-width: 80px; }
    .trf-title-row td { border: none; padding: 2px 0 4px; }
    .trf-title {
        font-size: 11pt;
        font-weight: bold;
        text-align: center;
        color: #000;
    }
    .trf-serial { text-align: right; font-size: 9pt; font-weight: bold; white-space: nowrap; color: {{ $maroon }}; margin-top: 2px; }
    .trf-company-name { font-weight: bold; font-size: 9pt; color: #000; }
    .trf-company-meta { font-size: 7pt; line-height: 1.25; }
    .trf-field-label { font-weight: bold; font-size: 7pt; }
    .trf-field-value { font-size: 7pt; }
    .trf-job-label { font-weight: bold; font-size: 7pt; color: {{ $maroon }}; }
    .trf-check { font-family: DejaVu Sans, sans-serif; font-size: 7pt; }
    .trf-check-on::before { content: "\2611"; color: #000; }
    .trf-check-off::before { content: "\2610"; color: #000; }
    .trf-check-only { display: inline-block; text-align: center; width: 100%; }
    .trf-small { font-size: 5.5pt; color: #333; }
    .trf-footer-table { width: 100%; border-collapse: collapse; margin-top: 3px; border-top: 1px solid #999; }
    .trf-footer-table td { border: none; font-size: 7pt; color: #333; padding-top: 2px; }
    .trf-center { text-align: center; vertical-align: middle; }
    .trf-right { text-align: right; }
    .trf-meta-key { font-weight: bold; display: block; font-size: 7pt; }
    .trf-meta-val { display: block; font-size: 7pt; line-height: 1.2; }
    .trf-meta-cell { vertical-align: top; padding: 2px 3px; }
    .trf-subheader { background: #e8e8e8; font-size: 6.5pt; text-align: center; font-weight: bold; vertical-align: middle; }
    .trf-lab-title { font-weight: bold; font-size: 7pt; text-transform: uppercase; text-align: center; margin-bottom: 3px; }
    .trf-banner-row td {
        background: {{ $maroon }};
        color: #fff;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 7.5pt;
        text-align: center;
        padding: 3px 4px;
    }
    .trf-no-gap { margin: 0; padding: 0; }
    .trf-vtext {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        white-space: nowrap;
        display: inline-block;
        height: 44px;
        font-size: 6pt;
        line-height: 1;
        margin: 0 auto;
    }
    .trf-vtext-br {
        display: inline-block;
        font-size: 6pt;
        line-height: 1.05;
        text-align: center;
    }
    .trf-vtext-wrap { text-align: center; vertical-align: middle; padding: 1px; }
    .trf-check-grid { width: 100%; border-collapse: collapse; }
    .trf-check-grid td { border: none; padding: 0 2px; font-size: 6.5pt; vertical-align: top; width: 50%; line-height: 1.15; }
    .trf-job-cell { vertical-align: top; text-align: left; padding: 3px 4px; }
    .trf-customer-left { width: 70%; vertical-align: top; padding: 3px 4px; }
    .trf-customer-right { width: 30%; vertical-align: top; padding: 3px 4px; }
    .trf-data-row td { height: 20px; vertical-align: middle; }
    .trf-lab-box {
        border: 1px solid #000;
        padding: 3px 4px;
        vertical-align: top;
    }
    .trf-lab-box .trf-field-label { display: block; margin-top: 2px; }
    .trf-state-cell { font-size: 6pt; text-align: center; }
</style>
