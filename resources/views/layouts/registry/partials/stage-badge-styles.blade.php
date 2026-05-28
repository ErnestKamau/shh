    /* Registry workflow stage badges — subtle light backgrounds */
    .reg-stage-badge {
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
        max-width: 100%;
    }

    .reg-stage-badge__dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
        opacity: 0.9;
    }

    .reg-stage-badge--empty {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
    }
    .reg-stage-badge--empty .reg-stage-badge__dot { background: #cbd5e1; }

    .reg-stage-badge--registry {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }
    .reg-stage-badge--registry .reg-stage-badge__dot { background: #64748b; }

    .reg-stage-badge--investigation {
        background: #eef2ff;
        color: #4338ca;
        border-color: #e0e7ff;
    }
    .reg-stage-badge--investigation .reg-stage-badge__dot { background: #6366f1; }

    .reg-stage-badge--director {
        background: #f5f3ff;
        color: #6d28d9;
        border-color: #ede9fe;
    }
    .reg-stage-badge--director .reg-stage-badge__dot { background: #8b5cf6; }

    .reg-stage-badge--lab_manager {
        background: #ecfeff;
        color: #0e7490;
        border-color: #cffafe;
    }
    .reg-stage-badge--lab_manager .reg-stage-badge__dot { background: #06b6d4; }

    .reg-stage-badge--lab_receiving {
        background: #ecfdf5;
        color: #047857;
        border-color: #d1fae5;
    }
    .reg-stage-badge--lab_receiving .reg-stage-badge__dot { background: #10b981; }

    .reg-stage-badge--customer_service {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #dbeafe;
    }
    .reg-stage-badge--customer_service .reg-stage-badge__dot { background: #3b82f6; }

    .reg-stage-badge--sro {
        background: #f0fdfa;
        color: #0f766e;
        border-color: #ccfbf1;
    }
    .reg-stage-badge--sro .reg-stage-badge__dot { background: #14b8a6; }

    .reg-stage-badge--analyst {
        background: #fff7ed;
        color: #c2410c;
        border-color: #ffedd5;
    }
    .reg-stage-badge--analyst .reg-stage-badge__dot { background: #f97316; }

    .reg-stage-badge--qa {
        background: #fdf2f8;
        color: #be185d;
        border-color: #fce7f3;
    }
    .reg-stage-badge--qa .reg-stage-badge__dot { background: #ec4899; }

    .reg-stage-badge--resolution {
        background: #f0fdf4;
        color: #15803d;
        border-color: #dcfce7;
    }
    .reg-stage-badge--resolution .reg-stage-badge__dot { background: #22c55e; }

    .reg-stage-badge--closed,
    .reg-stage-badge--completed {
        background: #f8fafc;
        color: #334155;
        border-color: #e2e8f0;
    }
    .reg-stage-badge--closed .reg-stage-badge__dot,
    .reg-stage-badge--completed .reg-stage-badge__dot { background: #64748b; }

    /* Fallback palette for uncommon / custom step codes */
    .reg-stage-badge--variant-0 {
        background: #eef2ff;
        color: #4338ca;
        border-color: #e0e7ff;
    }
    .reg-stage-badge--variant-0 .reg-stage-badge__dot { background: #6366f1; }

    .reg-stage-badge--variant-1 {
        background: #ecfeff;
        color: #0e7490;
        border-color: #cffafe;
    }
    .reg-stage-badge--variant-1 .reg-stage-badge__dot { background: #06b6d4; }

    .reg-stage-badge--variant-2 {
        background: #fff7ed;
        color: #c2410c;
        border-color: #ffedd5;
    }
    .reg-stage-badge--variant-2 .reg-stage-badge__dot { background: #f97316; }

    .reg-stage-badge--variant-3 {
        background: #fdf2f8;
        color: #be185d;
        border-color: #fce7f3;
    }
    .reg-stage-badge--variant-3 .reg-stage-badge__dot { background: #ec4899; }

    .reg-stage-badge--variant-4 {
        background: #f5f3ff;
        color: #6d28d9;
        border-color: #ede9fe;
    }
    .reg-stage-badge--variant-4 .reg-stage-badge__dot { background: #8b5cf6; }

    .reg-stage-badge--variant-5 {
        background: #f0fdfa;
        color: #0f766e;
        border-color: #ccfbf1;
    }
    .reg-stage-badge--variant-5 .reg-stage-badge__dot { background: #14b8a6; }
