<style>
    * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
    body { font-size: 8pt; color: #000; margin: 8px 10px; line-height: 1.2; }
    .trf-primary { color: {{ $branding['primary'] ?? '#6D0A0E' }}; }
    .trf-section-title {
        background: {{ $branding['primary'] ?? '#6D0A0E' }};
        color: #fff;
        font-weight: bold;
        font-size: 8pt;
        padding: 2px 5px;
        text-transform: uppercase;
        margin: 4px 0 0;
        border: 1px solid #000;
    }
    .trf-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .trf-table td, .trf-table th {
        border: 1px solid #000;
        padding: 2px 3px;
        vertical-align: top;
        font-size: 7pt;
        word-wrap: break-word;
    }
    .trf-table th {
        background: #d9d9d9;
        font-weight: bold;
        text-align: center;
        font-size: 6.5pt;
    }
    .trf-header-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .trf-header-table td { border: none; vertical-align: top; padding: 1px 3px; }
    .trf-logo { max-height: 44px; max-width: 80px; }
    .trf-title-row td { border: none; padding: 2px 0; }
    .trf-title {
        font-size: 11pt;
        font-weight: bold;
        text-align: center;
        color: {{ $branding['primary'] ?? '#6D0A0E' }};
    }
    .trf-serial { text-align: right; font-size: 9pt; font-weight: bold; white-space: nowrap; }
    .trf-company-name { font-weight: bold; font-size: 9pt; color: {{ $branding['primary'] ?? '#6D0A0E' }}; }
    .trf-company-meta { font-size: 7pt; line-height: 1.25; }
    .trf-field-label { font-weight: bold; font-size: 7pt; }
    .trf-field-value {
        border-bottom: 1px dotted #666;
        min-height: 11px;
        display: inline-block;
        min-width: 60%;
    }
    .trf-check { font-family: DejaVu Sans, sans-serif; font-size: 7pt; white-space: nowrap; }
    .trf-check-on::before { content: "\2611"; color: {{ $branding['primary'] ?? '#6D0A0E' }}; }
    .trf-check-off::before { content: "\2610"; }
    .trf-small { font-size: 6pt; color: #333; }
    .trf-footer {
        margin-top: 6px;
        font-size: 7pt;
        text-align: center;
        color: #333;
        border-top: 1px solid #999;
        padding-top: 3px;
    }
    .trf-center { text-align: center; }
    .trf-right { text-align: right; }
    .trf-meta-label { font-weight: bold; white-space: nowrap; }
    .trf-grid-meta { width: 18%; }
    .trf-grid-col { width: 20.5%; font-size: 6.5pt; }
    .trf-subheader { background: #e8e8e8; font-size: 6pt; text-align: center; font-weight: bold; }
    .trf-lab-title {
        background: {{ $branding['primary'] ?? '#6D0A0E' }};
        color: #fff;
        font-weight: bold;
        font-size: 7pt;
        padding: 2px 4px;
        text-transform: uppercase;
    }
</style>
