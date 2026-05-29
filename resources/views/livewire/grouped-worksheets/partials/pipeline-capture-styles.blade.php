<style>
    .gw-capture-preview {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    }

    .gw-capture-preview-banner {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1rem;
        background: linear-gradient(90deg, #eff6ff, #f8fafc);
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.8rem;
        color: #475569;
    }

    .gw-capture-layout {
        min-height: 320px;
    }

    .gw-capture-main-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }

    .gw-capture-context {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        padding: 0.65rem 1.15rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .gw-capture-context-chip {
        font-size: 0.75rem;
        padding: 0.2rem 0.55rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
    }

    .gw-capture-body {
        padding: 1rem 1.15rem 1.25rem;
        max-height: none;
        overflow: visible;
    }

    .gw-capture-body--live {
        padding: 0;
        background: #fff;
    }

    .gw-inner-timeline {
        position: relative;
        padding-left: 2.5rem;
    }

    .gw-inner-timeline::before {
        content: '';
        position: absolute;
        left: 0.9rem;
        top: 0.5rem;
        bottom: 0.5rem;
        width: 2px;
        background: #e2e8f0;
    }

    .gw-inner-timeline-item {
        position: relative;
        margin-bottom: 1rem;
    }

    .gw-inner-timeline-item:last-child {
        margin-bottom: 0;
    }

    .gw-inner-timeline-marker {
        position: absolute;
        left: -2.5rem;
        top: 0.65rem;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #64748b, #94a3b8);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .gw-inner-timeline-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.65rem;
        padding: 0.85rem 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .gw-capture-badge {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    /* ── Capture preview field base ──────────────────────────── */
    .gw-field {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        padding: 0.45rem 0.65rem;
        width: 100%;
    }

    .gw-field__icon {
        font-size: 1rem;
        flex-shrink: 0;
        opacity: 0.55;
    }

    .gw-field__placeholder {
        color: #94a3b8;
        font-style: italic;
    }

    /* Text / numeric input */
    .gw-field--input {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
        padding: 0.35rem 0.65rem;
    }

    .gw-field--input:disabled {
        background: #f8fafc;
        opacity: 1;
    }

    /* Readonly / calculated */
    .gw-field--readonly {
        background: #f1f5f9;
        border: 1px dashed #cbd5e1;
        color: #64748b;
        font-style: italic;
    }

    .gw-field--readonly .gw-field__icon {
        color: #94a3b8;
    }

    /* Posted result */
    .gw-field--result {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .gw-field--result .gw-field__icon {
        color: #22c55e;
        opacity: 1;
    }

    /* Static text */
    .gw-field--static {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        align-items: flex-start;
        white-space: pre-wrap;
        line-height: 1.5;
        color: #475569;
    }

    .gw-field--static .gw-field__icon {
        margin-top: 0.1rem;
        color: #64748b;
    }

    .gw-field__static-text {
        white-space: pre-wrap;
        word-break: break-word;
    }

    /* Capture panel (stage header / sequences) */
    .gw-field--capture {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .gw-field--capture .gw-field__icon {
        color: #3b82f6;
        opacity: 1;
    }

    /* Select dropdown */
    .gw-field--select {
        border: 1px solid #e2e8f0;
        background: #fff;
        justify-content: space-between;
        color: #94a3b8;
    }

    .gw-select-options-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .gw-select-option-chip {
        display: inline-block;
        font-size: 0.7rem;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
    }

    .gw-select-option-chip--more {
        background: #e2e8f0;
        color: #64748b;
    }

    /* Checkbox list */
    .gw-field--checkbox-list {
        flex-wrap: wrap;
        gap: 0.35rem;
        background: #fafafa;
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0.65rem;
        min-height: 2.25rem;
    }

    .gw-checkbox-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        padding: 0.2rem 0.55rem 0.2rem 0.35rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #374151;
        cursor: default;
    }

    .gw-checkbox-chip__input {
        width: 0.85rem;
        height: 0.85rem;
        flex-shrink: 0;
        cursor: not-allowed;
    }

    /* Custom table skeleton */
    .gw-field--table {
        padding: 0;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        display: block;
    }

    .gw-table-preview {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.78rem;
    }

    .gw-table-preview thead th {
        background: #f1f5f9;
        padding: 0.3rem 0.55rem;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        white-space: nowrap;
    }

    .gw-table-preview__index {
        width: 2rem;
        text-align: center;
        color: #94a3b8;
        border-right: 1px solid #e2e8f0;
    }

    .gw-table-preview tbody td {
        padding: 0.3rem 0.55rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .gw-table-preview tbody tr:last-child td {
        border-bottom: 0;
    }

    .gw-table-preview__cell-placeholder {
        height: 0.65rem;
        border-radius: 4px;
        background: #e2e8f0;
        min-width: 2.5rem;
    }

    /* PCR plate */
    .gw-field--plate {
        flex-direction: column;
        align-items: flex-start;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.65rem 0.75rem;
        gap: 0.5rem;
    }

    .gw-plate-header {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
    }

    .gw-plate-header .mdi {
        color: #3b82f6;
    }

    .gw-plate-qc-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }

    .gw-plate-chip {
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        font-weight: 600;
    }

    .gw-plate-chip--std {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1d4ed8;
    }

    .gw-plate-chip--control {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        color: #92400e;
    }

    .gw-plate-chip--buffer {
        background: #f5f3ff;
        border: 1px solid #c4b5fd;
        color: #5b21b6;
    }

    .gw-plate-grid-mini {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 0.25rem;
    }

    .gw-plate-grid-mini__row {
        display: flex;
        gap: 2px;
    }

    .gw-plate-grid-mini__cell {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        background: #e2e8f0;
        border: 1px solid #cbd5e1;
        flex-shrink: 0;
    }

    .gw-plate-grid-mini__more {
        font-size: 0.65rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* Results capture matrix */
    .gw-results-matrix-wrap {
        max-height: 520px;
        overflow-x: auto;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
    }

    .grouped-results-capture .gw-results-matrix {
        font-size: 0.8125rem;
        margin-bottom: 0;
        table-layout: fixed;
        width: max-content;
        min-width: 100%;
    }

    .grouped-results-capture .gw-results-matrix thead th {
        background: #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 2;
        vertical-align: middle;
        padding: 0.5rem 0.65rem;
    }

    .grouped-results-capture .gw-results-matrix__sticky-col {
        position: sticky;
        left: 0;
        z-index: 3;
        background: #fff;
        width: 160px;
        min-width: 160px;
        max-width: 220px;
        vertical-align: middle;
        padding: 0.5rem 0.65rem;
        box-shadow: 2px 0 4px rgba(15, 23, 42, 0.04);
    }

    .grouped-results-capture .gw-results-matrix thead .gw-results-matrix__sticky-col {
        z-index: 4;
        background: #f1f5f9;
    }

    .grouped-results-capture .gw-results-matrix__sample-col {
        width: 180px;
        min-width: 180px;
    }

    .grouped-results-capture .gw-results-matrix__sample-code {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.25;
        white-space: nowrap;
    }

    .grouped-results-capture .gw-results-matrix__sample-sub {
        font-size: 0.6875rem;
        font-weight: 600;
        color: #64748b;
        line-height: 1.2;
        margin-top: 0.15rem;
        white-space: nowrap;
    }

    .grouped-results-capture .gw-results-matrix__cell {
        width: 180px;
        min-width: 180px;
        vertical-align: middle;
        padding: 0.35rem;
    }

    .grouped-results-capture .gw-results-matrix__cell-inputs {
        display: grid;
        grid-template-columns: 3.25rem minmax(0, 1fr);
        column-gap: 0.35rem;
        align-items: center;
        width: 100%;
    }

    .grouped-results-capture .gw-results-matrix__cell-inputs .form-control {
        width: 100% !important;
        min-width: 0;
        max-width: 100%;
    }

    .grouped-results-capture .gw-results-matrix__symbol {
        grid-column: 1;
        font-size: 0.75rem;
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }

    .grouped-results-capture .gw-results-matrix__result {
        grid-column: 2;
        font-size: 0.75rem;
    }

    .grouped-results-capture .gw-results-capture__steps {
        border-bottom: none;
    }

    .grouped-results-capture .gw-results-capture__step {
        cursor: pointer;
        border-radius: 0.375rem;
        padding: 0.4rem 0.9rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .grouped-results-capture .gw-results-capture__step:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .grouped-results-capture .gw-results-capture__step--active,
    .grouped-results-capture .gw-results-capture__step--active:hover {
        background: #eef2ff;
        color: #3730a3;
        border-color: #c7d2fe;
    }

    .grouped-results-capture .gw-results-capture__comments .tox-tinymce {
        border-radius: 8px !important;
    }

    .grouped-results-capture .gw-results-capture__status {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    .grouped-results-capture .gw-results-capture__badge {
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.4rem 0.75rem;
        border-radius: 0.375rem;
    }

    .grouped-results-capture .gw-results-capture__badge--posted {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }

    .grouped-results-capture .gw-results-capture__badge--draft {
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
</style>
