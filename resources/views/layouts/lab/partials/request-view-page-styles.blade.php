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
		padding: 20px 24px 24px;
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
</style>
