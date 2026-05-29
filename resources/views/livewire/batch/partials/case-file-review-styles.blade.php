<style>
    .cf-modal-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 24px;
    }
    .cf-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .cf-card-header {
        background: #f8fafc;
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .cf-card-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .cf-card-body {
        padding: 20px;
    }
    .cf-input-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        display: block;
    }
    .cf-form-control {
        height: 38px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 0.88rem;
        color: #0f172a;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
        width: 100%;
    }
    .cf-form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }
    select.cf-form-control {
        appearance: auto;
    }
    textarea.cf-form-control {
        height: auto;
        min-height: 80px;
    }
    .custom-checkbox-modern {
        display: flex;
        align-items: center;
        position: relative;
        padding-left: 0;
        margin-bottom: 0;
    }
    .custom-checkbox-modern .custom-control-input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }
    .custom-checkbox-modern .custom-control-label {
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        user-select: none;
        line-height: 20px;
        margin-bottom: 0;
    }
    .custom-checkbox-modern .custom-control-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 20px;
        height: 20px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background-color: #ffffff;
        transition: all 0.15s ease-in-out;
    }
    .custom-checkbox-modern .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .custom-checkbox-modern .custom-control-label::after {
        content: '';
        position: absolute;
        left: 7px;
        top: 3px;
        width: 6px;
        height: 11px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        opacity: 0;
        transition: all 0.15s ease-in-out;
    }
    .custom-checkbox-modern .custom-control-input:checked ~ .custom-control-label::after {
        opacity: 1;
    }
    .cf-section-subtitle {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
        padding-bottom: 6px;
    }
    .cf-worksheet-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .cf-worksheet-bar p {
        margin: 0;
        font-size: 0.82rem;
        color: #64748b;
    }
    .btn-cf-outline {
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 8px 14px;
        border: 1px solid #2563eb;
        color: #2563eb;
        background: #fff;
        transition: all 0.15s ease;
    }
    .btn-cf-outline:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    /* Verification wizard */
    .vw-modal-shell {
        border-radius: 16px;
        overflow: hidden;
        background: #f8fafc;
        border: 0;
    }
    .vw-modal-header {
        background: #ffffff;
        padding: 20px 24px 0;
        border-bottom: none;
        position: relative;
    }
    .vw-modal-header .vw-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px;
        letter-spacing: -0.01em;
    }
    .vw-modal-header .vw-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }
    .vw-modal-header .vw-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        flex-shrink: 0;
    }
    .vw-modal-header .vw-icon-wrap i {
        font-size: 22px;
        color: #2563eb;
    }
    .vw-modal-header .close {
        position: absolute;
        top: 18px;
        right: 20px;
        opacity: 0.5;
        font-size: 1.5rem;
        text-shadow: none;
    }
    .vw-modal-header .close:hover {
        opacity: 1;
    }
    .vw-steps {
        display: flex;
        align-items: center;
        padding: 20px 24px 0;
        background: #ffffff;
        gap: 0;
    }
    .vw-step {
        flex: 1;
        display: flex;
        align-items: center;
        cursor: pointer;
        padding-bottom: 16px;
        border: none;
        background: transparent;
        text-align: left;
        position: relative;
    }
    .vw-step:disabled,
    .vw-step[disabled] {
        cursor: default;
    }
    .vw-step-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-right: 10px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        color: #94a3b8;
        transition: all 0.2s ease;
    }
    .vw-step.is-active .vw-step-indicator {
        border-color: #2563eb;
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
    }
    .vw-step.is-complete .vw-step-indicator {
        border-color: #10b981;
        background: #10b981;
        color: #ffffff;
    }
    .vw-step-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #94a3b8;
        line-height: 1.3;
    }
    .vw-step-label strong {
        display: block;
        font-size: 0.88rem;
        color: #64748b;
        font-weight: 700;
    }
    .vw-step.is-active .vw-step-label strong {
        color: #0f172a;
    }
    .vw-step.is-complete .vw-step-label strong {
        color: #334155;
    }
    .vw-step-connector {
        flex: 0 0 40px;
        height: 2px;
        background: #e2e8f0;
        margin: 0 8px 16px;
        align-self: flex-start;
        margin-top: 15px;
    }
    .vw-step-connector.is-complete {
        background: #10b981;
    }
    .vw-modal-body {
        padding: 20px 24px;
        max-height: min(68vh, 720px);
        overflow-y: auto;
    }
    .vw-modal-footer {
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .vw-approvers-card .table {
        margin-bottom: 0;
        font-size: 0.88rem;
    }
    .vw-approvers-card .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 12px 14px;
        border-top: none;
    }
    .vw-approvers-card .table td {
        vertical-align: middle;
        padding: 12px 14px;
        border-color: #f1f5f9;
    }
    .vw-approvers-card .table tbody tr:hover {
        background: #f8fafc;
    }
    .vw-info-banner {
        background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.85rem;
        color: #1e40af;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .vw-info-banner i {
        font-size: 20px;
        margin-top: 1px;
    }
    .btn-vw-primary {
        background: #0f172a;
        border-color: #0f172a;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 18px;
        transition: all 0.15s ease;
    }
    .btn-vw-primary:hover {
        background: #1e293b;
        border-color: #1e293b;
        color: #fff;
    }
    .btn-vw-next {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 18px;
    }
    .btn-vw-next:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }
</style>
