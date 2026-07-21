<style>
	/* Constrain workflow content so cards/tables stay dense (RFT-like), not edge-to-edge. */
	.workflow-board-page {
		max-width: 1280px;
		margin-left: auto;
		margin-right: auto;
		padding-left: 1.5rem;
		padding-right: 1.5rem;
	}

	@media (min-width: 768px) {
		.workflow-board-page {
			padding-left: 2rem;
			padding-right: 2rem;
		}
	}

	/* Single KPI strip instead of five separate cards */
	.workflow-stat-strip {
		display: grid;
		grid-template-columns: repeat(5, minmax(0, 1fr));
		background: var(--color-surface, #fff);
		border: 1px solid var(--color-border, #e2e8f0);
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
	}

	@media (max-width: 1199.98px) {
		.workflow-stat-strip {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}

	@media (max-width: 767.98px) {
		.workflow-stat-strip {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 575.98px) {
		.workflow-stat-strip {
			grid-template-columns: 1fr;
		}
	}

	.workflow-stat-strip__item {
		display: flex;
		gap: 0.5rem;
		align-items: flex-start;
		padding: 0.75rem 1rem;
		border-left: 1px solid var(--color-border, #e2e8f0);
		min-width: 0;
	}

	.workflow-stat-strip__item:first-child {
		border-left: none;
	}

	@media (max-width: 1199.98px) {
		.workflow-stat-strip__item:nth-child(3n + 1) {
			border-left: none;
		}
		.workflow-stat-strip__item:nth-child(n + 4) {
			border-top: 1px solid var(--color-border, #e2e8f0);
		}
	}

	@media (max-width: 767.98px) {
		.workflow-stat-strip__item:nth-child(3n + 1) {
			border-left: 1px solid var(--color-border, #e2e8f0);
		}
		.workflow-stat-strip__item:nth-child(2n + 1) {
			border-left: none;
		}
		.workflow-stat-strip__item:nth-child(n + 3) {
			border-top: 1px solid var(--color-border, #e2e8f0);
		}
	}

	@media (max-width: 575.98px) {
		.workflow-stat-strip__item {
			border-left: none !important;
		}
		.workflow-stat-strip__item:not(:first-child) {
			border-top: 1px solid var(--color-border, #e2e8f0);
		}
	}

	.workflow-stat-strip__dot {
		flex-shrink: 0;
		width: 8px;
		height: 8px;
		border-radius: 50%;
		margin-top: 6px;
		background: #94a3b8;
	}

	.workflow-stat-strip__dot--neutral { background: #94a3b8; }
	.workflow-stat-strip__dot--info { background: #0ea5e9; }
	.workflow-stat-strip__dot--success { background: #22c55e; }
	.workflow-stat-strip__dot--warning { background: #f59e0b; }
	.workflow-stat-strip__dot--primary { background: var(--color-primary, #3b5fc0); }

	.workflow-stat-strip__value {
		font-size: 1.25rem;
		font-weight: 700;
		color: var(--color-text, #0f172a);
		line-height: 1;
		margin: 0;
	}

	.workflow-stat-strip__label {
		font-size: 0.6875rem;
		font-weight: 600;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: var(--color-muted, #64748b);
		margin: 0.25rem 0 0;
	}

	.workflow-stat-strip__meta {
		font-size: 0.6875rem;
		color: #94a3b8;
		margin: 2px 0 0;
	}

	/* Legacy KPI grid (kept for any other consumers) */
	.workflow-kpi-grid {
		display: grid;
		grid-template-columns: repeat(5, minmax(0, 1fr));
		gap: 16px;
		margin-bottom: 1rem;
	}

	@media (max-width: 1199.98px) {
		.workflow-kpi-grid {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}

	@media (max-width: 767.98px) {
		.workflow-kpi-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 575.98px) {
		.workflow-kpi-grid {
			grid-template-columns: 1fr;
		}
	}

	.workflow-kpi-card {
		background: var(--color-surface);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		padding: var(--space-md);
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
		min-height: 100%;
	}

	.workflow-kpi-card__label {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--color-muted);
		margin-bottom: 0.35rem;
	}

	.workflow-kpi-card__value {
		font-size: var(--text-metric);
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
		color: var(--color-text);
	}

	.workflow-kpi-card__subtitle {
		font-size: var(--text-caption);
		color: var(--color-muted);
		margin-top: 0.35rem;
	}

	.workflow-receiving-tab.is-active {
		background: var(--color-primary);
		border-color: var(--color-primary);
		color: #fff;
	}

	.workflow-receiving-tab:not(.is-active):hover {
		border-color: var(--color-primary-border-soft);
		color: var(--color-primary);
	}

	.workflow-intray-panel {
		border-radius: 10px;
		border-color: var(--color-border);
	}

	.workflow-intray-toggle strong {
		color: var(--color-text);
	}
</style>
