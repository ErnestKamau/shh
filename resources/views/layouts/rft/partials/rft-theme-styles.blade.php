<style>
	/* RFT tablet density — colors come from this app's theme tokens */
	.rft-theme.lab-panel-theme,
	.rft-layout .rft-theme {
		--workflow-accent: var(--color-primary);
		--workflow-accent-soft: var(--color-primary-soft);
		--workflow-accent-border: var(--color-primary-border-soft, var(--color-primary-soft));
		--workflow-border: var(--color-border, #e2e8f0);
		--workflow-muted: var(--color-muted, #64748b);

		/* Accent is white in AmSpec — do not use it for icons/fills */
		--rft-support: var(--color-success, #22c55e);
		--rft-support-hover: var(--color-success-strong, #16a34a);
		--rft-support-deep: var(--color-success-strong, #16a34a);
		--rft-support-soft: #f0fdf4;
		--rft-support-border: #bbf7d0;
		--rft-support-rgb: 34, 197, 94;
		--rft-support-text: #14532d;

		--rft-surface: var(--color-bg-app, #f8fafc);
		--rft-card-radius: 10px;
		--rft-control-h: 34px;
		--rft-font-base: 0.8125rem;
	}

	/* —— App shell (tablet-first) —— */
	.rft-layout {
		background: var(--rft-surface);
		-webkit-font-smoothing: antialiased;
	}

	.rft-main {
		margin-top: 52px;
		padding: 0.75rem 0 1.75rem;
	}

	.rft-theme {
		font-size: var(--rft-font-base);
	}

	.rft-page-shell {
		max-width: 1280px;
		margin-left: auto;
		margin-right: auto;
	}

	.rft-topbar {
		background: rgba(255, 255, 255, 0.96);
		backdrop-filter: blur(8px);
		border-bottom: 1px solid #e8ecf2;
		box-shadow: none;
		min-height: 52px;
		padding-top: 0;
		padding-bottom: 0;
	}

	.rft-topbar::after {
		content: '';
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		height: 2px;
		background: linear-gradient(90deg, var(--workflow-accent) 0%, var(--workflow-accent) 72%, var(--rft-support) 72%, var(--rft-support) 100%);
		pointer-events: none;
	}

	/* —— Base cards (ported from reference lab-panel; used on RFT page) —— */
	.lab-panel-theme .rft-card-row {
		margin-left: -12px;
		margin-right: -12px;
	}

	.lab-panel-theme .rft-card-row > [class*="col-"] {
		padding-left: 12px;
		padding-right: 12px;
	}

	.lab-panel-theme .rft-how-it-works-row > [class*="col-"] {
		display: flex;
	}

	.lab-panel-theme .rft-workflow-card {
		display: flex;
		align-items: flex-start;
		gap: 12px;
		width: 100%;
		height: 100%;
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		padding: 14px 16px;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
		cursor: pointer;
	}

	.lab-panel-theme .rft-workflow-card:not(:disabled):hover,
	.lab-panel-theme .rft-workflow-card.is-active {
		border-color: var(--workflow-accent-border);
		box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
	}

	.lab-panel-theme .rft-workflow-card.is-active {
		border-left: 3px solid var(--workflow-accent);
	}

	.lab-panel-theme .rft-workflow-card:disabled {
		cursor: default;
		opacity: 1;
	}

	.lab-panel-theme .rft-workflow-card-icon {
		flex-shrink: 0;
		width: 40px;
		height: 40px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--workflow-accent-soft);
		color: var(--card-accent, var(--workflow-accent));
		font-size: 1.25rem;
	}

	.lab-panel-theme .rft-workflow-card-body {
		min-width: 0;
	}

	.lab-panel-theme .rft-card-row > [class*="col-"] {
		display: flex;
	}

	.lab-panel-theme .rft-form-type-card {
		position: relative;
		display: flex;
		flex-direction: column;
		width: 100%;
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		padding: 16px 18px;
		height: 100%;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.lab-panel-theme .rft-form-type-card.is-filtered {
		border-color: var(--workflow-accent);
		box-shadow: 0 0 0 1px var(--workflow-accent-soft);
	}

	.lab-panel-theme .rft-form-type-card-menu {
		position: absolute;
		top: 8px;
		right: 8px;
		z-index: 6;
	}

	.lab-panel-theme .rft-card-menu-toggle {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 30px;
		height: 30px;
		padding: 0;
		border: 1px solid transparent;
		border-radius: 8px;
		background: transparent;
		color: #94a3b8;
		line-height: 1;
		cursor: pointer;
		transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
	}

	.lab-panel-theme .rft-card-menu-toggle .mdi {
		font-size: 1.15rem;
	}

	.lab-panel-theme .rft-form-type-card:hover .rft-card-menu-toggle,
	.lab-panel-theme .rft-form-type-card-menu:focus-within .rft-card-menu-toggle,
	.lab-panel-theme .rft-form-type-card-menu.is-open .rft-card-menu-toggle {
		color: #475569;
		background: #f8fafc;
		border-color: #e2e8f0;
	}

	.lab-panel-theme .rft-card-menu-toggle:hover,
	.lab-panel-theme .rft-card-menu-toggle:focus {
		color: var(--workflow-accent, #17135F);
		background: var(--workflow-accent-soft, #e8e9f3);
		border-color: var(--workflow-accent-border, #b8bbd4);
		outline: none;
	}

	.lab-panel-theme .rft-card-menu-dropdown {
		display: none;
		position: absolute;
		top: calc(100% + 2px);
		right: 0;
		min-width: 148px;
		padding: 6px;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
	}

	.lab-panel-theme .rft-card-menu-dropdown::before {
		content: '';
		position: absolute;
		top: -10px;
		left: 0;
		right: 0;
		height: 10px;
	}

	.lab-panel-theme .rft-form-type-card-menu:hover .rft-card-menu-dropdown,
	.lab-panel-theme .rft-form-type-card-menu:focus-within .rft-card-menu-dropdown,
	.lab-panel-theme .rft-form-type-card-menu.is-open .rft-card-menu-dropdown {
		display: block;
	}

	.lab-panel-theme .rft-card-menu-item {
		display: flex;
		align-items: center;
		gap: 8px;
		width: 100%;
		padding: 8px 10px;
		border: 0;
		border-radius: 7px;
		background: transparent;
		color: #334155;
		font-size: 0.8125rem;
		font-weight: 500;
		line-height: 1.2;
		text-align: left;
		text-decoration: none;
		cursor: pointer;
		transition: background 0.12s ease, color 0.12s ease;
	}

	.lab-panel-theme .rft-card-menu-item .mdi {
		font-size: 1rem;
		color: #64748b;
	}

	.lab-panel-theme .rft-card-menu-item:hover,
	.lab-panel-theme .rft-card-menu-item:focus {
		background: var(--workflow-accent-soft, #e8e9f3);
		color: var(--workflow-accent, #17135F);
		text-decoration: none;
		outline: none;
	}

	.lab-panel-theme .rft-card-menu-item:hover .mdi,
	.lab-panel-theme .rft-card-menu-item:focus .mdi {
		color: var(--workflow-accent, #17135F);
	}

	.lab-panel-theme .rft-form-type-card-header {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-bottom: 10px;
		padding-right: 28px;
	}

	.lab-panel-theme .rft-form-type-card-icon {
		flex-shrink: 0;
		width: 42px;
		height: 42px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--workflow-accent-soft);
		color: var(--workflow-accent);
		font-size: 1.3rem;
	}

	.lab-panel-theme .rft-form-type-card-desc {
		font-size: 0.85rem;
		color: var(--workflow-muted);
		line-height: 1.45;
		margin-bottom: 12px;
		min-height: 2.9em;
	}

	.lab-panel-theme .rft-form-type-card-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 10px 14px;
		font-size: 0.78rem;
		color: #64748b;
		margin-bottom: 14px;
	}

	.lab-panel-theme .rft-form-type-card-meta .mdi {
		font-size: 0.95rem;
		vertical-align: -1px;
	}

	.lab-panel-theme .rft-form-type-card-actions {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 8px;
		margin-top: auto;
		border-top: 1px solid #f1f5f9;
		padding-top: 12px;
	}

	.lab-panel-theme .rft-form-type-card-actions .rft-icon-btn.btn {
		min-width: 34px;
		width: 34px;
		height: 34px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-color: #cbd5e1;
		color: #475569;
		background: #fff;
	}

	.lab-panel-theme .rft-form-type-card-actions .rft-icon-btn.btn:hover {
		border-color: var(--workflow-accent-border, #b8bbd4);
		color: var(--workflow-accent, #17135F);
		background: var(--workflow-accent-soft, #e8e9f3);
	}

	.lab-panel-theme .rft-form-type-card.is-rft-hidden {
		opacity: 0.72;
		border-style: dashed;
	}

	.lab-panel-theme .workflow-board-filter-nested {
		background: #fff;
		border: 1px solid #f1f5f9;
		border-radius: 8px;
		padding: 16px 18px;
		margin-bottom: 16px;
	}

	.rft-topbar .navbar-brand span {
		color: #334155 !important;
		font-weight: 600;
		font-size: 0.8125rem;
	}

	.rft-topbar__actions {
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.rft-topbar__user {
		font-size: 0.75rem;
		font-weight: 500;
		color: #64748b;
		padding: 0 2px;
	}

	.rft-icon-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 32px;
		min-height: 32px;
		padding: 0 10px;
		border-radius: 8px;
		border: 1px solid #e8ecf2;
		background: #fff;
		color: #64748b;
		font-size: 0.75rem;
		font-weight: 500;
		text-decoration: none;
		transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
	}

	.rft-icon-btn:hover {
		background: var(--workflow-accent-soft, #e8e9f3);
		border-color: var(--workflow-accent-border, #b8bbd4);
		color: var(--workflow-accent, #17135F);
		text-decoration: none;
	}

	.rft-icon-btn--danger:hover {
		background: #fef2f2;
		border-color: #fecaca;
		color: #dc2626;
	}

	/* —— Panels & cards —— */
	.rft-theme .workflow-board-panel {
		border-radius: var(--rft-card-radius);
		border: 1px solid #e8ecf2;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
		overflow: visible;
		margin-bottom: 0.75rem !important;
	}

	.rft-theme .workflow-board-panel-header {
		padding: 10px 14px;
		background: #fff;
		border-bottom: 1px solid #f1f5f9;
	}

	.rft-theme .workflow-board-panel-header h5 {
		font-size: 0.875rem;
	}

	.rft-theme .workflow-board-panel-header h5 small {
		font-size: 0.75rem;
	}

	.rft-theme .workflow-board-panel-body {
		padding: 14px;
	}

	.rft-theme .workflow-board-section-label {
		font-size: 0.68rem;
		letter-spacing: 0.05em;
		text-transform: uppercase;
		font-weight: 600;
		color: #94a3b8;
		margin-bottom: 0.5rem !important;
	}

	.rft-theme .rft-overview-section {
		margin-bottom: 0.75rem !important;
	}

	.rft-theme .workflow-board-section-label .mdi {
		color: var(--rft-support-deep);
	}

	.rft-theme .workflow-board-panel-header h5 .mdi {
		color: var(--workflow-accent, #17135F);
	}

	/* —— Controls —— */
	.rft-theme .btn-action-sm,
	.rft-theme .btn.btn-sm {
		min-height: var(--rft-control-h);
		padding: 0.25rem 0.65rem;
		border-radius: 8px;
		font-weight: 500;
		font-size: 0.75rem;
		line-height: 1.4;
	}

	.rft-theme .btn-primary.btn-action-sm,
	.rft-theme .btn-primary {
		box-shadow: none;
	}

	.rft-theme .btn-outline-primary.btn-action-sm {
		border-width: 1px;
	}

	.rft-theme .rft-btn-support {
		background: var(--rft-support);
		border-color: var(--rft-support);
		color: var(--rft-support-text);
		font-weight: 700;
		box-shadow: 0 2px 8px rgba(var(--rft-support-rgb), 0.25);
	}

	.rft-theme .rft-btn-support:hover,
	.rft-theme .rft-btn-support:focus {
		background: var(--rft-support-hover);
		border-color: var(--rft-support-hover);
		color: var(--rft-support-text);
	}

	.rft-theme .form-control {
		min-height: var(--rft-control-h);
		border-radius: 8px;
		border-color: #e2e8f0;
		font-size: 0.8125rem;
		padding: 0.35rem 0.65rem;
		background: #fff;
	}

	.rft-theme .form-control:focus {
		border-color: var(--workflow-accent-border);
		box-shadow: 0 0 0 2px var(--color-primary-focus, rgba(59, 95, 192, 0.15));
	}

	.rft-theme label,
	.rft-theme .control-label,
	.rft-theme .form-label {
		font-size: 0.75rem;
		font-weight: 600;
		color: #475569;
		margin-bottom: 0.25rem;
	}

	.rft-theme .form-group,
	.rft-theme .mb-3 {
		margin-bottom: 0.75rem !important;
	}

	/* —— Segmented tabs (replaces bootstrappy pills) —— */
	.rft-segmented {
		display: inline-flex;
		padding: 3px;
		background: #eef1f6;
		border-radius: 9px;
		gap: 2px;
		margin-bottom: 0.875rem;
	}

	.rft-segmented__btn {
		border: none;
		background: transparent;
		color: #64748b;
		font-weight: 500;
		font-size: 0.75rem;
		padding: 6px 14px;
		border-radius: 7px;
		min-height: 30px;
		cursor: pointer;
		transition: background 0.15s ease, color 0.15s ease;
	}

	.rft-segmented__btn:hover {
		color: var(--workflow-accent, #17135F);
	}

	.rft-segmented__btn.is-active {
		background: #fff;
		color: var(--workflow-accent, #17135F);
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
		border-bottom: 2px solid var(--rft-support, #A7CE38);
	}

	/* —— Workflow overview cards —— */
	.rft-theme .rft-workflow-card {
		border-radius: var(--rft-card-radius);
		padding: 12px 14px;
		border: 1px solid #e8ecf2;
		min-height: 0;
	}

	.rft-theme .rft-workflow-card strong {
		font-size: 0.8125rem;
		font-weight: 600;
	}

	.rft-theme .rft-workflow-card .text-muted.small {
		font-size: 0.72rem !important;
		line-height: 1.4;
	}

	.rft-theme .rft-workflow-card.is-active {
		border-color: var(--workflow-accent-border, #b8bbd4);
		border-left: 3px solid var(--workflow-accent, #17135F);
		background: linear-gradient(135deg, var(--workflow-accent-soft, #e8e9f3) 0%, #fff 50%);
		box-shadow: none;
	}

	.rft-theme .rft-workflow-card:not(:disabled):hover {
		border-color: #d5dae3;
		box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
	}

	.rft-theme .rft-workflow-card-icon {
		width: 36px;
		height: 36px;
		border-radius: 9px;
		font-size: 1.1rem;
		background: color-mix(in srgb, var(--card-accent, var(--workflow-accent)) 12%, white);
		color: var(--card-accent, var(--workflow-accent));
	}

	.rft-theme .rft-workflow-card.is-active .rft-workflow-card-icon {
		box-shadow: inset 0 0 0 2px var(--rft-support, #A7CE38);
	}

	.rft-theme .rft-count-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 22px;
		height: 22px;
		padding: 0 6px;
		border-radius: 999px;
		font-size: 0.7rem;
		font-weight: 600;
		background: #f1f5f9;
		color: #475569;
		border: none;
	}

	.rft-theme .rft-workflow-card.is-active .rft-count-badge {
		background: var(--rft-support-soft);
		color: var(--rft-support-deep);
	}

	/* —— Form type cards —— */
	.rft-theme .rft-card-row > [class*="col-"] {
		display: flex;
	}

	.rft-theme .rft-form-type-card {
		display: flex;
		flex-direction: column;
		width: 100%;
		border-radius: var(--rft-card-radius);
		padding: 12px 14px;
	}

	.rft-theme .rft-form-type-card h6 {
		font-size: 0.8125rem;
	}

	.rft-theme .rft-form-type-card-desc {
		font-size: 0.75rem !important;
		min-height: 0 !important;
		margin-bottom: 8px !important;
	}

	.rft-theme .rft-form-type-card-meta {
		font-size: 0.7rem !important;
		margin-bottom: 10px !important;
		gap: 6px 10px !important;
	}

	.rft-theme .rft-form-type-card-actions {
		margin-top: auto;
		padding-top: 8px !important;
	}

	.rft-theme .rft-form-type-card-icon {
		width: 36px;
		height: 36px;
		border-radius: 9px;
		font-size: 1.1rem !important;
		background: var(--workflow-accent-soft, #e8e9f3);
		color: var(--workflow-accent, #17135F);
		position: relative;
	}

	.rft-theme .rft-card-row > [class*="col-"]:nth-child(3n + 1) .rft-form-type-card-icon::after,
	.rft-theme .rft-card-row > [class*="col-"]:nth-child(3n + 2) .rft-form-type-card-icon::after,
	.rft-theme .rft-card-row > [class*="col-"]:nth-child(3n) .rft-form-type-card-icon::after {
		content: '';
		position: absolute;
		bottom: -2px;
		right: -2px;
		width: 10px;
		height: 10px;
		border-radius: 50%;
		background: var(--rft-support, #A7CE38);
		border: 2px solid #fff;
	}

	.rft-theme .rft-form-type-card.is-filtered {
		border-color: var(--workflow-accent, #17135F);
		box-shadow: 0 0 0 2px var(--rft-support-soft), 0 4px 16px rgba(23, 19, 95, 0.08);
	}

	.rft-theme .rft-form-type-card-meta .mdi-calendar-today {
		color: var(--rft-support-deep);
	}

	/* —— Instance list (tablet cards, not table) —— */
	.rft-instance-list {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}

	.rft-instance-card {
		display: grid;
		grid-template-columns: 1fr auto;
		grid-template-rows: auto auto;
		gap: 4px 10px;
		align-items: center;
		padding: 10px 12px;
		background: #fff;
		border: 1px solid #e8ecf2;
		border-radius: 9px;
		transition: border-color 0.15s ease;
	}

	.rft-instance-card:hover {
		border-color: var(--workflow-accent-border, #b8bbd4);
	}

	.rft-instance-card__main {
		display: flex;
		align-items: center;
		gap: 10px;
		min-width: 0;
		grid-column: 1;
		grid-row: 1;
	}

	.rft-instance-card__number {
		flex-shrink: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 40px;
		height: 36px;
		padding: 0 6px;
		border-radius: 7px;
		background: var(--workflow-accent-soft, #e8e9f3);
		color: var(--workflow-accent, #17135F);
		font-weight: 600;
		font-size: 0.7rem;
		border-bottom: 2px solid var(--rft-support, #A7CE38);
	}

	.rft-instance-card__title {
		font-weight: 600;
		font-size: 0.8125rem;
		color: #334155;
		margin: 0;
		line-height: 1.3;
	}

	.rft-instance-card__subtitle {
		font-size: 0.72rem;
		color: #94a3b8;
		margin: 1px 0 0;
	}

	.rft-instance-card__meta {
		grid-column: 1;
		grid-row: 2;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 8px;
		padding-left: 50px;
	}

	.rft-instance-card__actions {
		grid-column: 2;
		grid-row: 1 / span 2;
		display: flex;
		align-items: center;
		gap: 4px;
	}

	.rft-instance-card__actions .btn {
		min-width: 30px;
		min-height: 30px;
		width: 30px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		padding: 0;
		border-radius: 7px;
		font-size: 0.85rem;
	}

	.rft-status-chip {
		display: inline-flex;
		align-items: center;
		padding: 2px 7px;
		border-radius: 999px;
		font-size: 0.65rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.03em;
	}

	.rft-status-chip--draft {
		background: #fef3c7;
		color: #92400e;
	}

	.rft-status-chip--partial {
		background: #fff8e1;
		color: #f57f17;
	}

	.rft-status-chip--submitted {
		background: var(--rft-support-soft);
		color: var(--rft-support-deep);
	}

	.rft-status-chip--default {
		background: #f1f5f9;
		color: #475569;
	}

	.rft-instance-card__time {
		font-size: 0.7rem;
		color: #94a3b8;
	}

	@media (min-width: 992px) {
		.rft-instance-card {
			grid-template-columns: 1fr auto auto;
			grid-template-rows: 1fr;
		}

		.rft-instance-card__meta {
			grid-column: 2;
			grid-row: 1;
			padding-left: 0;
			justify-content: flex-end;
		}

		.rft-instance-card__actions {
			grid-column: 3;
			grid-row: 1;
		}
	}

	/* —— Filter toolbar —— */
	.rft-toolbar {
		display: grid;
		grid-template-columns: 1fr;
		gap: 6px;
		margin-bottom: 0.875rem;
	}

	@media (min-width: 768px) {
		.rft-toolbar {
			grid-template-columns: 1.4fr 1fr auto;
		}
	}

	.rft-toolbar .btn {
		min-height: var(--rft-control-h);
		border-radius: 8px;
		font-weight: 500;
		font-size: 0.75rem;
	}

	/* —— Wizard progress bar (step-based) —— */
	.rft-wizard-progress__track {
		height: 8px;
		border-radius: 999px;
		background: #e8ecf2;
		overflow: hidden;
	}

	.rft-wizard-progress__bar {
		height: 100%;
		border-radius: inherit;
		background: linear-gradient(90deg, var(--workflow-accent) 0%, var(--rft-support) 100%);
		transition: width 0.35s ease;
	}

	/* —— Wizard stepper: spread + connecting lines —— */
	.rft-wizard-stepper {
		background: #fff;
		border: 1px solid #e8ecf2;
		border-radius: var(--rft-card-radius);
		padding: 14px 16px 12px;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
		margin-bottom: 0.75rem;
	}

	.rft-wizard-stepper__list {
		display: flex;
		flex-wrap: nowrap;
		align-items: flex-start;
		list-style: none;
		margin: 0;
		padding: 0;
		position: relative;
	}

	.rft-wizard-stepper__list--spread {
		justify-content: space-around;
		width: 100%;
	}

	.rft-wizard-stepper__list--spread::before {
		content: '';
		position: absolute;
		top: 15px;
		left: 8%;
		right: 8%;
		height: 2px;
		background: #e2e8f0;
		z-index: 0;
		border-radius: 999px;
	}

	.rft-wizard-stepper__item {
		display: flex;
		align-items: flex-start;
		justify-content: center;
		flex: 1 1 0;
		min-width: 0;
		position: relative;
		z-index: 1;
	}

	.rft-wizard-stepper__item:not(:last-child)::after {
		display: none;
	}

	.rft-wizard-stepper__link,
	.rft-wizard-stepper__current,
	.rft-wizard-stepper__upcoming {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 6px;
		min-width: 0;
		max-width: 110px;
		width: 100%;
		text-align: center;
		text-decoration: none;
		padding: 0 4px;
		color: inherit;
	}

	.rft-wizard-stepper__link:hover {
		text-decoration: none;
	}

	.rft-wizard-stepper__index {
		width: 30px;
		height: 30px;
		border-radius: 50%;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 0.75rem;
		font-weight: 700;
		border: 2px solid #e2e8f0;
		background: #fff;
		color: #94a3b8;
		transition: all 0.15s ease;
		box-shadow: 0 0 0 4px #fff;
	}

	.rft-wizard-stepper__label {
		font-size: 0.68rem;
		font-weight: 500;
		color: #94a3b8;
		line-height: 1.25;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.rft-wizard-stepper__item.is-complete .rft-wizard-stepper__index {
		background: var(--color-success-strong, #16a34a);
		border-color: var(--color-success-strong, #16a34a);
		color: #fff;
	}

	.rft-wizard-stepper__item.is-complete .rft-wizard-stepper__index .mdi {
		color: #fff;
		font-size: 1rem;
		line-height: 1;
		display: inline-block;
	}

	.rft-wizard-stepper__item.is-complete .rft-wizard-stepper__label {
		color: var(--color-success-strong, #16a34a);
		font-weight: 600;
	}

	.rft-wizard-stepper__item.is-active .rft-wizard-stepper__index {
		background: var(--workflow-accent);
		border-color: var(--workflow-accent);
		color: #fff;
		box-shadow: 0 0 0 4px #fff, 0 0 0 6px var(--workflow-accent-soft);
	}

	.rft-wizard-stepper__item.is-active .rft-wizard-stepper__label {
		color: var(--workflow-accent);
		font-weight: 700;
	}

	/* Sample row cards — subtle frame when closed and open */
	.rft-sample-row-card {
		border: 1px solid #e8ecf2;
		border-radius: 12px;
		background: #fff;
		padding: 0;
		margin-bottom: 12px;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
		overflow: visible;
	}

	.rft-sample-row-card.is-open {
		overflow: visible;
	}

	.rft-sample-row-card__header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		width: 100%;
		margin: 0;
		padding: 12px 14px;
		border: 0 !important;
		border-radius: 0;
		background: transparent;
		text-align: left;
		outline: none !important;
		box-shadow: none !important;
	}

	.rft-sample-row-card.is-open .rft-sample-row-card__header {
		padding: 12px 14px 8px;
	}

	.rft-sample-row-card__toggle-main {
		cursor: pointer;
		border-radius: 0 !important;
		outline: none !important;
		box-shadow: none !important;
	}

	.rft-sample-row-card__toggle-main:focus,
	.rft-sample-row-card__toggle-main:focus-visible,
	.rft-sample-row-card__chevron:focus,
	.rft-sample-row-card__chevron:focus-visible {
		outline: none !important;
		box-shadow: none !important;
	}

	.rft-sample-row-card__toggle-main:hover .rft-sample-row-card__title {
		color: var(--workflow-accent, #6D0A0E);
	}

	.rft-sample-row-card__header-actions {
		display: flex;
		align-items: center;
		gap: 6px;
		flex-shrink: 0;
		margin-left: auto;
	}

	.rft-sample-row-card__chevron {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		color: #64748b;
		font-size: 1.35rem;
		line-height: 1;
		cursor: pointer;
		border-radius: 6px;
		outline: none !important;
		box-shadow: none !important;
	}

	.rft-sample-row-card__chevron:hover {
		background: #f1f5f9;
		color: #334155;
	}

	.rft-sample-row-card__body {
		padding: 4px 14px 14px;
		border: 0 !important;
		border-radius: 0 !important;
		overflow: visible;
	}

	.rft-sample-row-card__title {
		font-size: 0.8125rem;
		font-weight: 700;
		color: #334155;
		margin: 0;
	}

	.rft-sample-row-card__summary {
		line-height: 1.35;
	}

	/* Wider gaps between the 3 columns; fields sit in narrower tracks */
	.rft-sample-grid-row.row,
	.rft-sample-grid-row {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		column-gap: 2.25rem;
		row-gap: 0.35rem;
		margin-left: 0;
		margin-right: 0;
	}

	.rft-sample-cards .rft-sample-grid-row--field-data {
		margin-bottom: 1rem;
	}

	.rft-sample-cards .rft-sample-grid-row--field-data .rft-sample-field {
		margin-bottom: 0;
	}

	.rft-sample-cards .rft-sample-grid-row-divider {
		margin: 0.5rem 0 1rem;
		border-top: 1px solid #e2e8f0;
	}

	.rft-sample-section-label ~ .rft-sample-field--full {
		margin-bottom: calc(0.75rem + 2px);
	}

	.rft-sample-grid-row:has(.rft-param-picker.is-open) {
		position: relative;
		z-index: 120;
	}

	.rft-sample-grid-row > .rft-sample-field:has(.rft-param-picker.is-open) {
		position: relative;
		z-index: 2;
	}

	.rft-sample-grid-row--cols-2 {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.rft-sample-grid-row--cols-3 {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}

	.rft-sample-section-label {
		font-size: 0.72rem;
		font-weight: 700;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: #64748b;
		margin: 1.35rem 0 0.5rem;
		padding-top: 0.35rem;
		border-top: 1px solid #e2e8f0;
	}

	.rft-sample-field--test-requirements .row.pt-1 {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
		gap: 0.25rem 0.75rem;
		margin-left: 0;
		margin-right: 0;
	}

	.rft-sample-field--test-requirements .col-6 {
		flex: none;
		max-width: none;
		width: auto;
		padding-left: 0;
		padding-right: 0;
	}

	.rft-sample-field--test-requirements .col-6:nth-child(1) {
		grid-column: 1;
		grid-row: 1;
	}

	.rft-sample-field--test-requirements .col-6:nth-child(2) {
		grid-column: 1;
		grid-row: 2;
	}

	.rft-sample-field--test-requirements .col-6:nth-child(3) {
		grid-column: 2;
		grid-row: 1;
	}

	.rft-sample-field--test-requirements .custom-control {
		margin-bottom: 0.35rem;
	}

	.rft-sample-field--test-requirements .custom-control-label {
		font-size: 0.78rem;
		line-height: 1.25;
	}

	.rft-sample-grid-row.row > [class*='col-'],
	.rft-sample-grid-row > [class*='col-'] {
		width: 100%;
		max-width: 100%;
		flex: none;
		padding-left: 0;
		padding-right: 0;
	}

	@media (max-width: 767.98px) {
		.rft-sample-grid-row.row,
		.rft-sample-grid-row {
			grid-template-columns: 1fr;
			column-gap: 0;
		}

		.rft-sample-grid-row--compact {
			grid-template-columns: 1fr;
		}
	}

	.rft-sample-grid-row .rft-sample-field .form-control,
	.rft-sample-grid-row .rft-sample-field .custom-select,
	.rft-sample-grid-row .rft-sample-field select.form-control,
	.rft-sample-grid-row .rft-sample-field .input-group,
	.rft-sample-grid-row .walk-in-trf-qty-unit {
		width: 100%;
		max-width: calc(100% - 0.25rem);
	}

	.rft-sample-field {
		margin-bottom: 0.65rem;
	}

	.rft-sample-field--full {
		margin-bottom: 0.75rem;
	}

	.rft-sample-field label {
		display: block;
		font-size: 0.72rem;
		font-weight: 600;
		color: #64748b;
		margin-bottom: 0.25rem;
	}

	.rft-sample-field .form-control,
	.rft-sample-field .custom-select,
	.rft-sample-field select.form-control {
		min-height: 34px;
		height: 34px;
		padding: 0.25rem 0.5rem;
		font-size: 0.8125rem;
		border-radius: 8px;
	}

	.rft-sample-field textarea.form-control {
		height: auto;
		min-height: 80px;
	}

	[x-cloak] {
		display: none !important;
	}

	.rft-sample-field .form-check {
		min-height: 0;
		margin-bottom: 0.2rem;
		padding-left: 1.35rem;
	}

	.rft-sample-field .form-check-label {
		font-size: 0.78rem;
		color: #475569;
	}

	.rft-sample-description .tox-tinymce {
		border-radius: 8px;
		border-color: #e2e8f0;
	}

	.rft-gap-xs {
		gap: 0.25rem;
	}

	.rft-qty-input {
		width: 48%;
	}

	.rft-unit-input {
		width: 52%;
	}

	.rft-sample-field--empty {
		min-height: 1px;
	}

	.min-width-0 {
		min-width: 0;
	}

	.rft-sample-row-card__preview {
		display: inline-block;
		max-width: 220px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		vertical-align: bottom;
	}

	.rft-wizard-nav {
		gap: 0.5rem;
	}

	/* Parameter chip picker */
	.rft-param-picker {
		position: relative;
		z-index: 5;
	}

	.rft-param-picker.is-open {
		z-index: 130;
	}

	.rft-param-picker__trigger {
		min-height: 38px;
		width: 100%;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #f8fafc;
		padding: 6px 10px;
		cursor: pointer;
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 8px;
		text-align: left;
	}

	.rft-param-picker__chips {
		display: flex;
		flex-wrap: wrap;
		gap: 4px;
		align-items: center;
		align-content: flex-start;
		min-width: 0;
		flex: 1 1 auto;
		width: 100%;
	}

	.rft-param-chip {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		padding: 2px 8px;
		border-radius: 999px;
		background: var(--workflow-accent-soft);
		color: var(--workflow-accent);
		font-size: 0.68rem;
		font-weight: 600;
		max-width: 180px;
		flex: 0 1 auto;
		min-width: 0;
	}

	.rft-param-chip span {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		min-width: 0;
	}

	.rft-param-chip__remove,
	.rft-param-chip button {
		border: 0;
		background: transparent;
		color: inherit;
		padding: 0;
		line-height: 1;
		cursor: pointer;
		flex: 0 0 auto;
	}

	.rft-param-picker__caret {
		flex: 0 0 auto;
		margin-top: 4px;
	}

	.rft-param-picker__more {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 22px;
		height: 22px;
		border-radius: 999px;
		background: var(--workflow-accent);
		color: #fff;
		font-size: 0.65rem;
		font-weight: 700;
		flex: 0 0 auto;
	}

	.rft-param-picker__panel {
		position: absolute;
		z-index: 1080;
		left: 0;
		right: 0;
		top: calc(100% + 6px);
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
		padding: 10px;
		max-height: min(320px, 55vh);
		overflow: auto;
	}

	.rft-param-picker__panel.is-up {
		top: auto;
		bottom: calc(100% + 6px);
	}

	.rft-param-picker__grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 6px;
		margin-top: 8px;
	}

	@media (min-width: 992px) {
		.rft-param-picker__grid {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}

	.rft-param-option {
		display: flex;
		align-items: flex-start;
		gap: 6px;
		border: 1px solid #e8ecf2;
		border-radius: 8px;
		padding: 6px 8px;
		cursor: pointer;
		font-size: 0.72rem;
		color: #475569;
		background: #fff;
		margin: 0;
		min-width: 0;
	}

	.rft-param-option span {
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
	}

	.rft-param-option.is-selected {
		border-color: var(--workflow-accent);
		background: var(--workflow-accent-soft);
		color: var(--workflow-accent);
		font-weight: 600;
	}

	.rft-analysis-type-checks__toolbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.75rem;
		margin-bottom: 0.5rem;
	}

	.rft-analysis-type-checks__toolbar .form-control {
		flex: 1 1 auto;
		min-width: 0;
	}

	.rft-analysis-type-checks__panel {
		max-height: 220px;
		overflow: auto;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #f8fafc;
	}

	.rft-analysis-type-checks__row {
		display: flex;
		align-items: flex-start;
		gap: 0.55rem;
		margin: 0;
		padding: 0.5rem 0.7rem;
		border-bottom: 1px solid #eef2f7;
		cursor: pointer;
	}

	.rft-analysis-type-checks__row:last-child {
		border-bottom: 0;
	}

	.rft-analysis-type-checks__row.is-selected {
		background: var(--workflow-accent-soft);
	}

	.rft-analysis-type-checks__list {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
		gap: 0.5rem;
	}

	.rft-analysis-type-checks__item {
		display: flex;
		align-items: flex-start;
		gap: 0.55rem;
		margin: 0;
		padding: 0.55rem 0.7rem;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #f8fafc;
		cursor: pointer;
	}

	.rft-analysis-type-checks__item.is-selected {
		border-color: var(--workflow-accent);
		background: var(--workflow-accent-soft);
	}

	.rft-analysis-type-checks__input {
		margin-top: 0.15rem;
	}

	.rft-analysis-type-checks__body {
		display: flex;
		flex-direction: column;
		min-width: 0;
		gap: 0.1rem;
	}

	.rft-analysis-type-checks__label {
		font-size: 0.9rem;
		font-weight: 600;
		color: #1f2937;
		line-height: 1.25;
	}

	.rft-analysis-type-checks__meta {
		font-size: 0.75rem;
		color: #64748b;
	}

	.rft-tests-modal {
		position: fixed;
		inset: 0;
		z-index: 1080;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1rem;
		background: rgba(15, 23, 42, 0.45);
	}

	.rft-tests-modal__dialog {
		width: min(920px, 100%);
		max-height: min(86vh, 820px);
		display: flex;
		flex-direction: column;
		background: #fff;
		border-radius: 14px;
		box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
		overflow: hidden;
	}

	.rft-tests-modal__header,
	.rft-tests-modal__toolbar,
	.rft-tests-modal__footer {
		padding: 0.9rem 1.1rem;
		border-bottom: 1px solid #e2e8f0;
	}

	.rft-tests-modal__header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
	}

	.rft-tests-modal__footer {
		border-bottom: 0;
		border-top: 1px solid #e2e8f0;
		display: flex;
		justify-content: flex-end;
	}

	.rft-tests-modal__body {
		padding: 1rem 1.1rem;
		overflow: auto;
		flex: 1 1 auto;
	}

	.rft-tests-sample-block {
		margin-bottom: 1rem;
	}

	.rft-tests-sample-block__title {
		margin: 0 0 0.55rem;
		font-size: 0.95rem;
		font-weight: 700;
		color: #0f172a;
		padding-bottom: 0.35rem;
		border-bottom: 2px solid #e2e8f0;
	}

	.rft-tests-atable-wrap {
		margin-bottom: 0.75rem;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		overflow: hidden;
		background: #fff;
	}

	.rft-tests-atable__analysis {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.75rem;
		padding: 0.55rem 0.75rem;
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
		font-size: 0.86rem;
		font-weight: 700;
		color: #334155;
	}

	.rft-tests-atable__toggle {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		border: 0;
		background: transparent;
		padding: 0;
		font: inherit;
		font-weight: 700;
		color: inherit;
		text-align: left;
		cursor: pointer;
		min-width: 0;
	}

	.rft-tests-atable__toggle .mdi {
		font-size: 1.1rem;
		line-height: 1;
		color: #64748b;
	}

	.rft-tests-atable__count {
		font-size: 0.75rem;
		font-weight: 600;
		color: #64748b;
		margin-left: 0.15rem;
	}

	.rft-tests-atable__group-check {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		margin: 0;
		padding: 0;
		border: 0;
		background: transparent;
		cursor: pointer;
		white-space: nowrap;
		font-weight: 500;
	}

	.rft-tests-check {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 1.05rem;
		height: 1.05rem;
		border: 1.5px solid #94a3b8;
		border-radius: 0.25rem;
		background: #fff;
		flex-shrink: 0;
		box-sizing: border-box;
		transition: background-color 0.12s ease, border-color 0.12s ease;
	}

	.rft-tests-check.is-checked {
		background: var(--workflow-accent, #2563eb);
		border-color: var(--workflow-accent, #2563eb);
	}

	.rft-tests-check.is-checked::after {
		content: '';
		width: 0.28rem;
		height: 0.5rem;
		margin-top: -0.1rem;
		border: solid #fff;
		border-width: 0 2px 2px 0;
		transform: rotate(45deg);
	}

	.rft-tests-check.is-partial {
		background: var(--workflow-accent, #2563eb);
		border-color: var(--workflow-accent, #2563eb);
	}

	.rft-tests-check.is-partial::after {
		content: '';
		width: 0.55rem;
		height: 0;
		border-top: 2px solid #fff;
	}

	.rft-tests-modal__toolbar-actions {
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
	}

	.rft-tests-atable {
		width: 100%;
		border-collapse: collapse;
		margin: 0;
	}

	.rft-tests-atable th,
	.rft-tests-atable td {
		padding: 0.45rem 0.75rem;
		border-bottom: 1px solid #f1f5f9;
		vertical-align: middle;
		font-size: 0.88rem;
	}

	.rft-tests-atable thead th {
		font-size: 0.72rem;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: #64748b;
		background: #fff;
	}

	.rft-tests-atable__check {
		width: 42px;
		text-align: center;
	}

	.rft-tests-atable__row {
		cursor: pointer;
	}

	.rft-tests-atable__row:hover {
		background: #f8fafc;
	}

	.rft-tests-atable__row.is-selected {
		background: var(--workflow-accent-soft, #eff6ff);
	}

	.rft-tests-atable__row.is-selected .rft-tests-check {
		background: var(--workflow-accent, #2563eb);
		border-color: var(--workflow-accent, #2563eb);
	}

	.rft-tests-atable__row.is-selected .rft-tests-check::after {
		content: '';
		width: 0.28rem;
		height: 0.5rem;
		margin-top: -0.1rem;
		border: solid #fff;
		border-width: 0 2px 2px 0;
		transform: rotate(45deg);
	}

	.rft-tests-atable tbody tr:last-child td {
		border-bottom: 0;
	}

	.rft-tests-group {
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 0.75rem;
		margin-bottom: 0.75rem;
		background: #f8fafc;
	}

	.rft-tests-group__header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 0.75rem;
		margin-bottom: 0.65rem;
	}

	.rft-tests-group__sample {
		font-size: 0.72rem;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: #64748b;
	}

	.rft-tests-group__analysis {
		font-size: 0.98rem;
		font-weight: 700;
		color: #111827;
	}

	.rft-sample-field--description {
		margin-top: 0.25rem;
	}

	.rft-theme .walk-in-trf-wizard__panel {
		overflow: visible;
	}

	/* —— Section progress: navy bar, green when complete —— */
	.rft-theme .submission-form-progress {
		border-radius: 9px;
		border-color: var(--workflow-accent-border, #b8bbd4);
		padding: 0.75rem 0.875rem;
	}

	.rft-theme .submission-form-progress__label {
		font-size: 0.8125rem;
		color: var(--workflow-accent, #17135F);
	}

	.rft-theme .submission-form-progress__percent {
		font-size: 0.875rem;
		color: var(--workflow-accent, #17135F);
	}

	.rft-theme .submission-form-progress__count {
		font-size: 0.7rem;
	}

	.rft-theme .submission-form-progress__bar.is-high,
	.rft-theme .submission-form-progress__bar.is-complete {
		background: linear-gradient(90deg, var(--workflow-accent, #17135F) 0%, var(--rft-support, #A7CE38) 100%);
	}

	/* —— Wizard touch action bar —— */
	.rft-touch-bar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 8px;
		padding-top: 0.875rem;
		margin-top: 0.25rem;
		border-top: 1px solid #f1f5f9;
	}

	.rft-touch-bar .btn {
		min-height: var(--rft-control-h);
		padding: 0.3rem 0.85rem;
		border-radius: 8px;
		font-size: 0.8125rem;
		font-weight: 500;
	}

	.rft-touch-bar .btn-primary {
		min-width: 0;
	}

	/* —— Empty state —— */
	.rft-theme .workflow-empty-state {
		padding: 2rem 1rem;
	}

	.rft-theme .workflow-empty-state h5 {
		font-size: 0.9375rem;
	}

	.rft-theme .workflow-empty-state .mdi {
		color: var(--workflow-accent, #17135F) !important;
		opacity: 0.2 !important;
	}

	/* —— Modal —— */
	.rft-theme .workflow-modal-panel {
		border-radius: var(--rft-card-radius);
	}

	.rft-theme .workflow-modal-footer .btn {
		min-height: var(--rft-control-h);
		border-radius: 8px;
		font-weight: 500;
		font-size: 0.8125rem;
	}

	@media (max-width: 576px) {
		.rft-wizard-stepper__label {
			display: none;
		}

		.rft-instance-card__meta {
			padding-left: 0;
		}

		.rft-instance-card {
			grid-template-columns: 1fr;
		}

		.rft-instance-card__actions {
			grid-column: 1;
			grid-row: 3;
			justify-content: flex-end;
		}
	}

	/* Touch sizing must win over the compact desktop rules above. */
	@media (max-width: 991.98px) {
		.rft-theme .btn,
		.rft-theme .btn.btn-sm,
		.rft-theme .btn-action-sm,
		.rft-theme .rft-touch-bar .btn,
		.rft-theme .rft-sample-field .form-control,
		.rft-theme .rft-sample-field .custom-select,
		.rft-theme .rft-sample-field select.form-control,
		.rft-theme .walk-in-trf-wizard__panel .form-control,
		.rft-theme .walk-in-trf-wizard__panel .custom-select {
			min-height: 44px;
		}

		.rft-theme .rft-sample-field .form-control,
		.rft-theme .rft-sample-field .custom-select,
		.rft-theme .rft-sample-field select.form-control,
		.rft-theme .walk-in-trf-wizard__panel .form-control,
		.rft-theme .walk-in-trf-wizard__panel .custom-select {
			height: auto;
			font-size: 16px !important;
		}

		.rft-theme .rft-form-type-card-actions .rft-icon-btn.btn,
		.rft-theme .rft-card-menu-toggle,
		.rft-theme .rft-instance-card__actions .btn,
		.rft-theme .rft-sample-row-card__chevron {
			width: 44px;
			height: 44px;
			min-width: 44px;
			min-height: 44px;
		}

		.rft-theme .rft-card-menu-item {
			min-height: 44px;
			padding: 10px 12px;
		}
	}
</style>
