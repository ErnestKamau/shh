<style>
    .qc-page {
        padding-left: 15px;
        padding-right: 15px;
    }

    .qc-page > .d-flex:first-of-type {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 14px;
        padding: 0.9rem 1rem;
    }

    .qc-page .card {
        border: 1px solid #e3e8ef;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
    }

    .qc-page .card .card-header {
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
    }

    .qc-page .card .card-footer {
        border-bottom-left-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .qc-page .qc-table-card .card-body {
        padding: 0;
    }

    .qc-page .qc-table-wrap {
        border-radius: 14px;
        overflow: hidden;
    }

    .qc-page .table {
        margin-bottom: 0;
    }

    .qc-page .table thead th {
        vertical-align: middle;
    }

    .qc-page .table td {
        vertical-align: middle;
    }

    .qc-config-page .qc-config-search-wrap {
        min-width: 280px;
    }

    .qc-config-page .qc-config-tabs {
        gap: 0.45rem;
    }

    .qc-config-page .qc-config-tabs .nav-link {
        border: 1px solid #d6dde8;
        border-radius: 10px;
        color: #243447;
        background: #f8fafc;
        font-weight: 600;
        padding: 0.45rem 1rem;
    }

    .qc-config-page .qc-config-tabs .nav-link.active {
        background: var(--color-primary-soft);
        border-color: var(--color-primary-border-soft);
        color: var(--color-primary);
    }

    .qc-config-page .qc-table-card > .card-body {
        padding: 1rem;
    }

    .qc-config-page .qc-inline-form {
        background: #fbfdff;
        border-color: #dde5f0 !important;
        border-radius: 12px !important;
    }

    .qc-config-page .qc-inline-form strong {
        color: #13263b;
    }

    .qc-config-page .table thead th {
        background: #f4f7fb;
        color: #3b4f68;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .qc-config-page .table td .btn {
        margin-right: 0.35rem;
        margin-bottom: 0.25rem;
    }

    .qc-config-page .table td .btn:last-child {
        margin-right: 0;
    }

    @media (max-width: 767.98px) {
        .qc-config-page .qc-config-search-wrap {
            min-width: 0;
            width: 100%;
            margin-top: 0.75rem;
        }

        .qc-config-page > .d-flex:first-of-type {
            flex-wrap: wrap;
            align-items: stretch !important;
        }
    }
</style>
