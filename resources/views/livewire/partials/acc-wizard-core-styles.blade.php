<style>
    .acc-wizard-root {
        --acc-accent: var(--color-primary, var(--color-primary));
        --acc-accent-dark: var(--color-primary-hover, var(--color-primary-hover));
        --acc-accent-soft: var(--color-primary-soft, var(--color-primary-soft));
        --acc-border: #e5e7eb;
        --acc-muted: #6b7280;
        --acc-text: #111827;
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
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
    }

    .acc-wizard-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: #fff;
        color: var(--acc-text);
        border-bottom: 1px solid var(--acc-border);
    }

    .acc-wizard-eyebrow {
        display: block;
        font-size: var(--text-caption);
        font-weight: var(--font-semibold);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--acc-muted);
        margin-bottom: 0.15rem;
    }

    .acc-wizard-title {
        margin: 0;
        font-size: var(--text-xl);
        font-weight: var(--font-bold);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .acc-wizard-close {
        border: none;
        background: #f1f5f9;
        color: var(--acc-muted);
        width: 36px;
        height: 36px;
        border-radius: 10px;
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
        gap: 0.5rem;
        padding: 0.85rem 0.5rem;
        border: none;
        background: transparent;
        color: var(--acc-muted);
        font-size: var(--text-sm);
        font-weight: var(--font-semibold);
        border-bottom: 3px solid transparent;
        transition: color 0.15s, border-color 0.15s, background 0.15s;
    }

    .acc-wizard-step:hover:not(:disabled) {
        color: #111827;
        background: var(--acc-accent-soft);
    }

    .acc-wizard-step.is-active {
        color: #111827;
        border-bottom-color: var(--acc-accent);
        background: #fff;
    }

    .acc-wizard-step.is-active .acc-wizard-step-label {
        color: #111827;
    }

    .acc-wizard-step.is-done {
        color: var(--color-muted, #64748b);
    }

    .acc-wizard-step:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .acc-wizard-step-index {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-caption);
        background: #e2e8f0;
        color: var(--acc-muted);
    }

    .acc-wizard-step.is-active .acc-wizard-step-index {
        background: var(--acc-accent);
        color: #fff;
    }

    .acc-wizard-step.is-done .acc-wizard-step-index {
        background: #d1fae5;
        color: #059669;
    }

    .acc-wizard-body {
        padding: 1.25rem 1.5rem 1rem;
        background: #f8fafc;
        max-height: min(70vh, 640px);
        overflow-y: auto;
        color: var(--acc-text);
    }

    .acc-wizard-section {
        background: #fff;
        border: 1px solid var(--acc-border);
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1rem;
    }

    .acc-wizard-section:last-child {
        margin-bottom: 0;
    }

    .acc-wizard-section-title {
        font-size: var(--text-caption);
        font-weight: var(--font-semibold);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--acc-muted);
        margin-bottom: 0.85rem;
    }

    .acc-wizard-hint {
        font-size: var(--text-caption);
        color: var(--acc-muted);
    }

    .acc-wizard-fields > [class*="col-"] {
        margin-bottom: 0.75rem;
    }

    .acc-label {
        font-size: var(--text-caption);
        font-weight: var(--font-semibold);
        color: var(--acc-muted);
        margin-bottom: 0.35rem;
    }

    .acc-input {
        border-radius: 8px;
        border-color: var(--acc-border);
        font-size: var(--text-sm);
    }

    .acc-input:focus {
        border-color: var(--acc-accent);
        box-shadow: 0 0 0 3px var(--color-primary-highlight);
    }

    .acc-wizard-footer {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.35rem;
        background: #fff;
        border-top: 1px solid var(--acc-border);
    }

    .acc-pricing-toolbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .acc-pricing-table-wrap {
        border: 1px solid var(--acc-border);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .acc-pricing-table thead th {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
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
        padding: 1.25rem 1.35rem 1.1rem;
    }

    .acc-sample-config-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem !important;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eef2f7;
    }

    .acc-sample-config-toolbar-actions {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.5rem;
    }

    .acc-sample-config-sync-btn {
        width: 2rem;
        height: 2rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .acc-btn-add {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0.35rem 0.85rem;
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
        border-radius: 8px;
        color: #be123c;
        border-color: #fecdd3;
        background: #fff5f7;
        padding: 0.2rem 0.45rem;
    }

    .acc-btn-remove:hover {
        color: #9f1239;
        background: #ffe4e6;
        border-color: #fda4af;
    }

    .acc-sample-config-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .acc-sample-config-card {
        border: 1px solid var(--acc-border);
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .acc-sample-config-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 1rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--acc-border);
    }

    .acc-sample-config-card-title {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--acc-text);
    }

    .acc-sample-config-card-title .mdi {
        color: var(--acc-accent);
        font-size: 1.05rem;
    }

    .acc-sample-config-table-wrap {
        overflow-x: auto;
    }

    .acc-sample-config-table {
        table-layout: fixed;
        width: 100%;
        margin: 0;
    }

    .acc-sample-config-table thead th {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--acc-muted);
        background: #fff;
        border-bottom: 1px solid var(--acc-border);
        padding: 0.65rem 0.75rem;
        vertical-align: bottom;
        white-space: nowrap;
    }

    .acc-sample-config-table tbody td {
        border-top: none;
        padding: 0.75rem;
        vertical-align: top;
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
        width: 14%;
        min-width: 90px;
    }

    .acc-sample-config-table--acceptance .acc-col-analysis-type {
        width: 14%;
        min-width: 90px;
    }

    .acc-sample-config-table--acceptance .acc-col-lab-section {
        width: 14%;
        min-width: 100px;
    }

    .acc-sample-config-table--acceptance .acc-col-condition {
        width: 10%;
        min-width: 88px;
    }

    .acc-sample-config-table--acceptance .acc-col-main-standard {
        width: 14%;
        min-width: 100px;
    }

    .acc-sample-config-table--acceptance .acc-col-lab {
        width: 12%;
        min-width: 80px;
    }

    .acc-sample-config-table--acceptance .acc-col-assigned-user {
        width: 14%;
        min-width: 100px;
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
        overflow: hidden;
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

    body.modal-open {
        overflow: hidden;
    }
</style>
