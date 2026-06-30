<style>
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
		border-color: rgba(109, 10, 14, 0.22);
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
