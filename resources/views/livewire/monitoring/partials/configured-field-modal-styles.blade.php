<style>
.cf-modal {
    position: fixed;
    inset: 0;
    z-index: 1060;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(3px);
    overflow-y: auto;
}
.cf-modal-dialog {
    margin: auto;
    width: 100%;
    max-height: calc(100vh - 2.5rem);
}
.cf-modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
    overflow: visible;
}
.cf-modal-header {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 1.15rem 1.35rem;
}
.cf-modal-subtitle { font-size: 0.8rem; color: #64748b; }
.cf-modal-body {
    padding: 1.25rem 1.35rem;
    background: #fff;
    max-height: min(70vh, calc(100vh - 12rem));
    overflow-y: auto;
    overflow-x: visible;
}
.cf-form .tag-select-container {
    width: 100%;
}
.cf-form .tag-dropdown {
    z-index: 1070;
}
.cf-form .tag-dropdown-item.active {
    background-color: #eef2ff;
    color: #4338ca;
    font-weight: 600;
}
.cf-modal-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 0.85rem 1.35rem;
}
.cf-form-section {
    margin-bottom: 1.25rem;
    padding-bottom: 1.15rem;
    border-bottom: 1px solid #f1f5f9;
}
.cf-form-section-title {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #64748b;
    margin: 0 0 0.85rem;
}
.cf-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.35rem;
}
.cf-control {
    border-radius: 0.5rem;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
}
.cf-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.cf-hint { font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem; }
.cf-type-panel {
    padding: 0.85rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.65rem;
    font-size: 0.85rem;
    color: #475569;
}
.cf-check-tile {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.65rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    background: #fff;
    cursor: pointer;
    font-size: 0.85rem;
    margin: 0;
}
.cf-check-tile:has(input:checked) {
    border-color: #6366f1;
    background: #eef2ff;
}
.cf-check-required { padding-top: 0.25rem; }
.cf-alert { border-radius: 0.5rem; }
</style>
