<style>
    .pcr-plate-shell {
        max-width: 100%;
    }

    .pcr-plate-grid {
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
        width: 100%;
        min-width: 720px;
        background: #fff;
    }

    .pcr-plate-grid__corner,
    .pcr-plate-grid__col-head,
    .pcr-plate-grid__row-head {
        background: #e8f4fc;
        color: #1e3a5f;
        font-weight: 700;
        font-size: 0.75rem;
        text-align: center;
        vertical-align: middle;
        padding: 0.35rem 0.25rem;
        border-color: #cbd5e1 !important;
    }

    .pcr-plate-grid__corner {
        width: 2rem;
    }

    .pcr-plate-grid__cell {
        padding: 2px !important;
        border-color: #e2e8f0 !important;
        vertical-align: middle;
    }

    .pcr-well {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 2.5rem;
        padding: 0.2rem 0.15rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #f8fafc;
        font-size: 0.68rem;
        line-height: 1.2;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }

    .pcr-plate-shell--interactive .pcr-well {
        cursor: pointer;
    }

    .pcr-plate-shell--interactive .pcr-well:hover {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .pcr-well--active {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.35) !important;
    }

    .pcr-well__coord {
        color: #64748b;
        font-weight: 600;
        font-size: 0.62rem;
    }

    .pcr-well__value {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.72rem;
        margin-top: 0.1rem;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pcr-well--sample {
        background: #ecfdf5;
        border-color: #6ee7b7;
    }

    .pcr-well--std {
        background: #eff6ff;
        border-color: #93c5fd;
    }

    .pcr-well--control {
        background: #fef3c7;
        border-color: #fcd34d;
    }

    .pcr-well--buffer {
        background: #f5f3ff;
        border-color: #c4b5fd;
    }

    .pcr-well--preview {
        min-height: 1.75rem;
    }

    .pcr-plate-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        margin-top: 0.75rem;
        font-size: 0.8rem;
        color: #475569;
    }

    .pcr-plate-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .pcr-plate-legend__swatch {
        width: 0.85rem;
        height: 0.85rem;
        border-radius: 4px;
        border: 1px solid rgba(0, 0, 0, 0.08);
    }

    .pcr-chip-list__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.25rem;
        background: #f1f5f9;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .pcr-well-editor {
        margin-top: 1rem;
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .pcr-well-editor__title {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .pcr-kind-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
    }

    .pcr-kind-tab {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s;
    }

    .pcr-kind-tab.is-active {
        border-color: #2563eb;
        background: #2563eb;
        color: #fff;
    }

    .pcr-quick-picks {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.5rem;
    }

    .pcr-quick-pick {
        font-size: 0.78rem;
        padding: 0.2rem 0.55rem;
    }
</style>
