<style>
    .acc-wizard-root {
        --acc-accent: #3b5fc0;
        --acc-accent-dark: #2f4da0;
        --acc-accent-soft: #eef2ff;
        --acc-border: #e2e8f0;
        --acc-muted: #64748b;
        --acc-text: #0f172a;
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
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--acc-muted);
        margin-bottom: 0.15rem;
    }

    .acc-wizard-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
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
        font-size: 0.8rem;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        transition: color 0.15s, border-color 0.15s, background 0.15s;
    }

    .acc-wizard-step:hover:not(:disabled) {
        color: var(--acc-accent);
        background: rgba(59, 95, 192, 0.06);
    }

    .acc-wizard-step.is-active {
        color: var(--acc-accent);
        border-bottom-color: var(--acc-accent);
        background: #fff;
    }

    .acc-wizard-step.is-done {
        color: #059669;
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
        font-size: 0.75rem;
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
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--acc-muted);
        margin-bottom: 0.85rem;
    }

    .acc-wizard-hint {
        font-size: 0.8rem;
        color: var(--acc-muted);
    }

    .acc-wizard-fields > [class*="col-"] {
        margin-bottom: 0.75rem;
    }

    .acc-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--acc-muted);
        margin-bottom: 0.35rem;
    }

    .acc-input {
        border-radius: 8px;
        border-color: var(--acc-border);
        font-size: 0.9rem;
    }

    .acc-input:focus {
        border-color: var(--acc-accent);
        box-shadow: 0 0 0 3px rgba(59, 95, 192, 0.15);
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

    body.modal-open {
        overflow: hidden;
    }
</style>
