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

    .gw-capture-sidebar {
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
    }

    .gw-capture-sidebar-head {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .gw-capture-nav-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1rem;
        border: 0;
        background: transparent;
        font-size: 0.8rem;
        width: 100%;
        text-align: left;
    }

    .gw-capture-nav-item.active {
        background: #fff;
        color: #2563eb;
        font-weight: 600;
        border-left: 3px solid #3b82f6;
    }

    .gw-capture-nav-order {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.25rem;
        height: 1.25rem;
        border-radius: 999px;
        background: #e2e8f0;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .gw-capture-nav-item.active .gw-capture-nav-order {
        background: #3b82f6;
        color: #fff;
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

    .grouped-wizard-pipeline-nav .list-group-item.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-color: #2563eb;
        color: #fff;
    }

    .grouped-wizard-pipeline-nav .list-group-item.active .mdi {
        color: #fff !important;
    }
</style>
