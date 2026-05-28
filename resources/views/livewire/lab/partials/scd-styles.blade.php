<style>
    :root {
        --scd-primary: #2563eb;
        --scd-primary-soft: #eff6ff;
        --scd-slate-50: #f8fafc;
        --scd-slate-100: #f1f5f9;
        --scd-slate-200: #e2e8f0;
        --scd-slate-800: #1e293b;
        --scd-radius-sm: 8px;
    }
    .scd-hero { border-radius: 12px; background: #fff; }
    .scd-eyebrow { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; }
    .scd-title { font-size: 1.5rem; font-weight: 700; color: var(--scd-slate-800); }
    .scd-subtitle { color: #64748b; font-size: 0.95rem; }
    .scd-back-btn { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--scd-slate-200); color: var(--scd-primary); text-decoration: none; }
    .scd-panel { border-radius: 12px; overflow: hidden; }
    .scd-panel__head { display: flex; align-items: center; gap: 12px; padding: 1rem 1.25rem; background: var(--scd-slate-50); border-bottom: 1px solid var(--scd-slate-200); }
    .scd-panel__head--split { justify-content: space-between; }
    .scd-panel__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--scd-primary-soft); color: var(--scd-primary); display: flex; align-items: center; justify-content: center; }
    .scd-label { font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 0.35rem; }
    .scd-input { border-radius: var(--scd-radius-sm); border-color: var(--scd-slate-200); }
    .scd-tabs .nav-link { border: none; color: #64748b; font-weight: 500; }
    .scd-tabs .nav-link.active { color: var(--scd-primary); border-bottom: 2px solid var(--scd-primary); background: transparent; }
    .scd-modal-backdrop { background: rgba(15, 23, 42, 0.45); }
    .scd-status { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 0.82rem; }
    .scd-status--active { background: #ecfdf5; color: #047857; }
    .scd-status--inactive { background: #fef2f2; color: #b91c1c; }
    .scd-status--preparing { background: #fef3c7; color: #b45309; }
    .scd-status--awaiting { background: #dbeafe; color: #1d4ed8; }
    .scd-status--completed { background: #ecfdf5; color: #047857; }
    .scd-status--cancelled { background: #f1f5f9; color: #64748b; }
</style>
