    /* Registry status badges — subtle light backgrounds */
    .reg-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.7rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        letter-spacing: 0.02em;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .reg-status-badge__dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
        opacity: 0.9;
    }

    .reg-status-badge--open {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #dbeafe;
    }
    .reg-status-badge--open .reg-status-badge__dot { background: #3b82f6; }

    .reg-status-badge--pending {
        background: #fff7ed;
        color: #c2410c;
        border-color: #ffedd5;
    }
    .reg-status-badge--pending .reg-status-badge__dot { background: #f97316; }

    .reg-status-badge--returned {
        background: #fffbeb;
        color: #b45309;
        border-color: #fef3c7;
    }
    .reg-status-badge--returned .reg-status-badge__dot { background: #f59e0b; }

    .reg-status-badge--closed {
        background: #ecfdf5;
        color: #047857;
        border-color: #d1fae5;
    }
    .reg-status-badge--closed .reg-status-badge__dot { background: #10b981; }

    .reg-status-badge--cancelled {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fee2e2;
    }
    .reg-status-badge--cancelled .reg-status-badge__dot { background: #ef4444; }

    .reg-status-badge--draft {
        background: #f8fafc;
        color: #475569;
        border-color: #e2e8f0;
    }
    .reg-status-badge--draft .reg-status-badge__dot { background: #94a3b8; }

    .reg-status-badge--neutral {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }
    .reg-status-badge--neutral .reg-status-badge__dot { background: #64748b; }

    /* Show page aliases (rr-badge) — same palette */
    .registry-request-show .rr-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        letter-spacing: 0.02em;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .registry-request-show .rr-badge .mdi-circle-small {
        font-size: 0.5rem;
        line-height: 1;
        margin: 0 -0.15rem 0 0;
        opacity: 0.85;
    }
    .registry-request-show .rr-badge--open { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
    .registry-request-show .rr-badge--open .mdi { color: #3b82f6; }
    .registry-request-show .rr-badge--pending { background: #fff7ed; color: #c2410c; border-color: #ffedd5; }
    .registry-request-show .rr-badge--pending .mdi { color: #f97316; }
    .registry-request-show .rr-badge--returned { background: #fffbeb; color: #b45309; border-color: #fef3c7; }
    .registry-request-show .rr-badge--returned .mdi { color: #f59e0b; }
    .registry-request-show .rr-badge--closed { background: #ecfdf5; color: #047857; border-color: #d1fae5; }
    .registry-request-show .rr-badge--closed .mdi { color: #10b981; }
    .registry-request-show .rr-badge--cancelled { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }
    .registry-request-show .rr-badge--cancelled .mdi { color: #ef4444; }
    .registry-request-show .rr-badge--draft { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .registry-request-show .rr-badge--draft .mdi { color: #94a3b8; }
    .registry-request-show .rr-badge--neutral { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .registry-request-show .rr-badge--category { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }
    .registry-request-show .rr-badge--priority-high { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }
    .registry-request-show .rr-badge--priority-medium { background: #fffbeb; color: #b45309; border-color: #fef3c7; }
    .registry-request-show .rr-badge--priority-low { background: #ecfdf5; color: #047857; border-color: #d1fae5; }
