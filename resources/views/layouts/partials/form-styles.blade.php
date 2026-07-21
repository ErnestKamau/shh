<style>
	.lab-panel-theme {
		--workflow-accent: var(--color-primary);
		--workflow-accent-soft: var(--color-primary-soft);
	}

	.workflow-board-panel,
	.workflow-board-page .card {
		border-radius: 12px;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
		border: 1px solid var(--color-border);
		background: var(--color-surface);
	}

	.workflow-board-panel-header h5 .mdi,
	.workflow-board-panel-header h6 .mdi {
		color: var(--color-primary);
	}

	.workflow-filters-card,
	.workflow-brand-filters-card,
	.workflow-receiving-filters-card {
		background: var(--color-surface);
		border: 1px solid var(--color-border);
		border-radius: 10px;
		padding: 0.7rem 0.9rem;
		margin-bottom: 0.9rem;
		box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
	}

	.workflow-filters-card__title,
	.workflow-brand-filters-card__title {
		font-size: var(--ls-text-md, 0.875rem);
		font-weight: var(--font-semibold);
		color: var(--color-text);
		margin-bottom: 0.65rem;
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.workflow-brand-filters-card .form-control,
	.workflow-filters-card .form-control {
		border-radius: 8px;
		border-color: var(--color-border);
	}

	.workflow-brand-filters-card .btn-more-filters,
	.workflow-filters-card .btn-more-filters {
		border-radius: 8px;
		font-weight: 600;
	}
</style>
