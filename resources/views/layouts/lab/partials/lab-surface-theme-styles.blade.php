{{--
	Shared lab surface theme — tokens for Samples Receiving board + Request View.
	Keep brand colors from the active app theme (--color-primary etc.).

	Type presets (set data-ls-type on the shell) — all-sans product UI (no serif faces):
	  plex   — IBM Plex Sans + Plex Mono (default)
	  source — Source Sans 3 + Plex Mono
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Source+Sans+3:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<style>
	.lab-surface-theme,
	.lab-panel-theme.workflow-theme,
	.lab-panel-theme.batch-show-page,
	.lab-panel-theme.worksheets-page,
	.request-view-page.lab-panel-theme {
		/* Brand / semantic (prefer app theme tokens) */
		--ls-color-primary: var(--color-primary, #800000);
		--ls-color-primary-hover: var(--color-primary-hover, var(--color-primary, #6b0000));
		--ls-color-primary-soft: var(--color-primary-soft, #f8ecec);
		--ls-color-primary-border: var(--color-primary-border-soft, #e2b4b4);
		--ls-color-primary-focus: var(--color-primary-focus, rgba(128, 0, 0, 0.18));

		--ls-color-success: var(--color-success, #16a34a);
		--ls-color-success-soft: #dcfce7;
		--ls-color-success-text: #166534;
		--ls-color-warning: var(--color-warning, #d97706);
		--ls-color-warning-soft: #fef3c7;
		--ls-color-warning-text: #92400e;
		--ls-color-danger: var(--color-danger, #dc2626);
		--ls-color-danger-soft: #fee2e2;
		--ls-color-danger-text: #991b1b;
		--ls-color-info: var(--color-info, #0284c7);
		--ls-color-info-soft: #e0f2fe;
		--ls-color-info-text: #075985;

		--ls-color-surface: var(--color-surface, #ffffff);
		--ls-color-bg: var(--workflow-bg, var(--color-bg-app, #f8fafc));
		--ls-color-border: var(--color-border, #e2e8f0);
		--ls-color-ink: var(--color-text, #1e293b);
		--ls-color-muted: var(--color-muted, #64748b);
		--ls-color-slate-400: #94a3b8;

		/* Layout */
		--ls-max-width: 1280px;
		--ls-page-pad-x: 1.5rem;
		--ls-page-pad-x-md: 2rem;
		--ls-radius-sm: 6px;
		--ls-radius-md: 8px;
		--ls-radius-lg: 10px;
		--ls-radius-xl: 12px;
		--ls-shadow-sm: 0 1px 2px rgb(0 0 0 / 0.05);
		--ls-shadow-md: 0 1px 3px 0 rgb(0 0 0 / 0.08), 0 1px 2px -1px rgb(0 0 0 / 0.06);

		/* Typography — all-sans (no serif faces on lab surfaces) */
		--ls-font-sans: "IBM Plex Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
		--ls-font-mono: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
		--ls-font-ui: var(--ls-font-sans);
		--ls-font-display: var(--ls-font-sans);
		--ls-font-code: var(--ls-font-mono);

		/* Typography — sizes */
		--ls-text-xs: 0.6875rem;   /* 11px */
		--ls-text-sm: 0.75rem;     /* 12px */
		--ls-text-base: 0.8125rem; /* 13px — board / request view body */
		--ls-text-md: 0.875rem;    /* 14px */
		--ls-text-lg: 0.95rem;     /* ~15px */
		--ls-text-xl: 1.05rem;
		--ls-text-2xl: 1.25rem;
		--ls-table-emphasis: calc(var(--ls-text-base) - 1px); /* 12px — TRF # / customer */
		--ls-font-medium: 500;
		--ls-font-semibold: 600;
		--ls-font-bold: 700;

		/* Component sizing */
		--ls-control-h: 34px;
		--ls-btn-h: 34px;
		--ls-btn-h-sm: 30px;
		--ls-btn-pad-x: 0.85rem;
		--ls-btn-pad-x-sm: 0.65rem;
		--ls-card-pad-y: 0.85rem;
		--ls-card-pad-x: 1rem;
		--ls-table-cell-y: calc(0.7rem - 0.5px);
		--ls-table-cell-x: 0.7rem;
		--ls-badge-pad-y: 0.2rem;
		--ls-badge-pad-x: 0.55rem;
		--ls-badge-font: var(--ls-text-sm);
		--ls-tab-pad-y: calc(0.5rem - 0.5px);
		--ls-tab-pad-x: 0.85rem;
		--ls-tab-font: var(--ls-text-base);
	}

	/* Preset: IBM Plex Sans (default) */
	.lab-surface-theme[data-ls-type="plex"],
	.lab-panel-theme.workflow-theme[data-ls-type="plex"],
	.lab-panel-theme.batch-show-page[data-ls-type="plex"],
	.lab-panel-theme.worksheets-page[data-ls-type="plex"],
	.request-view-page.lab-panel-theme[data-ls-type="plex"] {
		--ls-font-sans: "IBM Plex Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
		--ls-font-mono: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
		--ls-font-ui: var(--ls-font-sans);
		--ls-font-display: var(--ls-font-sans);
		--ls-font-code: var(--ls-font-mono);
	}

	/* Preset: Source Sans 3 */
	.lab-surface-theme[data-ls-type="source"],
	.lab-panel-theme.workflow-theme[data-ls-type="source"],
	.lab-panel-theme.batch-show-page[data-ls-type="source"],
	.lab-panel-theme.worksheets-page[data-ls-type="source"],
	.request-view-page.lab-panel-theme[data-ls-type="source"] {
		--ls-font-sans: "Source Sans 3", system-ui, -apple-system, "Segoe UI", sans-serif;
		--ls-font-mono: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
		--ls-font-ui: var(--ls-font-sans);
		--ls-font-display: var(--ls-font-sans);
		--ls-font-code: var(--ls-font-mono);
	}

	@media (min-width: 768px) {
		.lab-surface-theme,
		.lab-panel-theme.workflow-theme,
		.lab-panel-theme.batch-show-page,
		.lab-panel-theme.worksheets-page,
		.request-view-page.lab-panel-theme {
			--ls-page-pad-x: var(--ls-page-pad-x-md);
		}
	}

	/* —— Page shell —— */
	.lab-surface-theme {
		font-family: var(--ls-font-ui);
	}

	.lab-surface-theme.workflow-board-page,
	.workflow-board-page.lab-surface-theme,
	.batch-show-page.lab-surface-theme,
	.worksheets-page.lab-surface-theme,
	.request-view-page.lab-surface-theme {
		max-width: var(--ls-max-width);
		margin-left: auto;
		margin-right: auto;
		padding-left: var(--ls-page-pad-x);
		padding-right: var(--ls-page-pad-x);
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-base);
		color: var(--ls-color-ink);
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	.lab-surface-theme .workflow-board-panel-header h5,
	.lab-surface-theme .workflow-board-panel-header h6,
	.lab-surface-theme .batch-code-label,
	.request-view-page .rv-card-title,
	.request-view-page .rv-page-title {
		font-family: var(--ls-font-ui) !important;
	}

	/* —— Cards / panels —— */
	.lab-surface-theme .ls-card,
	.lab-surface-theme .workflow-board-panel,
	.request-view-page .rv-card {
		background: var(--ls-color-surface);
		border: 1px solid var(--ls-color-border);
		border-radius: var(--ls-radius-xl);
		box-shadow: var(--ls-shadow-md);
		margin-bottom: 0.85rem;
	}

	.lab-surface-theme .workflow-board-panel-header {
		padding: var(--ls-card-pad-y) var(--ls-card-pad-x);
		gap: 10px;
	}

	.lab-surface-theme .workflow-board-panel-header h5,
	.lab-surface-theme .workflow-board-panel-header h6 {
		font-family: var(--ls-font-ui) !important;
		font-size: var(--ls-text-lg);
		font-weight: var(--ls-font-semibold);
	}

	.lab-surface-theme .workflow-board-panel-body {
		padding: var(--ls-card-pad-y) var(--ls-card-pad-x);
	}

	.lab-surface-theme .workflow-board-panel-body.flush-top {
		padding-top: 0.65rem;
	}

	/* —— Buttons —— */
	.lab-surface-theme .ls-btn,
	.lab-surface-theme .btn-sm.ls-btn,
	.lab-surface-theme .btn-action-sm,
	.lab-surface-theme .workflow-panel-selection-actions .btn,
	.request-view-page .rv-btn-compact {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 0.35rem;
		min-height: var(--ls-btn-h-sm);
		height: var(--ls-btn-h-sm);
		padding: 0.3rem var(--ls-btn-pad-x-sm);
		font-size: var(--ls-text-sm);
		font-weight: var(--ls-font-semibold);
		line-height: 1.2;
		border-radius: var(--ls-radius-sm);
		border: 1px solid transparent;
		transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
	}

	.lab-surface-theme .ls-btn--md,
	.request-view-page .btn-action-sm {
		min-height: var(--ls-btn-h);
		padding: 0.35rem var(--ls-btn-pad-x);
		font-size: var(--ls-text-base);
	}

	.lab-surface-theme .ls-btn--primary,
	.lab-surface-theme .btn-primary,
	.request-view-page .rv-action-btn--primary,
	.request-view-page .rv-action-btn--accent {
		background: var(--ls-color-primary);
		border-color: var(--ls-color-primary);
		color: #fff;
	}

	.lab-surface-theme .ls-btn--primary:hover,
	.lab-surface-theme .btn-primary:hover,
	.request-view-page .rv-action-btn--primary:hover,
	.request-view-page .rv-action-btn--accent:hover {
		background: var(--ls-color-primary-hover);
		border-color: var(--ls-color-primary-hover);
		color: #fff;
	}

	.lab-surface-theme .ls-btn--outline-primary,
	.lab-surface-theme .btn-outline-primary {
		background: #fff;
		border-color: var(--ls-color-primary);
		color: var(--ls-color-primary);
	}

	.lab-surface-theme .ls-btn--outline-primary:hover,
	.lab-surface-theme .btn-outline-primary:hover {
		background: var(--ls-color-primary-soft);
		border-color: var(--ls-color-primary);
		color: var(--ls-color-primary);
	}

	.lab-surface-theme .ls-btn--success,
	.lab-surface-theme .btn-success {
		background: var(--ls-color-success);
		border-color: var(--ls-color-success);
		color: #fff;
	}

	.lab-surface-theme .ls-btn--danger,
	.lab-surface-theme .btn-danger,
	.request-view-page .rv-action-btn--danger {
		background: var(--ls-color-danger-soft);
		border-color: #fecaca;
		color: var(--ls-color-danger-text);
	}

	.lab-surface-theme .ls-btn--warning,
	.lab-surface-theme .btn-warning {
		background: var(--ls-color-warning-soft);
		border-color: #fde68a;
		color: var(--ls-color-warning-text);
	}

	.lab-surface-theme .ls-btn--info,
	.lab-surface-theme .btn-info {
		background: var(--ls-color-info-soft);
		border-color: #bae6fd;
		color: var(--ls-color-info-text);
	}

	.lab-surface-theme .ls-btn:focus,
	.lab-surface-theme .ls-btn:focus-visible,
	.request-view-page .rv-action-btn:focus,
	.request-view-page .rv-action-btn:focus-visible {
		outline: none;
		box-shadow: 0 0 0 3px var(--ls-color-primary-focus);
	}

	/* —— Badges —— */
	.lab-surface-theme .ls-badge,
	.lab-surface-theme .badge,
	.request-view-page .rv-status-badge,
	.request-view-page .rv-pill {
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
		padding: var(--ls-badge-pad-y) var(--ls-badge-pad-x);
		font-size: var(--ls-badge-font);
		font-weight: var(--ls-font-semibold);
		line-height: 1.25;
		border-radius: 999px;
		border: 1px solid transparent;
		vertical-align: middle;
	}

	.lab-surface-theme .badge-primary {
		background: var(--ls-color-primary-soft);
		color: var(--ls-color-primary);
		border-color: var(--ls-color-primary-border);
	}

	.lab-surface-theme .badge-success {
		background: var(--ls-color-success-soft);
		color: var(--ls-color-success-text);
	}

	.lab-surface-theme .badge-warning {
		background: var(--ls-color-warning-soft);
		color: var(--ls-color-warning-text);
	}

	.lab-surface-theme .badge-danger {
		background: var(--ls-color-danger-soft);
		color: var(--ls-color-danger-text);
	}

	.lab-surface-theme .badge-info {
		background: var(--ls-color-info-soft);
		color: var(--ls-color-info-text);
	}

	.lab-surface-theme .badge-secondary,
	.lab-surface-theme .badge-light {
		background: #f1f5f9;
		color: #475569;
		border-color: #e2e8f0;
	}

	/* —— Alerts —— */
	.lab-surface-theme .alert,
	.request-view-page .alert {
		border-radius: var(--ls-radius-md);
		font-size: var(--ls-text-base);
		padding: 0.65rem 0.9rem;
		border-width: 1px;
	}

	.lab-surface-theme .alert-success { background: var(--ls-color-success-soft); border-color: #bbf7d0; color: var(--ls-color-success-text); }
	.lab-surface-theme .alert-danger { background: var(--ls-color-danger-soft); border-color: #fecaca; color: var(--ls-color-danger-text); }
	.lab-surface-theme .alert-warning { background: var(--ls-color-warning-soft); border-color: #fde68a; color: var(--ls-color-warning-text); }
	.lab-surface-theme .alert-info { background: var(--ls-color-info-soft); border-color: #bae6fd; color: var(--ls-color-info-text); }

	/* —— Form controls —— */
	.lab-surface-theme .form-control,
	.lab-surface-theme .form-control-sm,
	.request-view-page .form-control {
		min-height: var(--ls-control-h);
		font-size: var(--ls-text-base);
		border-radius: var(--ls-radius-sm);
		border-color: var(--ls-color-border);
		color: var(--ls-color-ink);
	}

	.lab-surface-theme .form-control:focus,
	.lab-surface-theme .form-control-sm:focus {
		border-color: var(--ls-color-primary-border);
		box-shadow: 0 0 0 3px var(--ls-color-primary-focus);
	}

	.lab-surface-theme .form-label,
	.lab-surface-theme label.form-label {
		font-size: var(--ls-text-sm);
		font-weight: var(--ls-font-semibold);
		color: var(--ls-color-muted);
		margin-bottom: 0.3rem;
	}

	/* —— Tabs —— */
	.lab-surface-theme .workflow-receiving-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
		padding: 4px;
		background: #f8fafc;
		border: 1px solid var(--ls-color-border);
		border-radius: var(--ls-radius-lg);
	}

	.lab-surface-theme .workflow-receiving-tab {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: var(--ls-tab-pad-y) var(--ls-tab-pad-x);
		border: 1px solid transparent;
		border-radius: var(--ls-radius-md);
		background: transparent;
		color: #475569;
		font-family: var(--ls-font-ui) !important;
		font-size: var(--ls-tab-font);
		font-weight: var(--ls-font-semibold);
		line-height: 1.25;
		cursor: pointer;
		transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
	}

	.lab-surface-theme .workflow-receiving-tab:hover {
		background: #fff;
		border-color: var(--ls-color-primary-border);
		color: var(--ls-color-primary);
	}

	.lab-surface-theme .workflow-receiving-tab.is-active {
		background: #fff;
		border-color: var(--ls-color-primary);
		color: var(--ls-color-primary);
		box-shadow: var(--ls-shadow-sm);
	}

	.lab-surface-theme .workflow-receiving-tab-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 1.25rem;
		padding: 2px 6px;
		border-radius: 999px;
		font-size: var(--ls-text-xs);
		font-weight: var(--ls-font-bold);
		background: #e2e8f0;
		color: #475569;
	}

	.lab-surface-theme .workflow-receiving-tab.is-active .workflow-receiving-tab-badge {
		background: var(--ls-color-primary-soft);
		color: var(--ls-color-primary);
	}

	.lab-surface-theme .workflow-receiving-tab.is-loading,
	.lab-surface-theme .workflow-receiving-tabs.is-busy .workflow-receiving-tab:not(.is-active) {
		opacity: 0.65;
		pointer-events: none;
	}

	/* —— Tables —— */
	.lab-surface-theme .workflow-table,
	.request-view-page .rv-sample-table {
		width: 100%;
		font-family: var(--ls-font-ui) !important;
		font-size: var(--ls-text-base);
	}

	.lab-surface-theme .workflow-table thead th,
	.request-view-page .rv-sample-table thead th {
		font-family: var(--ls-font-ui) !important;
		font-size: var(--ls-text-xs);
		font-weight: var(--ls-font-bold);
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: var(--ls-color-muted);
		padding: var(--ls-table-cell-y) var(--ls-table-cell-x);
		border-bottom: 1px solid var(--ls-color-border);
		white-space: nowrap;
		vertical-align: middle;
	}

	.lab-surface-theme .workflow-table tbody td,
	.request-view-page .rv-sample-table tbody td {
		padding: calc(var(--ls-table-cell-y) + 0.1rem) var(--ls-table-cell-x);
		border-bottom: 1px solid #f1f5f9;
		vertical-align: middle;
		color: var(--ls-color-ink);
		font-family: var(--ls-font-ui) !important;
		font-size: var(--ls-text-base);
		font-weight: var(--ls-font-medium);
	}

	/* TRF # / customer — 1px smaller than body, code stack for IDs */
	.lab-surface-theme .workflow-table .ls-table-emphasis,
	.lab-surface-theme .workflow-table .ls-table-id {
		font-size: var(--ls-table-emphasis);
		font-weight: var(--ls-font-medium);
	}

	.lab-surface-theme .workflow-table .ls-table-id {
		font-family: var(--ls-font-code);
		letter-spacing: 0.01em;
	}

	/* Sample Type / Tests Required / Submitted — regular weight, 1px smaller */
	.lab-surface-theme .workflow-table td.ls-table-meta {
		font-size: calc(var(--ls-text-base) - 1px);
		font-weight: 400;
	}

	.lab-surface-theme .workflow-table .rm-act-btn {
		border-radius: var(--ls-radius-sm);
		padding: 5px 9px;
		font-size: var(--ls-text-sm);
	}

	/* —— Filters accordion (client-side) —— */
	.lab-surface-theme .workflow-brand-filters-card {
		padding: 0.7rem 0.9rem;
		margin-bottom: 0.9rem;
		border-radius: var(--ls-radius-lg);
		background: var(--ls-color-surface);
		border: 1px solid var(--ls-color-border);
		box-shadow: var(--ls-shadow-sm);
	}

	.lab-surface-theme .workflow-filters-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		gap: 0.75rem;
		padding: 0.15rem 0;
		margin: 0;
		border: 0 !important;
		outline: none !important;
		box-shadow: none !important;
		background: transparent;
		color: inherit;
		text-align: left;
		cursor: pointer;
		-webkit-tap-highlight-color: transparent;
	}

	.lab-surface-theme .workflow-filters-toggle:focus,
	.lab-surface-theme .workflow-filters-toggle:focus-visible,
	.lab-surface-theme .workflow-filters-toggle:active {
		outline: none !important;
		box-shadow: none !important;
		border: 0 !important;
	}

	.lab-surface-theme .workflow-filters-toggle__label {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: var(--ls-text-md);
		font-weight: var(--ls-font-semibold);
		color: var(--ls-color-ink);
	}

	.lab-surface-theme .workflow-filters-toggle__badge {
		display: inline-flex;
		align-items: center;
		padding: 1px 7px;
		border-radius: 999px;
		font-size: var(--ls-text-xs);
		font-weight: var(--ls-font-bold);
		background: var(--ls-color-primary-soft);
		color: var(--ls-color-primary);
	}

	.lab-surface-theme .workflow-filters-panel {
		margin-top: 0.75rem;
	}

	/* Dropdown menus — slate hover like Samples Receiving Actions (no brand fill) */
	.lab-surface-theme .dropdown-menu,
	.request-view-page .dropdown-menu {
		border: 1px solid var(--ls-color-border);
		border-radius: var(--ls-radius-lg);
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		font-size: var(--ls-text-base);
		padding: 0.35rem 0;
		background: #fff;
	}

	.lab-surface-theme .dropdown-item,
	.request-view-page .dropdown-item {
		font-size: var(--ls-text-base);
		padding: 0.5rem 0.95rem;
		font-weight: 500;
		color: #334155;
		background: transparent;
		border: none;
		border-radius: 0;
	}

	.lab-surface-theme .dropdown-item:hover,
	.lab-surface-theme .dropdown-item:focus,
	.lab-surface-theme .dropdown-item:active,
	.lab-surface-theme .dropdown-item.active,
	.request-view-page .dropdown-item:hover,
	.request-view-page .dropdown-item:focus,
	.request-view-page .dropdown-item:active,
	.request-view-page .dropdown-item.active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
		outline: none;
		box-shadow: none;
	}

	.lab-surface-theme .dropdown-item .mdi,
	.request-view-page .dropdown-item .mdi {
		color: #64748b;
	}

	.lab-surface-theme .dropdown-item:hover .mdi,
	.lab-surface-theme .dropdown-item:focus .mdi,
	.request-view-page .dropdown-item:hover .mdi,
	.request-view-page .dropdown-item:focus .mdi {
		color: #475569;
	}

	/* Keep KPI / stat strip compact (do not grow with surface denser tokens) */
	.lab-surface-theme .workflow-stat-strip__value {
		font-size: 1.25rem;
	}

	.lab-surface-theme .workflow-stat-strip__label,
	.lab-surface-theme .workflow-stat-strip__meta {
		font-size: 0.6875rem;
	}

	.lab-surface-theme .workflow-stat-strip__item {
		padding: 0.75rem 1rem;
	}

	/* —— Modals (Bootstrap + wizard overlays on Receiving / Request Review / Request view) —— */
	.lab-surface-theme .modal .modal-content,
	.lab-surface-theme.acc-wizard-root .acc-wizard-modal {
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-base);
		color: var(--ls-color-ink);
		border-radius: var(--ls-radius-xl);
	}

	.lab-surface-theme .modal .modal-header {
		padding: 0.85rem 1rem;
	}

	.lab-surface-theme .modal .modal-title,
	.lab-surface-theme .modal .modal-header h4,
	.lab-surface-theme .modal .modal-header h5 {
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-lg);
		font-weight: var(--ls-font-semibold);
		line-height: 1.3;
	}

	.lab-surface-theme .modal .modal-body {
		padding: 0.85rem 1rem;
		font-size: var(--ls-text-base);
		color: var(--ls-color-ink);
	}

	.lab-surface-theme .modal .modal-footer {
		padding: 0.65rem 1rem;
		gap: 0.5rem;
	}

	.lab-surface-theme .modal .modal-body p,
	.lab-surface-theme .modal .modal-body .small,
	.lab-surface-theme .modal .modal-body small {
		font-size: var(--ls-text-sm);
	}

	.lab-surface-theme .modal label,
	.lab-surface-theme .modal .control-label,
	.lab-surface-theme .modal .col-form-label {
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-sm);
		font-weight: var(--ls-font-semibold);
		color: var(--ls-color-muted);
	}

	.lab-surface-theme .modal .form-control,
	.lab-surface-theme .modal .form-control-sm,
	.lab-surface-theme .modal .custom-select {
		min-height: var(--ls-control-h);
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-base);
		border-radius: var(--ls-radius-sm);
		border-color: var(--ls-color-border);
		color: var(--ls-color-ink);
	}

	.lab-surface-theme .modal .btn,
	.lab-surface-theme .modal .btn-sm {
		min-height: var(--ls-btn-h-sm);
		padding: 0.3rem var(--ls-btn-pad-x-sm);
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-sm);
		font-weight: var(--ls-font-semibold);
		border-radius: var(--ls-radius-sm);
	}

	.lab-surface-theme .modal .btn:not(.btn-sm) {
		min-height: var(--ls-btn-h);
		padding: 0.35rem var(--ls-btn-pad-x);
		font-size: var(--ls-text-base);
	}

	.lab-surface-theme .modal .badge {
		padding: var(--ls-badge-pad-y) var(--ls-badge-pad-x);
		font-size: var(--ls-badge-font);
		font-weight: var(--ls-font-semibold);
		border-radius: 999px;
	}

	.lab-surface-theme .modal .table,
	.lab-surface-theme .modal .workflow-table {
		font-family: var(--ls-font-ui);
		font-size: var(--ls-text-base);
	}

	.lab-surface-theme .modal .table thead th,
	.lab-surface-theme .modal .workflow-table thead th {
		font-size: var(--ls-text-xs);
		font-weight: var(--ls-font-bold);
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: var(--ls-color-muted);
		padding: var(--ls-table-cell-y) var(--ls-table-cell-x);
	}

	.lab-surface-theme .modal .table tbody td,
	.lab-surface-theme .modal .workflow-table tbody td {
		font-size: var(--ls-text-base);
		font-weight: var(--ls-font-medium);
		color: var(--ls-color-ink);
		padding: var(--ls-table-cell-y) var(--ls-table-cell-x);
	}

	.lab-surface-theme .modal .alert {
		border-radius: var(--ls-radius-md);
		font-size: var(--ls-text-base);
		padding: 0.55rem 0.8rem;
	}

	.lab-surface-theme .modal .card,
	.lab-surface-theme .modal .workflow-board-panel {
		border-radius: var(--ls-radius-lg);
		border-color: var(--ls-color-border);
		box-shadow: var(--ls-shadow-sm);
	}

	/* Status chips — match Receiving density */
	.lab-surface-theme .workflow-status-chip {
		padding: 2px 8px;
		font-size: var(--ls-text-xs);
		border-radius: 999px;
	}

	/*
	 * Batch details form: keep existing Roboto (app body) for column labels
	 * and values. Sizing still comes from surface tokens; font family must not
	 * switch to IBM Plex / surface UI face.
	 */
	.batch-show-page.lab-surface-theme #batch-detail-form,
	.batch-show-page.lab-surface-theme #batch-detail-form .control-label,
	.batch-show-page.lab-surface-theme #batch-detail-form label,
	.batch-show-page.lab-surface-theme #batch-detail-form .form-label,
	.batch-show-page.lab-surface-theme #batch-detail-form .form-control,
	.batch-show-page.lab-surface-theme #batch-detail-form .form-control-sm,
	.batch-show-page.lab-surface-theme #batch-detail-form select,
	.batch-show-page.lab-surface-theme #batch-detail-form textarea,
	.batch-show-page.lab-surface-theme #batch-detail-form .custom-control-label,
	.batch-show-page.lab-surface-theme #batch-detail-form .select2-container--default .select2-selection--single .select2-selection__rendered,
	.batch-show-page.lab-surface-theme #batch-detail-form .select2-container--default .select2-selection--multiple .select2-selection__rendered {
		font-family: 'Roboto', sans-serif !important;
	}
</style>
