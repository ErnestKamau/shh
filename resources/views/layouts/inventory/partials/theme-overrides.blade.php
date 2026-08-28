{{--
  Bridges legacy inventory markup to lab surface / workflow panel styling
  without rewriting every blade file.
--}}
<style>
	.inventory-page h2,
	.inventory-page h3 {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: var(--ls-font-semibold, 600);
		color: var(--ls-color-ink, var(--color-text));
	}

	.inventory-page h2.p-4,
	.inventory-page h3.p-4,
	.inventory-page .inventory-page-header {
		padding: 0.5rem 0 0.85rem !important;
		font-size: var(--ls-text-2xl, 1.05rem);
		overflow: hidden;
	}

	.inventory-page h2 .mdi,
	.inventory-page h3 .mdi {
		color: var(--color-primary);
	}

	.inventory-page > .bg-light,
	.inventory-page .bg-light:not(.modal-body):not(.dropdown-menu):not(.card-body):not(thead) {
		background: var(--ls-color-surface, #fff) !important;
		border: 1px solid var(--workflow-border, var(--color-border, #e2e8f0));
		border-radius: 12px;
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.1));
		padding: 0 !important;
		overflow: hidden;
		margin-bottom: 0.85rem;
	}

	.inventory-page .bg-light > .table-responsive,
	.inventory-page .bg-light > .card-body {
		padding: 0.85rem 1rem;
	}

	.inventory-page .table.table-bordered {
		border: none;
	}

	.inventory-page .table thead th {
		background: #f8fafc !important;
		color: var(--workflow-muted, #64748b) !important;
		font-size: var(--ls-text-xs, 0.6875rem);
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0) !important;
		white-space: nowrap;
	}

	.inventory-page .table thead.bg-light,
	.inventory-page .table thead.bg-light th,
	.inventory-page .table thead.p-2 {
		background: #f8fafc !important;
	}

	.inventory-page .table tbody td {
		vertical-align: middle;
		color: var(--workflow-text-main, #1e293b);
		border-color: var(--workflow-border, #e2e8f0);
	}

	.inventory-page .tab-card {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px;
		box-shadow: var(--card-shadow);
		overflow: hidden;
		background: #fff;
	}

	.inventory-page .tab-card .card-header.tab-card-header {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		padding: 0.35rem 0.75rem 0;
	}

	.inventory-page .tab-card-header > .nav-tabs > li > a.show,
	.inventory-page .tab-card-header > .nav-tabs > li > a.active {
		border-bottom-color: var(--color-primary) !important;
		color: var(--color-primary) !important;
	}

	.inventory-page .tab-card .nav-link.active {
		background-color: var(--color-primary-soft, #f1f5f9) !important;
		border-color: var(--color-border, #e2e8f0) !important;
		color: var(--color-primary) !important;
	}

	.inventory-page .card:not(.tab-card) {
		border-radius: 12px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		box-shadow: var(--card-shadow);
	}

	.inventory-page .card-head-sm,
	.inventory-page .card .card-header:not(.tab-card-header) {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
	}

	.inventory-page .btn-primary {
		background-color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
	}

	.inventory-page .btn-primary:hover:not(:disabled) {
		background-color: var(--color-primary-hover) !important;
		border-color: var(--color-primary-hover) !important;
	}

	.inventory-page .btn-outline-primary {
		color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
	}

	.inventory-page .btn-outline-primary:hover {
		background-color: var(--color-primary) !important;
		color: #fff !important;
	}

	.inventory-page .text-primary {
		color: var(--color-primary) !important;
	}

	.inventory-page .badge.badge-pill.bg-white {
		background: var(--color-primary-soft, #f1f5f9) !important;
		color: var(--color-primary) !important;
		border: 1px solid var(--color-border, #e2e8f0);
		font-weight: 600;
	}

	/* Dashboard */
	.inventory-dash-hero {
		display: flex;
		align-items: flex-end;
		justify-content: space-between;
		gap: 1rem;
		flex-wrap: wrap;
		margin-bottom: 1.25rem;
	}

	.inventory-dash-kicker {
		margin: 0 0 0.25rem;
		font-size: 0.75rem;
		font-weight: 600;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: var(--workflow-muted, #64748b);
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
	}

	.inventory-dash-kicker .mdi {
		color: var(--color-primary);
		font-size: 0.95rem;
	}

	.inventory-dash-title {
		margin: 0;
		font-size: 1.35rem;
		font-weight: 650;
		letter-spacing: -0.02em;
		color: var(--workflow-text-main, #1e293b);
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
	}

	.inventory-dash-subtitle {
		margin: 0.35rem 0 0;
		font-size: 0.875rem;
		color: var(--workflow-muted, #64748b);
		max-width: 42rem;
		line-height: 1.45;
	}

	.inventory-dash-hero-meta {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		padding: 0.4rem 0.7rem;
		border-radius: 999px;
		background: #fff;
		border: 1px solid var(--workflow-border, #e2e8f0);
		color: var(--workflow-muted, #64748b);
		font-size: 0.75rem;
		font-weight: 600;
	}

	.inventory-page .stat-card-link {
		text-decoration: none;
		color: inherit;
		cursor: pointer;
	}

	.inventory-page .stat-card-link:focus-visible {
		outline: 2px solid var(--color-primary);
		outline-offset: 2px;
	}

	.inventory-page .stat-card-hint {
		margin-top: 0.2rem;
		font-size: 0.75rem;
		color: var(--workflow-muted, #64748b);
		font-weight: 500;
		text-transform: none;
		letter-spacing: 0;
	}

	.inventory-page .stat-card.is-alert {
		border-color: #fecaca;
		background: linear-gradient(180deg, #fff 60%, #fef2f2 100%);
	}

	.inventory-page .stat-card.is-alert .stat-card-icon {
		background: #fef2f2;
		color: #b91c1c;
	}

	.inventory-page .stat-card.is-warn {
		border-color: #fde68a;
		background: linear-gradient(180deg, #fff 60%, #fffbeb 100%);
	}

	.inventory-page .stat-card.is-warn .stat-card-icon {
		background: #fffbeb;
		color: #b45309;
	}

	.lab-panel-theme .stat-cards-row.inventory-kpi-row {
		grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
		gap: 12px;
		margin-bottom: 1rem;
	}

	.inventory-pipeline {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
		gap: 0;
		margin: 0 0 1.15rem;
		background: #fff;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px;
		overflow: hidden;
		box-shadow: var(--card-shadow, 0 1px 3px rgb(0 0 0 / 0.08));
	}

	.inventory-pipeline-step {
		display: flex;
		flex-direction: column;
		gap: 0.15rem;
		padding: 0.75rem 0.85rem;
		min-height: 72px;
		text-decoration: none;
		color: inherit;
		border-right: 1px solid var(--workflow-border, #e2e8f0);
		position: relative;
		transition: background 0.15s ease;
	}

	.inventory-pipeline-step:last-child {
		border-right: none;
	}

	.inventory-pipeline-step:hover,
	.inventory-pipeline-step:focus-visible {
		background: #f8fafc;
		text-decoration: none;
		color: inherit;
	}

	.inventory-pipeline-step.has-work {
		box-shadow: inset 0 -3px 0 0 var(--color-primary, #6d0a0e);
	}

	.inventory-pipeline-count {
		font-size: 1.15rem;
		font-weight: 700;
		font-variant-numeric: tabular-nums;
		color: var(--workflow-text-main, #1e293b);
		line-height: 1.1;
	}

	.inventory-pipeline-label {
		font-size: 0.6875rem;
		font-weight: 600;
		letter-spacing: 0.02em;
		color: var(--workflow-muted, #64748b);
		line-height: 1.3;
	}

	.inventory-panel-meta {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 0.75rem;
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--workflow-muted, #64748b);
	}

	.inventory-attention-dot.is-warn { background: #d97706; }
	.inventory-attention-dot.is-out { background: #64748b; }

	.inventory-store-mark {
		width: 28px;
		height: 28px;
		border-radius: 8px;
		background: var(--color-primary-soft, #f8eaea);
		color: var(--color-primary, #6d0a0e);
		font-size: 0.75rem;
		font-weight: 700;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 auto;
	}

	.inventory-dash-grid + .inventory-dash-grid {
		margin-top: 0.15rem;
	}

	.inventory-quick-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 0.5rem;
		margin: 0 0 1.25rem;
	}

	.inventory-quick-actions a {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		min-height: 40px;
		padding: 0.4rem 0.85rem;
		border-radius: 999px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		background: #fff;
		color: var(--workflow-text-main, #1e293b);
		font-size: 0.8125rem;
		font-weight: 600;
		text-decoration: none;
		transition: border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
	}

	.inventory-quick-actions a .mdi {
		color: var(--color-primary);
		font-size: 1rem;
	}

	.inventory-quick-actions a:hover,
	.inventory-quick-actions a:focus-visible {
		border-color: var(--color-primary);
		color: var(--color-primary);
		box-shadow: var(--card-shadow);
		text-decoration: none;
	}

	.inventory-dash-grid {
		display: grid;
		grid-template-columns: minmax(0, 1.6fr) minmax(280px, 1fr);
		gap: 1rem;
		align-items: stretch;
	}

	.inventory-panel-link {
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--color-primary);
		text-decoration: none;
	}

	.inventory-panel-link:hover {
		text-decoration: underline;
	}

	.inventory-chart-legend {
		display: flex;
		gap: 1rem;
		margin-top: 0.75rem;
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--workflow-muted, #64748b);
	}

	.inventory-legend-swatch {
		display: inline-block;
		width: 10px;
		height: 10px;
		border-radius: 2px;
		margin-right: 0.35rem;
	}

	.inventory-legend-swatch.is-in { background: #6d0a0e; }
	.inventory-legend-swatch.is-out { background: #64748b; }

	.inventory-empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		min-height: 240px;
		padding: 1.5rem 1rem;
		color: var(--workflow-muted, #64748b);
	}

	.inventory-empty-state .mdi {
		font-size: 2.25rem;
		color: var(--color-primary);
		opacity: 0.7;
		margin-bottom: 0.5rem;
	}

	.inventory-empty-state h6 {
		margin: 0 0 0.35rem;
		font-size: 0.95rem;
		font-weight: 650;
		color: var(--workflow-text-main, #1e293b);
	}

	.inventory-empty-state p {
		margin: 0 0 0.85rem;
		max-width: 28rem;
		font-size: 0.8125rem;
		line-height: 1.5;
	}

	.inventory-empty-state-compact {
		min-height: 180px;
	}

	.inventory-attention-group + .inventory-attention-group {
		border-top: 1px solid var(--workflow-border, #e2e8f0);
	}

	.inventory-attention-heading {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 0.7rem 1rem 0.35rem;
		font-size: 0.6875rem;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: var(--workflow-muted, #64748b);
	}

	.inventory-attention-heading a {
		font-size: 0.75rem;
		font-weight: 600;
		letter-spacing: 0;
		text-transform: none;
		color: var(--color-primary);
	}

	.inventory-attention-item {
		display: flex;
		align-items: center;
		gap: 0.7rem;
		padding: 0.7rem 1rem;
		color: inherit;
		text-decoration: none;
		border-top: 1px solid #f1f5f9;
		min-height: 48px;
		transition: background 0.15s ease;
	}

	.inventory-attention-item:hover,
	.inventory-attention-item:focus-visible {
		background: #f8fafc;
		text-decoration: none;
		color: inherit;
	}

	.inventory-attention-dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		flex: 0 0 auto;
	}

	.inventory-attention-dot.is-action { background: var(--color-primary, #6d0a0e); }
	.inventory-attention-dot.is-alert { background: #b91c1c; }

	.inventory-attention-copy {
		display: flex;
		flex-direction: column;
		min-width: 0;
		flex: 1 1 auto;
	}

	.inventory-attention-copy strong {
		font-size: 0.8125rem;
		font-weight: 650;
		color: var(--workflow-text-main, #1e293b);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.inventory-attention-copy small {
		font-size: 0.75rem;
		color: var(--workflow-muted, #64748b);
	}

	.inventory-attention-item > .mdi {
		color: #94a3b8;
		font-size: 1.1rem;
	}

	@media (prefers-reduced-motion: reduce) {
		.lab-panel-theme .stat-card:hover {
			transform: none;
		}
	}

	#sidebar-container .inventory-nav-section {
		display: flex;
		align-items: center;
		width: 100%;
		padding: 0.85rem 0.85rem 0.25rem;
		margin: 0.15rem 0 0;
		height: auto !important;
		background: transparent !important;
		border: none !important;
		box-shadow: none !important;
		transform: none !important;
		pointer-events: none;
	}

	#sidebar-container .inventory-nav-section span {
		font-size: 0.65rem;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: rgba(255, 255, 255, 0.48) !important;
	}

	#sidebar-container .list-group > a.list-group-item .badge-danger:empty,
	#sidebar-container .list-group > a.list-group-item .badge:empty {
		display: none;
	}
</style>
