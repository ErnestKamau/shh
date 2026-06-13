<style>
    * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
    body { font-size: 9px; color: #1a1a1a; margin: 10px 12px; line-height: 1.25; }
    .trf-primary { color: {{ $branding['primary'] ?? '#6D0A0E' }}; }
    .trf-section-title {
        background: {{ $branding['primary'] ?? '#6D0A0E' }};
        color: #fff;
        font-weight: bold;
        font-size: 9px;
        padding: 3px 6px;
        text-transform: uppercase;
        margin: 8px 0 4px;
    }
    .trf-table { width: 100%; border-collapse: collapse; }
    .trf-table td, .trf-table th { border: 1px solid #666; padding: 3px 4px; vertical-align: top; }
    .trf-table th { background: #e8e8e8; font-size: 8px; text-align: center; }
    .trf-header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .trf-header-table td { border: none; vertical-align: top; padding: 2px 4px; }
    .trf-logo { max-height: 48px; max-width: 90px; }
    .trf-hex { width: 70px; height: 52px; }
    .trf-title { font-size: 13px; font-weight: bold; text-align: center; color: {{ $branding['primary'] ?? '#6D0A0E' }}; margin: 4px 0; }
    .trf-serial { text-align: right; font-size: 10px; font-weight: bold; }
    .trf-company-name { font-weight: bold; font-size: 10px; color: {{ $branding['primary'] ?? '#6D0A0E' }}; }
    .trf-company-meta { font-size: 8px; line-height: 1.3; }
    .trf-field-label { font-weight: bold; font-size: 8px; }
    .trf-field-value { border-bottom: 1px dotted #888; min-height: 12px; display: block; }
    .trf-checkbox-grid { width: 100%; border-collapse: collapse; }
    .trf-checkbox-grid td { border: none; padding: 1px 3px; font-size: 8px; white-space: nowrap; }
    .trf-check { font-family: DejaVu Sans, sans-serif; }
    .trf-check-on::before { content: "\2611"; color: {{ $branding['primary'] ?? '#6D0A0E' }}; }
    .trf-check-off::before { content: "\2610"; }
    .trf-small { font-size: 7px; color: #444; }
    .trf-footer { margin-top: 10px; font-size: 8px; text-align: center; color: #555; border-top: 1px solid #ccc; padding-top: 4px; }
    .trf-state-group { white-space: nowrap; font-size: 7px; }
    .trf-center { text-align: center; }
    .trf-right { text-align: right; }
    .trf-signature-table td { border: none; padding: 4px 6px 2px 0; vertical-align: bottom; }
    .trf-signature-line { border-bottom: 1px solid #333; min-height: 14px; display: block; margin-top: 12px; }
</style>
