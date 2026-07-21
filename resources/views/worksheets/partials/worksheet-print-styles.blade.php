<style>
    .worksheet-print-toolbar {
        margin-bottom: 1rem;
    }

    .worksheet-print-header {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #333;
    }

    .worksheet-print-header h1 {
        font-size: 1.25rem;
        margin: 0 0 0.25rem;
    }

    .worksheet-print-header p {
        margin: 0;
        color: #555;
        font-size: 0.9rem;
    }

    .worksheet-meta-table th,
    .worksheet-meta-table td {
        font-size: 0.85rem;
        vertical-align: top;
    }

    .worksheet-meta-table thead th {
        background: #f1f5f9;
    }

    .worksheet-print-section-title {
        font-size: 1rem;
        font-weight: 700;
        margin: 1rem 0 0.5rem;
    }

    .worksheet-print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .worksheet-print-table th,
    .worksheet-print-table td {
        border: 1px solid #cbd5e1;
        padding: 0.4rem 0.5rem;
        vertical-align: top;
    }

    .worksheet-print-table thead th {
        background: #f8fafc;
    }

    @media print {
        .worksheet-print-toolbar,
        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .worksheet-print-shell {
            padding: 0 !important;
        }
    }
</style>
