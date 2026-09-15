<style>
    .cpo-burgundy-header.batch-header-bar {
        background: linear-gradient(
            135deg,
            var(--color-primary, #8b1e2d) 0%,
            var(--color-primary-hover, #6f1623) 100%
        ) !important;
        border: none !important;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(139, 30, 45, 0.28);
        padding: 1.1rem 1.25rem 1.15rem;
        color: #ffffff !important;
    }

    .cpo-burgundy-header__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .cpo-burgundy-header__identity {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem 0.75rem;
        min-width: 0;
    }

    .cpo-burgundy-header__title {
        margin: 0;
        color: #ffffff !important;
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.25;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .cpo-burgundy-header__title i {
        color: rgba(255, 255, 255, 0.95) !important;
    }

    .cpo-burgundy-header__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff !important;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .cpo-burgundy-header__subtitle {
        margin: 0.55rem 0 0;
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 0.9rem;
        max-width: 44rem;
        line-height: 1.45;
    }

    .cpo-burgundy-header__subtitle strong {
        color: #ffffff !important;
        font-weight: 700;
    }

    .cpo-burgundy-header__meta {
        margin: 0.45rem 0 0;
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 0.9rem;
    }

    .cpo-burgundy-header__meta strong {
        color: #ffffff !important;
    }

    .cpo-burgundy-header__back {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: rgba(255, 255, 255, 0.9) !important;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.45rem;
    }

    .cpo-burgundy-header__back:hover {
        color: #ffffff !important;
        text-decoration: underline;
    }

    .cpo-burgundy-header__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .cpo-burgundy-header__actions .ls-btn,
    .cpo-burgundy-header__actions .cpo-header-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        height: 36px;
        padding: 0 0.9rem;
        border-radius: 9px;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
    }

    .cpo-burgundy-header__actions .cpo-header-btn--light {
        background: #ffffff;
        border: 1px solid #ffffff;
        color: var(--color-primary, #8b1e2d) !important;
    }

    .cpo-burgundy-header__actions .cpo-header-btn--light:hover {
        background: #f8fafc;
        color: var(--color-primary-hover, #6f1623) !important;
        text-decoration: none;
    }

    .cpo-burgundy-header__actions .cpo-header-btn--ghost {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.55);
        color: #ffffff !important;
    }

    .cpo-burgundy-header__actions .cpo-header-btn--ghost:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff !important;
        text-decoration: none;
    }

    .cpo-burgundy-header__pills {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.22);
    }

    .cpo-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff !important;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .cpo-stat-pill--ok {
        background: rgba(16, 185, 129, 0.28);
        border-color: rgba(167, 243, 208, 0.55);
    }

    .cpo-stat-pill--file {
        background: rgba(59, 130, 246, 0.28);
        border-color: rgba(191, 219, 254, 0.55);
    }

    .cpo-stat-pill--skip {
        background: rgba(148, 163, 184, 0.3);
        border-color: rgba(226, 232, 240, 0.4);
    }
</style>
