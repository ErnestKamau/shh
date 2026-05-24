    .wf-config {
        --wf-primary: #2563eb;
        --wf-primary-soft: #eff6ff;
        --wf-slate-50: #f8fafc;
        --wf-slate-100: #f1f5f9;
        --wf-slate-200: #e2e8f0;
        --wf-slate-400: #94a3b8;
        --wf-slate-500: #64748b;
        --wf-slate-700: #334155;
        --wf-slate-800: #1e293b;
        --wf-radius: 16px;
        --wf-radius-sm: 10px;
        --wf-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .wf-config__panel {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: var(--wf-radius);
        box-shadow: var(--wf-shadow);
        overflow: hidden;
    }

    .wf-config__tabs-wrap {
        padding: 1rem 1rem 0;
        background: linear-gradient(180deg, var(--wf-slate-50) 0%, #fff 100%);
        border-bottom: 1px solid var(--wf-slate-200);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .wf-config__tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        list-style: none;
        margin: 0;
        padding: 0 0 1rem;
        min-width: min-content;
    }

    .wf-config__tabs .nav-item {
        margin: 0;
    }

    .wf-config-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.9rem;
        border-radius: 999px;
        border: 1px solid var(--wf-slate-200);
        background: #fff;
        color: var(--wf-slate-700);
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none !important;
        white-space: nowrap;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .wf-config-tab:hover {
        border-color: #93c5fd;
        background: var(--wf-primary-soft);
        color: var(--wf-primary);
    }

    .wf-config-tab.active {
        background: var(--wf-primary);
        border-color: var(--wf-primary);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .wf-config-tab.active .wf-config-tab__count {
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
        border-color: transparent;
    }

    .wf-config-tab__icon {
        font-size: 1rem;
        line-height: 1;
        opacity: 0.85;
    }

    .wf-config-tab.active .wf-config-tab__icon {
        opacity: 1;
    }

    .wf-config-tab__count {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: var(--wf-slate-100);
        color: var(--wf-slate-500);
        border: 1px solid var(--wf-slate-200);
    }

    .wf-config__body {
        padding: 1.5rem 1.5rem 1.25rem;
    }

    .wf-config__intro {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px dashed var(--wf-slate-200);
    }

    .wf-config__intro-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--wf-slate-800);
        margin: 0 0 0.35rem;
    }

    .wf-config__intro-desc {
        margin: 0;
        font-size: 0.875rem;
        color: var(--wf-slate-500);
        max-width: 42rem;
        line-height: 1.5;
    }

    .wf-config__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .wf-config__code {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        background: var(--wf-slate-100);
        color: var(--wf-slate-700);
        border: 1px solid var(--wf-slate-200);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .wf-config__categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.65rem;
    }

    .wf-config__category-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .wf-timeline {
        list-style: none;
        margin: 0;
        padding: 0.25rem 0 0 0.25rem;
    }

    .wf-timeline__item {
        display: flex;
        gap: 1rem;
        position: relative;
        padding-bottom: 1.5rem;
    }

    .wf-timeline__item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 17px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--wf-slate-200) 0%, #dbeafe 100%);
    }

    .wf-timeline__item:last-child {
        padding-bottom: 0;
    }

    .wf-timeline__marker {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.8rem;
        font-weight: 800;
        z-index: 1;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px var(--wf-slate-200);
    }

    .wf-timeline__marker--start {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .wf-timeline__marker--middle {
        background: #f1f5f9;
        color: #475569;
    }

    .wf-timeline__marker--final {
        background: #d1fae5;
        color: #047857;
    }

    .wf-timeline__card {
        flex: 1;
        min-width: 0;
        background: var(--wf-slate-50);
        border: 1px solid var(--wf-slate-200);
        border-radius: var(--wf-radius-sm);
        padding: 0.85rem 1rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .wf-timeline__item:hover .wf-timeline__card {
        border-color: #bfdbfe;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);
    }

    .wf-timeline__header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .wf-timeline__name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--wf-slate-800);
        margin: 0;
    }

    .wf-timeline__badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
    }

    .wf-timeline__role {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: #fff;
        color: var(--wf-slate-600);
        border: 1px solid var(--wf-slate-200);
    }

    .wf-timeline__final {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #d1fae5;
    }

    .wf-timeline__footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--wf-slate-500);
    }

    .wf-timeline__arrow {
        color: var(--wf-slate-400);
        font-size: 1rem;
    }

    .wf-config__empty {
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .wf-config__empty-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        border-radius: 14px;
        background: var(--wf-primary-soft);
        color: var(--wf-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    @media (max-width: 575.98px) {
        .wf-config__body {
            padding: 1rem;
        }
        .wf-config-tab {
            font-size: 0.75rem;
            padding: 0.4rem 0.7rem;
        }
    }
