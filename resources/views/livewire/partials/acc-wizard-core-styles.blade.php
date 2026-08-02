<style>
    .acc-wizard-root {
        --acc-accent: var(--ls-color-primary, var(--color-primary, #800000));
        --acc-accent-dark: var(--ls-color-primary-hover, var(--color-primary-hover, #6b0000));
        --acc-accent-soft: var(--ls-color-primary-soft, var(--color-primary-soft, #f8ecec));
        --acc-border: var(--ls-color-border, #e2e8f0);
        --acc-muted: var(--ls-color-muted, #64748b);
        --acc-text: var(--ls-color-ink, #1e293b);
        --acc-font: var(--ls-font-ui, "IBM Plex Sans", system-ui, -apple-system, "Segoe UI", sans-serif);
        --acc-text-xs: var(--ls-text-xs, 0.6875rem);
        --acc-text-sm: var(--ls-text-sm, 0.75rem);
        --acc-text-base: var(--ls-text-base, 0.8125rem);
        --acc-text-md: var(--ls-text-md, 0.875rem);
        --acc-text-lg: var(--ls-text-lg, 0.95rem);
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        color: var(--acc-text);
        -webkit-font-smoothing: antialiased;
    }

    .acc-wizard-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(4px);
    }

    .acc-wizard-dialog {
        margin: 0;
        max-width: 920px;
        width: 100%;
    }

    .acc-wizard-dialog.modal-xl {
        max-width: 1140px;
    }

    .acc-wizard-modal {
        border: none;
        border-radius: var(--ls-radius-xl, 12px);
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        color: var(--acc-text);
    }

    .acc-wizard-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1rem;
        background: #fff;
        color: var(--acc-text);
        border-bottom: 1px solid var(--acc-border);
    }

    .acc-wizard-eyebrow {
        display: block;
        font-size: var(--acc-text-xs);
        font-weight: var(--ls-font-semibold, 600);
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--acc-muted);
        margin-bottom: 0.15rem;
    }

    .acc-wizard-title {
        margin: 0;
        font-family: var(--acc-font);
        font-size: var(--acc-text-lg);
        font-weight: var(--ls-font-semibold, 600);
        display: flex;
        align-items: center;
        gap: 0.45rem;
        line-height: 1.3;
    }

    .acc-wizard-header .text-muted,
    .acc-wizard-header .small {
        font-size: var(--acc-text-sm) !important;
    }

    .acc-wizard-close {
        border: none;
        background: #f1f5f9;
        color: var(--acc-muted);
        width: var(--ls-btn-h, 34px);
        height: var(--ls-btn-h, 34px);
        border-radius: var(--ls-radius-sm, 6px);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
    }

    .acc-wizard-close:hover {
        background: #e2e8f0;
        color: var(--acc-text);
    }

    .acc-wizard-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0;
        padding: 0;
        background: #f8fafc;
        border-bottom: 1px solid var(--acc-border);
    }

    .acc-wizard-step {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.55rem 0.5rem;
        border: none;
        background: transparent;
        color: var(--acc-muted);
        font-family: var(--acc-font);
        font-size: var(--acc-text-sm);
        font-weight: var(--ls-font-semibold, 600);
        border-bottom: 3px solid transparent;
        transition: color 0.15s, border-color 0.15s, background 0.15s;
    }

    .acc-wizard-step:hover:not(:disabled) {
        color: var(--acc-text);
        background: var(--acc-accent-soft);
    }

    .acc-wizard-step.is-active {
        color: var(--acc-text);
        border-bottom-color: var(--acc-accent);
        background: #fff;
    }

    .acc-wizard-step.is-active .acc-wizard-step-label {
        color: var(--acc-text);
    }

    .acc-wizard-step.is-done {
        color: var(--acc-muted);
    }

    .acc-wizard-step:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .acc-wizard-step-index {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: var(--acc-text-xs);
        background: #e2e8f0;
        color: var(--acc-muted);
    }

    .acc-wizard-step.is-active .acc-wizard-step-index {
        background: var(--acc-accent);
        color: #fff;
    }

    .acc-wizard-step.is-done .acc-wizard-step-index {
        background: var(--ls-color-success-soft, #dcfce7);
        color: var(--ls-color-success-text, #166534);
    }

    .acc-wizard-top-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.55rem 1rem;
        background: #fff;
        border-bottom: 1px solid var(--acc-border);
    }

    .acc-wizard-top-nav-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-left: auto;
    }

    .acc-wizard-top-nav .btn {
        min-height: var(--ls-btn-h-sm, 30px);
        padding: 0.3rem var(--ls-btn-pad-x-sm, 0.65rem);
        font-family: var(--acc-font);
        font-size: var(--acc-text-sm);
        font-weight: var(--ls-font-semibold, 600);
        line-height: 1.2;
        border-radius: var(--ls-radius-sm, 6px);
    }

    .acc-wizard-body {
        padding: 0.85rem 1rem 1rem;
        background: #f8fafc;
        max-height: min(70vh, 640px);
        overflow-y: auto;
        color: var(--acc-text);
        font-size: var(--acc-text-base);
    }

    .acc-wizard-section {
        background: #fff;
        border: 1px solid var(--acc-border);
        border-radius: var(--ls-radius-lg, 10px);
        padding: var(--ls-card-pad-y, 0.85rem) var(--ls-card-pad-x, 1rem);
        margin-bottom: 0.85rem;
        box-shadow: var(--ls-shadow-sm, 0 1px 2px rgb(0 0 0 / 0.05));
    }

    .acc-wizard-section:last-child {
        margin-bottom: 0;
    }

    .acc-wizard-section-title {
        font-family: var(--acc-font);
        font-size: var(--acc-text-xs);
        font-weight: var(--ls-font-semibold, 600);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--acc-muted);
        margin-bottom: 0.65rem;
    }

    .acc-wizard-hint {
        font-size: var(--acc-text-sm);
        color: var(--acc-muted);
    }

    .acc-wizard-fields > [class*="col-"] {
        margin-bottom: 0.65rem;
        font-size: var(--acc-text-base);
    }

    .acc-wizard-fields strong {
        font-size: var(--acc-text-sm);
        font-weight: var(--ls-font-semibold, 600);
        color: var(--acc-muted);
    }

    .acc-label {
        font-size: var(--acc-text-sm);
        font-weight: var(--ls-font-semibold, 600);
        color: var(--acc-muted);
        margin-bottom: 0.3rem;
    }

    .acc-input {
        border-radius: var(--ls-radius-sm, 6px);
        border-color: var(--acc-border);
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        min-height: var(--ls-control-h, 34px);
    }

    .acc-input:focus {
        border-color: var(--acc-accent);
        box-shadow: 0 0 0 3px var(--ls-color-primary-focus, var(--color-primary-highlight, rgba(128, 0, 0, 0.18)));
    }

    .acc-wizard-footer {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        background: #fff;
        border-top: 1px solid var(--acc-border);
    }

    .acc-wizard-footer .btn,
    .acc-wizard-root .btn-sm {
        min-height: var(--ls-btn-h-sm, 30px);
        padding: 0.3rem var(--ls-btn-pad-x-sm, 0.65rem);
        font-family: var(--acc-font);
        font-size: var(--acc-text-sm);
        font-weight: var(--ls-font-semibold, 600);
        border-radius: var(--ls-radius-sm, 6px);
    }

    .acc-wizard-root .btn:not(.btn-sm) {
        min-height: var(--ls-btn-h, 34px);
        padding: 0.35rem var(--ls-btn-pad-x, 0.85rem);
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        font-weight: var(--ls-font-semibold, 600);
        border-radius: var(--ls-radius-sm, 6px);
    }

    .acc-wizard-root .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: var(--ls-badge-pad-y, 0.2rem) var(--ls-badge-pad-x, 0.55rem);
        font-size: var(--ls-badge-font, var(--acc-text-sm));
        font-weight: var(--ls-font-semibold, 600);
        line-height: 1.25;
        border-radius: 999px;
    }

    .acc-wizard-root .table,
    .acc-wizard-root .workflow-table {
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        color: var(--acc-text);
    }

    .acc-wizard-root .table thead th,
    .acc-wizard-root .workflow-table thead th {
        font-size: var(--acc-text-xs);
        font-weight: var(--ls-font-bold, 700);
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--acc-muted);
        background: #f8fafc;
        padding: var(--ls-table-cell-y, 0.55rem) var(--ls-table-cell-x, 0.7rem);
        white-space: nowrap;
    }

    .acc-wizard-root .table tbody td,
    .acc-wizard-root .workflow-table tbody td {
        font-size: var(--acc-text-base);
        font-weight: var(--ls-font-medium, 500);
        color: var(--acc-text);
        padding: var(--ls-table-cell-y, 0.55rem) var(--ls-table-cell-x, 0.7rem);
        vertical-align: middle;
    }

    .acc-wizard-root .form-control,
    .acc-wizard-root .form-control-sm {
        min-height: var(--ls-control-h, 34px);
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        border-radius: var(--ls-radius-sm, 6px);
        border-color: var(--acc-border);
        color: var(--acc-text);
    }

    .acc-wizard-root .alert {
        border-radius: var(--ls-radius-md, 8px);
        font-size: var(--acc-text-base);
        padding: 0.55rem 0.8rem;
    }

    .acc-pricing-toolbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .acc-pricing-table-wrap {
        border: 1px solid var(--acc-border);
        border-radius: var(--ls-radius-md, 8px);
        overflow: hidden;
        background: #fff;
    }

    .acc-pricing-table thead th {
        font-size: var(--acc-text-xs);
        font-weight: var(--ls-font-bold, 700);
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--acc-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--acc-border);
        white-space: nowrap;
    }

    [x-cloak] {
        display: none !important;
    }

    /* Sample configuration (Process Enquiry + Acceptance wizard) */
    .acc-sample-config-section {
        padding: 0.85rem 1rem;
    }

    .acc-sample-config-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        margin-bottom: 0.85rem !important;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid #eef2f7;
    }

    .acc-sample-config-toolbar-actions {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.5rem;
    }

    .acc-sample-config-sync-btn {
        width: var(--ls-btn-h, 34px);
        height: var(--ls-btn-h, 34px);
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--ls-radius-sm, 6px);
    }

    .acc-btn-add {
        border-radius: var(--ls-radius-sm, 6px);
        font-weight: var(--ls-font-semibold, 600);
        font-size: var(--acc-text-sm);
        padding: 0.3rem 0.75rem;
        min-height: var(--ls-btn-h-sm, 30px);
        color: #fff !important;
        background: var(--acc-accent) !important;
        border: 1px solid var(--acc-accent) !important;
    }

    .acc-btn-add:hover {
        color: #fff !important;
        background: var(--acc-accent-dark) !important;
        border-color: var(--acc-accent-dark) !important;
    }

    .acc-btn-remove {
        border-radius: var(--ls-radius-sm, 6px);
        color: var(--ls-color-danger-text, #991b1b);
        border-color: #fecaca;
        background: var(--ls-color-danger-soft, #fee2e2);
        padding: 0.2rem 0.45rem;
        font-size: var(--acc-text-sm);
    }

    .acc-btn-remove:hover {
        color: #9f1239;
        background: #ffe4e6;
        border-color: #fda4af;
    }

    .acc-sample-config-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .acc-sample-config-card {
        border: 1px solid var(--acc-border);
        border-radius: var(--ls-radius-lg, 10px);
        background: #fff;
        overflow: visible;
        box-shadow: var(--ls-shadow-sm, 0 1px 2px rgba(15, 23, 42, 0.04));
    }

    .acc-sample-config-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.55rem 0.85rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--acc-border);
    }

    .acc-sample-config-card-title {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-family: var(--acc-font);
        font-size: var(--acc-text-base);
        font-weight: var(--ls-font-semibold, 600);
        color: var(--acc-text);
    }

    .acc-sample-config-card-title .mdi {
        color: var(--acc-accent);
        font-size: 1rem;
    }

    .acc-sample-config-table-wrap {
        overflow-x: auto;
    }

    .acc-sample-config-table {
        table-layout: fixed;
        width: 100%;
        margin: 0;
        font-size: var(--acc-text-base);
    }

    .acc-sample-config-table thead th {
        font-size: var(--acc-text-xs);
        font-weight: var(--ls-font-bold, 700);
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--acc-muted);
        background: #fff;
        border-bottom: 1px solid var(--acc-border);
        padding: 0.55rem 0.65rem;
        vertical-align: bottom;
        white-space: nowrap;
    }

    .acc-sample-config-table tbody td {
        border-top: none;
        padding: 0.65rem;
        vertical-align: top;
        font-size: var(--acc-text-base);
    }

    .acc-sample-config-table--compact th:nth-child(1),
    .acc-sample-config-table--compact td:nth-child(1) {
        width: 22%;
        min-width: 130px;
    }

    .acc-sample-config-table--compact th:nth-child(2),
    .acc-sample-config-table--compact td:nth-child(2) {
        width: 30%;
        min-width: 160px;
    }

    .acc-sample-config-table--compact th:nth-child(3),
    .acc-sample-config-table--compact td:nth-child(3) {
        width: 28%;
        max-width: 220px;
    }

    .acc-sample-config-table--compact th:nth-child(4),
    .acc-sample-config-table--compact td:nth-child(4) {
        width: 110px;
    }

    .acc-sample-config-table--with-condition th:nth-child(1),
    .acc-sample-config-table--with-condition td:nth-child(1) {
        width: 18%;
        min-width: 120px;
    }

    .acc-sample-config-table--with-condition th:nth-child(2),
    .acc-sample-config-table--with-condition td:nth-child(2) {
        width: 22%;
        min-width: 140px;
    }

    .acc-sample-config-table--with-condition th:nth-child(3),
    .acc-sample-config-table--with-condition td:nth-child(3) {
        width: 18%;
        min-width: 120px;
    }

    .acc-sample-config-table--with-condition th:nth-child(4),
    .acc-sample-config-table--with-condition td:nth-child(4) {
        width: 24%;
        max-width: 200px;
    }

    .acc-sample-config-table--with-condition th:nth-child(5),
    .acc-sample-config-table--with-condition td:nth-child(5) {
        width: 110px;
    }

    .acc-sample-config-table--acceptance {
        table-layout: fixed;
    }

    .acc-sample-config-table--acceptance .acc-col-sample-type {
        width: 16%;
        min-width: 90px;
    }

    .acc-sample-config-table--acceptance .acc-col-analysis-type {
        width: 18%;
        min-width: 110px;
    }

    .acc-sample-config-table--acceptance .acc-col-lab-section {
        width: 14%;
        min-width: 100px;
    }

    .acc-sample-config-table--acceptance .acc-col-condition {
        width: 12%;
        min-width: 88px;
    }

    .acc-sample-config-table--acceptance .acc-col-main-standard {
        width: 34%;
        min-width: 180px;
    }

    .acc-sample-config-table--acceptance .acc-col-lab {
        width: 12%;
        min-width: 80px;
    }

    .acc-sample-config-table--acceptance .acc-col-assigned-user {
        width: 20%;
        min-width: 120px;
    }

    .acc-sample-config-table--acceptance td .form-control {
        width: 100%;
        max-width: 100%;
    }

    .acc-sample-config-table--acceptance .acc-col-qty {
        width: 56px;
    }

    .acc-sample-config-table--acceptance thead th {
        white-space: normal;
        line-height: 1.25;
        padding: 0.5rem 0.45rem;
        font-size: 0.62rem;
    }

    .acc-sample-config-table--acceptance tbody td {
        padding: 0.55rem 0.45rem;
    }

    .acc-sample-config-table--acceptance .acc-config-readonly {
        font-size: 0.8rem;
        padding: 0.25rem 0;
        word-break: break-word;
    }

    .acc-sample-config-table--acceptance .form-control-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.4rem;
    }

    .acc-sample-config-main-row td {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
    }

    .acc-sample-config-main-row .form-control {
        min-height: 2.125rem;
    }

    .acc-input-sm {
        max-width: 5.5rem;
        margin-inline: auto;
    }

    .acc-sample-config-params-row td,
    .acc-sample-config-section-row td {
        padding: 0.65rem 0.75rem 0.75rem;
        background: #f8fafc;
    }

    .acc-sample-config-params-panel {
        border: 1px solid var(--acc-border);
        border-radius: 10px;
        background: #fff;
        overflow: visible;
    }

    .acc-sample-config-params-panel + .acc-sample-config-params-panel,
    .acc-sample-config-instances-panel {
        margin-top: 0.65rem;
    }

    .acc-sample-config-params-band {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.45rem 0.55rem 0.45rem 0.35rem;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }

    .acc-sample-config-instances-band {
        border-bottom: none;
    }

    .acc-sample-config-section-toggle {
        flex: 1;
        display: flex;
        align-items: center;
        min-width: 0;
        padding: 0.35rem 0.5rem;
        border: none;
        background: transparent;
        text-align: left;
        cursor: pointer;
        border-radius: 8px;
        transition: background 0.15s ease;
    }

    .acc-sample-config-section-toggle:hover {
        background: var(--acc-accent-soft);
    }

    .acc-sample-config-section-toggle-main {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-width: 0;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--acc-text);
    }

    .acc-sample-config-chevron {
        color: var(--acc-accent);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .acc-sample-config-section-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: var(--acc-accent);
        background: var(--acc-accent-soft);
        border: 1px solid var(--color-primary-shadow);
        white-space: nowrap;
    }

    .acc-sample-config-params-band-actions {
        display: flex;
        flex-shrink: 0;
        gap: 0.4rem;
        align-items: center;
    }

    .acc-sample-config-select-all {
        border-radius: 7px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.65rem;
        color: var(--acc-accent);
        border-color: var(--color-primary-focus);
        background: #fff;
    }

    .acc-sample-config-select-all:hover:not(:disabled) {
        color: #fff !important;
        background: var(--acc-accent) !important;
        border-color: var(--acc-accent) !important;
    }

    .acc-sample-config-select-all:disabled {
        opacity: 0.45;
    }

    .acc-sample-config-section-body {
        padding: 0.85rem 0.9rem 0.95rem;
    }

    .acc-sample-config-params-search-row {
        margin-bottom: 0.75rem;
    }

    .acc-sample-config-search {
        max-width: 280px;
    }

    .acc-sample-config-param-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
        gap: 0.5rem;
    }

    .acc-sample-config-param-chip {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        margin: 0;
        padding: 0.55rem 0.65rem;
        border: 1px solid var(--acc-border);
        border-radius: 8px;
        background: #fff;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--acc-text);
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }

    .acc-sample-config-param-chip:hover {
        border-color: var(--color-primary-border-soft);
        background: var(--acc-accent-soft);
    }

    .acc-sample-config-param-chip.is-selected {
        border-color: var(--acc-accent);
        background: var(--acc-accent-soft);
        box-shadow: inset 0 0 0 1px var(--color-primary-highlight);
    }

    .acc-param-lab-section-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .acc-param-lab-section-row {
        display: grid;
        grid-template-columns: minmax(120px, 1fr) minmax(180px, 1.4fr);
        gap: 0.75rem;
        align-items: center;
    }

    .acc-param-lab-section-row--multi {
        align-items: start;
    }

    .acc-param-lab-section-multi {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
        gap: 0.5rem;
    }

    .acc-param-lab-section-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--acc-text);
    }

    .acc-section-analyst-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .acc-section-analyst-block {
        padding: 0.75rem;
        border: 1px solid var(--acc-border);
        border-radius: 10px;
        background: #fff;
    }

    .acc-section-analyst-heading {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
    }

    .acc-section-analyst-heading strong {
        font-size: 0.875rem;
    }

    .acc-section-analyst-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
        gap: 0.5rem;
    }

    .acc-sample-config-param-chip input {
        margin-top: 0.15rem;
        flex-shrink: 0;
        accent-color: var(--acc-accent);
    }

    .acc-sample-config-param-chip span {
        line-height: 1.35;
        word-break: break-word;
    }

    .acc-sample-config-instances-body {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .acc-sample-config-instance-item {
        border: 1px solid var(--acc-border);
        border-radius: 10px;
        padding: 0.75rem 0.85rem;
        background: #f8fafc;
    }

    .acc-sample-config-instance-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--acc-muted);
        margin-bottom: 0.55rem;
    }

    .acc-sample-config-instance-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    @media (max-width: 767.98px) {
        .acc-sample-config-instance-fields {
            grid-template-columns: 1fr;
        }

        .acc-sample-config-table {
            table-layout: auto;
        }
    }

    /* Tags / Select2-style multi-select */
    .acc-param-tags {
        position: relative;
        z-index: 5;
    }

    .acc-param-tags__control {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.25rem;
        min-height: 34px;
        padding: 0.35rem 0.45rem;
        border: 1px solid var(--acc-border);
        border-radius: 8px;
        background: #fff;
        cursor: text;
    }

    .acc-param-tags__control:focus-within {
        border-color: var(--acc-accent);
        box-shadow: 0 0 0 3px var(--ls-color-primary-focus, rgba(128, 0, 0, 0.15));
    }

    .acc-param-tags__control.is-disabled {
        opacity: 0.65;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .acc-analysis-type-tags,
    .acc-sample-type-tags {
        min-width: 10rem;
    }

    .acc-param-tags__chips {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        max-height: 6.5rem;
        overflow-y: auto;
        min-height: 1.25rem;
        padding-top: 0.25rem;
        border-top: 1px solid #eef2f7;
    }

    .acc-param-tags__chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        max-width: 100%;
        padding: 0.15rem 0.35rem 0.15rem 0.45rem;
        border-radius: 999px;
        background: var(--acc-accent-soft);
        border: 1px solid var(--color-primary-border-soft, #e2b4b4);
        color: var(--acc-accent);
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .acc-param-tags__remove {
        border: none;
        background: transparent;
        color: inherit;
        font-size: 1rem;
        line-height: 1;
        padding: 0 0.15rem;
        cursor: pointer;
        opacity: 0.75;
    }

    .acc-param-tags__remove:hover {
        opacity: 1;
    }

    .acc-param-tags__placeholder {
        color: var(--acc-muted);
        font-size: 0.8125rem;
    }

    .acc-param-tags__input {
        display: block;
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.8125rem;
        color: var(--acc-text);
        padding: 0.15rem 0.15rem 0.2rem;
    }

    .acc-param-tags__panel {
        position: absolute;
        z-index: 40;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        max-height: 240px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid var(--acc-border);
        border-radius: 8px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
    }

    .acc-param-tags__panel--floating {
        position: fixed;
        left: auto;
        right: auto;
        top: auto;
        bottom: auto;
        z-index: 2050;
    }

    .acc-param-tags__panel--floating .acc-param-tags__options {
        flex: 1 1 auto;
        max-height: none;
        min-height: 0;
    }

    .acc-param-tags__panel-hint {
        padding: 0.35rem 0.65rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--acc-muted);
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
        flex-shrink: 0;
    }

    .acc-param-tags__options {
        overflow-y: auto;
        max-height: 200px;
        padding: 0.25rem;
    }

    .acc-param-tags__option {
        display: block;
        width: 100%;
        text-align: left;
        border: none;
        background: transparent;
        border-radius: 6px;
        padding: 0.4rem 0.55rem;
        font-size: 0.8125rem;
        color: var(--acc-text);
        cursor: pointer;
    }

    .acc-param-tags__option:hover {
        background: var(--acc-accent-soft);
        color: var(--acc-accent);
    }

    /*
     * Enquiry sample cards must stay overflow:visible so the tags dropdown
     * is not clipped. Fade the card edge with an inset shadow instead of ::after.
     */
    .acc-wizard-root--enquiry .acc-sample-config-card {
        position: relative;
        overflow: visible;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            inset 0 -14px 12px -12px rgba(148, 163, 184, 0.55);
    }

    .acc-wizard-root--enquiry .acc-sample-config-card::after {
        display: none;
    }

    .acc-wizard-root--enquiry .acc-sample-config-table-wrap {
        overflow: visible;
    }

    /* Process Enquiry Step 2 shows only Sample type + Analysis type(s) — span full width. */
    .acc-wizard-root--enquiry .acc-sample-config-table--compact th:nth-child(1),
    .acc-wizard-root--enquiry .acc-sample-config-table--compact td:nth-child(1) {
        width: 32%;
        min-width: 140px;
    }

    .acc-wizard-root--enquiry .acc-sample-config-table--compact th:nth-child(2),
    .acc-wizard-root--enquiry .acc-sample-config-table--compact td:nth-child(2) {
        width: 68%;
        min-width: 220px;
    }

    .acc-wizard-root--enquiry .acc-sample-config-table--compact th:nth-child(3),
    .acc-wizard-root--enquiry .acc-sample-config-table--compact td:nth-child(3),
    .acc-wizard-root--enquiry .acc-sample-config-table--compact th:nth-child(4),
    .acc-wizard-root--enquiry .acc-sample-config-table--compact td:nth-child(4) {
        width: auto;
        min-width: 0;
        max-width: none;
    }

    .acc-wizard-root--enquiry .acc-sample-config-params-row td {
        overflow: visible;
        position: relative;
        z-index: 2;
    }

    .acc-wizard-root--enquiry .acc-sample-config-section-body {
        padding: 0.55rem 0.65rem 0.75rem;
        overflow: visible;
    }

    .acc-wizard-root--enquiry .acc-sample-config-param-grid {
        max-height: 220px;
        overflow-y: auto;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.35rem;
        padding-bottom: 0.35rem;
    }

    .acc-wizard-root--enquiry .acc-sample-config-param-chip {
        padding: 0.35rem 0.45rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }

    body.modal-open {
        overflow: hidden;
    }

    /* Mobile / tablet: wizards become near-fullscreen drawers */
    @media (max-width: 991.98px) {
        .acc-wizard-backdrop {
            align-items: stretch;
            justify-content: stretch;
            padding: 0;
            top: var(--app-header-height, 56px);
            height: calc(100dvh - var(--app-header-height, 56px));
        }

        .acc-wizard-dialog,
        .acc-wizard-dialog.modal-xl,
        .acc-wizard-dialog.modal-lg {
            max-width: 100%;
            width: 100%;
            height: 100%;
            margin: 0;
        }

        .acc-wizard-modal {
            border-radius: 0;
            height: 100%;
            max-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .acc-wizard-body {
            flex: 1 1 auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 0.85rem;
        }

        .acc-wizard-header,
        .acc-wizard-footer,
        .acc-wizard-top-nav {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        .acc-wizard-steps {
            display: flex;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            grid-template-columns: none;
            scrollbar-width: thin;
        }

        .acc-wizard-step {
            flex: 0 0 auto;
            min-width: 7.5rem;
            min-height: 44px;
            padding: 0.65rem 0.75rem;
        }

        .acc-wizard-top-nav,
        .acc-wizard-footer {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .acc-wizard-top-nav-actions,
        .acc-wizard-footer .btn,
        .acc-wizard-top-nav .btn {
            width: 100%;
            justify-content: center;
        }

        .acc-wizard-top-nav-actions {
            margin-left: 0;
            width: 100%;
        }

        .acc-wizard-top-nav-actions .btn {
            flex: 1 1 auto;
        }

        .acc-wizard-fields .col-md-6,
        .acc-wizard-fields .col-md-4,
        .acc-wizard-fields .col-md-3,
        .acc-wizard-summary .col-md-6,
        .acc-wizard-summary .col-md-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .acc-wizard-root .acc-sample-config-param-grid {
            grid-template-columns: 1fr;
        }

        .acc-wizard-close {
            min-width: 44px;
            min-height: 44px;
        }
    }

    @media (max-width: 575.98px) {
        .acc-wizard-step-label {
            font-size: 0.7rem;
        }

        .acc-wizard-title {
            font-size: 1rem;
        }
    }
</style>
