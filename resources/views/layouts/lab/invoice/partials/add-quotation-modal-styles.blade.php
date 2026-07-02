<style>
    #add-quotation {
        --quote-modal-accent: #8b1538;
        --quote-modal-accent-hover: #6f102d;
        --quote-modal-slate-500: #64748b;
        --quote-modal-slate-200: #e2e8f0;
    }

    #add-quotation .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
    }

    #add-quotation .quotation-modal-header {
        background: linear-gradient(135deg, var(--quote-modal-accent) 0%, #a61d45 100%);
        color: #fff;
        border: 0;
        padding: 1.25rem 1.5rem;
    }

    #add-quotation .quotation-modal-header .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
    }

    #add-quotation .quotation-modal-header .modal-subtitle {
        font-size: 0.85rem;
        opacity: 0.9;
        margin: 0.25rem 0 0;
    }

    #add-quotation .quotation-modal-body {
        padding: 1.5rem;
        background: #fafbfc;
    }

    #add-quotation .soft-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--quote-modal-slate-500);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.35rem;
    }

    #add-quotation .modern-input,
    #add-quotation .modern-select {
        border-radius: 10px;
        border: 1px solid var(--quote-modal-slate-200);
        min-height: 42px;
        font-size: 0.95rem;
    }

    #add-quotation .modern-input:focus,
    #add-quotation .modern-select:focus {
        border-color: var(--quote-modal-accent);
        box-shadow: 0 0 0 3px rgba(139, 21, 56, 0.12);
    }

    #add-quotation .modern-input[readonly] {
        background-color: #f1f5f9;
    }

    #add-quotation .quotation-modal-footer {
        border-top: 1px solid #eef2f7;
        background: #fff;
        padding: 1rem 1.5rem;
    }

    #add-quotation .btn-quotation-primary {
        background: var(--quote-modal-accent);
        border-color: var(--quote-modal-accent);
        color: #fff;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
    }

    #add-quotation .btn-quotation-primary:hover {
        background: var(--quote-modal-accent-hover);
        border-color: var(--quote-modal-accent-hover);
        color: #fff;
    }

    #add-quotation .btn-quotation-secondary {
        border-radius: 10px;
        font-weight: 600;
    }
</style>
