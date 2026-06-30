<style>
	.request-view-page.workflow-board-page {
		padding-bottom: 2rem;
		background: var(--workflow-bg, #f8fafc);
		min-height: calc(100vh - 56px);
	}

	.request-view-page .request-view-shell {
		background: transparent;
	}

	.request-view-page .breadcrumb-container {
		margin-bottom: 1rem;
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
		background: #f3f4f6;
		color: #111827;
	}

	.request-view-page .request-view-actions-menu .dropdown-item:active {
		background: #e5e7eb;
		color: #111827;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger,
	.request-view-page .request-view-actions-menu .dropdown-item-danger > i.mdi {
		color: #be123c;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus {
		background: #fff1f2;
		color: #9f1239;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover > i.mdi,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus > i.mdi {
		color: #be123c;
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
		padding: 0 20px;
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
		padding: 14px 16px;
		font-weight: 600;
		font-size: 0.875rem;
		background: transparent;
		transition: color 0.15s ease, border-color 0.15s ease;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
		color: #6D0A0E;
		border-bottom-color: #cbd5e1;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active {
		color: #6D0A0E;
		border-bottom-color: #6D0A0E;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.focus {
		color: #6D0A0E;
		border-bottom: 2px solid #cbd5e1;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active:focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active.focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active:active {
		border-bottom-color: #6D0A0E;
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
		padding: 20px 24px 24px;
	}

	/* Clinical form inside captured details panel */
	.request-view-page .clinical-form-display .clinical-section-card {
		background: transparent;
		box-shadow: none;
		margin-bottom: 1.25rem;
		border-radius: 0;
		overflow: visible;
	}

	.request-view-page .clinical-form-display .clinical-section-card:last-child {
		margin-bottom: 0;
	}

	.request-view-page .clinical-form-display .clinical-section-header {
		background: #f8fafc;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px 10px 0 0;
		padding: 12px 16px;
		border-bottom: none;
	}

	.request-view-page .clinical-form-display .clinical-section-title {
		font-size: 0.95rem;
		font-weight: 700;
		color: var(--workflow-text-main, #1e293b);
	}

	.request-view-page .clinical-form-display .clinical-section-title .mdi {
		color: var(--workflow-accent, #3b5fc0) !important;
		margin-right: 0.5rem !important;
		font-size: 1.1rem;
	}

	.request-view-page .clinical-form-display .clinical-section-description {
		margin-left: 1.75rem;
		color: var(--workflow-text-muted, #64748b);
		font-size: 0.82rem;
	}

	.request-view-page .clinical-form-display .clinical-section-content {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-top: none;
		border-radius: 0 0 10px 10px;
		padding: 16px;
		background: #fff;
	}

	.request-view-page .clinical-form-display .clinical-field-label {
		font-size: 0.7rem;
		letter-spacing: 0.04em;
		color: var(--workflow-text-muted, #64748b);
		font-weight: 700;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		padding: 10px 12px;
		min-height: 2.25rem;
		background: #fff;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box:hover {
		border-color: #cbd5e1;
	}

	.request-view-page .clinical-form-display .clinical-field-value {
		font-size: 0.875rem;
		font-weight: 500;
		color: var(--workflow-text-main, #1e293b);
	}

	.request-view-page .clinical-form-display .clinical-table-wrapper {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		overflow: hidden;
	}

	.request-view-page .clinical-form-display .clinical-table-header {
		background: #f8fafc;
	}

	.request-view-page .clinical-form-display .clinical-table-th {
		font-size: 0.7rem;
		color: var(--workflow-text-muted, #64748b);
		padding: 12px 14px;
		border-color: var(--workflow-border, #e2e8f0);
	}

	.request-view-page .clinical-form-display .clinical-table-td {
		padding: 12px 14px;
		font-size: 0.875rem;
		border-color: #f1f5f9;
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
</style>
