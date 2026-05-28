<style>
    .lab-expand-row {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .section-shell {
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        padding: 16px;
    }

    .section-shell__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
        gap: 16px;
    }

    .section-card--modern {
        position: relative;
        display: flex;
        flex-direction: column;
        border: 1px solid #e8eef7;
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }

    .section-card--modern:hover {
        border-color: #cbd5e1;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1);
        transform: translateY(-2px);
    }

    .section-card__accent {
        height: 4px;
        background: linear-gradient(90deg, #94a3b8 0%, #cbd5e1 100%);
    }

    .section-card__accent--env {
        background: linear-gradient(90deg, #059669 0%, #34d399 100%);
    }

    .section-card__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 1.125rem 1.25rem 0.875rem;
    }

    .section-card__identity {
        min-width: 0;
        flex: 1;
    }

    .section-card__code {
        display: inline-block;
        margin-bottom: 0.5rem;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #475569;
    }

    .section-card__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        word-break: break-word;
    }

    .section-card__toolbar {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .section-card__status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .section-card__status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .section-card__status--active {
        color: #166534;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
    }

    .section-card__status--inactive {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .section-card__actions {
        display: flex;
        gap: 0.35rem;
    }

    .section-card__action {
        width: 32px;
        height: 32px;
        border: 1px solid transparent;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .section-card__action--edit {
        color: #2563eb;
        border-color: #dbeafe;
        background: #eff6ff;
    }

    .section-card__action--edit:hover {
        background: #dbeafe;
    }

    .section-card__action--delete {
        color: #dc2626;
        border-color: #fecaca;
        background: #fef2f2;
    }

    .section-card__action--delete:hover {
        background: #fee2e2;
    }

    .section-card__body {
        display: flex;
        flex-direction: column;
        gap: 0;
        padding: 0 1rem 1.125rem;
        border-top: 1px solid #f1f5f9;
        margin-top: 0.25rem;
    }

    .section-card__detail {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.875rem 0;
        border-bottom: 1px solid #f8fafc;
    }

    .section-card__detail:last-child {
        border-bottom: none;
        padding-bottom: 0.25rem;
    }

    .section-card__detail-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        color: #64748b;
        font-size: 1.05rem;
    }

    .section-card__detail-icon--env {
        background: #ecfdf5;
        border-color: #d1fae5;
        color: #059669;
    }

    .section-card__detail-text {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
        flex: 1;
    }

    .section-card__detail-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .section-card__detail-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.45;
        word-break: break-word;
    }

    .empty-section-state {
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 18px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
    }

    .empty-section-state i {
        font-size: 24px;
        display: block;
        margin-bottom: 6px;
    }

    .section-add-btn {
        white-space: nowrap;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
    }

    .lab-section-modal {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 32px 70px rgba(15, 23, 42, 0.22);
    }

    .lab-section-modal__header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 34%),
            linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .lab-section-modal__eyebrow {
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #2563eb;
    }

    .lab-section-modal__title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
    }

    .lab-section-modal__title i {
        color: #2563eb;
        font-size: 1.3rem;
    }

    .lab-section-modal__subtitle {
        max-width: 640px;
        color: #475569;
        font-size: 0.93rem;
    }

    .lab-section-modal__body {
        padding: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .lab-section-form-grid {
        display: grid;
        gap: 18px;
    }

    .lab-section-panel {
        padding: 24px 24px 28px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .lab-section-panel--accent {
        border-color: #bfdbfe;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .lab-section-panel .row > [class*="col-"] {
        margin-bottom: 1.4rem;
    }

    .lab-section-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
    }

    .lab-section-panel__head h6 {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
    }

    .lab-section-panel__kicker {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #64748b;
    }

    .lab-section-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .form-label--modern {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #334155;
    }

    .form-control--modern {
        min-height: 48px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        padding: 12px 14px;
        color: #0f172a;
        background: #ffffff;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .form-control--modern:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    .form-control--modern::placeholder {
        color: #94a3b8;
    }

    .form-control--modern-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .lab-tag-select-input {
        min-height: 48px;
        padding: 8px 12px;
        border-radius: 14px;
        background: #ffffff;
    }

    .lab-switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }

    .lab-switch__track {
        position: relative;
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background-color 0.2s ease;
    }

    .lab-switch__track::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
        transition: transform 0.2s ease;
    }

    .lab-switch input:checked + .lab-switch__track {
        background: #2563eb;
    }

    .lab-switch input:checked + .lab-switch__track::after {
        transform: translateX(20px);
    }

    .lab-switch__label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #334155;
    }

    .lab-section-modal__footer {
        padding: 18px 24px 24px;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .btn-modal-soft {
        border-radius: 12px;
        padding-inline: 16px;
    }

    .btn-modal-primary {
        border-radius: 12px;
        padding-inline: 18px;
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.2);
    }

    .frequency-schedule-card {
        border: 1px solid #d8e2ef;
        border-radius: 12px;
        background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 100%);
        box-shadow: 0 2px 10px rgba(17, 24, 39, 0.04);
        overflow: hidden;
    }

    .frequency-schedule-table thead th {
        background-color: #f2f7ff;
        color: #37517a;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid #d8e2ef;
        padding: 10px 14px;
    }

    .frequency-schedule-table tbody td {
        vertical-align: middle;
        border-top: 1px solid #e8eef7;
        padding: 10px 14px;
        background-color: #ffffff;
    }

    .frequency-schedule-table tbody tr:first-child td {
        border-top: none;
    }

    .frequency-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        border-radius: 999px;
        background-color: #eaf2ff;
        color: #295a9b;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .frequency-interval-muted {
        color: #94a3b8;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .frequency-interval-input,
    .frequency-label-input {
        min-height: 40px;
        border: 1px solid #ccd9ea;
        border-radius: 10px;
        font-size: 0.88rem;
    }

    @media (max-width: 768px) {
        .section-shell__header {
            flex-direction: column;
            align-items: flex-start;
        }

        .lab-section-panel__head {
            flex-direction: column;
            align-items: flex-start;
        }

        .lab-table {
            min-width: 1100px;
        }
    }

    .rm-act-btn--view {
        border: 1px solid #bae6fd;
        color: #0369a1;
        background: #e0f2fe;
    }
    .rm-act-btn--view:hover {
        background: #bae6fd;
        border-color: #7dd3fc;
    }
</style>
