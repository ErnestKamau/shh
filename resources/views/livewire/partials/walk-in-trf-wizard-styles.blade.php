<style>
    .walk-in-trf-wizard-shell {
        background: linear-gradient(180deg, #f8fafc 0%, #fff 2.5rem);
        border: 1px solid var(--trf-wizard-border, #e2e8f0);
        border-radius: 14px;
        padding: 1.15rem 1.25rem 1.25rem;
    }

    .walk-in-trf-wizard {
        --trf-wizard-accent: var(--color-primary, #3b5fc0);
        --trf-wizard-accent-soft: var(--color-primary-soft, #eef2ff);
        --trf-wizard-border: #e2e8f0;
        --trf-wizard-muted: #64748b;
        --trf-wizard-text: #0f172a;
        --trf-wizard-track: #e2e8f0;
        --trf-wizard-done: #0ea5e9;
        --trf-wizard-done-soft: #e0f2fe;
    }

    .walk-in-trf-wizard__progress {
        height: 4px;
        border-radius: 999px;
        background: var(--trf-wizard-track);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    .walk-in-trf-wizard__progress-bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--trf-wizard-accent) 0%, var(--color-primary-hover, #2f4da0) 100%);
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .walk-in-trf-wizard__steps {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.25rem;
        margin-bottom: 1.35rem;
        position: relative;
        padding: 0 0.15rem;
    }

    .walk-in-trf-wizard__track {
        position: absolute;
        top: 17px;
        left: 8%;
        right: 8%;
        height: 2px;
        background: var(--trf-wizard-track);
        border-radius: 999px;
        overflow: hidden;
        z-index: 0;
    }

    .walk-in-trf-wizard__track-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--trf-wizard-accent) 0%, var(--trf-wizard-done) 100%);
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .walk-in-trf-wizard__steps::before {
        display: none;
    }

    .walk-in-trf-wizard__step {
        flex: 1 1 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.45rem;
        padding: 0;
        border: none;
        background: transparent;
        color: var(--trf-wizard-muted);
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.2;
        text-align: center;
        cursor: pointer;
        position: relative;
        z-index: 1;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .walk-in-trf-wizard__step:hover:not(:disabled) {
        color: var(--trf-wizard-text);
    }

    .walk-in-trf-wizard__step:disabled {
        cursor: default;
        opacity: 0.55;
    }

    .walk-in-trf-wizard__step-index {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        background: #fff;
        border: 2px solid var(--trf-wizard-track);
        color: var(--trf-wizard-muted);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .walk-in-trf-wizard__step.is-active .walk-in-trf-wizard__step-index {
        background: var(--trf-wizard-accent);
        border-color: var(--trf-wizard-accent);
        color: #fff;
        transform: scale(1.08);
        box-shadow: 0 4px 12px var(--color-primary-shadow, rgba(59, 95, 192, 0.35));
    }

    .walk-in-trf-wizard__step.is-done .walk-in-trf-wizard__step-index {
        background: #0ea5e9;
        border-color: #0284c7;
        color: #fff;
    }

    .walk-in-trf-wizard__step.is-done .walk-in-trf-wizard__step-index .mdi {
        color: #fff;
        font-size: 1rem;
        line-height: 1;
    }

    .walk-in-trf-wizard__step.is-active {
        color: var(--trf-wizard-text);
    }

    .walk-in-trf-wizard__step.is-active .walk-in-trf-wizard__step-label {
        font-weight: 700;
    }

    .walk-in-trf-wizard__step.is-done {
        color: var(--trf-wizard-muted);
    }

    .walk-in-trf-wizard__step:focus-visible {
        outline: 2px solid var(--trf-wizard-accent);
        outline-offset: 3px;
        border-radius: 8px;
    }

    .walk-in-trf-wizard__step-label {
        max-width: 100%;
        padding: 0 0.15rem;
        word-break: break-word;
    }

    .walk-in-trf-wizard__panel {
        background: #fff;
        border: 1px solid var(--trf-wizard-border);
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        animation: walkInTrfStepIn 0.28s ease-out;
        min-height: 0;
        overflow: visible;
    }

    @keyframes walkInTrfStepIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .walk-in-trf-wizard__panel {
            animation: none;
        }

        .walk-in-trf-wizard__progress-bar,
        .walk-in-trf-wizard__step-index {
            transition: none;
        }
    }

    .walk-in-trf-wizard__panel-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--trf-wizard-text);
        margin: 0 0 0.25rem;
    }

    .walk-in-trf-wizard__panel-desc {
        font-size: 0.8rem;
        color: var(--trf-wizard-muted);
        margin: 0 0 1rem;
    }

    .walk-in-trf-wizard__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.65rem;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--trf-wizard-border);
    }

    .walk-in-trf-wizard__footer-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
    }

    .walk-in-trf-wizard__step-hint {
        font-size: 0.75rem;
        color: var(--trf-wizard-muted);
    }

    .paper-trf-intake__meta {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
        gap: 0.85rem 1rem;
        margin-bottom: 1.15rem;
    }

    .paper-trf-intake__meta .form-group {
        margin-bottom: 0;
    }

    .paper-trf-intake .walk-in-trf-wizard-shell {
        background: transparent;
        border: 0;
        border-radius: 0;
        padding: 0;
    }

    .paper-trf-intake .walk-in-trf-wizard__panel {
        padding: 1rem 1.1rem 1.15rem;
        min-height: 0;
    }

    .paper-trf-intake .walk-in-trf-wizard__panel-title {
        font-size: 0.82rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 0.15rem;
    }

    @media (max-width: 767.98px) {
        .paper-trf-intake__meta {
            grid-template-columns: 1fr;
        }

        .walk-in-trf-wizard-shell {
            padding: 0.9rem 0.85rem 1rem;
        }

        .walk-in-trf-wizard__track {
            display: none;
        }

        .walk-in-trf-wizard__steps::before {
            display: none;
        }

        .walk-in-trf-wizard__step-label {
            font-size: 0.62rem;
        }

        .walk-in-trf-wizard__step-index {
            width: 30px;
            height: 30px;
            font-size: 0.68rem;
        }
    }

    .direct-registration-intake .paper-trf-intake__meta {
        grid-template-columns: 1fr;
        margin-bottom: 1rem;
    }

    /* External carousel rails — outside the modal dialog (on the backdrop) */
    #receive-sample-modal.receive-sample-modal--direct-registration {
        padding-left: 0;
        padding-right: 0;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .modal-dialog {
        margin-left: auto;
        margin-right: auto;
        max-width: min(1320px, calc(100vw - 8.5rem));
        width: calc(100% - 8.5rem);
        height: calc(95vh - 3.5rem);
        max-height: calc(95vh - 3.5rem);
        margin-top: 1.75rem;
        margin-bottom: 1.75rem;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .modal-content {
        height: 100%;
        max-height: 100%;
        display: flex;
        flex-direction: column;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .modal-body {
        flex: 1 1 auto;
        padding: 1.1rem 1.75rem 0.85rem;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .receive-sample-modal-body {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        height: 100%;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake > .d-flex,
    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake > .dr-success-banner {
        flex: 0 0 auto;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake .dr-select-trf {
        flex: 1 1 auto;
        min-height: 0;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake .walk-in-trf-wizard-shell {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake .walk-in-trf-wizard {
        flex: 0 0 auto;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake .walk-in-trf-wizard__steps {
        margin-bottom: 0.85rem;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .direct-registration-intake .walk-in-trf-wizard__panel {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .receive-sample-modal-footer {
        flex: 0 0 auto;
        margin-top: 0.35rem !important;
        padding-top: 0.55rem !important;
        padding-bottom: 0 !important;
    }

    #receive-sample-modal.receive-sample-modal--direct-registration .receive-sample-modal-header {
        padding: 1.15rem 1.75rem 0.85rem;
    }

    #receive-sample-modal .dr-ext-nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1065;
        width: 3.5rem;
        height: 7.5rem;
        border: 0;
        border-radius: 0.85rem;
        background: rgba(30, 41, 59, 0.88);
        color: #f8fafc;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
        transition: background 0.15s ease, transform 0.15s ease, opacity 0.15s ease;
        padding: 0.5rem 0.25rem;
    }

    #receive-sample-modal.show.receive-sample-modal--direct-registration .dr-ext-nav {
        display: inline-flex;
    }

    #receive-sample-modal .dr-ext-nav i {
        font-size: 2rem;
        line-height: 1;
    }

    #receive-sample-modal .dr-ext-nav--left {
        left: 1.25rem;
    }

    #receive-sample-modal .dr-ext-nav--right {
        right: 1.25rem;
    }

    #receive-sample-modal .dr-ext-nav.is-active:hover {
        background: var(--color-primary, #6d0a0e);
        transform: translateY(-50%) scale(1.03);
    }

    #receive-sample-modal .dr-ext-nav--right.is-active,
    #receive-sample-modal .dr-ext-nav--right.is-receive-cue {
        background: var(--color-primary, #6d0a0e);
        color: #fff;
        width: 3.65rem;
        min-height: 8.5rem;
        height: auto;
        padding: 0.85rem 0.35rem;
    }

    #receive-sample-modal .dr-ext-nav--right .dr-ext-nav__cue {
        display: block;
        opacity: 1;
        visibility: visible;
        color: inherit;
    }

    #receive-sample-modal .dr-ext-nav.is-disabled,
    #receive-sample-modal .dr-ext-nav:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
    }

    #receive-sample-modal .dr-ext-nav__cue {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: inherit;
        line-height: 1.15;
        white-space: nowrap;
    }

    .dr-select-trf {
        display: flex;
        flex-direction: column;
        min-height: min(28rem, calc(95vh - 14rem));
        height: 100%;
    }

    .dr-select-trf__intro {
        flex: 0 0 auto;
    }

    .dr-trf-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.15rem;
        flex: 1 1 auto;
        align-content: stretch;
        width: 100%;
        margin-top: 0.35rem;
    }

    .dr-trf-card {
        --dr-card-accent: var(--color-primary, #6d0a0e);
        --dr-card-accent-soft: var(--color-primary-soft, #f8ecec);
        --dr-card-mesh-a: 20%;
        --dr-card-mesh-b: 12%;
        --dr-card-grid-opacity: 0.55;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.25rem;
        min-height: 11.5rem;
        height: 100%;
        padding: 1.35rem 1.25rem 1.2rem;
        border-radius: 18px;
        border: 1px solid #e8edf3;
        background:
            radial-gradient(120% 90% at 100% 0%, color-mix(in srgb, var(--dr-card-accent) var(--dr-card-mesh-a), #fff) 0%, transparent 55%),
            radial-gradient(100% 80% at 0% 100%, color-mix(in srgb, var(--dr-card-accent) var(--dr-card-mesh-b), #fff) 0%, transparent 50%),
            linear-gradient(165deg, #ffffff 0%, color-mix(in srgb, var(--dr-card-accent) 4%, #f7f9fc) 100%);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        text-align: left;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, --dr-card-mesh-a 0.18s ease, --dr-card-mesh-b 0.18s ease, --dr-card-grid-opacity 0.18s ease;
        width: 100%;
        cursor: pointer;
    }

    .dr-trf-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 5px;
        z-index: 2;
        background: linear-gradient(90deg, var(--dr-card-accent), color-mix(in srgb, var(--dr-card-accent) 45%, #fff));
    }

    .dr-trf-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        z-index: 0;
        opacity: var(--dr-card-grid-opacity);
        background-image:
            radial-gradient(circle at 1px 1px, color-mix(in srgb, var(--dr-card-accent) 28%, transparent) 1px, transparent 0);
        background-size: 14px 14px;
        background-position: 0 0;
        mask-image: linear-gradient(165deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.18) 55%, transparent 100%);
        -webkit-mask-image: linear-gradient(165deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.18) 55%, transparent 100%);
    }

    .dr-trf-card--accent-1 {
        --dr-card-accent: var(--color-primary, #6d0a0e);
        --dr-card-accent-soft: var(--color-primary-soft, #f8ecec);
    }

    .dr-trf-card--accent-2 {
        --dr-card-accent: #0f766e;
        --dr-card-accent-soft: #ecfdf5;
    }

    .dr-trf-card--accent-3 {
        --dr-card-accent: #1d4ed8;
        --dr-card-accent-soft: #eff6ff;
    }

    .dr-trf-card--accent-4 {
        --dr-card-accent: #9a3412;
        --dr-card-accent-soft: #fff7ed;
    }

    .dr-trf-card--accent-5 {
        --dr-card-accent: #047857;
        --dr-card-accent-soft: #ecfdf5;
    }

    .dr-trf-card:hover {
        --dr-card-mesh-a: 26%;
        --dr-card-mesh-b: 16%;
        --dr-card-grid-opacity: 0.7;
        transform: translateY(-4px);
        border-color: color-mix(in srgb, var(--dr-card-accent) 40%, #e8edf3);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    .dr-trf-card:focus {
        outline: none;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--dr-card-accent) 22%, transparent), 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    .dr-trf-card__icon {
        position: relative;
        z-index: 1;
        width: 3.35rem;
        height: 3.35rem;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--dr-card-accent-soft);
        color: var(--dr-card-accent);
        font-size: 1.55rem;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7);
    }

    .dr-trf-card__body {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        width: 100%;
        margin-top: auto;
    }

    .dr-trf-card__name {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        max-width: 100%;
    }

    .dr-trf-card__cta {
        display: inline-flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--dr-card-accent);
        opacity: 0.9;
    }

    .dr-all-trfs-btn {
        border-radius: 999px !important;
        padding-left: 0.9rem !important;
        padding-right: 0.95rem !important;
    }

    .dr-fill-form-chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        background: var(--color-primary-soft, #f8ecec);
        color: var(--color-primary, #6d0a0e);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .dr-success-banner {
        border: 1px solid #bbf7d0;
        background: linear-gradient(135deg, #ecfdf5 0%, #f8fafc 100%);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        gap: 0.75rem;
        box-shadow: 0 12px 28px rgba(138, 149, 158, 0.12);
    }

    .dr-success-banner__icon {
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #059669;
        color: #fff;
        flex-shrink: 0;
    }

    .dr-accept-samples-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 0;
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
        background: var(--color-primary, #6d0a0e);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(138, 149, 158, 0.2);
    }

    .dr-accept-samples-badge:hover {
        filter: brightness(1.05);
        color: #fff;
        text-decoration: none;
    }

    .dr-receive-handoff {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 18px 40px rgba(138, 149, 158, 0.14);
    }

    .dr-receive-handoff__icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-primary-soft, #f8ecec);
        color: var(--color-primary, #6d0a0e);
        font-size: 1.5rem;
    }

    .direct-registration-intake .walk-in-trf-wizard-shell {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.85rem 1.1rem 0.9rem;
        box-shadow: 0 12px 28px rgba(138, 149, 158, 0.1);
    }

    @media (max-width: 991.98px) {
        #receive-sample-modal.receive-sample-modal--direct-registration .modal-dialog {
            max-width: calc(100vw - 5.5rem);
            width: calc(100% - 5.5rem);
            height: calc(95vh - 2.5rem);
            max-height: calc(95vh - 2.5rem);
            margin-top: 1.25rem;
            margin-bottom: 1.25rem;
        }

        #receive-sample-modal.receive-sample-modal--direct-registration .modal-body {
            padding: 0.95rem 1.15rem 0.75rem;
        }

        #receive-sample-modal.receive-sample-modal--direct-registration .receive-sample-modal-header {
            padding: 1rem 1.15rem 0.75rem;
        }

        #receive-sample-modal .dr-ext-nav {
            width: 2.85rem;
            height: 5.5rem;
        }

        #receive-sample-modal .dr-ext-nav i {
            font-size: 1.65rem;
        }

        #receive-sample-modal .dr-ext-nav--left {
            left: 0.45rem;
        }

        #receive-sample-modal .dr-ext-nav--right {
            right: 0.45rem;
        }

        #receive-sample-modal .dr-ext-nav__cue {
            display: block;
            font-size: 0.58rem;
        }
    }

    @media (max-width: 575.98px) {
        .dr-trf-card-grid {
            grid-template-columns: 1fr;
        }

        #receive-sample-modal .dr-ext-nav {
            min-height: 4.5rem;
            height: auto;
            width: 3.1rem;
            border-radius: 0.85rem;
        }
    }
</style>
