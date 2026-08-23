{{-- Sample Integrity Check — quotation-shell aligned workspace styles --}}
<style>
    .integrity-check-page {
        --ic-accent: var(--color-primary, #6D0A0E);
        --ic-accent-soft: var(--color-primary-soft, #f8ecec);
        --ic-border: var(--ls-border, #e2e8f0);
        --ic-surface: #f8fafc;
        --ic-ink: #1e293b;
        --ic-muted: #64748b;
    }

    .integrity-check-page .integrity-header-card {
        border-radius: 14px;
        overflow: visible;
    }

    .integrity-check-page .integrity-header-card .card-body {
        background: #fff;
    }

    .integrity-check-page .integrity-header-title {
        margin: 0 0 0.25rem;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--ic-ink);
    }

    .integrity-check-page .integrity-header-title .mdi {
        color: var(--ic-accent);
    }

    .integrity-check-page .integrity-header-sub {
        margin: 0;
        font-size: 0.8125rem;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-stage-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .integrity-check-page .btn-integrity-primary {
        background: var(--ic-accent);
        border-color: var(--ic-accent);
        color: #fff;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.4rem 0.95rem;
    }

    .integrity-check-page .btn-integrity-primary:hover:not(:disabled) {
        filter: brightness(0.92);
        color: #fff;
    }

    .integrity-check-page .btn-integrity-primary:disabled {
        opacity: 0.55;
    }

    .integrity-check-page .integrity-workspace {
        --ic-rail-width: 260px;
        align-items: stretch;
    }

    .integrity-check-page .integrity-workspace > [class*="col-"] {
        min-width: 0;
    }

    @media (min-width: 1200px) {
        .integrity-check-page .integrity-sample-rail-col {
            flex: 0 0 var(--ic-rail-width);
            max-width: var(--ic-rail-width);
            width: var(--ic-rail-width);
        }

        .integrity-check-page .integrity-assignment-col {
            flex: 1 1 0;
            max-width: none;
            min-width: 0;
        }
    }

    .integrity-check-page .integrity-panel--sample-rail {
        min-height: 0;
    }

    .integrity-check-page .integrity-panel__head--compact {
        padding: 0.55rem 0.5rem;
    }

    .integrity-check-page .integrity-panel__head--compact .integrity-panel__title {
        font-size: 0.75rem;
        white-space: nowrap;
    }

    .integrity-check-page .integrity-panel__head--compact .integrity-panel__title-text {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .integrity-check-page .integrity-panel {
        background: #fff;
        border: 1px solid var(--ic-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
        height: 100%;
        display: flex;
        flex-direction: column;
        min-height: 520px;
        overflow: hidden;
    }

    .integrity-check-page .integrity-panel.integrity-panel--sample-rail {
        min-height: 0;
        height: auto;
        align-self: start;
    }

    .integrity-check-page .integrity-panel__head {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--ic-border);
        background: var(--ic-surface);
    }

    .integrity-check-page .integrity-panel__title {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--ic-ink);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .integrity-check-page .integrity-panel__title .mdi {
        color: var(--ic-accent);
    }

    .integrity-check-page .integrity-panel__body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
    }

    .integrity-check-page .integrity-sample-list {
        padding: 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .integrity-check-page .integrity-sample-rail-col {
        min-width: 0;
    }

    .integrity-check-page .integrity-assignment-col .integrity-assign-panel {
        min-height: 520px;
    }

    .integrity-check-page .integrity-sample-tile {
        width: 100%;
    }

    .integrity-check-page .integrity-sample-tab {
        position: relative;
        display: block;
        width: 100%;
        text-align: left;
        border: 1px solid var(--ic-border);
        border-radius: 10px;
        background: #fff;
        padding: 0.45rem 0.45rem 0.4rem;
        color: var(--ic-muted);
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
        overflow: hidden;
    }

    .integrity-check-page .integrity-sample-tab:hover {
        border-color: #cbd5e1;
        color: var(--ic-ink);
    }

    .integrity-check-page .integrity-sample-tab.is-active {
        border-color: var(--ic-accent);
        background: var(--ic-accent-soft);
        color: var(--ic-accent);
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
    }

    .integrity-check-page .integrity-sample-tab__row {
        display: flex;
        align-items: flex-start;
        gap: 0.25rem;
        width: 100%;
    }

    .integrity-check-page .integrity-sample-tab__select {
        flex: 1 1 auto;
        min-width: 0;
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        color: inherit;
        text-align: left;
        cursor: pointer;
    }

    .integrity-check-page .integrity-sample-tab__label {
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.35;
        color: inherit;
    }

    .integrity-check-page .integrity-sample-tab__compact {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.45rem;
        padding-top: 0.1rem;
        text-align: left;
    }

    .integrity-check-page .integrity-sample-tab__icon-wrap {
        flex: 0 0 auto;
    }

    .integrity-check-page .integrity-sample-tab__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 10px;
        background:
            linear-gradient(135deg, rgb(253 164 175 / 0.95) 0%, rgb(192 132 252 / 0.92) 48%, rgb(96 165 250 / 0.9) 100%);
        box-shadow:
            inset 0 1px 0 rgb(255 255 255 / 0.45),
            0 4px 10px rgb(109 10 14 / 0.12);
        animation: integrity-sample-icon-float 3.2s ease-in-out infinite;
    }

    .integrity-check-page .integrity-sample-tab__icon .mdi {
        font-size: 1.05rem;
        line-height: 1;
        color: #fff;
        filter: drop-shadow(0 1px 1px rgb(15 23 42 / 0.18));
    }

    .integrity-check-page .integrity-sample-tab.is-active .integrity-sample-tab__icon {
        background:
            linear-gradient(135deg, rgb(127 29 29 / 0.95) 0%, rgb(109 10 14 / 0.92) 55%, rgb(190 18 60 / 0.88) 100%);
    }

    @keyframes integrity-sample-icon-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-2px); }
    }

    .integrity-check-page .integrity-sample-tab__copy {
        min-width: 0;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .integrity-check-page .integrity-sample-tab__description {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        width: 100%;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.3;
        color: inherit;
        word-break: break-word;
        text-align: left;
    }

    .integrity-check-page .integrity-sample-tab.is-active .integrity-sample-tab__description {
        color: var(--ic-accent);
    }

    .integrity-check-page .integrity-sample-tab__counter {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: var(--ic-muted);
        text-align: left;
    }

    .integrity-check-page .integrity-sample-tab.is-active .integrity-sample-tab__counter {
        color: #7f1d1d;
    }

    .integrity-check-page .integrity-sample-tab__meta {
        margin-top: 0.25rem;
        font-size: 0.72rem;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-sample-tab.is-active .integrity-sample-tab__meta {
        color: #7f1d1d;
        opacity: 0.85;
    }

    .integrity-check-page .integrity-sample-tab__labels {
        position: absolute;
        top: 0.3rem;
        right: 0.3rem;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        z-index: 2;
    }

    .integrity-check-page .integrity-sample-label-btn {
        width: 1.35rem;
        height: 1.35rem;
        min-width: 1.35rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.08);
    }

    .integrity-check-page .integrity-sample-tab.is-active .integrity-sample-label-btn {
        background: #fff;
    }

    .integrity-check-page .integrity-sample-label-btn .mdi {
        font-size: 0.82rem;
        line-height: 1;
    }

    .integrity-check-page .integrity-sample-tab__actions {
        flex: 0 0 auto;
        display: flex;
        align-items: flex-start;
        padding-top: 0.1rem;
    }

    .integrity-check-page .integrity-sample-info-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.55rem;
        height: 1.55rem;
        padding: 0;
        border: 1px solid rgb(191 219 254 / 0.7);
        border-radius: 8px;
        background:
            linear-gradient(135deg, rgb(239 246 255 / 0.95) 0%, rgb(219 234 254 / 0.72) 100%);
        color: #1d4ed8;
        box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.5);
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
    }

    .integrity-check-page .integrity-sample-info-btn:hover {
        border-color: #93c5fd;
        color: #1e40af;
        background:
            linear-gradient(135deg, rgb(219 234 254 / 0.98) 0%, rgb(191 219 254 / 0.85) 100%);
    }

    .integrity-check-page .integrity-sample-info-btn .mdi {
        font-size: 0.95rem;
        line-height: 1;
    }

    .integrity-check-page .integrity-sample-tab.is-active .integrity-sample-info-btn {
        border-color: rgb(254 202 202 / 0.85);
        background:
            linear-gradient(135deg, rgb(255 255 255 / 0.9) 0%, rgb(254 226 226 / 0.75) 100%);
        color: var(--ic-accent);
    }

    .integrity-check-page .integrity-progress {
        margin-top: 0.45rem;
        height: 4px;
        border-radius: 999px;
        background: rgba(30, 41, 59, 0.08);
        overflow: hidden;
    }

    .integrity-check-page .integrity-progress-bar {
        height: 100%;
        background: var(--ic-accent);
    }

    .integrity-check-page .integrity-dossier-panel {
        border: 0;
        border-radius: 0;
    }

    .integrity-check-page .integrity-dossier-header {
        cursor: default;
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
    }

    .integrity-check-page .integrity-dossier-body {
        padding: 0.85rem 1rem 1rem;
    }

    .integrity-check-page .integrity-dossier-section + .integrity-dossier-section {
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--ic-border);
    }

    .integrity-check-page .integrity-dossier-section__title {
        margin: 0 0 0.65rem;
        font-size: 0.68rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-dossier-fields {
        display: grid;
        gap: 0.55rem;
        margin: 0;
    }

    .integrity-check-page .integrity-dossier-field dt {
        margin: 0;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-dossier-field dd {
        margin: 0.1rem 0 0;
        font-size: 0.8125rem;
        color: var(--ic-ink);
    }

    .integrity-check-page .integrity-dossier-tests-table th {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--ic-muted);
        border-top: 0;
        white-space: nowrap;
        background: var(--ic-surface);
    }

    .integrity-check-page .integrity-dossier-tests-table td {
        font-size: 0.78rem;
        vertical-align: top;
    }

    .integrity-check-page .integrity-assign-panel .integrity-panel__body {
        display: flex;
        flex-direction: column;
    }

    .integrity-check-page .integrity-assign-list__toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0.55rem 0.85rem 0.35rem;
        border-bottom: 1px solid var(--ic-border);
        background: #fff;
        position: relative;
        z-index: 3;
    }

    .integrity-check-page .integrity-assign-list__search {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.45rem;
        margin-left: auto;
        position: relative;
        min-width: min(100%, 17.5rem);
        max-width: 22rem;
        flex: 1 1 17.5rem;
    }

    .integrity-check-page .integrity-assign-list__search > .ls-search-bar {
        flex: 1 1 auto;
        min-width: 0;
        width: 100%;
    }

    .integrity-check-page .integrity-assign-filter-panel {
        position: absolute;
        top: calc(100% + 0.4rem);
        right: 0;
        left: auto;
        width: min(100vw - 2rem, 22rem);
        z-index: 20;
    }

    .integrity-check-page .integrity-assign-pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.65rem 0.85rem;
        border-top: 1px solid var(--ic-border);
        background: #fff;
    }

    .integrity-check-page .integrity-assign-pagination--meta-only {
        justify-content: flex-start;
    }

    .integrity-check-page .integrity-assign-pagination__meta {
        font-size: 0.75rem;
        color: var(--ic-muted);
        font-weight: 600;
    }

    .integrity-check-page .integrity-assign-pagination__controls {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .integrity-check-page .integrity-assign-pagination__page {
        font-size: 0.75rem;
        font-weight: 600;
        color: #0f172a;
        min-width: 5.5rem;
        text-align: center;
    }

    .integrity-check-page .integrity-bulk-bar {
        padding: 0.75rem 1rem;
        background: linear-gradient(180deg, #fffbeb 0%, #fff 100%);
        border-bottom: 1px solid #fde68a;
    }

    .integrity-check-page .integrity-bulk-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
        width: 100%;
    }

    .integrity-check-page .integrity-bulk-count {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #fcd34d;
        font-size: 0.75rem;
        font-weight: 600;
        color: #92400e;
    }

    .integrity-check-page .integrity-bulk-lab-sections-group {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
        align-items: center;
        flex: 1 1 auto;
        min-width: 0;
        max-width: 560px;
    }

    .integrity-check-page .integrity-bulk-group-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ic-muted);
        white-space: nowrap;
    }

    .integrity-check-page .integrity-bulk-select-wrap {
        flex: 1 1 260px;
        min-width: 200px;
    }

    .integrity-check-page .integrity-bulk-save-sections {
        flex: 0 0 auto;
    }

    .integrity-check-page .integrity-bulk-select-wrap .ls-field {
        margin-bottom: 0;
    }

    .integrity-check-page .integrity-bulk-select-wrap .ls-select2-multi {
        width: 100%;
    }

    .integrity-check-page .integrity-bulk-analyst-row {
        margin-top: 0.65rem;
        padding-top: 0.65rem;
        border-top: 1px dashed #fde68a;
    }

    .integrity-check-page .integrity-bulk-analyst-columns {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem 1rem;
        margin-top: 0.45rem;
    }

    .integrity-check-page .integrity-bulk-analyst-group-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--ic-muted);
        margin-bottom: 0.35rem;
    }

    .integrity-check-page .integrity-bulk-analyst-picks {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .integrity-check-page .integrity-pick-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        border: 1px solid var(--ic-border);
        background: #fff;
        color: var(--ic-muted);
        font-size: 0.72rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .integrity-check-page .integrity-pick-chip:hover {
        border-color: #cbd5e1;
        color: var(--ic-ink);
    }

    .integrity-check-page .integrity-pick-chip.is-active {
        border-color: var(--ic-accent);
        background: var(--ic-accent-soft);
        color: var(--ic-accent);
    }

    .integrity-check-page .integrity-assign-list {
        padding: 0.65rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex: 1 1 auto;
        overflow: auto;
    }

    .integrity-check-page .integrity-assign-card {
        border: 1px solid var(--ic-border);
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .integrity-check-page .integrity-assign-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgb(15 23 42 / 0.06);
    }

    .integrity-check-page .integrity-assign-card.is-editing {
        border-color: var(--ic-accent);
        box-shadow: 0 0 0 1px var(--ic-accent-soft);
    }

    .integrity-check-page .integrity-assign-card.is-incomplete {
        border-left: 3px solid #f59e0b;
    }

    .integrity-check-page .integrity-assign-card.is-complete {
        border-left: 3px solid #16a34a;
    }

    .integrity-check-page .integrity-assign-card.is-subcontracted {
        background: #fffbeb;
    }

    .integrity-check-page .integrity-assign-card__main {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 0.65rem;
        align-items: start;
        padding: 0.75rem 0.85rem;
    }

    .integrity-check-page .integrity-assign-card__check {
        padding-top: 0.15rem;
        margin: 0;
    }

    .integrity-check-page .integrity-assign-card__check input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .integrity-check-page .integrity-assign-card__title {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--ic-ink);
        line-height: 1.3;
    }

    .integrity-check-page .integrity-assign-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.35rem;
    }

    .integrity-check-page .integrity-assign-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .integrity-check-page .integrity-assign-field + .integrity-assign-field {
        margin-top: 0.5rem;
    }

    .integrity-check-page .integrity-assign-field__label {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-chip-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }

    .integrity-check-page .integrity-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: var(--ic-surface);
        color: #334155;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .integrity-check-page .integrity-chip--section {
        border: 1px solid rgb(109 10 14 / 0.28);
        background:
            linear-gradient(135deg, rgb(109 10 14 / 0.16) 0%, rgb(127 29 29 / 0.1) 100%);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #6d0a0e;
        box-shadow:
            inset 0 1px 0 rgb(255 255 255 / 0.35),
            0 1px 2px rgb(109 10 14 / 0.08);
    }

    .integrity-check-page .integrity-chip--section .integrity-chip-remove:hover {
        background: rgb(109 10 14 / 0.14);
    }

    .integrity-check-page .integrity-chip-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: inherit;
        cursor: pointer;
        line-height: 1;
    }

    .integrity-check-page .integrity-chip-remove:hover {
        background: rgba(159, 18, 57, 0.12);
    }

    .integrity-check-page .integrity-assign-card__actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.35rem;
    }

    .integrity-check-page .integrity-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--ic-border);
        background: #fff;
        color: var(--ic-muted);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .integrity-check-page .integrity-toggle-btn:hover {
        border-color: #cbd5e1;
        color: var(--ic-ink);
    }

    .integrity-check-page .integrity-toggle-btn.is-active {
        border-color: #f59e0b;
        background: #fffbeb;
        color: #b45309;
    }

    .integrity-check-page .integrity-toggle-btn.is-edit-active {
        border-color: var(--ic-accent);
        background: var(--ic-accent-soft);
        color: var(--ic-accent);
    }

    .integrity-check-page .integrity-assign-editor {
        padding: 0.85rem 1rem 1rem;
        border-top: 1px solid var(--ic-border);
        background: var(--ic-surface);
    }

    .integrity-check-page .integrity-editor-grid {
        display: grid;
        grid-template-columns: minmax(200px, 1fr) minmax(240px, 1.4fr);
        gap: 1rem;
    }

    .integrity-check-page .integrity-editor-grid .rv-field-label,
    .integrity-check-page .integrity-editor-grid .ls-type-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-assign-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--ic-muted);
        font-size: 0.8125rem;
    }

    .integrity-check-page .integrity-sample-condition-flag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #b45309;
    }

    .integrity-check-page .integrity-dossier-collapse-toggle {
        display: none;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 1rem;
        border: 0;
        border-bottom: 1px solid var(--ic-border);
        background: #eff6ff;
        color: var(--ic-ink);
        font-size: 0.8125rem;
        font-weight: 600;
    }

    /* Select2 — row editor */
    .integrity-check-page .integrity-lab-section-select + .select2-container {
        width: 100% !important;
    }

    .integrity-check-page .integrity-lab-section-select + .select2-container .select2-selection--multiple {
        min-height: 38px !important;
        border: 1px solid var(--ic-border) !important;
        border-radius: 8px !important;
    }

    /* Select2 — bulk */
    .integrity-check-page .integrity-bulk-select-wrap .select2-container {
        width: 100% !important;
    }

    .integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection--multiple {
        min-height: 34px !important;
        border: 1px solid #fcd34d !important;
        border-radius: 8px !important;
        background: #fff !important;
    }

    .integrity-check-page .integrity-bulk-select-wrap.is-empty .select2-container .select2-selection__rendered {
        justify-content: center;
    }

    .integrity-check-page .integrity-bulk-select-wrap.has-values .select2-container .select2-selection__rendered {
        justify-content: flex-start;
    }

    .integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection__clear {
        display: none !important;
    }

    @media (min-width: 1200px) {
        .integrity-check-page .integrity-panel:not(.integrity-panel--sample-rail) {
            min-height: 560px;
        }

        .integrity-check-page .integrity-dossier-collapse-toggle {
            display: none !important;
        }
    }

    @media (max-width: 1199.98px) {
        .integrity-check-page .integrity-panel {
            min-height: 0;
        }

        .integrity-check-page .integrity-dossier-collapse-toggle {
            display: flex;
        }

        .integrity-check-page .integrity-assign-card__main {
            grid-template-columns: auto 1fr;
        }

        .integrity-check-page .integrity-assign-card__actions {
            grid-column: 2;
            flex-direction: row;
            justify-content: flex-end;
        }
    }

    @media (max-width: 767.98px) {
        .integrity-check-page .integrity-editor-grid {
            grid-template-columns: 1fr;
        }

        .integrity-check-page .integrity-bulk-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .integrity-check-page .integrity-bulk-lab-sections-group {
            max-width: none;
        }
    }

    .integrity-check-page .integrity-worksheet-section-list {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        max-height: 340px;
        overflow: auto;
    }

    .integrity-check-page .integrity-worksheet-section-row {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin: 0;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--ic-border);
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }

    .integrity-check-page .integrity-worksheet-section-row.is-selected,
    .integrity-check-page .integrity-worksheet-section-row:has(.integrity-worksheet-section-row__check:checked) {
        border-color: var(--ic-accent);
        background: var(--ic-accent-soft);
    }

    .integrity-check-page .integrity-worksheet-section-row__check {
        margin-top: 0.15rem;
        flex-shrink: 0;
    }

    .integrity-check-page .integrity-worksheet-section-row__body {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 0;
    }

    .integrity-check-page .integrity-worksheet-section-row__title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--ic-ink);
    }

    .integrity-check-page .integrity-worksheet-section-row__meta {
        font-size: 0.75rem;
        color: var(--ic-muted);
    }

    .integrity-check-page .integrity-assign-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .integrity-check-page .integrity-assign-status-chip--assigned {
        border: 1px solid rgb(191 219 254 / 0.65) !important;
        background:
            linear-gradient(135deg, rgb(239 246 255 / 0.96) 0%, rgb(219 234 254 / 0.78) 100%) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #1d4ed8 !important;
        box-shadow:
            inset 0 1px 0 rgb(255 255 255 / 0.55),
            0 1px 2px rgb(59 130 246 / 0.1);
    }

    .integrity-check-page .integrity-assign-status-chip--assigned .mdi {
        color: #2563eb !important;
    }

    /* Sample info modal — LS form-panel / form-grid (gallery) */
    .lab-surface-theme.ls-admin-page .modal.integrity-sample-info-modal,
    .integrity-check-page .integrity-sample-info-modal {
        background: rgba(15, 23, 42, 0.45);
        z-index: 1060;
    }

    .lab-surface-theme.ls-admin-page .modal.integrity-sample-info-modal .modal-dialog,
    .integrity-check-page .integrity-sample-info-modal .modal-dialog {
        max-width: 640px;
        width: calc(100% - 1.5rem);
        margin: 0.75rem auto;
        max-height: min(78vh, 620px);
    }

    .lab-surface-theme.ls-admin-page .modal.integrity-sample-info-modal .modal-content,
    .integrity-check-page .integrity-sample-info-modal .modal-content {
        display: flex !important;
        flex-direction: column;
        max-height: min(78vh, 620px);
        overflow: hidden !important;
    }

    .lab-surface-theme.ls-admin-page .modal.integrity-sample-info-modal .modal-header,
    .lab-surface-theme.ls-admin-page .modal.integrity-sample-info-modal .modal-footer,
    .integrity-check-page .integrity-sample-info-modal .modal-header,
    .integrity-check-page .integrity-sample-info-modal .modal-footer {
        flex-shrink: 0;
    }

    .lab-surface-theme.ls-admin-page .modal.integrity-sample-info-modal .modal-body,
    .integrity-check-page .integrity-sample-info-modal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    .integrity-check-page .integrity-sample-info-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 0;
    }

    .integrity-check-page .integrity-sample-info-summary .ls-quote-commercial-summary__value.is-warn {
        color: #b45309;
    }

    .integrity-check-page .integrity-sample-info-stack {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .integrity-check-page .integrity-sample-info-panel {
        margin: 0;
        padding: 0.85rem 0.95rem;
    }

    .integrity-check-page .integrity-sample-info-panel .ls-form-panel__title {
        margin-bottom: 0.7rem;
        font-size: 0.8rem;
    }

    .integrity-check-page .integrity-sample-info-panel .ls-form-panel__title .mdi {
        display: none;
    }

    .integrity-check-page .integrity-sample-info-client {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: -0.15rem;
    }

    .integrity-check-page .integrity-sample-info-value {
        font-size: 0.86rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.35;
        word-break: break-word;
    }

    .integrity-check-page .integrity-sample-info-value.is-emphasis {
        color: var(--ic-accent, #6D0A0E);
    }

    .integrity-check-page .integrity-sample-info-value.is-warn {
        color: #b45309;
    }

    .integrity-check-page .integrity-sample-info-panel .ls-field {
        min-width: 0;
    }

    .integrity-check-page .integrity-sample-info-panel .ls-form-grid--2 > .ls-span-2 {
        grid-column: span 2;
    }

    @media (max-width: 767.98px) {
        .integrity-check-page .integrity-sample-info-summary {
            grid-template-columns: 1fr;
        }

        .integrity-check-page .integrity-sample-info-panel .ls-form-grid--2 > .ls-span-2 {
            grid-column: span 1;
        }
    }

    .integrity-check-page .integrity-doc-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-height: 34px;
        border-radius: 8px;
        font-weight: 600;
    }

    .integrity-check-page .integrity-dossier-rail__panel {
        border: 0;
        border-radius: 0;
        box-shadow: none;
        min-height: 0;
    }

    .integrity-check-page .integrity-dossier-col .integrity-panel__body {
        padding: 0;
    }

    .integrity-check-page .integrity-dossier-test-tags__list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .integrity-check-page .integrity-dossier-test-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .integrity-check-page .integrity-assign-card__title-btn {
        border: 0;
        background: transparent;
        padding: 0;
        margin: 0;
        font: inherit;
        font-weight: 700;
        color: inherit;
        text-align: left;
        cursor: pointer;
    }

    .integrity-check-page .integrity-assign-card__title-btn:hover {
        color: var(--ic-accent);
        text-decoration: underline;
    }

    .integrity-check-page .integrity-toggle-btn--info.is-active,
    .integrity-check-page .integrity-toggle-btn--info:hover {
        border-color: #cbd5e1;
        color: #0f172a;
        background: #f8fafc;
    }

    .integrity-check-page .integrity-test-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem 1rem;
        margin: 0;
    }

    .integrity-check-page .integrity-test-info-grid__item dt {
        margin: 0 0 0.15rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
    }

    .integrity-check-page .integrity-test-info-grid__item dd {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #0f172a;
    }

    @media (max-width: 767.98px) {
        .integrity-check-page .integrity-test-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
