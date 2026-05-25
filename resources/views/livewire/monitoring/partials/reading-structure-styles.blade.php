<style>
.fs-modal { position: fixed; inset: 0; z-index: 1055; display: flex; align-items: center; justify-content: center; padding: 1.25rem; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px); overflow-y: auto; }
.fs-modal-dialog { margin: auto; width: 100%; max-width: 920px; max-height: calc(100vh - 2.5rem); display: flex; }
.fs-modal-content { border: none; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.28); display: flex; flex-direction: column; max-height: calc(100vh - 2.5rem); overflow: hidden; }
.fs-modal-header { flex-shrink: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; }
.fs-modal-subtitle { font-size: 0.8rem; color: #64748b; }
.fs-modal-close { flex-shrink: 0; background: transparent; border: 0; font-size: 1.5rem; color: #64748b; cursor: pointer; }
.fs-step-form { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; }
.fs-modal-body { flex: 1 1 auto; min-height: 0; overflow-x: hidden; overflow-y: auto; padding: 1.25rem 1.5rem; background: #fff; -webkit-overflow-scrolling: touch; }
.fs-modal-footer { flex-shrink: 0; display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
.fs-form-section { margin-bottom: 1.25rem; padding-bottom: 1.15rem; border-bottom: 1px solid #f1f5f9; }
.fs-form-section-title { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b; margin: 0 0 0.75rem; }
.fs-form-section-hint { font-size: 0.8rem; color: #94a3b8; margin: -0.35rem 0 0.75rem; }
.fs-form-label { display: block; font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem; }
.fs-input, .fs-step-form .form-control, .fs-step-form .form-select { border-radius: 0.5rem; border: 1px solid #d1d5db; }
.fs-step-type-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.65rem; }
.fs-step-type-tile { position: relative; display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem; padding: 0.85rem; border: 2px solid #e2e8f0; border-radius: 0.65rem; background: #fff; cursor: pointer; }
.fs-step-type-tile.is-active { border-color: #3b82f6; background: linear-gradient(180deg, #fff 0%, #f0f7ff 100%); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); }
.fs-step-type-input { position: absolute; opacity: 0; pointer-events: none; }
.fs-step-type-icon { display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; font-size: 1.1rem; }
.fs-step-type-tile--input .fs-step-type-icon { background: #ecfdf5; color: #059669; }
.fs-step-type-tile--derived .fs-step-type-icon { background: #eff6ff; color: #2563eb; }
.fs-step-type-tile--lookup .fs-step-type-icon { background: #fffbeb; color: #d97706; }
.fs-step-type-tile--result .fs-step-type-icon { background: #f5f3ff; color: #7c3aed; }
.fs-step-type-name { font-size: 0.875rem; font-weight: 600; color: #0f172a; }
.fs-step-type-hint { font-size: 0.72rem; color: #64748b; line-height: 1.35; }
.fs-form-scroll { overflow: visible; max-height: none; }
.fs-config-panel { margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; background: #fafbfc; }
.fs-config-panel-head { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.85rem 1rem; background: #fff; border-bottom: 1px solid #e2e8f0; }
.fs-config-panel-icon { display: flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; }
.fs-config-panel--derived .fs-config-panel-icon { background: #eff6ff; color: #2563eb; }
.fs-config-panel--lookup .fs-config-panel-icon { background: #fffbeb; color: #d97706; }
.fs-config-panel--result .fs-config-panel-icon { background: #f5f3ff; color: #7c3aed; }
.fs-config-panel--input .fs-config-panel-icon { background: #ecfdf5; color: #059669; }
.fs-config-panel-body { padding: 1rem; }
.reading-steps-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.reading-step-row { display: flex; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; align-items: flex-start; }
.reading-step-row:last-child { border-bottom: none; }
.reading-step-row__num { width: 36px; height: 36px; border-radius: 999px; background: #eff6ff; color: #2563eb; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.reading-step-row__body { flex: 1; min-width: 0; }
.reading-step-row__label { color: #0f172a; }
.rs-type-badge { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.2rem 0.55rem; border-radius: 999px; }
.rs-type--input { background: #ecfdf5; color: #059669; }
.rs-type--derived { background: #eff6ff; color: #2563eb; }
.rs-type--lookup { background: #fffbeb; color: #d97706; }
.rs-type--result { background: #f5f3ff; color: #7c3aed; }
.rs-var-name { font-size: 0.82rem; background: #f8fafc; padding: 0.15rem 0.45rem; border-radius: 6px; }
.rs-expression code { font-size: 0.82rem; color: #1d4ed8; }
.reading-steps-empty { text-align: center; padding: 3rem 1.5rem 2.75rem; color: #64748b; background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%); }
.reading-steps-empty__icon { width: 56px; height: 56px; margin: 0 auto 1rem; border-radius: 14px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
.reading-steps-empty h6 { color: #0f172a; font-weight: 700; margin-bottom: 0.35rem; }
.rs-add-first-step-btn,
.rs-add-step-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 999px; padding: 0.55rem 1.25rem; font-weight: 600; font-size: 0.875rem; border-width: 1.5px; transition: all 0.2s ease; }
.rs-add-first-step-btn { min-width: 180px; }
.rs-add-first-step-btn:hover,
.rs-add-step-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15); }
.rs-derived-limits { background: #f8fafc; border-radius: 10px; padding: 1rem 1rem 0.25rem; margin-left: -0.25rem; margin-right: -0.25rem; }
.rs-readonly-field { min-height: 38px; display: flex; align-items: center; padding: 0.35rem 0; }
@media (min-width: 768px) { .fs-step-type-grid { grid-template-columns: repeat(4, 1fr); } }
.reading-steps-card .rm-act-btn { padding: 0.2rem 0.45rem; line-height: 1.2; border-radius: 6px; }
</style>
@include('layouts.registry.partials.rm-act-btn-styles')
