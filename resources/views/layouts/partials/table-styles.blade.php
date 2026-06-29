<style>
	.workflow-table-wrapper {
		border: 1px solid var(--color-border);
		border-radius: 12px;
		overflow: hidden;
		background: var(--color-surface);
	}

	.workflow-table thead th {
		background: var(--color-bg);
		border-bottom: 1px solid var(--color-border);
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--color-muted);
	}

	.workflow-table tbody tr {
		border-left: 4px solid var(--color-primary);
		transition: background-color 0.15s ease;
	}

	.workflow-table tbody tr:hover {
		background: #fafafa;
	}

	.workflow-table tbody td {
		border-color: #f1f5f9;
		vertical-align: middle;
		padding: 0.75rem 1rem;
		font-size: var(--text-sm);
	}

	.workflow-status-pill {
		display: inline-flex;
		align-items: center;
		padding: 0.2rem 0.65rem;
		border-radius: var(--radius-pill);
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: capitalize;
	}

	.workflow-status-pill--complete {
		background: #dcfce7;
		color: #15803d;
		border: 1px solid #bbf7d0;
	}

	.workflow-status-pill--pending {
		background: #fef3c7;
		color: #b45309;
		border: 1px solid #fde68a;
	}

	.workflow-stat-card {
		background: var(--color-surface);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
		padding: var(--space-md);
	}

	.workflow-stat-label {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--color-muted);
	}

	.workflow-stat-value {
		font-size: var(--text-metric);
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
		color: var(--color-text);
	}

	.workflow-panel-selection-actions .btn-outline-primary {
		border-color: rgba(109, 10, 14, 0.22);
		color: var(--color-primary);
	}

	.workflow-panel-selection-actions .btn-outline-primary:hover {
		background: var(--color-primary-soft);
		color: var(--color-primary);
	}
</style>
