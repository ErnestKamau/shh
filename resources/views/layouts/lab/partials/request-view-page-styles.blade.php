<style>
	/* Compact density aligned with RFT / sample-workflow pages */
	.request-view-page.workflow-board-page {
		padding-bottom: 1.5rem;
		background: var(--workflow-bg, #f8fafc);
		min-height: calc(100vh - 56px);
		font-size: 0.8125rem;
	}

	.request-view-page .request-view-shell {
		background: transparent;
		max-width: 1280px;
		margin-left: auto;
		margin-right: auto;
	}

	.request-view-page .breadcrumb-container {
		margin-bottom: 0.75rem;
	}

	/* Burgundy header (workflow theme) */
	.request-view-page .rv-header {
		margin-bottom: 1.25rem;
	}

	.request-view-page .rv-header-bar {
		padding: 12px 16px !important;
		border-radius: 10px;
		margin-bottom: 0;
	}

	.request-view-page .rv-header-top {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 8px;
		padding-bottom: 0;
	}

	.request-view-page .rv-header-identity {
		display: flex;
		flex-wrap: wrap;
		flex-direction: row;
		align-items: center;
		gap: 0.35rem 0.5rem;
		margin-bottom: 0;
	}

	.request-view-page .rv-header-request {
		font-size: 1.15rem;
		font-weight: 700;
		color: #fff;
		letter-spacing: -0.01em;
		line-height: 1.2;
	}

	.request-view-page .rv-header-form-name {
		font-size: 0.875rem;
		font-weight: 500;
		color: rgba(255, 255, 255, 0.9);
		line-height: 1.2;
	}

	.request-view-page .rv-header-sep {
		display: inline-block;
		color: rgba(255, 255, 255, 0.55);
		font-weight: 500;
		line-height: 1;
		padding: 0 0.1rem;
	}

	.request-view-page .rv-header-stage {
		margin-left: 0.2rem;
	}

	.request-view-page .rv-actions-dropdown {
		position: relative;
		flex-shrink: 0;
	}

	.request-view-page .rv-actions-dropdown > .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		float: none;
		margin-top: 0.35rem;
		min-width: 15.5rem;
		max-width: 20rem;
		max-height: min(70vh, 520px);
		overflow-y: auto;
		padding: 0.35rem 0;
		border: 1px solid #dbe5f0;
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		z-index: 1050;
		background: #fff;
	}

	.request-view-page .rv-actions-dropdown .dropdown-menu > li {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	.request-view-page .rv-actions-dropdown .btn-action-sm {
		height: 32px;
		padding: 0 14px;
		font-size: 0.82rem;
		border-radius: 6px;
		display: inline-flex;
		align-items: center;
		gap: 5px;
		font-weight: 500;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item {
		display: flex;
		align-items: center;
		width: 100%;
		padding: 0.5rem 0.95rem;
		font-size: 0.8125rem;
		font-weight: 500;
		line-height: 1.35;
		color: #334155 !important;
		border: none;
		border-radius: 0;
		background: transparent !important;
		box-shadow: none !important;
		text-align: left;
		white-space: nowrap;
		text-decoration: none;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item:hover,
	.request-view-page .rv-actions-dropdown .dropdown-item:focus {
		background: #f1f5f9 !important;
		color: #1e293b !important;
		text-decoration: none;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item:active,
	.request-view-page .rv-actions-dropdown .dropdown-item.active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item .mdi {
		flex-shrink: 0;
		width: 1.125rem;
		margin-right: 0.5rem;
		text-align: center;
		color: #64748b !important;
		font-size: 1.05rem;
		line-height: 1;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item:hover .mdi,
	.request-view-page .rv-actions-dropdown .dropdown-item:focus .mdi,
	.request-view-page .rv-actions-dropdown .dropdown-item:active .mdi {
		color: #475569 !important;
	}

	.request-view-page .rv-actions-dropdown .request-view-actions-form {
		margin: 0;
		padding: 0;
		display: block;
		width: 100%;
	}

	.request-view-page .rv-actions-dropdown .request-view-actions-form .dropdown-item {
		width: 100%;
	}

	.request-view-page .rv-actions-dropdown .dropdown-divider {
		margin: 0.35rem 0;
		border-top: 1px solid #e8eef4;
	}

	/* Kill legacy danger/burgundy item styles inside Actions */
	.request-view-page .rv-actions-dropdown .dropdown-item-danger,
	.request-view-page .rv-actions-dropdown .dropdown-item-danger:hover,
	.request-view-page .rv-actions-dropdown .dropdown-item-danger:focus,
	.request-view-page .rv-actions-dropdown .dropdown-item-danger:active,
	.request-view-page .request-view-actions-menu .dropdown-item-danger,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item-danger .mdi,
	.request-view-page .request-view-actions-menu .dropdown-item-danger .mdi {
		color: #64748b !important;
	}

	/* Equal-height flex card grids — compact */
	.request-view-page .rv-section-grid {
		display: flex;
		flex-wrap: wrap;
		align-items: stretch;
		gap: 16px;
		margin-bottom: 1.5rem;
	}

	.request-view-page .rv-section-grid > .rv-card {
		flex: 1 1 240px;
		min-width: 220px;
		max-width: 100%;
	}

	.request-view-page .rv-bottom-grid {
		display: flex;
		flex-wrap: wrap;
		align-items: stretch;
		gap: 16px;
		margin-bottom: 1.5rem;
	}

	.request-view-page .rv-bottom-grid > .rv-card--samples {
		flex: 2 1 420px;
		min-width: 280px;
	}

	.request-view-page .rv-bottom-grid > .rv-card--actions {
		flex: 1 1 240px;
		min-width: 220px;
		max-width: 320px;
	}

	.request-view-page .rv-card {
		display: flex;
		flex-direction: column;
		border-radius: 10px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		overflow: hidden;
		min-height: 100%;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		background: #fff;
	}

	.request-view-page .rv-card-body {
		display: flex;
		flex-direction: column;
		flex: 1 1 auto;
		padding: 10px 14px 14px;
		gap: 8px;
	}

	.request-view-page .rv-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		padding: 10px 14px 0;
	}

	.request-view-page .rv-card-header--plain { padding-bottom: 0; }
	.request-view-page .rv-card-header--actions { padding-top: 12px; }

	.request-view-page .rv-card-tag {
		display: inline-block;
		background: var(--workflow-accent-soft, var(--color-primary-soft, #fdf2f2));
		color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		font-size: 0.68rem;
		font-weight: 700;
		letter-spacing: 0.02em;
		padding: 3px 8px;
		border-radius: 999px;
	}

	.request-view-page .rv-card-header-icon {
		color: rgba(30, 41, 59, 0.4);
		font-size: 1rem;
	}

	.request-view-page .rv-card-title {
		margin: 0;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.95rem;
		font-weight: 700;
		color: #1e293b;
		line-height: 1.3;
	}

	.request-view-page .rv-card-title--sm { font-size: 0.9rem; }

	.request-view-page .rv-card-subtitle {
		font-size: 0.72rem;
		color: #64748b;
		margin-top: 1px;
	}

	.request-view-page .rv-card--sand,
	.request-view-page .rv-card--sky,
	.request-view-page .rv-card--sage,
	.request-view-page .rv-card--lavender,
	.request-view-page .rv-card--mist,
	.request-view-page .rv-card--white {
		background: #fff;
		border-color: var(--workflow-border, #e2e8f0);
	}

	.request-view-page .rv-card--actions-light {
		background: #fff;
		border-color: var(--workflow-border, #e2e8f0);
		color: #1e293b;
	}

	.request-view-page .rv-field-list {
		list-style: none; margin: 0; padding: 0;
		display: flex; flex-direction: column; gap: 7px; flex: 1 1 auto;
	}
	.request-view-page .rv-field-list--compact { gap: 6px; }
	.request-view-page .rv-field-item { display: flex; align-items: flex-start; gap: 8px; }
	.request-view-page .rv-field-item > .mdi {
		margin-top: 1px; color: rgba(30, 41, 59, 0.5); font-size: 0.95rem; flex-shrink: 0;
	}
	.request-view-page .rv-field-label {
		display: block; font-size: 0.65rem; font-weight: 600; text-transform: uppercase;
		letter-spacing: 0.03em; color: #64748b; margin-bottom: 0;
	}
	.request-view-page .rv-field-value {
		display: block; font-size: 0.8125rem; font-weight: 600; color: #1e293b;
		line-height: 1.35; word-break: break-word;
	}
	.request-view-page .rv-contact-block {
		margin-top: auto; padding-top: 8px; border-top: 1px solid rgba(30, 41, 59, 0.1);
	}
	.request-view-page .rv-contact-heading {
		display: block; font-size: 0.7rem; font-weight: 700; color: #475569; margin-bottom: 6px;
	}
	.request-view-page .rv-pill-row { display: flex; flex-wrap: wrap; gap: 5px; }
	.request-view-page .rv-pill {
		display: inline-block; background: rgba(255,255,255,0.75); border: 1px solid rgba(30,41,59,0.1);
		border-radius: 999px; padding: 2px 8px; font-size: 0.7rem; font-weight: 600; color: #1e293b;
	}
	.request-view-page .rv-card--white .rv-pill { background: #f1f5f9; }

	.request-view-page .rv-samples-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
	.request-view-page .rv-meta-label {
		font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;
	}
	.request-view-page .rv-status-badge {
		display: inline-block; background: var(--workflow-accent-soft, #fdf2f2);
		color: var(--workflow-accent, var(--color-primary)); border: 1px solid var(--color-primary-border-soft, #f0d4d4);
		border-radius: 999px; padding: 2px 8px; font-size: 0.7rem; font-weight: 600;
	}
	.request-view-page .rv-btn-compact {
		height: 28px; padding: 0 10px; font-size: 0.75rem; font-weight: 600; border-radius: 6px;
		display: inline-flex; align-items: center; gap: 4px;
	}
	.request-view-page .rv-sample-table-wrap { overflow-x: auto; flex: 1 1 auto; }
	.request-view-page .rv-sample-table {
		width: 100%;
		border-collapse: collapse;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8125rem;
	}
	.request-view-page .rv-sample-table th {
		text-align: left; font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
		letter-spacing: 0.03em; color: #64748b; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; white-space: nowrap;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
	}
	.request-view-page .rv-sample-table td {
		padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #1e293b; font-weight: 500;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
	}
	.request-view-page .rv-sample-table tbody tr:last-child td { border-bottom: none; }
	.request-view-page .rv-sample-index { font-weight: 700; color: #94a3b8; width: 2rem; }
	.request-view-page .rv-sample-id { font-weight: 700; color: #1e293b; }
	.request-view-page .rv-test-codes { display: flex; flex-wrap: wrap; gap: 4px; }
	.request-view-page .rv-test-code {
		display: inline-block; background: #f8fafc;
		color: #475569; border: 1px solid #e2e8f0;
		border-radius: 4px; padding: 1px 5px; font-size: 0.62rem; font-weight: 600;
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; letter-spacing: 0.01em;
		line-height: 1.35; max-width: 14rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
	}
	.request-view-page .rv-empty-copy { font-size: 0.8125rem; color: #64748b; }
	.request-view-page .rv-samples-footer { margin-top: auto; padding-top: 6px; }

	.request-view-page .rv-actions-body { gap: 8px; }
	.request-view-page .rv-actions-list {
		list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; flex: 1 1 auto;
	}
	.request-view-page .rv-action-form { margin: 0; display: block; width: 100%; }
	.request-view-page .rv-action-btn {
		display: flex; align-items: center; gap: 0.45rem; width: 100%; border-radius: 8px;
		padding: 0.45rem 0.7rem; font-size: 0.8125rem; font-weight: 600; text-align: left;
		border: 1px solid transparent; cursor: pointer; text-decoration: none; min-height: 34px;
		transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
	}
	.request-view-page .rv-action-btn .mdi { font-size: 1rem; flex-shrink: 0; }
	.request-view-page .rv-action-btn--primary,
	.request-view-page .rv-action-btn--accent {
		background: #fff;
		border-color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		justify-content: center;
		padding: 0.55rem 0.85rem;
	}
	.request-view-page .rv-action-btn--primary:hover,
	.request-view-page .rv-action-btn--primary:focus,
	.request-view-page .rv-action-btn--accent:hover,
	.request-view-page .rv-action-btn--accent:focus {
		background: var(--workflow-accent-soft, #fdf2f2);
		border-color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		text-decoration: none;
		filter: none;
	}
	.request-view-page .rv-action-btn--secondary {
		background: #fff; border-color: var(--workflow-border, #e2e8f0); color: #334155;
	}
	.request-view-page .rv-action-btn--secondary:hover,
	.request-view-page .rv-action-btn--secondary:focus {
		background: var(--workflow-accent-soft, #fdf2f2);
		border-color: var(--color-primary-border-soft, #f0d4d4);
		color: var(--workflow-accent, var(--color-primary));
		text-decoration: none;
	}
	.request-view-page .rv-actions-danger { margin-top: auto; padding-top: 8px; border-top: 1px solid #f1f5f9; }
	.request-view-page .rv-action-btn--danger {
		background: #fff; border-color: #fecaca; color: #b91c1c;
	}
	.request-view-page .rv-action-btn--danger:hover,
	.request-view-page .rv-action-btn--danger:focus {
		background: #fef2f2; border-color: #fca5a5; color: #991b1b; text-decoration: none;
	}

	@media (max-width: 991.98px) {
		.request-view-page .rv-bottom-grid > .rv-card--actions { max-width: none; }
	}
	@media (prefers-reduced-motion: reduce) {
		.request-view-page .rv-action-btn { transition: none; }
	}

	/* Header bar — base layout; burgundy gradient applied via workflow-theme */
	.request-view-page .batch-header-bar {
		border-radius: 12px;
		padding: 18px 22px;
		margin-bottom: 1.25rem;
	}

	.request-view-page:not(.workflow-theme) .batch-header-bar {
		background: #fff;
		border: 1px solid var(--workflow-border, #e2e8f0);
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.1));
	}

	.request-view-page .batch-header-top {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 12px;
	}

	.request-view-page .batch-title-group {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 6px;
	}

	.request-view-page .request-view-title {
		font-size: 1.65rem;
		font-weight: 800;
		color: var(--workflow-text-main, #1e293b);
		margin: 0;
		line-height: 1.2;
		letter-spacing: -0.02em;
	}

	.request-view-page.workflow-theme .request-view-title {
		color: #ffffff !important;
	}

	.request-view-page .request-view-form-name {
		font-size: 0.9rem;
		color: var(--workflow-text-muted, #64748b);
		margin: 0;
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.request-view-page .request-view-meta {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 2px;
	}

	.request-view-page .request-view-meta .text-muted {
		font-size: 0.85rem;
	}

	.request-view-page .batch-header-actions .btn {
		border-radius: 8px;
		font-weight: 600;
		font-size: 0.82rem;
		padding: 6px 14px;
	}

	.request-view-page .batch-header-actions .btn-outline-secondary:hover,
	.request-view-page .batch-header-actions .btn-outline-secondary:focus,
	.request-view-page .batch-header-actions .btn-outline-secondary:active,
	.request-view-page .batch-header-actions .btn-outline-secondary.show {
		background: #f8fafc;
		border-color: #f8fafc;
		color: var(--color-primary);
		box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.35);
	}

	.request-view-page .batch-header-actions .btn-group {
		position: relative;
	}

	.request-view-page .batch-header-actions .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		z-index: 1050;
	}

	.request-view-page .request-view-actions-dropdown .dropdown-toggle::after {
		margin-left: 0.45rem;
		vertical-align: 0.15em;
	}

	.request-view-page .request-view-actions-menu {
		min-width: 15.5rem;
		max-width: 20rem;
		padding: 0.35rem 0;
		margin-top: 0.35rem;
		border: 1px solid var(--color-border);
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		overflow: visible;
	}

	.request-view-page .request-view-actions-menu .dropdown-divider {
		margin: 0.35rem 0;
		border-top-color: #e8eef4;
	}

	.request-view-page .request-view-actions-form {
		margin: 0;
		padding: 0;
		display: block;
		width: 100%;
	}

	.request-view-page .request-view-actions-menu .dropdown-item {
		display: flex;
		align-items: center;
		gap: 0.65rem;
		width: 100%;
		padding: 0.55rem 1rem;
		font-size: 0.8125rem;
		font-weight: 500;
		line-height: 1.35;
		color: #111827;
		border: none;
		background: transparent;
		text-align: left;
		white-space: normal;
	}


	.request-view-page .request-view-actions-menu .dropdown-item > i.mdi {
		flex-shrink: 0;
		width: 1.125rem;
		font-size: 1.05rem;
		line-height: 1;
		text-align: center;
		color: #64748b;
	}

	.request-view-page .request-view-actions-menu .dropdown-item > span {
		flex: 1;
		min-width: 0;
	}

	.request-view-page .request-view-actions-menu .dropdown-item:hover,
	.request-view-page .request-view-actions-menu .dropdown-item:focus {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item:active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger {
		color: #334155 !important;
		background: transparent !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger > i.mdi {
		color: #64748b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover > i.mdi,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus > i.mdi {
		color: #475569 !important;
	}

	.request-view-page .workflow-status-chip--in-review {
		background: #eef2ff;
		color: #3b5fc0;
		border-color: #c7d7fc;
	}

	.request-view-page .workflow-status-chip--submitted {
		background: #ecfeff;
		color: #0e7490;
		border-color: #a5f3fc;
	}

	.request-view-page .workflow-status-chip--approved,
	.request-view-page .workflow-status-chip--complete {
		background: #f0fdf4;
		color: #15803d;
		border-color: #bbf7d0;
	}

	.request-view-page .workflow-status-chip--rejected {
		background: #fff1f2;
		color: #be123c;
		border-color: #fecdd3;
	}

	.request-view-page .priority-chip {
		display: inline-flex;
		align-items: center;
		padding: 4px 10px;
		border-radius: 20px;
		font-weight: 600;
		font-size: 0.75rem;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #475569;
	}

	.request-view-page .priority-chip--normal {
		background: #ecfeff;
		color: #0e7490;
		border-color: #a5f3fc;
	}

	.request-view-page .priority-chip--high,
	.request-view-page .priority-chip--urgent {
		background: #fffbeb;
		color: #b45309;
		border-color: #fde68a;
	}

	/* Alerts */
	.request-view-page .request-view-alerts .alert {
		border-radius: 10px;
		border: 1px solid rgba(0, 0, 0, 0.06);
	}

	/* Stat cards — first card status text */
	.request-view-page .stat-card .stat-status-label {
		font-size: 0.95rem;
		font-weight: 700;
		color: var(--workflow-text-main, #1e293b);
	}

	/* Tabs */
	.request-view-page .batch-tabs-panel .batch-nav-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 2px;
		padding: 0 14px;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		background: #fff;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-item {
		margin-bottom: -1px;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		border: none;
		border-bottom: 2px solid transparent;
		border-radius: 0;
		color: var(--workflow-text-muted, #64748b);
		padding: 10px 12px;
		font-weight: 600;
		font-size: 0.8125rem;
		background: transparent;
		transition: color 0.15s ease, border-color 0.15s ease;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
		color: var(--color-primary);
		border-bottom-color: #cbd5e1;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active {
		color: var(--color-primary);
		border-bottom-color: var(--color-primary);
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.focus {
		color: var(--color-primary);
		border-bottom: 2px solid #cbd5e1;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active:focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active.focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active:active {
		border-bottom-color: var(--color-primary);
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
		color: var(--workflow-accent, #3b5fc0);
		border-bottom-color: #cbd5e1;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active {
		color: var(--workflow-accent, #3b5fc0);
		border-bottom-color: var(--workflow-accent, #3b5fc0);
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:active {
		outline: none;
		box-shadow: none;
		border: none;
		border-radius: 0;
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.focus {
		color: var(--workflow-accent, #3b5fc0);
		border-bottom: 2px solid #cbd5e1;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active:focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active.focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active:active {
		border-bottom-color: var(--workflow-accent, #3b5fc0);
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link .badge {
		font-size: 0.7rem;
		font-weight: 700;
		padding: 2px 7px;
		border-radius: 999px;
		background: #e2e8f0;
		color: #475569;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active .badge {
		background: var(--workflow-accent-soft, #f0f4ff);
		color: var(--workflow-accent, #3b5fc0);
	}

	.request-view-page .batch-tabs-panel .tab-content {
		padding: 14px 16px 16px;
	}

	/* Captured request details panel */
	.request-view-page .captured-details-panel-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.request-view-page .captured-details-panel-subtitle {
		margin: 4px 0 0;
		padding-left: 28px;
		font-size: 0.82rem;
		color: var(--workflow-text-muted, #64748b);
		font-weight: 400;
		line-height: 1.4;
	}

	.request-view-page .captured-details-panel-body {
		background: var(--workflow-bg, #f8fafc);
		padding: 20px 24px 24px;
	}

	.request-view-page .clinical-form-display {
		display: grid;
		grid-template-columns: 1fr;
		gap: 1.25rem;
		width: 100%;
		align-items: start;
		font-family: inherit;
	}

	/* Section cards */
	.request-view-page .clinical-form-display .clinical-section-card {
		background: #fff;
		border: 1px solid var(--color-primary, var(--color-primary));
		border-radius: 12px;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
		overflow: hidden;
		margin-bottom: 0;
		min-width: 0;
		transition: box-shadow 0.2s ease, transform 0.2s ease;
	}

	@media (prefers-reduced-motion: no-preference) {
		.request-view-page .clinical-form-display .clinical-section-card:hover {
			box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
			transform: translateY(-1px);
		}
	}

	.request-view-page .clinical-form-display .clinical-section-header {
		background: #f0f4f8;
		border: none;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px 12px 0 0;
		padding: 14px 20px;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		text-align: left;
		cursor: pointer;
		transition: background-color 0.2s ease;
	}

	.request-view-page .clinical-form-display .clinical-section-header:hover,
	.request-view-page .clinical-form-display .clinical-section-header:focus {
		background: #e8edf3;
	}

	.request-view-page .clinical-form-display .clinical-section-header:focus-visible {
		outline: 2px solid var(--color-primary, var(--color-primary));
		outline-offset: -2px;
	}

	.request-view-page .clinical-form-display .clinical-section-header-text {
		display: flex;
		flex-direction: column;
		gap: 4px;
		min-width: 0;
		flex: 1;
	}

	.request-view-page .clinical-form-display .clinical-section-title {
		font-size: 1.05rem;
		font-weight: 600;
		color: var(--color-primary, var(--color-primary));
		margin: 0;
		display: flex;
		align-items: center;
		gap: 0.5rem;
	}

	.request-view-page .clinical-form-display .clinical-section-icon {
		color: var(--color-primary, var(--color-primary));
		font-size: 1.1rem;
		flex-shrink: 0;
	}

	.request-view-page .clinical-form-display .clinical-section-description {
		margin: 0 0 0 1.75rem;
		color: var(--color-primary, var(--color-primary));
		opacity: 0.85;
		font-size: 0.82rem;
		line-height: 1.45;
		font-weight: 400;
	}

	.request-view-page .clinical-form-display .clinical-section-chevron {
		color: var(--color-primary, var(--color-primary));
		font-size: 1.35rem;
		flex-shrink: 0;
		transition: transform 0.2s ease;
	}

	.request-view-page .clinical-form-display .clinical-section-toggle[aria-expanded="false"] .clinical-section-chevron {
		transform: rotate(-90deg);
	}

	.request-view-page .clinical-form-display .clinical-section-card:has(.clinical-section-toggle[aria-expanded="false"]) .clinical-section-header {
		border-radius: 12px;
	}

	.request-view-page .clinical-form-display .clinical-section-content {
		border: none;
		border-radius: 0;
		padding: 16px 20px 20px;
		background: #fff;
	}

	/* Field grid */
	.request-view-page .clinical-form-display .clinical-fields-holder {
		width: 100%;
	}

	.request-view-page .clinical-form-display .clinical-fields-grid {
		display: grid;
		gap: 1rem;
		width: 100%;
	}

	.request-view-page .clinical-form-display .clinical-grid-1 {
		grid-template-columns: 1fr;
	}

	.request-view-page .clinical-form-display .clinical-grid-2 {
		grid-template-columns: repeat(2, 1fr);
	}

	.request-view-page .clinical-form-display .clinical-grid-3 {
		grid-template-columns: repeat(3, 1fr);
	}

	@media (max-width: 1200px) {
		.request-view-page .clinical-form-display .clinical-grid-3 {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	@media (max-width: 768px) {
		.request-view-page .clinical-form-display .clinical-grid-2,
		.request-view-page .clinical-form-display .clinical-grid-3 {
			grid-template-columns: 1fr;
		}

		.request-view-page .captured-details-panel-body {
			padding: 16px;
		}

		.request-view-page .clinical-form-display .clinical-section-header,
		.request-view-page .clinical-form-display .clinical-section-content {
			padding-left: 16px;
			padding-right: 16px;
		}
	}

	.request-view-page .clinical-form-display .clinical-field {
		display: flex;
		flex-direction: column;
	}

	.request-view-page .clinical-form-display .clinical-field-label {
		font-size: 0.75rem;
		letter-spacing: normal;
		text-transform: none;
		color: var(--workflow-text-muted, #64748b);
		font-weight: 600;
		margin-bottom: 0.4rem;
	}

	.request-view-page .clinical-form-display .clinical-required {
		color: #ef4444;
		margin-left: 0.25rem;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		padding: 0.75rem 1rem;
		min-height: 2.75rem;
		background: #f1f5f9;
		display: flex;
		align-items: center;
		transition: border-color 0.2s ease;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box--signature,
	.request-view-page .clinical-form-display .clinical-field-value-box--textarea {
		min-height: 5rem;
		align-items: flex-start;
		padding-top: 0.875rem;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box--signature {
		align-items: center;
		justify-content: flex-start;
	}

	.request-view-page .clinical-form-display .clinical-field-value {
		font-size: 0.875rem;
		font-weight: 500;
		color: var(--workflow-text-main, #1e293b);
		word-break: break-word;
		line-height: 1.5;
	}

	.request-view-page .clinical-form-display .clinical-field-value--empty {
		color: #94a3b8;
		font-style: italic;
		font-weight: 400;
	}

	/* Tables */
	.request-view-page .clinical-form-display .clinical-rows-holder {
		margin-top: 0.25rem;
	}

	.request-view-page .clinical-form-display .clinical-table-wrapper {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		overflow-x: auto;
		overflow-y: hidden;
		-webkit-overflow-scrolling: touch;
		max-width: 100%;
		background: #fff;
	}

	.request-view-page .clinical-form-display .clinical-data-table {
		width: max-content;
		min-width: 100%;
		margin: 0;
		border-collapse: collapse;
	}

	.request-view-page .clinical-form-display .clinical-table-header {
		background: #475569;
	}

	.request-view-page .clinical-form-display .clinical-table-th {
		font-size: 0.8rem;
		font-weight: 600;
		color: #fff;
		text-transform: none;
		letter-spacing: normal;
		padding: 12px 14px;
		text-align: left;
		white-space: nowrap;
		border: none;
	}

	.request-view-page .clinical-form-display .clinical-row-index {
		width: 52px;
		text-align: center;
		background: #f8fafc;
		font-weight: 600;
		color: #94a3b8;
		font-size: 0.8rem;
	}

	.request-view-page .clinical-form-display .clinical-table-header .clinical-row-index {
		background: #3d4f63;
		color: #e2e8f0;
	}

	.request-view-page .clinical-form-display .clinical-table-row {
		border-bottom: 1px solid #e2e8f0;
		transition: background-color 0.15s ease;
	}

	.request-view-page .clinical-form-display .clinical-table-row--populated {
		background-color: #ecfdf5;
	}

	.request-view-page .clinical-form-display .clinical-table-row--populated:hover {
		background-color: #d1fae5;
	}

	.request-view-page .clinical-form-display .clinical-table-row:not(.clinical-table-row--populated):hover {
		background-color: #f8fafc;
	}

	.request-view-page .clinical-form-display .clinical-table-row:last-child {
		border-bottom: none;
	}

	.request-view-page .clinical-form-display .clinical-table-td {
		padding: 12px 14px;
		font-size: 0.875rem;
		color: var(--workflow-text-main, #1e293b);
		vertical-align: middle;
		border: none;
	}

	.request-view-page .clinical-form-display .clinical-table-td .clinical-field-value {
		font-size: 0.875rem;
	}

	/* Signature, files, empty state */
	.request-view-page .clinical-form-display .clinical-signature {
		max-width: 140px;
		max-height: 64px;
		border-radius: 4px;
		display: block;
	}

	.request-view-page .clinical-form-display .clinical-file-link {
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
		color: var(--workflow-accent, var(--color-primary));
		font-weight: 500;
		font-size: 0.875rem;
		text-decoration: none;
		transition: color 0.15s ease;
	}

	.request-view-page .clinical-form-display .clinical-file-link:hover {
		color: #8c1419;
		text-decoration: underline;
	}

	.request-view-page .clinical-form-display .clinical-file-link:focus-visible {
		outline: 2px solid var(--workflow-accent, var(--color-primary));
		outline-offset: 2px;
		border-radius: 4px;
	}

	.request-view-page .clinical-form-display .clinical-empty-state {
		text-align: center;
		padding: 2.5rem 1rem;
		color: #94a3b8;
	}

	.request-view-page .clinical-form-display .clinical-empty-state i {
		font-size: 2rem;
		margin-bottom: 0.5rem;
		opacity: 0.5;
		display: block;
	}

	.request-view-page .clinical-form-display .clinical-empty-state p {
		margin: 0;
		font-size: 0.875rem;
		font-weight: 500;
	}

	/* Notes composer */
	.request-view-page .request-notes-composer {
		background: #f8fafc;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		padding: 16px 18px;
		margin-bottom: 1.25rem;
	}

	.request-view-page .request-notes-composer h6 {
		font-size: 0.85rem;
		font-weight: 700;
		color: var(--workflow-text-main, #1e293b);
		margin-bottom: 12px;
	}

	.request-view-page .request-notes-composer .form-control {
		border-radius: 8px;
		border-color: var(--workflow-border, #e2e8f0);
		font-size: 0.875rem;
	}

	.request-view-page .request-notes-composer .btn-primary {
		border-radius: 8px;
		font-weight: 600;
	}

	.request-view-page .batch-tabs-panel {
		margin-top: 0.25rem;
		margin-bottom: 1rem;
	}

	.request-view-page .rv-col-actions {
		width: 2.5rem;
		text-align: center;
		white-space: nowrap;
	}

	.request-view-page .rv-icon-btn {
		display: inline-flex;
		align-items: center;
		gap: 3px;
		border: 1px solid #e2e8f0;
		background: #fff;
		color: var(--workflow-accent, var(--color-primary));
		border-radius: 6px;
		padding: 2px 6px;
		font-size: 0.65rem;
		font-weight: 700;
		cursor: pointer;
		line-height: 1;
	}

	.request-view-page .rv-icon-btn--solo {
		padding: 4px 6px;
		font-size: 1rem;
	}

	.request-view-page .rv-icon-btn:hover,
	.request-view-page .rv-icon-btn:focus {
		background: var(--workflow-accent-soft, #fdf2f2);
		border-color: var(--color-primary-border-soft, #f0d4d4);
	}

	.request-view-page .rv-modal-backdrop {
		position: fixed;
		inset: 0;
		z-index: 1055;
		background: rgba(15, 23, 42, 0.45);
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1rem;
	}

	.request-view-page .rv-modal {
		background: #fff;
		border-radius: 10px;
		border: 1px solid #e2e8f0;
		box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
		width: min(560px, 100%);
		max-height: min(80vh, 640px);
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	.request-view-page .rv-modal-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 12px 14px;
		border-bottom: 1px solid #e2e8f0;
	}

	.request-view-page .rv-modal-title {
		margin: 0;
		font-size: 0.95rem;
		font-weight: 700;
		color: #1e293b;
	}

	.request-view-page .rv-modal-close {
		border: none;
		background: transparent;
		color: #64748b;
		font-size: 1.15rem;
		line-height: 1;
		padding: 2px;
		cursor: pointer;
	}

	.request-view-page .rv-modal-body {
		padding: 14px;
		overflow: auto;
	}

	.request-view-page .rv-test-codes--modal .rv-test-code {
		max-width: none;
		white-space: normal;
		font-size: 0.7rem;
	}

	.request-view-page .rv-detail-grid {
		margin: 0;
	}

	.request-view-page .rv-detail-row {
		display: grid;
		grid-template-columns: minmax(120px, 38%) 1fr;
		gap: 8px 12px;
		padding: 8px 0;
		border-bottom: 1px solid #f1f5f9;
	}

	.request-view-page .rv-detail-row:last-child {
		border-bottom: none;
	}

	.request-view-page .rv-detail-row dt {
		margin: 0;
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: #64748b;
	}

	.request-view-page .rv-detail-row dd {
		margin: 0;
		font-size: 0.8125rem;
		font-weight: 600;
		color: #1e293b;
		word-break: break-word;
	}

	.request-view-page [x-cloak] {
		display: none !important;
	}

	/* Receive / Move to In Review modal — match workflow board compact confirm */
	.request-view-page #receive-sample-modal .modal-content {
		max-height: calc(100vh - 2rem);
	}

	.request-view-page #receive-sample-modal .modal-body {
		padding: 0 1.5rem 1.25rem;
		overflow-y: auto;
		overscroll-behavior: contain;
		min-height: 4rem;
	}

	.request-view-page #receive-sample-modal.receive-sample-modal--compact .modal-body {
		padding: 0 1.25rem 0.75rem;
	}

	.request-view-page #receive-sample-modal .receive-sample-modal-body {
		padding: 0;
	}

	/* Request Info card — batch-details 4-col read-only */
	.request-view-page .rv-request-info-panel {
		margin-bottom: 0.75rem;
	}

	.request-view-page .rv-request-info-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		width: 100%;
		padding: 10px 14px;
		border: none;
		border-radius: 10px 10px 0 0;
		background: transparent;
		cursor: pointer;
		text-align: left;
	}

	.request-view-page .rv-request-info-panel:has(.rv-request-info-toggle[aria-expanded="false"]) .rv-request-info-toggle {
		border-radius: 10px;
	}

	.request-view-page .rv-request-info-toggle:hover,
	.request-view-page .rv-request-info-toggle:focus {
		background: #f8fafc;
	}

	.request-view-page .rv-request-info-toggle:focus-visible {
		outline: 2px solid var(--color-primary, #8B1A1A);
		outline-offset: -2px;
	}

	.request-view-page .rv-request-info-toggle h5 {
		font-size: 0.875rem;
		margin: 0;
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		font-weight: 700;
		color: #1e293b;
	}

	.request-view-page .rv-request-info-chevron {
		font-size: 1.25rem;
		color: #64748b;
		flex-shrink: 0;
		line-height: 1;
	}

	.request-view-page .rv-request-info-panel .workflow-board-panel-body {
		padding: 6px 10px 8px;
		border-top: 1px solid var(--workflow-border, #e2e8f0);
	}

	.request-view-page .rv-request-info-grid {
		margin-top: 1rem;
		row-gap: 1rem;
	}

	.request-view-page .rv-request-info-grid .rv-info-field {
		margin-bottom: 0 !important;
	}

	.request-view-page .rv-request-info-grid .rv-info-label,
	.request-view-page .rv-request-info-grid .control-label {
		display: block;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.75rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: #0a0a0a;
		margin-bottom: 0.2rem;
	}

	.request-view-page .rv-info-value {
		min-height: 34px;
		height: auto;
		padding: 0.35rem 0.65rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8125rem !important;
		font-weight: 500 !important;
		line-height: 1.35 !important;
		color: #1e293b;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		display: flex;
		align-items: center;
		white-space: pre-wrap;
		word-break: break-word;
		cursor: default;
		box-shadow: none;
	}

	.request-view-page .rv-info-value--emphasis {
		font-weight: 700 !important;
		color: #0a0a0a;
	}

	.request-view-page .rv-info-remarks {
		min-height: 72px;
		height: auto;
		padding: 0.5rem 0.65rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8125rem !important;
		font-weight: 500 !important;
		color: #1e293b;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		white-space: pre-wrap;
		word-break: break-word;
		cursor: default;
		line-height: 1.45;
	}

	.request-view-page .rv-header-primary-btn {
		font-weight: 600;
		font-size: 0.75rem;
		min-height: 32px;
		display: inline-flex;
		align-items: center;
		gap: 4px;
	}

	.request-view-page .rv-tests-tab-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 8px;
		padding: 10px 14px;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		background: #fff;
	}

	.request-view-page .rv-tests-tab-header h5 {
		font-size: 0.875rem;
		font-weight: 700;
		margin: 0;
	}

	.request-view-page .rv-samples-count-badge {
		display: inline-block;
		background: #f1f5f9;
		color: #475569;
		border: 1px solid #e2e8f0;
		border-radius: 999px;
		padding: 2px 8px;
		font-size: 0.68rem;
		font-weight: 700;
	}

	.request-view-page .rv-tests-table {
		font-size: 0.8125rem;
	}

	.request-view-page .rv-tests-table thead th {
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: #64748b;
		padding: 8px 10px;
		white-space: nowrap;
		background: #f8fafc;
	}

	.request-view-page .rv-tests-table tbody td {
		padding: 8px 10px;
		vertical-align: middle;
		font-size: 0.8125rem;
		font-weight: 500;
		color: #1e293b;
	}

	.request-view-page .rv-tests-table .btn-icon {
		width: 28px;
		height: 28px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 6px;
	}

	.request-view-page .rv-sample-description-cell {
		max-width: 220px;
		vertical-align: middle;
	}

	.request-view-page .rv-sample-description-inline {
		font-size: 0.8125rem;
		line-height: 1.4;
		font-weight: 500;
		color: #1e293b;
		word-break: break-word;
	}

	.request-view-page .rv-sample-description-inline p,
	.request-view-page .rv-sample-description-inline div {
		margin: 0;
	}

	.request-view-page .rv-modal--wide {
		width: min(720px, 100%);
	}

	.request-view-page .rv-sample-row-edit-dialog {
		position: relative;
		overflow: hidden;
	}

	.request-view-page .rv-sample-row-edit-dialog:not(.is-ready) .rv-modal-header,
	.request-view-page .rv-sample-row-edit-dialog:not(.is-ready) .rv-modal-body,
	.request-view-page .rv-sample-row-edit-dialog:not(.is-ready) .rv-modal-footer {
		visibility: hidden;
	}

	.request-view-page .rv-sample-row-edit-loading {
		position: absolute;
		inset: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		background: rgba(255, 255, 255, 0.92);
		z-index: 2;
	}

	.request-view-page .rv-sample-row-edit-dialog.is-ready .rv-sample-row-edit-loading {
		display: none;
	}

	.request-view-page .rv-sample-row-select2-wrap .select2-container {
		width: 100% !important;
	}

	.request-view-page .rv-sample-row-edit-dialog .rv-modal-body {
		overflow-x: hidden;
	}

	.request-view-page .rv-qty-unit-wrap .form-control-sm {
		min-width: 0;
	}

	.request-view-page .rv-test-requirements-checkboxes .form-check-label {
		font-size: 0.8125rem;
	}

	.request-view-page .rv-param-group-title {
		font-size: 0.8rem;
		font-weight: 700;
		color: #334155;
		margin-bottom: 6px;
	}

	.request-view-page .rv-param-table {
		font-size: 0.8125rem;
	}

	.request-view-page .rv-param-table thead th {
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: #64748b;
		background: #f8fafc;
		padding: 6px 8px;
	}

	.request-view-page .rv-param-table td {
		padding: 6px 8px;
	}

	.request-view-page .rv-richtext-readonly {
		font-size: 0.8125rem;
		line-height: 1.5;
		color: #1e293b;
		min-height: 80px;
		padding: 10px 12px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #f8fafc;
	}

	.request-view-page .rv-richtext-readonly p:last-child {
		margin-bottom: 0;
	}

	.request-view-page .batch-tabs-panel .tab-content {
		padding: 0;
	}

	.request-view-page .batch-tabs-panel .tab-content > .rv-tests-tab {
		padding: 0;
	}

	.request-view-page .batch-tabs-panel .tab-pane-pad {
		padding: 14px 16px 16px;
	}

</style>
