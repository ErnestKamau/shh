<style>
    #add-quotation {
        --quote-modal-accent: #8b1538;
        --quote-modal-accent-hover: #6f102d;
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
        padding: 1.25rem 1.5rem 1.5rem;
        background: #f8fafc;
    }

    #add-quotation .ls-form-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
    }

    #add-quotation .ls-form-panel__title {
        margin: 0 0 0.85rem;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #1e293b;
    }

    #add-quotation .ls-form-panel__title .mdi {
        color: var(--quote-modal-accent);
    }

    #add-quotation .ls-field {
        margin-bottom: 0;
        min-width: 0;
    }

    /* Preselected search-basic without green success chrome */
    #add-quotation .ls-search-basic.is-success .ls-field__control,
    #add-quotation .ls-search-basic[data-ls-disable-success="1"].is-success .ls-field__control {
        border-color: var(--ls-border, #e2e8f0) !important;
        box-shadow: none !important;
        background: #fff !important;
    }

    /*
     * Gallery Select2 only — do NOT restyle selection borders here.
     * Theme + gallery previously stacked container + selection borders.
     */
    #add-quotation .ls-select2-multi .select2-container,
    #add-quotation .ls-select2-single .select2-container,
    #add-quotation .select2-container.select2-container--open:not(.select2) {
        border: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
    }

    #add-quotation .ls-select2-multi .select2-container--default .select2-selection--multiple,
    #add-quotation .ls-select2-multi .select2-container--default .select2-selection--single {
        min-height: 38px !important;
        height: auto !important;
        border: 1px solid var(--ls-border, var(--quote-modal-slate-200)) !important;
        border-radius: var(--ls-radius, 8px) !important;
        background: #fff !important;
        padding: 0 !important;
        box-shadow: none !important;
    }

    #add-quotation .ls-select2-multi .select2-selection__rendered {
        min-height: 0 !important;
        padding: 0.25rem 0.4rem !important;
    }

    #add-quotation .ls-select2-multi .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 0.55rem !important;
        padding-right: 1.75rem !important;
        font-size: 0.8125rem !important;
    }

    #add-quotation .ls-select2-multi .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
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

    @media (max-width: 767.98px) {
        #add-quotation .ls-form-grid--3 {
            grid-template-columns: 1fr;
        }
    }
</style>
