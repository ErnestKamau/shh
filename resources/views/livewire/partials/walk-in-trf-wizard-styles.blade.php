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
        --trf-wizard-done: #059669;
        --trf-wizard-done-soft: #d1fae5;
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
        background: var(--trf-wizard-done-soft);
        border-color: var(--trf-wizard-done);
        color: var(--trf-wizard-done);
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
        min-height: 12rem;
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

    @media (max-width: 767.98px) {
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
</style>
