<style>
    .cpo-stepper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .cpo-stepper__item { flex: 1 1 180px; }

    .cpo-stepper__btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.6rem 0.8rem;
        border: 1px solid var(--ls-border, #e2e8f0);
        border-radius: 10px;
        background: #ffffff;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: left;
    }

    .cpo-stepper__btn:disabled { cursor: default; }

    .cpo-stepper__dot {
        flex: 0 0 26px;
        height: 26px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.8rem;
    }

    .cpo-stepper__item.is-current .cpo-stepper__btn {
        border-color: var(--color-primary, #8b1e2d);
        color: var(--color-primary, #8b1e2d);
        box-shadow: 0 0 0 3px rgba(139, 30, 45, 0.08);
    }

    .cpo-stepper__item.is-current .cpo-stepper__dot {
        background: var(--color-primary, #8b1e2d);
        color: #ffffff;
    }

    .cpo-stepper__item.is-done .cpo-stepper__btn { color: #0f766e; cursor: pointer; }
    .cpo-stepper__item.is-done .cpo-stepper__dot { background: #ccfbf1; color: #0f766e; }

    .cpo-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid var(--ls-border, #e2e8f0);
    }

    .cpo-chip__clear {
        border: 0;
        background: transparent;
        color: #94a3b8;
        padding: 0;
        line-height: 1;
    }

    .cpo-dropdown {
        position: absolute;
        z-index: 20;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        max-height: 260px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid var(--ls-border, #e2e8f0);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }

    .cpo-dropdown__item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .cpo-dropdown__item:hover { background: #f8fafc; }

    .cpo-error { color: #b91c1c; font-size: 0.78rem; margin-top: 0.25rem; }
    .cpo-help { color: #64748b; font-size: 0.76rem; margin-top: 0.25rem; }

    .cpo-note {
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        padding: 0.45rem 0.65rem;
        border-radius: 8px;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .cpo-note--warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .cpo-note--danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .cpo-note--info { background: #f0f9ff; color: #075985; border: 1px solid #bae6fd; }

    .cpo-review {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 0.45rem 0.75rem;
        margin: 0;
        font-size: 0.875rem;
    }

    .cpo-review dt { color: #64748b; font-weight: 600; }
    .cpo-review dd { margin: 0; color: var(--ls-ink, #0f172a); }

    .cpo-status {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .cpo-status--active { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .cpo-status--exhausted { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .cpo-status--expired { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .cpo-status--closed,
    .cpo-status--cancelled,
    .cpo-status--skipped { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

    .cpo-balance-bar {
        display: flex;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
    }

    .cpo-balance-bar__seg--committed { background: var(--color-primary, #8b1e2d); }
    .cpo-balance-bar__seg--invoiced { background: #0f766e; }
    .cpo-balance-bar__seg--reserved { background: #f59e0b; }
    .cpo-balance-bar__seg--remaining { background: #e2e8f0; }

    .cpo-balance-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.9rem;
        margin-top: 0.4rem;
        font-size: 0.76rem;
        color: #475569;
    }

    .cpo-balance-legend span::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 2px;
        margin-right: 0.3rem;
        vertical-align: middle;
        background: var(--cpo-legend, #e2e8f0);
    }

    .cpo-line-card {
        border: 1px solid var(--ls-border, #e2e8f0);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
        background: #ffffff;
    }

    .cpo-line-card.is-exhausted { border-color: #fed7aa; background: #fffaf5; }
    .cpo-line-card.is-low { border-color: #fde68a; }

    .cpo-timeline { list-style: none; padding: 0; margin: 0; }

    .cpo-timeline__item {
        position: relative;
        padding: 0 0 0.85rem 1.4rem;
        border-left: 2px solid #e2e8f0;
        margin-left: 0.4rem;
        font-size: 0.84rem;
    }

    .cpo-timeline__item:last-child { border-left-color: transparent; padding-bottom: 0; }

    .cpo-timeline__item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 3px;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--cpo-dot, #94a3b8);
        border: 2px solid #ffffff;
    }

    .cpo-timeline__meta { color: #64748b; font-size: 0.75rem; }

    .cpo-modal-backdrop {
        background: rgba(15, 23, 42, 0.45);
        z-index: 1065;
    }

    .cpo-qty { font-variant-numeric: tabular-nums; }
</style>
